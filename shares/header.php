<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$baseUrl    = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$isAdmin    = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$isLoggedIn = isset($_SESSION['user_id']);
$avatarUrl  = !empty($_SESSION['user_avatar'])
    ? $baseUrl.'/public/uploads/avatars/'.htmlspecialchars($_SESSION['user_avatar'])
    : 'https://ui-avatars.com/api/?name='.urlencode($_SESSION['user_name'] ?? 'U').'&background=ffd400&color=222&size=32';
?>
<header class="site-header">
    <div class="header-inner">
        <a href="<?= $baseUrl ?>/Product/list" class="logo">🛒 <span>HUY TOAN STORE</span></a>
        <div class="header-actions">
            <?php if ($isLoggedIn): ?>
                <a href="<?= $baseUrl ?>/Cart/view" class="header-cart" title="Giỏ hàng">🛒
                    <?php
                    $cartCount = 0;
                    if (!empty($_SESSION['cart'])) foreach ($_SESSION['cart'] as $item) $cartCount += $item['qty'] ?? 1;
                    if ($cartCount > 0): ?>
                        <span class="cart-badge"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
                <?php if ($isAdmin): ?>
                    <a href="<?= $baseUrl ?>/Auth/manageUsers" class="header-link admin-link" title="Quản lý Users">
                        <i class="fa-solid fa-users-gear"></i> Quản lý
                    </a>
                <?php endif; ?>
                <div class="header-user">
                    <img src="<?= $avatarUrl ?>" class="header-avatar"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name']??'U') ?>&background=ffd400&color=222&size=32'">
                    <div class="user-dropdown">
                        <span class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></span>
                        <?php if ($isAdmin): ?>
                            <span class="role-badge admin-badge">👑 Admin</span>
                        <?php else: ?>
                            <span class="role-badge user-badge">👤 Khách</span>
                        <?php endif; ?>
                        <a href="<?= $baseUrl ?>/Auth/profile" class="header-link">Hồ sơ</a>
                        <a href="<?= $baseUrl ?>/Auth/logout" class="header-link logout-link">Đăng xuất</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= $baseUrl ?>/Cart/view" class="header-cart" title="Giỏ hàng">🛒</a>
                <a href="<?= $baseUrl ?>/Auth/login"    class="btn-login">Đăng nhập</a>
                <a href="<?= $baseUrl ?>/Auth/register" class="btn-register">Đăng ký</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
.site-header{background:#222;color:#fff;position:sticky;top:0;z-index:1000;box-shadow:0 2px 10px rgba(0,0,0,.3)}
.header-inner{max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:10px 24px}
.logo{color:#ffd400;font-size:19px;font-weight:900;text-decoration:none;display:flex;align-items:center;gap:8px}
.header-actions{display:flex;align-items:center;gap:12px}
.header-cart{font-size:19px;position:relative;text-decoration:none}
.cart-badge{position:absolute;top:-8px;right:-10px;background:#e00;color:#fff;border-radius:50%;font-size:10px;font-weight:bold;min-width:18px;height:18px;display:flex;align-items:center;justify-content:center;padding:0 4px}
.header-avatar{width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid #ffd400;cursor:pointer}
.header-user{display:flex;align-items:center;gap:8px;position:relative}
.user-dropdown{display:flex;align-items:center;gap:8px}
.role-badge{padding:2px 8px;border-radius:20px;font-size:11px;font-weight:bold}
.admin-badge{background:#ffd400;color:#222}
.user-badge{background:#444;color:#ccc}
.user-name{color:#ccc;font-size:14px;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.header-link{color:#aaa;font-size:13px;text-decoration:none}
.header-link:hover{color:#ffd400}
.admin-link{background:#333;padding:5px 10px;border-radius:8px;font-size:13px}
.admin-link:hover{background:#444;color:#ffd400}
.logout-link{color:#f66}
.logout-link:hover{color:#ff4444}
.btn-login,.btn-register{padding:7px 16px;border-radius:20px;font-size:13px;font-weight:bold;text-decoration:none}
.btn-login{background:transparent;color:#ffd400;border:2px solid #ffd400}
.btn-login:hover{background:#ffd400;color:#222}
.btn-register{background:#ffd400;color:#222;border:2px solid #ffd400}
.btn-register:hover{background:#e6bf00}
</style>
