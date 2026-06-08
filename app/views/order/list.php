<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';

$statusLabels = [
    'pending'    => ['label'=>'Ch&#7901; x&#7917; l&yacute;',   'color'=>'#f59e0b','bg'=>'#fef3c7'],
    'processing' => ['label'=>'&#272;ang x&#7917; l&yacute;', 'color'=>'#3b82f6','bg'=>'#dbeafe'],
    'shipped'    => ['label'=>'&#272;ang giao',        'color'=>'#8b5cf6','bg'=>'#ede9fe'],
    'delivered'  => ['label'=>'&#272;&#227; giao',      'color'=>'#16a34a','bg'=>'#dcfce7'],
    'cancelled'  => ['label'=>'&#272;&#227; h&#7911;y', 'color'=>'#dc2626','bg'=>'#fee2e2'],
];
function statusBadge($s,$labels){
    $l=$labels[$s]??['label'=>$s,'color'=>'#888','bg'=>'#eee'];
    return "<span style='background:{$l['bg']};color:{$l['color']};padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700'>{$l['label']}</span>";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= $isAdmin ? 'Qu&#7843;n l&yacute; &#273;&#417;n h&agrave;ng' : '&#272;&#417;n h&agrave;ng c&#7911;a t&ocirc;i' ?> - HUY TOAN STORE</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body{background:#f5f5f5}
        .wrap{max-width:1100px;margin:32px auto;padding:0 20px}
        .page-title{font-size:24px;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:10px}
        .toolbar{display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:center}
        .search-box{flex:1;min-width:200px;display:flex;gap:8px}
        .search-box input{flex:1;padding:10px 14px;border:2px solid #eee;border-radius:10px;font-size:14px;outline:none;transition:.2s}
        .search-box input:focus{border-color:#ffd400}
        .search-box button{padding:10px 18px;background:#ffd400;border:none;border-radius:10px;font-weight:700;cursor:pointer;font-size:14px}
        .f-tabs{display:flex;gap:6px;flex-wrap:wrap}
        .f-tab{padding:7px 14px;border:2px solid #eee;border-radius:20px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;color:#666;transition:.2s}
        .f-tab:hover,.f-tab.active{border-color:#ffd400;background:#fffbe6;color:#b8860b}
        .card{background:#fff;border-radius:16px;box-shadow:0 4px 18px rgba(0,0,0,.07);overflow:hidden}
        .orders-table{width:100%;border-collapse:collapse}
        .orders-table th{background:#fafafa;padding:12px 16px;text-align:left;font-size:13px;color:#555;border-bottom:2px solid #f0f0f0;font-weight:700}
        .orders-table td{padding:12px 16px;border-bottom:1px solid #f5f5f5;font-size:14px;vertical-align:middle}
        .orders-table tr:hover td{background:#fffbe6}
        .btn-sm{padding:5px 12px;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;transition:.2s;text-decoration:none;display:inline-block}
        .btn-view{background:#e0f2fe;color:#0369a1}
        .btn-view:hover{background:#bae6fd}
        .status-select{padding:5px 10px;border:2px solid #eee;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;outline:none}
        .empty-state{text-align:center;padding:60px 20px;color:#888}
        .empty-state i{font-size:48px;margin-bottom:16px;display:block;color:#ddd}
        .stats-row{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:20px}
        .stat-card{background:#fff;border-radius:14px;padding:16px;box-shadow:0 2px 10px rgba(0,0,0,.06);text-align:center}
        .stat-num{font-size:24px;font-weight:800}
        .stat-lbl{font-size:11px;color:#888;margin-top:3px}
        @media(max-width:700px){.stats-row{grid-template-columns:1fr 1fr}.orders-table{font-size:12px}}
    </style>
</head>
<body>
<?php include 'shares/header.php'; ?>
<nav class="menu">
    <a href="<?= $baseUrl ?>/Product/list">&#127968; Trang ch&#7911;</a>
    <a href="<?= $baseUrl ?>/Auth/profile">&#128100; H&#7891; s&#417;</a>
    <?php if ($isAdmin): ?>
    <a href="<?= $baseUrl ?>/Auth/manageUsers">&#128101; Ng&#432;&#7901;i d&ugrave;ng</a>
    <a href="<?= $baseUrl ?>/Order/list" style="color:#ffd400;font-weight:700">&#128230; &#272;&#417;n h&agrave;ng</a>
    <?php else: ?>
    <a href="<?= $baseUrl ?>/Order/list" style="color:#ffd400;font-weight:700">&#128230; &#272;&#417;n h&agrave;ng c&#7911;a t&ocirc;i</a>
    <?php endif; ?>
</nav>

<div class="wrap">
    <div class="page-title">
        <i class="fa-solid fa-box-open"></i>
        <?= $isAdmin ? 'Qu&#7843;n l&yacute; &#273;&#417;n h&agrave;ng' : '&#272;&#417;n h&agrave;ng c&#7911;a t&ocirc;i' ?>
    </div>

    <?php if ($isAdmin): ?>
    <!-- Stats cho admin -->
    <?php
    $counts=['all'=>count($orders),'pending'=>0,'processing'=>0,'shipped'=>0,'delivered'=>0,'cancelled'=>0];
    foreach($orders as $o) if(isset($counts[$o['status']])) $counts[$o['status']]++;
    $revenue = array_sum(array_column(array_filter($orders,fn($o)=>$o['status']==='delivered'),'total'));
    ?>
    <div class="stats-row">
        <div class="stat-card"><div class="stat-num"><?=$counts['all']?></div><div class="stat-lbl">T&#7893;ng &#273;&#417;n</div></div>
        <div class="stat-card"><div class="stat-num" style="color:#f59e0b"><?=$counts['pending']?></div><div class="stat-lbl">Ch&#7901; x&#7917; l&yacute;</div></div>
        <div class="stat-card"><div class="stat-num" style="color:#3b82f6"><?=$counts['processing']?></div><div class="stat-lbl">&#272;ang x&#7917; l&yacute;</div></div>
        <div class="stat-card"><div class="stat-num" style="color:#16a34a"><?=$counts['delivered']?></div><div class="stat-lbl">&#272;&#227; giao</div></div>
        <div class="stat-card"><div class="stat-num" style="color:#16a34a;font-size:16px"><?=number_format($revenue)?>&#8363;</div><div class="stat-lbl">Doanh thu</div></div>
    </div>
    <?php endif; ?>

    <!-- Toolbar -->
    <div class="toolbar">
        <?php if ($isAdmin): ?>
        <form class="search-box" method="GET">
            <input type="hidden" name="url" value="Order/list">
            <input type="text" name="search" placeholder="&#128269; T&igrave;m t&ecirc;n, SĐT, m&#227; &#273;&#417;n..." value="<?= htmlspecialchars($_GET['search']??'') ?>">
            <input type="hidden" name="filter" value="<?= htmlspecialchars($_GET['filter']??'all') ?>">
            <button type="submit">T&igrave;m</button>
        </form>
        <?php endif; ?>
        <div class="f-tabs">
            <?php
            $tabs=['all'=>'T&#7845;t c&#7843;','pending'=>'Ch&#7901; x&#7917; l&yacute;','processing'=>'&#272;ang x&#7917; l&yacute;','shipped'=>'&#272;ang giao','delivered'=>'&#272;&#227; giao','cancelled'=>'&#272;&#227; h&#7911;y'];
            $cur=$_GET['filter']??'all'; $srch=htmlspecialchars($_GET['search']??'');
            foreach($tabs as $k=>$lbl):?>
            <a href="?url=Order/list&filter=<?=$k?>&search=<?=$srch?>" class="f-tab <?=$cur===$k?'active':''?>"><?=$lbl?></a>
            <?php endforeach;?>
        </div>
    </div>

    <div class="card">
        <?php if (empty($orders)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-box-open"></i>
            <p>Ch&#432;a c&oacute; &#273;&#417;n h&agrave;ng n&agrave;o.</p>
        </div>
        <?php else: ?>
        <table class="orders-table">
            <thead>
                <tr>
                    <th>#M&#227;</th>
                    <th>Kh&aacute;ch h&agrave;ng</th>
                    <?php if ($isAdmin): ?><th>Email</th><?php endif; ?>
                    <th>T&#7893;ng ti&#7873;n</th>
                    <th>Thanh to&aacute;n</th>
                    <th>Tr&#7841;ng th&aacute;i</th>
                    <th>Ng&agrave;y &#273;&#7863;t</th>
                    <th>Thao t&aacute;c</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td><strong>#<?= $o['id'] ?></strong></td>
                <td>
                    <div style="font-weight:600"><?= htmlspecialchars($o['fullname']) ?></div>
                    <div style="font-size:12px;color:#888"><?= htmlspecialchars($o['phone']) ?></div>
                </td>
                <?php if ($isAdmin): ?>
                <td style="font-size:12px;color:#666"><?= htmlspecialchars($o['user_email']??'Kh&aacute;ch') ?></td>
                <?php endif; ?>
                <td><strong style="color:#e53e3e"><?= number_format($o['total']) ?>&#8363;</strong></td>
                <td style="font-size:12px"><?= $o['payment_method']==='cod'?'Ti&#7873;n m&#7863;t':'Chuy&#7875;n kho&#7843;n' ?></td>
                <td>
                    <?php if ($isAdmin): ?>
                    <form method="POST" action="?url=Order/updateStatus" style="display:inline">
                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                        <select name="status" class="status-select" onchange="this.form.submit()">
                            <?php foreach(['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                            <option value="<?=$s?>" <?=$o['status']===$s?'selected':''?>><?=$statusLabels[$s]['label']??$s?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    <?php else: ?>
                        <?= statusBadge($o['status'], $statusLabels) ?>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px;white-space:nowrap"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                <td>
                    <a href="?url=Order/detail/<?= $o['id'] ?>" class="btn-sm btn-view">
                        <i class="fa-solid fa-eye"></i> Chi ti&#7871;t
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
