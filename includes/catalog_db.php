<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * DB-backed catalog helpers.
 * This replaces the current hardcoded includes/products.php model.
 */

function ensure_catalog_seeded(PDO $pdo): void {
  // First, ensure products table has all required columns
  ensure_products_schema($pdo);

  // Ensure users table exists or upgrade schema if needed
  $usersExists = true;
  try {
    $pdo->query("SELECT 1 FROM users LIMIT 1");
  } catch (Throwable $e) {
    $usersExists = false;
  }

  if (!$usersExists) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
      id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
      role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
      full_name VARCHAR(150) NOT NULL,
      email VARCHAR(190) NOT NULL UNIQUE,
      phone VARCHAR(30) NULL,
      password_hash VARCHAR(255) NOT NULL,
      gender ENUM('female','male','unisex') NULL,
      otp_code_hash VARCHAR(255) NULL,
      otp_expires_at DATETIME NULL,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  } else {
    // Upgrade users schema if needed (adding role and full_name)
    try {
      $pdo->query("SELECT role FROM users LIMIT 1");
    } catch (Throwable $e) {
      try {
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('customer','admin') NOT NULL DEFAULT 'customer' AFTER id");
      } catch (Throwable $e2) {}
    }
    try {
      $pdo->query("SELECT full_name FROM users LIMIT 1");
    } catch (Throwable $e) {
      try {
        $hasName = false;
        try {
          $pdo->query("SELECT name FROM users LIMIT 1");
          $hasName = true;
        } catch (Throwable $eName) {}
        if ($hasName) {
          $pdo->exec("ALTER TABLE users CHANGE COLUMN name full_name VARCHAR(150) NULL");
        } else {
          $pdo->exec("ALTER TABLE users ADD COLUMN full_name VARCHAR(150) NOT NULL AFTER role");
        }
      } catch (Throwable $e2) {}
    }
  }

  // Ensure default users are seeded if table is empty
  $userCount = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
  if ($userCount === 0) {
    $stmtUser = $pdo->prepare("INSERT INTO users (role, full_name, email, password_hash, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmtUser->execute(['admin', 'Admin User', 'admin@visnavi.com', password_hash('admin123', PASSWORD_DEFAULT)]);
    $stmtUser->execute(['customer', 'Mahi Sonar', 'customer@visnavi.com', password_hash('customer123', PASSWORD_DEFAULT)]);
  }

  // Create categories table if missing
  try {
    $pdo->query('SELECT 1 FROM categories LIMIT 1');
  } catch (Throwable $e) {
    $pdo->exec('CREATE TABLE IF NOT EXISTS categories (
      id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
      name VARCHAR(120) NOT NULL,
      slug VARCHAR(140) NOT NULL UNIQUE,
      gender ENUM("female","male","unisex","all") NOT NULL DEFAULT "all",
      display_order INT NOT NULL DEFAULT 0,
      is_active TINYINT(1) NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
  }

  // Check if categories exist
  $cnt = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
  if ($cnt > 0) {
    return; // Already seeded
  }

  // Insert categories
  $categoriesToSeed = [
    ['All Jewellery', 'all-jewellery', 'all', 0],
    ['Gold', 'gold', 'all', 1],
    ['Diamond', 'diamond', 'all', 2],
    ['Silver', 'silver', 'all', 3],
    ['Earrings', 'earrings', 'all', 4],
    ['Rings', 'rings', 'all', 5],
    ['Necklaces', 'necklaces', 'all', 6],
    ['Bangles', 'bangles', 'all', 7],
    ['Bracelets', 'bracelets', 'all', 8],
    ['Wedding', 'wedding', 'all', 9],
    ['Gemstone', 'gemstone', 'all', 10],
    ['Gifting', 'gifting', 'all', 11],
    ['Daily Wear', 'daily-wear', 'all', 12]
  ];

  $stmtCat = $pdo->prepare("INSERT INTO categories (name, slug, gender, display_order, is_active) VALUES (?, ?, ?, ?, 1)");
  foreach ($categoriesToSeed as $cat) {
    $stmtCat->execute([$cat[0], $cat[1], $cat[2], $cat[3]]);
  }

  // Create subcategories table if missing
  try {
    $pdo->query('SELECT 1 FROM subcategories LIMIT 1');
  } catch (Throwable $e) {
    $pdo->exec('CREATE TABLE IF NOT EXISTS subcategories (
      id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
      category_id BIGINT UNSIGNED NOT NULL,
      name VARCHAR(120) NOT NULL,
      slug VARCHAR(140) NOT NULL UNIQUE,
      display_order INT NOT NULL DEFAULT 0,
      is_active TINYINT(1) NOT NULL DEFAULT 1,
      FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
  }

  // Create product_images table if missing
  try {
    $pdo->query('SELECT 1 FROM product_images LIMIT 1');
  } catch (Throwable $e) {
    $pdo->exec('CREATE TABLE IF NOT EXISTS product_images (
      id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
      product_id BIGINT UNSIGNED NOT NULL,
      image_url VARCHAR(500) NOT NULL,
      alt_text VARCHAR(300) NULL,
      is_primary TINYINT(1) NOT NULL DEFAULT 0,
      display_order INT NOT NULL DEFAULT 0,
      FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
  }

  // Dynamically scan folders to seed products
  $imagesDir = __DIR__ . '/../assets/images';
  if (!is_dir($imagesDir)) {
    return; // Safety fallback if directory doesn't exist
  }

  // Get categories map
  $catMap = [];
  foreach ($pdo->query("SELECT id, slug FROM categories")->fetchAll() as $row) {
    $catMap[$row['slug']] = (int)$row['id'];
  }

  // Recursive folder scanner
  $allPaths = [];
  $scan = function($dir) use (&$allPaths, &$scan) {
    $files = scandir($dir);
    foreach ($files as $value) {
      if ($value === '.' || $value === '..') continue;
      $path = $dir . DIRECTORY_SEPARATOR . $value;
      if (is_dir($path)) {
        $scan($path);
      } else {
        $allPaths[] = $path;
      }
    }
  };
  $scan($imagesDir);

  $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'jfif', 'avif'];
  $productImages = [];
  foreach ($allPaths as $path) {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (in_array($ext, $imageExtensions)) {
      $productImages[] = $path;
    }
  }

  $counters = [];
  $insertedCount = 0;
  $slugs = [];

  $stmtProd = $pdo->prepare("INSERT INTO products (
    category_id, name, slug, sku, base_price, discount_percent, stock, gender, metal, stone, occasion, collection, short_description, description, is_active, display_order
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");

  $stmtImg = $pdo->prepare("INSERT INTO product_images (product_id, image_url, alt_text, is_primary, display_order) VALUES (?, ?, ?, 1, 0)");

  foreach ($productImages as $imgPath) {
    $cleanPath = str_replace('\\', '/', $imgPath);
    $rootPos = strpos($cleanPath, 'assets/images');
    if ($rootPos === false) continue;
    $relativeWebPath = substr($cleanPath, $rootPos);

    $parts = explode('/', $relativeWebPath);
    $parentCat = isset($parts[2]) ? strtolower($parts[2]) : '';
    $subCat = isset($parts[3]) ? strtolower($parts[3]) : '';
    $filename = basename($imgPath);

    $folderNameForGeneric = ($subCat !== '' && !is_file($imagesDir.'/'.$parentCat.'/'.$subCat)) ? $subCat : $parentCat;
    
    // Clean name
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $isGeneric = (stripos($name, 'WhatsApp') !== false || stripos($name, 'download') !== false || preg_match('/^[0-9_\-\(\)\s]+$/', $name));
    if ($isGeneric) {
      $key = strtolower($folderNameForGeneric);
      if (!isset($counters[$key])) $counters[$key] = 1;
      $count = $counters[$key]++;
      $prodName = "Exclusive " . ucwords(str_replace(['-', '_'], ' ', $folderNameForGeneric)) . " Classique #$count";
    } else {
      $name = preg_replace('/#\w+/', '', $name);
      $name = str_replace(['_', '-'], ' ', $name);
      $name = preg_replace('/\s+/', ' ', $name);
      $name = preg_replace('/\+\d+/', '', $name);
      $prodName = ucwords(strtolower(trim($name)));
      if (strlen($prodName) > 60) {
        $prodName = substr($prodName, 0, 57) . '...';
      }
    }

    $metal = 'Gold';
    $stone = null;
    $occasion = 'Everyday';
    $categorySlug = 'all-jewellery';

    if (stripos($relativeWebPath, 'silver') !== false) {
      $metal = 'Silver';
    } else if (stripos($relativeWebPath, 'diamond') !== false) {
      $metal = 'White Gold';
      $stone = 'Diamond';
    }

    $fullPathLower = strtolower($relativeWebPath);
    if (strpos($fullPathLower, 'ring') !== false || strpos($fullPathLower, 'challa') !== false) {
      $categorySlug = 'rings';
    } else if (strpos($fullPathLower, 'earing') !== false || strpos($fullPathLower, 'earings') !== false || strpos($fullPathLower, 'tops') !== false) {
      $categorySlug = 'earrings';
    } else if (strpos($fullPathLower, 'neklace') !== false || strpos($fullPathLower, 'necklace') !== false || strpos($fullPathLower, 'pendant') !== false || strpos($fullPathLower, 'manglsutr') !== false || strpos($fullPathLower, 'chain') !== false) {
      $categorySlug = 'necklaces';
    } else if (strpos($fullPathLower, 'braclet') !== false || strpos($fullPathLower, 'bracelet') !== false) {
      $categorySlug = 'bracelets';
    } else if (strpos($fullPathLower, 'bangle') !== false || strpos($fullPathLower, 'bangles') !== false) {
      $categorySlug = 'bangles';
    } else if (strpos($fullPathLower, 'kids') !== false) {
      $categorySlug = 'daily-wear';
      $occasion = 'Kids';
    } else if (strpos($fullPathLower, 'payal') !== false) {
      $categorySlug = 'silver';
    } else if (strpos($fullPathLower, 'gifting') !== false) {
      $categorySlug = 'gifting';
      $occasion = 'Gifting';
    } else if (strpos($fullPathLower, 'daliywear') !== false || strpos($fullPathLower, 'dailywear') !== false) {
      $categorySlug = 'daily-wear';
    } else {
      if ($metal === 'Silver') $categorySlug = 'silver';
      else if ($stone === 'Diamond') $categorySlug = 'diamond';
      else $categorySlug = 'gold';
    }

    $basePrice = 25000;
    if ($metal === 'Silver') {
      if ($categorySlug === 'rings') $basePrice = rand(15, 35) * 100;
      else if ($categorySlug === 'earrings') $basePrice = rand(10, 30) * 100;
      else if ($categorySlug === 'bracelets') $basePrice = rand(25, 65) * 100;
      else if ($categorySlug === 'necklaces') $basePrice = rand(45, 95) * 100;
      else $basePrice = rand(15, 75) * 100;
    } else if ($stone === 'Diamond') {
      if ($categorySlug === 'rings') $basePrice = rand(45, 125) * 1000;
      else if ($categorySlug === 'earrings') $basePrice = rand(35, 95) * 1000;
      else if ($categorySlug === 'necklaces') $basePrice = rand(110, 350) * 1000;
      else $basePrice = rand(50, 180) * 1000;
    } else {
      if ($categorySlug === 'rings') $basePrice = rand(25, 65) * 1000;
      else if ($categorySlug === 'earrings') $basePrice = rand(20, 55) * 1000;
      else if ($categorySlug === 'necklaces') $basePrice = rand(80, 260) * 1000;
      else if ($categorySlug === 'bangles') $basePrice = rand(40, 95) * 1000;
      else $basePrice = rand(30, 85) * 1000;
    }

    $discount = (rand(1, 10) > 7) ? (rand(1, 3) * 5) : 0;
    $stock = rand(5, 45);
    $gender = (strpos($fullPathLower, 'men') !== false) ? 'male' : ((strpos($fullPathLower, 'kids') !== false) ? 'unisex' : 'female');

    $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $prodName)));
    $slug = $baseSlug;
    $suffix = 1;
    while (in_array($slug, $slugs)) {
      $slug = $baseSlug . '-' . $suffix++;
    }
    $slugs[] = $slug;

    $sku = 'SKU-' . strtoupper(substr($categorySlug, 0, 3)) . '-' . strtoupper(substr($metal, 0, 3)) . '-' . str_pad((string)($insertedCount + 1), 4, '0', STR_PAD_LEFT);
    $catId = $catMap[$categorySlug] ?? $catMap['all-jewellery'];
    $shortDesc = "Premium quality handcrafted $metal " . str_replace('-', ' ', $categorySlug) . ".";
    $longDesc = "Beautifully finished $metal " . str_replace('-', ' ', $categorySlug) . " designed by Vaishnavi Jewellers. Certified purity and lifetime exchange guarantee included.";
    $displayOrder = $insertedCount + 1;

    $stmtProd->execute([
      $catId, $prodName, $slug, $sku, $basePrice, $discount, $stock, $gender, $metal, $stone, $occasion, 'Classic', $shortDesc, $longDesc, $displayOrder
    ]);

    $productId = $pdo->lastInsertId();
    $stmtImg->execute([$productId, $relativeWebPath, $prodName]);
    $insertedCount++;
  }
}

