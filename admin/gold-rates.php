<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

ensure_session();
require_login();
require_admin();

$pdo = db();
$notice = '';

// Handle rate updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gold24k = (float)($_POST['gold_24k'] ?? 0);
    $gold22k = (float)($_POST['gold_22k'] ?? 0);
    $gold18k = (float)($_POST['gold_18k'] ?? 0);
    $silver = (float)($_POST['silver'] ?? 0);

    try {
        $pdo->beginTransaction();
        
        $upd = $pdo->prepare("INSERT INTO gold_rates (metal_type, rate_per_gram) VALUES (?, ?) ON DUPLICATE KEY UPDATE rate_per_gram = ?");
        $upd->execute(['gold_24k', $gold24k, $gold24k]);
        $upd->execute(['gold_22k', $gold22k, $gold22k]);
        $upd->execute(['gold_18k', $gold18k, $gold18k]);
        $upd->execute(['silver', $silver, $silver]);
        
        $pdo->commit();
        $notice = "Gold and Silver rates updated successfully.";
    } catch (Throwable $e) {
        $pdo->rollBack();
        $notice = "Error updating rates: " . $e->getMessage();
    }
}

// Fetch current rates
$rates = [];
try {
    $rows = $pdo->query("SELECT metal_type, rate_per_gram FROM gold_rates")->fetchAll(PDO::FETCH_KEY_PAIR);
    $rates = $rows;
} catch (Throwable $e) {
    // Fail-safe
}
$gold24kVal = (float)($rates['gold_24k'] ?? 7400.00);
$gold22kVal = (float)($rates['gold_22k'] ?? 6800.00);
$gold18kVal = (float)($rates['gold_18k'] ?? 5600.00);
$silverVal = (float)($rates['silver'] ?? 95.00);

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Gold Rates Manager | Admin Panel</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Poppins:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/visnavi%20jwallers/assets/css/admin-premium.css" />
  <style>
    .rate-card {
      background: #fff;
      border: 1px solid #eee;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.02);
      margin-bottom: 24px;
    }
    .rate-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 24px;
    }
    .metal-badge {
      font-size: 11px;
      text-transform: uppercase;
      font-weight: 700;
      letter-spacing: 0.5px;
      padding: 6px 12px;
      border-radius: 4px;
      display: inline-block;
      margin-bottom: 8px;
    }
    .badge-gold { background: #fff8e1; color: #b78103; border: 1px solid #ffe082; }
    .badge-silver { background: #f5f5f5; color: #666; border: 1px solid #e0e0e0; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 6px; }
    .page-title { font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: var(--maroon); margin-bottom: 24px; }
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
        <a href="/visnavi%20jwallers/admin/gold-rates.php" class="active"><i class="fas fa-coins"></i> Gold Rates</a>
        <a href="/visnavi%20jwallers/admin/settings.php"><i class="fas fa-cog"></i> Settings</a>
      </nav>
    </aside>

    <main class="admin-main">
      <h1 class="page-title">Daily Gold & Silver Rates Manager</h1>
      
      <?php if (!empty($notice)): ?>
        <div style="background:#e8f5e9; color:#2e7d32; padding:15px 20px; border-radius:8px; margin-bottom:24px; font-weight:600; font-size:14px; border:1px solid #c8e6c9;">
          <i class="fas fa-check-circle"></i> <?= h($notice) ?>
        </div>
      <?php endif; ?>

      <div style="background:#fdfbfa; border-left:4px solid var(--gold); padding:15px; border-radius:8px; margin-bottom:24px; font-size:13.5px; border:1px solid #f0ece7;">
        <i class="fas fa-info-circle" style="color:var(--gold);"></i> <strong>Rate Sync:</strong> Changing these rates updates the base price of all catalog products marked as <em>Dynamic</em> in real-time.
      </div>

      <div class="rate-grid">
        <div class="rate-card">
          <span class="metal-badge badge-gold">24K Gold</span>
          <div style="font-size:28px; font-weight:700; color:#333; font-family:'Outfit', sans-serif;">₹<?= number_format($gold24kVal, 2) ?></div>
          <div class="meta" style="margin-top:4px;">Rate per gram</div>
        </div>
        
        <div class="rate-card">
          <span class="metal-badge badge-gold">22K Gold</span>
          <div style="font-size:28px; font-weight:700; color:#333; font-family:'Outfit', sans-serif;">₹<?= number_format($gold22kVal, 2) ?></div>
          <div class="meta" style="margin-top:4px;">Rate per gram</div>
        </div>

        <div class="rate-card">
          <span class="metal-badge badge-gold">18K Gold</span>
          <div style="font-size:28px; font-weight:700; color:#333; font-family:'Outfit', sans-serif;">₹<?= number_format($gold18kVal, 2) ?></div>
          <div class="meta" style="margin-top:4px;">Rate per gram</div>
        </div>

        <div class="rate-card">
          <span class="metal-badge badge-silver">Silver</span>
          <div style="font-size:28px; font-weight:700; color:#333; font-family:'Outfit', sans-serif;">₹<?= number_format($silverVal, 2) ?></div>
          <div class="meta" style="margin-top:4px;">Rate per gram</div>
        </div>
      </div>

      <div class="rate-card" style="max-width: 500px;">
        <h3 style="margin-bottom:20px; font-family:'Outfit', sans-serif; font-weight:600;">Update Rates</h3>
        <form method="post" action="">
          <div class="form-group">
            <label>Gold 24K Rate (per gram in ₹)</label>
            <input type="number" step="0.01" name="gold_24k" class="select-role" style="font-size:14px; padding:10px 16px; width:100%; box-sizing:border-box; background:#fff; border:1px solid #ddd; border-radius:8px;" value="<?= $gold24kVal ?>" required>
          </div>
          
          <div class="form-group">
            <label>Gold 22K Rate (per gram in ₹)</label>
            <input type="number" step="0.01" name="gold_22k" class="select-role" style="font-size:14px; padding:10px 16px; width:100%; box-sizing:border-box; background:#fff; border:1px solid #ddd; border-radius:8px;" value="<?= $gold22kVal ?>" required>
          </div>

          <div class="form-group">
            <label>Gold 18K Rate (per gram in ₹)</label>
            <input type="number" step="0.01" name="gold_18k" class="select-role" style="font-size:14px; padding:10px 16px; width:100%; box-sizing:border-box; background:#fff; border:1px solid #ddd; border-radius:8px;" value="<?= $gold18kVal ?>" required>
          </div>

          <div class="form-group">
            <label>Silver Rate (per gram in ₹)</label>
            <input type="number" step="0.01" name="silver" class="select-role" style="font-size:14px; padding:10px 16px; width:100%; box-sizing:border-box; background:#fff; border:1px solid #ddd; border-radius:8px;" value="<?= $silverVal ?>" required>
          </div>

          <div style="margin-top:24px;">
            <button type="submit" class="btn" style="padding:12px 24px; font-size:14px; font-weight:600; border-radius:50px; width:100%;">
              <i class="fas fa-save"></i> Save Rates & Sync Catalog
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>
</html>
