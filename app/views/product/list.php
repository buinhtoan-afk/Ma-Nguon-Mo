<?php
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$activeCat = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách sản phẩm</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* ===== CATEGORY FILTER BAR ===== */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 18px 40px 6px;
            flex-wrap: wrap;
        }
        .filter-bar .filter-label {
            font-weight: bold;
            color: #333;
            font-size: 15px;
            white-space: nowrap;
        }
        .filter-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            border: 2px solid #ddd;
            background: #fff;
            color: #333;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .filter-btn:hover {
            border-color: #ffd400;
            background: #fff9e0;
            color: #000;
        }
        .filter-btn.active {
            background: #ffd400;
            border-color: #ffd400;
            color: #000;
            box-shadow: 0 3px 10px rgba(255,212,0,0.4);
        }
        .filter-btn .cat-icon {
            font-size: 16px;
        }
        .product-count {
            color: #888;
            font-size: 14px;
            margin-left: auto;
        }
    </style>
</head>
<body>

<header class="topbar">
    <div class="logo">
        <i class="fa-solid fa-mobile-screen-button"></i>
        HUY TOAN STORE
    </div>
    <div class="search-box">
        <input type="text" placeholder="Bạn tìm gì hôm nay...">
        <button><i class="fa fa-search"></i></button>
    </div>
    <div class="top-actions">
        <div><i class="fa-regular fa-user"></i> Đăng nhập</div>
        <div><i class="fa-solid fa-cart-shopping"></i> Giỏ hàng</div>
    </div>
</header>

<nav class="menu">
    <a href="<?= $baseUrl ?>/Category/list">Quản lý danh mục</a>
    <a href="<?= $baseUrl ?>/Banner/list">🖼️ Banner</a>
    <a href="<?= $baseUrl ?>/Product/add" style="color:#e00;">+ Thêm SP</a>
</nav>

<!-- BANNER -->
<section class="banner">
    <div class="banner-left">
        <?php $b1 = $banners[1] ?? null; ?>
        <img src="<?= $b1 ? $baseUrl . '/public/images/banners/' . htmlspecialchars($b1['image']) : 'https://placehold.co/800x400?text=Banner+1' ?>"
             onerror="this.src='https://placehold.co/800x400?text=Banner+1'">
    </div>
    <div class="banner-right">
        <?php $b2 = $banners[2] ?? null; ?>
        <img src="<?= $b2 ? $baseUrl . '/public/images/banners/' . htmlspecialchars($b2['image']) : 'https://placehold.co/400x195?text=Banner+2' ?>"
             onerror="this.src='https://placehold.co/400x195?text=Banner+2'">
        <?php $b3 = $banners[3] ?? null; ?>
        <img src="<?= $b3 ? $baseUrl . '/public/images/banners/' . htmlspecialchars($b3['image']) : 'https://placehold.co/400x195?text=Banner+3' ?>"
             onerror="this.src='https://placehold.co/400x195?text=Banner+3'">
    </div>
</section>

<!-- ===== FILTER BAR PHÂN LOẠI ===== -->
<?php
// Icon gợi ý theo tên danh mục
function getCatIcon($name) {
    $name = mb_strtolower($name);
    if (str_contains($name, 'điện thoại') || str_contains($name, 'phone')) return '📱';
    if (str_contains($name, 'laptop') || str_contains($name, 'máy tính xách tay')) return '💻';
    if (str_contains($name, 'tablet') || str_contains($name, 'máy tính bảng')) return '📲';
    if (str_contains($name, 'phụ kiện') || str_contains($name, 'accessory')) return '🎧';
    if (str_contains($name, 'âm thanh') || str_contains($name, 'loa') || str_contains($name, 'tai nghe')) return '🔊';
    if (str_contains($name, 'đồng hồ') || str_contains($name, 'watch')) return '⌚';
    if (str_contains($name, 'máy ảnh') || str_contains($name, 'camera')) return '📷';
    return '🏷️';
}

// Đếm sản phẩm mỗi danh mục
$countByCat = [];
foreach ($products as $p) {
    $cid = $p->getCategoryID();
    if ($cid) $countByCat[$cid] = ($countByCat[$cid] ?? 0) + 1;
}