function ensure_products_schema(PDO $pdo): void {
  // Check if products table exists
  $tableExists = true;
  try {
    $pdo->query('SELECT 1 FROM products LIMIT 1');
  } catch (Throwable $e) {
    $tableExists = false;
  }

  if (!$tableExists) {
    // Create fresh products table with full schema
    $pdo->exec('CREATE TABLE IF NOT EXISTS products (
      id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
      category_id BIGINT UNSIGNED NULL,
      subcategory_id BIGINT UNSIGNED NULL,
      name VARCHAR(200) NOT NULL,
      slug VARCHAR(240) NOT NULL UNIQUE,
      sku VARCHAR(80) NOT NULL UNIQUE,
      base_price DECIMAL(12,2) NOT NULL,
      price_type ENUM("fixed", "dynamic") NOT NULL DEFAULT "fixed",
      purity ENUM("24k", "22k", "18k", "silver") NULL,
      making_charge_percent DECIMAL(5,2) NOT NULL DEFAULT 8.00,
      discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
      stock INT NOT NULL DEFAULT 100,
      gender ENUM("female","male","unisex") NOT NULL DEFAULT "unisex",
      occasion VARCHAR(80) NULL,
      metal VARCHAR(80) NULL,
      stone VARCHAR(80) NULL,
      collection VARCHAR(80) NULL,
      weight_grams DECIMAL(8,2) NULL,
      short_description VARCHAR(300) NULL,
      description TEXT NULL,
      rating_avg DECIMAL(3,2) NOT NULL DEFAULT 0,
      rating_count INT NOT NULL DEFAULT 0,
      is_featured TINYINT(1) NOT NULL DEFAULT 0,
      is_active TINYINT(1) NOT NULL DEFAULT 1,
      display_order INT NOT NULL DEFAULT 0,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    return;
  }

  // Table exists - ensure all required columns are present
  $requiredColumns = [
    'subcategory_id' => 'BIGINT UNSIGNED NULL AFTER category_id',
    'base_price' => 'DECIMAL(12,2) NOT NULL DEFAULT 0',
    'price_type' => "ENUM('fixed', 'dynamic') NOT NULL DEFAULT 'fixed' AFTER base_price",
    'purity' => "ENUM('24k', '22k', '18k', 'silver') NULL AFTER price_type",
    'making_charge_percent' => "DECIMAL(5,2) NOT NULL DEFAULT 8.00 AFTER purity",
    'discount_percent' => 'DECIMAL(5,2) NOT NULL DEFAULT 0',
    'stock' => 'INT NOT NULL DEFAULT 100',
    'gender' => 'ENUM("female","male","unisex") NOT NULL DEFAULT "unisex"',
    'occasion' => 'VARCHAR(80) NULL',
    'metal' => 'VARCHAR(80) NULL',
    'stone' => 'VARCHAR(80) NULL',
    'collection' => 'VARCHAR(80) NULL',
    'weight_grams' => 'DECIMAL(8,2) NULL',
    'short_description' => 'VARCHAR(300) NULL',
    'description' => 'TEXT NULL',
    'rating_avg' => 'DECIMAL(3,2) NOT NULL DEFAULT 0',
    'rating_count' => 'INT NOT NULL DEFAULT 0',
    'is_featured' => 'TINYINT(1) NOT NULL DEFAULT 0',
    'is_active' => 'TINYINT(1) NOT NULL DEFAULT 1',
    'display_order' => 'INT NOT NULL DEFAULT 0',
    'created_at' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
    'updated_at' => 'TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP',
  ];

  foreach ($requiredColumns as $col => $def) {
    try {
      $pdo->query("SELECT $col FROM products LIMIT 1");
    } catch (Throwable $e) {
      try {
        $pdo->exec("ALTER TABLE products ADD COLUMN $col $def");
      } catch (Throwable $e2) {
        // Ignore column add errors
      }
    }
  }
}

function catalog_categories(): array {
  $pdo = db();
  ensure_catalog_seeded($pdo);
  $stmt = $pdo->query("SELECT name FROM categories WHERE is_active=1 ORDER BY display_order ASC, id ASC");
  return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
}

function catalog_products(?string $categorySlug = null): array {
  $pdo = db();
  ensure_catalog_seeded($pdo);

  // Base query for listing products with primary image.
  $sql = "
    SELECT
      p.id,
      p.name,
      p.slug,
      p.sku,
      p.base_price AS price,
      p.price_type,
      p.purity,
      p.making_charge_percent,
      p.weight_grams,
      p.discount_percent,
      p.category_id,
      c.name AS category_name,
      p.subcategory_id,
      s.name AS subcategory_name,
      p.metal,
      p.description,
      p.short_description,
      p.collection,
      p.gender,
      p.occasion,
      p.stone,
      p.rating_avg,
      p.rating_count,
      (
        SELECT pi.image_url
        FROM product_images pi
        WHERE pi.product_id = p.id
        ORDER BY pi.is_primary DESC, pi.display_order ASC, pi.id ASC
        LIMIT 1
      ) AS primary_image_url
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN subcategories s ON s.id = p.subcategory_id
    WHERE p.is_active=1
  ";

  $params = [];
  if ($categorySlug !== null && $categorySlug !== '') {
    // Check if the slug belongs to a main category
    $stmtCheck = $pdo->prepare("SELECT 1 FROM categories WHERE slug = ? LIMIT 1");
    $stmtCheck->execute([$categorySlug]);
    $isMainCategory = (bool)$stmtCheck->fetchColumn();

    if ($isMainCategory) {
      $sql .= " AND c.slug = :cat_slug";
      $params[':cat_slug'] = $categorySlug;
    } else {
      // Treat as subcategory slug
      $sql .= " AND s.slug = :sub_slug";
      $params[':sub_slug'] = $categorySlug;
    }
  }

  $sql .= " ORDER BY p.display_order ASC, p.created_at DESC LIMIT 200";


  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);

  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $products = [];
  foreach ($rows as $r) {
    $discount = (float)$r['discount_percent'];
    $price = (float)$r['price'];
    $final = $price;
    if ($discount > 0) {
      $final = $price * (1 - ($discount / 100));
    }

    $products[] = [
      'id' => (string)$r['id'],
      'name' => (string)$r['name'],
      'slug' => (string)$r['slug'],
      'sku' => (string)$r['sku'],
      'category' => (string)($r['category_name'] ?? ''),
      'metal' => (string)($r['metal'] ?? ''),
      'description' => (string)($r['short_description'] ?? $r['description'] ?? ''),
      'price' => (float)$final,
      'discount_percent' => (float)$discount,
      'rating_avg' => (float)$r['rating_avg'],
      'rating_count' => (int)$r['rating_count'],
      'collection' => $r['collection'] ?? null,
      'gender' => $r['gender'] ?? null,
      'occasion' => $r['occasion'] ?? null,
      'stone' => $r['stone'] ?? null,
      'primary_image_url' => $r['primary_image_url'] ? (string)$r['primary_image_url'] : null,
      // Lightweight highlights for UI placeholders.
      'highlights' => [
        'Certified stone',
        'Premium finish',
        'Secure delivery',
      ],
      'colors' => [],
    ];
  }

  return $products;
}

