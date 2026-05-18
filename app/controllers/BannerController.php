<?php

require_once 'app/models/BannerModel.php';
require_once 'app/config/database.php';

class BannerController {

    private $conn;

    public function __construct() {
        $db         = new Database();
        $this->conn = $db->getConnection();
        $this->_ensureTable();
    }

    // Tự động tạo bảng banner nếu chưa có
    private function _ensureTable() {
        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS banner (
                id       INT AUTO_INCREMENT PRIMARY KEY,
                image    VARCHAR(255) NOT NULL,
                title    VARCHAR(150) DEFAULT '',
                position TINYINT NOT NULL DEFAULT 1
            )
        ");
    }

    public function index() { $this->list(); }

    // Trang quản lý banner
    public function list() {
        $banners = $this->_getBanners();
        include 'app/views/banner/list.php';
    }

    // Upload banner cho vị trí position (1/2/3)
    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Bai01_BuiNguyenHuyToan/Banner/list');
            exit();
        }

        $position = intval($_POST['position'] ?? 0);
        $title    = trim($_POST['title'] ?? '');
        $errors   = [];

        if ($position < 1 || $position > 3) {
            $errors[] = 'Vị trí banner không hợp lệ.';
        }

        if (empty($_FILES['image']) || $_FILES['image']['error'] != 0) {
            $errors[] = 'Vui lòng chọn file ảnh.';
        } else {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($_FILES['image']['type'], $allowed)) {
                $errors[] = 'Chỉ chấp nhận JPG, PNG, GIF, WEBP.';
            } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Ảnh tối đa 5MB.';
            }
        }

        if (empty($errors)) {
            $targetDir = 'public/images/banners/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

            $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $imgName  = 'banner' . $position . '_' . time() . '.' . $ext;
            $filePath = $targetDir . $imgName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
                // Xóa banner cũ ở vị trí này (nếu có)
                $old = $this->conn->prepare("SELECT image FROM banner WHERE position = :pos");
                $old->execute([':pos' => $position]);
                $oldRow = $old->fetch();
                if ($oldRow && file_exists('public/images/banners/' . $oldRow['image'])) {
                    @unlink('public/images/banners/' . $oldRow['image']);
                }

                // Upsert: xóa rồi insert lại cho vị trí này
                $del = $this->conn->prepare("DELETE FROM banner WHERE position = :pos");
                $del->execute([':pos' => $position]);

                $ins = $this->conn->prepare(
                    "INSERT INTO banner (image, title, position) VALUES (:img, :title, :pos)"
                );
                $ins->execute([':img' => $imgName, ':title' => $title, ':pos' => $position]);
            } else {
                $errors[] = 'Upload thất bại, kiểm tra quyền thư mục.';
            }
        }

        $banners = $this->_getBanners();
        include 'app/views/banner/list.php';
    }

    // Xóa banner
    public function delete($id) {
        $stmt = $this->conn->prepare("SELECT image FROM banner WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if ($row && file_exists('public/images/banners/' . $row['image'])) {
            @unlink('public/images/banners/' . $row['image']);
        }

        $del = $this->conn->prepare("DELETE FROM banner WHERE id = :id");
        $del->execute([':id' => $id]);

        header('Location: /Bai01_BuiNguyenHuyToan/Banner/list');
        exit();
    }

    // Helper: lấy 3 banner
    private function _getBanners() {
        $stmt = $this->conn->query("SELECT * FROM banner ORDER BY position ASC");
        $rows = $stmt->fetchAll();
        $map  = [];
        foreach ($rows as $r) {
            $map[$r['position']] = new BannerModel($r['id'], $r['image'], $r['title'], $r['position']);
        }
        return $map; // key = position (1,2,3)
    }

    // Static helper cho các controller khác lấy banner
    public static function getBannersStatic($conn) {
        try {
            $stmt = $conn->query("SELECT * FROM banner ORDER BY position ASC");
            $rows = $stmt->fetchAll();
            $map  = [];
            foreach ($rows as $r) {
                $map[$r['position']] = $r;
            }
            return $map;
        } catch (Exception $e) {
            return [];
        }
    }
}
