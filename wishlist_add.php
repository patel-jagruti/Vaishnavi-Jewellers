<?php
declare(strict_types=1);

require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/catalog_db.php';
require __DIR__ . '/includes/cart_db.php';
ensure_session();

$id = $_GET['id'] ?? '';
$action = $_GET['action'] ?? 'add';

if ($id === '') {
  header('Location: pages/wishlist.php');
  exit;
}

$p = catalog_find_product_by_id((string)$id);
if (!$p) {
  header('Location: pages/wishlist.php');
  exit;
}

try {
  $pdo = db();
  wishlist_toggle($pdo, (string)$id, $action);
} catch (Throwable $e) {
  // Fallback: session-based wishlist
  $wishlist = $_SESSION['wishlist'] ?? [];
  if ($action === 'remove') {
    unset($wishlist[$id]);
  } else {
    $wishlist[$id] = 1;
  }
  $_SESSION['wishlist'] = $wishlist;
}

header('Location: pages/wishlist.php');
exit;
