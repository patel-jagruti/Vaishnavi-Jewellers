<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';
ensure_session();

$id = (string)($_GET['id'] ?? '');
$qty = (int)($_GET['qty'] ?? 0);

if ($id === '') {
  header('Location: /visnavi jwallers/cart.php');
  exit;
}

// Try DB cart first
try {
  require_once __DIR__ . '/includes/catalog_db.php';
  require_once __DIR__ . '/includes/db.php';
  require_once __DIR__ . '/includes/cart_db.php';

  $p = catalog_find_product_by_id((string)$id);
  if ($p) {
    $pdo = db();
    cart_set_qty($pdo, $id, $qty, null);
    header('Location: /visnavi jwallers/cart.php');
    exit;
  }
} catch (Throwable $e) {
  // DB failed, fall through to session cart
}

// Fallback: use session cart
$cart = $_SESSION['cart'] ?? [];
if ($qty <= 0) {
  unset($cart[$id]);
} else {
  $cart[$id] = $qty;
}
$_SESSION['cart'] = $cart;

header('Location: /visnavi jwallers/cart.php');
exit;