<?php
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$shipping_fee = (($_POST['shipping_method'] ?? 'standard') === 'express') ? 150000 : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán - HUY TOAN STORE</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .checkout-wrap { max-width:1100px; margin:40px auto; padding:0 20px; display:grid; grid-template-columns:1fr 360px; gap:28px; align-items:start; }
        .page-head { padding:30px 20px 0; max-width:1100px; margin:0 auto; }
        h1.page-title { font-size:30px; margin-bottom:6px; }
        .page-sub { color:#888; font-size:14px; }
        .section-card { background:#fff; border-radius:18px; padding:28px; box-shadow:0 4px 20px rgba(0,0,0,.08); margin-bottom:20px; }
        .section-card h3 { font-size:17px; font-weight:bold; margin-bottom:20px; display:flex; align-items:center; gap:8px; }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .form-full { grid-column:1/-1; }
        .form-group { margin-bottom:0; }
        .form-group label { display:block; font-size:13px; color:#666; margin-bottom:6px; font-weight:bold; }
        .form-group input, .form-group select, .form-group textarea {
            width:100%; padding:12px 14px; border:2px solid #eee; border-radius:10px; font-size:14px; outline:none; transition:.2s; font-family:inherit;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color:#ffd400; }

        /* SHIPPING */
        .ship-options { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .ship-opt { border:2px solid #eee; border-radius:12px; padding:16px; cursor:pointer; transition:.2s; display:flex; justify-content:space-between; align-items:center; }
        .ship-opt.selected { border-color:#ffd400; background:#fff9e0; }
        .ship-opt-info { font-size:13px; color:#888; margin-top:2px; }
        .ship-opt input[type=radio] { display:none; }

        /* PAYMENT */
        .pay-opts { display:flex; flex-direction:column; gap:10px; }
        .pay-opt { border:2px solid #eee; border-radius:12px; padding:16px 20px; cursor:pointer; transition:.2s; display:flex; align-items:center; gap:12px; }
        .pay-opt.selected { border-color:#ffd400; background:#fff9e0; }
        .pay-opt input[type=radio] { accent-color:#ffd400; width:18px; height:18px; }
        .pay-opt-icon { font-size:22px; }
        .card-fields { margin-top:14px; display:grid; grid-template-columns:1fr 1fr; gap:10px; }
        .card-fields-wrap { display:none; }
        .card-fields-wrap.show { display:block; }

        /* SUMMARY */
        .sum-card { background:#fff; border-radius:18px; padding:24px; box-shadow:0 4px 20px rgba(0,0,0,.08); position:sticky; top:20px; }
        .sum-card h3 { font-size:17px; margin-bottom:16px; }
        .sum-item { display:flex; gap:12px; margin-bottom:14px; align-items:center; }
        .sum-item img { width:52px; height:52px; object-fit:contain; border-radius:8px; background:#f5f5f5; }
        .sum-item-name { font-size:13px; font-weight:bold; }
        .sum-item-qty { font-size:12px; color:#888; }
        .sum-item-price { font-size:13px; font-weight:bold; color:#e00; margin-left:auto; white-space:nowrap; }
        .sum-divider { border:none; border-top:1px solid #f0f0f0; margin:14px 0; }
        .sum-row { display:flex; justify-content:space-between; font-size:14px; margin-bottom:10px; }
        .sum-row.total { font-size:18px; font-weight:bold; }
        .sum-row.total .val { color:#e00; font-size:22px; }
        .sum-row.discount { color:#16a34a; }
        .btn-order { width:100%; padding:16px; background:#ffd400; border:none; border-radius:12px; font-size:16px; font-weight:bold; cursor:pointer; margin:16px 0 8px; }
        .btn-order:hover { background:#e6be00; }
        .terms { font-size:12px; color:#888; text-align:center; }
        .terms a { color:#0d6efd; }
        .alert-err { background:#fff0f0; border:1px solid #fca5a5; border-radius:10px; padding:12px 16px; margin-bottom:16px; }
        .alert-err li { color:#c00; font-size:14px; margin-left:16px; }
    </style>
</head>
<body>
<?php include 'shares/header.php'; ?>
<nav class="menu">
    <a href="<?= $baseUrl ?>/Product/list">🏠 Trang chủ</a>
    <a href="<?= $baseUrl ?>/Cart/view">← Giỏ hàng</a>
</nav>

<div class="page-head">
    <h1 class="page-title">Thanh Toán</h1>
    <p class="page-sub">Vui lòng kiểm tra lại thông tin đơn hàng và địa chỉ giao hàng.</p>
</div>

<form method="POST" id="checkoutForm">
<div class="checkout-wrap">

    <!-- LEFT COLUMN -->
    <div>

        <?php if (!empty($errors)): ?>
            <div class="alert-err"><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <!-- SHIPPING INFO -->
        <div class="section-card">
            <h3><i class="fa-solid fa-truck"></i> Thông tin giao hàng</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Họ và tên</label>
                    <input type="text" name="fullname" placeholder="Nguyễn Văn A"
                           value="<?= htmlspecialchars($_POST['fullname'] ?? ($isLogged ? $_SESSION['user_name'] : '')) ?>" required>
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" placeholder="090 123 4567"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                </div>
                <div class="form-group form-full">
                    <label>Địa chỉ chi tiết</label>
                    <input type="text" name="address" placeholder="Số nhà, tên đường, phường/xã..."
                           value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Thành phố / Tỉnh</label>
                    <select name="city">
                        <option value="">-- Chọn thành phố --</option>
                        <?php foreach ($cities as $c): ?>
                            <option value="<?= $c ?>" <?= (($_POST['city'] ?? '') === $c) ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ghi chú (Tùy chọn)</label>
                    <input type="text" name="note" placeholder="Giao hàng vào giờ hành chính"
                           value="<?= htmlspecialchars($_POST['note'] ?? '') ?>">
                </div>
            </div>
        </div>

        <!-- SHIPPING METHOD -->
        <div class="section-card">
            <h3><i class="fa-solid fa-clock"></i> Phương thức vận chuyển</h3>
            <div class="ship-options">
                <div class="ship-opt selected" id="shipStd" onclick="selectShip('standard')">
                    <div>
                        <div style="font-weight:bold;">Giao hàng tiêu chuẩn</div>
                        <div class="ship-opt-info">3-5 ngày làm việc</div>
                    </div>
                    <div style="color:#16a34a;font-weight:bold;">Miễn phí</div>
                    <input type="radio" name="shipping_method" value="standard" checked>
                </div>
                <div class="ship-opt" id="shipExp" onclick="selectShip('express')">
                    <div>
                        <div style="font-weight:bold;">Giao hàng hỏa tốc</div>
                        <div class="ship-opt-info">Trong vòng 24 giờ</div>
                    </div>
                    <div style="font-weight:bold;">150.000₫</div>
                    <input type="radio" name="shipping_method" value="express">
                </div>
            </div>
        </div>

        <!-- PAYMENT METHOD -->
        <div class="section-card">
            <h3><i class="fa-solid fa-credit-card"></i> Phương thức thanh toán</h3>
            <div class="pay-opts">

                <label class="pay-opt selected" id="payCard" onclick="selectPay('card')">
                    <input type="radio" name="payment_method" value="card">
                    <span class="pay-opt-icon">💳</span>
                    <div>
                        <div style="font-weight:bold;">Thẻ tín dụng / Ghi nợ</div>
                        <div style="font-size:12px;color:#888;">Visa, Mastercard</div>
                    </div>
                    <div style="margin-left:auto;display:flex;gap:6px;opacity:.6;">
                        <i class="fa-brands fa-cc-visa fa-lg"></i>
                        <i class="fa-brands fa-cc-mastercard fa-lg"></i>
                    </div>
                </label>

                <!-- Card fields -->
                <div class="card-fields-wrap show" id="cardFields">
                    <div style="border:2px solid #eee;border-radius:12px;padding:16px;">
                        <div class="form-group" style="margin-bottom:12px;">
                            <label>Số thẻ</label>
                            <input type="text" name="card_number" placeholder="0000 0000 0000 0000" maxlength="19"
                                   oninput="this.value=this.value.replace(/\D/g,'').replace(/(.{4})/g,'$1 ').trim()">
                        </div>
                        <div class="card-fields">
                            <div class="form-group">
                                <label>Ngày hết hạn</label>
                                <input type="text" name="card_exp" placeholder="MM/YY" maxlength="5"
                                       oninput="this.value=this.value.replace(/\D/g,'').replace(/(\d{2})(\d)/,'$1/$2')">
                            </div>
                            <div class="form-group">
                                <label>CVC</label>
                                <input type="text" name="card_cvc" placeholder="123" maxlength="3"
                                       oninput="this.value=this.value.replace(/\D/g,'')">
                            </div>
                        </div>
                    </div>
                </div>

                <label class="pay-opt" id="payBank" onclick="selectPay('bank')">
                    <input type="radio" name="payment_method" value="bank">
                    <span class="pay-opt-icon"><i class="fa-solid fa-building-columns"></i></span>
                    <div>
                        <div style="font-weight:bold;">Chuyển khoản ngân hàng</div>
                        <div style="font-size:12px;color:#888;">STK: 1234 5678 9012 - Ngân hàng ABC</div>
                    </div>
                </label>

                <label class="pay-opt" id="payWallet" onclick="selectPay('wallet')">
                    <input type="radio" name="payment_method" value="wallet">
                    <span class="pay-opt-icon"><i class="fa-solid fa-wallet"></i></span>
                    <div>
                        <div style="font-weight:bold;">Ví điện tử (Momo, ZaloPay)</div>
                        <div style="font-size:12px;color:#888;">Quét mã QR để thanh toán</div>
                    </div>
                </label>

            </div>
        </div>
    </div>

    <!-- RIGHT: summary -->
    <div class="sum-card">
        <h3>Tóm tắt đơn hàng</h3>

        <?php foreach ($cartItems as $item): ?>
        <div class="sum-item">
            <img src="<?= $baseUrl ?>/public/images/<?= htmlspecialchars($item['image'] ?? 'default.jpg') ?>"
                 onerror="this.src='https://placehold.co/52x52?text=SP'">
            <div>
                <div class="sum-item-name"><?= htmlspecialchars(mb_substr($item['name'], 0, 35)) ?><?= mb_strlen($item['name']) > 35 ? '...' : '' ?></div>
                <div class="sum-item-qty">Số lượng: <?= $item['qty'] ?></div>
            </div>
            <div class="sum-item-price"><?= number_format($item['price']*$item['qty'],0,',','.') ?>₫</div>
        </div>
        <?php endforeach; ?>

        <hr class="sum-divider">

        <div class="sum-row">
            <span>Tạm tính</span>
            <span><?= number_format($subtotal,0,',','.') ?>₫</span>
        </div>
        <?php if ($discount > 0): ?>
        <div class="sum-row discount">
            <span>Giảm giá</span>
            <span>-<?= number_format($discount,0,',','.') ?>₫</span>
        </div>
        <?php endif; ?>
        <div class="sum-row" id="shipFeeRow">
            <span>Phí vận chuyển</span>
            <span id="shipFeeVal" style="color:#16a34a;font-weight:bold;">Miễn phí</span>
        </div>

        <hr class="sum-divider">

        <div class="sum-row total">
            <span>Tổng cộng</span>
            <span class="val" id="grandTotal"><?= number_format($subtotal - $discount, 0, ',', '.') ?>₫</span>
        </div>

        <button type="submit" class="btn-order">Đặt Hàng Ngay →</button>
        <p class="terms">Bằng cách đặt hàng, bạn đồng ý với các <a href="#">Điều khoản dịch vụ</a> của chúng tôi.</p>
    </div>

</div>
</form>

<script>
var subtotal  = <?= $subtotal ?>;
var discount  = <?= $discount ?>;
var shipFee   = 0;

function fmt(n) { return n.toLocaleString('vi-VN') + '₫'; }
function updateTotal() {
    document.getElementById('grandTotal').textContent = fmt(Math.max(0, subtotal - discount + shipFee));
}

function selectShip(type) {
    document.getElementById('shipStd').classList.toggle('selected', type === 'standard');
    document.getElementById('shipExp').classList.toggle('selected', type === 'express');
    document.querySelector('[value="' + type + '"]').checked = true;
    shipFee = (type === 'express') ? 150000 : 0;
    var feeEl = document.getElementById('shipFeeVal');
    feeEl.textContent = shipFee > 0 ? fmt(shipFee) : 'Miễn phí';
    feeEl.style.color = shipFee > 0 ? '#333' : '#16a34a';
    updateTotal();
}

function selectPay(type) {
    ['payCard','payBank','payWallet'].forEach(function(id) {
        document.getElementById(id).classList.remove('selected');
    });
    document.getElementById('pay' + type.charAt(0).toUpperCase() + type.slice(1)).classList.add('selected');
    document.querySelector('[value="' + type + '"]').checked = true;
    document.getElementById('cardFields').classList.toggle('show', type === 'card');
}
</script>
</body>
</html>
