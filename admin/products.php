<?php
declare(strict_types=1);

/**
 * Admin Products Management
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/catalog_db.php';
require_once __DIR__ . '/../includes/base_url.php';

ensure_session();
require_login();
require_admin();

$pdo = db();
$action = $_GET['action'] ?? 'list';
$notice = '';

// Handle actions
if ($action === 'delete' && !empty($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
  $stmt->execute([$id]);
  $notice = 'Product deleted successfully.';
}

if ($action === 'toggle' && !empty($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare("UPDATE products SET is_active = NOT is_active WHERE id = ?");
  $stmt->execute([$id]);
  $notice = 'Product status toggled.';
}

// Handle Update Action (POST request to update a product)
if ($action === 'update' && !empty($_GET['id']) && !empty($_POST['name'])) {
  $id = (int)$_GET['id'];
  $name = trim($_POST['name']);
  $basePrice = (float)($_POST['base_price'] ?? 0);
  $metal = trim($_POST['metal'] ?? '');
  $stock = (int)($_POST['stock'] ?? 100);
  $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
  $subcategoryId = !empty($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : null;
  $gender = trim($_POST['gender'] ?? 'unisex');
  if (!in_array($gender, ['female', 'male', 'unisex'], true)) {
    $gender = 'unisex';
  }

  $priceType = trim($_POST['price_type'] ?? 'fixed');
  $purity = !empty($_POST['purity']) ? trim($_POST['purity']) : null;
  $makingChargePercent = (float)($_POST['making_charge_percent'] ?? 8.00);
  $weightGrams = !empty($_POST['weight_grams']) ? (float)$_POST['weight_grams'] : null;

  $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
  $slug = trim($slug, '-');
  if ($slug === '') {
    $slug = 'product-' . time();
  }
  
  $origSlug = $slug;
  $suffix = 1;
  while (true) {
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE slug = ? AND id != ?");
    $checkStmt->execute([$slug, $id]);
    if ($checkStmt->fetchColumn() == 0) {
      break;
    }
    $slug = $origSlug . '-' . $suffix;
    $suffix++;
  }
  
  $stmt = $pdo->prepare("
    UPDATE products SET 
      name = ?, 
      slug = ?, 
      base_price = ?, 
      price_type = ?, 
      purity = ?, 
      making_charge_percent = ?, 
      weight_grams = ?, 
      stock = ?, 
      metal = ?, 
      category_id = ?, 
      subcategory_id = ?, 
      gender = ? 
    WHERE id = ?
  ");
  $stmt->execute([
    $name, 
    $slug, 
    $basePrice, 
    $priceType, 
    $purity, 
    $makingChargePercent, 
    $weightGrams, 
    $stock, 
    $metal, 
    $categoryId, 
    $subcategoryId, 
    $gender, 
    $id
  ]);

  // Handle image upload if new file is supplied
  if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES['image_file']['tmp_name'];
    $originalName = basename($_FILES['image_file']['name']);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (in_array($extension, $allowed, true)) {
      $newFilename = uniqid('prod_', true) . '.' . $extension;
      $targetDir = __DIR__ . '/../assets/images/products/';
      if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0755, true);
      }
      $targetFile = $targetDir . $newFilename;
      if (@move_uploaded_file($tmpName, $targetFile)) {
        $imageUrl = '/visnavi%20jwallers/assets/images/products/' . $newFilename;
        
        // Remove existing primary image first
        $stmtDel = $pdo->prepare("DELETE FROM product_images WHERE product_id = ? AND is_primary = 1");
        $stmtDel->execute([$id]);
        
        $stmtImg = $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_primary, display_order) VALUES (?, ?, 1, 0)");
        $stmtImg->execute([$id, $imageUrl]);
      }
    }
  }

  $notice = 'Product updated successfully.';
  $action = 'list';
}

// Handle Add Action
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
  $name = trim($_POST['name']);
  $basePrice = (float)($_POST['base_price'] ?? 0);
  $metal = trim($_POST['metal'] ?? '');
  $stock = (int)($_POST['stock'] ?? 100);
  $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
  $subcategoryId = !empty($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : null;
  $gender = trim($_POST['gender'] ?? 'unisex');
  if (!in_array($gender, ['female', 'male', 'unisex'], true)) {
    $gender = 'unisex';
  }

  $priceType = trim($_POST['price_type'] ?? 'fixed');
  $purity = !empty($_POST['purity']) ? trim($_POST['purity']) : null;
  $makingChargePercent = (float)($_POST['making_charge_percent'] ?? 8.00);
  $weightGrams = !empty($_POST['weight_grams']) ? (float)$_POST['weight_grams'] : null;

  $imageUrl = '';
  if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES['image_file']['tmp_name'];
    $originalName = basename($_FILES['image_file']['name']);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (in_array($extension, $allowed, true)) {
      $newFilename = uniqid('prod_', true) . '.' . $extension;
      $targetDir = __DIR__ . '/../assets/images/products/';
      if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0755, true);
      }
      $targetFile = $targetDir . $newFilename;
      if (@move_uploaded_file($tmpName, $targetFile)) {
        $imageUrl = '/visnavi%20jwallers/assets/images/products/' . $newFilename;
      }
    }
  }

  $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
  $slug = trim($slug, '-');
  if ($slug === '') {
    $slug = 'product-' . time();
  }
  
  $origSlug = $slug;
  $suffix = 1;
  while (true) {
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE slug = ?");
    $checkStmt->execute([$slug]);
    if ($checkStmt->fetchColumn() == 0) {
      break;
    }
    $slug = $origSlug . '-' . $suffix;
    $suffix++;
  }

  $sku = 'SKU-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $slug), 0, 8)) . '-' . rand(100, 999);
  while (true) {
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE sku = ?");
    $checkStmt->execute([$sku]);
    if ($checkStmt->fetchColumn() == 0) {
      break;
    }
    $sku = 'SKU-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $slug), 0, 8)) . '-' . rand(1000, 9999);
  }

  $stmt = $pdo->prepare("
    INSERT INTO products (
      name, slug, sku, base_price, price_type, purity, making_charge_percent, weight_grams, stock, metal, category_id, subcategory_id, gender, is_active
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
  ");
  $stmt->execute([
    $name, 
    $slug, 
    $sku, 
    $basePrice, 
    $priceType, 
    $purity, 
    $makingChargePercent, 
    $weightGrams, 
    $stock, 
    $metal, 
    $categoryId, 
    $subcategoryId, 
    $gender
  ]);
  $productId = $pdo->lastInsertId();

  if ($imageUrl !== '' && $productId) {
    $stmtImg = $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_primary, display_order) VALUES (?, ?, 1, 0)");
    $stmtImg->execute([$productId, $imageUrl]);
  }

  $notice = 'Product added successfully.';
  $action = 'list';
}

// Fetch categories and subcategories for forms
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY display_order ASC, name ASC')->fetchAll();
$subcategories = $pdo->query('SELECT id, category_id, name FROM subcategories ORDER BY display_order ASC, name ASC')->fetchAll();

// Get single product for editing
$editProduct = null;
if ($action === 'edit' && !empty($_GET['id'])) {
  $stmtEdit = $pdo->prepare("
    SELECT p.*, pi.image_url as primary_image
    FROM products p
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    WHERE p.id = ?
  ");
  $stmtEdit->execute([(int)$_GET['id']]);
  $editProduct = $stmtEdit->fetch();
}

$products = $pdo->query('
  SELECT p.*, c.name as category_name, s.name as subcategory_name, pi.image_url as primary_image
  FROM products p
  LEFT JOIN categories c ON p.category_id = c.id
  LEFT JOIN subcategories s ON p.subcategory_id = s.id
  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
  ORDER BY p.id DESC
  LIMIT 50
')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Products | Admin Panel</title>
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
        <a href="/visnavi%20jwallers/admin/products.php" class="active"><i class="fas fa-gem"></i> Products</a>
        <a href="/visnavi%20jwallers/admin/orders.php"><i class="fas fa-shopping-bag"></i> Orders</a>
        <a href="/visnavi%20jwallers/admin/categories.php"><i class="fas fa-tags"></i> Categories</a>
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
      <h1>Products Management</h1>

      <?php if ($notice): ?>
        <div style="background:#e8f5e9; color:#2e7d32; padding:15px; border-radius:8px; margin-bottom:24px; border:1px solid #c8e6c9; font-weight:500;"><i class="fas fa-check-circle"></i> <?= h($notice) ?></div>
      <?php endif; ?>

      <?php if ($action === 'edit' && $editProduct): ?>
        <div class="admin-section">
          <h2>Edit Product</h2>
          <form method="post" action="?action=update&id=<?= (int)$editProduct['id'] ?>" enctype="multipart/form-data" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; align-items:end;">
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Product Name</label>
              <input type="text" name="name" value="<?= h((string)$editProduct['name']) ?>" class="input" required>
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Base Price (₹)</label>
              <input type="number" name="base_price" value="<?= (float)$editProduct['base_price'] ?>" class="input" required>
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Metal Details</label>
              <input type="text" name="metal" value="<?= h((string)$editProduct['metal']) ?>" class="input">
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Stock Level</label>
              <input type="number" name="stock" value="<?= (int)$editProduct['stock'] ?>" class="input" required>
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Main Category</label>
              <select name="category_id" class="input" style="height:45px;" required>
                <option value="">Select Main Category</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= (int)$cat['id'] ?>" <?= $editProduct['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= h((string)$cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Subcategory</label>
              <select name="subcategory_id" class="input" style="height:45px;">
                <option value="">Select Subcategory</option>
                <?php foreach ($subcategories as $sub): ?>
                  <option value="<?= (int)$sub['id'] ?>" data-parent-id="<?= (int)$sub['category_id'] ?>" <?= $editProduct['subcategory_id'] == $sub['id'] ? 'selected' : '' ?>><?= h((string)$sub['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Gender Target</label>
              <select name="gender" class="input" style="height:45px;">
                <option value="unisex" <?= $editProduct['gender'] === 'unisex' ? 'selected' : '' ?>>Unisex</option>
                <option value="female" <?= $editProduct['gender'] === 'female' ? 'selected' : '' ?>>Women</option>
                <option value="male" <?= $editProduct['gender'] === 'male' ? 'selected' : '' ?>>Men</option>
              </select>
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">New Image (optional)</label>
              <input type="file" name="image_file" accept="image/*" class="input" style="padding: 8px;">
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Pricing Type</label>
              <select name="price_type" class="input" style="height:45px;">
                <option value="fixed" <?= ($editProduct['price_type'] ?? 'fixed') === 'fixed' ? 'selected' : '' ?>>Fixed Price</option>
                <option value="dynamic" <?= ($editProduct['price_type'] ?? 'fixed') === 'dynamic' ? 'selected' : '' ?>>Dynamic Rate (Gold/Silver)</option>
              </select>
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Metal Purity (for Dynamic)</label>
              <select name="purity" class="input" style="height:45px;">
                <option value="">N/A</option>
                <option value="24k" <?= ($editProduct['purity'] ?? '') === '24k' ? 'selected' : '' ?>>24K Gold</option>
                <option value="22k" <?= ($editProduct['purity'] ?? '') === '22k' ? 'selected' : '' ?>>22K Gold</option>
                <option value="18k" <?= ($editProduct['purity'] ?? '') === '18k' ? 'selected' : '' ?>>18K Gold</option>
                <option value="silver" <?= ($editProduct['purity'] ?? '') === 'silver' ? 'selected' : '' ?>>Silver</option>
              </select>
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Metal Weight (grams)</label>
              <input type="number" step="0.01" name="weight_grams" value="<?= isset($editProduct['weight_grams']) ? (float)$editProduct['weight_grams'] : '' ?>" class="input" placeholder="e.g. 10.50">
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Making Charges (%)</label>
              <input type="number" step="0.01" name="making_charge_percent" value="<?= (float)($editProduct['making_charge_percent'] ?? 8.00) ?>" class="input" placeholder="e.g. 8.00">
            </div>
            <div style="grid-column: span 2; display: flex; gap: 8px; margin-top: 10px;">
              <button type="submit" class="btn"><i class="fas fa-save"></i> Save Changes</button>
              <a class="btn btn-secondary" href="products.php">Cancel</a>
            </div>
          </form>
        </div>
      <?php else: ?>
        <div class="admin-section">
          <h2>Add Product</h2>
          <form method="post" action="?action=add" enctype="multipart/form-data" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; align-items:end;">
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Product Name</label>
              <input type="text" name="name" placeholder="Product Name" class="input" required>
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Base Price (₹)</label>
              <input type="number" name="base_price" placeholder="Price" class="input" required>
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Metal Details</label>
              <input type="text" name="metal" placeholder="e.g. 18K Gold" class="input">
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Stock Level</label>
              <input type="number" name="stock" placeholder="100" value="100" class="input" required>
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Main Category</label>
              <select name="category_id" class="input" style="height:45px;" required>
                <option value="">Select Main Category</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= (int)$cat['id'] ?>"><?= h((string)$cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Subcategory</label>
              <select name="subcategory_id" class="input" style="height:45px;">
                <option value="">Select Subcategory</option>
                <?php foreach ($subcategories as $sub): ?>
                  <option value="<?= (int)$sub['id'] ?>" data-parent-id="<?= (int)$sub['category_id'] ?>"><?= h((string)$sub['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Gender Target</label>
              <select name="gender" class="input" style="height:45px;">
                <option value="unisex">Unisex</option>
                <option value="female">Women</option>
                <option value="male">Men</option>
              </select>
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Primary Image File</label>
              <input type="file" name="image_file" accept="image/*" class="input" style="padding: 8px;">
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Pricing Type</label>
              <select name="price_type" class="input" style="height:45px;">
                <option value="fixed">Fixed Price</option>
                <option value="dynamic">Dynamic Rate (Gold/Silver)</option>
              </select>
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Metal Purity (for Dynamic)</label>
              <select name="purity" class="input" style="height:45px;">
                <option value="">N/A</option>
                <option value="24k">24K Gold</option>
                <option value="22k">22K Gold</option>
                <option value="18k">18K Gold</option>
                <option value="silver">Silver</option>
              </select>
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Metal Weight (grams)</label>
              <input type="number" step="0.01" name="weight_grams" class="input" placeholder="e.g. 10.50">
            </div>
            <div>
              <label style="display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#555;">Making Charges (%)</label>
              <input type="number" step="0.01" name="making_charge_percent" value="8.00" class="input" placeholder="e.g. 8.00">
            </div>
            <div style="grid-column: span 2; display: flex; gap: 8px; margin-top: 10px;">
              <button type="submit" class="btn"><i class="fas fa-plus"></i> Add Product</button>
            </div>
          </form>
        </div>
      <?php endif; ?>

      <div class="admin-section" style="overflow-x: auto; width: 100%;">
        <table style="width: 100%; min-width: 1100px; border-collapse: collapse;">
          <thead>
            <tr>
              <th>Image Preview</th>
              <th>Product Name</th>
              <th>SKU</th>
              <th>Category Path</th>
              <th>Gender</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $p): ?>
              <tr>
                <td>
                  <?php if (!empty($p['primary_image'])): ?>
                    <img src="<?= h(asset((string)$p['primary_image'])) ?>" alt="" style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:1px solid #eee;">
                  <?php else: ?>
                    <span style="color:#999; font-size:12px;"><i class="fas fa-image"></i> No image</span>
                  <?php endif; ?>
                </td>
                <td style="font-weight:600; color:var(--maroon);"><?= h((string)$p['name']) ?></td>
                <td style="font-size:13px; color:#666;"><?= h((string)$p['sku']) ?></td>
                <td style="font-size:13px; font-weight:600; color:#555;">
                  <?= h((string)($p['category_name'] ?? 'Uncategorized')) ?><?= !empty($p['subcategory_name']) ? ' &gt; ' . h((string)$p['subcategory_name']) : '' ?>
                </td>
                <td>
                  <span class="badge" style="background:#f5f5f5; color:#555; font-size:11px; text-transform:uppercase;">
                    <?= h(ucfirst((string)($p['gender'] ?? 'unisex'))) ?>
                  </span>
                </td>
                <td style="font-weight:600;"><?= money((float)$p['base_price']) ?></td>
                <td style="font-weight:600;"><?= (int)$p['stock'] ?></td>
                <td>
                  <span class="badge <?= $p['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                    <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
                  </span>
                </td>
                <td>
                  <div class="actions-cell" style="display: flex; gap: 8px; flex-wrap: nowrap; align-items: center;">
                    <a class="btn btn-sm" href="?action=toggle&id=<?= (int)$p['id'] ?>" style="min-width: 60px;"><?= $p['is_active'] ? 'Disable' : 'Enable' ?></a>
                    <a class="btn btn-sm btn-secondary" href="?action=edit&id=<?= (int)$p['id'] ?>"><i class="fas fa-edit"></i> Edit</a>
                    <a class="btn btn-sm btn-danger" href="?action=delete&id=<?= (int)$p['id'] ?>" onclick="return confirm('Delete product?')"><i class="fas fa-trash"></i> Delete</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Dynamic subcategory filtering for both forms (Add & Edit)
      const setupSubcategoryFilter = (formEl) => {
        if (!formEl) return;
        const catSelect = formEl.querySelector('select[name="category_id"]');
        const subSelect = formEl.querySelector('select[name="subcategory_id"]');
        if (!catSelect || !subSelect) return;

        const updateSubcategories = () => {
          const catId = catSelect.value;
          let firstMatch = null;
          Array.from(subSelect.options).forEach(opt => {
            if (opt.value === '') {
              opt.style.display = '';
              return;
            }
            const parentId = opt.getAttribute('data-parent-id');
            if (parentId === catId) {
              opt.style.display = '';
              if (!firstMatch) firstMatch = opt.value;
            } else {
              opt.style.display = 'none';
            }
          });
          
          // Reset selection if the current selected option is hidden
          const selectedOpt = subSelect.options[subSelect.selectedIndex];
          if (selectedOpt && selectedOpt.style.display === 'none') {
            subSelect.value = '';
          }
        };

        catSelect.addEventListener('change', updateSubcategories);
        // Initial run
        updateSubcategories();
      };

      // Apply to all forms
      document.querySelectorAll('form').forEach(setupSubcategoryFilter);
    });
  </script>
</body>
</html>