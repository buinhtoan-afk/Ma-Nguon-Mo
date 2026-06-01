<?php
require_once 'app/models/UserModel.php';
require_once 'app/config/database.php';
require_once 'app/middleware/AuthMiddleware.php';

class AuthController {
    private $conn;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $db = new Database();
        $this->conn = $db->getConnection();
        $this->_ensureTables();
    }

    private function _ensureTables() {
        // Bảng account với role admin/user (theo yêu cầu)
        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS account (
                id       INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(255) NOT NULL UNIQUE,
                fullname VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                role     ENUM('admin','user') DEFAULT 'user'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Bảng user (dùng cho đặt hàng, profile)
        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS user (
                id         INT AUTO_INCREMENT PRIMARY KEY,
                fullname   VARCHAR(100) NOT NULL,
                email      VARCHAR(150) NOT NULL UNIQUE,
                phone      VARCHAR(20)  DEFAULT '',
                password   VARCHAR(255) NOT NULL,
                role       ENUM('admin','user') DEFAULT 'user',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Tạo admin mặc định nếu chưa có
        $chk = $this->conn->query("SELECT COUNT(*) FROM user WHERE role='admin'")->fetchColumn();
        if ($chk == 0) {
            $hash = password_hash('admin123', PASSWORD_BCRYPT);
            $this->conn->exec("
                INSERT IGNORE INTO user (fullname, email, phone, password, role)
                VALUES ('Administrator', 'admin@store.com', '', '$hash', 'admin')
            ");
        }
    }

    public function index() { $this->login(); }

    // ── Đăng nhập ──────────────────────────────────────────────────────────
    public function login() {
        if (AuthMiddleware::isLoggedIn()) {
            header('Location: ' . $this->_base() . '/Product/list');
            exit();
        }
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email']    ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($email))    $errors[] = 'Vui lòng nhập email.';
            if (empty($password)) $errors[] = 'Vui lòng nhập mật khẩu.';

            if (empty($errors)) {
                $stmt = $this->conn->prepare("SELECT * FROM user WHERE email = :email LIMIT 1");
                $stmt->execute([':email' => $email]);
                $row = $stmt->fetch();

                if ($row && password_verify($password, $row['password'])) {
                    $_SESSION['user_id']   = $row['id'];
                    $_SESSION['user_name'] = $row['fullname'];
                    $_SESSION['user_email']= $row['email'];
                    $_SESSION['user_role'] = $row['role']; // 'admin' hoặc 'user'

                    // Redirect về trang đã định trước (nếu có)
                    $redirect = $_SESSION['redirect_after_login'] ?? null;
                    unset($_SESSION['redirect_after_login']);

                    header('Location: ' . ($redirect ?: $this->_base() . '/Product/list'));
                    exit();
                } else {
                    $errors[] = 'Email hoặc mật khẩu không đúng.';
                }
            }
        }
        include 'app/views/auth/login.php';
    }

    // ── Đăng ký ────────────────────────────────────────────────────────────
    public function register() {
        if (AuthMiddleware::isLoggedIn()) {
            header('Location: ' . $this->_base() . '/Product/list');
            exit();
        }
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullname = trim($_POST['fullname'] ?? '');
            $email    = trim($_POST['email']    ?? '');
            $phone    = trim($_POST['phone']    ?? '');
            $password = trim($_POST['password'] ?? '');
            $confirm  = trim($_POST['confirm']  ?? '');

            if (strlen($fullname) < 3)  $errors[] = 'Họ tên phải ít nhất 3 ký tự.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
            if (strlen($password) < 6)  $errors[] = 'Mật khẩu phải ít nhất 6 ký tự.';
            if ($password !== $confirm)  $errors[] = 'Mật khẩu xác nhận không khớp.';

            if (empty($errors)) {
                $chk = $this->conn->prepare("SELECT id FROM user WHERE email = :e");
                $chk->execute([':e' => $email]);
                if ($chk->fetch()) {
                    $errors[] = 'Email đã được sử dụng.';
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $ins  = $this->conn->prepare(
                        "INSERT INTO user (fullname, email, phone, password, role)
                         VALUES (:f,:e,:p,:h,'user')"
                    );
                    $ins->execute([':f' => $fullname, ':e' => $email, ':p' => $phone, ':h' => $hash]);
                    $_SESSION['register_success'] = 'Đăng ký thành công! Vui lòng đăng nhập.';
                    header('Location: ' . $this->_base() . '/Auth/login');
                    exit();
                }
            }
        }
        include 'app/views/auth/register.php';
    }

    // ── Đăng xuất ──────────────────────────────────────────────────────────
    public function logout() {
        session_destroy();
        header('Location: ' . $this->_base() . '/Product/list');
        exit();
    }

    // ── Hồ sơ ──────────────────────────────────────────────────────────────
    public function profile() {
        AuthMiddleware::requireLogin();

        $stmt = $this->conn->prepare("SELECT * FROM user WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $row  = $stmt->fetch();
        $user = new UserModel(
            $row['id'], $row['fullname'], $row['email'],
            $row['phone'], $row['password'], $row['created_at']
        );

        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS `order` (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT, fullname VARCHAR(100), phone VARCHAR(20),
                address TEXT, city VARCHAR(100), note TEXT,
                shipping_method VARCHAR(20) DEFAULT 'standard',
                payment_method  VARCHAR(20) DEFAULT 'cod',
                total DECIMAL(15,2), status VARCHAR(20) DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $oStmt = $this->conn->prepare("SELECT * FROM `order` WHERE user_id = :uid ORDER BY created_at DESC");
        $oStmt->execute([':uid' => $_SESSION['user_id']]);
        $orders = $oStmt->fetchAll();

        include 'app/views/auth/profile.php';
    }

    private function _base() {
        return rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    }
}