function catalog_find_product_by_id(string $productId): ?array {
  // Alias kept for backwards compatibility with earlier code paths.
  return catalog_find_product_by_id_impl($productId);
}

function catalog_find_product_by_id_impl(string $productId): ?array {
  $pdo = db();

  $sql = "
    SELECT
      p.*, 
      c.name AS category_name,
      s.name AS subcategory_name,
      (
        SELECT pi.image_url
        FROM product_images pi
        WHERE pi.product_id = p.id
        ORDER BY pi.is_primary DESC, pi.display_order ASC, pi.id ASC
        LIMIT 1
      ) AS primary_image_url
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN subcategories s ON s.id = p.subcategory_id
    WHERE p.is_active=1 AND p.id = :pid
    LIMIT 1
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([':pid' => $productId]);
  $p = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$p) return null;

  $discount = (float)$p['discount_percent'];
  $price = (float)($p['base_price'] ?? 0);

  $final = $price;
  if ($discount > 0) {
    $final = $price * (1 - ($discount / 100));
  }

  $images = catalog_product_images((string)$p['id']);

  return [
    'id' => (string)$p['id'],
    'name' => (string)$p['name'],
    'slug' => (string)$p['slug'],
    'sku' => (string)$p['sku'],
    'category' => (string)($p['category_name'] ?? ''),
    'subcategory' => (string)($p['subcategory_name'] ?? ''),
    'metal' => (string)($p['metal'] ?? ''),
    'description' => (string)($p['description'] ?? $p['short_description'] ?? ''),
    'price' => (float)$final,
    'discount_percent' => (float)$discount,
    'rating_avg' => (float)$p['rating_avg'],
    'rating_count' => (int)$p['rating_count'],
    'collection' => $p['collection'] ?? null,
    'gender' => $p['gender'] ?? null,
    'occasion' => $p['occasion'] ?? null,
    'stone' => $p['stone'] ?? null,
    'primary_image_url' => $images['primary'] ?? $p['primary_image_url'] ?? null,
    'gallery' => $images['all'],
    'highlights' => ['Certified stone','Premium finish','Secure delivery'],
    'colors' => [],
    'stock' => (int)$p['stock'],
  ];
}

