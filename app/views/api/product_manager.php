<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản lý Sản phẩm - API Demo</title>
<link rel="stylesheet" href="<?= $baseUrl ?>/public/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<style>
/* ── Layout ── */
body { background: #f4f5f7; font-family: 'Segoe UI', sans-serif; }
.page-wrap { max-width: 1200px; margin: 0 auto; padding: 30px 20px; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; flex-wrap: wrap; gap: 12px; }
.page-title { font-size: 24px; font-weight: 800; display: flex; align-items: center; gap: 10px; }
/* ── Cards ── */
.card { background: #fff; border-radius: 16px; box-shadow: 0 4px 18px rgba(0,0,0,.07); overflow: hidden; }
.card-header { padding: 18px 24px; border-bottom: 2px solid #f5f5f5; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.card-header h3 { font-size: 17px; margin: 0; display: flex; align-items: center; gap: 8px; }
.card-body { padding: 24px; }
/* ── Toolbar ── */
.toolbar { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.search-group { display: flex; gap: 8px; }
.search-group input { padding: 9px 14px; border: 2px solid #eee; border-radius: 10px; font-size: 14px; outline: none; width: 220px; transition: .2s; }
.search-group input:focus { border-color: #ffd400; }
.search-group select { padding: 9px 12px; border: 2px solid #eee; border-radius: 10px; font-size: 14px; outline: none; }
/* ── Buttons ── */
.btn { padding: 9px 18px; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: .2s; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
.btn-primary   { background: #ffd400; color: #222; }
.btn-primary:hover { background: #e6be00; }
.btn-success   { background: #22c55e; color: #fff; }
.btn-success:hover { background: #16a34a; }
.btn-warning   { background: #f59e0b; color: #fff; }
.btn-warning:hover { background: #d97706; }
.btn-danger    { background: #ef4444; color: #fff; }
.btn-danger:hover { background: #dc2626; }
.btn-secondary { background: #e5e7eb; color: #555; }
.btn-secondary:hover { background: #d1d5db; }
.btn-sm { padding: 5px 12px; font-size: 12px; border-radius: 8px; }
/* ── Table ── */
.product-table { width: 100%; border-collapse: collapse; }
.product-table th { background: #fafafa; padding: 12px 16px; text-align: left; font-size: 13px; color: #666; border-bottom: 2px solid #f0f0f0; font-weight: 700; white-space: nowrap; }
.product-table td { padding: 12px 16px; border-bottom: 1px solid #f5f5f5; font-size: 14px; vertical-align: middle; }
.product-table tr:hover td { background: #fffbe6; }
.product-img { width: 54px; height: 54px; object-fit: cover; border-radius: 10px; border: 2px solid #eee; }
.price-text { font-weight: 700; color: #e00; }
.cat-badge { background: #e0e7ff; color: #4338ca; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.action-btns { display: flex; gap: 6px; }
/* ── Modal ── */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
.modal-overlay.show { display: flex; }
.modal-box { background: #fff; border-radius: 20px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.2); animation: slideUp .25s ease; }
@keyframes slideUp { from { transform: translateY(40px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.modal-header { padding: 20px 24px 16px; border-bottom: 2px solid #f5f5f5; display: flex; align-items: center; justify-content: space-between; }
.modal-header h4 { margin: 0; font-size: 18px; }
.modal-close { background: none; border: none; font-size: 20px; cursor: pointer; color: #888; padding: 4px; border-radius: 6px; }
.modal-close:hover { background: #f5f5f5; color: #333; }
.modal-body { padding: 24px; }
.modal-footer { padding: 16px 24px; border-top: 2px solid #f5f5f5; display: flex; gap: 10px; justify-content: flex-end; }
/* ── Form ── */
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 14px; color: #444; }
.form-group input, .form-group textarea, .form-group select {
    width: 100%; padding: 10px 14px; border: 2px solid #eee; border-radius: 10px;
    font-size: 14px; outline: none; transition: .2s; box-sizing: border-box; font-family: inherit;
}
.form-group input:focus, .form-group textarea:focus, .form-group select:focus { border-color: #ffd400; }
.form-group textarea { resize: vertical; min-height: 90px; }
.form-err { border-color: #ef4444 !important; }
.err-msg { color: #ef4444; font-size: 12px; margin-top: 4px; }
/* ── Toast ── */
.toast-wrap { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; }
.toast { padding: 14px 20px; border-radius: 12px; font-size: 14px; font-weight: 600; min-width: 260px; box-shadow: 0 4px 16px rgba(0,0,0,.15); animation: fadeIn .3s ease; display: flex; align-items: center; gap: 10px; }
@keyframes fadeIn { from { opacity: 0; transform: translateX(40px); } to { opacity: 1; transform: translateX(0); } }
.toast.success { background: #22c55e; color: #fff; }
.toast.error   { background: #ef4444; color: #fff; }
.toast.info    { background: #3b82f6; color: #fff; }
/* ── Stats ── */
.stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 24px; }
.stat-card { background: #fff; border-radius: 14px; padding: 18px 20px; box-shadow: 0 2px 10px rgba(0,0,0,.07); text-align: center; }
.stat-num { font-size: 30px; font-weight: 800; }
.stat-lbl { font-size: 12px; color: #888; margin-top: 4px; }
/* ── Loading ── */
.loading { text-align: center; padding: 40px; color: #aaa; }
.spinner { display: inline-block; width: 32px; height: 32px; border: 3px solid #eee; border-top-color: #ffd400; border-radius: 50%; animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.empty-state { text-align: center; padding: 50px; color: #aaa; }
.empty-state i { font-size: 48px; margin-bottom: 12px; display: block; }
/* ── API Response preview ── */
.api-preview { background: #1e1e2e; color: #cdd6f4; border-radius: 12px; padding: 16px; font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.6; overflow-x: auto; max-height: 320px; overflow-y: auto; white-space: pre-wrap; }
.tab-bar { display: flex; gap: 4px; margin-bottom: 20px; background: #f5f5f5; border-radius: 12px; padding: 6px; }
.tab-btn { flex: 1; padding: 9px 12px; border: none; background: transparent; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; color: #666; transition: .2s; }
.tab-btn.active { background: #fff; color: #222; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
.tab-pane { display: none; }
.tab-pane.active { display: block; }
@media (max-width: 640px) { .stats-row { grid-template-columns: 1fr 1fr; } .search-group input { width: 140px; } }
</style>
</head>
<body>
<?php include 'shares/header.php'; ?>
<nav class="menu">
  <a href="<?= $baseUrl ?>/Product/list">🏠 Trang chủ</a>
  <a href="<?= $baseUrl ?>/Api/productManager" style="color:#ffd400;font-weight:700">🔌 API Manager</a>
  <?php if ($isAdmin): ?>
    <a href="<?= $baseUrl ?>/Category/list">📂 Danh mục</a>
  <?php endif; ?>
</nav>

<div class="page-wrap">
  <div class="page-header">
    <div class="page-title">
      <i class="fa-solid fa-plug" style="color:#ffd400"></i>
      RESTful API – Quản lý Sản phẩm
    </div>
    <?php if ($isAdmin): ?>
      <button class="btn btn-success" id="btn-add-product">
        <i class="fa-solid fa-plus"></i> Thêm sản phẩm
      </button>
    <?php endif; ?>
  </div>

  <!-- Stats -->
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-num" id="stat-total" style="color:#222">–</div>
      <div class="stat-lbl">Tổng sản phẩm</div>
    </div>
    <div class="stat-card">
      <div class="stat-num" id="stat-categories" style="color:#4338ca">–</div>
      <div class="stat-lbl">Danh mục</div>
    </div>
    <div class="stat-card">
      <div class="stat-num" id="stat-api-status" style="color:#22c55e">✓</div>
      <div class="stat-lbl">API Status</div>
    </div>
  </div>

  <!-- Tabs -->
  <div class="tab-bar">
    <button class="tab-btn active" onclick="switchTab('products')">
      <i class="fa-solid fa-box"></i> Sản phẩm
    </button>
    <button class="tab-btn" onclick="switchTab('categories')">
      <i class="fa-solid fa-tags"></i> Danh mục
    </button>
    <button class="tab-btn" onclick="switchTab('api-docs')">
      <i class="fa-solid fa-code"></i> API Docs
    </button>
  </div>

  <!-- ── Tab: Sản phẩm ── -->
  <div class="tab-pane active" id="tab-products">
    <div class="card">
      <div class="card-header">
        <h3><i class="fa-solid fa-box"></i> Danh sách sản phẩm</h3>
        <div class="toolbar">
          <div class="search-group">
            <input type="text" id="search-input" placeholder="🔍 Tìm sản phẩm...">
            <select id="filter-category">
              <option value="">Tất cả danh mục</option>
            </select>
            <select id="sort-by">
              <option value="id">Mới nhất</option>
              <option value="name">Tên A-Z</option>
              <option value="price">Giá tăng dần</option>
            </select>
          </div>
          <button class="btn btn-secondary btn-sm" id="btn-refresh">
            <i class="fa-solid fa-rotate-right"></i> Tải lại
          </button>
        </div>
      </div>
      <div id="product-list-wrap">
        <div class="loading"><div class="spinner"></div><br>Đang tải dữ liệu...</div>
      </div>
    </div>
  </div>

  <!-- ── Tab: Danh mục ── -->
  <div class="tab-pane" id="tab-categories">
    <div class="card">
      <div class="card-header">
        <h3><i class="fa-solid fa-tags"></i> Danh sách danh mục</h3>
        <?php if ($isAdmin): ?>
          <button class="btn btn-success btn-sm" id="btn-add-category">
            <i class="fa-solid fa-plus"></i> Thêm danh mục
          </button>
        <?php endif; ?>
      </div>
      <div id="category-list-wrap">
        <div class="loading"><div class="spinner"></div><br>Đang tải...</div>
      </div>
    </div>
  </div>

  <!-- ── Tab: API Docs ── -->
  <div class="tab-pane" id="tab-api-docs">
    <div class="card">
      <div class="card-header"><h3><i class="fa-solid fa-code"></i> Tài liệu & Test API</h3></div>
      <div class="card-body">
        <!-- Bảng endpoints -->
        <table class="product-table" style="margin-bottom:24px">
          <thead>
            <tr>
              <th>Method</th><th>Endpoint</th><th>Mô tả</th><th>Quyền</th><th>Test</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $apiBase = $baseUrl;
            $endpoints = [
              ['GET','api/product','Lấy danh sách sản phẩm','Public'],
              ['GET','api/product/{id}','Lấy chi tiết sản phẩm','Public'],
              ['POST','api/product','Thêm sản phẩm mới','Admin'],
              ['PUT','api/product/{id}','Cập nhật sản phẩm','Admin'],
              ['DELETE','api/product/{id}','Xóa sản phẩm','Admin'],
              ['GET','api/category','Lấy danh sách danh mục','Public'],
              ['GET','api/category/{id}','Lấy chi tiết danh mục','Public'],
              ['POST','api/category','Thêm danh mục mới','Admin'],
              ['PUT','api/category/{id}','Cập nhật danh mục','Admin'],
              ['DELETE','api/category/{id}','Xóa danh mục','Admin'],
            ];
            $methodColors = ['GET'=>'#22c55e','POST'=>'#3b82f6','PUT'=>'#f59e0b','DELETE'=>'#ef4444'];
            foreach ($endpoints as $ep):
              $m = $ep[0]; $url2 = $ep[1]; $desc = $ep[2]; $perm = $ep[3];
              $col = $methodColors[$m];
              $testable = !str_contains($url2, '{id}');
            ?>
            <tr>
              <td><span style="background:<?= $col ?>;color:#fff;padding:3px 10px;border-radius:6px;font-size:12px;font-weight:700"><?= $m ?></span></td>
              <td><code style="background:#f5f5f5;padding:3px 8px;border-radius:6px;font-size:13px">/<?= $url2 ?></code></td>
              <td style="color:#555"><?= $desc ?></td>
              <td><span style="<?= $perm==='Admin'?'color:#c00;font-weight:700':'color:#22c55e;font-weight:700' ?>"><?= $perm ?></span></td>
              <td>
                <?php if ($testable): ?>
                  <button class="btn btn-secondary btn-sm"
                    onclick="testEndpoint('<?= $m ?>','<?= $baseUrl.'/'.$url2 ?>')">
                    ▶ Test
                  </button>
                <?php else: ?>
                  <span style="color:#aaa;font-size:12px">Cần ID</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <!-- API Response Preview -->
        <div class="form-group">
          <label><i class="fa-solid fa-terminal"></i> API Response Preview</label>
          <div class="api-preview" id="api-response-preview">// Nhấn nút "▶ Test" để xem kết quả JSON ở đây</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── Toast container ── -->
<div class="toast-wrap" id="toast-wrap"></div>

<!-- ── Modal: Thêm / Sửa Sản phẩm ── -->
<div class="modal-overlay" id="modal-product">
  <div class="modal-box">
    <div class="modal-header">
      <h4 id="modal-product-title">Thêm sản phẩm</h4>
      <button class="modal-close" onclick="closeModal('modal-product')">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="product-id">
      <div class="form-group">
        <label>Tên sản phẩm <span style="color:#e00">*</span></label>
        <input type="text" id="product-name" placeholder="Nhập tên sản phẩm">
        <div class="err-msg" id="err-name"></div>
      </div>
      <div class="form-group">
        <label>Mô tả</label>
        <textarea id="product-desc" placeholder="Mô tả sản phẩm"></textarea>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div class="form-group">
          <label>Giá (VND) <span style="color:#e00">*</span></label>
          <input type="number" id="product-price" placeholder="0" min="0">
          <div class="err-msg" id="err-price"></div>
        </div>
        <div class="form-group">
          <label>Danh mục</label>
          <select id="product-category">
            <option value="">-- Chọn danh mục --</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Tên file ảnh</label>
        <input type="text" id="product-image" placeholder="default.jpg">
        <div style="font-size:12px;color:#888;margin-top:4px">Tên file ảnh trong /public/images/</div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-product')">Hủy</button>
      <button class="btn btn-success" id="btn-save-product">
        <i class="fa-solid fa-floppy-disk"></i> Lưu
      </button>
    </div>
  </div>
</div>

<!-- ── Modal: Thêm / Sửa Danh mục ── -->
<div class="modal-overlay" id="modal-category">
  <div class="modal-box" style="max-width:400px">
    <div class="modal-header">
      <h4 id="modal-cat-title">Thêm danh mục</h4>
      <button class="modal-close" onclick="closeModal('modal-category')">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="cat-id">
      <div class="form-group">
        <label>Tên danh mục <span style="color:#e00">*</span></label>
        <input type="text" id="cat-name" placeholder="Nhập tên danh mục">
        <div class="err-msg" id="err-cat-name"></div>
      </div>
      <div class="form-group">
        <label>Mô tả</label>
        <textarea id="cat-desc" placeholder="Mô tả danh mục" style="min-height:70px"></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-category')">Hủy</button>
      <button class="btn btn-success" id="btn-save-category">
        <i class="fa-solid fa-floppy-disk"></i> Lưu
      </button>
    </div>
  </div>
</div>

<script>
const BASE     = '<?= $baseUrl ?>';
const IS_ADMIN = <?= $isAdmin ? 'true' : 'false' ?>;
const API      = BASE + '/api';

// ══════════════════════════════════════════════════════════
// UTILITIES
// ══════════════════════════════════════════════════════════
function toast(msg, type = 'success') {
  const t = $(`<div class="toast ${type}">
    <i class="fa-solid fa-${type==='success'?'check-circle':type==='error'?'xmark-circle':'info-circle'}"></i>
    ${msg}
  </div>`);
  $('#toast-wrap').append(t);
  setTimeout(() => t.fadeOut(400, () => t.remove()), 3500);
}

function openModal(id)  { $('#'+id).addClass('show'); }
function closeModal(id) { $('#'+id).removeClass('show'); }

function switchTab(name) {
  $('.tab-btn').removeClass('active');
  $('.tab-btn').eq(['products','categories','api-docs'].indexOf(name)).addClass('active');
  $('.tab-pane').removeClass('active');
  $('#tab-'+name).addClass('active');
  if (name === 'categories') loadCategories();
}

function formatPrice(p) {
  return new Intl.NumberFormat('vi-VN').format(p) + ' VND';
}

function clearErrors() {
  $('.err-msg').text('');
  $('input, textarea, select').removeClass('form-err');
}

// ══════════════════════════════════════════════════════════
// LOAD DANH MỤC vào <select>
// ══════════════════════════════════════════════════════════
function loadCategorySelect() {
  $.getJSON(API + '/category', function(res) {
    if (!res.success) return;
    $('#filter-category, #product-category').each(function() {
      const sel = $(this);
      const isFilter = sel.attr('id') === 'filter-category';
      if (!isFilter) sel.empty().append('<option value="">-- Chọn danh mục --</option>');
      res.data.forEach(c => {
        sel.append(`<option value="${c.id}">${c.name} (${c.product_count || 0})</option>`);
      });
    });
    $('#stat-categories').text(res.total);
  });
}

// ══════════════════════════════════════════════════════════
// LOAD SẢN PHẨM
// ══════════════════════════════════════════════════════════
function loadProducts() {
  const search = $('#search-input').val().trim();
  const catId  = $('#filter-category').val();
  const sort   = $('#sort-by').val();
  const order  = sort === 'price' ? 'ASC' : 'DESC';

  let url = `${API}/product?sort=${sort}&order=${order}`;
  if (search) url += `&search=${encodeURIComponent(search)}`;
  if (catId)  url += `&category_id=${catId}`;

  $('#product-list-wrap').html('<div class="loading"><div class="spinner"></div><br>Đang tải...</div>');

  $.getJSON(url, function(res) {
    if (!res.success) {
      $('#product-list-wrap').html('<div class="empty-state"><i class="fa-solid fa-exclamation-triangle"></i><p>Lỗi tải dữ liệu</p></div>');
      return;
    }
    $('#stat-total').text(res.total);
    if (res.total === 0) {
      $('#product-list-wrap').html('<div class="empty-state"><i class="fa-solid fa-box-open"></i><p>Không có sản phẩm nào</p></div>');
      return;
    }

    let html = `<table class="product-table">
      <thead><tr>
        <th>#</th><th>Ảnh</th><th>Tên sản phẩm</th><th>Giá</th><th>Danh mục</th>
        ${IS_ADMIN ? '<th>Thao tác</th>' : ''}
      </tr></thead><tbody>`;

    res.data.forEach(p => {
      const imgSrc = p.image_url;
      html += `<tr>
        <td style="color:#aaa;font-size:13px">${p.id}</td>
        <td><img src="${imgSrc}" class="product-img"
             onerror="this.src='https://via.placeholder.com/54x54?text=No+Img'"></td>
        <td>
          <div style="font-weight:600">${p.name}</div>
          <div style="font-size:12px;color:#888;margin-top:2px">${(p.description||'').substring(0,60)}${(p.description||'').length>60?'…':''}</div>
        </td>
        <td class="price-text">${p.price_display}</td>
        <td>${p.category_name ? `<span class="cat-badge">${p.category_name}</span>` : '<span style="color:#ccc">–</span>'}</td>
        ${IS_ADMIN ? `<td>
          <div class="action-btns">
            <button class="btn btn-warning btn-sm" onclick="editProduct(${p.id})">
              <i class="fa-solid fa-pen"></i> Sửa
            </button>
            <button class="btn btn-danger btn-sm" onclick="deleteProduct(${p.id}, '${p.name.replace(/'/g,"\\'")}')">
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
        </td>` : ''}
      </tr>`;
    });
    html += '</tbody></table>';
    $('#product-list-wrap').html(html);
  }).fail(function() {
    $('#product-list-wrap').html('<div class="empty-state"><i class="fa-solid fa-wifi"></i><p>Không thể kết nối API</p></div>');
    $('#stat-api-status').text('✗').css('color','#ef4444');
  });
}

// ══════════════════════════════════════════════════════════
// LOAD DANH MỤC (tab)
// ══════════════════════════════════════════════════════════
function loadCategories() {
  $('#category-list-wrap').html('<div class="loading"><div class="spinner"></div><br>Đang tải...</div>');
  $.getJSON(API + '/category', function(res) {
    if (!res.success || res.total === 0) {
      $('#category-list-wrap').html('<div class="empty-state"><i class="fa-solid fa-tags"></i><p>Chưa có danh mục</p></div>');
      return;
    }
    let html = `<table class="product-table">
      <thead><tr><th>#</th><th>Tên danh mục</th><th>Mô tả</th><th>Số sản phẩm</th>
      ${IS_ADMIN ? '<th>Thao tác</th>' : ''}</tr></thead><tbody>`;

    res.data.forEach(c => {
      html += `<tr>
        <td style="color:#aaa">${c.id}</td>
        <td style="font-weight:600">${c.name}</td>
        <td style="color:#666">${c.description||'–'}</td>
        <td><span style="background:#e0e7ff;color:#4338ca;padding:2px 10px;border-radius:20px;font-size:13px;font-weight:700">${c.product_count}</span></td>
        ${IS_ADMIN ? `<td><div class="action-btns">
          <button class="btn btn-warning btn-sm" onclick="editCategory(${c.id})">
            <i class="fa-solid fa-pen"></i> Sửa
          </button>
          <button class="btn btn-danger btn-sm" onclick="deleteCategory(${c.id}, '${c.name.replace(/'/g,"\\'")}')">
            <i class="fa-solid fa-trash"></i>
          </button>
        </div></td>` : ''}
      </tr>`;
    });
    html += '</tbody></table>';
    $('#category-list-wrap').html(html);
  });
}

// ══════════════════════════════════════════════════════════
// THÊM / SỬA SẢN PHẨM
// ══════════════════════════════════════════════════════════
$('#btn-add-product').click(function() {
  clearErrors();
  $('#modal-product-title').text('Thêm sản phẩm mới');
  $('#product-id').val('');
  $('#product-name').val('');
  $('#product-desc').val('');
  $('#product-price').val('');
  $('#product-category').val('');
  $('#product-image').val('');
  openModal('modal-product');
});

function editProduct(id) {
  clearErrors();
  $.getJSON(`${API}/product/${id}`, function(res) {
    if (!res.success) { toast('Không tìm thấy sản phẩm', 'error'); return; }
    const p = res.data;
    $('#modal-product-title').text('Chỉnh sửa sản phẩm #' + id);
    $('#product-id').val(p.id);
    $('#product-name').val(p.name);
    $('#product-desc').val(p.description);
    $('#product-price').val(p.price);
    $('#product-category').val(p.category_id || '');
    $('#product-image').val(p.image);
    openModal('modal-product');
  });
}

$('#btn-save-product').click(function() {
  clearErrors();
  const id    = $('#product-id').val();
  const isNew = !id;
  const data  = {
    name:        $('#product-name').val().trim(),
    description: $('#product-desc').val().trim(),
    price:       parseFloat($('#product-price').val()) || 0,
    category_id: $('#product-category').val() || null,
    image:       $('#product-image').val().trim() || 'default.jpg',
  };

  const url    = isNew ? `${API}/product` : `${API}/product/${id}`;
  const method = isNew ? 'POST' : 'PUT';

  $.ajax({
    url, method,
    contentType: 'application/json',
    data: JSON.stringify(data),
    success: function(res) {
      if (res.success) {
        toast(res.message, 'success');
        closeModal('modal-product');
        loadProducts();
        loadCategorySelect();
      } else {
        if (res.errors) {
          Object.entries(res.errors).forEach(([k, v]) => {
            $(`#err-${k}`).text(v);
            $(`#product-${k}`).addClass('form-err');
          });
        }
        toast(res.message || 'Có lỗi xảy ra', 'error');
      }
    },
    error: function(xhr) {
      const res = xhr.responseJSON || {};
      if (res.errors) {
        Object.entries(res.errors).forEach(([k, v]) => {
          $(`#err-${k}`).text(v);
          $(`#product-${k}`).addClass('form-err');
        });
      }
      toast(res.message || 'Lỗi kết nối API', 'error');
    }
  });
});

function deleteProduct(id, name) {
  if (!confirm(`Xóa sản phẩm "${name}"? Thao tác này không thể hoàn tác!`)) return;
  $.ajax({
    url: `${API}/product/${id}`,
    method: 'DELETE',
    success: function(res) {
      toast(res.message, res.success ? 'success' : 'error');
      if (res.success) { loadProducts(); loadCategorySelect(); }
    },
    error: function(xhr) {
      toast(xhr.responseJSON?.message || 'Lỗi xóa sản phẩm', 'error');
    }
  });
}

// ══════════════════════════════════════════════════════════
// THÊM / SỬA DANH MỤC
// ══════════════════════════════════════════════════════════
$('#btn-add-category').click(function() {
  clearErrors();
  $('#modal-cat-title').text('Thêm danh mục mới');
  $('#cat-id').val('');
  $('#cat-name').val('');
  $('#cat-desc').val('');
  openModal('modal-category');
});

function editCategory(id) {
  clearErrors();
  $.getJSON(`${API}/category/${id}`, function(res) {
    if (!res.success) { toast('Không tìm thấy danh mục', 'error'); return; }
    const c = res.data;
    $('#modal-cat-title').text('Chỉnh sửa danh mục #' + id);
    $('#cat-id').val(c.id);
    $('#cat-name').val(c.name);
    $('#cat-desc').val(c.description);
    openModal('modal-category');
  });
}

$('#btn-save-category').click(function() {
  clearErrors();
  const id    = $('#cat-id').val();
  const isNew = !id;
  const data  = { name: $('#cat-name').val().trim(), description: $('#cat-desc').val().trim() };

  $.ajax({
    url:    isNew ? `${API}/category` : `${API}/category/${id}`,
    method: isNew ? 'POST' : 'PUT',
    contentType: 'application/json',
    data: JSON.stringify(data),
    success: function(res) {
      if (res.success) {
        toast(res.message, 'success');
        closeModal('modal-category');
        loadCategories();
        loadCategorySelect();
      } else {
        if (res.errors?.name) {
          $('#err-cat-name').text(res.errors.name);
          $('#cat-name').addClass('form-err');
        }
        toast(res.message || 'Có lỗi', 'error');
      }
    },
    error: function(xhr) {
      const res = xhr.responseJSON || {};
      if (res.errors?.name) { $('#err-cat-name').text(res.errors.name); }
      toast(res.message || 'Lỗi kết nối', 'error');
    }
  });
});

function deleteCategory(id, name) {
  if (!confirm(`Xóa danh mục "${name}"? Sản phẩm thuộc danh mục này sẽ mất liên kết.`)) return;
  $.ajax({
    url: `${API}/category/${id}`, method: 'DELETE',
    success: function(res) {
      toast(res.message, res.success ? 'success' : 'error');
      if (res.success) { loadCategories(); loadCategorySelect(); }
    }
  });
}

// ══════════════════════════════════════════════════════════
// API DOCS TEST
// ══════════════════════════════════════════════════════════
function testEndpoint(method, url) {
  $('#api-response-preview').text('// Đang gọi: ' + method + ' ' + url + '...');
  $.ajax({
    url, method,
    success: function(res) {
      $('#api-response-preview').text(JSON.stringify(res, null, 2));
    },
    error: function(xhr) {
      try {
        $('#api-response-preview').text(JSON.stringify(JSON.parse(xhr.responseText), null, 2));
      } catch(e) {
        $('#api-response-preview').text('// HTTP ' + xhr.status + ': ' + xhr.statusText);
      }
    }
  });
}

// ══════════════════════════════════════════════════════════
// EVENTS & INIT
// ══════════════════════════════════════════════════════════
let searchTimer;
$('#search-input').on('input', function() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(loadProducts, 400);
});
$('#filter-category, #sort-by').change(loadProducts);
$('#btn-refresh').click(loadProducts);

// Đóng modal khi click ngoài
$('.modal-overlay').click(function(e) {
  if ($(e.target).hasClass('modal-overlay')) $(this).removeClass('show');
});

// Khởi động
$(document).ready(function() {
  loadCategorySelect();
  loadProducts();
});
</script>
</body>
</html>
