<?php
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$discount = $_SESSION['discount_amount'] ?? 0;
$voucher  = $_SESSION['voucher_code']    ?? '';
$shipping_fee = 0; // default standard
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng - HUY TOAN STORE</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .cart-wrap { max-width:1100px; margin:40px auto; padding:0 20px; display:grid; grid-template-columns:1fr 360px; gap:28px; align-items:start; }
        h1.page-title { font-size:30px; margin:0 0 6px; }
        .page-sub { color:#888; margin-bottom:0; font-size:14px; }
        .page-head { padding:30px 20px 0; max-width:1100px; margin:0 auto; }
        .cart-table { background:#fff; border-radius:18px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.08); }
        .cart-item { display:grid; grid-template-columns:90px 1fr auto auto; gap:16px; align-items:center; padding:20px 24px; border-bottom:1px solid #f0f0f0; }
        .cart-item:last-child { border-bottom:none; }
        .cart-item img { width:80px; height:80px; object-fit:contain; border-radius:10px; background:#f5f5f5; }
        .item-name { font-weight:bold; font-size:15px; margin-bottom:4px; }
        .item-price-orig { font-size:12px; color:#aaa; text-decoration:line-through; }
        .item-price { font-size:16px; font-weight:bold; color:#e00; }
        .qty-ctrl { display:flex; align-items:center; gap:8px; }
        .qty-ctrl button { width:30px; height:30px; border:2px solid #ddd; border-radius:8px; background:#fff; font-size:16px; cursor:pointer; font-weight:bold; transition:.15s; }
        .qty-ctrl button:hover { border-color:#ffd400; background:#ffd400; }
        .qty-ctrl input { width:48px; text-align:center; border:2px solid #ddd; border-radius:8px; padding:4px; font-size:15px; font-weight:bold; }
        .item-total { font-size:18px; font-weight:bold; min-width:120px; text-align:right; }
        .remove-btn { background:none; border:none; color:#ccc; cursor:pointer; font-size:20px; padding:4px 8px; border-radius:8px; transition:.15s; }
        .remove-btn:hover { color:#e00; background:#fff0f0; }
        .cart-actions { padding:16px 24px; display:flex; justify-content:space-between; align-items:center; }
        .btn-continue { text-decoration:none; color:#333; font-size:14px; }
        .btn-continue:hover { color:#e00; }
        .btn-clear { background:none; border:2px solid #ddd; border-radius:10px; padding:8px 16px; cursor:pointer; color:#888; font-size:13px; }
        .btn-clear:hover { border-color:#e00; color:#e00; }

        /* ORDER SUMMARY */
        .summary-card { background:#fff; border-radius:18px; padding:28px; box-shadow:0 4px 20px rgba(0,0,0,.08); position:sticky; top:20px; }
        .summary-card h3 { font-size:18px; margin-bottom:20px; }
        .summary-row { display:flex; justify-content:space-between; margin-bottom:12px; font-size:15px; }
        .summary-row.discount { color:#16a34a; font-weight:bold; }
        .summary-row.total { font-size:20px; font-weight:bold; border-top:2px solid #f0f0f0; padding-top:16px; margin-top:4px; }
        .summary-row.total .amount { color:#e00; font-size:24px; }
        .summary-row .vat { font-size:11px; color:#888; display:block; }
        .btn-checkout { width:100%; padding:16px; background:#ffd400; border:none; border-radius:12px; font-size:16px; font-weight:bold; cursor:pointer; margin-bottom:12px; }
        .btn-checkout:hover { background:#e6be00; }

        /* VOUCHER */
        .voucher-box { border:2px dashed #ddd; border-radius:12px; padding:16px; margin-top:20px; }
        .voucher-box label { font-size:13px; color:#666; margin-bottom:8px; display:block; }
        .voucher-input-row { display:flex; gap:8px; }
        .voucher-input-row input { flex:1; padding:10px 12px; border:2px solid #ddd; border-radius:10px; font-size:14px; outline:none; }
        .voucher-input-row input:focus { border-color:#ffd400; }
        .btn-apply { padding:10px 16px; background:#333; color:#fff; border:none; border-radius:10px; cursor:pointer; font-size:14px; font-weight:bold; white-space:nowrap; }
        .btn-apply:hover { background:#000; }
        .voucher-msg { font-size:13px; margin-top:8px; }
        .voucher-msg.ok { color:#16a34a; }
        .voucher-msg.err { color:#e00; }

        /* PAYMENT ICONS */
        .pay-icons { display:flex; gap:10px; justify-content:center; margin-top:16px; opacity:.5; }

        /* SUGGESTIONS */
        .suggestions { max-width:1100px; margin:40px auto 20px; padding:0 20px; }
        .suggestions h3 { font-size:20px; margin-bottom:16px; }
        .sug-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
        .sug-card { background:#fff; border-radius:14px; padding:14px; box-shadow:0 2px 10px rgba(0,0,0,.07); }
        .sug-card img { width:100%; height:140px; object-fit:contain; margin-bottom:10px; }
        .sug-card h4 { font-size:14px; margin-bottom:4px; }
        .sug-card .price { color:#e00; font-weight:bold; font-size:15px; margin-bottom:10px; }
        .btn-add-sug { width:100%; padding:9px; background:#ffd400; border:none; border-radius:10px; cursor:pointer; font-weight:bold; font-size:13px; }
        .btn-add-sug:hover { background:#e6be00; }
        .empty-cart { text-align:center; padding:80px 20px; }
    </style>
</head>
<body>
<?php include 'shares/header.php'; ?>
<nav class="menu">
    <a href="<?= $baseUrl ?>/Product/list">🏠 Trang chủ</a>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?= $baseUrl ?>/Auth/profile">Hồ sơ</a>
    <?php else: ?>
        <a href="<?= $baseUrl ?>/Auth/login">Đăng nhập</a>
    <?php endif; ?>
</nav>

<div class="page-head">
    <h1 class="page-title">Giỏ hàng của bạn</h1>
    <p class="page-sub">Kiểm tra lại các sản phẩm và tiến hành thanh toán.</p>
</div>

<?php if (empty($products)): ?>
<div class="empty-cart">
    <div style="font-size:64px; margin-bottom:16px;">🛒</div>
    <h2 style="color:#666;">Giỏ hàng của bạn đang trống</h2>
    <a href="<?= $baseUrl ?>/Product/list" style="display:inline-block; margin-top:20px; background:#ffd400; padding:14px 30px; border-radius:12px; font-weight:bold; text-decoration:none; color:#000;">← Tiếp tục mua sắm</a>
</div>

<?php else: ?>

<div class="cart-wrap">
    <!-- LEFT: cart items -->
    <div>
        <form method="POST" action="<?= $baseUrl ?>/Cart/update" id="cartForm">
        <div class="cart-table">
            <?php foreach ($products as $item):
                $itemTotal = $item['price'] * $item['qty'];
            ?>
            <div class="cart-item">
                <img src="<?= $baseUrl ?>/public/images/<?= htmlspecialchars($item['image'] ?? 'default.jpg') ?>"
                     onerror="this.src='https://placehold.co/80x80?text=SP'">
                <div>
                    <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                    <div class="item-price"><?= number_format($item['price'],0,',','.') ?>₫</div>
                </div>
                <div class="qty-ctrl">
                    <button type="button" onclick="changeQty(<?= $item['id'] ?>, -1)">−</button>
                    <input type="number" name="qty[<?= $item['id'] ?>]" id="qty_<?= $item['id'] ?>"
                           value="<?= $item['qty'] ?>" min="1" max="99"
                           onchange="recalc()">
                    <button type="button" onclick="changeQty(<?= $item['id'] ?>, 1)">+</button>
                </div>
                <div style="text-align:right;">
                    <div class="item-total" id="total_<?= $item['id'] ?>"><?= number_format($itemTotal,0,',','.') ?>₫</div>
                    <a href="<?= $baseUrl ?>/Cart/remove/<?= $item['id'] ?>" class="remove-btn" title="Xóa" onclick="return confirm('Xóa sản phẩm này?')">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="cart-actions">
                <a href="<?= $baseUrl ?>/Product/list" class="btn-continue">← Tiếp tục mua sắm</a>
                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn-clear">🔄 Cập nhật giỏ</button>
                    <a href="<?= $baseUrl ?>/Cart/clear" class="btn-clear" onclick="return confirm('Xóa toàn bộ giỏ hàng?')">🗑️ Xóa tất cả</a>
                </div>
            </div>
        </div>
        </form>
    </div>

    <!-- RIGHT: summary -->
    <div class="summary-card">
        <h3>Tóm tắt đơn hàng</h3>

        <div class="summary-row">
            <span>Tổng tiền sản phẩm</span>
            <span id="subtotalDisplay"><?= number_format($subtotal,0,',','.') ?>₫</span>
        </div>
        <div class="summary-row discount" id="discountRow" style="<?= $discount > 0 ? '' : 'display:none' ?>">
            <span>Giảm giá <?= $voucher ? "($voucher)" : '' ?></span>
            <span>-<span id="discountDisplay"><?= number_format($discount,0,',','.') ?></span>₫</span>
        </div>
        <div class="summary-row">
            <span>Phí vận chuyển</span>
            <span style="color:#16a34a;font-weight:bold;">Miễn phí</span>
        </div>
        <div class="summary-row total">
            <span>Tổng cộng</span>
            <div>
                <span class="amount" id="totalDisplay"><?= number_format($subtotal - $discount, 0, ',', '.') ?>₫</span>
                <span class="vat">Đã bao gồm VAT</span>
            </div>
        </div>

        <a href="<?= $baseUrl ?>/Order/checkout">
            <button class="btn-checkout">Tiến hành thanh toán →</button>
        </a>

        <!-- VOUCHER -->
        <div class="voucher-box">
            <label><i class="fa-solid fa-tag"></i> Mã giảm giá / Voucher</label>
            <div class="voucher-input-row">
                <input type="text" id="voucherInput" placeholder="Nhập mã..." value="<?= htmlspecialchars($voucher) ?>">
                <button class="btn-apply" onclick="applyVoucher()">Áp dụng</button>
            </div>
            <div class="voucher-msg" id="voucherMsg" style="display:<?= $voucher ? 'block' : 'none' ?>; color:#16a34a;">
                <?= $voucher ? "✅ Đang áp dụng: $voucher" : '' ?>
            </div>
        </div>

        <div class="pay-icons">
            <i class="fa-brands fa-cc-visa fa-2x"></i>
            <i class="fa-brands fa-cc-mastercard fa-2x"></i>
            <i class="fa-solid fa-building-columns fa-2x"></i>
            <i class="fa-solid fa-wallet fa-2x"></i>
        </div>
    </div>
</div>

<?php endif; ?>

<!-- GỢI Ý SẢN PHẨM -->
<?php if (!empty($suggestions)): ?>
<div class="suggestions">
    <h3>Có thể bạn quan tâm <a href="<?= $baseUrl ?>/Product/list" style="font-size:14px;color:#0d6efd;text-decoration:none;float:right;font-weight:normal;">Xem tất cả ↗</a></h3>
    <div class="sug-grid">
        <?php foreach ($suggestions as $s): ?>
        <div class="sug-card">
            <img src="<?= $baseUrl ?>/public/images/<?= htmlspecialchars($s['image'] ?? 'default.jpg') ?>"
                 onerror="this.src='https://placehold.co/200x200?text=SP'">
            <h4><?= htmlspecialchars($s['name']) ?></h4>
            <div class="price"><?= number_format($s['price'],0,',','.') ?>₫</div>
            <button class="btn-add-sug" onclick="addToCart(<?= $s['id'] ?>)">+ Thêm vào giỏ</button>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
const PRICES = {
    <?php foreach ($products as $item): ?>
    <?= $item['id'] ?>: <?= $item['price'] ?>,
    <?php endforeach; ?>
};
const SUBTOTAL_ORIG = <?= $subtotal ?>;
let discount = <?= $discount ?>;

function fmt(n) { return n.toLocaleString('vi-VN') + '₫'; }

function changeQty(id, delta) {
    var inp = document.getElementById('qty_' + id);
    var val = parseInt(inp.value) + delta;
    if (val < 1) val = 1;
    inp.value = val;
    recalc();
}

function recalc() {
    var sub = 0;
    <?php foreach ($products as $item): ?>
    (function() {
        var inp = document.getElementById('qty_<?= $item['id'] ?>');
        if (!inp) return;
        var qty = parseInt(inp.value) || 1;
        var tot = qty * PRICES[<?= $item['id'] ?>];
        sub += tot;
        var el = document.getElementById('total_<?= $item['id'] ?>');
        if (el) el.textContent = fmt(tot);
    })();
    <?php endforeach; ?>
    document.getElementById('subtotalDisplay').textContent = fmt(sub);
    document.getElementById('totalDisplay').textContent = fmt(Math.max(0, sub - discount));
}

function applyVoucher() {
    var code = document.getElementById('voucherInput').value.trim();
    var sub = SUBTOTAL_ORIG;
    fetch('<?= $baseUrl ?>/Order/applyVoucher', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'code=' + encodeURIComponent(code) + '&subtotal=' + sub
    })
    .then(r => r.json())
    .then(data => {
        var msg = document.getElementById('voucherMsg');
        msg.style.display = 'block';
        if (data.success) {
            discount = data.discount;
            msg.className = 'voucher-msg ok';
            msg.textContent = '✅ ' + data.message;
            document.getElementById('discountRow').style.display = 'flex';
            document.getElementById('discountDisplay').textContent = data.discount.toLocaleString('vi-VN');
            recalc();
        } else {
            msg.className = 'voucher-msg err';
            msg.textContent = '❌ ' + data.message;
        }
    });
}

function addToCart(id) {
    fetch('<?= $baseUrl ?>/Cart/add', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
        body: 'product_id=' + id + '&qty=1'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            var b = document.getElementById('cartBadge');
            if (b) { b.textContent = data.count; b.style.display = 'flex'; }
        }
    });
}
</script>
</body>
</html>
