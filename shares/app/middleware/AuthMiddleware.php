<?php
/**
 * AuthMiddleware - Phân quyền truy cập
 */
class AuthMiddleware {

    public static function isLoggedIn(): bool {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['user_id']);
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
}
