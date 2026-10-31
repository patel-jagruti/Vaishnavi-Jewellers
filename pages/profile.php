<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/base_url.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

ensure_session();
require_login(); // Force login check

$userSession = $_SESSION['user'];
$pdo = db();

// Fetch current user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $userSession['id']]);
$dbUser = $stmt->fetch();

if (!$dbUser) {
  // Session corrupted or user deleted
  session_destroy();
  header('Location: /visnavi jwallers/pages/login.php');
  exit;
}

$notice = '';
$error = '';

// Handle save changes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $firstName = trim((string)($_POST['first_name'] ?? ''));
  $lastName = trim((string)($_POST['last_name'] ?? ''));
  $phone = trim((string)($_POST['phone'] ?? ''));

  $fullName = trim($firstName . ' ' . $lastName);

  if ($fullName === '') {
    $error = 'Name cannot be empty.';
  } else {
    $stmtUpdate = $pdo->prepare("UPDATE users SET full_name = :full_name, phone = :phone WHERE id = :id");
    $stmtUpdate->execute([
      ':full_name' => $fullName,
      ':phone' => $phone !== '' ? $phone : null,
      ':id' => $dbUser['id']
    ]);

    // Refresh user session display name
    $_SESSION['user']['full_name'] = $fullName;
    
    // Refresh dbUser details
    $dbUser['full_name'] = $fullName;
    $dbUser['phone'] = $phone;

    $notice = 'Changes saved successfully.';
  }
}

// Split full name for display
$parts = explode(' ', $dbUser['full_name']);
$firstName = $parts[0] ?? '';
$lastName = isset($parts[1]) ? implode(' ', array_slice($parts, 1)) : '';

// Fetch real user orders
$stmtOrders = $pdo->prepare("
  SELECT o.*, 
         (SELECT SUM(quantity) FROM order_items WHERE order_id = o.id) AS total_items
  FROM orders o 
  WHERE o.user_id = :uid 
  ORDER BY o.created_at DESC 
  LIMIT 5
");
$stmtOrders->execute([':uid' => $dbUser['id']]);
$orders = $stmtOrders->fetchAll();

$pageTitle = 'My Profile | Vaishnavi Jewellers';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
.main-content {
  display: none !important;
}
.profile-hero {
  background: linear-gradient(135deg, #5A1626 0%, #7A1F2A 100%);
  color: #fff;
  padding: 65px 0;
}

.profile-hero h1 {
  font-family: 'Playfair Display', serif;
  font-size: 38px;
  font-weight: 700;
  margin-bottom: 8px;
}

.profile-hero p {
  font-size: 15px;
  opacity: 0.9;
  margin: 0;
}

.profile-content {
  background: #FAF8F6;
  padding: 60px 0;
}

.sidebar-card {
  background: #fff;
  border-radius: 20px;
  padding: 24px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.03);
  border: 1px solid #F0ECE7;
}

.sidebar-nav a {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 14px 20px;
  border-radius: 12px;
  margin-bottom: 8px;
  color: #333;
  text-decoration: none;
  font-weight: 500;
  font-size: 15px;
  transition: all 0.3s ease;
}

.sidebar-nav a:hover, .sidebar-nav a.active {
  background: #5A1626;
  color: #fff;
}

.sidebar-nav a i {
  font-size: 18px;
}

.profile-card {
  background: #fff;
  border-radius: 20px;
  padding: 35px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.03);
  border: 1px solid #F0ECE7;
}

.profile-card h3 {
  font-family: 'Playfair Display', serif;
  font-size: 24px;
  color: #5A1626;
  margin-bottom: 25px;
}

.form-label {
  font-size: 14px;
  font-weight: 500;
  color: #444;
  margin-bottom: 8px;
}

.form-control {
  padding: 14px 18px;
  border-radius: 12px;
  border: 1.5px solid #E5E0DA;
  font-size: 14px;
  margin-bottom: 20px;
}

.form-control:focus {
  border-color: #5A1626;
  box-shadow: 0 0 0 4px rgba(90, 22, 38, 0.08);
}

.btn-save {
  background: #5A1626;
  color: #fff;
  padding: 12px 30px;
  border-radius: 50px;
  font-weight: 600;
  border: none;
  font-size: 15px;
  transition: all 0.3s ease;
}

