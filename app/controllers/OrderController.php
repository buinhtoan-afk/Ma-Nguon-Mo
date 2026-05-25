<?php
require_once 'app/models/OrderModel.php';
require_once 'app/config/database.php';

class OrderController {
    private $conn;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $db = new Database();
        $this->conn = $db->getConnection();
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

    public function index() { $this->checkout(); }

    // Trang thanh toán
    public function checkout() {
        if (empty($_SESSION['cart'])) {
            header('Location: ' . $this->_base() . '/Cart/view');
            exit();
        }

        $cart     = $_SESSION['cart'];
        $ids      = implode(',', array_map('intval', array_keys($cart)));
        $rows     = $this->conn->query("SELECT * FROM product WHERE id IN ($ids)")->fetchAll();
        $cartItems = [];
        $subtotal  = 0;
        foreach ($rows as $row) {
            $qty = $cart[$row['id']] ?? 1;
            $row['qty'] = $qty;
            $cartItems[] = $row;
            $subtotal += $row['price'] * $qty;
        }

        $discount = $_SESSION['discount_amount'] ?? 0;
        $voucher  = $_SESSION['voucher_code']    ?? '';

        $cities = ['Hà Nội','TP. Hồ Chí Minh','Đà Nẵng','Hải Phòng','Cần Thơ',
                   'Bình Dương','Đồng Nai','An Giang','Khánh Hòa','Quảng Ninh'];

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullname       = trim($_POST['fullname']        ?? '');
            $phone          = trim($_POST['phone']           ?? '');
            $address        = trim($_POST['address']         ?? '');
            $city           = trim($_POST['city']            ?? '');
            $note           = trim($_POST['note']            ?? '');
            $shipping       = $_POST['shipping_method']      ?? 'standard';
            $payment        = $_POST['payment_method']       ?? 'cod';

            if (empty($fullname)) $errors[] = 'Vui lòng nhập họ tên.';
            if (!preg_match('/^[0-9]{9,11}$/', $phone)) $errors[] = 'Số điện thoại không hợp lệ.';
            if (empty($address))  $errors[] = 'Vui lòng nhập địa chỉ.';
            if (empty($city))     $errors[] = 'Vui lòng chọn thành phố.';

            $shipping_fee = ($shipping === 'express') ? 150000 : 0;
            $total        = $subtotal - $discount + $shipping_fee;

            if (empty($errors)) {
                // Lưu đơn hàng
                $stmt = $this->conn->prepare("
                    INSERT INTO `order` (user_id, fullname, phone, address, city, note,
                                        shipping_method, payment_method, discount, total)
                    VALUES (:uid,:fn,:ph,:addr,:city,:note,:ship,:pay,:disc,:total)
                ");
                $stmt->execute([
                    ':uid'   => $_SESSION['user_id'] ?? null,
                    ':fn'    => $fullname, ':ph'   => $phone,
                    ':addr'  => $address,  ':city' => $city,
                    ':note'  => $note,
                    ':ship'  => $shipping, ':pay'  => $payment,
                    ':disc'  => $discount, ':total'=> $total,
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

                // Xóa giỏ + voucher
                $_SESSION['cart']            = [];
                $_SESSION['discount_amount'] = 0;
                $_SESSION['voucher_code']    = '';

                header('Location: ' . $this->_base() . '/Order/success/' . $orderId);
                exit();
            }
        }

        include 'app/views/order/checkout.php';
    }

    // Trang thành công
    public function success($orderId) {
        $stmt = $this->conn->prepare("SELECT * FROM `order` WHERE id = :id");
        $stmt->execute([':id' => $orderId]);
        $orderRow = $stmt->fetch();
        if (!$orderRow) die('Đơn hàng không tồn tại.');

        $order = new OrderModel($orderRow);

        $iStmt = $this->conn->prepare("SELECT * FROM order_item WHERE order_id = :oid");
        $iStmt->execute([':oid' => $orderId]);
        $items = $iStmt->fetchAll();

        include 'app/views/order/success.php';
    }

    // Apply voucher (AJAX)
    public function applyVoucher() {
        header('Content-Type: application/json');
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $subtotal = floatval($_POST['subtotal'] ?? 0);

        // Danh sách voucher demo
        $vouchers = [
            'GIAM10'  => ['type' => 'percent', 'value' => 10,       'min' => 0],
            'SALE50K' => ['type' => 'fixed',   'value' => 50000,    'min' => 500000],
            'VIP100K' => ['type' => 'fixed',   'value' => 100000,   'min' => 1000000],
            'FREESHIP'=> ['type' => 'fixed',   'value' => 150000,   'min' => 0],
        ];

        if (isset($vouchers[$code])) {
            $v = $vouchers[$code];
            if ($subtotal < $v['min']) {
                echo json_encode(['success' => false, 'message' => 'Đơn hàng chưa đạt tối thiểu ' . number_format($v['min']) . '₫']);
            } else {
                $discount = ($v['type'] === 'percent') ? $subtotal * $v['value'] / 100 : $v['value'];
                $_SESSION['discount_amount'] = $discount;
                $_SESSION['voucher_code']    = $code;
                echo json_encode(['success' => true, 'discount' => $discount, 'message' => 'Áp dụng voucher thành công!']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Mã giảm giá không hợp lệ.']);
        }
        exit();
    }

    private function _base() {
        return rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    }
}
