<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

// Inputs
$categorySlug = trim((string)($_GET['category'] ?? ''));
$search = trim((string)($_GET['search'] ?? ''));
$sort = trim((string)($_GET['sort'] ?? 'featured'));

// Price ranges (array)
$priceRanges = $_GET['price_range'] ?? [];
if (!is_array($priceRanges)) {
  $priceRanges = $priceRanges !== '' ? [$priceRanges] : [];
}

// Genders (array)
$gendersInput = $_GET['gender'] ?? [];
$genders = [];
if (is_array($gendersInput)) {
  foreach ($gendersInput as $g) {
    $genders[] = strtolower(trim((string)$g));
  }
} else {
  $g = strtolower(trim((string)$gendersInput));
  if ($g !== '') $genders[] = $g;
}

// Metals (array)
$metalsInput = $_GET['metal'] ?? [];
$metals = [];
if (is_array($metalsInput)) {
  foreach ($metalsInput as $m) {
    $mVal = strtolower(trim((string)$m));
    if ($mVal !== '') $metals[] = $mVal;
  }
} else {
  $mVal = strtolower(trim((string)$metalsInput));
  if ($mVal !== '') $metals[] = $mVal;
}

// Purities (array)
$puritiesInput = $_GET['purity'] ?? [];
$purities = [];
if (is_array($puritiesInput)) {
  foreach ($puritiesInput as $p) {
    $pVal = strtolower(trim((string)$p));
    if ($pVal !== '') $purities[] = $pVal;
  }
} else {
  $pVal = strtolower(trim((string)$puritiesInput));
  if ($pVal !== '') $purities[] = $pVal;
}

// Stones (array)
$stonesInput = $_GET['stone'] ?? [];
$stones = [];
if (is_array($stonesInput)) {
  foreach ($stonesInput as $s) {
    $sVal = strtolower(trim((string)$s));
    if ($sVal !== '') $stones[] = $sVal;
  }
} else {
  $sVal = strtolower(trim((string)$stonesInput));
  if ($sVal !== '') $stones[] = $sVal;
}

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(24, max(6, (int)($_GET['limit'] ?? 12)));
$offset = ($page - 1) * $limit;

try {
  $pdo = db();

  $where = ['p.is_active = 1'];
  $params = [];

  // Category
  if ($categorySlug !== '' && $categorySlug !== 'all-jewellery') {
    $where[] = 'c.slug = :cat_slug';
    $params[':cat_slug'] = $categorySlug;
  }

  // Search
  if ($search !== '') {
    $where[] = '(p.name LIKE :search OR p.sku LIKE :search OR p.description LIKE :search OR p.stone LIKE :search OR p.metal LIKE :search)';
    $params[':search'] = '%' . $search . '%';
  }

  // Price Ranges
  $priceConditions = [];
  if (!empty($priceRanges)) {
    foreach ($priceRanges as $range) {
      if ($range === 'under_10k') $priceConditions[] = 'p.base_price < 10000';
      if ($range === '10k_25k') $priceConditions[] = 'p.base_price BETWEEN 10000 AND 25000';
      if ($range === '25k_50k') $priceConditions[] = 'p.base_price BETWEEN 25000 AND 50000';
      if ($range === '50k_100k') $priceConditions[] = 'p.base_price BETWEEN 50000 AND 100000';
      if ($range === 'above_100k') $priceConditions[] = 'p.base_price > 100000';
    }
    if ($priceConditions) {
      $where[] = '(' . implode(' OR ', $priceConditions) . ')';
    }
  }

  // Genders
  if (!empty($genders)) {
    $genderPlaceholders = [];
    foreach ($genders as $idx => $g) {
      $key = ':gender_' . $idx;
      $genderPlaceholders[] = $key;
      $params[$key] = $g;
    }
    $where[] = '(p.gender IN (' . implode(', ', $genderPlaceholders) . ') OR p.gender = \'unisex\')';
  }

  // Metals
  if (!empty($metals)) {
    $placeholders = [];
    foreach ($metals as $idx => $m) {
      $key = ':metal_' . $idx;
      $placeholders[] = $key;
      $params[$key] = $m;
    }
    $where[] = 'LOWER(p.metal) IN (' . implode(', ', $placeholders) . ')';
  }

  // Purities
  if (!empty($purities)) {
    $placeholders = [];
    foreach ($purities as $idx => $pur) {
      $key = ':purity_' . $idx;
      $placeholders[] = $key;
      $params[$key] = $pur;
    }
    $where[] = 'LOWER(p.purity) IN (' . implode(', ', $placeholders) . ')';
  }

  // Stones
  if (!empty($stones)) {
    $placeholders = [];
    foreach ($stones as $idx => $st) {
      $key = ':stone_' . $idx;
      $placeholders[] = $key;
      $params[$key] = $st;
    }
    $where[] = 'LOWER(p.stone) IN (' . implode(', ', $placeholders) . ')';
  }

  $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

  // Count Total
  $countStmt = $pdo->prepare("SELECT COUNT(*) AS cnt
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    $whereSql");
  $countStmt->execute($params);
  $total = (int)($countStmt->fetch()['cnt'] ?? 0);

  // Sorting
  $orderBy = 'p.display_order ASC, p.created_at DESC';
  if ($sort === 'price_asc') {
    $orderBy = 'p.base_price ASC';
  } else if ($sort === 'price_desc') {
    $orderBy = 'p.base_price DESC';
  } else if ($sort === 'newest') {
    $orderBy = 'p.id DESC';
  }

  // Fetch items
  $stmt = $pdo->prepare("SELECT
      p.id, p.name, p.slug, p.sku,
      p.base_price, p.price_type, p.purity, p.making_charge_percent, p.weight_grams,
      p.discount_percent, p.metal, p.stone,
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
    $whereSql
    ORDER BY $orderBy
    LIMIT :limit OFFSET :offset");

  foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
  }
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();

  $rows = $stmt->fetchAll();

  $items = [];
  foreach ($rows as $r) {
    $final = price_now($r);

    $items[] = [
      'id' => (string)$r['id'],
      'name' => (string)$r['name'],
      'slug' => (string)($r['slug'] ?? ''),
      'sku' => (string)($r['sku'] ?? ''),
      'category' => (string)($r['category_name'] ?? ''),
      'price' => $final,
      'base_price' => base_price_now($r),
      'discount_percent' => (float)($r['discount_percent'] ?? 0),
      'metal' => (string)($r['metal'] ?? 'Gold'),
      'stone' => (string)($r['stone'] ?? ''),
      'image_url' => $r['primary_image_url'] ? product_image((string)$r['primary_image_url']) : null,
      'url' => 'pages/product.php?id=' . urlencode((string)$r['id']),
    ];
  }

  $pages = $limit > 0 ? (int)ceil($total / $limit) : 1;

  echo json_encode([
    'items' => $items,
    'page' => $page,
    'pages' => $pages,
    'total' => $total,
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  $msg = $e->getMessage();
  echo json_encode(['items' => [], 'error' => 'Filtering failed.', 'details' => $msg]);
}
