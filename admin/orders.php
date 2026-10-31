<?php
declare(strict_types=1);

/**
 * Admin Orders Management
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/catalog_db.php';
require_once __DIR__ . '/../includes/cart_db.php';

ensure_session();
require_login();
require_admin();

$pdo = db();
$action = $_GET['action'] ?? 'list';
$notice = '';

// Handle status update
if ($action === 'update_status' && !empty($_GET['id']) && !empty($_GET['status'])) {
  $id = (int)$_GET['id'];
  $status = trim($_GET['status']);
  $allowed = ['PLACED', 'CONFIRMED', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'CANCELLED'];
  if (in_array($status, $allowed, true)) {
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    trigger_order_notifications($pdo, $id, $status);
    $notice = 'Order status updated successfully and notification triggered.';
  }
}

// Handle delete
if ($action === 'delete' && !empty($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
  $stmt->execute([$id]);
  $notice = 'Order deleted successfully.';
}

// Fetch orders with customer info
$orders = $pdo->query("
  SELECT o.*, u.full_name as customer_name, u.email as customer_email
  FROM orders o
  LEFT JOIN users u ON o.user_id = u.id
  ORDER BY o.id DESC
  LIMIT 50
")->fetchAll();

$stats = [
  'placed' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'PLACED'")->fetchColumn(),
  'confirmed' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'CONFIRMED'")->fetchColumn(),
  'shipped' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'SHIPPED'")->fetchColumn(),
  'delivered' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'DELIVERED'")->fetchColumn(),
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Orders | Admin Panel</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Poppins:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/visnavi%20jwallers/assets/css/admin-premium.css" />
  <style>
    .badge-placed { background:#e3f2fd; color:#0066cc; }
    .badge-confirmed { background:#fff3e0; color:#ff6600; }
    .badge-processing { background:#f3e5f5; color:#7b1fa2; }
    .badge-shipped { background:#e8eaf6; color:#3f51b5; }
    .badge-delivered { background:#e8f5e9; color:#2e7d32; }
    .badge-cancelled { background:#ffebee; color:#c62828; }
  </style>
</head>
<body>
  <div class="admin-layout">
    <aside class="admin-sidebar">
      <div class="brand">Vaishnavi Panel</div>
      <nav>
        <a href="/visnavi%20jwallers/admin/"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="/visnavi%20jwallers/admin/products.php"><i class="fas fa-gem"></i> Products</a>
        <a href="/visnavi%20jwallers/admin/orders.php" class="active"><i class="fas fa-shopping-bag"></i> Orders</a>
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
      <h1>Orders</h1>

      <?php if ($notice): ?>
        <div style="background:#d4edda; padding:12px; border-radius:4px; margin-bottom:16px;"><?= h($notice) ?></div>
      <?php endif; ?>

      <div class="admin-stats">
        <div class="stat-card placed">
          <div class="value"><?= $stats['placed'] ?></div>
          <div class="label">Placed</div>
        </div>
        <div class="stat-card confirmed">
          <div class="value"><?= $stats['confirmed'] ?></div>
          <div class="label">Confirmed</div>
        </div>
        <div class="stat-card shipped">
          <div class="value"><?= $stats['shipped'] ?></div>
          <div class="label">Shipped</div>
        </div>
        <div class="stat-card delivered">
          <div class="value"><?= $stats['delivered'] ?></div>
          <div class="label">Delivered</div>
        </div>
      </div>

      <div class="admin-section">
        <table>
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Items</th>
              <th>Total</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
              <?php
                $itemCount = 1;
                if (!empty($o['items'])) {
                  $items = @json_decode($o['items'], true);
                  if (is_array($items)) {
                    $itemCount = array_sum(array_column($items, 'qty'));
                  }
                }
              ?>
              <tr>
                <td>#<?= (int)$o['id'] ?></td>
                <td>
                  <div><?= h((string)($o['customer_name'] ?? 'Guest')) ?></div>
                  <div style="font-size:11px; color:#666;"><?= h((string)($o['customer_email'] ?? '')) ?></div>
                </td>
                <td><?= $itemCount ?> item(s)</td>
                <td><?= money((float)$o['total']) ?></td>
                <td>
                  <span class="badge badge-<?= strtolower((string)$o['order_status']) ?>">
                    <?= h((string)$o['order_status']) ?>
                  </span>
                </td>
                <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                <td>
                  <div class="actions-cell">
                    <select onchange="location.href='?action=update_status&id=<?= (int)$o['id'] ?>&status='+this.value" class="select-role" style="font-size:12px; padding:4px 8px;">
                      <option value="PLACED" <?= $o['order_status'] === 'PLACED' ? 'selected' : '' ?>>Placed</option>
                      <option value="CONFIRMED" <?= $o['order_status'] === 'CONFIRMED' ? 'selected' : '' ?>>Confirmed</option>
                      <option value="PROCESSING" <?= $o['order_status'] === 'PROCESSING' ? 'selected' : '' ?>>Processing</option>
                      <option value="SHIPPED" <?= $o['order_status'] === 'SHIPPED' ? 'selected' : '' ?>>Shipped</option>
                      <option value="DELIVERED" <?= $o['order_status'] === 'DELIVERED' ? 'selected' : '' ?>>Delivered</option>
                      <option value="CANCELLED" <?= $o['order_status'] === 'CANCELLED' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                    <a class="btn btn-sm btn-secondary" href="/visnavi%20jwallers/admin/order-view.php?id=<?= (int)$o['id'] ?>"><i class="fas fa-eye"></i> View</a>
                    <a class="btn btn-sm btn-danger" href="?action=delete&id=<?= (int)$o['id'] ?>" onclick="return confirm('Delete order?')"><i class="fas fa-trash"></i> Delete</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <?php if (empty($orders)): ?>
          <p style="padding:24px; text-align:center; color:#666;">No orders yet.</p>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>