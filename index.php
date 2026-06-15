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

    $resource = $url[1] ?? '';   // 'product' | 'category' | 'cart' | 'order'
    $seg2     = $url[2] ?? null; // ID hoặc sub-action
    $seg3     = $url[3] ?? null; // sub-action khi có ID (vd: /api/order/5/cancel)
    $method   = strtoupper($_SERVER['REQUEST_METHOD']);

    // ──────────────────────────────────────────────────────────
    // ĐỊNH TUYẾN AUTH: /api/login  và  /api/register
    // ──────────────────────────────────────────────────────────
    if ($resource === 'login' || $resource === 'register') {
        require_once 'app/controllers/AuthApiController.php';
        $authController = new AuthApiController();

        if ($resource === 'login' && $method === 'POST') {
            $authController->login();
            exit;
        }
        if ($resource === 'register' && $method === 'POST') {
            $authController->register();
            exit;
        }

        SessionHelper::jsonResponse([
            'success' => false,
            'message' => 'Method không được hỗ trợ cho endpoint này',
        ], 405);
    }

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
                'GET    /api/cart'              => 'Xem giỏ hàng',
                'POST   /api/cart/add'          => 'Thêm sản phẩm vào giỏ',
                'PUT    /api/cart/update'       => 'Cập nhật số lượng',
                'DELETE /api/cart/{product_id}' => 'Xóa 1 sản phẩm khỏi giỏ',
                'DELETE /api/cart/clear'        => 'Xóa toàn bộ giỏ',
                'GET    /api/cart/total'        => 'Tính tổng tiền giỏ hàng',
                'POST   /api/order'             => 'Tạo đơn hàng từ giỏ hàng',
                'GET    /api/order'             => 'Danh sách đơn hàng',
                'GET    /api/order/{id}'        => 'Chi tiết đơn hàng',
                'PUT    /api/order/{id}/cancel' => 'Hủy đơn hàng',
                'PUT    /api/order/{id}/status' => 'Cập nhật trạng thái đơn hàng [Admin]',
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

    // ──────────────────────────────────────────────────────────
    // ĐỊNH TUYẾN ĐẶC BIỆT: /api/cart  và  /api/order
    // ──────────────────────────────────────────────────────────
    if ($resource === 'cart') {
        // GET    /api/cart            → index()  : xem giỏ hàng
        // GET    /api/cart/total      → total()  : tính tổng tiền
        // POST   /api/cart/add        → add()    : thêm sản phẩm
        // PUT    /api/cart/update     → update() : cập nhật số lượng
        // DELETE /api/cart/clear      → clear()  : xóa toàn bộ giỏ
        // DELETE /api/cart/{id}       → remove() : xóa 1 sản phẩm
        if ($method === 'GET' && $seg2 === 'total') {
            $controller->total(); exit;
        }
        if ($method === 'GET' && $seg2 === null) {
            $controller->index(); exit;
        }
        if ($method === 'POST' && $seg2 === 'add') {
            $controller->add(); exit;
        }
        if (in_array($method, ['PUT', 'PATCH']) && $seg2 === 'update') {
            $controller->update(); exit;
        }
        if ($method === 'DELETE' && $seg2 === 'clear') {
            $controller->clear(); exit;
        }
        if ($method === 'DELETE' && $seg2 !== null) {
            $controller->remove($seg2); exit;
        }
        SessionHelper::jsonResponse(['success' => false, 'message' => 'Endpoint /api/cart không hợp lệ'], 404);
    }

    if ($resource === 'order') {
        // GET    /api/order            → index()      : danh sách đơn hàng
        // GET    /api/order/{id}       → show()       : chi tiết đơn hàng
        // POST   /api/order            → store()      : tạo đơn từ giỏ hàng
        // PUT    /api/order/{id}/cancel→ cancel()     : hủy đơn hàng
        // PUT    /api/order/{id}/status→ updateStatus(): cập nhật trạng thái [Admin]
        if ($method === 'GET' && $seg2 === null) {
            $controller->index(); exit;
        }
        if ($method === 'GET' && $seg2 !== null) {
            $controller->show($seg2); exit;
        }
        if ($method === 'POST' && $seg2 === null) {
            $controller->store(); exit;
        }
        if (in_array($method, ['PUT', 'PATCH']) && $seg2 !== null && $seg3 === 'cancel') {
            $controller->cancel($seg2); exit;
        }
        if (in_array($method, ['PUT', 'PATCH']) && $seg2 !== null && $seg3 === 'status') {
            $controller->updateStatus($seg2); exit;
        }
        SessionHelper::jsonResponse(['success' => false, 'message' => 'Endpoint /api/order không hợp lệ'], 404);
    }

    // ──────────────────────────────────────────────────────────
    // ĐỊNH TUYẾN CHUẨN CRUD: /api/{resource}/{id?}  (product, category, ...)
    // ──────────────────────────────────────────────────────────
    $id = $seg2;

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
