<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';
$statusLabels = [
    'pending'    => ['label'=>'Chờ xử lý',   'color'=>'#f59e0b','bg'=>'#fef3c7'],
    'processing' => ['label'=>'Đang xử lý',  'color'=>'#3b82f6','bg'=>'#dbeafe'],
    'shipped'    => ['label'=>'Đang giao',    'color'=>'#8b5cf6','bg'=>'#ede9fe'],
    'delivered'  => ['label'=>'Đã giao',      'color'=>'#16a34a','bg'=>'#dcfce7'],
    'cancelled'  => ['label'=>'Đã hủy',       'color'=>'#dc2626','bg'=>'#fee2e2'],
];
$sl = $statusLabels[$order->status] ?? ['label'=>$order->status,'color'=>'#888','bg'=>'#eee'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng #<?= $order->id ?> - HUY TOAN STORE</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body{background:#f5f5f5}
        .wrap{max-width:860px;margin:32px auto;padding:0 20px}
        .back-link{display:inline-flex;align-items:center;gap:6px;margin-bottom:18px;color:#555;text-decoration:none;font-size:14px;font-weight:600}
        .back-link:hover{color:#ffd400}
        .card{background:#fff;border-radius:16px;box-shadow:0 4px 18px rgba(0,0,0,.07);padding:28px;margin-bottom:20px}
        .card-title{font-size:16px;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:8px;border-bottom:2px solid #f0f0f0;padding-bottom:12px}
        .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
        .info-item label{font-size:12px;color:#888;display:block;margin-bottom:3px}
        .info-item span{font-size:14px;font-weight:600}
        .items-table{width:100%;border-collapse:collapse}
        .items-table th{background:#fafafa;padding:10px 12px;text-align:left;font-size:13px;color:#555;border-bottom:2px solid #f0f0f0}
        .items-table td{padding:12px;border-bottom:1px solid #f5f5f5;font-size:14px;vertical-align:middle}
        .items-table img{width:50px;height:50px;object-fit:cover;border-radius:8px}
        .summary-box{text-align:right;margin-top:16px;font-size:14px}
        .summary-box div{margin-bottom:6px}
        .summary-box .total-row{font-size:18px;font-weight:800;color:#e53e3e;border-top:2px solid #f0f0f0;padding-top:10px;margin-top:10px}
        .status-badge{display:inline-block;padding:5px 14px;border-radius:20px;font-size:13px;font-weight:700}
    </style>
</head>
<body>
<?php include 'shares/header.php'; ?>
<nav class="menu">
    <a href="<?= $baseUrl ?>/Product/list">&#127968; Trang chủ</a>
    <a href="<?= $baseUrl ?>/Order/list">&#128230; Đơn hàng</a>
</nav>

<div class="wrap">
    <a href="?url=Order/list" class="back-link"><i class="fa-solid fa-arrow-left"></i> Quay lại danh sách</a>

    <!-- Header -->
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
            <div>
                <h2 style="font-size:22px;margin:0">Đơn hàng <span style="color:#ffd400">#<?= $order->id ?></span></h2>
                <p style="color:#888;font-size:13px;margin:6px 0 0">Ngày đặt: <?= date('d/m/Y H:i', strtotime($order->created_at)) ?></p>
            </div>
            <span class="status-badge" style="background:<?=$sl['bg']?>;color:<?=$sl['color']?>"><?=$sl['label']?></span>
        </div>
    </div>

    <!-- Thông tin giao hàng -->
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-truck" style="color:#ffd400"></i> Thông tin giao hàng</div>
        <div class="info-grid">
            <div class="info-item"><label>Người nhận</label><span><?= htmlspecialchars($order->fullname) ?></span></div>
            <div class="info-item"><label>Số điện thoại</label><span><?= htmlspecialchars($order->phone) ?></span></div>
            <div class="info-item"><label>Thành phố</label><span><?= htmlspecialchars($order->city) ?></span></div>
            <div class="info-item"><label>Địa chỉ</label><span><?= htmlspecialchars($order->address) ?></span></div>
            <div class="info-item"><label>Phương thức vận chuyển</label><span><?= $order->shipping_method === 'express' ? '🚀 Giao nhanh' : '📦 Giao tiêu chuẩn' ?></span></div>
            <div class="info-item"><label>Thanh toán</label><span><?= $order->payment_method === 'cod' ? '💵 Tiền mặt (COD)' : '🏦 Chuyển khoản' ?></span></div>
            <?php if ($order->note): ?>
            <div class="info-item" style="grid-column:1/-1"><label>Ghi chú</label><span><?= htmlspecialchars($order->note) ?></span></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sản phẩm -->
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-box" style="color:#ffd400"></i> Sản phẩm đặt mua</div>
        <table class="items-table">
            <thead><tr><th>Sản phẩm</th><th>Đơn giá</th><th>SL</th><th style="text-align:right">Thành tiền</th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td style="display:flex;align-items:center;gap:12px">
                    <?php if ($item['image']): ?>
                    <img src="<?= $baseUrl ?>/public/images/<?= htmlspecialchars($item['image']) ?>" alt="">
                    <?php else: ?>
                    <div style="width:50px;height:50px;background:#f0f0f0;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#ccc"><i class="fa-solid fa-image"></i></div>
                    <?php endif; ?>
                    <span style="font-weight:600"><?= htmlspecialchars($item['name']) ?></span>
                </td>
                <td><?= number_format($item['price']) ?>₫</td>
                <td>x<?= $item['qty'] ?></td>
                <td style="text-align:right;font-weight:700"><?= number_format($item['price'] * $item['qty']) ?>₫</td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="summary-box">
            <?php $subtotal = array_sum(array_map(fn($i)=>$i['price']*$i['qty'], $items)); ?>
            <div>Tạm tính: <strong><?= number_format($subtotal) ?>₫</strong></div>
            <?php if (!empty($order->discount) && $order->discount > 0): ?>
            <div style="color:#16a34a">Giảm giá: <strong>-<?= number_format($order->discount) ?>₫</strong></div>
            <?php endif; ?>
            <div>Phí vận chuyển: <strong><?= $order->shipping_method === 'express' ? number_format(150000).'₫' : 'Miễn phí' ?></strong></div>
            <div class="total-row">Tổng cộng: <?= number_format($order->total) ?>₫</div>
        </div>
    </div>

    <?php if ($isAdmin): ?>
    <!-- Cập nhật trạng thái (admin) -->
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-pen-to-square" style="color:#6366f1"></i> Cập nhật trạng thái</div>
        <form method="POST" action="?url=Order/updateStatus" style="display:flex;gap:12px;align-items:center">
            <input type="hidden" name="order_id" value="<?= $order->id ?>">
            <select name="status" style="padding:10px 14px;border:2px solid #eee;border-radius:10px;font-size:14px;font-weight:600;outline:none">
                <?php foreach(['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                <option value="<?=$s?>" <?=$order->status===$s?'selected':''?>><?=$statusLabels[$s]['label']??$s?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" style="padding:10px 22px;background:#ffd400;border:none;border-radius:10px;font-weight:700;cursor:pointer">Cập nhật</button>
        </form>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