function catalog_find_product_by_slug(string $slug): ?array {
  $pdo = db();
  $pdo = db();

  $sql = "
    SELECT
      p.*, 
      c.name AS category_name,
      s.name AS subcategory_name,
      (
        SELECT pi.image_url
        FROM product_images pi
        WHERE pi.product_id = p.id
        ORDER BY pi.is_primary DESC, pi.display_order ASC, pi.id ASC
        LIMIT 1
      ) AS primary_image_url
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN subcategories s ON s.id = p.subcategory_id
    WHERE p.is_active=1 AND p.slug = :slug
    LIMIT 1
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([':slug' => $slug]);
  $p = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$p) return null;

  $discount = (float)$p['discount_percent'];
  $price = (float)($p['base_price'] ?? 0);

  $final = $price;
  if ($discount > 0) {
    $final = $price * (1 - ($discount / 100));
  }

  $images = catalog_product_images((string)$p['id']);

  return [
    'id' => (string)$p['id'],
    'name' => (string)$p['name'],
    'slug' => (string)$p['slug'],
    'sku' => (string)$p['sku'],
    'category' => (string)($p['category_name'] ?? ''),
    'subcategory' => (string)($p['subcategory_name'] ?? ''),
    'metal' => (string)($p['metal'] ?? ''),
    'description' => (string)($p['description'] ?? $p['short_description'] ?? ''),
    'price' => (float)$final,
    'discount_percent' => (float)$discount,
    'rating_avg' => (float)$p['rating_avg'],
    'rating_count' => (int)$p['rating_count'],
    'collection' => $p['collection'] ?? null,
    'gender' => $p['gender'] ?? null,
    'occasion' => $p['occasion'] ?? null,
    'stone' => $p['stone'] ?? null,
    'primary_image_url' => $images['primary'] ?? $p['primary_image_url'] ?? null,
    'gallery' => $images['all'],
    'highlights' => ['Certified stone','Premium finish','Secure delivery'],
    'colors' => [],
    'stock' => (int)$p['stock'],
  ];
}

