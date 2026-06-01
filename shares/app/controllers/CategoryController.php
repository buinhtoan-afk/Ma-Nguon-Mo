<?php
require_once 'app/models/CategoryModel.php';
require_once 'app/config/database.php';
require_once 'app/middleware/AuthMiddleware.php';

class CategoryController {

    private $conn;

    public function __construct() {
        $database   = new Database();
        $this->conn = $database->getConnection();
    }

    public function index() { $this->list(); }

    // Danh sách danh mục (CHỈ ADMIN)
    public function list() {
        AuthMiddleware::requireAdmin();

        $stmt       = $this->conn->query("SELECT * FROM category ORDER BY id DESC");
        $rows       = $stmt->fetchAll();
        $categories = [];
        foreach ($rows as $row) {
            $categories[] = new CategoryModel($row['id'], $row['name'], $row['description']);
        }
        include 'app/views/category/list.php';
    }

    // Thêm danh mục (CHỈ ADMIN)
    public function add() {
        AuthMiddleware::requireAdmin();

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name        = trim($_POST['name']        ?? '');
            $description = trim($_POST['description'] ?? '');

            if (empty($name)) {
                $errors[] = 'Tên danh mục là bắt buộc.';
            } elseif (strlen($name) < 3 || strlen($name) > 100) {
                $errors[] = 'Tên danh mục phải từ 3 đến 100 ký tự.';
            }

            if (empty($errors)) {
                $stmt = $this->conn->prepare(
                    "INSERT INTO category (name, description) VALUES (:name, :description)"
                );
                $stmt->execute([':name' => $name, ':description' => $description]);
                header('Location: ' . $this->_base() . '/Category/list');
                exit();
            }
        }
        include 'app/views/category/add.php';
    }

    // Sửa danh mục (CHỈ ADMIN)
    public function edit($id) {
        AuthMiddleware::requireAdmin();

        $stmt = $this->conn->prepare("SELECT * FROM category WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) die('Danh mục không tồn tại.');

        $category = new CategoryModel($row['id'], $row['name'], $row['description']);
        $errors   = [];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name        = trim($_POST['name']        ?? '');
            $description = trim($_POST['description'] ?? '');

            if (empty($name)) {
                $errors[] = 'Tên danh mục là bắt buộc.';
            } elseif (strlen($name) < 3 || strlen($name) > 100) {
                $errors[] = 'Tên danh mục phải từ 3 đến 100 ký tự.';
            }

            if (empty($errors)) {
                $stmt = $this->conn->prepare(
                    "UPDATE category SET name=:name, description=:description WHERE id=:id"
                );
                $stmt->execute([':name' => $name, ':description' => $description, ':id' => $id]);
                header('Location: ' . $this->_base() . '/Category/list');
                exit();
            }

            $category->setName($name);
            $category->setDescription($description);
        }
        include 'app/views/category/edit.php';
    }

    // Xóa danh mục (CHỈ ADMIN)
    public function delete($id) {
        AuthMiddleware::requireAdmin();

        $stmt = $this->conn->prepare("DELETE FROM category WHERE id = :id");
        $stmt->execute([':id' => $id]);
        header('Location: ' . $this->_base() . '/Category/list');
        exit();
    }

    private function _base() {
        return rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    }
}
