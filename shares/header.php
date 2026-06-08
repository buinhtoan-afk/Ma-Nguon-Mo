<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$isAdmin   = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$isLoggedIn = isset($_SESSION['user_id']);
?>
<header class="site-header">
    <div class="header-inner">
        <a href="<?= $baseUrl ?>/Product/list" class="logo">
            &#128722; <span>HUY TOAN STORE</span>
        </a>
        <div class="header-actions">
            <?php if ($isLoggedIn): ?>
                <a href="<?= $baseUrl ?>/Cart/view" class="header-cart" title="Gi&#7887; h&agrave;ng">
                    &#128722;
                    <?php
                    $cartCount = 0;
                    if (!empty($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $item) $cartCount += $item['qty'] ?? 1;
                    }
                    if ($cartCount > 0): ?>
                        <span class="cart-badge"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
                <div class="header-user">
                    <?php if ($isAdmin): ?>
                        <span class="role-badge admin-badge">&#128081; Admin</span>
                        <a href="<?= $baseUrl ?>/Auth/manageUsers" class="header-link" title="Qu&#7843;n l&yacute; ng&#432;&#7901;i d&ugrave;ng">&#128101; Users</a>
                        <a href="<?= $baseUrl ?>/Order/list" class="header-link" title="Qu&#7843;n l&yacute; &#273;&#417;n h&agrave;ng">&#128230; &#272;&#417;n h&agrave;ng</a>
                    <?php else: ?>
                        <span class="role-badge user-badge">&#128100; Kh&aacute;ch</span>
                        <a href="<?= $baseUrl ?>/Order/list" class="header-link">&#128230; &#272;&#417;n c&#7911;a t&ocirc;i</a>
                    <?php endif; ?>
                    <span class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></span>
                    <a href="<?= $baseUrl ?>/Auth/profile" class="header-link">H&#7891; s&#417;</a>
                    <a href="<?= $baseUrl ?>/Api/productManager" class="header-link" style="color:#ffd400"><i class="fa-solid fa-plug"></i> API</a>
                    <a href="<?= $baseUrl ?>/Auth/logout" class="header-link logout-link">&#272;&#259;ng xu&#7845;t</a>
                </div>
            <?php else: ?>
                <a href="<?= $baseUrl ?>/Cart/view" class="header-cart" title="Gi&#7887; h&agrave;ng">&#128722;</a>
                <a href="<?= $baseUrl ?>/Auth/login" class="btn-login">&#272;&#259;ng nh&#7853;p</a>
                <a href="<?= $baseUrl ?>/Auth/register" class="btn-register">&#272;&#259;ng k&yacute;</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<style>
.site-header{background:#222;color:#fff;padding:0;position:sticky;top:0;z-index:1000;box-shadow:0 2px 10px rgba(0,0,0,.3)}
.header-inner{max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:12px 24px}
.logo{color:#ffd400;font-size:20px;font-weight:900;text-decoration:none;display:flex;align-items:center;gap:8px}
.logo span{letter-spacing:.5px}
.header-actions{display:flex;align-items:center;gap:14px}
.header-cart{font-size:20px;position:relative;text-decoration:none}
.cart-badge{position:absolute;top:-8px;right:-10px;background:#e00;color:#fff;border-radius:50%;font-size:10px;font-weight:bold;min-width:18px;height:18px;display:flex;align-items:center;justify-content:center;padding:0 4px}
.header-user{display:flex;align-items:center;gap:10px}
.role-badge{padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold}
.admin-badge{background:#ffd400;color:#222}
.user-badge{background:#444;color:#ccc}
.user-name{color:#ccc;font-size:14px}
.header-link{color:#aaa;font-size:13px;text-decoration:none}
.header-link:hover{color:#ffd400}
.logout-link{color:#f66}
.logout-link:hover{color:#ff4444}
.btn-login,.btn-register{padding:8px 18px;border-radius:20px;font-size:13px;font-weight:bold;text-decoration:none}
.btn-login{background:transparent;color:#ffd400;border:2px solid #ffd400}
.btn-login:hover{background:#ffd400;color:#222}
.btn-register{background:#ffd400;color:#222;border:2px solid #ffd400}
.btn-register:hover{background:#e6bf00}
</style>
