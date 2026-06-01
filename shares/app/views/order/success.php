<?php
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$payLabel = ['card'=>'Thẻ tín dụng/Ghi nợ','bank'=>'Chuyển khoản ngân hàng','wallet'=>'Ví điện tử','cod'=>'Thanh toán khi nhận hàng'];
$shipLabel = ['standard'=>'Giao hàng tiêu chuẩn (3-5 ngày)','express'=>'Giao hàng hỏa tốc (24h)'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt hàng thành công!</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .success-wrap { max-width:680px; margin:50px auto; padding:0 20px; }
        .success-card { background:#fff; border-radius:20px; padding:40px; box-shadow:0 6px 30px rgba(0,0,0,.10); text-align:center; }
        .success-icon { font-size:72px; margin-bottom:16px; animation:pop .4s ease; }
        @keyframes pop { 0%{transform:scale(0)} 80%{transform:scale(1.1)} 100%{transform:scale(1)} }
        .success-card h2 { font-size:26px; margin-bottom:8px; color:#16a34a; }
        .success-card p.sub { color:#888; margin-bottom:30px; }
        .order-detail { background:#f9f9f9; border-radius:14px; padding:20px; text-align:left; margin-bottom:24px; }
        .detail-row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #eee; font-size:14px; }
        .detail-row:last-child { border-bottom:none; }
        .detail-row .label { color:#888; }
        .detail-row .val { font-weight:bold; }
        .total-row { font-size:18px; color:#e00; }
        .items-list { text-align:left; margin-bottom:20px; }
        .items-list h4 { margin-bottom:10px; font-size:15px; }
        .item-row { display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #eee; }
        .item-row img { width:48px; height:48px; object-fit:contain; background:#f5f5f5; border-radius:8px; }
        .item-row .name { font-size:13px; font-weight:bold; flex:1; }
        .item-row .qty { font-size:12px; color:#888; }
        .item-row .price { font-size:14px; font-weight:bold; color:#e00; white-space:nowrap; }
        .btn-home { display:inline-block; padding:14px 30px; background:#ffd400; border-radius:12px; font-weight:bold; text-decoration:none; color:#000; margin:0 6px; }
        .btn-profile { display:inline-block; padding:14px 30px; background:#0d6efd; border-radius:12px; font-weight:bold; text-decoration:none; color:#fff; margin:0 6px; }
    </style>
</head>
<body>
<?php include 'shares/header.php'; ?>
<nav class="menu">
    <a href="<?= $baseUrl ?>/Product/list">🏠 Trang chủ</a>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?= $baseUrl ?>/Auth/profile">Hồ sơ</a>
    <?php endif; ?>
</nav>

<div class="success-wrap">
    <div class="success-card">
        <div class="success-icon">🎉</div>
        <h2>Đặt hàng thành công!</h2>
        <p class="sub">Cảm ơn bạn đã mua sắm tại HUY TOAN STORE. Đơn hàng của bạn đang được xử lý.</p>

        <div class="order-detail">
            <div class="detail-row">
                <span class="label">Mã đơn hàng</span>
                <span class="val">#<?= $order->getID() ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Khách hàng</span>
                <span class="val"><?= htmlspecialchars($order->getFullname()) ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Điện thoại</span>
                <span class="val"><?= htmlspecialchars($order->getPhone()) ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Địa chỉ giao</span>
                <span class="val"><?= htmlspecialchars($order->getAddress() . ', ' . $order->getCity()) ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Vận chuyển</span>
                <span class="val"><?= $shipLabel[$order->getShippingMethod()] ?? $order->getShippingMethod() ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Thanh toán</span>
                <span class="val"><?= $payLabel[$order->getPaymentMethod()] ?? $order->getPaymentMethod() ?></span>
            </div>
            <div class="detail-row total-row">
                <span class="label" style="color:#333;font-weight:bold;">Tổng cộng</span>
                <span class="val"><?= number_format($order->getTotal(),0,',','.') ?>₫</span>
            </div>
        </div>

        <?php if (!empty($items)): ?>
        <div class="items-list">
            <h4>📦 Sản phẩm trong đơn:</h4>
            <?php foreach ($items as $item): ?>
            <div class="item-row">
                <img src="<?= $baseUrl ?>/public/images/<?= htmlspecialchars($item['image'] ?? 'default.jpg') ?>"
                     onerror="this.src='https://placehold.co/48x48?text=SP'">
                <div class="name"><?= htmlspecialchars($item['name']) ?></div>
                <div class="qty">x<?= $item['qty'] ?></div>
                <div class="price"><?= number_format($item['price']*$item['qty'],0,',','.') ?>₫</div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <a href="<?= $baseUrl ?>/Product/list" class="btn-home">🛍️ Tiếp tục mua sắm</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="<?= $baseUrl ?>/Auth/profile" class="btn-profile">📦 Xem đơn hàng</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
