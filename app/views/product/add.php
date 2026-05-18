<?php
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow">

                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0">Thêm Sản Phẩm Mới</h4>
                </div>

                <div class="card-body">

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= $baseUrl ?>/Product/add" method="POST" enctype="multipart/form-data">

                        <!-- TÊN SẢN PHẨM -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   placeholder="Nhập từ 10 - 100 ký tự"
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        </div>

                        <!-- MÔ TẢ -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mô tả</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>

                        <!-- GIÁ -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Giá sản phẩm <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₫</span>
                                <input type="number" name="price" class="form-control" step="1" min="1"
                                       value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- DANH MỤC -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select">
                                <option value="0">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"
                                        <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- HÌNH ẢNH -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Hình ảnh sản phẩm</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <div class="form-text">JPG, PNG, GIF, WEBP - tối đa 5MB</div>
                        </div>

                        <!-- BUTTON -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">Lưu sản phẩm</button>
                            <a href="<?= $baseUrl ?>/Product/list" class="btn btn-outline-secondary">Quay lại</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
