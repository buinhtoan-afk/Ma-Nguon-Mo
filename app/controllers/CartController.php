<?php
require_once 'app/config/database.php';

class CartController {
    private $conn;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $db = new Database();
        $this->conn = $db->getConnection();
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    }

    public function index() { $this->view(); }

    // Xem giỏ hàng
    public function view() {
        $cart      = $_SESSION['cart'];
        $products  = $this->_getCartProducts($cart);
        $subtotal  = 0;
        foreach ($products as $item) $subtotal += $item['price'] * $item['qty'];

        // Lấy sản phẩm gợi ý (random 4 SP)
        $stmt = $this->conn->query("SELECT * FROM product ORDER BY RAND() LIMIT 4");
        $suggestions = $stmt->fetchAll();

        include 'app/views/cart/view.php';
    }

    // Thêm vào giỏ (POST hoặc GET)
    public function add($id = null) {
        $id  = $id ?? intval($_POST['product_id'] ?? 0);
        $qty = intval($_POST['qty'] ?? 1);
        if ($qty < 1) $qty = 1;

        if ($id > 0) {
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id] += $qty;
            } else {
                $_SESSION['cart'][$id] = $qty;
            }
        }

        // AJAX response
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'count'   => array_sum($_SESSION['cart'])
            ]);
            exit();
        }

        header('Location: ' . $this->_base() . '/Cart/view');
        exit();
    }

    // Cập nhật số lượng
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $quantities = $_POST['qty'] ?? [];
            foreach ($quantities as $pid => $qty) {
                $qty = intval($qty);
                if ($qty <= 0) {
                    unset($_SESSION['cart'][$pid]);
                } else {
                    $_SESSION['cart'][$pid] = $qty;
                }
            }
        }
        header('Location: ' . $this->_base() . '/Cart/view');
        exit();
    }

    // Xóa 1 sản phẩm
    public function remove($id) {
        unset($_SESSION['cart'][$id]);
        header('Location: ' . $this->_base() . '/Cart/view');
        exit();
    }

    // Xóa toàn bộ giỏ
    public function clear() {
        $_SESSION['cart'] = [];
        header('Location: ' . $this->_base() . '/Cart/view');
        exit();
    }

    private function _getCartProducts($cart) {
        if (empty($cart)) return [];
        $ids  = implode(',', array_map('intval', array_keys($cart)));
        $rows = $this->conn->query("SELECT * FROM product WHERE id IN ($ids)")->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $row['qty'] = $cart[$row['id']] ?? 1;
            $result[]   = $row;
        }
        return $result;
    }

    public static function getCount() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return array_sum($_SESSION['cart'] ?? []);
    }

    private function _base() {
        return rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    }
}
