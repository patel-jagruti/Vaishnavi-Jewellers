<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/catalog_db.php';
require_once __DIR__ . '/../includes/cart_db.php';

ensure_session();

$action = (string)($_POST['action'] ?? 'toggle');
$id = (string)($_POST['product_id'] ?? '');

if ($id === '') {
  json_response(['ok' => false, 'error' => 'Missing product_id'], 400);
}

// Validate product exists in DB-backed catalog.
if (!catalog_find_product_by_id((string)$id)) {
  json_response(['ok' => false, 'error' => 'Invalid product_id'], 404);
}

try {
  $pdo = db();
  ensure_wishlist_table($pdo);
  
  $actualAction = $action;
  if ($action === 'toggle') {
    $uid = current_user_id();
    $sid = guest_session_id();
    if ($uid !== null) {
      $stmt = $pdo->prepare('SELECT 1 FROM wishlists WHERE user_id = :uid AND product_id = :pid');
      $stmt->execute([':uid' => $uid, ':pid' => $id]);
    } else {
      $stmt = $pdo->prepare('SELECT 1 FROM wishlists WHERE session_id = :sid AND product_id = :pid');
      $stmt->execute([':sid' => $sid, ':pid' => $id]);
    }
    $exists = (bool)$stmt->fetchColumn();
    $actualAction = $exists ? 'remove' : 'add';
  }

  wishlist_toggle($pdo, $id, $actualAction);
  $count = wishlist_count($pdo);
  
  json_response([
    'ok' => true, 
    'action' => $actualAction === 'remove' ? 'removed' : 'added', 
    'wishlist_count' => $count
  ]);
} catch (Throwable $e) {
  json_response(['ok' => false, 'error' => 'Wishlist update failed: ' . $e->getMessage()], 500);
}
