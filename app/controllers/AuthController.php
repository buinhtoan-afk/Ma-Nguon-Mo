<?php
require_once 'app/models/UserModel.php';
require_once 'app/config/database.php';
require_once 'app/middleware/AuthMiddleware.php';

class AuthController {
    private $conn;

    private const VALID_EMPLOYEE_CODES = ['ADMIN2024','NV001','NV002','MANAGER01'];

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $db = new Database();
        $this->conn = $db->getConnection();
        $this->_ensureTables();
    }

    // ── Tạo / migrate bảng ──────────────────────────────────────────────
    private function _ensureTables() {
        $this->conn->exec("CREATE TABLE IF NOT EXISTS `user` (
            id           INT AUTO_INCREMENT PRIMARY KEY,
            fullname     VARCHAR(100) NOT NULL,
            email        VARCHAR(150) NOT NULL UNIQUE,
            phone        VARCHAR(20)  DEFAULT '',
            password     VARCHAR(255) NOT NULL,
            role         ENUM('admin','user') DEFAULT 'user',
            avatar       VARCHAR(255) DEFAULT NULL,
            is_locked    TINYINT(1)   DEFAULT 0,
            is_verified  TINYINT(1)   DEFAULT 0,
            verify_token VARCHAR(64)  DEFAULT NULL,
            remember_token VARCHAR(64) DEFAULT NULL,
            reset_token  VARCHAR(64)  DEFAULT NULL,
            reset_expires DATETIME   DEFAULT NULL,
            created_at   DATETIME    DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Thêm các cột mới nếu chưa có (tương thích DB cũ)
        $migrateCols = [
            'avatar'         => "VARCHAR(255) DEFAULT NULL",
            'is_locked'      => "TINYINT(1) DEFAULT 0",
            'is_verified'    => "TINYINT(1) DEFAULT 0",
            'verify_token'   => "VARCHAR(64) DEFAULT NULL",
            'remember_token' => "VARCHAR(64) DEFAULT NULL",
            'reset_token'    => "VARCHAR(64) DEFAULT NULL",
            'reset_expires'  => "DATETIME DEFAULT NULL",
        ];
        foreach ($migrateCols as $col => $def) {
            $r = $this->conn->query("SHOW COLUMNS FROM `user` LIKE '$col'")->fetchAll();
            if (empty($r)) {
                $this->conn->exec("ALTER TABLE `user` ADD COLUMN `$col` $def");
            }
        }

        // Admin mặc định
        $chk = $this->conn->query("SELECT COUNT(*) FROM user WHERE role='admin'")->fetchColumn();
        if ($chk == 0) {
            $hash = password_hash('admin123', PASSWORD_BCRYPT);
            $this->conn->exec("INSERT IGNORE INTO user
                (fullname,email,phone,password,role,is_verified)
                VALUES ('Administrator','admin@store.com','',$this->conn->quote($hash),'admin',1)");
        }
    }

    public function index() { $this->login(); }

    // ═══════════════════════════════════════════════════════════
    // ĐĂNG NHẬP
    // ═══════════════════════════════════════════════════════════
    public function login() {
        if (AuthMiddleware::isLoggedIn()) {
            header('Location: ' . $this->_base() . '/Product/list'); exit();
        }
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email']    ?? '');
            $password = trim($_POST['password'] ?? '');
            $remember = !empty($_POST['remember']);

            if (empty($email))    $errors[] = 'Vui lòng nhập email.';
            if (empty($password)) $errors[] = 'Vui lòng nhập mật khẩu.';

            if (empty($errors)) {
                $stmt = $this->conn->prepare("SELECT * FROM user WHERE email=:e LIMIT 1");
                $stmt->execute([':e' => $email]);
                $row = $stmt->fetch();

                if ($row && password_verify($password, $row['password'])) {
                    if ($row['is_locked']) {
                        $errors[] = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.';
                    } else {
                        $_SESSION['user_id']    = $row['id'];
                        $_SESSION['user_name']  = $row['fullname'];
                        $_SESSION['user_email'] = $row['email'];
                        $_SESSION['user_role']  = $row['role'];
                        $_SESSION['user_avatar']= $row['avatar'];

                        // Remember Me
                        if ($remember) {
                            $token = bin2hex(random_bytes(32));
                            $this->conn->prepare("UPDATE user SET remember_token=:t WHERE id=:id")
                                ->execute([':t'=>$token,':id'=>$row['id']]);
                            setcookie('remember_me', $token, time()+60*60*24*30, '/', '', false, true);
                        }

                        $redirect = $_SESSION['redirect_after_login'] ?? null;
                        unset($_SESSION['redirect_after_login']);
                        header('Location: '.($redirect ?: $this->_base().'/Product/list')); exit();
                    }
                } else {
                    $errors[] = 'Email hoặc mật khẩu không đúng.';
                }
            }
        }

        // Auto-login qua cookie
        if (!$_SERVER['REQUEST_METHOD'] === 'POST' && isset($_COOKIE['remember_me'])) {
            $this->_autoLogin();
        }
        include 'app/views/auth/login.php';
    }

    private function _autoLogin() {
        $token = $_COOKIE['remember_me'] ?? '';
        if (!$token) return;
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE remember_token=:t AND is_locked=0 LIMIT 1");
        $stmt->execute([':t'=>$token]);
        $row = $stmt->fetch();
        if ($row) {
            $_SESSION['user_id']    = $row['id'];
            $_SESSION['user_name']  = $row['fullname'];
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['user_role']  = $row['role'];
            $_SESSION['user_avatar']= $row['avatar'];
            header('Location: '.$this->_base().'/Product/list'); exit();
        }
    }

    // ═══════════════════════════════════════════════════════════
    // ĐĂNG KÝ
    // ═══════════════════════════════════════════════════════════
    public function register() {
        if (AuthMiddleware::isLoggedIn()) {
            header('Location: '.$this->_base().'/Product/list'); exit();
        }
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullname      = trim($_POST['fullname']      ?? '');
            $email         = trim($_POST['email']         ?? '');
            $phone         = trim($_POST['phone']         ?? '');
            $password      = trim($_POST['password']      ?? '');
            $confirm       = trim($_POST['confirm']       ?? '');
            $role          = trim($_POST['role']          ?? 'user');
            $employee_code = trim($_POST['employee_code'] ?? '');

            if (!in_array($role, ['admin','user'])) $role = 'user';
            if (strlen($fullname) < 3)  $errors[] = 'Họ tên phải ít nhất 3 ký tự.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
            if (strlen($password) < 6)  $errors[] = 'Mật khẩu phải ít nhất 6 ký tự.';
            if ($password !== $confirm)  $errors[] = 'Mật khẩu xác nhận không khớp.';
            if ($role === 'admin') {
                if (empty($employee_code)) $errors[] = 'Vui lòng nhập mã nhân viên.';
                elseif (!in_array(strtoupper($employee_code), array_map('strtoupper', self::VALID_EMPLOYEE_CODES)))
                    $errors[] = 'Mã nhân viên không hợp lệ.';
            }
            if (empty($errors)) {
                $chk = $this->conn->prepare("SELECT id FROM user WHERE email=:e");
                $chk->execute([':e'=>$email]);
                if ($chk->fetch()) {
                    $errors[] = 'Email đã được sử dụng.';
                } else {
                    $hash  = password_hash($password, PASSWORD_BCRYPT);
                    $token = bin2hex(random_bytes(32));
                    $ins   = $this->conn->prepare(
                        "INSERT INTO user (fullname,email,phone,password,role,is_verified,verify_token)
                         VALUES (:f,:e,:p,:h,:r,0,:t)"
                    );
                    $ins->execute([':f'=>$fullname,':e'=>$email,':p'=>$phone,
                                   ':h'=>$hash,':r'=>$role,':t'=>$token]);
                    // Gửi email xác thực (simulation)
                    $this->_sendVerifyEmail($email, $fullname, $token);
                    $_SESSION['register_success'] = 'Đăng ký thành công! Vui lòng kiểm tra email để xác thực tài khoản.';
                    header('Location: '.$this->_base().'/Auth/login'); exit();
                }
            }
        }
        include 'app/views/auth/register.php';
    }

    // ═══════════════════════════════════════════════════════════
    // XÁC THỰC EMAIL
    // ═══════════════════════════════════════════════════════════
    public function verify($token = '') {
        if (!$token) { header('Location:'.$this->_base().'/Auth/login'); exit(); }
        $stmt = $this->conn->prepare("SELECT id FROM user WHERE verify_token=:t AND is_verified=0 LIMIT 1");
        $stmt->execute([':t'=>$token]);
        $row = $stmt->fetch();
        if ($row) {
            $this->conn->prepare("UPDATE user SET is_verified=1, verify_token=NULL WHERE id=:id")
                ->execute([':id'=>$row['id']]);
            $_SESSION['register_success'] = '✅ Email đã được xác thực! Bạn có thể đăng nhập.';
        } else {
            $_SESSION['register_success'] = '❌ Liên kết xác thực không hợp lệ hoặc đã dùng.';
        }
        header('Location:'.$this->_base().'/Auth/login'); exit();
    }

    private function _sendVerifyEmail($email, $name, $token) {
        $base = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];
        $base .= $this->_base();
        $link = "$base/Auth/verify/$token";
        // Lưu token vào file log (thay mail() để demo không cần SMTP)
        $logDir = 'public/uploads/mail_log';
        if (!is_dir($logDir)) mkdir($logDir, 0755, true);
        file_put_contents("$logDir/verify_{$token}.txt",
            "To: $email\nName: $name\nVerify Link: $link\nTime: ".date('Y-m-d H:i:s'));
        // Nếu có cấu hình SMTP, bật dòng dưới:
        // mail($email, 'Xác thực tài khoản HUY TOAN STORE', "Xin chào $name,\n\nNhấn link để xác thực:\n$link", "From: noreply@huytoanstore.com");
    }

    // ═══════════════════════════════════════════════════════════
    // QUÊN MẬT KHẨU
    // ═══════════════════════════════════════════════════════════
    public function forgotPassword() {
        $sent = false; $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email không hợp lệ.';
            } else {
                $stmt = $this->conn->prepare("SELECT id,fullname FROM user WHERE email=:e LIMIT 1");
                $stmt->execute([':e'=>$email]);
                $row = $stmt->fetch();
                if ($row) {
                    $token   = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    $this->conn->prepare("UPDATE user SET reset_token=:t, reset_expires=:ex WHERE id=:id")
                        ->execute([':t'=>$token,':ex'=>$expires,':id'=>$row['id']]);
                    $this->_sendResetEmail($email, $row['fullname'], $token);
                }
                // Luôn hiện thông báo (bảo mật, không leak email)
                $sent = true;
            }
        }
        include 'app/views/auth/forgot_password.php';
    }

    // ═══════════════════════════════════════════════════════════
    // ĐẶT LẠI MẬT KHẨU
    // ═══════════════════════════════════════════════════════════
    public function resetPassword($token = '') {
        if (!$token) { header('Location:'.$this->_base().'/Auth/forgotPassword'); exit(); }
        $stmt = $this->conn->prepare(
            "SELECT id FROM user WHERE reset_token=:t AND reset_expires > NOW() LIMIT 1");
        $stmt->execute([':t'=>$token]);
        $row = $stmt->fetch();
        if (!$row) {
            $_SESSION['flash_error'] = 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.';
            header('Location:'.$this->_base().'/Auth/forgotPassword'); exit();
        }
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = trim($_POST['password'] ?? '');
            $confirm  = trim($_POST['confirm']  ?? '');
            if (strlen($password) < 6) $errors[] = 'Mật khẩu phải ít nhất 6 ký tự.';
            if ($password !== $confirm) $errors[] = 'Mật khẩu xác nhận không khớp.';
            if (empty($errors)) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $this->conn->prepare(
                    "UPDATE user SET password=:h, reset_token=NULL, reset_expires=NULL WHERE id=:id")
                    ->execute([':h'=>$hash,':id'=>$row['id']]);
                $_SESSION['register_success'] = '✅ Mật khẩu đã được đặt lại. Vui lòng đăng nhập.';
                header('Location:'.$this->_base().'/Auth/login'); exit();
            }
        }
        include 'app/views/auth/reset_password.php';
    }

    private function _sendResetEmail($email, $name, $token) {
        $base = (isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].$this->_base();
        $link = "$base/Auth/resetPassword/$token";
        $logDir = 'public/uploads/mail_log';
        if (!is_dir($logDir)) mkdir($logDir, 0755, true);
        file_put_contents("$logDir/reset_{$token}.txt",
            "To: $email\nName: $name\nReset Link: $link\nExpires: +1 hour\nTime: ".date('Y-m-d H:i:s'));
    }

    // ═══════════════════════════════════════════════════════════
    // HỒ SƠ CÁ NHÂN
    // ═══════════════════════════════════════════════════════════
    public function profile() {
        AuthMiddleware::requireLogin();
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE id=:id");
        $stmt->execute([':id'=>$_SESSION['user_id']]);
        $row = $stmt->fetch();

        $user = new UserModel(
            $row['id'],$row['fullname'],$row['email'],
            $row['phone'],$row['password'],$row['created_at']
        );
        $userExtra = $row; // avatar, role, is_verified, is_locked

        // Đảm bảo bảng order tồn tại
        $this->conn->exec("CREATE TABLE IF NOT EXISTS `order` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT, fullname VARCHAR(100), phone VARCHAR(20),
            address TEXT, city VARCHAR(100), note TEXT,
            shipping_method VARCHAR(20) DEFAULT 'standard',
            payment_method  VARCHAR(20) DEFAULT 'cod',
            total DECIMAL(15,2), status VARCHAR(20) DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $oStmt = $this->conn->prepare(
            "SELECT * FROM `order` WHERE user_id=:uid ORDER BY created_at DESC");
        $oStmt->execute([':uid'=>$_SESSION['user_id']]);
        $orders = $oStmt->fetchAll();

        $success = $_SESSION['profile_success'] ?? null;
        unset($_SESSION['profile_success']);
        include 'app/views/auth/profile.php';
    }

    // ═══════════════════════════════════════════════════════════
    // CẬP NHẬT HỒ SƠ
    // ═══════════════════════════════════════════════════════════
    public function updateProfile() {
        AuthMiddleware::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location:'.$this->_base().'/Auth/profile'); exit();
        }
        $fullname = trim($_POST['fullname'] ?? '');
        $phone    = trim($_POST['phone']    ?? '');
        $errors   = [];
        if (strlen($fullname) < 3) $errors[] = 'Họ tên phải ít nhất 3 ký tự.';

        // Upload avatar
        $avatarFile = null;
        if (!empty($_FILES['avatar']['name'])) {
            $ext  = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (!in_array($ext, $allowed)) {
                $errors[] = 'Ảnh đại diện chỉ cho phép: jpg, jpeg, png, gif, webp.';
            } elseif ($_FILES['avatar']['size'] > 2*1024*1024) {
                $errors[] = 'Ảnh đại diện tối đa 2MB.';
            } else {
                $dir = 'public/uploads/avatars/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $filename = time().'_'.$_SESSION['user_id'].'.'.$ext;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dir.$filename)) {
                    $avatarFile = $filename;
                } else {
                    $errors[] = 'Không thể tải ảnh lên. Thử lại.';
                }
            }
        }

        if (empty($errors)) {
            if ($avatarFile) {
                // Xóa avatar cũ
                $old = $this->conn->query("SELECT avatar FROM user WHERE id=".$_SESSION['user_id'])->fetchColumn();
                if ($old && file_exists('public/uploads/avatars/'.$old)) {
                    unlink('public/uploads/avatars/'.$old);
                }
                $this->conn->prepare(
                    "UPDATE user SET fullname=:f, phone=:p, avatar=:a WHERE id=:id")
                    ->execute([':f'=>$fullname,':p'=>$phone,':a'=>$avatarFile,':id'=>$_SESSION['user_id']]);
                $_SESSION['user_avatar'] = $avatarFile;
            } else {
                $this->conn->prepare(
                    "UPDATE user SET fullname=:f, phone=:p WHERE id=:id")
                    ->execute([':f'=>$fullname,':p'=>$phone,':id'=>$_SESSION['user_id']]);
            }
            $_SESSION['user_name'] = $fullname;
            $_SESSION['profile_success'] = 'Cập nhật thông tin thành công!';
            header('Location:'.$this->_base().'/Auth/profile'); exit();
        }
        // Nếu có lỗi → reload profile với lỗi
        $_SESSION['profile_errors'] = $errors;
        header('Location:'.$this->_base().'/Auth/profile'); exit();
    }

    // ═══════════════════════════════════════════════════════════
    // ĐỔI MẬT KHẨU
    // ═══════════════════════════════════════════════════════════
    public function changePassword() {
        AuthMiddleware::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location:'.$this->_base().'/Auth/profile'); exit();
        }
        $old     = trim($_POST['old_password'] ?? '');
        $new     = trim($_POST['new_password'] ?? '');
        $confirm = trim($_POST['confirm_password'] ?? '');
        $errors  = [];

        $row = $this->conn->query(
            "SELECT password FROM user WHERE id=".$_SESSION['user_id'])->fetch();
        if (!password_verify($old, $row['password'])) $errors[] = 'Mật khẩu hiện tại không đúng.';
        if (strlen($new) < 6)  $errors[] = 'Mật khẩu mới phải ít nhất 6 ký tự.';
        if ($new !== $confirm)  $errors[] = 'Mật khẩu xác nhận không khớp.';

        if (empty($errors)) {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $this->conn->prepare("UPDATE user SET password=:h WHERE id=:id")
                ->execute([':h'=>$hash,':id'=>$_SESSION['user_id']]);
            $_SESSION['profile_success'] = '✅ Đổi mật khẩu thành công!';
        } else {
            $_SESSION['profile_errors'] = $errors;
        }
        header('Location:'.$this->_base().'/Auth/profile'); exit();
    }

    // ═══════════════════════════════════════════════════════════
    // ĐĂNG XUẤT
    // ═══════════════════════════════════════════════════════════
    public function logout() {
        // Xóa remember token
        if (isset($_SESSION['user_id'])) {
            $this->conn->prepare("UPDATE user SET remember_token=NULL WHERE id=:id")
                ->execute([':id'=>$_SESSION['user_id']]);
        }
        setcookie('remember_me','', time()-3600, '/');
        session_destroy();
        header('Location:'.$this->_base().'/Product/list'); exit();
    }

    // ═══════════════════════════════════════════════════════════
    // QUẢN LÝ NGƯỜI DÙNG (ADMIN)
    // ═══════════════════════════════════════════════════════════
    public function manageUsers() {
        AuthMiddleware::requireAdmin();
        $search  = trim($_GET['search'] ?? '');
        $filter  = $_GET['filter'] ?? 'all'; // all|admin|user|locked
        $query   = "SELECT * FROM user WHERE 1=1";
        $params  = [];
        if ($search) {
            $query .= " AND (fullname LIKE :s OR email LIKE :s2)";
            $params[':s'] = $params[':s2'] = "%$search%";
        }
        if ($filter === 'admin')  { $query .= " AND role='admin'"; }
        if ($filter === 'user')   { $query .= " AND role='user'"; }
        if ($filter === 'locked') { $query .= " AND is_locked=1"; }
        $query .= " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        $success = $_SESSION['admin_success'] ?? null;
        unset($_SESSION['admin_success']);
        include 'app/views/admin/users.php';
    }

    // ═══════════════════════════════════════════════════════════
    // KHÓA / MỞ KHÓA TÀI KHOẢN (ADMIN)
    // ═══════════════════════════════════════════════════════════
    public function toggleLock($userId = 0) {
        AuthMiddleware::requireAdmin();
        $userId = (int)$userId;
        if ($userId === (int)$_SESSION['user_id']) {
            $_SESSION['admin_success'] = '⚠️ Không thể khóa chính tài khoản của bạn.';
            header('Location:'.$this->_base().'/Auth/manageUsers'); exit();
        }
        $row = $this->conn->query(
            "SELECT is_locked,fullname FROM user WHERE id=$userId")->fetch();
        if ($row) {
            $newLock = $row['is_locked'] ? 0 : 1;
            $this->conn->prepare("UPDATE user SET is_locked=:l WHERE id=:id")
                ->execute([':l'=>$newLock,':id'=>$userId]);
            $label = $newLock ? 'khóa' : 'mở khóa';
            $_SESSION['admin_success'] = "✅ Đã $label tài khoản: ".htmlspecialchars($row['fullname']);
        }
        header('Location:'.$this->_base().'/Auth/manageUsers'); exit();
    }

    // ═══════════════════════════════════════════════════════════
    // ĐỔI ROLE (ADMIN)
    // ═══════════════════════════════════════════════════════════
    public function changeRole($userId = 0) {
        AuthMiddleware::requireAdmin();
        $userId = (int)$userId;
        if ($userId === (int)$_SESSION['user_id']) {
            $_SESSION['admin_success'] = '⚠️ Không thể đổi role của chính bạn.';
            header('Location:'.$this->_base().'/Auth/manageUsers'); exit();
        }
        $row = $this->conn->query(
            "SELECT role,fullname FROM user WHERE id=$userId")->fetch();
        if ($row) {
            $newRole = $row['role'] === 'admin' ? 'user' : 'admin';
            $this->conn->prepare("UPDATE user SET role=:r WHERE id=:id")
                ->execute([':r'=>$newRole,':id'=>$userId]);
            $_SESSION['admin_success'] = "✅ Đã đổi role thành [$newRole] cho: ".htmlspecialchars($row['fullname']);
        }
        header('Location:'.$this->_base().'/Auth/manageUsers'); exit();
    }

    // ═══════════════════════════════════════════════════════════
    // XÓA NGƯỜI DÙNG (ADMIN)
    // ═══════════════════════════════════════════════════════════
    public function deleteUser($userId = 0) {
        AuthMiddleware::requireAdmin();
        $userId = (int)$userId;
        if ($userId === (int)$_SESSION['user_id']) {
            $_SESSION['admin_success'] = '⚠️ Không thể xóa chính tài khoản của bạn.';
            header('Location:'.$this->_base().'/Auth/manageUsers'); exit();
        }
        $row = $this->conn->query(
            "SELECT fullname,avatar FROM user WHERE id=$userId")->fetch();
        if ($row) {
            if ($row['avatar'] && file_exists('public/uploads/avatars/'.$row['avatar'])) {
                unlink('public/uploads/avatars/'.$row['avatar']);
            }
            $this->conn->prepare("DELETE FROM user WHERE id=:id")->execute([':id'=>$userId]);
            $_SESSION['admin_success'] = '🗑️ Đã xóa tài khoản: '.htmlspecialchars($row['fullname']);
        }
        header('Location:'.$this->_base().'/Auth/manageUsers'); exit();
    }

    private function _base() {
        return rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    }
}
