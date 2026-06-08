<?php
require_once 'app/config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/helpers/SessionHelper.php';

/**
 * ProductApiController
 * RESTful API cho sản phẩm
 *
 * GET    /api/product          → index()  : Lấy danh sách sản phẩm
 * GET    /api/product/{id}     → show()   : Lấy 1 sản phẩm theo ID
 * POST   /api/product          → store()  : Thêm sản phẩm mới  [Admin]
 * PUT    /api/product/{id}     → update() : Cập nhật sản phẩm  [Admin]
 * DELETE /api/product/{id}     → destroy(): Xóa sản phẩm       [Admin]
 */
class ProductApiController {

    private $conn;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->conn = (new Database())->getConnection();
    }

    // ══════════════════════════════════════════════════════════
    // GET /api/product  →  Danh sách sản phẩm
    // ══════════════════════════════════════════════════════════
    public function index() {
        $params = [];
        $sql    = "SELECT p.id, p.name, p.description, p.price, p.image,
                          p.category_id, c.name AS category_name
                   FROM product p
                   LEFT JOIN category c ON p.category_id = c.id
                   WHERE 1=1";

        // Lọc theo danh mục
        if (!empty($_GET['category_id'])) {
            $sql .= " AND p.category_id = :cat";
            $params[':cat'] = (int)$_GET['category_id'];
        }

        // Tìm kiếm theo tên
        if (!empty($_GET['search'])) {
            $sql .= " AND p.name LIKE :search";
            $params[':search'] = '%' . trim($_GET['search']) . '%';
        }

        // Sắp xếp
        $sortAllowed = ['id', 'name', 'price'];
        $sort  = in_array($_GET['sort'] ?? '', $sortAllowed) ? $_GET['sort'] : 'id';
        $order = strtoupper($_GET['order'] ?? '') === 'ASC' ? 'ASC' : 'DESC';
        $sql  .= " ORDER BY p.$sort $order";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format dữ liệu
        $products = array_map(function($r) {
            return $this->_formatProduct($r);
        }, $rows);

        SessionHelper::jsonResponse([
            'success' => true,
            'total'   => count($products),
            'data'    => $products,
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // GET /api/product/{id}  →  Chi tiết 1 sản phẩm
    // ══════════════════════════════════════════════════════════
    public function show($id) {
        $stmt = $this->conn->prepare(
            "SELECT p.id, p.name, p.description, p.price, p.image,
                    p.category_id, c.name AS category_name
             FROM product p
             LEFT JOIN category c ON p.category_id = c.id
             WHERE p.id = :id"
        );
        $stmt->execute([':id' => (int)$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => "Không tìm thấy sản phẩm có ID = $id",
            ], 404);
        }

        SessionHelper::jsonResponse([
            'success' => true,
            'data'    => $this->_formatProduct($row),
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // POST /api/product  →  Thêm sản phẩm mới  [Admin]
    // ══════════════════════════════════════════════════════════
    public function store() {
        SessionHelper::requireAdminApi();

        $data = SessionHelper::getJsonInput();
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
        $price       = (float)$data['price'];
        $category_id = (int)($data['category_id'] ?? 0);
        $image       = htmlspecialchars(strip_tags(trim($data['image'] ?? 'default.jpg')));

        $stmt = $this->conn->prepare(
            "INSERT INTO product (name, description, price, image, category_id)
             VALUES (:name, :description, :price, :image, :category_id)"
        );
        $stmt->execute([
            ':name'        => $name,
            ':description' => $description,
            ':price'       => $price,
            ':image'       => $image,
            ':category_id' => $category_id ?: null,
        ]);

        $newId = $this->conn->lastInsertId();

        SessionHelper::jsonResponse([
            'success' => true,
            'message' => 'Thêm sản phẩm thành công',
            'data'    => ['id' => (int)$newId],
        ], 201);
    }

    // ══════════════════════════════════════════════════════════
    // PUT /api/product/{id}  →  Cập nhật sản phẩm  [Admin]
    // ══════════════════════════════════════════════════════════
    public function update($id) {
        SessionHelper::requireAdminApi();

        // Kiểm tra sản phẩm tồn tại
        $check = $this->conn->prepare("SELECT id FROM product WHERE id = :id");
        $check->execute([':id' => (int)$id]);
        if (!$check->fetch()) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => "Không tìm thấy sản phẩm có ID = $id",
            ], 404);
        }

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
        $price       = (float)$data['price'];
        $category_id = (int)($data['category_id'] ?? 0);
        $image       = htmlspecialchars(strip_tags(trim($data['image'] ?? '')));

        $sql = "UPDATE product SET
                    name        = :name,
                    description = :description,
                    price       = :price,
                    category_id = :category_id";

        $params = [
            ':name'        => $name,
            ':description' => $description,
            ':price'       => $price,
            ':category_id' => $category_id ?: null,
            ':id'          => (int)$id,
        ];

        // Chỉ cập nhật image nếu được gửi lên
        if ($image) {
            $sql .= ", image = :image";
            $params[':image'] = $image;
        }
        $sql .= " WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $ok   = $stmt->execute($params);

        if ($ok) {
            SessionHelper::jsonResponse([
                'success' => true,
                'message' => 'Cập nhật sản phẩm thành công',
            ]);
        } else {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => 'Cập nhật thất bại',
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════
    // DELETE /api/product/{id}  →  Xóa sản phẩm  [Admin]
    // ══════════════════════════════════════════════════════════
    public function destroy($id) {
        SessionHelper::requireAdminApi();

        $check = $this->conn->prepare("SELECT id, image FROM product WHERE id = :id");
        $check->execute([':id' => (int)$id]);
        $row = $check->fetch();

        if (!$row) {
            SessionHelper::jsonResponse([
                'success' => false,
                'message' => "Không tìm thấy sản phẩm có ID = $id",
            ], 404);
        }

        // Xóa ảnh nếu không phải default
        if (!empty($row['image']) && $row['image'] !== 'default.jpg') {
            $imgPath = 'public/images/' . $row['image'];
            if (file_exists($imgPath)) unlink($imgPath);
        }

        $stmt = $this->conn->prepare("DELETE FROM product WHERE id = :id");
        $stmt->execute([':id' => (int)$id]);

        SessionHelper::jsonResponse([
            'success' => true,
            'message' => "Xóa sản phẩm ID $id thành công",
        ]);
    }

    // ── Private helpers ─────────────────────────────────────
    private function _validate(array $data): array {
        $errors = [];
        $name  = trim($data['name'] ?? '');
        $price = $data['price'] ?? null;

        if (empty($name)) {
            $errors['name'] = 'Tên sản phẩm không được để trống';
        } elseif (strlen($name) < 3 || strlen($name) > 200) {
            $errors['name'] = 'Tên sản phẩm phải từ 3 đến 200 ký tự';
        }

        if ($price === null || $price === '') {
            $errors['price'] = 'Giá sản phẩm không được để trống';
        } elseif (!is_numeric($price) || (float)$price < 0) {
            $errors['price'] = 'Giá sản phẩm phải là số không âm';
        }

        return $errors;
    }

    private function _formatProduct(array $r): array {
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        return [
            'id'            => (int)$r['id'],
            'name'          => $r['name'],
            'description'   => $r['description'],
            'price'         => (float)$r['price'],
            'price_display' => number_format((float)$r['price'], 0, ',', '.') . ' VND',
            'image'         => $r['image'],
            'image_url'     => $base . '/public/images/' . ($r['image'] ?: 'default.jpg'),
            'category_id'   => $r['category_id'] ? (int)$r['category_id'] : null,
            'category_name' => $r['category_name'] ?? null,
        ];
    }
}
