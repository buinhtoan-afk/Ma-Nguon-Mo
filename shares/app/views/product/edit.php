<?php
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow">

                <div class="card-header bg-warning text-dark text-center">
                    <h4 class="mb-0">Chỉnh Sửa Sản Phẩm #<?= $product->getID() ?></h4>
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

                    <form action="<?= $baseUrl ?>/Product/edit/<?= $product->getID() ?>"
                          method="POST" enctype="multipart/form-data">

                        <!-- TÊN SẢN PHẨM -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="<?= htmlspecialchars($product->getName()) ?>">
                        </div>

                        <!-- MÔ TẢ -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mô tả</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($product->getDescription()) ?></textarea>
                        </div>

                        <!-- GIÁ -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Giá sản phẩm <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₫</span>
                                <input type="number" name="price" class="form-control" step="1" min="1"
                                       value="<?= htmlspecialchars($product->getPrice()) ?>">
                            </div>
                        </div>

                        <!-- DANH MỤC -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select">
                                <option value="0">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"
                                        <?= ($product->getCategoryID() == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- HÌNH ẢNH -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Hình ảnh hiện tại</label><br>
                            <?php
                                $imgName = $product->getImage() ?: 'default.jpg';
                            ?>
                            <img src="<?= $baseUrl ?>/public/images/<?= htmlspecialchars($imgName) ?>"
                                 style="max-height:150px; object-fit:cover; border-radius:6px; margin-bottom:8px;"
                                 onerror="this.src='https://placehold.co/150x150?text=No+Image'">
                            <label class="form-label fw-bold d-block">Chọn ảnh mới (nếu muốn thay)</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <div class="form-text">JPG, PNG, GIF, WEBP - tối đa 5MB</div>
                        </div>

                        <!-- BUTTON -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning">Cập nhật sản phẩm</button>
                            <a href="<?= $baseUrl ?>/Product/list" class="btn btn-outline-secondary">Hủy bỏ</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
