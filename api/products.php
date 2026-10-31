<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

header('Cache-Control: no-store, max-age=0');
header('Content-Type: application/json; charset=utf-8');

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$q = trim((string)($_GET['q'] ?? ''));
$pdo = db();

$limit = min(50, max(1, (int)($_GET['limit'] ?? 24)));

try {
  // If requesting a single product details
  if ($id !== null) {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name
      FROM products p
      LEFT JOIN categories c ON c.id = p.category_id
      WHERE p.id = :id AND p.is_active = 1
      LIMIT 1");
    $stmt->execute([':id' => $id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$p) {
      echo json_encode(['error' => 'Product not found']);
      http_response_code(404);
      exit;
    }

    $finalPrice = price_now($p);
    $basePriceNow = base_price_now($p);

    // Fetch images
    $imgStmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = :id ORDER BY is_primary DESC, display_order ASC, id ASC");
    $imgStmt->execute([':id' => $id]);
    $images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($images)) {
      $defaultImages = ['Rings.png','Earrings.png','Necklaces.png','Bangles.png','Bracelets.png','Pendants.png'];
      $images = ['assets/images/' . $defaultImages[$id % count($defaultImages)]];
    }

    echo json_encode([
      'product' => [
        'id' => (string)$p['id'],
        'name' => (string)$p['name'],
        'category' => (string)($p['category_name'] ?? 'Gold'),
        'sku' => (string)$p['sku'],
        'price' => $finalPrice,
        'base_price' => $basePriceNow,
        'discount_percent' => (float)$p['discount_percent'],
        'metal' => (string)($p['metal'] ?? 'Gold'),
        'stone' => (string)($p['stone'] ?? ''),
        'weight_grams' => (float)($p['weight_grams'] ?? 0),
        'purity' => (string)($p['purity'] ?? ''),
        'short_description' => (string)($p['short_description'] ?? ''),
        'description' => (string)($p['description'] ?? ''),
        'images' => $images
      ]
    ]);
    exit;
  }

  // Fallback to list/search products
  if ($q !== '') {
    $stmt = $pdo->prepare(
      "SELECT p.id, p.name, p.slug, p.category_id,
              p.base_price, p.price_type, p.purity, p.making_charge_percent, p.weight_grams,
              p.base_price AS price, p.discount_percent,
              c.name AS category,
              (SELECT pi.image_url
                 FROM product_images pi
                 WHERE pi.product_id = p.id
                 ORDER BY pi.is_primary DESC, pi.display_order ASC, pi.id ASC
                 LIMIT 1) AS image_url
       FROM products p
       LEFT JOIN categories c ON c.id = p.category_id
       WHERE p.is_active = 1
         AND (p.name LIKE :q OR p.sku LIKE :q OR c.name LIKE :q)
       ORDER BY p.display_order ASC, p.created_at DESC
       LIMIT :lim"
    );
    $like = '%' . $q . '%';
    $stmt->bindValue(':q', $like);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
  } else {
    $stmt = $pdo->prepare(
      "SELECT p.id, p.name, p.slug, p.category_id,
              p.base_price, p.price_type, p.purity, p.making_charge_percent, p.weight_grams,
              p.base_price AS price, p.discount_percent,
              c.name AS category,
              (SELECT pi.image_url
                 FROM product_images pi
                 WHERE pi.product_id = p.id
                 ORDER BY pi.is_primary DESC, pi.display_order ASC, pi.id ASC
                 LIMIT 1) AS image_url
       FROM products p
       LEFT JOIN categories c ON c.id = p.category_id
       WHERE p.is_active = 1
       ORDER BY p.display_order ASC, p.created_at DESC
       LIMIT :lim"
    );
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
  }

  $rows = $stmt->fetchAll();

  $items = [];
  foreach ($rows as $r) {
    $final = price_now($r);

    $items[] = [
      'id' => (string)$r['id'],
      'name' => (string)$r['name'],
      'category' => (string)($r['category'] ?? ''),
      'price' => $final,
      'image_url' => !empty($r['image_url']) ? product_image((string)$r['image_url']) : '',
      'url' => 'pages/product.php?id=' . urlencode((string)$r['id']),
    ];
  }

  echo json_encode(['products' => $items]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Failed to load products', 'details' => $e->getMessage()]);
}
