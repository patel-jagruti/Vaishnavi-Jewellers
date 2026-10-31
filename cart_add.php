<?php
declare(strict_types=1);

// Start session FIRST to ensure consistent session_id
require __DIR__ . '/includes/functions.php';
ensure_session();

$id = $_GET['id'] ?? '';
$delta = (int)($_GET['delta'] ?? 1);

if ($id === '') {
  header('Location: /visnavi jwallers/cart.php');
  exit;
}

// Try DB cart first
try {
  require __DIR__ . '/includes/db.php';
  require __DIR__ . '/includes/catalog_db.php';
  require __DIR__ . '/includes/cart_db.php';

  $pdo = db();
  $p = catalog_find_product_by_id((string)$id);

  if ($p) {
    // Product exists in DB - use DB cart
    cart_add($pdo, (string)$id, $delta);
    header('Location: /visnavi jwallers/cart.php');
    exit;
  }
} catch (Throwable $e) {
  // DB failed, fall through to session cart
}

// Fallback: use session-based cart (for demo products)
$cart = $_SESSION['cart'] ?? [];
$qty = (int)($cart[$id] ?? 0);
$qty += $delta;
if ($qty <= 0) {
  unset($cart[$id]);
} else {
  $cart[$id] = $qty;
}
$_SESSION['cart'] = $cart;

header('Location: /visnavi jwallers/cart.php');
exit;