<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($q) < 2) {
  echo json_encode(['items' => []]);
  exit;
}

try {
  $pdo = db();

  // Search by product name/sku/description
  $like = '%' . $q . '%';

  $stmt = $pdo->prepare(
    "SELECT p.id, p.name, p.slug, p.sku,
            p.base_price AS price, p.discount_percent,
            c.name AS category_name,
            (
              SELECT pi.image_url
              FROM product_images pi
              WHERE pi.product_id = p.id
              ORDER BY pi.is_primary DESC, pi.display_order ASC, pi.id ASC
              LIMIT 1
            ) AS primary_image_url
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.is_active = 1 AND (
       p.name LIKE :like OR p.sku LIKE :like OR p.description LIKE :like OR p.short_description LIKE :like
     )
     ORDER BY p.created_at DESC
     LIMIT 12"
  );

  $stmt->execute([':like' => $like]);
  $rows = $stmt->fetchAll();

  $items = [];
  foreach ($rows as $r) {
    $price = (float)($r['price'] ?? 0);
    $discount = (float)($r['discount_percent'] ?? 0);
    $final = $discount > 0 ? $price * (1 - ($discount / 100)) : $price;

    $items[] = [
      'id' => (string)$r['id'],
      'name' => (string)$r['name'],
      'slug' => (string)($r['slug'] ?? ''),
      'sku' => (string)($r['sku'] ?? ''),
      'category' => (string)($r['category_name'] ?? ''),
      'price' => $final,
      'image_url' => $r['primary_image_url'] ? product_image((string)$r['primary_image_url']) : null,
      'url' => 'pages/product.php?id=' . urlencode((string)$r['id']),
    ];
  }

  echo json_encode(['items' => $items]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['items' => [], 'error' => 'Search failed.']);
}

