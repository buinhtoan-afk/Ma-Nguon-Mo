<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
?>
<!DOCTYPE html><html lang="vi"><head>
<meta charset="UTF-8"><title>Đặt lại mật khẩu - HUY TOAN STORE</title>
<link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
.auth-wrap{min-height:80vh;display:flex;align-items:center;justify-content:center;padding:40px 20px;background:#f5f5f5}
.auth-card{background:#fff;border-radius:20px;padding:40px;width:100%;max-width:440px;box-shadow:0 8px 30px rgba(0,0,0,.10)}
.auth-card h2{font-size:24px;margin-bottom:6px}
.auth-card p.sub{color:#888;font-size:14px;margin-bottom:24px}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-weight:bold;margin-bottom:6px;font-size:14px;color:#444}
.form-group input{width:100%;padding:12px 16px;border:2px solid #eee;border-radius:10px;font-size:15px;outline:none;transition:.2s;box-sizing:border-box}
.form-group input:focus{border-color:#ffd400}
.btn-auth{width:100%;padding:14px;background:#ffd400;border:none;border-radius:12px;font-size:16px;font-weight:bold;cursor:pointer;transition:.2s}
.btn-auth:hover{background:#e6be00}
.alert-err{background:#fff0f0;border:1px solid #fca5a5;border-radius:10px;padding:12px 16px;margin-bottom:16px}
.alert-err li{color:#c00;font-size:14px;margin-left:16px}
.strength-bar{height:4px;border-radius:4px;margin-top:6px;transition:.3s;background:#eee}
</style>
</head>
<body>
<?php include 'shares/header.php'; ?>
<nav class="menu"><a href="<?= $baseUrl ?>/Product/list">🏠 Trang chủ</a></nav>
<div class="auth-wrap">
  <div class="auth-card">
    <h2><i class="fa-solid fa-lock"></i> Đặt lại mật khẩu</h2>
    <p class="sub">Nhập mật khẩu mới cho tài khoản của bạn.</p>

    <?php if (!empty($errors)): ?>
      <div class="alert-err"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Mật khẩu mới</label>
        <input type="password" name="password" id="pw" placeholder="Tối thiểu 6 ký tự" required>
        <div class="strength-bar" id="strength-bar"></div>
      </div>
      <div class="form-group">
        <label>Xác nhận mật khẩu mới</label>
        <input type="password" name="confirm" placeholder="Nhập lại mật khẩu mới" required>
      </div>
      <button type="submit" class="btn-auth"><i class="fa-solid fa-floppy-disk"></i> Lưu mật khẩu mới</button>
    </form>
  </div>
</div>
<script>
document.getElementById('pw').addEventListener('input', function(){
  const v = this.value, bar = document.getElementById('strength-bar');
  if (v.length < 6)      { bar.style.background='#ef4444'; bar.style.width='30%'; }
  else if (v.length < 10) { bar.style.background='#f59e0b'; bar.style.width='60%'; }
  else                    { bar.style.background='#22c55e'; bar.style.width='100%'; }
});
</script>
</body></html>
