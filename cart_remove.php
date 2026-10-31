<?php

declare(strict_types=1);

require __DIR__ . '/includes/functions.php';
ensure_session();

$id = (string)($_GET['id'] ?? '');

if ($id === '') {
  header('Location: /visnavi jwallers/cart.php');
  exit;
}

try {
  require __DIR__ . '/includes/db.php';
  require __DIR__ . '/includes/catalog_db.php';
  require __DIR__ . '/includes/cart_db.php';

  $pdo = db();
  cart_remove($pdo, $id, null);
} catch (Throwable $e) {
  // Fallback to session cart
  $cart = $_SESSION['cart'] ?? [];
  unset($cart[$id]);
  $_SESSION['cart'] = $cart;
}

header('Location: /visnavi jwallers/cart.php');
exit;