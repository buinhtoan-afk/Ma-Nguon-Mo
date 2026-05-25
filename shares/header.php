<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$baseUrl  = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$cartCount = array_sum($_SESSION['cart'] ?? []);
$isLogged  = isset($_SESSION['user_id']);
?>
<header class="topbar">
    <div class="logo">
        <a href="<?= $baseUrl ?>/Product/list" style="text-decoration:none;color:inherit;">
            <i class="fa-solid fa-mobile-screen-button"></i> HUY TOAN STORE
        </a>
    </div>
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Bạn tìm gì hôm nay..." autocomplete="off">
        <button><i class="fa fa-search"></i></button>
    </div>
    <div class="top-actions">
        <!-- GIỎ HÀNG -->
        <a href="<?= $baseUrl ?>/Cart/view" style="text-decoration:none;color:inherit;position:relative;">
            <i class="fa-solid fa-cart-shopping"></i> Giỏ hàng
            <?php if ($cartCount > 0): ?>
                <span id="cartBadge" style="position:absolute;top:-8px;right:-10px;background:#e00;color:#fff;border-radius:50%;width:18px;height:18px;font-size:11px;display:flex;align-items:center;justify-content:center;font-weight:bold;">
                    <?= $cartCount ?>
                </span>
            <?php else: ?>
                <span id="cartBadge" style="position:absolute;top:-8px;right:-10px;background:#e00;color:#fff;border-radius:50%;width:18px;height:18px;font-size:11px;display:<?= $cartCount > 0 ? 'flex' : 'none' ?>;align-items:center;justify-content:center;font-weight:bold;">0</span>
            <?php endif; ?>
        </a>
        <!-- USER -->
        <?php if ($isLogged): ?>
            <div class="user-menu" style="position:relative;">
                <span style="cursor:pointer;" onclick="document.getElementById('userDropdown').classList.toggle('show')">
                    <i class="fa-regular fa-user"></i> <?= htmlspecialchars($_SESSION['user_name']) ?> ▾
                </span>
                <div id="userDropdown" style="display:none;position:absolute;right:0;top:30px;background:#fff;border:1px solid #ddd;border-radius:10px;min-width:170px;box-shadow:0 4px 15px rgba(0,0,0,.12);z-index:999;">
                    <a href="<?= $baseUrl ?>/Auth/profile" style="display:block;padding:12px 16px;text-decoration:none;color:#333;border-bottom:1px solid #eee;">
                        <i class="fa-solid fa-user-circle"></i> Hồ sơ của tôi
                    </a>
                    <a href="<?= $baseUrl ?>/Auth/logout" style="display:block;padding:12px 16px;text-decoration:none;color:#e00;">
                        <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                    </a>
                </div>
            </div>
        <?php else: ?>
            <a href="<?= $baseUrl ?>/Auth/login" style="text-decoration:none;color:inherit;">
                <i class="fa-regular fa-user"></i> Đăng nhập
            </a>
        <?php endif; ?>
    </div>
</header>
<script>
document.addEventListener('click', function(e) {
    var dd = document.getElementById('userDropdown');
    if (dd && !e.target.closest('.user-menu')) dd.classList.remove('show');
});
document.getElementById('userDropdown') && document.getElementById('userDropdown').classList.contains('show') 
    ? document.getElementById('userDropdown').style.display='block' : null;
// Toggle show class
document.querySelectorAll('.user-menu span').forEach(function(el){
    el.addEventListener('click',function(){
        var dd = document.getElementById('userDropdown');
        dd.style.display = dd.style.display==='block' ? 'none' : 'block';
    });
});
</script>
