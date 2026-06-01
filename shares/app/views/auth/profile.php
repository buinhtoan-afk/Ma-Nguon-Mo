<?php
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$statusLabel = ['pending'=>'Chờ xác nhận','processing'=>'Đang xử lý','shipping'=>'Đang giao','done'=>'Hoàn thành','cancelled'=>'Đã hủy'];
$statusColor = ['pending'=>'#e67e00','processing'=>'#0d6efd','shipping'=>'#8800cc','done'=>'#16a34a','cancelled'=>'#dc2626'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ - HUY TOAN STORE</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .profile-wrap { max-width:900px; margin:40px auto; padding:0 20px; }
        .profile-card { background:#fff; border-radius:18px; padding:32px; box-shadow:0 4px 20px rgba(0,0,0,.08); margin-bottom:24px; }
        .profile-card h3 { font-size:20px; margin-bottom:20px; border-bottom:2px solid #ffd400; padding-bottom:10px; }
        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .info-item label { font-size:12px; color:#888; display:block; margin-bottom:4px; }
        .info-item span { font-weight:bold; font-size:15px; }
        .order-row { display:grid; grid-template-columns:80px 1fr auto auto; gap:16px; align-items:center; padding:16px 0; border-bottom:1px solid #f0f0f0; }
        .status-badge { padding:4px 12px; border-radius:20px; font-size:12px; font-weight:bold; color:#fff; }
        .empty-orders { text-align:center; padding:40px; color:#888; }
    </style>
</head>
<body>
<?php include 'shares/header.php'; ?>
<nav class="menu">
    <a href="<?= $baseUrl ?>/Product/list">🏠 Trang chủ</a>
    <a href="<?= $baseUrl ?>/Cart/view">Giỏ hàng</a>
</nav>

<div class="profile-wrap">
    <div class="profile-card">
        <h3><i class="fa-solid fa-user-circle"></i> Thông tin tài khoản</h3>
        <div class="info-grid">
            <div class="info-item"><label>Họ và tên</label><span><?= htmlspecialchars($user->getFullname()) ?></span></div>
            <div class="info-item"><label>Email</label><span><?= htmlspecialchars($user->getEmail()) ?></span></div>
            <div class="info-item"><label>Số điện thoại</label><span><?= htmlspecialchars($user->getPhone() ?: 'Chưa cập nhật') ?></span></div>
            <div class="info-item"><label>Ngày đăng ký</label><span><?= date('d/m/Y', strtotime($user->getCreatedAt())) ?></span></div>
        </div>
    </div>

    <div class="profile-card">
        <h3><i class="fa-solid fa-box"></i> Lịch sử đơn hàng (<?= count($orders) ?>)</h3>
        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <div style="font-size:48px; margin-bottom:12px;">📦</div>
                <p>Bạn chưa có đơn hàng nào.</p>
                <a href="<?= $baseUrl ?>/Product/list" style="color:#e00; display:inline-block; margin-top:10px;">Mua sắm ngay →</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $o): ?>
                <div class="order-row">
                    <div>
                        <div style="font-weight:bold;font-size:13px;">#<?= $o['id'] ?></div>
                        <div style="font-size:11px;color:#888;"><?= date('d/m/Y', strtotime($o['created_at'])) ?></div>
                    </div>
                    <div>
                        <div style="font-weight:bold;"><?= htmlspecialchars($o['fullname']) ?></div>
                        <div style="font-size:13px;color:#666;"><?= htmlspecialchars($o['city']) ?></div>
                    </div>
                    <div style="font-weight:bold;color:#e00;"><?= number_format($o['total'],0,',','.') ?>₫</div>
                    <div>
                        <?php $st = $o['status']; ?>
                        <span class="status-badge" style="background:<?= $statusColor[$st] ?? '#888' ?>">
                            <?= $statusLabel[$st] ?? $st ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
