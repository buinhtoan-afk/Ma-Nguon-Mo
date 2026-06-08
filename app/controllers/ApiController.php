<?php
require_once 'app/middleware/AuthMiddleware.php';

/**
 * ApiController - Phục vụ trang frontend của API Manager
 */
class ApiController {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    // GET /Api/productManager  → Trang quản lý sản phẩm bằng jQuery
    public function productManager() {
        include 'app/views/api/product_manager.php';
    }
}
