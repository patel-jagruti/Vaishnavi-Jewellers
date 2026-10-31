<?php
declare(strict_types=1);

/**
 * Admin Order Detail View
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/catalog_db.php';

ensure_session();
require_login();
require_admin();

$pdo = db();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
  header('Location: orders.php');
  exit;
}

$stmt = $pdo->prepare("
  SELECT o.*, u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone
  FROM orders o
  LEFT JOIN users u ON o.user_id = u.id
  WHERE o.id = ?
");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
  header('Location: orders.php');
  exit;
}

$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderStatus = trim($_POST['order_status'] ?? '');
    $paymentStatus = trim($_POST['payment_status'] ?? '');
    $trackingNumber = trim($_POST['tracking_number'] ?? '');
    $shippingCarrier = trim($_POST['shipping_carrier'] ?? '');
    $returnStatus = trim($_POST['return_status'] ?? 'NONE');

    // Begin transaction
    $pdo->beginTransaction();
    try {
        $upd = $pdo->prepare("
            UPDATE orders SET 
                order_status = ?, 
                payment_status = ?, 
                tracking_number = ?, 
                shipping_carrier = ?, 
                return_status = ? 
            WHERE id = ?
        ");
        $upd->execute([
            $orderStatus,
            $paymentStatus,
            $trackingNumber !== '' ? $trackingNumber : null,
            $shippingCarrier !== '' ? $shippingCarrier : null,
            $returnStatus,
            $id
        ]);

        // If returned, also update status to returned
        if ($returnStatus === 'APPROVED' && $orderStatus !== 'RETURNED') {
            $orderStatus = 'RETURNED';
            $pdo->prepare("UPDATE orders SET order_status = 'RETURNED' WHERE id = ?")->execute([$id]);
        }

        // Trigger notifications if status or tracking changes
        if ($orderStatus !== $order['order_status'] || $trackingNumber !== $order['tracking_number'] || $shippingCarrier !== $order['shipping_carrier']) {
            trigger_order_notifications($pdo, $id, $orderStatus, $shippingCarrier, $trackingNumber);
        }

        $pdo->commit();
        
        // Reload order data
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        $notice = "Order updated successfully.";
    } catch (Throwable $e) {
        $pdo->rollBack();
        $notice = "Error updating order: " . $e->getMessage();
    }
}

// Fetch actual items
$stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Order #<?= $id ?> | Admin Panel</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Poppins:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/visnavi%20jwallers/assets/css/admin-premium.css" />
  <style>
    .order-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; }
    .order-id { font-family: 'Cormorant Garamond', serif; font-size:32px; font-weight:700; color:var(--maroon); }
    .order-date { color:#666; font-size:14px; margin-top:4px; }
    .info-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:24px; }
    .info-block h3 { font-family: 'Cormorant Garamond', serif; font-size:18px; text-transform:uppercase; color:var(--maroon); margin-bottom:12px; font-weight:700; }
    .info-block p { margin: 8px 0; font-size: 14px; }
    .total-row td { font-weight:700; font-size:18px; color:var(--maroon); }
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
        <a href="/visnavi%20jwallers/admin/orders.php"><i class="fas fa-arrow-left"></i> Back to Orders</a>
      </nav>
    </aside>

    <main class="admin-main">
      <?php if (!empty($notice)): ?>
        <div style="background:#e8f5e9; color:#2e7d32; padding:15px 20px; border-radius:8px; margin-bottom:20px; font-weight:600; font-size:14px; border:1px solid #c8e6c9;">
          <i class="fas fa-check-circle"></i> <?= h($notice) ?>
        </div>
      <?php endif; ?>

      <div class="order-header">
        <div>
          <div class="order-id">Order #<?= $id ?></div>
          <div class="order-date">Placed on <?= date('d M Y, g:i A', strtotime($order['created_at'])) ?></div>
        </div>
        <div>
          <span class="badge badge-<?= strtolower((string)$order['order_status']) ?>" style="padding:8px 16px; font-size:14px;">
            <?= h((string)$order['order_status']) ?>
          </span>
        </div>
      </div>

      <div class="info-grid">
        <div class="admin-section">
          <div class="info-block">
            <h3>Customer Information</h3>
            <p><strong>Name:</strong> <?= h((string)($order['customer_name'] ?? 'Guest')) ?></p>
            <p><strong>Email:</strong> <?= h((string)($order['customer_email'] ?? 'N/A')) ?></p>
            <p><strong>Phone:</strong> <?= h((string)($order['customer_phone'] ?? 'N/A')) ?></p>
          </div>
        </div>

        <div class="admin-section">
          <div class="info-block">
            <h3>Shipping Address</h3>
            <p><?= nl2br(h((string)($order['shipping_address'] ?? 'N/A'))) ?></p>
          </div>
        </div>
      </div>

      <div class="admin-section">
        <h3 style="margin-bottom:16px;">Order Items</h3>
        <table>
          <thead>
            <tr>
              <th>Product</th>
              <th>Price</th>
              <th>Qty</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): ?>
              <tr>
                <td><?= h((string)($item['product_name'] ?? 'Product #' . ($item['product_id'] ?? '?'))) ?></td>
                <td><?= money((float)($item['unit_price'] ?? 0)) ?></td>
                <td><?= (int)($item['quantity'] ?? 1) ?></td>
                <td><?= money((float)($item['total_price'] ?? 0)) ?></td>
              </tr>
            <?php endforeach; ?>
            <tr>
              <td colspan="3" style="text-align:right; font-size:14px; color:#555; font-weight:600; padding:10px 15px;">Subtotal</td>
              <td style="text-align:right; font-size:14px; color:#333; font-weight:600; padding:10px 15px;"><?= money((float)$order['subtotal']) ?></td>
            </tr>
            <?php
            $taxableAmount = (float)$order['subtotal'];
            $cgst = round($taxableAmount * 0.015, 2);
            $sgst = round($taxableAmount * 0.015, 2);
            ?>
            <tr>
              <td colspan="3" style="text-align:right; font-size:13px; color:#666; padding:8px 15px;">CGST (1.5%)</td>
              <td style="text-align:right; font-size:13px; color:#666; padding:8px 15px;"><?= money($cgst) ?></td>
            </tr>
            <tr>
              <td colspan="3" style="text-align:right; font-size:13px; color:#666; padding:8px 15px;">SGST (1.5%)</td>
              <td style="text-align:right; font-size:13px; color:#666; padding:8px 15px;"><?= money($sgst) ?></td>
            </tr>
            <?php if ((float)$order['discount_amount'] > 0): ?>
              <tr>
                <td colspan="3" style="text-align:right; font-size:13px; color:green; padding:8px 15px;">Discount</td>
                <td style="text-align:right; font-size:13px; color:green; padding:8px 15px;">-<?= money((float)$order['discount_amount']) ?></td>
              </tr>
            <?php endif; ?>
            <?php if ((float)$order['shipping'] > 0): ?>
              <tr>
                <td colspan="3" style="text-align:right; font-size:13px; color:#666; padding:8px 15px;">Shipping</td>
                <td style="text-align:right; font-size:13px; color:#666; padding:8px 15px;"><?= money((float)$order['shipping']) ?></td>
              </tr>
            <?php endif; ?>
            <tr class="total-row">
              <td colspan="3" style="text-align:right; padding:15px; font-weight:700; font-size:18px; color:var(--maroon);">Grand Total</td>
              <td style="text-align:right; padding:15px; font-weight:700; font-size:18px; color:var(--maroon);"><?= money((float)$order['total']) ?></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- If there is a return request, display details -->
      <?php if ($order['return_status'] !== 'NONE'): ?>
        <div class="admin-section" style="border-left: 4px solid #ff6600; background: #fff3e0; padding:20px; border-radius:8px; margin-bottom:25px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
          <h3 style="color:#e65100; margin:0 0 12px 0; font-family:'Outfit', serif; font-size:18px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">
            <i class="fas fa-undo"></i> Return / Exchange Request Details
          </h3>
          <p style="font-size:14px; margin:5px 0; color:#333;"><strong>Customer Explanation:</strong></p>
          <pre style="background:#fff; padding:15px; border-radius:8px; border:1px solid #ffe0b2; font-family:inherit; font-size:13.5px; white-space:pre-wrap; margin:8px 0 0 0; line-height:1.5; color:#555;"><?= h($order['return_reason'] ?? 'No reason provided') ?></pre>
        </div>
      <?php endif; ?>

      <div class="admin-section" style="max-width: 600px;">
        <h3 style="margin-bottom:16px;">Update Order Status & Tracking Details</h3>
        <form method="post" action="" style="display:flex; flex-direction:column; gap:16px;">
          
          <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
            <div>
              <label style="display:block; font-size:11px; font-weight:600; text-transform:uppercase; margin-bottom:6px; color:#666; letter-spacing:0.5px;">Order Status</label>
              <select name="order_status" class="select-role" style="font-size:14px; padding:10px 16px; width:100%; box-sizing:border-box;">
                <option value="PLACED" <?= $order['order_status'] === 'PLACED' ? 'selected' : '' ?>>Placed</option>
                <option value="CONFIRMED" <?= $order['order_status'] === 'CONFIRMED' ? 'selected' : '' ?>>Confirmed</option>
                <option value="PROCESSING" <?= $order['order_status'] === 'PROCESSING' ? 'selected' : '' ?>>Processing</option>
                <option value="SHIPPED" <?= $order['order_status'] === 'SHIPPED' ? 'selected' : '' ?>>Shipped</option>
                <option value="DELIVERED" <?= $order['order_status'] === 'DELIVERED' ? 'selected' : '' ?>>Delivered</option>
                <option value="CANCELLED" <?= $order['order_status'] === 'CANCELLED' ? 'selected' : '' ?>>Cancelled</option>
                <option value="RETURNED" <?= $order['order_status'] === 'RETURNED' ? 'selected' : '' ?>>Returned</option>
              </select>
            </div>
            
            <div>
              <label style="display:block; font-size:11px; font-weight:600; text-transform:uppercase; margin-bottom:6px; color:#666; letter-spacing:0.5px;">Payment Status</label>
              <select name="payment_status" class="select-role" style="font-size:14px; padding:10px 16px; width:100%; box-sizing:border-box;">
                <option value="PENDING" <?= $order['payment_status'] === 'PENDING' ? 'selected' : '' ?>>Pending</option>
                <option value="PAID" <?= $order['payment_status'] === 'PAID' ? 'selected' : '' ?>>Paid</option>
                <option value="FAILED" <?= $order['payment_status'] === 'FAILED' ? 'selected' : '' ?>>Failed</option>
                <option value="REFUNDED" <?= $order['payment_status'] === 'REFUNDED' ? 'selected' : '' ?>>Refunded</option>
              </select>
            </div>
          </div>

          <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
            <div>
              <label style="display:block; font-size:11px; font-weight:600; text-transform:uppercase; margin-bottom:6px; color:#666; letter-spacing:0.5px;">Shipping Carrier</label>
              <input type="text" name="shipping_carrier" class="select-role" style="font-size:14px; padding:10px 16px; width:100%; box-sizing:border-box; background:#fff; border:1px solid #ddd; border-radius:8px;" value="<?= h((string)($order['shipping_carrier'] ?? '')) ?>" placeholder="e.g. BlueDart, Delhivery">
            </div>
            
            <div>
              <label style="display:block; font-size:11px; font-weight:600; text-transform:uppercase; margin-bottom:6px; color:#666; letter-spacing:0.5px;">Tracking Number / ID</label>
              <input type="text" name="tracking_number" class="select-role" style="font-size:14px; padding:10px 16px; width:100%; box-sizing:border-box; background:#fff; border:1px solid #ddd; border-radius:8px;" value="<?= h((string)($order['tracking_number'] ?? '')) ?>" placeholder="e.g. 123456789">
            </div>
          </div>

          <?php if ($order['return_status'] !== 'NONE'): ?>
            <div>
              <label style="display:block; font-size:11px; font-weight:600; text-transform:uppercase; margin-bottom:6px; color:#666; letter-spacing:0.5px;">Review Return Request</label>
              <select name="return_status" class="select-role" style="font-size:14px; padding:10px 16px; width:100%; box-sizing:border-box;">
                <option value="REQUESTED" <?= $order['return_status'] === 'REQUESTED' ? 'selected' : '' ?>>Requested (Pending Review)</option>
                <option value="APPROVED" <?= $order['return_status'] === 'APPROVED' ? 'selected' : '' ?>>Approved (Approve return & refund)</option>
                <option value="REJECTED" <?= $order['return_status'] === 'REJECTED' ? 'selected' : '' ?>>Rejected (Decline return request)</option>
              </select>
            </div>
          <?php endif; ?>

          <div>
            <button type="submit" class="btn" style="padding:12px 24px; font-size:14px; font-weight:600; border-radius:50px; width:100%;">
              <i class="fas fa-save"></i> Save Order Details & Dispatch Alerts
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>
</html>