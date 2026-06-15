<?php
require_once 'app/config/database.php';
require_once 'app/models/OrderModel.php';
require_once 'app/helpers/SessionHelper.php';

/**
 * OrderApiController
 * RESTful API cho đặt hàng
 *
 * POST   /api/order              → store()       : Tạo đơn hàng từ giỏ hàng
 * GET    /api/order               → index()       : Danh sách đơn hàng (của user, hoặc tất cả nếu Admin)
 * GET    /api/order/{id}          → show()        : Chi tiết đơn hàng
 * PUT    /api/order/{id}/cancel   → cancel()      : Hủy đơn hàng
 * PUT    /api/order/{id}/status   → updateStatus(): Cập nhật trạng thái đơn hàng [Admin]
 *
 * Lưu ý: tất cả API đặt hàng yêu cầu đăng nhập (session).
 */
class OrderApiController {

    private $conn;
    private $allowedStatus = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->conn = (new Database())->getConnection();
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $this->_ensureTables();
    }

    private function _ensureTables() {
        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS `order` (
                id              INT AUTO_INCREMENT PRIMARY KEY,
                user_id         INT DEFAULT NULL,
                fullname        VARCHAR(100) NOT NULL,
                phone           VARCHAR(20)  NOT NULL,
                address         TEXT         NOT NULL,
                city            VARCHAR(100) NOT NULL,
                note            TEXT,
                shipping_method VARCHAR(20) DEFAULT 'standard',
                payment_method  VARCHAR(20) DEFAULT 'cod',
                discount        DECIMAL(15,2) DEFAULT 0,
                total           DECIMAL(15,2) NOT NULL,
                status          VARCHAR(20)   DEFAULT 'pending',
                created_at      DATETIME      DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS order_item (
                id         INT AUTO_INCREMENT PRIMARY KEY,
                order_id   INT NOT NULL,
                product_id INT NOT NULL,
                name       VARCHAR(200) NOT NULL,
                price      DECIMAL(15,2) NOT NULL,
                qty        INT NOT NULL,
                image      VARCHAR(255) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    // ══════════════════════════════════════════════════════════
    // POST /api/order  →  Tạo đơn hàng từ giỏ hàng
    // Body: { "fullname": "...", "phone": "...", "address": "...",
    //         "city": "...", "note": "...", "shipping_method": "standard|express",
    //         "payment_method": "cod|...", "discount": 0 }
    // ══════════════════════════════════════════════════════════
    public function store() {
        SessionHelper::requireLoginApi();

        // Không cho đặt hàng nếu giỏ hàng rỗng
        if (empty($_SESSION['cart'])) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => 'Giỏ hàng đang trống, không thể đặt hàng',
            ], 422);
        }

        $data = SessionHelper::getJsonInput();

        $fullname = htmlspecialchars(strip_tags(trim($data['fullname'] ?? '')));
        $phone    = trim($data['phone'] ?? '');
        $address  = htmlspecialchars(strip_tags(trim($data['address'] ?? '')));
        $city     = htmlspecialchars(strip_tags(trim($data['city'] ?? '')));
        $note     = htmlspecialchars(strip_tags(trim($data['note'] ?? '')));
        $shipping = in_array($data['shipping_method'] ?? '', ['standard', 'express'])
            ? $data['shipping_method'] : 'standard';
        $payment  = htmlspecialchars(strip_tags(trim($data['payment_method'] ?? 'cod')));
        $discount = (float)($data['discount'] ?? ($_SESSION['discount_amount'] ?? 0));

        $errors = [];
        if (empty($fullname))               $errors['fullname'] = 'Vui lòng nhập họ tên';
        if (!preg_match('/^[0-9]{9,11}$/', $phone)) $errors['phone'] = 'Số điện thoại không hợp lệ (9-11 số)';
        if (empty($address))                $errors['address'] = 'Vui lòng nhập địa chỉ';
        if (empty($city))                   $errors['city'] = 'Vui lòng chọn thành phố';

        if (!empty($errors)) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors'  => $errors,
            ], 422);
        }

        // Lấy sản phẩm trong giỏ hàng + kiểm tra tồn tại
        $cart = $_SESSION['cart'];
        $ids  = implode(',', array_map('intval', array_keys($cart)));
        $rows = $this->conn->query("SELECT * FROM product WHERE id IN ($ids)")->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => 'Sản phẩm trong giỏ hàng không còn tồn tại',
            ], 422);
        }

        $cartItems = [];
        $subtotal  = 0;
        foreach ($rows as $row) {
            $qty = (int)($cart[$row['id']] ?? 1);
            if ($qty <= 0) continue;
            $row['qty'] = $qty;
            $cartItems[] = $row;
            $subtotal += $row['price'] * $qty;
        }

        $shippingFee = ($shipping === 'express') ? 150000 : 0;
        $total       = $subtotal - $discount + $shippingFee;
        if ($total < 0) $total = 0;

        // Lưu đơn hàng
        $stmt = $this->conn->prepare("
            INSERT INTO `order` (user_id, fullname, phone, address, city, note,
                                shipping_method, payment_method, discount, total, status)
            VALUES (:uid,:fn,:ph,:addr,:city,:note,:ship,:pay,:disc,:total,'pending')
        ");
        $stmt->execute([
            ':uid'  => SessionHelper::currentUserId(),
            ':fn'   => $fullname, ':ph'   => $phone,
            ':addr' => $address,  ':city' => $city,
            ':note' => $note,
            ':ship' => $shipping, ':pay'  => $payment,
            ':disc' => $discount, ':total'=> $total,
        ]);
        $orderId = $this->conn->lastInsertId();

        // Lưu order_item
        $iStmt = $this->conn->prepare("
            INSERT INTO order_item (order_id, product_id, name, price, qty, image)
            VALUES (:oid,:pid,:name,:price,:qty,:img)
        ");
        foreach ($cartItems as $item) {
            $iStmt->execute([
                ':oid'   => $orderId,
                ':pid'   => $item['id'],
                ':name'  => $item['name'],
                ':price' => $item['price'],
                ':qty'   => $item['qty'],
                ':img'   => $item['image'],
            ]);
        }

        // Làm trống giỏ hàng sau khi đặt hàng thành công
        $_SESSION['cart']            = [];
        $_SESSION['discount_amount'] = 0;
        $_SESSION['voucher_code']    = '';

        SessionHelper::jsonResponse([
            'success' => true,
            'message' => 'Đặt hàng thành công',
            'data'    => [
                'order_id'     => (int)$orderId,
                'subtotal'     => $subtotal,
                'discount'     => $discount,
                'shipping_fee' => $shippingFee,
                'total'        => $total,
                'status'       => 'pending',
            ],
        ], 201);
    }

    // ══════════════════════════════════════════════════════════
    // GET /api/order  →  Danh sách đơn hàng
    // User: chỉ xem đơn hàng của mình. Admin: xem tất cả.
    // Query: ?status=pending|processing|...  ?search=...
    // ══════════════════════════════════════════════════════════
    public function index() {
        SessionHelper::requireLoginApi();

        $isAdmin = SessionHelper::currentUserRole() === 'admin';
        $where   = '1=1';
        $params  = [];

        if (!$isAdmin) {
            $where .= " AND o.user_id = :uid";
            $params[':uid'] = SessionHelper::currentUserId();
        } elseif (!empty($_GET['search'])) {
            $where .= " AND (o.fullname LIKE :s OR o.phone LIKE :s OR o.id LIKE :s)";
            $params[':s'] = '%' . trim($_GET['search']) . '%';
        }

        if (!empty($_GET['status']) && in_array($_GET['status'], $this->allowedStatus)) {
            $where .= " AND o.status = :status";
            $params[':status'] = $_GET['status'];
        }

        $stmt = $this->conn->prepare("
            SELECT o.*, u.email AS user_email
            FROM `order` o
            LEFT JOIN user u ON u.id = o.user_id
            WHERE $where
            ORDER BY o.created_at DESC
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $orders = array_map(fn($r) => $this->_formatOrder($r), $rows);

        SessionHelper::jsonResponse([
            'success' => true,
            'total'   => count($orders),
            'data'    => $orders,
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // GET /api/order/{id}  →  Chi tiết đơn hàng (kèm order_item)
    // ══════════════════════════════════════════════════════════
    public function show($id) {
        SessionHelper::requireLoginApi();

        $order = $this->_findOrder($id);
        if (!$order) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => "Không tìm thấy đơn hàng có ID = $id",
            ], 404);
        }

        $this->_authorizeOrderAccess($order);

        $iStmt = $this->conn->prepare("SELECT * FROM order_item WHERE order_id = :oid");
        $iStmt->execute([':oid' => (int)$id]);
        $items = $iStmt->fetchAll(PDO::FETCH_ASSOC);

        $data         = $this->_formatOrder($order);
        $data['items'] = array_map(fn($i) => [
            'product_id' => (int)$i['product_id'],
            'name'       => $i['name'],
            'price'      => (float)$i['price'],
            'qty'        => (int)$i['qty'],
            'image'      => $i['image'],
            'line_total' => (float)$i['price'] * (int)$i['qty'],
        ], $items);

        SessionHelper::jsonResponse([
            'success' => true,
            'data'    => $data,
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // PUT /api/order/{id}/cancel  →  Hủy đơn hàng
    // User chỉ hủy được đơn của mình; Admin hủy được mọi đơn.
    // Chỉ hủy được khi trạng thái còn 'pending' hoặc 'processing'.
    // ══════════════════════════════════════════════════════════
    public function cancel($id) {
        SessionHelper::requireLoginApi();

        $order = $this->_findOrder($id);
        if (!$order) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => "Không tìm thấy đơn hàng có ID = $id",
            ], 404);
        }

        $this->_authorizeOrderAccess($order);

        if (!in_array($order['status'], ['pending', 'processing'])) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => "Không thể hủy đơn hàng ở trạng thái '{$order['status']}'",
            ], 409);
        }

        $this->conn->prepare("UPDATE `order` SET status = 'cancelled' WHERE id = :id")
            ->execute([':id' => (int)$id]);

        SessionHelper::jsonResponse([
            'success' => true,
            'message' => "Đã hủy đơn hàng ID $id",
            'data'    => ['order_id' => (int)$id, 'status' => 'cancelled'],
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // PUT /api/order/{id}/status  →  Cập nhật trạng thái đơn hàng [Admin]
    // Body: { "status": "processing|shipped|delivered|cancelled|pending" }
    // ══════════════════════════════════════════════════════════
    public function updateStatus($id) {
        SessionHelper::requireLoginApi();

        if (SessionHelper::currentUserRole() !== 'admin') {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => 'Không có quyền truy cập. Yêu cầu quyền Admin.',
            ], 403);
        }

        $order = $this->_findOrder($id);
        if (!$order) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => "Không tìm thấy đơn hàng có ID = $id",
            ], 404);
        }

        $data   = SessionHelper::getJsonInput();
        $status = $data['status'] ?? '';

        if (!in_array($status, $this->allowedStatus)) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => 'Trạng thái không hợp lệ',
                'errors'  => ['status' => 'status phải thuộc: ' . implode(', ', $this->allowedStatus)],
            ], 422);
        }

        $this->conn->prepare("UPDATE `order` SET status = :s WHERE id = :id")
            ->execute([':s' => $status, ':id' => (int)$id]);

        SessionHelper::jsonResponse([
            'success' => true,
            'message' => "Đã cập nhật trạng thái đơn hàng ID $id thành '$status'",
            'data'    => ['order_id' => (int)$id, 'status' => $status],
        ]);
    }

    // ── Private helpers ─────────────────────────────────────
    private function _findOrder($id) {
        $stmt = $this->conn->prepare("SELECT * FROM `order` WHERE id = :id");
        $stmt->execute([':id' => (int)$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function _authorizeOrderAccess(array $order): void {
        $isAdmin = SessionHelper::currentUserRole() === 'admin';
        if (!$isAdmin && (int)$order['user_id'] !== (int)SessionHelper::currentUserId()) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập đơn hàng này',
            ], 403);
        }
    }

    private function _formatOrder(array $r): array {
        return [
            'id'              => (int)$r['id'],
            'user_id'         => $r['user_id'] !== null ? (int)$r['user_id'] : null,
            'fullname'        => $r['fullname'],
            'phone'           => $r['phone'],
            'address'         => $r['address'],
            'city'            => $r['city'],
            'note'            => $r['note'],
            'shipping_method' => $r['shipping_method'],
            'payment_method'  => $r['payment_method'],
            'discount'        => (float)$r['discount'],
            'total'           => (float)$r['total'],
            'total_display'   => number_format((float)$r['total'], 0, ',', '.') . ' VND',
            'status'          => $r['status'],
            'created_at'      => $r['created_at'],
        ];
    }
}
