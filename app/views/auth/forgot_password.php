<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
?>
<!DOCTYPE html><html lang="vi"><head>
<meta charset="UTF-8"><title>Quên mật khẩu - HUY TOAN STORE</title>
<link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
.auth-wrap{min-height:80vh;display:flex;align-items:center;justify-content:center;padding:40px 20px;background:#f5f5f5}
.auth-card{background:#fff;border-radius:20px;padding:40px;width:100%;max-width:440px;box-shadow:0 8px 30px rgba(0,0,0,.10)}
.auth-card h2{font-size:24px;margin-bottom:6px}
.auth-card p.sub{color:#888;font-size:14px;margin-bottom:24px;line-height:1.6}
.form-group{margin-bottom:18px}
.form-group label{display:block;font-weight:bold;margin-bottom:6px;font-size:14px;color:#444}
.form-group input{width:100%;padding:12px 16px;border:2px solid #eee;border-radius:10px;font-size:15px;outline:none;transition:.2s;box-sizing:border-box}
.form-group input:focus{border-color:#ffd400}
.btn-auth{width:100%;padding:14px;background:#ffd400;border:none;border-radius:12px;font-size:16px;font-weight:bold;cursor:pointer;transition:.2s}
.btn-auth:hover{background:#e6be00}
.alert-ok{background:#f0fff4;border:1px solid #86efac;border-radius:10px;padding:16px;margin-bottom:18px;color:#166534;font-size:14px;line-height:1.6}
.alert-err{background:#fff0f0;border:1px solid #fca5a5;border-radius:10px;padding:12px 16px;margin-bottom:16px}
.alert-err li{color:#c00;font-size:14px;margin-left:16px}
.back-link{display:block;text-align:center;margin-top:18px;font-size:14px;color:#0d6efd;text-decoration:none}
.back-link:hover{text-decoration:underline}
.info-box{background:#e8f4fd;border:1px solid #90cdf4;border-radius:10px;padding:14px 16px;margin-bottom:20px;font-size:13px;color:#1a4a6e;line-height:1.7}
</style>
</head>
<body>
<?php include 'shares/header.php'; ?>
<nav class="menu"><a href="<?= $baseUrl ?>/Product/list">🏠 Trang chủ</a></nav>
<div class="auth-wrap">
  <div class="auth-card">
    <h2><i class="fa-solid fa-key"></i> Quên mật khẩu</h2>
    <p class="sub">Nhập email tài khoản của bạn, chúng tôi sẽ gửi link đặt lại mật khẩu.</p>

    <?php if (!empty($errors)): ?>
      <div class="alert-err"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <?php if (!empty($sent)): ?>
      <div class="alert-ok">
        <strong>✅ Đã gửi!</strong><br>
        Nếu email tồn tại trong hệ thống, bạn sẽ nhận được link đặt lại mật khẩu.<br><br>
        <strong>Môi trường demo:</strong> Link được lưu tại <code>public/uploads/mail_log/</code>
      </div>
      <a href="<?= $baseUrl ?>/Auth/login" class="back-link">← Quay lại đăng nhập</a>
    <?php else: ?>
      <div class="info-box">
        <i class="fa-solid fa-circle-info"></i>
        Link đặt lại mật khẩu có hiệu lực trong <strong>1 giờ</strong>.
      </div>
      <form method="POST">
        <div class="form-group">
          <label>Email tài khoản</label>
          <input type="email" name="email" placeholder="example@email.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        <button type="submit" class="btn-auth"><i class="fa-solid fa-paper-plane"></i> Gửi link đặt lại</button>
      </form>
      <a href="<?= $baseUrl ?>/Auth/login" class="back-link">← Quay lại đăng nhập</a>
    <?php endif; ?>
  </div>
</div>
</body></html>
