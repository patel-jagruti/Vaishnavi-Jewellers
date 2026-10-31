<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/base_url.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

ensure_session();

$orderCode = $_GET['order'] ?? null;
$pdo = db();
$orders = [];
$user = $_SESSION['user'] ?? null;

if ($orderCode !== null && $orderCode !== '') {
  // Fetch specific order by code (supports guest checkout order tracking)
  $stmt = $pdo->prepare("
    SELECT o.* 
    FROM orders o 
    WHERE o.order_code = :code 
    LIMIT 1
  ");
  $stmt->execute([':code' => $orderCode]);
  $order = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($order) {
    $orders = [$order];
  }
} else {
  // Require login to view complete order history
  require_login();
  $user = $_SESSION['user'];
  
  $stmt = $pdo->prepare("
    SELECT o.* 
    FROM orders o 
    WHERE o.user_id = :uid 
    ORDER BY o.created_at DESC
  ");
  $stmt->execute([':uid' => $user['id']]);
  $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getStepClass(string $currentStatus, string $stepStatus): string {
    $statuses = ['PLACED', 'CONFIRMED', 'PROCESSING', 'SHIPPED', 'DELIVERED'];
    $currentIndex = array_search(strtoupper($currentStatus), $statuses, true);
    $stepIndex = array_search($stepStatus, $statuses, true);

    if ($currentIndex === false || $stepIndex === false) return '';
    if ($currentIndex === $stepIndex) return 'active';
    if ($stepIndex < $currentIndex) return 'completed';
    return '';
}

function getProgressWidth(string $currentStatus): string {
    switch (strtoupper($currentStatus)) {
        case 'PLACED': return '0%';
        case 'CONFIRMED': return '25%';
        case 'PROCESSING': return '50%';
        case 'SHIPPED': return '75%';
        case 'DELIVERED': return '100%';
        default: return '0%';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<base href="/visnavi jwallers/">
<title>My Orders - Vaishnavi Jewellers</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>
.main-content {
  display: none !important;
}
:root {
  --maroon: #5A1625;
  --maroon-dark: #3D0F19;
  --gold: #D4AF37;
  --gold-light: #F4E2AC;
  --ivory: #FAF8F5;
  --gray: #666;
  --gray-light: #999;
  --gray-bg: #FAF9F7;
  --border-color: #EFEBE6;
  --shadow-sm: 0 4px 15px rgba(90, 22, 37, 0.02);
  --shadow-md: 0 10px 30px rgba(90, 22, 37, 0.05);
  --shadow-lg: 0 20px 40px rgba(90, 22, 37, 0.08);
}

* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Poppins', sans-serif; background: var(--ivory); color: #2A2A2A; -webkit-font-smoothing: antialiased; }
a { text-decoration: none; color: inherit; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

/* Page Header */
.page-header {
  background: linear-gradient(135deg, var(--maroon-dark) 0%, var(--maroon) 50%, #7A1F2A 100%);
  padding: 70px 0;
  color: #fff;
  position: relative;
  overflow: hidden;
  box-shadow: inset 0 -10px 30px rgba(0,0,0,0.1);
}

.page-header::after {
  content: '';
  position: absolute;
  top: 0; right: 0; bottom: 0; left: 0;
  background: radial-gradient(circle at top right, rgba(212, 175, 55, 0.15) 0%, transparent 60%);
  pointer-events: none;
}

.page-title {
  font-family: 'Playfair Display', serif;
  font-size: 42px;
  font-weight: 700;
  margin-bottom: 10px;
  letter-spacing: -0.5px;
  text-shadow: 0 2px 4px rgba(0,0,0,0.15);
}

.breadcrumb {
  font-family: 'Outfit', sans-serif;
  font-size: 14px;
  opacity: 0.9;
  letter-spacing: 0.5px;
}

.breadcrumb a {
  color: var(--gold-light);
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s ease;
}

.breadcrumb a:hover {
  color: #fff;
}

/* Orders Section */
.orders-section {
  padding: 70px 0;
}

.orders-title {
  font-family: 'Playfair Display', serif;
  font-size: 32px;
  font-weight: 600;
  color: var(--maroon);
  margin-bottom: 35px;
  position: relative;
  display: inline-block;
}

.orders-title::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: -8px;
  width: 50px;
  height: 2px;
  background: var(--gold);
}

.orders-grid {
  display: grid;
  gap: 30px;
}

/* Order Card */
.order-card {
  background: #fff;
  border-radius: 24px;
  padding: 35px;
  box-shadow: var(--shadow-sm);
  border: 1.5px solid var(--border-color);
  transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), border-color 0.4s ease;
}

.order-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-lg);
  border-color: rgba(212, 175, 55, 0.35);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  padding-bottom: 20px;
  border-bottom: 1px solid var(--border-color);
}

.order-id {
  font-family: 'Outfit', sans-serif;
  font-weight: 700;
  color: var(--maroon-dark);
  font-size: 18px;
  letter-spacing: 0.5px;
}

.order-date {
  font-size: 13.5px;
  color: var(--gray);
  margin-top: 4px;
}

.order-status {
  font-family: 'Outfit', sans-serif;
  padding: 6px 18px;
  border-radius: 50px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.02);
}

.status-placed { background: #E8F4FD; color: #1D88E5; }
.status-confirmed { background: #FFF9E6; color: #B28900; }
.status-processing { background: #F3E8FD; color: #8A2BE2; }
.status-shipped { background: #E8F0FE; color: #1A73E8; }
.status-delivered { background: #E6F4EA; color: #137333; }
.status-cancelled { background: #FCE8E6; color: #C5221F; }

/* Progress Stepper Styles */
.order-stepper {
  display: flex;
  justify-content: space-between;
  position: relative;
  margin: 35px 0 45px 0;
  padding: 0 15px;
}

.order-stepper::before {
  content: '';
  position: absolute;
  top: 16px;
  left: 40px;
  right: 40px;
  height: 3px;
  background: var(--border-color);
  z-index: 1;
}

.stepper-line {
  position: absolute;
  top: 16px;
  left: 40px;
  height: 3px;
  background: var(--gold);
  z-index: 1;
  transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.step-node {
  position: relative;
  z-index: 2;
  text-align: center;
  flex: 1;
}

.step-circle {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: #fff;
  border: 3px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 12px auto;
  font-size: 12px;
  font-weight: 700;
  color: var(--gray-light);
  box-shadow: 0 4px 10px rgba(0,0,0,0.03);
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.step-label {
  font-family: 'Outfit', sans-serif;
  font-size: 10.5px;
  font-weight: 700;
  color: var(--gray-light);
  text-transform: uppercase;
  letter-spacing: 0.8px;
  transition: all 0.3s ease;
}

/* Active & Completed step states */
.step-node.active .step-circle {
  border-color: var(--gold);
  background: var(--gold);
  color: #fff;
  box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);
  animation: pulseActive 2s infinite alternate;
}

@keyframes pulseActive {
  0% { transform: scale(1); box-shadow: 0 4px 10px rgba(212, 175, 55, 0.3); }
  100% { transform: scale(1.05); box-shadow: 0 4px 18px rgba(212, 175, 55, 0.6); }
}

.step-node.active .step-label {
  color: var(--maroon);
  font-weight: 700;
}

.step-node.completed .step-circle {
  border-color: var(--maroon);
  background: var(--maroon);
  color: #fff;
  box-shadow: 0 4px 12px rgba(90, 22, 37, 0.15);
}

.step-node.completed .step-label {
  color: #333;
}

/* Tracking Block Styles */
.tracking-info-box {
  background: var(--gray-bg);
  border-left: 4px solid var(--gold);
  padding: 18px 24px;
  border-radius: 12px;
  margin: 25px 0;
  font-size: 14px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border: 1.5px solid var(--border-color);
  box-shadow: var(--shadow-sm);
}

.tracking-info-box p {
  margin: 0;
  color: #333;
}

.tracking-ship-link {
  font-family: 'Outfit', sans-serif;
  color: var(--maroon);
  font-weight: 700;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  text-decoration: underline !important;
  transition: color 0.2s ease;
}

.tracking-ship-link:hover {
  color: var(--gold);
}

/* Order Items */
.order-items {
  display: grid;
  gap: 15px;
  margin-bottom: 25px;
}

.order-item {
  display: flex;
  gap: 20px;
  align-items: center;
  padding: 15px;
  border-radius: 16px;
  background: transparent;
  transition: background-color 0.3s ease, padding-left 0.3s ease;
}

.order-item:hover {
  background-color: var(--gray-bg);
  padding-left: 20px;
}

.order-item:not(:last-child) {
  border-bottom: 1px dashed var(--border-color);
}

.item-img {
  width: 65px;
  height: 65px;
  background: #fff;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  color: var(--maroon);
  border: 1.5px solid var(--border-color);
  box-shadow: 0 4px 10px rgba(0,0,0,0.02);
  transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.order-item:hover .item-img {
  transform: scale(1.1) rotate(6deg);
  border-color: var(--gold);
}

.item-details h4 {
  font-family: 'Playfair Display', serif;
  font-weight: 700;
  font-size: 16px;
  color: #222;
  margin-bottom: 4px;
}

.item-details p {
  font-size: 13px;
  color: var(--gray);
}

.item-price {
  margin-left: auto;
  font-family: 'Playfair Display', serif;
  font-weight: 700;
  color: var(--maroon);
  font-size: 18px;
}

/* Order Footer */
.order-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 25px;
  padding-top: 20px;
  border-top: 1px solid var(--border-color);
}

.order-total {
  font-family: 'Outfit', sans-serif;
  font-size: 15px;
  font-weight: 500;
  color: var(--gray);
}

.order-total span {
  font-family: 'Playfair Display', serif;
  font-weight: 700;
  color: var(--maroon);
  font-size: 22px;
  margin-left: 5px;
}

.order-actions {
  display: flex;
  gap: 12px;
}

.btn-details {
  background: transparent;
  border: 1.5px solid #E5E0DA;
  color: #444;
  padding: 10px 22px;
  border-radius: 50px;
  font-family: 'Outfit', sans-serif;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.btn-details:hover {
  border-color: var(--maroon);
  color: var(--maroon);
  box-shadow: 0 4px 12px rgba(90, 22, 37, 0.06);
}

/* Empty States */
.no-orders {
  text-align: center;
  padding: 100px 24px;
  background: #fff;
  border-radius: 28px;
  box-shadow: var(--shadow-sm);
  border: 1.5px solid var(--border-color);
}

.no-orders i {
  font-size: 70px;
  color: var(--maroon);
  margin-bottom: 25px;
  opacity: 0.85;
}

.no-orders h3 {
  font-family: 'Playfair Display', serif;
  font-size: 28px;
  margin-bottom: 12px;
  color: #222;
}

.no-orders p {
  color: var(--gray);
  margin-bottom: 30px;
  font-size: 16px;
  max-width: 480px;
  margin-left: auto;
  margin-right: auto;
}

.btn-shop {
  background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
  color: #fff;
  padding: 14px 35px;
  border-radius: 50px;
  font-family: 'Outfit', sans-serif;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  text-decoration: none;
  display: inline-block;
  box-shadow: 0 5px 15px rgba(90, 22, 37, 0.2);
  transition: all 0.3s ease;
}

.btn-shop:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(90, 22, 37, 0.35);
  color: #fff;
}
</style>
</head>
<body>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="page-header">
  <div class="container">
    <h1 class="page-title">My Orders</h1>
    <div class="breadcrumb"><a href="index.php">Home</a> / Orders</div>
  </div>
</div>

<div class="container">
  <div class="orders-section">
    <h2 class="orders-title">Order History</h2>

    <?php if (count($orders) > 0): ?>
      <div class="orders-grid">
        <?php foreach ($orders as $o): ?>
          <?php
          // Fetch order items with their primary images for this order
          $stmtItems = $pdo->prepare("
            SELECT oi.*, 
                   (
                     SELECT pi.image_url 
                     FROM product_images pi 
                     WHERE pi.product_id = oi.product_id 
                     ORDER BY pi.is_primary DESC, pi.display_order ASC, pi.id ASC 
                     LIMIT 1
                   ) AS image_url
            FROM order_items oi 
            WHERE oi.order_id = :oid
          ");
          $stmtItems->execute([':oid' => $o['id']]);
          $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
          ?>
          <div class="order-card" onclick="openOrderDetailModal(<?= h(json_encode($o)) ?>,  <?= h(json_encode($items)) ?>)" style="cursor:pointer;" title="Click to view tracking & details">
            <div class="order-header">
              <div>
                <div class="order-id">Order #<?= h($o['order_code']) ?></div>
                <div class="order-date">Placed on <?= date('M d, Y', strtotime($o['created_at'])) ?></div>
              </div>
              <span class="order-status status-<?= strtolower($o['order_status']) ?>"><?= h($o['order_status']) ?></span>
            </div>

            <div class="order-items">
              <?php foreach ($items as $item): ?>
                <?php
                // Choose item emoji/icon based on name keywords
                $emoji = '💍';
                $nameLower = strtolower($item['product_name']);
                if (strpos($nameLower, 'earring') !== false || strpos($nameLower, 'tops') !== false) {
                  $emoji = '✨';
                } else if (strpos($nameLower, 'necklace') !== false || strpos($nameLower, 'chain') !== false) {
                  $emoji = '📿';
                } else if (strpos($nameLower, 'bracelet') !== false || strpos($nameLower, 'bangle') !== false) {
                  $emoji = '💫';
                } else if (strpos($nameLower, 'payal') !== false || strpos($nameLower, 'anklet') !== false) {
                  $emoji = '👣';
                }
                ?>
                <div class="order-item py-1">
                  <div class="item-img">
                    <?php if (!empty($item['image_url'])): ?>
                      <img src="<?= h($item['image_url']) ?>" alt="<?= h($item['product_name']) ?>" style="width:100%; height:100%; object-fit:cover; border-radius:10px;">
                    <?php else: ?>
                      <?= $emoji ?>
                    <?php endif; ?>
                  </div>
                  <div class="item-details">
                    <h4><?= h($item['product_name']) ?></h4>
                    <p style="font-size:12px; color:var(--gray); margin-top:2px;">SKU: <?= h($item['product_sku']) ?></p>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="order-footer" onclick="event.stopPropagation();">
              <div class="order-total">Total: <span><?= money((float)$o['total']) ?></span></div>
              <div class="order-actions">
                <button class="btn-details" onclick="openOrderDetailModal(<?= h(json_encode($o)) ?>, <?= h(json_encode($items)) ?>)" style="background:var(--maroon); border-color:var(--maroon); color:#fff;">
                  <i class="fas fa-truck"></i> Track & Details
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="no-orders">
        <i class="fas fa-shopping-bag"></i>
        <h3>No Orders Found</h3>
        <p>You have not placed any orders yet. Explore our luxury collection to buy your first piece.</p>
        <a href="pages/shop.php" class="btn-shop">Start Shopping</a>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius: 24px; border:none; box-shadow: 0 15px 40px rgba(0,0,0,0.15); background:#fff;">
      <div class="modal-header" style="border-bottom: 1px solid var(--border-color); padding: 25px;">
        <h5 class="modal-title" id="orderDetailModalLabel" style="font-family:'Playfair Display', serif; font-size:24px; font-weight:700; color:var(--maroon);">Order Details & Tracker</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="orderDetailModalBody" style="padding: 30px;">
        <!-- Will be populated dynamically -->
      </div>
    </div>
  </div>
</div>

<!-- Return Request Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 20px; border:none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
      <div class="modal-header" style="border-bottom: 1px solid #ECEAE6; padding: 25px;">
        <h5 class="modal-title" id="returnModalLabel" style="font-family:'Playfair Display', serif; font-size:22px; font-weight:700; color:var(--maroon);">Return or Exchange Request</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="returnForm">
        <div class="modal-body" style="padding: 25px;">
          <input type="hidden" name="order_id" id="returnOrderId">
          
          <div class="mb-3">
            <label class="form-label" style="font-size: 13px; font-weight:600; text-transform:uppercase; color:#555;">Request Type</label>
            <select name="type" class="form-select" style="border-radius: 8px;" required>
              <option value="RETURN">Return (Full Refund)</option>
              <option value="EXCHANGE">Exchange (Swap for different size/piece)</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label" style="font-size: 13px; font-weight:600; text-transform:uppercase; color:#555;">Reason for request</label>
            <select name="reason" class="form-select" style="border-radius: 8px;" required id="returnReasonSelect">
              <option value="Size issue">Size Issue / Fit not correct</option>
              <option value="Defect in piece">Damaged or Defective Item received</option>
              <option value="Not as expected">Item looks different from images</option>
              <option value="Wrong item">Wrong item shipped</option>
              <option value="Other">Other (Please specify below)</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label" style="font-size: 13px; font-weight:600; text-transform:uppercase; color:#555;">Additional details / comments</label>
            <textarea name="comments" class="form-control" rows="4" style="border-radius: 8px;" placeholder="Please describe the issue in detail..." required></textarea>
          </div>
        </div>
        <div class="modal-footer" style="border-top: 1px solid #ECEAE6; padding: 20px 25px;">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius:50px; font-size:13px; padding:8px 20px;">Cancel</button>
          <button type="submit" class="btn" style="background:var(--maroon); color:#fff; border-radius:50px; font-size:13px; padding:8px 25px;">Submit Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Tracker helper functions
function getProgressWidth(status) {
  switch (status.toUpperCase()) {
    case 'PLACED': return '0%';
    case 'CONFIRMED': return '25%';
    case 'PROCESSING': return '50%';
    case 'SHIPPED': return '75%';
    case 'DELIVERED': return '100%';
    default: return '0%';
  }
}

function getStepClass(currentStatus, stepStatus) {
  const statuses = ['PLACED', 'CONFIRMED', 'PROCESSING', 'SHIPPED', 'DELIVERED'];
  const currentIndex = statuses.indexOf(currentStatus.toUpperCase());
  const stepIndex = statuses.indexOf(stepStatus.toUpperCase());
  if (currentIndex === -1 || stepIndex === -1) return '';
  if (currentIndex === stepIndex) return 'active';
  if (stepIndex < currentIndex) return 'completed';
  return '';
}

function openOrderDetailModal(order, items) {
  const body = document.getElementById('orderDetailModalBody');
  
  // Format Date
  const dateObj = new Date(order.created_at);
  const formattedDate = dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) + ' ' + dateObj.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
  
  // Build Stepper Timeline HTML (Right Side)
  let stepperHtml = '';
  if (order.order_status !== 'CANCELLED') {
    const progressWidth = getProgressWidth(order.order_status);
    stepperHtml = `
      <div class="order-stepper" style="margin: 20px 0 35px 0;">
        <div class="stepper-line" style="width: ${progressWidth};"></div>
        <div class="step-node ${getStepClass(order.order_status, 'PLACED')}">
          <div class="step-circle"><i class="fas fa-shopping-cart"></i></div>
          <div class="step-label" style="font-size:10px;">Placed</div>
        </div>
        <div class="step-node ${getStepClass(order.order_status, 'CONFIRMED')}">
          <div class="step-circle"><i class="fas fa-check"></i></div>
          <div class="step-label" style="font-size:10px;">Confirmed</div>
        </div>
        <div class="step-node ${getStepClass(order.order_status, 'PROCESSING')}">
          <div class="step-circle"><i class="fas fa-cog fa-spin"></i></div>
          <div class="step-label" style="font-size:10px;">Processing</div>
        </div>
        <div class="step-node ${getStepClass(order.order_status, 'SHIPPED')}">
          <div class="step-circle"><i class="fas fa-truck"></i></div>
          <div class="step-label" style="font-size:10px;">Shipped</div>
        </div>
        <div class="step-node ${getStepClass(order.order_status, 'DELIVERED')}">
          <div class="step-circle"><i class="fas fa-gift"></i></div>
          <div class="step-label" style="font-size:10px;">Delivered</div>
        </div>
      </div>
    `;
  } else {
    stepperHtml = `
      <div style="background:#FCE8E6; border-left:4px solid #C5221F; padding: 15px 20px; border-radius: 8px; margin: 20px 0; font-size:13.5px; color:#C5221F;">
        <i class="fas fa-ban"></i> This order has been cancelled.
      </div>
    `;
  }
  
  // Tracking Box (Right Side)
  let trackingHtml = '';
  if ((order.order_status === 'SHIPPED' || order.order_status === 'DELIVERED') && order.tracking_number) {
    trackingHtml = `
      <div class="tracking-info-box" style="margin: 15px 0; padding:12px 18px; border-radius:8px;">
        <div>
          <p style="font-size:13px; font-weight:600; margin:0;"><i class="fas fa-truck-ramp-box"></i> Shipping Info</p>
          <p style="font-size:12.5px; margin:4px 0 0 0; color:#555;">
            Carrier: <strong>${order.shipping_carrier || 'Standard Delivery'}</strong> | 
            Tracking ID: <strong>${order.tracking_number}</strong>
          </p>
        </div>
        <div>
          <a href="https://www.google.com/search?q=track+${encodeURIComponent((order.shipping_carrier || '') + ' ' + order.tracking_number)}" target="_blank" class="tracking-ship-link" style="font-size:11px;">
            Track Package <i class="fas fa-external-link-alt" style="font-size:10px;"></i>
          </a>
        </div>
      </div>
    `;
  }

  // Build items rows (Left Side)
  let itemsHtml = '';
  items.forEach(item => {
    let emoji = '💍';
    const nameLower = item.product_name.toLowerCase();
    if (nameLower.includes('earring') || nameLower.includes('tops')) {
      emoji = '✨';
    } else if (nameLower.includes('necklace') || nameLower.includes('chain')) {
      emoji = '📿';
    } else if (nameLower.includes('bracelet') || nameLower.includes('bangle')) {
      emoji = '💫';
    } else if (nameLower.includes('payal') || nameLower.includes('anklet')) {
      emoji = '👣';
    }
    
    let imgTag = `<div class="item-img" style="width:70px; height:70px; font-size:32px; flex-shrink: 0; background:#FAF8F6; display:flex; align-items:center; justify-content:center; border:1px solid #EFEBE6; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.02);">${emoji}</div>`;
    if (item.image_url) {
      imgTag = `<div class="item-img" style="width:70px; height:70px; overflow:hidden; border-radius:12px; flex-shrink: 0; border:1px solid #EFEBE6; box-shadow:0 4px 12px rgba(0,0,0,0.02);"><img src="${item.image_url}" alt="${item.product_name}" style="width:100%; height:100%; object-fit:cover;"></div>`;
    }
    
    itemsHtml += `
      <div class="d-flex align-items-center gap-3 py-3" style="border-bottom: 1px dashed var(--border-color);">
        ${imgTag}
        <div>
          <h5 style="font-family:'Playfair Display', serif; font-size:15px; font-weight:700; color:#222; margin:0;">${item.product_name}</h5>
          <p style="font-size:12.5px; color:#666; margin:4px 0 0 0;">Qty: <strong>${item.quantity}</strong> | SKU: ${item.product_sku}</p>
        </div>
        <div style="margin-left:auto; font-family:'Playfair Display', serif; font-weight:700; color:var(--maroon); font-size:16px;">
          ₹${parseFloat(item.unit_price).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
        </div>
      </div>
    `;
  });

  // Calculate billing values (Left Side)
  const subtotal = parseFloat(order.subtotal);
  const shipping = parseFloat(order.shipping);
  const tax = subtotal * 0.03;
  const total = parseFloat(order.total);

  // Build modal body structure
  body.innerHTML = `
    <div class="row g-5">
      <!-- Left Side (Product Information) -->
      <div class="col-md-6">
        <h6 style="font-family:'Outfit', sans-serif; font-size:12px; font-weight:700; text-transform:uppercase; color:var(--maroon); letter-spacing:0.8px; margin-bottom:15px;">Product Information</h6>
        <div class="d-flex flex-column gap-2 mb-4">
          ${itemsHtml}
        </div>
        
        <!-- Billing Details -->
        <h6 style="font-family:'Outfit', sans-serif; font-size:12px; font-weight:700; text-transform:uppercase; color:var(--maroon); letter-spacing:0.8px; margin-bottom:15px;">Billing Summary</h6>
        <div class="p-3" style="background:var(--gray-bg); border-radius:16px; border:1px solid var(--border-color); font-size:13px; color:#555;">
          <div class="d-flex justify-content-between mb-2">
            <span>Subtotal</span>
            <span>₹${subtotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>GST (3%)</span>
            <span>₹${tax.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Shipping</span>
            <span>${shipping === 0 ? 'Free' : '₹' + shipping.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
          </div>
          <div class="d-flex justify-content-between pt-2" style="border-top:1px solid #e2ded8; font-weight:700; font-size:16px; color:#111;">
            <span>Total</span>
            <span style="color:var(--maroon);">₹${total.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
          </div>
        </div>
      </div>
      
      <!-- Right Side (Tracking & Order Details) -->
      <div class="col-md-6" style="border-left: 1px solid var(--border-color); padding-left: 35px;">
        <!-- Stepper -->
        <h6 style="font-family:'Outfit', sans-serif; font-size:12px; font-weight:700; text-transform:uppercase; color:var(--maroon); letter-spacing:0.8px; margin-bottom:15px;">Order Tracking</h6>
        ${stepperHtml}
        ${trackingHtml}
        
        <!-- Full Information -->
        <h6 style="font-family:'Outfit', sans-serif; font-size:12px; font-weight:700; text-transform:uppercase; color:var(--maroon); letter-spacing:0.8px; margin-top:25px; margin-bottom:15px;">Delivery Details</h6>
        
        <div class="d-flex flex-column gap-3" style="font-size:13.5px; color:#444;">
          <div class="row g-2">
            <div class="col-6">
              <span style="font-weight:600; color:#111; display:block;">Order Code</span>
              <span style="font-family:'Outfit', sans-serif; font-size:14px; font-weight:600; color:var(--maroon);">${order.order_code}</span>
            </div>
            <div class="col-6">
              <span style="font-weight:600; color:#111; display:block;">Placed On</span>
              <span>${formattedDate}</span>
            </div>
          </div>
          <div>
            <span style="font-weight:600; color:#111; display:block;">Shipping Recipient</span>
            <strong>${order.customer_name}</strong><br>
            <span style="color:#666; font-size:12.5px;">Phone: ${order.customer_phone || 'N/A'} | Email: ${order.customer_email || 'N/A'}</span>
          </div>
          <div>
            <span style="font-weight:600; color:#111; display:block;">Delivery Address</span>
            <span style="line-height:1.4; display:block; color:#555;">${order.shipping_address}</span>
          </div>
          <div>
            <span style="font-weight:600; color:#111; display:block;">Payment Information</span>
            <span>Method: <strong>${order.payment_method}</strong> | Status: <strong style="color:${order.payment_status === 'PAID' ? '#137333' : '#B28900'};">${order.payment_status}</strong></span>
          </div>
        </div>
        
        <!-- Invoice & Returns -->
        <div class="d-flex gap-3 mt-4 pt-3" style="border-top:1px solid var(--border-color);">
          <a href="pages/invoice.php?code=${order.order_code}" class="btn-details flex-grow-1 justify-content-center" style="background:var(--maroon); border-color:var(--maroon); color:#fff; font-size:11px;">
            <i class="fas fa-file-invoice"></i> Invoice
          </a>
          
          ${order.order_status === 'DELIVERED' && order.return_status === 'NONE' ? `
            <button class="btn-details flex-grow-1 justify-content-center btn-return-trigger-modal" style="font-size:11px;" data-order-id="${order.id}" data-order-code="${order.order_code}">
              <i class="fas fa-undo"></i> Return Request
            </button>
          ` : order.return_status !== 'NONE' ? `
            <div class="flex-grow-1" style="font-size:11px; font-weight:700; text-transform:uppercase; padding:10px; border-radius:50px; text-align:center; display:flex; align-items:center; justify-content:center;
              ${order.return_status === 'REQUESTED' ? 'background:#FFF3CD; color:#856404;' : order.return_status === 'APPROVED' ? 'background:#E8F5E9; color:#2E7D32;' : 'background:#FFEBEE; color:#C62828;'}">
              Return Status: ${order.return_status}
            </div>
          ` : ''}
        </div>
      </div>
    </div>
  `;
  
  // Register click handler for return request inside modal
  const btnReturnModal = body.querySelector('.btn-return-trigger-modal');
  if (btnReturnModal) {
    btnReturnModal.addEventListener('click', function() {
      // Hide current details modal first
      bootstrap.Modal.getInstance(document.getElementById('orderDetailModal')).hide();
      
      // Populate and show Return Request Modal
      const returnOrderIdInput = document.getElementById('returnOrderId');
      returnOrderIdInput.value = order.id;
      document.getElementById('returnModalLabel').textContent = 'Return/Exchange Request - Order #' + order.order_code;
      
      const rModal = new bootstrap.Modal(document.getElementById('returnModal'));
      rModal.show();
    });
  }

  // Show Details Modal
  const detailsModal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
  detailsModal.show();
}

document.addEventListener('DOMContentLoaded', function() {
  const returnModalEl = document.getElementById('returnModal');
  const returnModal = new bootstrap.Modal(returnModalEl);
  const returnForm = document.getElementById('returnForm');
  const returnOrderIdInput = document.getElementById('returnOrderId');

  document.querySelectorAll('.btn-return-trigger').forEach(btn => {
    btn.addEventListener('click', function() {
      const orderId = this.getAttribute('data-order-id');
      const orderCode = this.getAttribute('data-order-code');
      returnOrderIdInput.value = orderId;
      document.getElementById('returnModalLabel').textContent = 'Return/Exchange Request - Order #' + orderCode;
      returnModal.show();
    });
  });

  returnForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    try {
      const res = await fetch('<?= route('api/order-return.php') ?>', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      if (data.ok) {
        alert('Your return/exchange request has been successfully submitted and is under review.');
        location.reload();
      } else {
        alert('Error: ' + (data.error || 'Failed to submit request'));
      }
    } catch(err) {
      console.error(err);
      alert('Network error submitting return request');
    }
  });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>