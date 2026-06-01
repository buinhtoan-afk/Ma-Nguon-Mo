<?php
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Banner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .banner-slot { border: 2px dashed #ccc; border-radius: 12px; padding: 16px; background: #fafafa; }
        .banner-slot.has-banner { border-color: #28a745; background: #f0fff4; }
        .banner-preview { width: 100%; height: 160px; object-fit: cover; border-radius: 8px; margin-bottom: 10px; }
        .slot-label { font-weight: bold; font-size: 13px; color: #555; margin-bottom: 8px; }
        .pos-badge { display: inline-block; width: 28px; height: 28px; border-radius: 50%;
                     background: #ffd400; color: #000; font-weight: bold;
                     text-align: center; line-height: 28px; margin-right: 6px; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark px-4">
    <a class="navbar-brand" href="<?= $baseUrl ?>/Product/list">
        <i class="fa-solid fa-mobile-screen-button"></i> HUY TOAN STORE
    </a>
    <div>
        <a href="<?= $baseUrl ?>/Product/list"  class="btn btn-outline-light btn-sm me-2">Sản phẩm</a>
        <a href="<?= $baseUrl ?>/Category/list" class="btn btn-outline-light btn-sm me-2">Danh mục</a>
        <a href="<?= $baseUrl ?>/Banner/list"   class="btn btn-light btn-sm">Banner</a>
    </div>
</nav>

<div class="container mt-4">

    <h4 class="mb-1"><i class="fa-solid fa-images"></i> Quản lý Banner trang chủ</h4>
    <p class="text-muted mb-4">Trang chủ có 3 vị trí banner: 1 ảnh lớn bên trái, 2 ảnh nhỏ bên phải.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <?php
        $slots = [
            1 => ['label' => 'Banner 1 — Ảnh lớn (bên trái)', 'size' => '800×400px'],
            2 => ['label' => 'Banner 2 — Ảnh nhỏ trên (bên phải)', 'size' => '400×195px'],
            3 => ['label' => 'Banner 3 — Ảnh nhỏ dưới (bên phải)', 'size' => '400×195px'],
        ];
        ?>

        <?php foreach ($slots as $pos => $slot): ?>

            <?php $hasBanner = isset($banners[$pos]); ?>

            <div class="col-md-4">
                <div class="banner-slot <?= $hasBanner ? 'has-banner' : '' ?>">

                    <div class="slot-label">
                        <span class="pos-badge"><?= $pos ?></span>
                        <?= $slot['label'] ?>
                        <small class="text-muted d-block ms-4">Khuyến nghị: <?= $slot['size'] ?></small>
                    </div>

                    <?php if ($hasBanner): ?>
                        <img src="<?= $baseUrl ?>/public/images/banners/<?= htmlspecialchars($banners[$pos]->getImage()) ?>"
                             class="banner-preview"
                             onerror="this.src='https://placehold.co/800x400?text=Banner+<?= $pos ?>'">
                        <?php if ($banners[$pos]->getTitle()): ?>
                            <p class="text-muted small mb-2"><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($banners[$pos]->getTitle()) ?></p>
                        <?php endif; ?>
                        <a href="<?= $baseUrl ?>/Banner/delete/<?= $banners[$pos]->getID() ?>"
                           class="btn btn-sm btn-danger w-100 mb-2"
                           onclick="return confirm('Xóa banner này?')">
                            <i class="fa-solid fa-trash"></i> Xóa banner
                        </a>
                    <?php else: ?>
                        <div style="height:160px; display:flex; align-items:center; justify-content:center; color:#bbb; font-size:48px; margin-bottom:10px;">
                            <i class="fa-regular fa-image"></i>
                        </div>
                        <p class="text-center text-muted small mb-2">Chưa có banner</p>
                    <?php endif; ?>

                    <!-- Form upload -->
                    <form action="<?= $baseUrl ?>/Banner/upload" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="position" value="<?= $pos ?>">
                        <div class="mb-2">
                            <input type="text" name="title" class="form-control form-control-sm"
                                   placeholder="Tiêu đề banner (tuỳ chọn)"
                                   value="<?= $hasBanner ? htmlspecialchars($banners[$pos]->getTitle()) : '' ?>">
                        </div>
                        <div class="mb-2">
                            <input type="file" name="image" class="form-control form-control-sm" accept="image/*" required>
                        </div>
                        <button type="submit" class="btn btn-sm btn-success w-100">
                            <i class="fa-solid fa-upload"></i>
                            <?= $hasBanner ? 'Thay banner mới' : 'Upload banner' ?>
                        </button>
                    </form>

                </div>
            </div>

        <?php endforeach; ?>

    </div>

    <div class="mt-4">
        <a href="<?= $baseUrl ?>/Product/list" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left"></i> Về trang chủ
        </a>
    </div>

</div>
</body>
</html>
