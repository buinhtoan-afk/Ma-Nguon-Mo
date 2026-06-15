<?php
require_once 'app/config/database.php';
require_once 'app/helpers/SessionHelper.php';

/**
 * CartApiController
 * RESTful API cho giỏ hàng (lưu trong session)
 *
 * GET    /api/cart            → index() : Xem giỏ hàng
 * GET    /api/cart/total      → total() : Tính tổng tiền giỏ hàng
 * POST   /api/cart/add        → add()   : Thêm sản phẩm vào giỏ hàng
 * PUT    /api/cart/update     → update(): Cập nhật số lượng sản phẩm
 * DELETE /api/cart/{id}       → remove(): Xóa 1 sản phẩm khỏi giỏ
 * DELETE /api/cart/clear      → clear() : Xóa toàn bộ giỏ hàng
 *
 * Lưu ý: tất cả API giỏ hàng yêu cầu đăng nhập (session).
 */
class CartApiController {

    private $conn;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->conn = (new Database())->getConnection();
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    // ══════════════════════════════════════════════════════════
    // GET /api/cart  →  Xem giỏ hàng
    // ══════════════════════════════════════════════════════════
    public function index() {
        SessionHelper::requireLoginApi();

        $items    = $this->_getCartItems();
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['qty'];
        }

        SessionHelper::jsonResponse([
            'success'   => true,
            'total_qty' => array_sum($_SESSION['cart']),
            'subtotal'  => $subtotal,
            'data'      => $items,
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // GET /api/cart/total  →  Tính tổng tiền giỏ hàng
    // ══════════════════════════════════════════════════════════
    public function total() {
        SessionHelper::requireLoginApi();

        $items    = $this->_getCartItems();
        $subtotal = 0;
        $totalQty = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['qty'];
            $totalQty += $item['qty'];
        }

        SessionHelper::jsonResponse([
            'success' => true,
            'data'    => [
                'total_qty'      => $totalQty,
                'subtotal'       => $subtotal,
                'subtotal_text'  => number_format($subtotal, 0, ',', '.') . ' VND',
            ],
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // POST /api/cart/add  →  Thêm sản phẩm vào giỏ hàng
    // Body: { "product_id": 5, "qty": 2 }
    // ══════════════════════════════════════════════════════════
    public function add() {
        SessionHelper::requireLoginApi();

        $data      = SessionHelper::getJsonInput();
        $productId = (int)($data['product_id'] ?? 0);
        $qty       = (int)($data['qty'] ?? 1);

        if ($productId <= 0) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => 'product_id không hợp lệ',
            ], 422);
        }

        if ($qty <= 0) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => 'Số lượng sản phẩm phải lớn hơn 0',
                'errors'  => ['qty' => 'Số lượng phải lớn hơn 0'],
            ], 422);
        }

        // Kiểm tra sản phẩm có tồn tại
        $stmt = $this->conn->prepare("SELECT id FROM product WHERE id = :id");
        $stmt->execute([':id' => $productId]);
        if (!$stmt->fetch()) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => "Sản phẩm ID = $productId không tồn tại",
            ], 404);
        }

        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $qty;
        } else {
            $_SESSION['cart'][$productId] = $qty;
        }

        SessionHelper::jsonResponse([
            'success'   => true,
            'message'   => 'Đã thêm sản phẩm vào giỏ hàng',
            'total_qty' => array_sum($_SESSION['cart']),
            'data'      => $this->_getCartItems(),
        ], 201);
    }

    // ══════════════════════════════════════════════════════════
    // PUT /api/cart/update  →  Cập nhật số lượng sản phẩm trong giỏ
    // Body: { "product_id": 5, "qty": 3 }
    // (qty <= 0 sẽ xóa sản phẩm khỏi giỏ)
    // ══════════════════════════════════════════════════════════
    public function update() {
        SessionHelper::requireLoginApi();

        $data      = SessionHelper::getJsonInput();
        $productId = (int)($data['product_id'] ?? 0);
        $qty       = (int)($data['qty'] ?? 0);

        if ($productId <= 0 || !isset($_SESSION['cart'][$productId])) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => "Sản phẩm ID = $productId không có trong giỏ hàng",
            ], 404);
        }

        if ($qty <= 0) {
            unset($_SESSION['cart'][$productId]);
            SessionHelper::jsonResponse([
                'success'   => true,
                'message'   => 'Đã xóa sản phẩm khỏi giỏ hàng (số lượng <= 0)',
                'total_qty' => array_sum($_SESSION['cart']),
                'data'      => $this->_getCartItems(),
            ]);
        }

        $_SESSION['cart'][$productId] = $qty;

        SessionHelper::jsonResponse([
            'success'   => true,
            'message'   => 'Cập nhật số lượng thành công',
            'total_qty' => array_sum($_SESSION['cart']),
            'data'      => $this->_getCartItems(),
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // DELETE /api/cart/{id}  →  Xóa 1 sản phẩm khỏi giỏ hàng
    // ══════════════════════════════════════════════════════════
    public function remove($id) {
        SessionHelper::requireLoginApi();

        $productId = (int)$id;
        if (!isset($_SESSION['cart'][$productId])) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => "Sản phẩm ID = $productId không có trong giỏ hàng",
            ], 404);
        }

        unset($_SESSION['cart'][$productId]);

        SessionHelper::jsonResponse([
            'success'   => true,
            'message'   => "Đã xóa sản phẩm ID $productId khỏi giỏ hàng",
            'total_qty' => array_sum($_SESSION['cart']),
            'data'      => $this->_getCartItems(),
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // DELETE /api/cart/clear  →  Xóa toàn bộ giỏ hàng
    // ══════════════════════════════════════════════════════════
    public function clear() {
        SessionHelper::requireLoginApi();

        $_SESSION['cart'] = [];

        SessionHelper::jsonResponse([
            'success' => true,
            'message' => 'Đã xóa toàn bộ giỏ hàng',
            'data'    => [],
        ]);
    }

    // ── Private helpers ─────────────────────────────────────
    private function _getCartItems(): array {
        $cart = $_SESSION['cart'];
        if (empty($cart)) return [];

        $ids  = implode(',', array_map('intval', array_keys($cart)));
        $rows = $this->conn->query(
            "SELECT p.id, p.name, p.price, p.image, p.category_id, c.name AS category_name
             FROM product p
             LEFT JOIN category c ON p.category_id = c.id
             WHERE p.id IN ($ids)"
        )->fetchAll(PDO::FETCH_ASSOC);

        $base   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $result = [];
        foreach ($rows as $row) {
            $qty = $cart[$row['id']] ?? 1;
            $result[] = [
                'product_id'    => (int)$row['id'],
                'name'          => $row['name'],
                'price'         => (float)$row['price'],
                'price_display' => number_format((float)$row['price'], 0, ',', '.') . ' VND',
                'image'         => $row['image'],
                'image_url'     => $base . '/public/images/' . ($row['image'] ?: 'default.jpg'),
                'category_id'   => $row['category_id'] ? (int)$row['category_id'] : null,
                'category_name' => $row['category_name'] ?? null,
                'qty'           => (int)$qty,
                'line_total'    => (float)$row['price'] * (int)$qty,
            ];
        }
        return $result;
    }
}