.btn-save:hover {
  background: #440F1B;
  transform: translateY(-1px);
}

.order-card {
  background: #fff;
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 16px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.02);
  border: 1px solid #F0ECE7;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  font-size: 14px;
}

.order-id {
  font-weight: 600;
  color: #222;
}

.order-status {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.order-status.placed { background: #E8F4FD; color: #1D88E5; }
.order-status.delivered { background: #E8F5E9; color: #388E3C; }
.order-status.cancelled { background: #FFEBEE; color: #D32F2F; }

.order-summary {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 12px;
  border-top: 1px dashed #E5E0DA;
  font-size: 14px;
  color: #666;
}

.order-total {
  font-weight: 700;
  font-size: 16px;
  color: #5A1626;
}

.alert-success {
  background-color: #E8F5E9;
  border-color: #C8E6C9;
  color: #2E7D32;
  padding: 12px 15px;
  border-radius: 12px;
  margin-bottom: 24px;
  font-size: 14px;
}

.alert-danger {
  background-color: #FFEBEE;
  border-color: #FFCDD2;
  color: #C62828;
  padding: 12px 15px;
  border-radius: 12px;
  margin-bottom: 24px;
  font-size: 14px;
}
</style>

<div class="profile-hero">
  <div class="container">
    <h1>Welcome, <?= h($dbUser['full_name']) ?>!</h1>
    <p>Manage your account settings and track your purchases.</p>
  </div>
</div>

<div class="profile-content">
  <div class="container">
    <div class="row g-4">
      
      <!-- Left Sidebar navigation -->
      <div class="col-lg-3">
        <div class="sidebar-card">
          <div class="sidebar-nav">
            <a href="pages/profile.php" class="active"><i class="fas fa-user"></i> My Profile</a>
            <a href="pages/orders.php"><i class="fas fa-box"></i> My Orders</a>
            <a href="pages/wishlist.php"><i class="fas fa-heart"></i> Wishlist</a>
            <a href="pages/cart.php"><i class="fas fa-shopping-bag"></i> Cart</a>
            <a href="pages/appointment.php"><i class="fas fa-calendar"></i> Book Visit</a>
            <a href="pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
          </div>
        </div>
      </div>
      
      <!-- Right panel -->
      <div class="col-lg-9">
        
        <?php if ($notice): ?>
          <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= h($notice) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?= h($error) ?></div>
        <?php endif; ?>

        <div class="profile-card">
          <h3>Personal Information</h3>
          
          <form method="POST" action="pages/profile.php">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">First Name</label>
                  <input type="text" name="first_name" class="form-control" placeholder="First Name" value="<?= h($firstName) ?>" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Last Name</label>
                  <input type="text" name="last_name" class="form-control" placeholder="Last Name" value="<?= h($lastName) ?>">
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Email Address</label>
              <input type="email" class="form-control" value="<?= h($dbUser['email']) ?>" readonly style="background-color: #FAF8F6; cursor: not-allowed;" title="Email cannot be changed.">
            </div>
            
            <div class="mb-4">
              <label class="form-label">Phone Number</label>
              <input type="tel" name="phone" class="form-control" placeholder="+91 98765 43210" value="<?= h((string)$dbUser['phone']) ?>">
            </div>
            
            <button type="submit" class="btn-save">Save Changes</button>
          </form>
        </div>

        <div class="profile-card mt-4">
          <h3>Recent Orders</h3>
          
          <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $o): ?>
              <div class="order-card">
                <div class="order-header">
                  <span class="order-id">Order #<?= h($o['order_code']) ?></span>
                  <span class="order-status <?= strtolower($o['order_status']) ?>"><?= h($o['order_status']) ?></span>
                </div>
                <div class="order-summary">
                  <span>Items Count: <?= (int)$o['total_items'] ?> | Date: <?= date('d M Y', strtotime($o['created_at'])) ?></span>
                  <span class="order-total"><?= money((float)$o['total']) ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p style="color: var(--gray); font-size: 15px; margin: 0;">You have not placed any orders yet.</p>
          <?php endif; ?>
        </div>

      </div>

    </div>
  </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
