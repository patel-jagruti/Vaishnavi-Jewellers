<?php
declare(strict_types=1);

/**
 * Submit Review API
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
ensure_session();

$productId = (string)($_POST['product_id'] ?? '');
$rating = (int)($_POST['rating'] ?? 0);
$title = trim((string)($_POST['title'] ?? ''));
$body = trim((string)($_POST['body'] ?? ''));

if (!$productId || $rating < 1 || $rating > 5) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid product or rating'], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $pdo = db();

  $stmt = $pdo->prepare('INSERT INTO reviews (product_id, user_id, rating, title, body, is_verified_purchase, created_at) VALUES (:pid, :uid, :rating, :title, :body, 0, NOW())');

  $userId = !empty($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
  $stmt->execute([
    ':pid' => $productId,
    ':uid' => $userId,
    ':rating' => $rating,
    ':title' => $title ?: null,
    ':body' => $body ?: null,
  ]);

  // Update product avg rating
  $upd = $pdo->prepare('UPDATE products SET rating_count = rating_count + 1, rating_avg = (rating_avg * rating_count + :r) / (rating_count + 1) WHERE id = :pid');
  $upd->execute([':r' => $rating, ':pid' => $productId]);

  echo json_encode(['success' => true, 'message' => 'Thank you for your review!'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Could not submit review'], JSON_UNESCAPED_UNICODE);
}
exit;