<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$baseUrl     = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$statusLabel = ['pending'=>'Chờ xác nhận','processing'=>'Đang xử lý','shipping'=>'Đang giao','done'=>'Hoàn thành','cancelled'=>'Đã hủy'];
$statusColor = ['pending'=>'#e67e00','processing'=>'#0d6efd','shipping'=>'#8800cc','done'=>'#16a34a','cancelled'=>'#dc2626'];
$profileErrors  = $_SESSION['profile_errors']  ?? [];
unset($_SESSION['profile_errors']);
$avatarUrl = !empty($userExtra['avatar'])
    ? $baseUrl.'/public/uploads/avatars/'.htmlspecialchars($userExtra['avatar'])
    : $baseUrl.'/public/images/default_avatar.png';
?>
<!DOCTYPE html><html lang="vi"><head>
<meta charset="UTF-8"><title>Hồ sơ - HUY TOAN STORE</title>
<link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
body{background:#f5f5f5}
.profile-wrap{max-width:960px;margin:36px auto;padding:0 20px}
.tab-bar{display:flex;gap:6px;margin-bottom:24px;background:#fff;border-radius:14px;padding:8px;box-shadow:0 2px 10px rgba(0,0,0,.07)}
.tab-btn{flex:1;padding:10px;border:none;background:transparent;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;color:#666;transition:.2s}
.tab-btn.active{background:#ffd400;color:#222}
.tab-btn:hover:not(.active){background:#f5f5f5}
.tab-pane{display:none}
.tab-pane.active{display:block}
.profile-card{background:#fff;border-radius:18px;padding:30px;box-shadow:0 4px 20px rgba(0,0,0,.07);margin-bottom:20px}
.profile-card h3{font-size:18px;margin-bottom:20px;border-bottom:2px solid #ffd400;padding-bottom:10px;display:flex;align-items:center;gap:8px}
.avatar-section{display:flex;align-items:center;gap:24px;margin-bottom:24px}
.avatar-img{width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid #ffd400}
.avatar-info .name{font-size:20px;font-weight:700}
.avatar-info .role-tag{display:inline-block;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:700;margin-top:4px}
.role-admin{background:#ffd400;color:#222}.role-user{background:#e5e7eb;color:#555}
.verified-tag{background:#dcfce7;color:#16a34a;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700}
.unverified-tag{background:#fee2e2;color:#dc2626;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:600px){.form-grid{grid-template-columns:1fr}}
.form-group{margin-bottom:0}
.form-group label{display:block;font-weight:600;margin-bottom:6px;font-size:13px;color:#555}
.form-group input{width:100%;padding:10px 14px;border:2px solid #eee;border-radius:10px;font-size:14px;outline:none;transition:.2s;box-sizing:border-box}
.form-group input:focus{border-color:#ffd400}
.form-group input[readonly]{background:#f9f9f9;color:#888}
.btn-save{padding:11px 28px;background:#ffd400;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;transition:.2s}
.btn-save:hover{background:#e6be00}
.btn-danger{padding:11px 28px;background:#ef4444;color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;transition:.2s}
.btn-danger:hover{background:#dc2626}
.alert-ok{background:#f0fff4;border:1px solid #86efac;border-radius:10px;padding:12px 16px;margin-bottom:18px;color:#166534;font-size:14px}
.alert-err{background:#fff0f0;border:1px solid #fca5a5;border-radius:10px;padding:12px 16px;margin-bottom:18px}
.alert-err li{color:#c00;font-size:14px;margin-left:16px}
.order-row{display:grid;grid-template-columns:90px 1fr auto auto;gap:14px;align-items:center;padding:14px 0;border-bottom:1px solid #f0f0f0}
.status-badge{padding:4px 12px;border-radius:20px;font-size:11px;font-weight:bold;color:#fff}
.empty-orders{text-align:center;padding:40px;color:#888}
.avatar-upload-wrap{position:relative}
.avatar-preview-wrap{position:relative;display:inline-block}
.avatar-edit-btn{position:absolute;bottom:2px;right:2px;background:#ffd400;border:none;border-radius:50%;width:28px;height:28px;cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.2)}
.pw-strength{height:4px;border-radius:4px;margin-top:6px;background:#eee;transition:.3s}
</style>
</head>
<body>
<?php include 'shares/header.php'; ?>
<nav class="menu">
  <a href="<?= $baseUrl ?>/Product/list">🏠 Trang chủ</a>
  <a href="<?= $baseUrl ?>/Cart/view">Giỏ hàng</a>
  <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
    <a href="<?= $baseUrl ?>/Auth/manageUsers">👥 Quản lý Users</a>
  <?php endif; ?>
</nav>

<div class="profile-wrap">

  <?php if ($success): ?><div class="alert-ok">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>
  <?php if (!empty($profileErrors)): ?>
    <div class="alert-err"><ul><?php foreach($profileErrors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>

  <!-- Avatar + tên -->
  <div class="profile-card">
    <div class="avatar-section">
      <div class="avatar-preview-wrap">
        <img src="<?= $avatarUrl ?>" alt="Avatar" class="avatar-img" id="avatarPreview"
             onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user->getFullname()) ?>&background=ffd400&color=222&size=96'">
      </div>
      <div class="avatar-info">
        <div class="name"><?= htmlspecialchars($user->getFullname()) ?></div>
        <div style="margin-top:6px;display:flex;align-items:center;gap:8px;flex-wrap:wrap">
          <span class="role-tag <?= $userExtra['role']==='admin'?'role-admin':'role-user' ?>">
            <?= $userExtra['role']==='admin'?'👑 Admin':'👤 Khách hàng' ?>
          </span>
          <?php if ($userExtra['is_verified']): ?>
            <span class="verified-tag">✅ Đã xác thực</span>
          <?php else: ?>
            <span class="unverified-tag">⚠️ Chưa xác thực</span>
          <?php endif; ?>
        </div>
        <div style="font-size:13px;color:#888;margin-top:6px">
          Tham gia: <?= date('d/m/Y', strtotime($user->getCreatedAt())) ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabs -->
  <div class="tab-bar">
    <button class="tab-btn active" onclick="switchTab('info')"><i class="fa-solid fa-user"></i> Thông tin</button>
    <button class="tab-btn" onclick="switchTab('password')"><i class="fa-solid fa-lock"></i> Mật khẩu</button>
    <button class="tab-btn" onclick="switchTab('orders')"><i class="fa-solid fa-box"></i> Đơn hàng (<?= count($orders) ?>)</button>
  </div>

  <!-- TAB: Thông tin -->
  <div class="tab-pane active" id="tab-info">
    <div class="profile-card">
      <h3><i class="fa-solid fa-pen-to-square"></i> Cập nhật thông tin</h3>
      <form method="POST" action="<?= $baseUrl ?>/Auth/updateProfile" enctype="multipart/form-data">
        <!-- Avatar upload -->
        <div class="form-group" style="margin-bottom:18px">
          <label>Ảnh đại diện <span style="color:#888;font-weight:400">(JPG/PNG/WEBP, tối đa 2MB)</span></label>
          <input type="file" name="avatar" accept="image/*" id="avatarInput"
                 onchange="previewAvatar(this)" style="margin-top:4px">
        </div>
        <div class="form-grid" style="margin-bottom:16px">
          <div class="form-group">
            <label>Họ và tên</label>
            <input type="text" name="fullname" value="<?= htmlspecialchars($user->getFullname()) ?>" required>
          </div>
          <div class="form-group">
            <label>Email <span style="color:#888;font-weight:400">(không thể đổi)</span></label>
            <input type="email" value="<?= htmlspecialchars($user->getEmail()) ?>" readonly>
          </div>
          <div class="form-group">
            <label>Số điện thoại</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user->getPhone()) ?>" placeholder="0909123456">
          </div>
          <div class="form-group">
            <label>Ngày đăng ký</label>
            <input type="text" value="<?= date('d/m/Y H:i', strtotime($user->getCreatedAt())) ?>" readonly>
          </div>
        </div>
        <button type="submit" class="btn-save"><i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi</button>
      </form>
    </div>
  </div>

  <!-- TAB: Đổi mật khẩu -->
  <div class="tab-pane" id="tab-password">
    <div class="profile-card">
      <h3><i class="fa-solid fa-key"></i> Đổi mật khẩu</h3>
      <form method="POST" action="<?= $baseUrl ?>/Auth/changePassword" style="max-width:420px">
        <div class="form-group" style="margin-bottom:14px">
          <label>Mật khẩu hiện tại</label>
          <input type="password" name="old_password" placeholder="••••••••" required>
        </div>
        <div class="form-group" style="margin-bottom:14px">
          <label>Mật khẩu mới</label>
          <input type="password" name="new_password" id="newpw" placeholder="Tối thiểu 6 ký tự" required>
          <div class="pw-strength" id="pwStrength"></div>
        </div>
        <div class="form-group" style="margin-bottom:20px">
          <label>Xác nhận mật khẩu mới</label>
          <input type="password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required>
        </div>
        <button type="submit" class="btn-danger"><i class="fa-solid fa-shield"></i> Đổi mật khẩu</button>
      </form>
    </div>
  </div>

  <!-- TAB: Đơn hàng -->
  <div class="tab-pane" id="tab-orders">
    <div class="profile-card">
      <h3><i class="fa-solid fa-box"></i> Lịch sử đơn hàng</h3>
      <?php if (empty($orders)): ?>
        <div class="empty-orders"><div style="font-size:48px;margin-bottom:12px">📦</div>
          <p>Bạn chưa có đơn hàng nào.</p>
          <a href="<?= $baseUrl ?>/Product/list" style="color:#e00;display:inline-block;margin-top:10px">Mua sắm ngay →</a>
        </div>
      <?php else: ?>
        <?php foreach($orders as $o): ?>
          <div class="order-row">
            <div><div style="font-weight:700;font-size:13px">#<?= $o['id'] ?></div>
              <div style="font-size:11px;color:#888"><?= date('d/m/Y', strtotime($o['created_at'])) ?></div></div>
            <div><div style="font-weight:600"><?= htmlspecialchars($o['fullname']) ?></div>
              <div style="font-size:13px;color:#666"><?= htmlspecialchars($o['city']) ?></div></div>
            <div style="font-weight:700;color:#e00"><?= number_format($o['total'],0,',','.') ?>₫</div>
            <div><?php $st=$o['status']; ?>
              <span class="status-badge" style="background:<?= $statusColor[$st]??'#888' ?>"><?= $statusLabel[$st]??$st ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</div>
<script>
function switchTab(name) {
  document.querySelectorAll('.tab-btn').forEach((b,i) => {
    const tabs = ['info','password','orders'];
    b.classList.toggle('active', tabs[i]===name);
  });
  document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
  document.getElementById('tab-'+name).classList.add('active');
}
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
    reader.readAsDataURL(input.files[0]);
  }
}
document.getElementById('newpw').addEventListener('input', function(){
  const bar = document.getElementById('pwStrength');
  const v = this.value;
  if (!v) { bar.style.width='0'; return; }
  if (v.length < 6)      { bar.style.background='#ef4444'; bar.style.width='30%'; }
  else if (v.length < 10) { bar.style.background='#f59e0b'; bar.style.width='65%'; }
  else                    { bar.style.background='#22c55e'; bar.style.width='100%'; }
});
<?php if (!empty($_GET['tab'])): ?>
switchTab('<?= htmlspecialchars($_GET['tab']) ?>');
<?php endif; ?>
</script>
</body></html>
