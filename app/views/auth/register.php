<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$selectedRole = $_POST['role'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký - HUY TOAN STORE</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .auth-wrap{min-height:80vh;display:flex;align-items:center;justify-content:center;padding:40px 20px}
        .auth-card{background:#fff;border-radius:20px;padding:40px;width:100%;max-width:520px;box-shadow:0 8px 30px rgba(0,0,0,.10)}
        .auth-card h2{font-size:26px;margin-bottom:6px}
        .auth-card p.sub{color:#888;margin-bottom:24px;font-size:14px}
        .form-group{margin-bottom:16px}
        .form-group label{display:block;font-weight:bold;margin-bottom:6px;font-size:14px;color:#444}
        .form-group input{width:100%;padding:12px 16px;border:2px solid #eee;border-radius:10px;font-size:15px;outline:none;transition:.2s;box-sizing:border-box}
        .form-group input:focus{border-color:#ffd400}
        .btn-auth{width:100%;padding:14px;background:#ffd400;border:none;border-radius:12px;font-size:16px;font-weight:bold;cursor:pointer;margin-top:6px;transition:.2s}
        .btn-auth:hover{background:#e6be00}
        .divider{text-align:center;color:#aaa;margin:20px 0;font-size:14px}
        .alt-link{text-align:center;font-size:14px}
        .alt-link a{color:#0d6efd;text-decoration:none;font-weight:bold}
        .alert-err{background:#fff0f0;border:1px solid #fca5a5;border-radius:10px;padding:12px 16px;margin-bottom:18px}
        .alert-err li{color:#c00;font-size:14px;margin-left:16px}
        .row2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .role-selector{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px}
        .role-btn{position:relative}
        .role-btn input[type="radio"]{position:absolute;opacity:0;width:0;height:0}
        .role-btn label{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;padding:18px 12px;border:2px solid #eee;border-radius:14px;cursor:pointer;transition:.2s;text-align:center;font-size:13px;font-weight:600;color:#555}
        .role-btn label i{font-size:28px}
        .role-btn input:checked + label{border-color:#ffd400;background:#fffbe6;color:#222;box-shadow:0 0 0 3px rgba(255,212,0,0.25)}
        .role-btn.admin-btn input:checked + label{border-color:#6366f1;background:#eef2ff;color:#3730a3;box-shadow:0 0 0 3px rgba(99,102,241,0.2)}
        .role-btn label .role-name{font-size:15px;font-weight:700}
        .role-btn label .role-desc{font-size:11px;color:#888;font-weight:400}
        #emp-code-box{display:none;background:#eef2ff;border:2px solid #c7d2fe;border-radius:12px;padding:16px;margin-bottom:16px}
        #emp-code-box.show{display:block}
        #emp-code-box .emp-label{color:#3730a3;font-weight:700;font-size:14px;display:block;margin-bottom:8px}
        #emp-code-box input{border-color:#a5b4fc}
        #emp-code-box input:focus{border-color:#6366f1}
        #emp-code-box .hint{font-size:12px;color:#6366f1;margin-top:8px}
        .role-label{font-size:13px;font-weight:700;color:#444;margin-bottom:10px;display:block}
    </style>
</head>
<body>
<?php include 'shares/header.php'; ?>
<nav class="menu">
    <a href="<?= $baseUrl ?>/Product/list">&#127968; Trang ch&#7911;</a>
    <a href="<?= $baseUrl ?>/Auth/login">&#272;&#259;ng nh&#7853;p</a>
</nav>

<div class="auth-wrap">
    <div class="auth-card">
        <h2>T&#7841;o t&#224;i kho&#7843;n</h2>
        <p class="sub">&#272;&#259;ng k&yacute; &#273;&#7875; mua h&#224;ng nhanh h&#417;n v&agrave; xem l&#7883;ch s&#7917; &#273;&#417;n h&#224;ng.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert-err"><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <form method="POST" id="registerForm">
            <span class="role-label">B&#7841;n l&agrave;:</span>
            <div class="role-selector">
                <div class="role-btn">
                    <input type="radio" name="role" id="role_user" value="user" <?= $selectedRole !== 'admin' ? 'checked' : '' ?>>
                    <label for="role_user">
                        <i class="fa-solid fa-user" style="color:#16a34a"></i>
                        <span class="role-name">Kh&aacute;ch h&agrave;ng</span>
                        <span class="role-desc">Mua s&#7855;m, theo d&#245;i &#273;&#417;n h&agrave;ng</span>
                    </label>
                </div>
                <div class="role-btn admin-btn">
                    <input type="radio" name="role" id="role_admin" value="admin" <?= $selectedRole === 'admin' ? 'checked' : '' ?>>
                    <label for="role_admin">
                        <i class="fa-solid fa-user-shield" style="color:#6366f1"></i>
                        <span class="role-name">Admin / Nh&acirc;n vi&ecirc;n</span>
                        <span class="role-desc">Qu&#7843;n l&yacute; c&#7917;a h&agrave;ng</span>
                    </label>
                </div>
            </div>

            <div id="emp-code-box" class="<?= $selectedRole === 'admin' ? 'show' : '' ?>">
                <span class="emp-label"><i class="fa-solid fa-id-badge"></i> M&#227; nh&acirc;n vi&ecirc;n <span style="color:red">*</span></span>
                <input type="text" name="employee_code" id="employee_code" placeholder="Nh&#7853;p m&#227; nh&acirc;n vi&ecirc;n &#273;&#432;&#7907;c c&#7845;p" value="<?= htmlspecialchars($_POST['employee_code'] ?? '') ?>">
                <p class="hint">&#9888;&#65039; M&#227; nh&acirc;n vi&ecirc;n do qu&#7843;n l&yacute; c&#7845;p. Demo: ADMIN2024, NV001, NV002, NV003, STAFF123</p>
            </div>

            <div class="form-group">
                <label>H&#7885; v&agrave; t&ecirc;n</label>
                <input type="text" name="fullname" placeholder="Nguy&#7877;n V&#259;n A" value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>" required>
            </div>
            <div class="row2">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="email@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>S&#7889; &#273;i&#7879;n tho&#7841;i</label>
                    <input type="text" name="phone" placeholder="0909123456" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
            </div>
            <div class="row2">
                <div class="form-group">
                    <label>M&#7853;t kh&#7849;u</label>
                    <input type="password" name="password" placeholder="T&#7889;i thi&#7875;u 6 k&#253; t&#7921;" required>
                </div>
                <div class="form-group">
                    <label>X&aacute;c nh&#7853;n m&#7853;t kh&#7849;u</label>
                    <input type="password" name="confirm" placeholder="Nh&#7853;p l&#7841;i m&#7853;t kh&#7849;u" required>
                </div>
            </div>
            <button type="submit" class="btn-auth">&#272;&#259;ng k&yacute;</button>
        </form>

        <div class="divider">&#8212; ho&#7863;c &#8212;</div>
        <div class="alt-link">&#272;&#227; c&oacute; t&agrave;i kho&#7843;n? <a href="<?= $baseUrl ?>/Auth/login">&#272;&#259;ng nh&#7853;p</a></div>
    </div>
</div>
<script>
const radios=document.querySelectorAll('input[name="role"]');
const empBox=document.getElementById('emp-code-box');
const empInput=document.getElementById('employee_code');
radios.forEach(r=>r.addEventListener('change',()=>{
    if(r.value==='admin'&&r.checked){empBox.classList.add('show');empInput.setAttribute('required','required');}
    else{empBox.classList.remove('show');empInput.removeAttribute('required');}
}));
</script>
</body>
</html>
