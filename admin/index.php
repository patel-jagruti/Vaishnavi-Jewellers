<?php
declare(strict_types=1);

/**
 * Admin Dashboard
 * Access: /admin (requires admin role)
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/catalog_db.php';
require_once __DIR__ . '/../includes/cart_db.php';

ensure_session();
require_login();
require_admin();

$pdo = db();

// Stats
$stats = [
  'products' => (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
  'orders' => (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
  'users' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
  'categories' => (int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
];

// Recent orders
$recentOrders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Low stock products
$lowStock = $pdo->query("SELECT id, name, stock FROM products WHERE stock < 10 AND is_active=1 ORDER BY stock ASC LIMIT 5")->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Admin Dashboard | Vaishnavi Jewellers</title>
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
        <a href="/visnavi%20jwallers/admin/" class="active"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="/visnavi%20jwallers/admin/products.php"><i class="fas fa-gem"></i> Products</a>
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
      <h1>Dashboard</h1>

      <div class="admin-stats">
        <div class="stat-card">
          <div class="value"><?= $stats['products'] ?></div>
          <div class="label">Products</div>
        </div>
        <div class="stat-card">
          <div class="value"><?= $stats['orders'] ?></div>
          <div class="label">Orders</div>
        </div>
        <div class="stat-card">
          <div class="value"><?= $stats['users'] ?></div>
          <div class="label">Customers</div>
        </div>
        <div class="stat-card">
          <div class="value"><?= $stats['categories'] ?></div>
          <div class="label">Categories</div>
        </div>
      </div>

      <div class="admin-section">
        <h2>Recent Orders</h2>
        <?php if ($recentOrders): ?>
          <table>
            <thead>
              <tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr>
            </thead>
            <tbody>
              <?php foreach ($recentOrders as $o): ?>
                <tr>
                  <td>#<?= (int)$o['id'] ?></td>
                  <td><?= h((string)($o['customer_name'] ?? '')) ?></td>
                  <td><?= money((float)$o['total']) ?></td>
                  <td><?= h((string)($o['order_status'] ?? 'PLACED')) ?></td>
                  <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>No orders yet.</p>
        <?php endif; ?>
      </div>

      <div class="admin-section">
        <h2>Low Stock Alert</h2>
        <?php if ($lowStock): ?>
          <table>
            <thead>
              <tr><th>Product</th><th>Stock</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php foreach ($lowStock as $p): ?>
                <tr>
                  <td><?= h((string)$p['name']) ?></td>
                  <td style="color:red;"><?= (int)$p['stock'] ?></td>
                  <td><a class="btn btn-sm" href="/visnavi jwallers/admin/products.php">Restock</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>All products well stocked.</p>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>