// Lọc sản phẩm theo danh mục được chọn
$filtered = ($activeCat > 0)
    ? array_filter($products, fn($p) => $p->getCategoryID() == $activeCat)
    : $products;
$filtered = array_values($filtered);
?>

<div class="filter-bar">
    <span class="filter-label">Phân loại:</span>

    <a href="<?= $baseUrl ?>/Product/list"
       class="filter-btn <?= $activeCat === 0 ? 'active' : '' ?>">
        🔥 Tất cả
        <span style="background:rgba(0,0,0,0.1); border-radius:20px; padding:1px 7px; font-size:12px;">
            <?= count($products) ?>
        </span>
    </a>

    <?php foreach ($categories as $cat): ?>
        <?php $cnt = $countByCat[$cat['id']] ?? 0; ?>
        <a href="<?= $baseUrl ?>/Product/list?category_id=<?= $cat['id'] ?>"
           class="filter-btn <?= $activeCat === $cat['id'] ? 'active' : '' ?>">
            <span class="cat-icon"><?= getCatIcon($cat['name']) ?></span>
            <?= htmlspecialchars($cat['name']) ?>
            <span style="background:rgba(0,0,0,0.1); border-radius:20px; padding:1px 7px; font-size:12px;">
                <?= $cnt ?>
            </span>
        </a>
    <?php endforeach; ?>

    <span class="product-count">
        Hiển thị <strong><?= count($filtered) ?></strong> sản phẩm
    </span>
</div>

<div class="container">

    <div class="title-row">
        <?php if ($activeCat > 0):
            $activeName = '';
            foreach ($categories as $c) { if ($c['id'] == $activeCat) { $activeName = $c['name']; break; } }
        ?>
            <h2><?= getCatIcon($activeName) ?> <?= htmlspecialchars($activeName) ?></h2>
        <?php else: ?>
            <h2>🔥 SẢN PHẨM HOT</h2>
        <?php endif; ?>

        <a href="<?= $baseUrl ?>/Product/add" class="add-btn">+ Thêm sản phẩm</a>
    </div>

    <div class="product-grid">

        <?php if (!empty($filtered)): ?>
            <?php foreach ($filtered as $product): ?>
                <div class="product-card">
                    <div class="discount-tag">Trả góp 0%</div>
                    <?php $imgName = $product->getImage() ?: 'default.jpg'; ?>
                    <img src="<?= $baseUrl ?>/public/images/<?= htmlspecialchars($imgName) ?>"
                         alt="<?= htmlspecialchars($product->getName()) ?>"
                         style="width:100%; height:200px; object-fit:cover; display:block;"
                         onerror="this.src='https://placehold.co/300x300?text=No+Image'">
                    <h3><?= htmlspecialchars($product->getName()) ?></h3>
                    <?php if (!empty($product->categoryName)): ?>
                        <p style="font-size:12px; color:#888; margin:4px 0;">
                            <i class="fa-solid fa-tag"></i> <?= htmlspecialchars($product->categoryName) ?>
                        </p>
                    <?php endif; ?>
                    <p class="desc"><?= htmlspecialchars($product->getDescription()) ?></p>
                    <div class="price"><?= number_format((float)$product->getPrice(), 0, ',', '.') ?>₫</div>
                    <div class="button-group">
                        <a href="<?= $baseUrl ?>/Product/edit/<?= $product->getID() ?>" class="edit-btn">Sửa</a>
                        <a href="<?= $baseUrl ?>/Product/delete/<?= $product->getID() ?>"
                           class="delete-btn"
                           onclick="return confirm('Bạn chắc chắn muốn xóa?')">Xóa</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column:1/-1; text-align:center; padding:60px 0;">
                <div style="font-size:48px; margin-bottom:12px;">🔍</div>
                <h3 style="color:#666;">Không có sản phẩm nào trong danh mục này</h3>
                <a href="<?= $baseUrl ?>/Product/list" style="color:#e00; margin-top:8px; display:inline-block;">← Xem tất cả</a>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
