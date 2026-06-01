<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
?>
<!DOCTYPE html><html lang="vi"><head>
<meta charset="UTF-8"><title>Quản lý người dùng - HUY TOAN STORE</title>
<link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
body{background:#f5f5f5}
.admin-wrap{max-width:1100px;margin:32px auto;padding:0 20px}
.page-title{font-size:24px;font-weight:700;margin-bottom:24px;display:flex;align-items:center;gap:10px}
.toolbar{display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:center}
.search-box{flex:1;min-width:200px;display:flex;gap:8px}
.search-box input{flex:1;padding:10px 14px;border:2px solid #eee;border-radius:10px;font-size:14px;outline:none;transition:.2s}
.search-box input:focus{border-color:#ffd400}
.search-box button{padding:10px 18px;background:#ffd400;border:none;border-radius:10px;font-weight:700;cursor:pointer;font-size:14px}
.filter-tabs{display:flex;gap:6px}
.f-tab{padding:8px 16px;border:2px solid #eee;border-radius:20px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;color:#666;transition:.2s}
.f-tab:hover,.f-tab.active{border-color:#ffd400;background:#fffbe6;color:#b8860b}
.card{background:#fff;border-radius:16px;box-shadow:0 4px 18px rgba(0,0,0,.07);overflow:hidden}
.users-table{width:100%;border-collapse:collapse}
.users-table th{background:#fafafa;padding:13px 16px;text-align:left;font-size:13px;color:#555;border-bottom:2px solid #f0f0f0;font-weight:700}
.users-table td{padding:13px 16px;border-bottom:1px solid #f5f5f5;font-size:14px;vertical-align:middle}
.users-table tr:hover td{background:#fffbe6}
.avatar-sm{width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid #eee}
.badge{padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700}
.badge-admin{background:#ffd400;color:#222}
.badge-user{background:#e5e7eb;color:#555}
.badge-locked{background:#fee2e2;color:#dc2626}
.badge-active{background:#dcfce7;color:#16a34a}
.badge-verified{background:#dbeafe;color:#1d4ed8}
.badge-unverified{background:#fef9c3;color:#854d0e}
.action-btns{display:flex;gap:6px;flex-wrap:wrap}
.btn-sm{padding:5px 12px;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;transition:.2s;text-decoration:none;display:inline-block}
.btn-lock{background:#fee2e2;color:#dc2626}
.btn-lock:hover{background:#fca5a5}
.btn-unlock{background:#dcfce7;color:#16a34a}
.btn-unlock:hover{background:#86efac}
.btn-role{background:#e0e7ff;color:#4338ca}
.btn-role:hover{background:#c7d2fe}
.btn-del{background:#f1f5f9;color:#94a3b8}
.btn-del:hover{background:#fee2e2;color:#dc2626}
.alert-ok{background:#f0fff4;border:1px solid #86efac;border-radius:10px;padding:12px 16px;margin-bottom:18px;color:#166534;font-size:14px}
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px}
.stat-card{background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 2px 10px rgba(0,0,0,.07);text-align:center}
.stat-num{font-size:28px;font-weight:800;color:#222}
.stat-lbl{font-size:12px;color:#888;margin-top:4px}
@media(max-width:700px){.stats-row{grid-template-columns:1fr 1fr}.users-table{font-size:12px}}
</style>
</head>
<body>
<?php include 'shares/header.php'; ?>
<nav class="menu">
  <a href="<?= $baseUrl ?>/Product/list">🏠 Trang chủ</a>
  <a href="<?= $baseUrl ?>/Auth/profile">👤 Hồ sơ</a>
  <a href="<?= $baseUrl ?>/Auth/manageUsers" style="color:#ffd400;font-weight:700">👥 Quản lý Users</a>
</nav>

<div class="admin-wrap">
  <div class="page-title">
    <i class="fa-solid fa-users-gear"></i> Quản lý người dùng
  </div>

  <?php if ($success): ?><div class="alert-ok"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <!-- Stats -->
  <?php
  $total   = count($users);
  $admins  = count(array_filter($users, fn($u) => $u['role']==='admin'));
  $locked  = count(array_filter($users, fn($u) => $u['is_locked']));
  $verified= count(array_filter($users, fn($u) => $u['is_verified']));
  ?>
  <div class="stats-row">
    <div class="stat-card"><div class="stat-num"><?= $total ?></div><div class="stat-lbl">Tổng người dùng</div></div>
    <div class="stat-card"><div class="stat-num" style="color:#b8860b"><?= $admins ?></div><div class="stat-lbl">Admin</div></div>
    <div class="stat-card"><div class="stat-num" style="color:#dc2626"><?= $locked ?></div><div class="stat-lbl">Đang khóa</div></div>
    <div class="stat-card"><div class="stat-num" style="color:#16a34a"><?= $verified ?></div><div class="stat-lbl">Đã xác thực</div></div>
  </div>

  <!-- Toolbar -->
  <div class="toolbar">
    <form class="search-box" method="GET">
      <input type="hidden" name="url" value="Auth/manageUsers">
      <input type="text" name="search" placeholder="🔍 Tìm tên hoặc email..."
             value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
      <input type="hidden" name="filter" value="<?= htmlspecialchars($_GET['filter'] ?? 'all') ?>">
      <button type="submit">Tìm</button>
    </form>
    <div class="filter-tabs">
      <?php
      $filters = ['all'=>'Tất cả','admin'=>'Admin','user'=>'Khách','locked'=>'Bị khóa'];
      $cur = $_GET['filter'] ?? 'all';
      $srch = htmlspecialchars($_GET['search'] ?? '');
      foreach ($filters as $k=>$lbl):
      ?>
        <a href="?url=Auth/manageUsers&filter=<?= $k ?>&search=<?= $srch ?>"
           class="f-tab <?= $cur===$k?'active':'' ?>"><?= $lbl ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Table -->
  <div class="card">
    <table class="users-table">
      <thead>
        <tr>
          <th>#</th><th>Avatar</th><th>Họ tên</th><th>Email</th>
          <th>Vai trò</th><th>Trạng thái</th><th>Xác thực</th>
          <th>Ngày đăng ký</th><th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($users)): ?>
        <tr><td colspan="9" style="text-align:center;padding:30px;color:#888">Không có người dùng nào.</td></tr>
      <?php else: ?>
        <?php foreach ($users as $u): ?>
        <tr>
          <td style="color:#aaa"><?= $u['id'] ?></td>
          <td>
            <img src="<?= !empty($u['avatar'])
              ? $baseUrl.'/public/uploads/avatars/'.htmlspecialchars($u['avatar'])
              : 'https://ui-avatars.com/api/?name='.urlencode($u['fullname']).'&background=ffd400&color=222&size=38'
            ?>" class="avatar-sm"
            onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($u['fullname']) ?>&background=ffd400&color=222&size=38'">
          </td>
          <td style="font-weight:600"><?= htmlspecialchars($u['fullname']) ?></td>
          <td style="color:#555"><?= htmlspecialchars($u['email']) ?></td>
          <td>
            <span class="badge <?= $u['role']==='admin'?'badge-admin':'badge-user' ?>">
              <?= $u['role']==='admin'?'👑 Admin':'👤 User' ?>
            </span>
          </td>
          <td>
            <span class="badge <?= $u['is_locked']?'badge-locked':'badge-active' ?>">
              <?= $u['is_locked']?'🔒 Bị khóa':'✅ Hoạt động' ?>
            </span>
          </td>
          <td>
            <span class="badge <?= $u['is_verified']?'badge-verified':'badge-unverified' ?>">
              <?= $u['is_verified']?'Đã xác thực':'Chưa xác thực' ?>
            </span>
          </td>
          <td style="color:#888;font-size:12px"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
          <td>
            <div class="action-btns">
              <?php if ($u['id'] != $_SESSION['user_id']): ?>
                <!-- Khóa / Mở khóa -->
                <a href="<?= $baseUrl ?>/Auth/toggleLock/<?= $u['id'] ?>"
                   class="btn-sm <?= $u['is_locked']?'btn-unlock':'btn-lock' ?>"
                   onclick="return confirm('<?= $u['is_locked']?'Mở khóa':'Khóa' ?> tài khoản này?')">
                  <?= $u['is_locked']?'🔓 Mở':'🔒 Khóa' ?>
                </a>
                <!-- Đổi role -->
                <a href="<?= $baseUrl ?>/Auth/changeRole/<?= $u['id'] ?>"
                   class="btn-sm btn-role"
                   onclick="return confirm('Đổi role cho <?= htmlspecialchars(addslashes($u['fullname'])) ?>?')">
                  ⇄ Role
                </a>
                <!-- Xóa -->
                <a href="<?= $baseUrl ?>/Auth/deleteUser/<?= $u['id'] ?>"
                   class="btn-sm btn-del"
                   onclick="return confirm('Xóa tài khoản <?= htmlspecialchars(addslashes($u['fullname'])) ?>? Không thể hoàn tác!')">
                  🗑️
                </a>
              <?php else: ?>
                <span style="color:#aaa;font-size:12px">Tài khoản bạn</span>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body></html>