function ensure_addresses_schema(PDO $pdo): void {
  // Check if addresses table exists
  $tableExists = true;
  try {
    $pdo->query('SELECT 1 FROM addresses LIMIT 1');
  } catch (Throwable $e) {
    $tableExists = false;
  }

  if (!$tableExists) {
    // Create fresh addresses table with full schema
    $pdo->exec('CREATE TABLE IF NOT EXISTS addresses (
      id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
      user_id BIGINT UNSIGNED NULL,
      full_name VARCHAR(150) NOT NULL,
      phone VARCHAR(30) NOT NULL,
      line1 VARCHAR(220) NOT NULL,
      line2 VARCHAR(220) NULL,
      city VARCHAR(120) NOT NULL,
      state VARCHAR(120) NOT NULL,
      pincode VARCHAR(20) NOT NULL,
      country VARCHAR(80) NOT NULL DEFAULT \'India\',
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_addresses_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    return;
  }

  // Table exists - ensure pincode column is present (legacy schema fix)
  try {
    $pdo->query('SELECT pincode FROM addresses LIMIT 1');
  } catch (Throwable $e) {
    try {
      $pdo->exec('ALTER TABLE addresses ADD COLUMN pincode VARCHAR(20) NOT NULL AFTER state');
    } catch (Throwable $e2) {
      // Ignore if column already exists
    }
  }
}

function ensure_orders_schema(PDO $pdo): void {
  // Check if orders table exists
  $tableExists = true;
  try {
    $pdo->query('SELECT 1 FROM orders LIMIT 1');
  } catch (Throwable $e) {
    $tableExists = false;
  }

  if (!$tableExists) {
    // Check if users table exists first (foreign key dependency)
    try {
      $pdo->query('SELECT 1 FROM users LIMIT 1');
    } catch (Throwable $e) {
      // Create users table if missing
      $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(190) NOT NULL UNIQUE,
        phone VARCHAR(30) NULL,
        name VARCHAR(150) NULL,
        password_hash VARCHAR(255) NOT NULL,
        is_verified TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }

    // Create fresh orders table with full schema
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
      id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
      order_code VARCHAR(40) NOT NULL UNIQUE,
      user_id BIGINT UNSIGNED NULL,
      address_id BIGINT UNSIGNED NULL,
      customer_name VARCHAR(150) NOT NULL,
      customer_email VARCHAR(190) NULL,
      customer_phone VARCHAR(30) NULL,
      shipping_address TEXT NOT NULL,
      subtotal DECIMAL(12,2) NOT NULL,
      shipping DECIMAL(12,2) NOT NULL DEFAULT 0,
      discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
      total DECIMAL(12,2) NOT NULL,
      coupon_code VARCHAR(60) NULL,
      payment_method ENUM('COD','Razorpay','Stripe','PayU') NOT NULL DEFAULT 'COD',
      payment_status ENUM('PENDING','PAID','FAILED','REFUNDED') NOT NULL DEFAULT 'PENDING',
      order_status ENUM('PLACED','CONFIRMED','PROCESSING','SHIPPED','DELIVERED','CANCELLED','RETURNED') NOT NULL DEFAULT 'PLACED',
      tracking_number VARCHAR(100) NULL,
      shipping_carrier VARCHAR(100) NULL,
      return_status ENUM('NONE', 'REQUESTED', 'APPROVED', 'REJECTED') NOT NULL DEFAULT 'NONE',
      return_reason TEXT NULL,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_orders_user (user_id),
      INDEX idx_orders_code (order_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Create order_items table
    $pdo->exec('CREATE TABLE IF NOT EXISTS order_items (
      id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
      order_id BIGINT UNSIGNED NOT NULL,
      product_id BIGINT UNSIGNED NULL,
      product_name VARCHAR(200) NOT NULL,
      product_sku VARCHAR(80) NOT NULL,
      quantity INT NOT NULL DEFAULT 1,
      unit_price DECIMAL(12,2) NOT NULL,
      total_price DECIMAL(12,2) NOT NULL,
      FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
  } else {
    // Table exists - check status enum and add new tracking/return columns if needed
    try {
      $pdo->exec("ALTER TABLE orders MODIFY COLUMN order_status ENUM('PLACED', 'CONFIRMED', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'CANCELLED', 'RETURNED') NOT NULL DEFAULT 'PLACED'");
    } catch (Throwable $e) {}

    $requiredOrderCols = [
      'tracking_number' => "VARCHAR(100) NULL AFTER order_status",
      'shipping_carrier' => "VARCHAR(100) NULL AFTER tracking_number",
      'return_status' => "ENUM('NONE', 'REQUESTED', 'APPROVED', 'REJECTED') NOT NULL DEFAULT 'NONE' AFTER shipping_carrier",
      'return_reason' => "TEXT NULL AFTER return_status",
    ];

    foreach ($requiredOrderCols as $col => $def) {
      try {
        $pdo->query("SELECT $col FROM orders LIMIT 1");
      } catch (Throwable $e) {
        try {
          $pdo->exec("ALTER TABLE orders ADD COLUMN $col $def");
        } catch (Throwable $e2) {}
      }
    }
  }

  // Ensure notification_logs table exists
  try {
    $pdo->query("SELECT 1 FROM notification_logs LIMIT 1");
  } catch (Throwable $e) {
    try {
      $pdo->exec("
        CREATE TABLE IF NOT EXISTS notification_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_id BIGINT UNSIGNED NULL,
            type ENUM('EMAIL', 'WHATSAPP', 'SMS') NOT NULL,
            recipient VARCHAR(150) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY fk_notify_order (order_id),
            CONSTRAINT fk_notify_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
      ");
    } catch (Throwable $e2) {}
  }
}

function ensure_appointments_schema(PDO $pdo): void {
  // Check if appointments table exists
  $tableExists = true;
  try {
    $pdo->query('SELECT 1 FROM appointments LIMIT 1');
  } catch (Throwable $e) {
    $tableExists = false;
  }

  if (!$tableExists) {
    $pdo->exec('CREATE TABLE IF NOT EXISTS appointments (
      id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
      city VARCHAR(120) NOT NULL,
      store VARCHAR(120) NOT NULL,
      name VARCHAR(150) NOT NULL,
      phone VARCHAR(30) NOT NULL,
      email VARCHAR(190) NOT NULL,
      preferred_date DATE NOT NULL,
      message TEXT NULL,
      status ENUM("pending","confirmed","cancelled","completed") NOT NULL DEFAULT "pending",
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_appointments_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
  }
}

function catalog_product_images(string $productId): array {
  $pdo = db();
  $stmt = $pdo->prepare("SELECT image_url, is_primary, display_order FROM product_images WHERE product_id = :pid ORDER BY is_primary DESC, display_order ASC, id ASC");
  $stmt->execute([':pid' => $productId]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $all = [];
  $primary = null;
  foreach ($rows as $r) {
    $url = (string)$r['image_url'];
    $all[] = ['url' => $url];
    if (!empty($r['is_primary'])) {
      $primary = $url;
    }
  }

  if ($primary === null && $all) {
    $primary = (string)$all[0]['url'];
  }

  return ['all' => $all, 'primary' => $primary];
}

