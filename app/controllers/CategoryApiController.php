<?php
require_once 'app/config/database.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/helpers/SessionHelper.php';

/**
 * CategoryApiController
 * RESTful API cho danh mục
 *
 * GET    /api/category          → index()  : Danh sách danh mục
 * GET    /api/category/{id}     → show()   : Chi tiết 1 danh mục
 * POST   /api/category          → store()  : Thêm danh mục  [Admin]
 * PUT    /api/category/{id}     → update() : Cập nhật       [Admin]
 * DELETE /api/category/{id}     → destroy(): Xóa            [Admin]
 */
class CategoryApiController {

    private $conn;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->conn = (new Database())->getConnection();
    }

    // GET /api/category  →  Danh sách danh mục (có đếm số sản phẩm)
    public function index() {
        $stmt = $this->conn->query(
            "SELECT c.id, c.name, c.description,
                    COUNT(p.id) AS product_count
             FROM category c
             LEFT JOIN product p ON p.category_id = c.id
             GROUP BY c.id
             ORDER BY c.name ASC"
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $categories = array_map(fn($r) => [
            'id'            => (int)$r['id'],
            'name'          => $r['name'],
            'description'   => $r['description'],
            'product_count' => (int)$r['product_count'],
        ], $rows);

        SessionHelper::jsonResponse([
            'success' => true,
            'total'   => count($categories),
            'data'    => $categories,
        ]);
    }

    // GET /api/category/{id}  →  Chi tiết danh mục + sản phẩm thuộc danh mục
    public function show($id) {
        $stmt = $this->conn->prepare("SELECT * FROM category WHERE id = :id");
        $stmt->execute([':id' => (int)$id]);
        $cat = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cat) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => "Không tìm thấy danh mục có ID = $id",
            ], 404);
        }

        // Lấy sản phẩm thuộc danh mục
        $pStmt = $this->conn->prepare(
            "SELECT id, name, price, image FROM product WHERE category_id = :cid ORDER BY id DESC");
        $pStmt->execute([':cid' => (int)$id]);
        $products = $pStmt->fetchAll(PDO::FETCH_ASSOC);

        SessionHelper::jsonResponse([
            'success' => true,
            'data'    => [
                'id'          => (int)$cat['id'],
                'name'        => $cat['name'],
                'description' => $cat['description'],
                'products'    => $products,
            ],
        ]);
    }

    // POST /api/category  →  Thêm danh mục  [Admin]
    public function store() {
        SessionHelper::requireAdminApi();

        $data   = SessionHelper::getJsonInput();
        $errors = $this->_validate($data);

        if (!empty($errors)) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors'  => $errors,
            ], 422);
        }

        $name        = htmlspecialchars(strip_tags(trim($data['name'])));
        $description = htmlspecialchars(strip_tags(trim($data['description'] ?? '')));

        // Kiểm tra tên trùng
        $dup = $this->conn->prepare("SELECT id FROM category WHERE name = :n");
        $dup->execute([':n' => $name]);
        if ($dup->fetch()) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => 'Tên danh mục đã tồn tại',
                'errors'  => ['name' => 'Tên danh mục đã tồn tại'],
            ], 422);
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO category (name, description) VALUES (:name, :description)");
        $stmt->execute([':name' => $name, ':description' => $description]);
        $newId = $this->conn->lastInsertId();

        SessionHelper::jsonResponse([
            'success' => true,
            'message' => 'Thêm danh mục thành công',
            'data'    => ['id' => (int)$newId, 'name' => $name],
        ], 201);
    }

    // PUT /api/category/{id}  →  Cập nhật danh mục  [Admin]
    public function update($id) {
        SessionHelper::requireAdminApi();

        $check = $this->conn->prepare("SELECT id FROM category WHERE id = :id");
        $check->execute([':id' => (int)$id]);
        if (!$check->fetch()) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => "Không tìm thấy danh mục có ID = $id",
            ], 404);
        }

        $data   = SessionHelper::getJsonInput();
        $errors = $this->_validate($data);
        if (!empty($errors)) {
            SessionHelper::jsonResponse([
                'success' => false,
                'errors'  => $errors,
            ], 422);
        }

        $name        = htmlspecialchars(strip_tags(trim($data['name'])));
        $description = htmlspecialchars(strip_tags(trim($data['description'] ?? '')));

        $stmt = $this->conn->prepare(
            "UPDATE category SET name=:name, description=:description WHERE id=:id");
        $stmt->execute([':name' => $name, ':description' => $description, ':id' => (int)$id]);

        SessionHelper::jsonResponse([
            'success' => true,
            'message' => 'Cập nhật danh mục thành công',
        ]);
    }

    // DELETE /api/category/{id}  →  Xóa danh mục  [Admin]
    public function destroy($id) {
        SessionHelper::requireAdminApi();

        $check = $this->conn->prepare("SELECT id FROM category WHERE id = :id");
        $check->execute([':id' => (int)$id]);
        if (!$check->fetch()) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => "Không tìm thấy danh mục có ID = $id",
            ], 404);
        }

        // Kiểm tra còn sản phẩm thuộc danh mục này không
        $cnt = $this->conn->prepare("SELECT COUNT(*) FROM product WHERE category_id = :id");
        $cnt->execute([':id' => (int)$id]);
        $productCount = (int)$cnt->fetchColumn();

        if ($productCount > 0) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => "Không thể xóa danh mục vì vẫn còn $productCount sản phẩm thuộc danh mục này",
            ], 409);
        }

        $this->conn->prepare("DELETE FROM category WHERE id = :id")
            ->execute([':id' => (int)$id]);

        SessionHelper::jsonResponse([
            'success' => true,
            'message' => "Xóa danh mục ID $id thành công",
        ]);
    }

    private function _validate(array $data): array {
        $errors = [];
        $name   = trim($data['name'] ?? '');
        if (empty($name)) {
            $errors['name'] = 'Tên danh mục không được để trống';
        } elseif (strlen($name) < 2 || strlen($name) > 100) {
            $errors['name'] = 'Tên danh mục phải từ 2 đến 100 ký tự';
        }
        return $errors;
    }
}
