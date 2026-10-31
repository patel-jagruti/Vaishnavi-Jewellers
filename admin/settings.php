<?php
declare(strict_types=1);

/**
 * Admin Settings Management
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

ensure_session();
require_login();
require_admin();

$pdo = db();
$notice = '';

// Ensure settings table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) UNIQUE NOT NULL,
  setting_value TEXT,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Default settings
$defaults = [
  'site_name' => 'Visnavi Jewellers',
  'site_email' => 'contact@visnavijewellers.com',
  'site_phone' => '+91 98765 43210',
  'site_address' => 'Mumbai, Maharashtra, India',
  'site_description' => 'Premium handmade jewellery for every occasion',
  'currency' => 'INR',
  'tax_rate' => '3',
  'free_shipping_threshold' => '50000',
  'shipping_charge' => '50',
  'order_email' => 'orders@visnavijewellers.com',
  'facebook' => '',
  'instagram' => '',
  'youtube' => '',
];

// Insert defaults if not exist
foreach ($defaults as $key => $value) {
  try {
    $pdo->exec("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('$key', '$value')");
  } catch (Throwable $e) {
    // Ignore
  }
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['setting_key'])) {
  $key = trim($_POST['setting_key']);
  $value = trim($_POST['setting_value']);
  $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
  $stmt->execute([$value, $key]);
  $notice = 'Settings saved successfully.';
}

// Fetch all settings
$settings = $pdo->query('SELECT * FROM settings ORDER BY setting_key ASC')->fetchAll();
$settingsMap = [];
foreach ($settings as $s) {
  $settingsMap[$s['setting_key']] = $s['setting_value'];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Settings | Admin Panel</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Poppins:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/visnavi%20jwallers/assets/css/admin-premium.css" />
  <style>
    .settings-group { margin-bottom:30px; }
    .settings-group h3 { font-family: 'Cormorant Garamond', serif; font-size:18px; text-transform:uppercase; color:var(--maroon); margin-bottom:20px; padding-bottom:10px; border-bottom:1px solid var(--border); font-weight:700; letter-spacing:1px; }
    .setting-row { display:flex; align-items:center; margin-bottom:16px; gap:16px; }
    .setting-row label { width:220px; font-weight:600; font-size:13px; color:#555; }
    .setting-row input { flex:1; padding:12px 16px; border:1px solid #DDD7CE; border-radius:8px; font-size:14px; font-family:'Poppins', sans-serif; transition: all 0.3s; }
    .setting-row input:focus { border-color: var(--gold); outline: none; }
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
        <a href="/visnavi%20jwallers/admin/banners.php"><i class="fas fa-images"></i> Banners</a>
        <a href="/visnavi%20jwallers/admin/blogs.php"><i class="fas fa-newspaper"></i> Blogs</a>
        <a href="/visnavi%20jwallers/admin/notifications.php"><i class="fas fa-bell"></i> Alerts Log</a>
        <a href="/visnavi%20jwallers/admin/gold-rates.php"><i class="fas fa-coins"></i> Gold Rates</a>
        <a href="/visnavi%20jwallers/admin/settings.php" class="active"><i class="fas fa-cog"></i> Settings</a>
        <a href="/visnavi%20jwallers/index.php"><i class="fas fa-store"></i> Back to Store</a>
      </nav>
    </aside>

    <main class="admin-main">
      <h1>Settings Management</h1>

      <?php if ($notice): ?>
        <div style="background:#e8f5e9; color:#2e7d32; padding:15px; border-radius:8px; margin-bottom:24px; border: 1px solid #c8e6c9; font-weight: 500;"><i class="fas fa-check-circle"></i> <?= h($notice) ?></div>
      <?php endif; ?>

      <div class="admin-section">
        <div class="settings-group">
          <h3>Store Information</h3>
          <form method="post">
            <input type="hidden" name="setting_key" value="site_name">
            <div class="setting-row">
              <label>Store Name</label>
              <input type="text" name="setting_value" value="<?= h((string)($settingsMap['site_name'] ?? '')) ?>">
              <button type="submit" class="btn" style="margin-left:12px;">Save</button>
            </div>
          </form>
          <form method="post">
            <input type="hidden" name="setting_key" value="site_email">
            <div class="setting-row">
              <label>Email</label>
              <input type="email" name="setting_value" value="<?= h((string)($settingsMap['site_email'] ?? '')) ?>">
              <button type="submit" class="btn" style="margin-left:12px;">Save</button>
            </div>
          </form>
          <form method="post">
            <input type="hidden" name="setting_key" value="site_phone">
            <div class="setting-row">
              <label>Phone</label>
              <input type="text" name="setting_value" value="<?= h((string)($settingsMap['site_phone'] ?? '')) ?>">
              <button type="submit" class="btn" style="margin-left:12px;">Save</button>
            </div>
          </form>
          <form method="post">
            <input type="hidden" name="setting_key" value="site_address">
            <div class="setting-row">
              <label>Address</label>
              <input type="text" name="setting_value" value="<?= h((string)($settingsMap['site_address'] ?? '')) ?>">
              <button type="submit" class="btn" style="margin-left:12px;">Save</button>
            </div>
          </form>
          <form method="post">
            <input type="hidden" name="setting_key" value="site_description">
            <div class="setting-row">
              <label>Description</label>
              <input type="text" name="setting_value" value="<?= h((string)($settingsMap['site_description'] ?? '')) ?>">
              <button type="submit" class="btn" style="margin-left:12px;">Save</button>
            </div>
          </form>
        </div>

        <div class="settings-group">
          <h3>Pricing & Shipping</h3>
          <form method="post">
            <input type="hidden" name="setting_key" value="currency">
            <div class="setting-row">
              <label>Currency</label>
              <input type="text" name="setting_value" value="<?= h((string)($settingsMap['currency'] ?? 'INR')) ?>">
              <button type="submit" class="btn" style="margin-left:12px;">Save</button>
            </div>
          </form>
          <form method="post">
            <input type="hidden" name="setting_key" value="tax_rate">
            <div class="setting-row">
              <label>Tax Rate (%)</label>
              <input type="number" name="setting_value" value="<?= h((string)($settingsMap['tax_rate'] ?? '3')) ?>">
              <button type="submit" class="btn" style="margin-left:12px;">Save</button>
            </div>
          </form>
          <form method="post">
            <input type="hidden" name="setting_key" value="free_shipping_threshold">
            <div class="setting-row">
              <label>Free Shipping Above (₹)</label>
              <input type="number" name="setting_value" value="<?= h((string)($settingsMap['free_shipping_threshold'] ?? '50000')) ?>">
              <button type="submit" class="btn" style="margin-left:12px;">Save</button>
            </div>
          </form>
          <form method="post">
            <input type="hidden" name="setting_key" value="shipping_charge">
            <div class="setting-row">
              <label>Shipping Charge (₹)</label>
              <input type="number" name="setting_value" value="<?= h((string)($settingsMap['shipping_charge'] ?? '50')) ?>">
              <button type="submit" class="btn" style="margin-left:12px;">Save</button>
            </div>
          </form>
        </div>

        <div class="settings-group">
          <h3>Notifications</h3>
          <form method="post">
            <input type="hidden" name="setting_key" value="order_email">
            <div class="setting-row">
              <label>Order Email</label>
              <input type="email" name="setting_value" value="<?= h((string)($settingsMap['order_email'] ?? '')) ?>">
              <button type="submit" class="btn" style="margin-left:12px;">Save</button>
            </div>
          </form>
        </div>

        <div class="settings-group">
          <h3>Social Media</h3>
          <form method="post">
            <input type="hidden" name="setting_key" value="facebook">
            <div class="setting-row">
              <label>Facebook</label>
              <input type="url" name="setting_value" value="<?= h((string)($settingsMap['facebook'] ?? '')) ?>" placeholder="https://facebook.com/...">
              <button type="submit" class="btn" style="margin-left:12px;">Save</button>
            </div>
          </form>
          <form method="post">
            <input type="hidden" name="setting_key" value="instagram">
            <div class="setting-row">
              <label>Instagram</label>
              <input type="url" name="setting_value" value="<?= h((string)($settingsMap['instagram'] ?? '')) ?>" placeholder="https://instagram.com/...">
              <button type="submit" class="btn" style="margin-left:12px;">Save</button>
            </div>
          </form>
          <form method="post">
            <input type="hidden" name="setting_key" value="youtube">
            <div class="setting-row">
              <label>YouTube</label>
              <input type="url" name="setting_value" value="<?= h((string)($settingsMap['youtube'] ?? '')) ?>" placeholder="https://youtube.com/...">
              <button type="submit" class="btn" style="margin-left:12px;">Save</button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</body>
</html>