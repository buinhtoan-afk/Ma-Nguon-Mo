<?php
session_start();

require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/models/BannerModel.php';
require_once 'app/models/UserModel.php';
require_once 'app/models/OrderModel.php';
require_once 'app/config/database.php';
require_once 'app/middleware/AuthMiddleware.php';
require_once 'app/helpers/SessionHelper.php';

// Parse URL
$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Bỏ prefix thư mục nếu có
if (!empty($url[0]) && in_array($url[0], ['Bai01_BuiNguyenHuyToan', 'Ma-Nguon-Mo', 'Ma-Nguon-Mo-Bai5'])) {
    array_shift($url);
}

// ══════════════════════════════════════════════════════════════
// ĐỊNH TUYẾN API  →  /api/{resource}/{id?}
// ══════════════════════════════════════════════════════════════
if (isset($url[0]) && strtolower($url[0]) === 'api') {

    // Xử lý CORS preflight
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        http_response_code(204);
        exit;
    }

    $resource = $url[1] ?? '';   // 'product' | 'category'
    $id       = $url[2] ?? null; // ID nếu có

    if (empty($resource)) {
        SessionHelper::jsonResponse([
            'success'   => false,
            'message'   => 'API endpoint không hợp lệ',
            'endpoints' => [
                'GET    /api/product'          => 'Danh sách sản phẩm',
                'GET    /api/product/{id}'     => 'Chi tiết sản phẩm',
                'POST   /api/product'          => 'Thêm sản phẩm [Admin]',
                'PUT    /api/product/{id}'     => 'Cập nhật sản phẩm [Admin]',
                'DELETE /api/product/{id}'     => 'Xóa sản phẩm [Admin]',
                'GET    /api/category'         => 'Danh sách danh mục',
                'GET    /api/category/{id}'    => 'Chi tiết danh mục',
                'POST   /api/category'         => 'Thêm danh mục [Admin]',
                'PUT    /api/category/{id}'    => 'Cập nhật danh mục [Admin]',
                'DELETE /api/category/{id}'    => 'Xóa danh mục [Admin]',
            ],
        ], 400);
    }

    // Load API controller
    $apiControllerName = ucfirst(strtolower($resource)) . 'ApiController';
    $apiControllerPath = 'app/controllers/' . $apiControllerName . '.php';

    if (!file_exists($apiControllerPath)) {
        SessionHelper::jsonResponse([
            'success' => false,
            'message' => "API resource '$resource' không tồn tại",
        ], 404);
    }

    require_once $apiControllerPath;
    $controller = new $apiControllerName();
    $method     = strtoupper($_SERVER['REQUEST_METHOD']);

    // Map HTTP method → action
    switch ($method) {
        case 'GET':
            $action = $id !== null ? 'show' : 'index';
            break;
        case 'POST':
            $action = 'store';
            break;
        case 'PUT':
        case 'PATCH':
            $action = $id !== null ? 'update' : null;
            break;
        case 'DELETE':
            $action = $id !== null ? 'destroy' : null;
            break;
        default:
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => "Method $method không được hỗ trợ",
            ], 405);
    }

    if (!$action || !method_exists($controller, $action)) {
        SessionHelper::jsonResponse([
            'success' => false,
            'message' => 'Action không tồn tại hoặc thiếu ID',
        ], 404);
    }

    // Gọi action
    $id !== null
        ? call_user_func([$controller, $action], $id)
        : call_user_func([$controller, $action]);

    exit;
}

// ══════════════════════════════════════════════════════════════
// ĐỊNH TUYẾN THÔNG THƯỜNG (non-API)
// ══════════════════════════════════════════════════════════════
$controllerName = (!empty($url[0])) ? ucfirst($url[0]) . 'Controller' : 'ProductController';
$action         = (!empty($url[1])) ? $url[1] : 'index';

$controllerPath = 'app/controllers/' . $controllerName . '.php';
if (!file_exists($controllerPath)) {
    die('Controller not found: ' . htmlspecialchars($controllerName));
}

require_once $controllerPath;
$controller = new $controllerName();

if (!method_exists($controller, $action)) {
    die('Action not found: ' . htmlspecialchars($action));
}

$params = array_slice($url, 2);
call_user_func_array([$controller, $action], $params);
