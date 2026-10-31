<?php
require_once __DIR__ . '/../includes/base_url.php';
$baseUrl = '/visnavi jwallers/';

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/cart_db.php';
require_once __DIR__ . '/../includes/catalog_db.php';

$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$categories = [
  '' => ['All Jewellery', 'fa-gem', 0]
];

$wishlistProductIds = [];
try {
  $pdo = db();
  ensure_wishlist_table($pdo);
  $wishlistProductIds = array_map(function($item) {
    return (string)$item['id'];
  }, wishlist_items($pdo));
  
  // Total active products count for All Jewellery
  $totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_active=1")->fetchColumn();
  $categories[''][2] = $totalProducts;

  // Fetch active main categories and count products mapped to each
  $catsQuery = $pdo->query("
    SELECT c.name, c.slug, (SELECT COUNT(*) FROM products WHERE category_id = c.id AND is_active=1) as cnt 
    FROM categories c 
    WHERE c.is_active=1 
    ORDER BY c.display_order ASC
  ")->fetchAll(PDO::FETCH_ASSOC);

  // Fetch active subcategories and count products mapped to each
  $subcatsQuery = $pdo->query("
    SELECT s.name, s.slug, (SELECT COUNT(*) FROM products WHERE subcategory_id = s.id AND is_active=1) as cnt 
    FROM subcategories s 
    WHERE s.is_active=1 
    ORDER BY s.display_order ASC
  ")->fetchAll(PDO::FETCH_ASSOC);

  $slugMap = [
    '' => '',
    'all-jewellery' => '',
    'all' => ''
  ];

  // Populate main categories into $categories and $slugMap
  foreach ($catsQuery as $c) {
    $cSlug = $c['slug'];
    $slugMap[$c['name']] = $cSlug;
    $slugMap[$cSlug] = $cSlug;
    $categories[$cSlug] = [$c['name'], 'fa-circle', (int)$c['cnt']];
  }

  // Populate subcategories into $categories and $slugMap
  foreach ($subcatsQuery as $s) {
    $sSlug = $s['slug'];
    $slugMap[$s['name']] = $sSlug;
    $slugMap[$sSlug] = $sSlug;
    // Map subcategories to the unified list for sidebar pills.
    // If a subcategory slug is shared, sum their counts
    if (!isset($categories[$sSlug])) {
      $categories[$sSlug] = [$s['name'], 'fa-ring', (int)$s['cnt']];
    } else {
      if ($categories[$sSlug][1] === 'fa-ring') {
        $categories[$sSlug][2] += (int)$s['cnt'];
      }
    }
  }

  if (!function_exists('getCatLink')) {
    function getCatLink($cat) {
      global $baseUrl;
      $params = $_GET;
      if ($cat !== '') {
        $params['category'] = $cat;
      } else {
        unset($params['category']);
      }
      $query = http_build_query($params);
      return $baseUrl . 'pages/shop.php' . ($query ? '?' . $query : '');
    }
  }

  // Determine active category slug
  $categorySlug = null;
  if (!empty($category)) {
    $categoryLower = strtolower(trim((string)$category));
    $categorySlug = $slugMap[$categoryLower] ?? $categoryLower;
  }

  $productsDb = catalog_products($categorySlug !== '' ? $categorySlug : null);

  // Optional search filter
  $searchTerm = trim((string)$search);
  if ($searchTerm !== '') {
    $searchLower = mb_strtolower($searchTerm);
    $productsDb = array_values(array_filter($productsDb, function ($p) use ($searchLower) {
      $name = mb_strtolower((string)($p['name'] ?? ''));
      $stone = mb_strtolower((string)($p['stone'] ?? ''));
      $metal = mb_strtolower((string)($p['metal'] ?? ''));
      return (strpos($name, $searchLower) !== false) ||
             (strpos($stone, $searchLower) !== false) ||
             (strpos($metal, $searchLower) !== false);
    }));
  }

  // Filter by Price Range
  $priceRanges = $_GET['price_range'] ?? [];
  if (!is_array($priceRanges)) {
    $priceRanges = $priceRanges !== '' ? [$priceRanges] : [];
  }
  if (!empty($_GET['price'])) {
    $pStr = $_GET['price'];
    if ($pStr === '0-10000') $priceRanges[] = 'under_10k';
    else if ($pStr === '10000-25000') $priceRanges[] = '10k_25k';
    else if ($pStr === '25000-50000') $priceRanges[] = '25k_50k';
    else if ($pStr === '50000-100000') $priceRanges[] = '50k_100k';
    else if ($pStr === '100000+') $priceRanges[] = 'above_100k';
  }
  if (!empty($priceRanges)) {
    $productsDb = array_values(array_filter($productsDb, function ($p) use ($priceRanges) {
      $salePrice = (float)($p['price'] ?? 0);
      foreach ($priceRanges as $range) {
        if ($range === 'under_10k' && $salePrice < 10000) return true;
        if ($range === '10k_25k' && $salePrice >= 10000 && $salePrice <= 25000) return true;
        if ($range === '25k_50k' && $salePrice >= 25000 && $salePrice <= 50000) return true;
        if ($range === '50k_100k' && $salePrice >= 50000 && $salePrice <= 100000) return true;
        if ($range === 'above_100k' && $salePrice > 100000) return true;
      }
      return false;
    }));
  }

  // Filter by Gender
  $gendersInput = $_GET['gender'] ?? [];
  $genders = [];
  if (is_array($gendersInput)) {
    foreach ($gendersInput as $g) {
      $genders[] = strtolower(trim((string)$g));
    }
  } else {
    $g = strtolower(trim((string)$gendersInput));
    if ($g === 'women' || $g === 'female') $genders[] = 'female';
    else if ($g === 'men' || $g === 'male') $genders[] = 'male';
    else if ($g === 'kids') $genders[] = 'kids';
    else if ($g === 'couples' || $g === 'unisex') $genders[] = 'unisex';
  }
  if (!empty($genders)) {
    $productsDb = array_values(array_filter($productsDb, function ($p) use ($genders) {
      $pGender = strtolower(trim((string)($p['gender'] ?? 'unisex')));
      return in_array($pGender, $genders, true) || $pGender === 'unisex';
    }));
  }

  // Filter by Metal (array)
  $metalsInput = $_GET['metal'] ?? [];
  $metals = [];
  if (is_array($metalsInput)) {
    foreach ($metalsInput as $m) {
      $metals[] = strtolower(trim((string)$m));
    }
  } else {
    $m = strtolower(trim((string)$metalsInput));
    if ($m !== '') $metals[] = $m;
  }
  if (!empty($metals)) {
    $productsDb = array_values(array_filter($productsDb, function ($p) use ($metals) {
      $pMetal = strtolower(trim((string)($p['metal'] ?? '')));
      return in_array($pMetal, $metals, true);
    }));
  }

  // Filter by Purity (array)
  $puritiesInput = $_GET['purity'] ?? [];
  $purities = [];
  if (is_array($puritiesInput)) {
    foreach ($puritiesInput as $pur) {
      $purities[] = strtolower(trim((string)$pur));
    }
  } else {
    $pur = strtolower(trim((string)$puritiesInput));
    if ($pur !== '') $purities[] = $pur;
  }
  if (!empty($purities)) {
    $productsDb = array_values(array_filter($productsDb, function ($p) use ($purities) {
      $pPurity = strtolower(trim((string)($p['purity'] ?? '')));
      return in_array($pPurity, $purities, true);
    }));
  }

  // Filter by Stone (array + string fallback)
  $stonesInput = $_GET['stone'] ?? [];
  $stones = [];
  if (is_array($stonesInput)) {
    foreach ($stonesInput as $st) {
      $stones[] = strtolower(trim((string)$st));
    }
  } else {
    $st = strtolower(trim((string)$stonesInput));
    if ($st !== '') $stones[] = $st;
  }
  if (!empty($stones)) {
    $productsDb = array_values(array_filter($productsDb, function ($p) use ($stones) {
      $pStone = strtolower(trim((string)($p['stone'] ?? '')));
      return in_array($pStone, $stones, true);
    }));
  }

  // Filter by Occasion (e.g. Wedding, Dailywear, Festival)
  $occasionFilter = trim((string)($_GET['occasion'] ?? ''));
  if ($occasionFilter !== '') {
    $productsDb = array_values(array_filter($productsDb, function ($p) use ($occasionFilter) {
      return strcasecmp((string)($p['occasion'] ?? ''), $occasionFilter) === 0;
    }));
  }

  // Sort products
  $sort = $_GET['sort'] ?? 'featured';
  if ($sort === 'price_asc') {
    usort($productsDb, function ($a, $b) {
      return (float)($a['price'] ?? 0) <=> (float)($b['price'] ?? 0);
    });
  } else if ($sort === 'price_desc') {
    usort($productsDb, function ($a, $b) {
      return (float)($b['price'] ?? 0) <=> (float)($a['price'] ?? 0);
    });
  } else if ($sort === 'newest') {
    usort($productsDb, function ($a, $b) {
      return (int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0);
    });
  }

  $products = $productsDb;
} catch (Throwable $e) {
  // Keep demo products fallback
}

require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* Important: remove any extra whitespace between header/category navigation and product list */
html, body { margin: 0; padding: 0; }
.main-content, .main-header, header, nav { margin: 0 !important; padding: 0 !important; }
.category-nav, .lux-navbar, #categoryNav { margin: 0 !important; padding: 0 !important; }
.main-content { min-height: 0 !important; }

.shop-page { background: #FAF8F6; padding: 0 0 20px 0; }
.shop-container { max-width: 1400px; margin: 0 auto; padding: 0 20px; }
.shop-layout { display: flex; gap: 30px; margin-top: 0 !important; padding-top: 0 !important; }

.sidebar { width: 320px; flex-shrink: 0; }
.sidebar-content { background: #FAF8F6; position: sticky; top: 100px; }

.filter-card {
  background: #fff;
  border-radius: 24px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.04);
}

.filter-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  cursor: pointer;
}

.filter-title {
  font-size: 15px;
  font-weight: 600;
  color: #222;
  position: relative;
  padding-bottom: 12px;
}

.filter-title::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 40px;
  height: 3px;
  background: #D4AF37;
  border-radius: 2px;
}

.filter-toggle { color: #888; transition: 0.3s; }
.filter-card.collapsed .filter-toggle { transform: rotate(-90deg); }
.filter-card.collapsed .filter-body { display: none; }

.cat-pills { display: flex; flex-wrap: wrap; gap: 10px; }

.cat-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 18px;
  background: #FAF8F6;
  border-radius: 30px;
  font-size: 13px;
  color: #444;
  transition: all 0.3s;
  cursor: pointer;
  text-decoration: none;
}

.cat-pill:hover {
  transform: scale(1.03);
  background: #fff;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.cat-pill.active { background: #6B1E2D; color: #fff; }

.cat-pill .pill-count {
  font-size: 11px;
  background: rgba(107,30,45,0.2);
  padding: 2px 8px;
  border-radius: 10px;
}

.cat-pill.active .pill-count { background: #D4AF37; color: #222; }

.price-list { display: flex; flex-direction: column; gap: 12px; }

.price-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 18px;
  background: #fff;
  border: 2px solid #E5E5E5;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s;
}

.price-item:hover { border-color: #D4AF37; background: #FFFAF5; }
.price-item input { display: none; }

.price-check {
  width: 20px;
  height: 20px;
  border: 2px solid #D9D9D9;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
  color: transparent;
}

.price-item input:checked + .price-check { background: #6B1E2D; border-color: #6B1E2D; color: #fff; }
.price-text { flex: 1; font-size: 13px; color: #444; }
.price-item:hover .price-text { color: #222; }

.gender-chips { display: flex; gap: 10px; }

.gender-chip {
  flex: 1;
  padding: 14px;
  text-align: center;
  background: #fff;
  border: 1px solid #E5E5E5;
  border-radius: 30px;
  font-size: 13px;
  color: #444;
  cursor: pointer;
  transition: all 0.3s;
}

.gender-chip:hover { border-color: #6B1E2D; }
.gender-chip.active { background: #6B1E2D; border-color: #6B1E2D; color: #fff; }

.filter-actions { display: flex; gap: 12px; margin-top: 20px; }

.btn-apply {
  flex: 1;
  height: 54px;
  background: #6B1E2D;
  border: none;
  border-radius: 14px;
  color: #fff;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-apply:hover { background: #D4AF37; color: #222; }

.btn-reset {
  height: 54px;
  padding: 0 24px;
  background: #fff;
  border: 1px solid #D9D9D9;
  border-radius: 14px;
  color: #666;
  font-weight: 500;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-reset:hover { background: #F5F5F5; }

.active-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }

.filter-tag {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: #6B1E2D;
  color: #fff;
  border-radius: 20px;
  font-size: 12px;
}

.filter-tag a { color: #fff; text-decoration: none; opacity: 0.7; }
.filter-tag a:hover { opacity: 1; }

.products-area { flex: 1; min-width: 0; }

.prod-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}

.prod-results { font-size: 15px; color: #666; }
.prod-results strong { color: #222; }

.prod-sort {
  padding: 12px 16px;
  border: 2px solid #E5E5E5;
  border-radius: 10px;
  font-size: 13px;
  color: #444;
  background: #fff;
  cursor: pointer;
}

.products-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }

.product-card {
  background: #fff;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.04);
  transition: all 0.3s;
}

.product-card:hover { transform: translateY(-8px); box-shadow: 0 12px 40px rgba(0,0,0,0.1); }

.product-img {
  height: 220px;
  background: linear-gradient(135deg, #f8f5f0 0%, #FAF8F6 100%);
  position: relative;
  overflow: hidden;
}

.product-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
.product-img:hover img { transform: scale(1.05); }
.product-img:hover .qv-hover-overlay { opacity: 1 !important; background: rgba(0,0,0,0.25) !important; }

.product-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  background: #D4AF37;
  color: #222;
  padding: 6px 12px;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 600;
}

.wish-btn {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 36px;
  height: 36px;
  background: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  cursor: pointer;
  transition: all 0.3s;
  color: #666;
}

.wish-btn:hover { background: #6B1E2D; color: #fff; }

.product-body { padding: 16px; }

.product-cat {
  color: #888;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 6px;
}

.product-title {
  font-family: 'Playfair Display', serif;
  font-size: 15px;
  font-weight: 600;
  margin-bottom: 8px;
  color: #222;
}

.product-title a { color: inherit; text-decoration: none; }
.product-title a:hover { color: #6B1E2D; }

.product-price { font-size: 18px; font-weight: 700; color: #6B1E2D; margin-bottom: 12px; }
.product-price del { font-size: 13px; color: #999; font-weight: 400; margin-left: 8px; }

.add-cart {
  width: 100%;
  padding: 12px;
  background: #6B1E2D;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  font-size: 13px;
}

.add-cart:hover { background: #D4AF37; color: #222; }

@media (max-width: 1200px) { .products-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 900px) {
  .shop-layout { flex-direction: column; }
  .sidebar { width: 100%; }
  .sidebar-content { position: static; }
  .products-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
  .products-grid { gap: 12px; }
  .product-img { height: 150px; }
  .product-body { padding: 12px; }
  .product-title { font-size: 13px; }
  .product-price { font-size: 15px; }
}

/* Layout Toggles */
.btn-layout {
  transition: all 0.3s;
  color: #666;
}
.btn-layout:hover, .btn-layout.active {
  background: #6B1E2D !important;
  color: #fff !important;
  border-color: #6B1E2D !important;
}

/* List layout styling */
.products-grid.list-view {
  grid-template-columns: 1fr !important;
  gap: 20px !important;
}
.products-grid.list-view .product-card {
  display: flex !important;
  flex-direction: row !important;
  align-items: center;
  height: 220px;
  width: 100%;
}
.products-grid.list-view .product-img {
  width: 220px !important;
  height: 220px !important;
  flex-shrink: 0;
}
.products-grid.list-view .product-body {
  flex: 1;
  padding: 24px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
  text-align: left;
}
.products-grid.list-view .product-title {
  font-size: 18px;
  margin-bottom: 8px;
}
.products-grid.list-view .product-price {
  font-size: 20px;
  margin-bottom: 15px;
}
.products-grid.list-view .add-cart {
  width: auto !important;
  padding: 12px 35px;
}
@media (max-width: 768px) {
  .products-grid.list-view .product-card {
    flex-direction: column !important;
    height: auto;
  }
  .products-grid.list-view .product-img {
    width: 100% !important;
    height: 180px !important;
  }
}
</style>

<div class="shop-page" style="padding-top:0;">
  <div class="shop-container">
    <div class="shop-layout" style="margin-top:0;">

      <aside class="sidebar">
        <div class="sidebar-content">

          <?php
          $hasFilters = !empty($_GET['category']) || !empty($_GET['price_range']) || !empty($_GET['gender']);
          if ($hasFilters):
          ?>
          <div class="active-tags" style="margin-bottom: 20px;">
            <?php if (!empty($_GET['category'])): ?>
              <span class="filter-tag">
                <?= h($categories[$_GET['category']][0] ?? $_GET['category']) ?>
                <a href="<?= getCatLink('') ?>"><i class="fas fa-times"></i></a>
              </span>
            <?php endif; ?>

            <?php
            if (!empty($priceRanges)) {
              foreach ($priceRanges as $pr) {
                $lbl = '';
                if ($pr === 'under_10k') $lbl = 'Under ₹10k';
                if ($pr === '10k_25k') $lbl = '₹10k - ₹25k';
                if ($pr === '25k_50k') $lbl = '₹25k - ₹50k';
                if ($pr === '50k_100k') $lbl = '₹50k - ₹100k';
                if ($pr === 'above_100k') $lbl = 'Above ₹100k';
                
                $remParams = $_GET;
                $remParams['price_range'] = array_diff($remParams['price_range'], [$pr]);
                $remLink = 'pages/shop.php?' . http_build_query($remParams);
                echo '<span class="filter-tag">' . h($lbl) . ' <a href="' . h($remLink) . '"><i class="fas fa-times"></i></a></span>';
              }
            }
            ?>

            <?php
            if (!empty($genders)) {
              foreach ($genders as $g) {
                $remParams = $_GET;
                $remParams['gender'] = array_diff($remParams['gender'], [$g]);
                $remLink = 'pages/shop.php?' . http_build_query($remParams);
                echo '<span class="filter-tag">' . h(ucfirst($g)) . ' <a href="' . h($remLink) . '"><i class="fas fa-times"></i></a></span>';
              }
            }
            ?>
          </div>
          <?php endif; ?>

          <form id="filterForm" method="get" action="pages/shop.php">
            <input type="hidden" name="category" value="<?= h($_GET['category'] ?? '') ?>">
            <input type="hidden" name="search" value="<?= h($_GET['search'] ?? '') ?>">
            <input type="hidden" name="sort" value="<?= h($_GET['sort'] ?? 'featured') ?>">

            <div class="filter-card">
              <div class="filter-header">
                <h4 class="filter-title">Categories</h4>
                <i class="fas fa-chevron-up filter-toggle"></i>
              </div>
              <div class="filter-body">
                <div class="cat-pills">
                  <?php foreach ($categories as $cat => $data): ?>
                  <a href="<?= getCatLink($cat) ?>" class="cat-pill <?= ($_GET['category'] ?? '') === $cat ? 'active' : '' ?>">
                    <?= $data[0] ?> <span class="pill-count"><?= $data[2] ?></span>
                  </a>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

            <div class="filter-card">
              <div class="filter-header">
                <h4 class="filter-title">Price Range</h4>
                <i class="fas fa-chevron-up filter-toggle"></i>
              </div>
              <div class="filter-body">
                <div class="price-list">
                  <label class="price-item">
                    <input type="checkbox" name="price_range[]" value="under_10k" <?= in_array('under_10k', $priceRanges) ? 'checked' : '' ?>>
                    <span class="price-check"><i class="fas fa-check"></i></span>
                    <span class="price-text">Under ₹10,000</span>
                  </label>
                  <label class="price-item">
                    <input type="checkbox" name="price_range[]" value="10k_25k" <?= in_array('10k_25k', $priceRanges) ? 'checked' : '' ?>>
                    <span class="price-check"><i class="fas fa-check"></i></span>
                    <span class="price-text">₹10,000 - ₹25,000</span>
                  </label>
                  <label class="price-item">
                    <input type="checkbox" name="price_range[]" value="25k_50k" <?= in_array('25k_50k', $priceRanges) ? 'checked' : '' ?>>
                    <span class="price-check"><i class="fas fa-check"></i></span>
                    <span class="price-text">₹25,000 - ₹50,000</span>
                  </label>
                  <label class="price-item">
                    <input type="checkbox" name="price_range[]" value="50k_100k" <?= in_array('50k_100k', $priceRanges) ? 'checked' : '' ?>>
                    <span class="price-check"><i class="fas fa-check"></i></span>
                    <span class="price-text">₹50,000 - ₹1,00,000</span>
                  </label>
                  <label class="price-item">
                    <input type="checkbox" name="price_range[]" value="above_100k" <?= in_array('above_100k', $priceRanges) ? 'checked' : '' ?>>
                    <span class="price-check"><i class="fas fa-check"></i></span>
                    <span class="price-text">Above ₹1,00,000</span>
                  </label>
                </div>
              </div>
            </div>

            <div class="filter-card">
              <div class="filter-header">
                <h4 class="filter-title">Gender</h4>
                <i class="fas fa-chevron-up filter-toggle"></i>
              </div>
              <div class="filter-body">
                <div class="gender-chips">
                  <label class="gender-chip <?= in_array('female', $genders) ? 'active' : '' ?>">
                    <input type="checkbox" name="gender[]" value="female" <?= in_array('female', $genders) ? 'checked' : '' ?> style="display:none;" onchange="this.parentElement.classList.toggle('active', this.checked)"> Women
                  </label>
                  <label class="gender-chip <?= in_array('male', $genders) ? 'active' : '' ?>">
                    <input type="checkbox" name="gender[]" value="male" <?= in_array('male', $genders) ? 'checked' : '' ?> style="display:none;" onchange="this.parentElement.classList.toggle('active', this.checked)"> Men
                  </label>
                  <label class="gender-chip <?= in_array('unisex', $genders) ? 'active' : '' ?>">
                    <input type="checkbox" name="gender[]" value="unisex" <?= in_array('unisex', $genders) ? 'checked' : '' ?> style="display:none;" onchange="this.parentElement.classList.toggle('active', this.checked)"> Unisex
                  </label>
                </div>
              </div>
            </div>

            <div class="filter-card">
              <div class="filter-header">
                <h4 class="filter-title">Metal Type</h4>
                <i class="fas fa-chevron-up filter-toggle"></i>
              </div>
              <div class="filter-body">
                <div class="price-list">
                  <label class="price-item">
                    <input type="checkbox" name="metal[]" value="gold" <?= in_array('gold', $metals) ? 'checked' : '' ?>>
                    <span class="price-check"><i class="fas fa-check"></i></span>
                    <span class="price-text">Gold</span>
                  </label>
                  <label class="price-item">
                    <input type="checkbox" name="metal[]" value="silver" <?= in_array('silver', $metals) ? 'checked' : '' ?>>
                    <span class="price-check"><i class="fas fa-check"></i></span>
                    <span class="price-text">Silver</span>
                  </label>
                  <label class="price-item">
                    <input type="checkbox" name="metal[]" value="platinum" <?= in_array('platinum', $metals) ? 'checked' : '' ?>>
                    <span class="price-check"><i class="fas fa-check"></i></span>
                    <span class="price-text">Platinum</span>
                  </label>
                </div>
              </div>
            </div>

            <div class="filter-card">
              <div class="filter-header">
                <h4 class="filter-title">Purity</h4>
                <i class="fas fa-chevron-up filter-toggle"></i>
              </div>
              <div class="filter-body">
                <div class="price-list">
                  <label class="price-item">
                    <input type="checkbox" name="purity[]" value="18k" <?= in_array('18k', $purities) ? 'checked' : '' ?>>
                    <span class="price-check"><i class="fas fa-check"></i></span>
                    <span class="price-text">18K</span>
                  </label>
                  <label class="price-item">
                    <input type="checkbox" name="purity[]" value="22k" <?= in_array('22k', $purities) ? 'checked' : '' ?>>
                    <span class="price-check"><i class="fas fa-check"></i></span>
                    <span class="price-text">22K</span>
                  </label>
                  <label class="price-item">
                    <input type="checkbox" name="purity[]" value="24k" <?= in_array('24k', $purities) ? 'checked' : '' ?>>
                    <span class="price-check"><i class="fas fa-check"></i></span>
                    <span class="price-text">24K</span>
                  </label>
                </div>
              </div>
            </div>

            <div class="filter-card">
              <div class="filter-header">
                <h4 class="filter-title">Stones</h4>
                <i class="fas fa-chevron-up filter-toggle"></i>
              </div>
              <div class="filter-body">
                <div class="price-list">
                  <label class="price-item">
                    <input type="checkbox" name="stone[]" value="diamond" <?= in_array('diamond', $stones) ? 'checked' : '' ?>>
                    <span class="price-check"><i class="fas fa-check"></i></span>
                    <span class="price-text">Diamond</span>
                  </label>
                  <label class="price-item">
                    <input type="checkbox" name="stone[]" value="solitaire" <?= in_array('solitaire', $stones) ? 'checked' : '' ?>>
                    <span class="price-check"><i class="fas fa-check"></i></span>
                    <span class="price-text">Solitaire</span>
                  </label>
                  <label class="price-item">
                    <input type="checkbox" name="stone[]" value="pearl" <?= in_array('pearl', $stones) ? 'checked' : '' ?>>
                    <span class="price-check"><i class="fas fa-check"></i></span>
                    <span class="price-text">Pearl</span>
                  </label>
                  <label class="price-item">
                    <input type="checkbox" name="stone[]" value="none" <?= in_array('none', $stones) ? 'checked' : '' ?>>
                    <span class="price-check"><i class="fas fa-check"></i></span>
                    <span class="price-text">None (Plain)</span>
                  </label>
                </div>
              </div>
            </div>

            <div class="filter-actions">
              <button type="submit" class="btn-apply">Apply Filters</button>
              <button type="button" class="btn-reset" onclick="window.location.href='pages/shop.php<?= !empty($search) ? '?search=' . urlencode($search) : '' ?>';">Reset</button>
            </div>

          </form>
        </div>
      </aside>

      <div class="products-area">
        <div class="prod-header">
          <div class="prod-results">
            Showing <strong id="lblProductCount"><?php echo count($products); ?></strong> products
            <?php if (!empty($_GET['category'])): ?>
              in <strong><?= htmlspecialchars($_GET['category']) ?></strong>
            <?php endif; ?>
            <?php if (!empty($_GET['search'])): ?>
              for <strong>"<?= htmlspecialchars($_GET['search']) ?>"</strong>
            <?php endif; ?>
          </div>
          <div style="display:flex; align-items:center; gap:12px;">
            <select class="prod-sort" id="shopSort" style="margin-bottom:0; height:42px;">
              <option value="featured" <?= $sort === 'featured' ? 'selected' : '' ?>>Sort by: Featured</option>
              <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
              <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
              <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
            </select>
            <div class="layout-toggles" style="display:flex; gap:6px;">
              <button type="button" class="btn-layout active" data-layout="grid" title="Grid View" style="background:#fff; border:2px solid #E5E5E5; border-radius:8px; width:42px; height:42px; display:flex; align-items:center; justify-content:center; cursor:pointer;"><i class="fas fa-th"></i></button>
              <button type="button" class="btn-layout" data-layout="list" title="List View" style="background:#fff; border:2px solid #E5E5E5; border-radius:8px; width:42px; height:42px; display:flex; align-items:center; justify-content:center; cursor:pointer;"><i class="fas fa-list"></i></button>
            </div>
          </div>
        </div>

        <div class="products-grid">

          <?php if (empty($products)): ?>
            <div class="no-products">
              <i class="fas fa-gem" style="font-size:48px;color:#ccc;margin-bottom:16px;"></i>
              <p>No products found</p>
              <?php if (!empty($search)): ?>
                <p style="font-size:13px;color:#888;">Try a different search term</p>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <?php foreach ($products as $p): ?>
              <?php
                $basePrice = base_price_now($p);
                $salePrice = price_now($p);
                $discount = (float)($p['discount_percent'] ?? 0);
              ?>
              <div class="product-card" data-product-id="<?= (int)$p['id'] ?>">
                <div class="product-img" style="position:relative; cursor:pointer;" data-quick-view data-product-id="<?= (int)$p['id'] ?>">
                  <?php
                  $imgUrl = $p['primary_image_url'] ?? $p['primary_image'] ?? '';
                  if (empty($imgUrl)) {
                    $images = ['Rings.png','Earrings.png','Necklaces.png','Bangles.png','Bracelets.png','Pendants.png'];
                    $imgUrl = asset('images/' . $images[($p['id'] ?? 0) % count($images)]);
                  } else {
                    $imgUrl = asset($imgUrl);
                  }
                  ?>
                  <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                  <span class="product-badge"><?= $discount > 0 ? '-' . $discount . '%' : 'New' ?></span>
                  <button class="wish-btn" data-wishlist-toggle data-product-id="<?= (int)$p['id'] ?>" title="Wishlist" onclick="event.stopPropagation();">
                    <i class="<?= in_array((string)$p['id'], $wishlistProductIds) ? 'fas' : 'far' ?> fa-heart"></i>
                  </button>
                  <!-- Quick View hover overlay -->
                  <div class="qv-hover-overlay" style="position:absolute; inset:0; background:rgba(0,0,0,0); display:flex; align-items:center; justify-content:center; opacity:0; transition:all 0.3s ease; pointer-events:none;">
                    <span style="background:rgba(255,255,255,0.92); color:#7A1E3A; font-size:12px; font-weight:600; padding:10px 20px; border-radius:30px; letter-spacing:0.5px; font-family:'Poppins',sans-serif; box-shadow:0 4px 16px rgba(0,0,0,0.12);">
                      <i class="far fa-eye me-2"></i>Quick View
                    </span>
                  </div>
                </div>
                <div class="product-body">
                  <p class="product-cat"><?= htmlspecialchars($p['metal'] ?? 'Gold') ?> | <?= htmlspecialchars($p['stone'] ?? '') ?></p>
                  <h4 class="product-title">
                    <a href="#" data-quick-view data-product-id="<?= (int)$p['id'] ?>" onclick="event.preventDefault();" style="color:inherit;"><?= htmlspecialchars($p['name']) ?></a>
                  </h4>
                  <div class="product-price">
                    &#8377;<?= number_format($salePrice) ?>
                    <?php if ($discount > 0): ?>
                      <del>&#8377;<?= number_format($basePrice) ?></del>
                    <?php endif; ?>
                  </div>
                  <button type="button" class="add-cart" data-cart="<?= (int)$p['id'] ?>">Add to Cart</button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <div class="load-more-container" style="text-align:center; margin-top:40px; margin-bottom:20px;">
          <button type="button" id="btnLoadMore" class="btn btn-gold" style="display:none; color:#222; font-family:'Poppins', sans-serif;">Load More</button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script src="<?= asset('js/cart-ui.js') ?>?v=<?= time(); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const filterForm = document.getElementById('filterForm');
  const productsGrid = document.querySelector('.products-grid');
  const btnLoadMore = document.getElementById('btnLoadMore');
  const lblProductCount = document.getElementById('lblProductCount');
  const shopSort = document.getElementById('shopSort');
  
  let currentPage = 1;
  let totalPages = 1;
  const itemsLimit = 12;
  
  // Set initial state from current URL
  const initParams = new URLSearchParams(window.location.search);
  if (initParams.has('sort')) {
    if (shopSort) shopSort.value = initParams.get('sort');
  }

  // Grid/List Layout Toggles
  const btnLayouts = document.querySelectorAll('.btn-layout');
  let currentLayout = localStorage.getItem('shop-layout') || 'grid';
  
  function applyLayout(layout) {
    currentLayout = layout;
    localStorage.setItem('shop-layout', layout);
    btnLayouts.forEach(btn => {
      btn.classList.toggle('active', btn.dataset.layout === layout);
    });
    if (productsGrid) {
      if (layout === 'list') {
        productsGrid.classList.add('list-view');
      } else {
        productsGrid.classList.remove('list-view');
      }
    }
  }
  
  btnLayouts.forEach(btn => {
    btn.addEventListener('click', function() {
      applyLayout(this.dataset.layout);
    });
  });
  
  applyLayout(currentLayout);

  // Function to serialize form and trigger AJAX load
  function loadProducts(append = false) {
    if (!append) {
      currentPage = 1;
    }
    
    // Build query params
    const formData = new FormData(filterForm);
    const params = new URLSearchParams();
    
    for (const [key, value] of formData.entries()) {
      if (value !== '') {
        // Handle PHP array variables
        if (key.endsWith('[]')) {
          params.append(key.slice(0, -2), value);
        } else {
          params.append(key, value);
        }
      }
    }
    
    // Add page, limit, and sort
    params.set('page', currentPage.toString());
    params.set('limit', itemsLimit.toString());
    if (shopSort) {
      params.set('sort', shopSort.value);
    }
    
    // Sync browser URL
    const browserParams = new URLSearchParams(params);
    // Cleanup internal page / limit for clean browser URLs
    browserParams.delete('page');
    browserParams.delete('limit');
    history.pushState(null, '', 'pages/shop.php?' + browserParams.toString());
    
    // Fetch from filter API
    const apiUrl = '/visnavi jwallers/api/products-filter.php?' + params.toString();
    
    // Add loading spinner or opacity
    productsGrid.style.opacity = '0.6';
    
    return fetch(apiUrl)
      .then(res => res.json())
      .then(data => {
        productsGrid.style.opacity = '1';
        totalPages = data.pages || 1;
        
        if (lblProductCount) {
          lblProductCount.textContent = data.total;
        }
        
        let html = '';
        if (data.items && data.items.length > 0) {
          data.items.forEach(p => {
            const salePrice = parseFloat(p.price);
            const basePrice = parseFloat(p.base_price);
            const discount = parseFloat(p.discount_percent);
            const badge = discount > 0 ? `-${discount}%` : 'New';
            
            let imgUrl = p.image_url;
            if (!imgUrl) {
              const defaultImages = ['Rings.png','Earrings.png','Necklaces.png','Bangles.png','Bracelets.png','Pendants.png'];
              imgUrl = 'assets/images/' + defaultImages[parseInt(p.id) % defaultImages.length];
            }
            
            const isWish = document.querySelector(`.wish-btn[data-product-id="${p.id}"] i.fas`) !== null;
            
            html += `
              <div class="product-card">
                <div class="product-img">
                  <img src="/visnavi jwallers/${imgUrl}" alt="${escapeHtml(p.name)}">
                  <span class="product-badge">${badge}</span>
                  <button class="wish-btn" data-wishlist-toggle data-product-id="${p.id}" title="Wishlist">
                    <i class="${isWish ? 'fas' : 'far'} fa-heart"></i>
                  </button>
                  <button class="quick-view-btn" data-quick-view data-product-id="${p.id}" title="Quick View" style="position: absolute; top: 54px; right: 12px; width: 36px; height: 36px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1); cursor: pointer; transition: all 0.3s; color: #666; border:none; z-index:2;">
                    <i class="far fa-eye"></i>
                  </button>
                </div>
                <div class="product-body">
                  <p class="product-cat">${escapeHtml(p.metal)} | ${escapeHtml(p.stone)}</p>
                  <h4 class="product-title">
                    <a href="/visnavi jwallers/pages/product.php?id=${p.id}">${escapeHtml(p.name)}</a>
                  </h4>
                  <div class="product-price">
                    ₹${salePrice.toLocaleString('en-IN')}
                    ${discount > 0 ? `<del>₹${basePrice.toLocaleString('en-IN')}</del>` : ''}
                  </div>
                  <button type="button" class="add-cart" data-cart-add data-product-id="${p.id}">Add to Cart</button>
                </div>
              </div>
            `;
          });
        } else {
          if (!append) {
            html = `
              <div class="no-products" style="grid-column: 1/-1; text-align:center; padding:50px 20px; width: 100%;">
                <i class="fas fa-gem" style="font-size:48px;color:#ccc;margin-bottom:16px;"></i>
                <p>No products found</p>
              </div>
            `;
          }
        }
        
        if (append) {
          productsGrid.insertAdjacentHTML('beforeend', html);
        } else {
          productsGrid.innerHTML = html;
        }
        
        // Setup wishlist and cart events on new nodes
        if (window.initializeCartEvents) {
          window.initializeCartEvents();
        }
        
        // Show/hide load more
        if (currentPage < totalPages) {
          btnLoadMore.style.display = 'inline-block';
        } else {
          btnLoadMore.style.display = 'none';
        }
      })
      .catch(err => {
        productsGrid.style.opacity = '1';
        console.error('Error loading products:', err);
      });
  }
  
  function escapeHtml(text) {
    if (!text) return '';
    return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }
  
  // Set up triggers
  if (filterForm) {
    filterForm.querySelectorAll('input[type="checkbox"]').forEach(cb => {
      cb.addEventListener('change', function() {
        loadProducts(false);
      });
    });
    
    filterForm.addEventListener('submit', function(e) {
      e.preventDefault();
      loadProducts(false);
    });
  }
  
  if (shopSort) {
    shopSort.addEventListener('change', function() {
      const hiddenSort = filterForm.querySelector('input[name="sort"]');
      if (hiddenSort) hiddenSort.value = this.value;
      loadProducts(false);
    });
  }
  
  document.querySelectorAll('.cat-pill').forEach(pill => {
    pill.addEventListener('click', function(e) {
      e.preventDefault();
      
      document.querySelectorAll('.cat-pill').forEach(p => p.classList.remove('active'));
      this.classList.add('active');
      
      const url = new URL(this.href, window.location.origin);
      const category = url.searchParams.get('category') || '';
      
      const hiddenCat = filterForm.querySelector('input[name="category"]');
      if (hiddenCat) hiddenCat.value = category;
      
      loadProducts(false);
    });
  });

  let isScrollingLoading = false;
  const infiniteScrollObserver = new IntersectionObserver((entries) => {
    const entry = entries[0];
    if (entry.isIntersecting && currentPage < totalPages && !isScrollingLoading) {
      isScrollingLoading = true;
      btnLoadMore.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
      btnLoadMore.style.display = 'inline-block';
      currentPage++;
      loadProducts(true).finally(() => {
        isScrollingLoading = false;
        btnLoadMore.innerHTML = 'Load More';
      });
    }
  }, {
    rootMargin: '200px',
  });

  if (btnLoadMore) {
    infiniteScrollObserver.observe(btnLoadMore);
    
    // Keep click event as fallback
    btnLoadMore.addEventListener('click', function() {
      if (currentPage < totalPages && !isScrollingLoading) {
        isScrollingLoading = true;
        btnLoadMore.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        currentPage++;
        loadProducts(true).finally(() => {
          isScrollingLoading = false;
          btnLoadMore.innerHTML = 'Load More';
        });
      }
    });
  }

  // ── Global Add to Cart click delegation for Shop ───────────────────
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-cart]') || e.target.closest('[data-cart-add]');
    if(!btn) return;
    e.preventDefault();
    const pid = btn.getAttribute('data-cart') || btn.getAttribute('data-product-id');
    if(!pid) return;

    const orig = btn.textContent;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;

    const fd = new FormData();
    fd.append('action', 'add');
    fd.append('product_id', pid);
    fd.append('qty', '1');

    fetch('api/cart-toggle.php', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(res => res.json())
      .then(data => {
        btn.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => {
          btn.innerHTML = orig;
          btn.disabled = false;
        }, 1500);

        // Update top-bar cart count
        const cc = document.getElementById('cartCount');
        if (cc && data.cart_count !== undefined) cc.textContent = data.cart_count;

        // Trigger cart drawer refresh and open it
        if(window.openCartDrawer) {
          window.openCartDrawer();
        } else {
          if (window.refreshCartDrawer) window.refreshCartDrawer();
          const drawer = document.getElementById('cartDrawer');
          if (drawer) drawer.classList.add('open');
        }
      })
      .catch(() => {
        btn.innerHTML = orig;
        btn.disabled = false;
      });
  });

  // Trigger paginated load on startup to match AJAX limit
  loadProducts(false);
});
</script>

