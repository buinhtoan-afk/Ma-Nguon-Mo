<?php
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách danh mục</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark px-4">
    <a class="navbar-brand" href="<?= $baseUrl ?>/Product/list">
        <i class="fa-solid fa-mobile-screen-button"></i> HUY TOAN STORE
    </a>
    <div>
        <a href="<?= $baseUrl ?>/Product/list" class="btn btn-outline-light btn-sm me-2">Sản phẩm</a>
        <a href="<?= $baseUrl ?>/Category/list" class="btn btn-light btn-sm">Danh mục</a>
    </div>
</nav>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fa-solid fa-list"></i> Quản lý danh mục</h4>
        <a href="<?= $baseUrl ?>/Category/add" class="btn btn-success">
            <i class="fa-solid fa-plus"></i> Thêm danh mục
        </a>
    </div>

    <?php if (!empty($categories)): ?>
        <table class="table table-bordered table-hover bg-white shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Tên danh mục</th>
                    <th>Mô tả</th>
                    <th style="width:160px">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= $category->getID() ?></td>
                        <td><?= htmlspecialchars($category->getName()) ?></td>
                        <td><?= htmlspecialchars($category->getDescription()) ?></td>
                        <td>
                            <a href="<?= $baseUrl ?>/Category/edit/<?= $category->getID() ?>"
                               class="btn btn-warning btn-sm">
                                <i class="fa-solid fa-pen"></i> Sửa
                            </a>
                            <a href="<?= $baseUrl ?>/Category/delete/<?= $category->getID() ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Xóa danh mục này sẽ xóa luôn các sản phẩm thuộc nó. Bạn chắc chắn?')">
                                <i class="fa-solid fa-trash"></i> Xóa
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">Chưa có danh mục nào.</div>
    <?php endif; ?>

</div>
</body>
</html>
