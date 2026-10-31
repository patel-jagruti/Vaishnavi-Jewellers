<?php
declare(strict_types=1);

/**
 * Admin Categories & Subcategories Management
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/catalog_db.php';

ensure_session();
require_login();
require_admin();

$pdo = db();
$action = $_GET['action'] ?? 'list';
$tab = $_GET['tab'] ?? 'categories';
if (!in_array($tab, ['categories', 'subcategories'], true)) {
  $tab = 'categories';
}
$notice = '';

// Handle Main Category Actions
if ($tab === 'categories') {
  if ($action === 'add' && !empty($_POST['name'])) {
    $name = trim($_POST['name']);
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    $slug = trim($slug, '-');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $gender = trim($_POST['gender'] ?? 'all');

    $stmt = $pdo->prepare("INSERT INTO categories (name, slug, display_order, gender) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $slug, $display_order, $gender]);
    $notice = 'Main category added successfully.';
    $action = 'list';
  }

  if ($action === 'update' && !empty($_GET['id']) && !empty($_POST['name'])) {
    $id = (int)$_GET['id'];
    $name = trim($_POST['name']);
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    $slug = trim($slug, '-');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $gender = trim($_POST['gender'] ?? 'all');

    $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, display_order = ?, gender = ? WHERE id = ?");
    $stmt->execute([$name, $slug, $display_order, $gender, $id]);
    $notice = 'Main category updated successfully.';
    $action = 'list';
  }

  if ($action === 'delete' && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $notice = 'Main category deleted successfully.';
    $action = 'list';
  }
}

// Handle Subcategory Actions
if ($tab === 'subcategories') {
  if ($action === 'add-sub' && !empty($_POST['name']) && !empty($_POST['category_id'])) {
    $name = trim($_POST['name']);
    $categoryId = (int)$_POST['category_id'];
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    $slug = trim($slug, '-');
    $display_order = (int)($_POST['display_order'] ?? 0);

    $stmt = $pdo->prepare("INSERT INTO subcategories (category_id, name, slug, display_order) VALUES (?, ?, ?, ?)");
    $stmt->execute([$categoryId, $name, $slug, $display_order]);
    $notice = 'Subcategory added successfully.';
    $action = 'list';
  }

  if ($action === 'update-sub' && !empty($_GET['id']) && !empty($_POST['name']) && !empty($_POST['category_id'])) {
    $id = (int)$_GET['id'];
    $name = trim($_POST['name']);
    $categoryId = (int)$_POST['category_id'];
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    $slug = trim($slug, '-');
    $display_order = (int)($_POST['display_order'] ?? 0);

    $stmt = $pdo->prepare("UPDATE subcategories SET category_id = ?, name = ?, slug = ?, display_order = ? WHERE id = ?");
    $stmt->execute([$categoryId, $name, $slug, $display_order, $id]);
    $notice = 'Subcategory updated successfully.';
    $action = 'list';
  }

  if ($action === 'delete-sub' && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM subcategories WHERE id = ?");
    $stmt->execute([$id]);
    $notice = 'Subcategory deleted successfully.';
    $action = 'list';
  }
}

// Fetch lists
$categories = $pdo->query("
  SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count
  FROM categories c
  ORDER BY c.display_order ASC, c.id ASC
")->fetchAll();

$subcategories = $pdo->query("
  SELECT s.*, c.name as parent_name, (SELECT COUNT(*) FROM products WHERE subcategory_id = s.id) as product_count
  FROM subcategories s
  JOIN categories c ON s.category_id = c.id
  ORDER BY c.display_order ASC, s.display_order ASC, s.id ASC
")->fetchAll();

// Get single category for edit
$editCategory = null;
if ($tab === 'categories' && $action === 'edit' && !empty($_GET['id'])) {
  $stmtEdit = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
  $stmtEdit->execute([(int)$_GET['id']]);
  $editCategory = $stmtEdit->fetch();
}

// Get single subcategory for edit
$editSubcategory = null;
if ($tab === 'subcategories' && $action === 'edit-sub' && !empty($_GET['id'])) {
  $stmtEdit = $pdo->prepare("SELECT * FROM subcategories WHERE id = ?");
  $stmtEdit->execute([(int)$_GET['id']]);
  $editSubcategory = $stmtEdit->fetch();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Categories | Admin Panel</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Poppins:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/visnavi%20jwallers/assets/css/admin-premium.css" />
</head>
<body>
  <div class="admin-layout">
    <aside class="admin-sidebar">
      <div class="brand">Vaishnavi Panel</div>
      <nav>
        <a href="/visnavi%20jwallers/admin/"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="/visnavi%20jwallers/admin/products.php"><i class="fas fa-gem"></i> Products</a>
        <a href="/visnavi%20jwallers/admin/orders.php"><i class="fas fa-shopping-bag"></i> Orders</a>
        <a href="/visnavi%20jwallers/admin/categories.php" class="active"><i class="fas fa-tags"></i> Categories</a>
        <a href="/visnavi%20jwallers/admin/users.php"><i class="fas fa-users"></i> Customers</a>
        <a href="/visnavi%20jwallers/admin/banners.php"><i class="fas fa-images"></i> Banners</a>
        <a href="/visnavi%20jwallers/admin/blogs.php"><i class="fas fa-newspaper"></i> Blogs</a>
        <a href="/visnavi%20jwallers/admin/notifications.php"><i class="fas fa-bell"></i> Alerts Log</a>
        <a href="/visnavi%20jwallers/admin/gold-rates.php"><i class="fas fa-coins"></i> Gold Rates</a>
        <a href="/visnavi%20jwallers/admin/settings.php"><i class="fas fa-cog"></i> Settings</a>
        <a href="/visnavi%20jwallers/index.php"><i class="fas fa-store"></i> Back to Store</a>
      </nav>
    </aside>

    <main class="admin-main">
      <h1>Categories Management</h1>

      <?php if ($notice): ?>
        <div style="background:#e8f5e9; color:#2e7d32; padding:15px; border-radius:8px; margin-bottom:24px; border: 1px solid #c8e6c9; font-weight: 500;"><i class="fas fa-check-circle"></i> <?= h($notice) ?></div>
      <?php endif; ?>

      <!-- Tabs Navigation -->
      <div class="tabs" style="display:flex; gap:24px; margin-bottom:28px; border-bottom:1px solid #e2e8f0; padding-bottom:8px;">
        <a href="?tab=categories" style="text-decoration:none; font-size:16px; font-weight:600; padding-bottom:10px; color:<?= $tab === 'categories' ? '#6B1E2D' : '#718096' ?>; border-bottom:3px solid <?= $tab === 'categories' ? '#6B1E2D' : 'transparent' ?>; transition:all 0.2s;">
          <i class="fas fa-folder-open"></i> Main Categories
        </a>
        <a href="?tab=subcategories" style="text-decoration:none; font-size:16px; font-weight:600; padding-bottom:10px; color:<?= $tab === 'subcategories' ? '#6B1E2D' : '#718096' ?>; border-bottom:3px solid <?= $tab === 'subcategories' ? '#6B1E2D' : 'transparent' ?>; transition:all 0.2s;">
          <i class="fas fa-folder-minus"></i> Subcategories
        </a>
      </div>

      <?php if ($tab === 'categories'): ?>
        <!-- Main Categories Tab -->
        <?php if ($action === 'edit' && $editCategory): ?>
          <div class="admin-section">
            <h2>Edit Main Category</h2>
            <form method="post" action="?tab=categories&action=update&id=<?= (int)$editCategory['id'] ?>">
              <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; max-width:700px;">
                <div>
                  <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Name</label>
                  <input type="text" name="name" class="input" value="<?= h((string)$editCategory['name']) ?>" required>
                </div>
                <div>
                  <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Target Gender</label>
                  <select name="gender" class="input" style="height:45px;">
                    <option value="all" <?= $editCategory['gender'] === 'all' ? 'selected' : '' ?>>All / Unisex</option>
                    <option value="female" <?= $editCategory['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                    <option value="male" <?= $editCategory['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                    <option value="unisex" <?= $editCategory['gender'] === 'unisex' ? 'selected' : '' ?>>Unisex</option>
                  </select>
                </div>
                <div>
                  <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Display Order</label>
                  <input type="number" name="display_order" class="input" value="<?= (int)$editCategory['display_order'] ?>">
                </div>
                <div style="grid-column:span 3; margin-top:10px;">
                  <button type="submit" class="btn">Update Category</button>
                  <a class="btn btn-secondary" href="?tab=categories" style="margin-left:8px;">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        <?php else: ?>
          <div class="admin-section">
            <h2>Add New Main Category</h2>
            <form method="post" action="?tab=categories&action=add">
              <div style="display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:16px; max-width:850px; align-items:end;">
                <div>
                  <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Category Name</label>
                  <input type="text" name="name" class="input" placeholder="e.g. Gold" required>
                </div>
                <div>
                  <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Gender</label>
                  <select name="gender" class="input" style="height:45px;">
                    <option value="all">All / Unisex</option>
                    <option value="female">Female</option>
                    <option value="male">Male</option>
                    <option value="unisex">Unisex</option>
                  </select>
                </div>
                <div>
                  <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Display Order</label>
                  <input type="number" name="display_order" class="input" placeholder="0" value="0">
                </div>
                <div>
                  <button type="submit" class="btn">Add Category</button>
                </div>
              </div>
            </form>
          </div>
        <?php endif; ?>

        <div class="admin-section">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Gender Category</th>
                <th>Display Order</th>
                <th>Products Count</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($categories as $c): ?>
                <tr>
                  <td>#<?= (int)$c['id'] ?></td>
                  <td style="font-weight:600; color:var(--maroon);"><?= h((string)$c['name']) ?></td>
                  <td style="font-size:13px; color:#666;"><?= h((string)$c['slug']) ?></td>
                  <td>
                    <span class="badge" style="background:#f5f5f5; color:#555; text-transform:uppercase;">
                      <?= h(ucfirst((string)$c['gender'])) ?>
                    </span>
                  </td>
                  <td style="font-weight:600;"><?= (int)$c['display_order'] ?></td>
                  <td style="font-weight:600;"><?= (int)$c['product_count'] ?> products</td>
                  <td>
                    <div class="actions-cell">
                      <a class="btn btn-sm btn-secondary" href="?tab=categories&action=edit&id=<?= (int)$c['id'] ?>"><i class="fas fa-edit"></i> Edit</a>
                      <a class="btn btn-sm btn-danger" href="?tab=categories&action=delete&id=<?= (int)$c['id'] ?>" onclick="return confirm('Delete category? All subcategories and products mapped to it will be affected.')"><i class="fas fa-trash"></i> Delete</a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php if (empty($categories)): ?>
            <p style="padding:24px; text-align:center; color:#666;">No main categories yet.</p>
          <?php endif; ?>
        </div>

      <?php else: ?>
        <!-- Subcategories Tab -->
        <?php if ($action === 'edit-sub' && $editSubcategory): ?>
          <div class="admin-section">
            <h2>Edit Subcategory</h2>
            <form method="post" action="?tab=subcategories&action=update-sub&id=<?= (int)$editSubcategory['id'] ?>">
              <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; max-width:700px;">
                <div>
                  <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Subcategory Name</label>
                  <input type="text" name="name" class="input" value="<?= h((string)$editSubcategory['name']) ?>" required>
                </div>
                <div>
                  <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Parent Main Category</label>
                  <select name="category_id" class="input" style="height:45px;" required>
                    <option value="">Select Main Category</option>
                    <?php foreach ($categories as $cat): ?>
                      <option value="<?= (int)$cat['id'] ?>" <?= $editSubcategory['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= h((string)$cat['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div>
                  <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Display Order</label>
                  <input type="number" name="display_order" class="input" value="<?= (int)$editSubcategory['display_order'] ?>">
                </div>
                <div style="grid-column:span 3; margin-top:10px;">
                  <button type="submit" class="btn">Update Subcategory</button>
                  <a class="btn btn-secondary" href="?tab=subcategories" style="margin-left:8px;">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        <?php else: ?>
          <div class="admin-section">
            <h2>Add New Subcategory</h2>
            <form method="post" action="?tab=subcategories&action=add-sub">
              <div style="display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:16px; max-width:850px; align-items:end;">
                <div>
                  <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Subcategory Name</label>
                  <input type="text" name="name" class="input" placeholder="e.g. Earrings" required>
                </div>
                <div>
                  <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Parent Main Category</label>
                  <select name="category_id" class="input" style="height:45px;" required>
                    <option value="">Select Main Category</option>
                    <?php foreach ($categories as $cat): ?>
                      <option value="<?= (int)$cat['id'] ?>"><?= h((string)$cat['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div>
                  <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Display Order</label>
                  <input type="number" name="display_order" class="input" placeholder="0" value="0">
                </div>
                <div>
                  <button type="submit" class="btn">Add Subcategory</button>
                </div>
              </div>
            </form>
          </div>
        <?php endif; ?>

        <div class="admin-section">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Subcategory Name</th>
                <th>Slug</th>
                <th>Parent Main Category</th>
                <th>Display Order</th>
                <th>Products Count</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($subcategories as $s): ?>
                <tr>
                  <td>#<?= (int)$s['id'] ?></td>
                  <td style="font-weight:600; color:var(--maroon);"><?= h((string)$s['name']) ?></td>
                  <td style="font-size:13px; color:#666;"><?= h((string)$s['slug']) ?></td>
                  <td>
                    <span class="badge" style="background:#eef2f6; color:#2b6cb0; font-weight:600;">
                      <?= h((string)$s['parent_name']) ?>
                    </span>
                  </td>
                  <td style="font-weight:600;"><?= (int)$s['display_order'] ?></td>
                  <td style="font-weight:600;"><?= (int)$s['product_count'] ?> products</td>
                  <td>
                    <div class="actions-cell">
                      <a class="btn btn-sm btn-secondary" href="?tab=subcategories&action=edit-sub&id=<?= (int)$s['id'] ?>"><i class="fas fa-edit"></i> Edit</a>
                      <a class="btn btn-sm btn-danger" href="?tab=subcategories&action=delete-sub&id=<?= (int)$s['id'] ?>" onclick="return confirm('Delete subcategory?')"><i class="fas fa-trash"></i> Delete</a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php if (empty($subcategories)): ?>
            <p style="padding:24px; text-align:center; color:#666;">No subcategories yet.</p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>