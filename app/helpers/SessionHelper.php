<?php
/**
 * SessionHelper - Tiện ích session & response cho API
 *
 * Phân quyền Admin hỗ trợ 2 cách:
 *  1. Session PHP  (đăng nhập qua trình duyệt)
 *  2. API Key      (dùng Postman / client bên ngoài)
 *     Header: X-API-Key: admin-secret-key-2024
 */
class SessionHelper {

    // ── API Key cho Admin (dùng khi test bằng Postman) ──
    // Thay đổi chuỗi này để bảo mật hơn
    private const ADMIN_API_KEY = 'admin-secret-key-2024';

    /**
     * Trả về JSON response chuẩn
     */
    public static function jsonResponse($data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Lấy body JSON từ request (POST/PUT)
     */
    public static function getJsonInput(): array {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Kiểm tra có phải Admin không.
     * Ưu tiên: API Key header → Session PHP
     */
    public static function isAdmin(): bool {
        // Cách 1: Kiểm tra API Key từ header X-API-Key
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
        if ($apiKey === self::ADMIN_API_KEY) {
            return true;
        }

        // Cách 2: Kiểm tra Authorization: Bearer <key>
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (str_starts_with($auth, 'Bearer ')) {
            $bearer = substr($auth, 7);
            if ($bearer === self::ADMIN_API_KEY) {
                return true;
            }
        }

        // Cách 3: Session PHP (đăng nhập qua trình duyệt)
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Middleware: yêu cầu Admin, trả JSON 403 nếu không đủ quyền
     */
    public static function requireAdminApi(): void {
        if (!self::isAdmin()) {
            self::jsonResponse([
                'success' => false,
                'message' => 'Không có quyền truy cập. Yêu cầu quyền Admin.',
                'hint'    => 'Thêm header: X-API-Key: ' . self::ADMIN_API_KEY,
            ], 403);
        }
    }

    /**
     * Kiểm tra user đã đăng nhập.
     * Hỗ trợ 2 cách:
     *  1. Session PHP (đăng nhập qua trình duyệt)
     *  2. JWT Bearer token (dùng Postman / client bên ngoài)
     *     Header: Authorization: Bearer <token>
     */
    public static function isLoggedIn(): bool {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Cách 1: Session PHP
        if (isset($_SESSION['user_id'])) {
            return true;
        }

        // Cách 2: JWT Bearer token
        $payload = self::getJwtPayload();
        if ($payload && isset($payload['id'])) {
            // Gán vào session tạm để các hàm khác (currentUserId...) dùng được
            $_SESSION['user_id']   = $payload['id'];
            $_SESSION['user_email'] = $payload['email'] ?? '';
            return true;
        }

        return false;
    }

    /**
     * Đọc và verify JWT từ header Authorization: Bearer <token>
     */
    public static function getJwtPayload(): ?array {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($auth, 'Bearer ')) return null;
        $token = substr($auth, 7);

        // Tránh conflict với ADMIN_API_KEY
        if ($token === self::ADMIN_API_KEY) return null;

        require_once 'app/helpers/JwtHelper.php';
        $data = JwtHelper::verify($token);
        return is_array($data) ? $data : null;
    }

    /**
     * Middleware: yêu cầu đăng nhập, trả JSON 401 nếu chưa
     */
    public static function requireLoginApi(): void {
        if (!self::isLoggedIn()) {
            self::jsonResponse([
                'success' => false,
                'message' => 'Bạn cần đăng nhập để sử dụng chức năng này',
                'hint'    => 'Thêm header: Authorization: Bearer <token> (lấy token từ POST /api/login)',
            ], 401);
        }
    }

    /**
     * Lấy user_id hiện tại (session hoặc JWT)
     */
    public static function currentUserId() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Lấy role hiện tại (session)
     */
    public static function currentUserRole(): string {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['user_role'] ?? 'user';
    }
}
