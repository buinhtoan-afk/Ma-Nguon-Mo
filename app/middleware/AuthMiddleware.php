<?php
/**
 * AuthMiddleware - Phân quyền truy cập
 * Hỗ trợ: Remember Me cookie auto-login
 */
class AuthMiddleware {

    public static function isLoggedIn(): bool {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['user_id'])) return true;
        // Kiểm tra Remember Me cookie
        if (!empty($_COOKIE['remember_me'])) {
            return self::_tryAutoLogin();
        }
        return false;
    }

    public static function isAdmin(): bool {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . $base . '/Auth/login');
            exit();
        }
    }

    public static function requireAdmin(): void {
        self::requireLogin();
        if (!self::isAdmin()) {
            http_response_code(403);
            include 'app/views/errors/403.php';
            exit();
        }
    }

    private static function _tryAutoLogin(): bool {
        try {
            require_once 'app/config/database.php';
            $db   = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare(
                "SELECT * FROM user WHERE remember_token=:t AND is_locked=0 LIMIT 1");
            $stmt->execute([':t' => $_COOKIE['remember_me']]);
            $row = $stmt->fetch();
            if ($row) {
                $_SESSION['user_id']    = $row['id'];
                $_SESSION['user_name']  = $row['fullname'];
                $_SESSION['user_email'] = $row['email'];
                $_SESSION['user_role']  = $row['role'];
                $_SESSION['user_avatar']= $row['avatar'];
                return true;
            }
        } catch (Exception $e) {}
        return false;
    }
}
