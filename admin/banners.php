<?php
declare(strict_types=1);

/**
 * Admin Banners Management
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/base_url.php';

ensure_session();
require_login();
require_admin();

$pdo = db();
$action = $_GET['action'] ?? 'list';
$notice = '';

// Handle add banner
if ($action === 'add' && !empty($_POST['title'])) {
  $title = trim($_POST['title']);
  $link = trim($_POST['link'] ?? '');
  $image = trim($_POST['image'] ?? '');
  $position = (int)($_POST['position'] ?? 0);
  $is_active = isset($_POST['is_active']) ? 1 : 0;

  $stmt = $pdo->prepare("INSERT INTO banners (title, link_url, image_url, display_order, is_active) VALUES (?, ?, ?, ?, ?)");
  $stmt->execute([$title, $link, $image, $position, $is_active]);
  $notice = 'Banner added successfully.';
  $action = 'list';
}

// Handle update
if ($action === 'update' && !empty($_GET['id']) && !empty($_POST['title'])) {
  $id = (int)$_GET['id'];
  $title = trim($_POST['title']);
  $link = trim($_POST['link'] ?? '');
  $image = trim($_POST['image'] ?? '');
  $position = (int)($_POST['position'] ?? 0);
  $is_active = isset($_POST['is_active']) ? 1 : 0;

  $stmt = $pdo->prepare("UPDATE banners SET title = ?, link_url = ?, image_url = ?, display_order = ?, is_active = ? WHERE id = ?");
  $stmt->execute([$title, $link, $image, $position, $is_active, $id]);
  $notice = 'Banner updated successfully.';
  $action = 'list';
}

// Handle toggle
if ($action === 'toggle' && !empty($_GET['id'])) {
  $id = (int)$_GET['id'];
  $pdo->query("UPDATE banners SET is_active = NOT is_active WHERE id = $id");
  $notice = 'Banner status toggled.';
  $action = 'list';
}

// Handle delete
if ($action === 'delete' && !empty($_GET['id'])) {
  $id = (int)$_GET['id'];
  $pdo->query("DELETE FROM banners WHERE id = $id");
  $notice = 'Banner deleted successfully.';
}

// Fetch banners
$banners = $pdo->query('SELECT * FROM banners ORDER BY display_order ASC, id ASC LIMIT 20')->fetchAll();

// Get single banner for edit
$editBanner = null;
if ($action === 'edit' && !empty($_GET['id'])) {
  $stmtEdit = $pdo->prepare("SELECT * FROM banners WHERE id = ?");
  $stmtEdit->execute([(int)$_GET['id']]);
  $editBanner = $stmtEdit->fetch();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Banners | Admin Panel</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Poppins:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/visnavi%20jwallers/assets/css/style.css" />
  <style>
    :root {
      --maroon: #6B1E2D;
      --maroon-dark: #4A101C;
      --gold: #D4AF37;
      --gold-light: #E8C95A;
      --dark: #0A0A0A;
      --gray-light: #F9F7F5;
      --text: #2D2D2D;
      --border: rgba(107,30,45,0.08);
      --radius: 12px;
      --transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    body { font-family: 'Poppins', sans-serif; background: #FAF9F6; color: var(--text); margin: 0; }
    .admin-layout { display: flex; min-height: 100vh; }
    
    /* Sidebar */
    .admin-sidebar { width: 260px; background: var(--dark); color: #FAF9F6; padding: 30px 20px; display: flex; flex-direction: column; border-right: 1px solid rgba(212,175,55,0.1); }
    .admin-sidebar .brand { font-family: 'Cormorant Garamond', serif; font-size: 24px; font-weight: 700; color: var(--gold); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 40px; text-align: center; }
    .admin-sidebar nav { display: flex; flex-direction: column; gap: 8px; }
    .admin-sidebar a { display: flex; align-items: center; gap: 12px; color: rgba(255,255,255,0.7); padding: 14px 20px; text-decoration: none; border-radius: var(--radius); font-weight: 500; font-size: 14px; transition: var(--transition); }
    .admin-sidebar a i { font-size: 16px; width: 20px; text-align: center; color: var(--gold); }
    .admin-sidebar a:hover, .admin-sidebar a.active { background: var(--maroon); color: #fff; box-shadow: 0 4px 15px rgba(107,30,45,0.4); }
    
    /* Main Area */
    .admin-main { flex: 1; padding: 40px; }
    .admin-main h1 { font-family: 'Cormorant Garamond', serif; font-size: 38px; font-weight: 700; color: var(--maroon); margin-top: 0; margin-bottom: 30px; letter-spacing: 0.5px; }
    
    /* Section card */
    .admin-section { background: #fff; padding: 30px; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: 0 10px 30px rgba(0,0,0,0.02); margin-bottom: 30px; }
    .admin-section h2 { font-family: 'Cormorant Garamond', serif; font-size: 24px; font-weight: 600; color: var(--maroon); margin-top: 0; margin-bottom: 20px; }
    
    /* Inputs */
    .input { font-family: 'Poppins', sans-serif; padding: 12px 16px; border: 1px solid #DDD7CE; border-radius: 8px; font-size: 14px; width: 100%; box-sizing: border-box; background: #fff; outline: none; transition: var(--transition); }
    .input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(212,175,55,0.1); }
    
    /* Table style */
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 16px; text-align: left; border-bottom: 1px solid #F0ECE7; vertical-align: middle; }
    th { background: var(--gray-light); font-weight: 600; font-size: 11px; text-transform: uppercase; color: var(--maroon); letter-spacing: 1px; }
    tr:hover td { background: #FAF9F8; }
    
    /* Buttons */
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 24px; background: var(--maroon); color: #fff; text-decoration: none; border-radius: 30px; border: none; cursor: pointer; font-weight: 600; font-size: 13px; transition: var(--transition); }
    .btn:hover { background: var(--maroon-dark); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(107,30,45,0.3); }
    .btn-sm { padding: 6px 12px; font-size: 11px; }
    .btn-danger { background: #d32f2f; }
    .btn-danger:hover { background: #c62828; box-shadow: 0 4px 12px rgba(211,47,47,0.3); }
    .btn-secondary { background: #777; }
    .btn-secondary:hover { background: #555; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    
    /* Badges */
    .badge { padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; display: inline-block; }
    .badge-active { background: #e8f5e9; color: #2e7d32; }
    .badge-inactive { background: rgba(107,30,45,0.1); color: var(--maroon); }
  </style>
</head>
<body>
  <div class="admin-layout">
    <aside class="admin-sidebar">
      <div class="brand">Vaishnavi Panel</div>
      <nav>
        <a href="/visnavi%20jwallers/admin/"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="/visnavi%20jwallers/admin/products.php"><i class="fas fa-gem"></i> Products</a>
        <a href="/visnavi%20jwallers/admin/orders.php"><i class="fas fa-shopping-bag"></i> Orders</a>
        <a href="/visnavi%20jwallers/admin/categories.php"><i class="fas fa-tags"></i> Categories</a>
        <a href="/visnavi%20jwallers/admin/users.php"><i class="fas fa-users"></i> Customers</a>
        <a href="/visnavi%20jwallers/admin/banners.php" class="active"><i class="fas fa-images"></i> Banners</a>
        <a href="/visnavi%20jwallers/admin/blogs.php"><i class="fas fa-newspaper"></i> Blogs</a>
        <a href="/visnavi%20jwallers/admin/settings.php"><i class="fas fa-cog"></i> Settings</a>
        <a href="/visnavi%20jwallers/index.php"><i class="fas fa-store"></i> Back to Store</a>
      </nav>
    </aside>

    <main class="admin-main">
      <h1>Banners Management</h1>

      <?php if ($notice): ?>
        <div style="background:#e8f5e9; color:#2e7d32; padding:15px; border-radius:8px; margin-bottom:24px; border: 1px solid #c8e6c9; font-weight: 500;"><i class="fas fa-check-circle"></i> <?= h($notice) ?></div>
      <?php endif; ?>

      <?php if ($action === 'edit' && $editBanner): ?>
        <div class="admin-section">
          <h2>Edit Banner</h2>
          <form method="post" action="?action=update&id=<?= (int)$editBanner['id'] ?>">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; max-width:700px;">
              <div style="grid-column:span 2;">
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Title</label>
                <input type="text" name="title" class="input" value="<?= h((string)$editBanner['title']) ?>" required>
              </div>
              <div style="grid-column:span 2;">
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Image URL</label>
                <input type="text" name="image" class="input" value="<?= h((string)$editBanner['image_url']) ?>" placeholder="/assets/images/..." required>
              </div>
              <div>
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Link URL</label>
                <input type="text" name="link" class="input" value="<?= h((string)$editBanner['link_url']) ?>" placeholder="/pages/shop.php?category=...">
              </div>
              <div>
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Display Order (Position)</label>
                <input type="number" name="position" class="input" value="<?= (int)$editBanner['display_order'] ?>">
              </div>
              <div style="grid-column:span 2; display:flex; align-items:center; gap:8px; margin-top:8px;">
                <input type="checkbox" name="is_active" id="edit_active" style="width:16px; height:16px; cursor:pointer;" <?= $editBanner['is_active'] ? 'checked' : '' ?>> 
                <label for="edit_active" style="font-size:13px; font-weight:600; color:#555; cursor:pointer;">Active and Visible on Storefront</label>
              </div>
              <div style="grid-column:span 2; margin-top:10px;">
                <button type="submit" class="btn">Update Banner</button>
                <a class="btn btn-secondary" href="/visnavi%20jwallers/admin/banners.php" style="margin-left:8px;">Cancel</a>
              </div>
            </div>
          </form>
        </div>
      <?php else: ?>
        <div class="admin-section">
          <h2>Add New Banner</h2>
          <form method="post" action="?action=add">
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:16px; max-width:900px; align-items:end;">
              <div>
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Title</label>
                <input type="text" name="title" class="input" placeholder="Banner Title" required>
              </div>
              <div>
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Image URL</label>
                <input type="text" name="image" class="input" placeholder="/assets/images/..." required>
              </div>
              <div>
                <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Link URL</label>
                <input type="text" name="link" class="input" placeholder="/pages/shop.php?category=...">
              </div>
              <div>
                <button type="submit" class="btn">Add Banner</button>
              </div>
            </div>
          </form>
        </div>
      <?php endif; ?>

      <div class="admin-section">
        <table>
          <thead>
            <tr>
              <th>Image Preview</th>
              <th>Banner Title</th>
              <th>Destination Link</th>
              <th>Display Order</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($banners as $b): ?>
              <tr>
                <td>
                  <?php if (!empty($b['image_url'])): ?>
                    <img src="<?= h(asset((string)$b['image_url'])) ?>" alt="" style="width:100px; height:50px; object-fit:cover; border-radius:6px; border:1px solid #eee;">
                  <?php else: ?>
                    <span style="color:#999; font-size:12px;"><i class="fas fa-image"></i> No image</span>
                  <?php endif; ?>
                </td>
                <td style="font-weight:600; color:var(--maroon);"><?= h((string)$b['title']) ?></td>
                <td style="font-size:13px; color:#666;"><?= h((string)$b['link_url']) ?></td>
                <td style="font-weight:600;"><?= (int)$b['display_order'] ?></td>
                <td>
                  <span class="badge <?= $b['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                    <?= $b['is_active'] ? 'Active' : 'Inactive' ?>
                  </span>
                </td>
                <td>
                  <div class="actions-cell">
                    <a class="btn btn-sm" href="?action=toggle&id=<?= (int)$b['id'] ?>" style="min-width: 60px;"><?= $b['is_active'] ? 'Disable' : 'Enable' ?></a>
                    <a class="btn btn-sm btn-secondary" href="?action=edit&id=<?= (int)$b['id'] ?>"><i class="fas fa-edit"></i> Edit</a>
                    <a class="btn btn-sm btn-danger" href="?action=delete&id=<?= (int)$b['id'] ?>" onclick="return confirm('Delete banner?')"><i class="fas fa-trash"></i> Delete</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <?php if (empty($banners)): ?>
          <p style="padding:40px; text-align:center; color:#888; font-size:14px;"><i class="fas fa-info-circle"></i> No storefront banners found. Create one above.</p>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>