<?php
session_start();

require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/models/BannerModel.php';
require_once 'app/models/UserModel.php';
require_once 'app/models/OrderModel.php';
require_once 'app/config/database.php';

$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Bỏ prefix thư mục nếu có
if (!empty($url[0]) && in_array($url[0], ['Bai01_BuiNguyenHuyToan','Ma-Nguon-Mo'])) {
    array_shift($url);
}

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
