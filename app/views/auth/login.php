<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập - HUY TOAN STORE</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .auth-wrap { min-height:80vh; display:flex; align-items:center; justify-content:center; padding:40px 20px; }
        .auth-card { background:#fff; border-radius:20px; padding:40px; width:100%; max-width:440px; box-shadow:0 8px 30px rgba(0,0,0,.10); }
        .auth-card h2 { font-size:26px; margin-bottom:6px; }
        .auth-card p.sub { color:#888; margin-bottom:28px; font-size:14px; }
        .form-group { margin-bottom:18px; }
        .form-group label { display:block; font-weight:bold; margin-bottom:6px; font-size:14px; color:#444; }
        .form-group input { width:100%; padding:12px 16px; border:2px solid #eee; border-radius:10px; font-size:15px; outline:none; transition:.2s; }
        .form-group input:focus { border-color:#ffd400; }
        .btn-auth { width:100%; padding:14px; background:#ffd400; border:none; border-radius:12px; font-size:16px; font-weight:bold; cursor:pointer; margin-top:6px; transition:.2s; }
        .btn-auth:hover { background:#e6be00; }
        .divider { text-align:center; color:#aaa; margin:20px 0; font-size:14px; }
        .alt-link { text-align:center; font-size:14px; }
        .alt-link a { color:#0d6efd; text-decoration:none; font-weight:bold; }
        .alert-err { background:#fff0f0; border:1px solid #fca5a5; border-radius:10px; padding:12px 16px; margin-bottom:18px; }
        .alert-err li { color:#c00; font-size:14px; margin-left:16px; }
        .alert-ok { background:#f0fff4; border:1px solid #86efac; border-radius:10px; padding:12px 16px; margin-bottom:18px; color:#166534; font-size:14px; }
        .voucher-hint { background:#fff9e0; border:1px solid #ffd400; border-radius:10px; padding:10px 14px; font-size:13px; margin-bottom:16px; }
    </style>
</head>
<body>
<?php include 'shares/header.php'; ?>
<nav class="menu">
    <a href="<?= $baseUrl ?>/Product/list">🏠 Trang chủ</a>
    <a href="<?= $baseUrl ?>/Auth/register">Đăng ký</a>
</nav>

<div class="auth-wrap">
    <div class="auth-card">
        <h2>Đăng nhập</h2>
        <p class="sub">Chào mừng bạn trở lại! Vui lòng đăng nhập.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert-err"><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['register_success'])): ?>
            <div class="alert-ok">✅ <?= htmlspecialchars($_SESSION['register_success']) ?></div>
            <?php unset($_SESSION['register_success']); ?>
        <?php endif; ?>

        <div class="voucher-hint">💡 <strong>Demo voucher:</strong> GIAM10 | SALE50K | VIP100K | FREESHIP</div>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="example@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-auth">Đăng nhập</button>
        </form>

        <div class="divider">— hoặc —</div>
        <div class="alt-link">Chưa có tài khoản? <a href="<?= $baseUrl ?>/Auth/register">Đăng ký ngay</a></div>
    </div>
</div>
</body>
</html>
