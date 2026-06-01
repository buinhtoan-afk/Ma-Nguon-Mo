<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Không có quyền truy cập</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
    <style>
        .error-box {
            max-width: 500px; margin: 100px auto; text-align: center;
            padding: 50px 30px; background: #fff; border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0,0,0,.1);
        }
        .error-box .code { font-size: 80px; font-weight: 900; color: #ffd400; line-height:1; }
        .error-box h2 { margin: 10px 0 8px; font-size: 24px; color: #222; }
        .error-box p  { color: #666; margin-bottom: 28px; }
        .error-box a  {
            display: inline-block; padding: 12px 32px;
            background: #222; color: #ffd400; border-radius: 30px;
            font-weight: bold; text-decoration: none;
        }
        .error-box a:hover { background: #000; }
    </style>
</head>
<body style="background:#f5f5f5;">
<div class="error-box">
    <div class="code">403</div>
    <h2>Bạn không có quyền truy cập</h2>
    <p>Chức năng này chỉ dành cho <strong>Quản trị viên (Admin)</strong>.<br>
       Vui lòng liên hệ quản trị viên nếu cần hỗ trợ.</p>
    <a href="<?= $baseUrl ?>/Product/list">← Về trang chủ</a>
</div>
</body>
</html>
