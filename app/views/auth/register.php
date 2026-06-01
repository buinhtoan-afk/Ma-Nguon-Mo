<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
?>
<!DOCTYPE html><html lang="vi"><head>
<meta charset="UTF-8"><title>Đăng ký - HUY TOAN STORE</title>
<link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
.auth-wrap{min-height:80vh;display:flex;align-items:center;justify-content:center;padding:40px 20px;background:#f5f5f5}
.auth-card{background:#fff;border-radius:20px;padding:40px;width:100%;max-width:520px;box-shadow:0 8px 30px rgba(0,0,0,.10)}
.auth-card h2{font-size:26px;margin-bottom:4px}
.auth-card p.sub{color:#888;margin-bottom:22px;font-size:14px}
.form-group{margin-bottom:14px}
.form-group label{display:block;font-weight:bold;margin-bottom:6px;font-size:14px;color:#444}
.form-group input{width:100%;padding:11px 14px;border:2px solid #eee;border-radius:10px;font-size:14px;outline:none;transition:.2s;box-sizing:border-box}
.form-group input:focus{border-color:#ffd400}
.btn-auth{width:100%;padding:13px;background:#ffd400;border:none;border-radius:12px;font-size:16px;font-weight:bold;cursor:pointer;margin-top:4px;transition:.2s}
.btn-auth:hover{background:#e6be00}
.btn-auth.admin-mode{background:#e63946;color:#fff}
.btn-auth.admin-mode:hover{background:#c1121f}
.divider{text-align:center;color:#aaa;margin:18px 0;font-size:14px}
.alt-link{text-align:center;font-size:14px}
.alt-link a{color:#0d6efd;text-decoration:none;font-weight:bold}
.alert-err{background:#fff0f0;border:1px solid #fca5a5;border-radius:10px;padding:12px 16px;margin-bottom:16px}
.alert-err li{color:#c00;font-size:14px;margin-left:16px}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:500px){.row2{grid-template-columns:1fr}}
.role-toggle{display:flex;gap:10px;margin-bottom:18px}
.role-btn{flex:1;padding:11px 10px;border:2px solid #eee;border-radius:12px;background:#f8f8f8;cursor:pointer;font-size:14px;font-weight:600;color:#888;transition:.2s;text-align:center;user-select:none}
.role-btn i{display:block;font-size:20px;margin-bottom:4px}
.role-btn.active-user{border-color:#ffd400;background:#fffbe6;color:#b8860b}
.role-btn.active-admin{border-color:#e63946;background:#fff0f2;color:#c00}
#admin-code-wrap{display:none}
#admin-code-wrap.show{display:block}
.perm-box{border-radius:10px;padding:11px 14px;margin-bottom:14px;font-size:13px;line-height:1.7}
.perm-box.user-perm{background:#f0f9ff;border:1px solid #bae6fd;color:#0369a1}
.perm-box.admin-perm{background:#fff0f2;border:1px solid #fca5a5;color:#c00}
.perm-box ul{margin:4px 0 0 14px;padding:0}
.perm-box strong{display:block;margin-bottom:2px}
</style>
</head>
<body>
<?php include 'shares/header.php'; ?>
<nav class="menu">
  <a href="<?= $baseUrl ?>/Product/list">🏠 Trang chủ</a>
  <a href="<?= $baseUrl ?>/Auth/login">Đăng nhập</a>
</nav>
<div class="auth-wrap">
  <div class="auth-card">
    <h2>Tạo tài khoản</h2>
    <p class="sub">Đăng ký để mua hàng nhanh hơn và xem lịch sử đơn hàng.</p>

    <?php if (!empty($errors)): ?>
      <div class="alert-err"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <div class="role-toggle">
      <div class="role-btn active-user" id="btn-user" onclick="selectRole('user')">
        <i class="fa-solid fa-user"></i> Khách hàng
      </div>
      <div class="role-btn" id="btn-admin" onclick="selectRole('admin')">
        <i class="fa-solid fa-user-shield"></i> Quản trị viên
      </div>
    </div>

    <div class="perm-box user-perm" id="perm-user">
      <strong>✅ Quyền khách hàng:</strong>
      <ul><li>Thêm giỏ hàng, đặt hàng, thanh toán</li><li>Áp dụng mã giảm giá</li><li>Xem lịch sử đơn hàng</li></ul>
    </div>
    <div class="perm-box admin-perm" id="perm-admin" style="display:none">
      <strong>🔐 Quyền quản trị viên:</strong>
      <ul><li>Thêm / sửa / xóa sản phẩm & danh mục</li><li>Quản lý banner, đơn hàng</li><li>Quản lý tài khoản người dùng</li></ul>
    </div>

    <form method="POST">
      <input type="hidden" name="role" id="role-input" value="<?= htmlspecialchars($_POST['role'] ?? 'user') ?>">
      <div class="form-group">
        <label>Họ và tên</label>
        <input type="text" name="fullname" placeholder="Nguyễn Văn A" value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>" required>
      </div>
      <div class="row2">
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" placeholder="email@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label>Số điện thoại</label>
          <input type="text" name="phone" placeholder="0909123456" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        </div>
      </div>
      <div class="row2">
        <div class="form-group">
          <label>Mật khẩu</label>
          <input type="password" name="password" placeholder="Tối thiểu 6 ký tự" required>
        </div>
        <div class="form-group">
          <label>Xác nhận mật khẩu</label>
          <input type="password" name="confirm" placeholder="Nhập lại mật khẩu" required>
        </div>
      </div>
      <div id="admin-code-wrap">
        <div class="form-group">
          <label><i class="fa-solid fa-key"></i> Mã nhân viên</label>
          <input type="text" name="employee_code" id="employee_code"
                 placeholder="Nhập mã nhân viên do quản lý cấp"
                 value="<?= htmlspecialchars($_POST['employee_code'] ?? '') ?>">
        </div>
      </div>
      <button type="submit" class="btn-auth" id="submit-btn">
        <i class="fa-solid fa-user-plus"></i> Đăng ký
      </button>
    </form>

    <div class="divider">— hoặc —</div>
    <div class="alt-link">Đã có tài khoản? <a href="<?= $baseUrl ?>/Auth/login">Đăng nhập</a></div>
  </div>
</div>
<script>
const savedRole = '<?= htmlspecialchars($_POST['role'] ?? 'user') ?>';
selectRole(savedRole);
function selectRole(role) {
  document.getElementById('role-input').value = role;
  const btnUser  = document.getElementById('btn-user');
  const btnAdmin = document.getElementById('btn-admin');
  const codeWrap = document.getElementById('admin-code-wrap');
  const codeInput= document.getElementById('employee_code');
  const permUser = document.getElementById('perm-user');
  const permAdmin= document.getElementById('perm-admin');
  const btn      = document.getElementById('submit-btn');
  if (role === 'admin') {
    btnUser.classList.remove('active-user'); btnAdmin.classList.add('active-admin');
    codeWrap.classList.add('show'); codeInput.required = true;
    permUser.style.display='none'; permAdmin.style.display='block';
    btn.classList.add('admin-mode');
  } else {
    btnAdmin.classList.remove('active-admin'); btnUser.classList.add('active-user');
    codeWrap.classList.remove('show'); codeInput.required = false;
    permUser.style.display='block'; permAdmin.style.display='none';
    btn.classList.remove('admin-mode');
  }
}
</script>
</body></html>
