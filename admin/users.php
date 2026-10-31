<?php
declare(strict_types=1);

/**
 * Admin Users/Customers Management
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

ensure_session();
require_login();
require_admin();

$pdo = db();
$action = $_GET['action'] ?? 'list';
$notice = '';

// Handle role update
if ($action === 'update_role' && !empty($_GET['id']) && !empty($_GET['role'])) {
  $id = (int)$_GET['id'];
  $role = trim($_GET['role']);
  if (in_array($role, ['customer', 'admin'], true)) {
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$role, $id]);
    $notice = 'User role updated successfully.';
  }
}

// Handle delete
if ($action === 'delete' && !empty($_GET['id'])) {
  $id = (int)$_GET['id'];
  $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
  $stmt->execute([$id]);
  $notice = 'User deleted successfully.';
}

// Fetch users with order count
$users = $pdo->query("
  SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count
  FROM users u
  ORDER BY u.id DESC
  LIMIT 50
")->fetchAll();

$stats = [
  'total' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
  'admin' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn(),
  'customer' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Customers | Admin Panel</title>
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
    
    /* Stats */
    .admin-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
    .stat-card { background: #fff; padding: 24px; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: 0 10px 30px rgba(0,0,0,0.02); text-align: center; transition: var(--transition); }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(107,30,45,0.06); }
    .stat-card .value { font-family: 'Outfit', sans-serif; font-size: 32px; font-weight: 700; color: var(--maroon); }
    .stat-card .label { font-size: 13px; color: #777; margin-top: 6px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; }
    
    /* Section card */
    .admin-section { background: #fff; padding: 30px; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: 0 10px 30px rgba(0,0,0,0.02); margin-bottom: 30px; }
    .admin-section h2 { font-family: 'Cormorant Garamond', serif; font-size: 24px; font-weight: 600; color: var(--maroon); margin-top: 0; margin-bottom: 20px; }
    
    /* Table style */
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 16px; text-align: left; border-bottom: 1px solid #F0ECE7; vertical-align: middle; }
    th { background: var(--gray-light); font-weight: 600; font-size: 11px; text-transform: uppercase; color: var(--maroon); letter-spacing: 1px; }
    tr:hover td { background: #FAF9F8; }
    
    /* Badges */
    .badge { padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; display: inline-block; }
    .badge-admin { background: rgba(107,30,45,0.1); color: var(--maroon); }
    .badge-customer { background: #e8f5e9; color: #2e7d32; }
    
    /* Inputs & Selects */
    .select-role { font-family: 'Poppins', sans-serif; font-size: 13px; padding: 6px 12px; border: 1px solid #DDD7CE; border-radius: 8px; background: #fff; cursor: pointer; outline: none; transition: var(--transition); }
    .select-role:focus { border-color: var(--gold); }
    
    /* Buttons */
    .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: var(--maroon); color: #fff; text-decoration: none; border-radius: 30px; border: none; cursor: pointer; font-weight: 600; font-size: 13px; transition: var(--transition); }
    .btn:hover { background: var(--maroon-dark); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(107,30,45,0.3); }
    .btn-sm { padding: 6px 12px; font-size: 11px; }
    .btn-danger { background: #d32f2f; }
    .btn-danger:hover { background: #c62828; box-shadow: 0 4px 12px rgba(211,47,47,0.3); }
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
        <a href="/visnavi%20jwallers/admin/users.php" class="active"><i class="fas fa-users"></i> Customers</a>
        <a href="/visnavi%20jwallers/admin/banners.php"><i class="fas fa-images"></i> Banners</a>
        <a href="/visnavi%20jwallers/admin/blogs.php"><i class="fas fa-newspaper"></i> Blogs</a>
        <a href="/visnavi%20jwallers/admin/settings.php"><i class="fas fa-cog"></i> Settings</a>
        <a href="/visnavi%20jwallers/index.php"><i class="fas fa-store"></i> Back to Store</a>
      </nav>
    </aside>

    <main class="admin-main">
      <h1>Customers</h1>

      <?php if ($notice): ?>
        <div style="background:#d4edda; padding:12px; border-radius:4px; margin-bottom:16px;"><?= h($notice) ?></div>
      <?php endif; ?>

      <div class="admin-stats">
        <div class="stat-card">
          <div class="value"><?= $stats['total'] ?></div>
          <div class="label">Total Users</div>
        </div>
        <div class="stat-card">
          <div class="value"><?= $stats['admin'] ?></div>
          <div class="label">Admins</div>
        </div>
        <div class="stat-card">
          <div class="value"><?= $stats['customer'] ?></div>
          <div class="label">Customers</div>
        </div>
      </div>

      <div class="admin-section">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Role</th>
              <th>Orders</th>
              <th>Joined</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
              <tr>
                <td>#<?= (int)$u['id'] ?></td>
                <td style="font-weight:600; color:var(--maroon);"><?= h((string)$u['full_name']) ?></td>
                <td><?= h((string)$u['email']) ?></td>
                <td><?= h((string)($u['phone'] ?? 'N/A')) ?></td>
                <td>
                  <span class="badge badge-<?= (string)$u['role'] ?>">
                    <?= ucfirst((string)$u['role']) ?>
                  </span>
                </td>
                <td style="font-weight:600;"><?= (int)$u['order_count'] ?></td>
                <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                <td>
                  <div class="actions-cell">
                    <select onchange="location.href='?action=update_role&id=<?= (int)$u['id'] ?>&role='+this.value" class="select-role">
                      <option value="customer" <?= $u['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                      <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <a class="btn btn-sm btn-danger" href="?action=delete&id=<?= (int)$u['id'] ?>" onclick="return confirm('Delete user?')"><i class="fas fa-trash"></i> Delete</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <?php if (empty($users)): ?>
          <p style="padding:24px; text-align:center; color:#666;">No users registered yet.</p>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>