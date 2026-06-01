<?php
require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/models/BannerModel.php';
require_once 'app/config/database.php';
require_once 'app/middleware/AuthMiddleware.php';

class ProductController {

    private $conn;

    public function __construct() {
        $database   = new Database();
        $this->conn = $database->getConnection();
    }

    public function index() { $this->list(); }

    // ── Danh sách sản phẩm (công khai) ─────────────────────────────────────
    public function list() {
        $categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
        $search     = trim($_GET['search'] ?? '');

        $sql = "SELECT p.*, c.name AS category_name
                FROM product p
                LEFT JOIN category c ON p.category_id = c.id
                WHERE 1=1";
        $params = [];
        if ($categoryId > 0) {
            $sql .= " AND p.category_id = :cat";
            $params[':cat'] = $categoryId;
        }
        if ($search !== '') {
            $sql .= " AND p.name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        $sql .= " ORDER BY p.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $rows     = $stmt->fetchAll();
        $products = [];
        foreach ($rows as $row) {
            $p = new ProductModel(
                $row['id'], $row['name'], $row['description'],
                $row['price'], $row['image'] ?? 'default.jpg', $row['category_id']
            );
            $p->categoryName = $row['category_name'] ?? '';
            $products[] = $p;
        }

        $banners    = $this->_getBanners();
        $categories = $this->_getCategories();

        include 'app/views/product/list.php';
    }

    // ── Chi tiết sản phẩm (công khai) ──────────────────────────────────────
    public function show($id) {
        $stmt = $this->conn->prepare("SELECT p.*, c.name AS category_name
            FROM product p LEFT JOIN category c ON p.category_id = c.id
            WHERE p.id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) die('Sản phẩm không tồn tại.');
        $product = new ProductModel(
            $row['id'], $row['name'], $row['description'],
            $row['price'], $row['image'] ?? 'default.jpg', $row['category_id']
        );
        $product->categoryName = $row['category_name'] ?? '';
        include 'app/views/product/show.php';
    }

    // ── Thêm sản phẩm (CHỈ ADMIN) ──────────────────────────────────────────
    public function add() {
        AuthMiddleware::requireAdmin();

        $errors     = [];
        $categories = $this->_getCategories();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name        = trim($_POST['name']        ?? '');
            $description = trim($_POST['description'] ?? '');
            $price       = trim($_POST['price']       ?? '');
            $category_id = intval($_POST['category_id'] ?? 0);

            if (empty($name)) {
                $errors[] = 'Tên sản phẩm là bắt buộc.';
            } elseif (strlen($name) < 10 || strlen($name) > 100) {
                $errors[] = 'Tên sản phẩm phải từ 10 đến 100 ký tự.';
            }
            if (!is_numeric($price) || $price <= 0) {
                $errors[] = 'Giá phải là số dương lớn hơn 0.';
            }
            if ($category_id <= 0) {
                $errors[] = 'Vui lòng chọn danh mục sản phẩm.';
            }

            $imageName = 'default.jpg';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $targetDir    = "public/images/";
                if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
                $allowedTypes = ['image/jpeg','image/png','image/gif','image/webp'];
                if (!in_array($_FILES['image']['type'], $allowedTypes)) {
                    $errors[] = 'Chỉ chấp nhận file ảnh JPG, PNG, GIF, WEBP.';
                } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                    $errors[] = 'Kích thước ảnh không được vượt quá 5MB.';
                } else {
                    $ext       = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $imageName = time() . '_' . uniqid() . '.' . $ext;
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $imageName)) {
                        $errors[] = 'Không thể upload hình ảnh.';
                    }
                }
            }

            if (empty($errors)) {
                $stmt = $this->conn->prepare(
                    "INSERT INTO product (name, description, price, image, category_id)
                     VALUES (:name, :description, :price, :image, :category_id)"
                );
                $stmt->execute([
                    ':name'        => $name, ':description' => $description,
                    ':price'       => $price, ':image'       => $imageName,
                    ':category_id' => $category_id,
                ]);
                header('Location: ' . $this->_base() . '/Product/list');
                exit();
            }
        }

        include 'app/views/product/add.php';
    }

    // ── Sửa sản phẩm (CHỈ ADMIN) ───────────────────────────────────────────
    public function edit($id) {
        AuthMiddleware::requireAdmin();

        $categories = $this->_getCategories();
        $stmt = $this->conn->prepare("SELECT * FROM product WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) die('Sản phẩm không tồn tại.');

        $product = new ProductModel(
            $row['id'], $row['name'], $row['description'],
            $row['price'], $row['image'] ?? 'default.jpg', $row['category_id']
        );
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name        = trim($_POST['name']        ?? '');
            $description = trim($_POST['description'] ?? '');
            $price       = trim($_POST['price']       ?? '');
            $category_id = intval($_POST['category_id'] ?? 0);

            if (empty($name)) {
                $errors[] = 'Tên sản phẩm là bắt buộc.';
            } elseif (strlen($name) < 10 || strlen($name) > 100) {
                $errors[] = 'Tên sản phẩm phải từ 10 đến 100 ký tự.';
            }
            if (!is_numeric($price) || $price <= 0) $errors[] = 'Giá phải là số dương lớn hơn 0.';
            if ($category_id <= 0) $errors[] = 'Vui lòng chọn danh mục sản phẩm.';

            $imageName = $product->getImage();
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $targetDir    = "public/images/";
                $allowedTypes = ['image/jpeg','image/png','image/gif','image/webp'];
                if (!in_array($_FILES['image']['type'], $allowedTypes)) {
                    $errors[] = 'Chỉ chấp nhận file ảnh JPG, PNG, GIF, WEBP.';
                } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                    $errors[] = 'Kích thước ảnh không được vượt quá 5MB.';
                } else {
                    $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $newImage = time() . '_' . uniqid() . '.' . $ext;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $newImage)) {
                        $imageName = $newImage;
                    } else {
                        $errors[] = 'Không thể upload hình ảnh.';
                    }
                }
            }

            if (empty($errors)) {
                $stmt = $this->conn->prepare(
                    "UPDATE product SET name=:name, description=:description, price=:price,
                     image=:image, category_id=:category_id WHERE id=:id"
                );
                $stmt->execute([
                    ':name'        => $name, ':description' => $description,
                    ':price'       => $price, ':image'       => $imageName,
                    ':category_id' => $category_id, ':id' => $id,
                ]);
                header('Location: ' . $this->_base() . '/Product/list');
                exit();
            }

            $product->setName($name);
            $product->setDescription($description);
            $product->setPrice($price);
            $product->setCategoryID($category_id);
        }

        include 'app/views/product/edit.php';
    }

    // ── Xóa sản phẩm (CHỈ ADMIN) ───────────────────────────────────────────
    public function delete($id) {
        AuthMiddleware::requireAdmin();

        $stmt = $this->conn->prepare("DELETE FROM product WHERE id = :id");
        $stmt->execute([':id' => $id]);
        header('Location: ' . $this->_base() . '/Product/list');
        exit();
    }

    private function _getCategories() {
        $stmt = $this->conn->query("SELECT * FROM category ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    private function _getBanners() {
        try {
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS banner (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    image VARCHAR(255) NOT NULL,
                    title VARCHAR(150) DEFAULT '',
                    position TINYINT NOT NULL DEFAULT 1
                )
            ");
            $stmt = $this->conn->query("SELECT * FROM banner ORDER BY position ASC");
            $rows = $stmt->fetchAll();
            $map  = [];
            foreach ($rows as $r) { $map[$r['position']] = $r; }
            return $map;
        } catch (Exception $e) {
            return [];
        }
    }

    private function _base() {
        return rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    }
}
