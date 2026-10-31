<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
$pdo = db();

echo "Starting category and subcategory database migration...\n";

// Disable foreign key checks to safely alter and truncate
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

// 1. Ensure subcategory_id column is present in products table
try {
    $pdo->query("SELECT subcategory_id FROM products LIMIT 1");
    echo "subcategory_id column already exists in products table.\n";
} catch (Throwable $e) {
    echo "Adding subcategory_id column to products table...\n";
    $pdo->exec("ALTER TABLE products ADD COLUMN subcategory_id BIGINT UNSIGNED NULL AFTER category_id");
}

// 2. Fetch all existing products before changing categories
$products = $pdo->query("SELECT id, name, category_id, metal, description FROM products")->fetchAll(PDO::FETCH_ASSOC);
echo "Fetched " . count($products) . " products for migration.\n";

// 3. Recreate categories and subcategories tables
$pdo->exec("DROP TABLE IF EXISTS subcategories");
$pdo->exec("TRUNCATE TABLE categories");

$pdo->exec("CREATE TABLE IF NOT EXISTS categories (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL UNIQUE,
    gender ENUM('female','male','unisex','all') NOT NULL DEFAULT 'all',
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE subcategories (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    category_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cat_sub (category_id, slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 4. Seed Main Categories
$mainCategories = [
    ['Gold', 'gold', 1],
    ['Diamond', 'diamond', 2],
    ['Silver', 'silver', 3],
    ['Gemstone', 'gemstone', 4]
];

$stmtCat = $pdo->prepare("INSERT INTO categories (id, name, slug, gender, display_order, is_active) VALUES (?, ?, ?, 'all', ?, 1)");
foreach ($mainCategories as $mc) {
    $stmtCat->execute([$mc[2], $mc[0], $mc[1], $mc[2]]);
}
echo "Seeded 4 main categories (Gold, Diamond, Silver, Gemstone).\n";

// Get new categories map
$categoryMap = [];
foreach ($pdo->query("SELECT id, name, slug FROM categories")->fetchAll() as $c) {
    $categoryMap[$c['slug']] = (int)$c['id'];
}

// 5. Seed Subcategories for each main category
$subcatNames = [
    'Earrings' => 'earrings',
    'Rings' => 'rings',
    'Couple Rings' => 'couple-rings',
    'Necklaces' => 'necklaces',
    'Necklace Sets' => 'necklace-sets',
    'Bangles' => 'bangles',
    'Bracelets' => 'bracelets',
    'Pendants' => 'pendants'
];

$subMap = []; // Maps parent_slug => [sub_name => sub_id]
$stmtSub = $pdo->prepare("INSERT INTO subcategories (category_id, name, slug, display_order, is_active) VALUES (?, ?, ?, ?, 1)");

foreach ($categoryMap as $pSlug => $pId) {
    $subMap[$pSlug] = [];
    $order = 1;
    foreach ($subcatNames as $name => $sSlug) {
        $stmtSub->execute([$pId, $name, $sSlug, $order++]);
        $subId = $pdo->lastInsertId();
        $subMap[$pSlug][$name] = (int)$subId;
    }
}
echo "Seeded subcategories for all main categories.\n";

// 6. Migrate Products
$updatedCount = 0;
$stmtUpdate = $pdo->prepare("UPDATE products SET category_id = ?, subcategory_id = ? WHERE id = ?");

// Fetch original category slug-mappings from our backup memory of old IDs
// Old category IDs:
// 2 => Gold, 3 => Diamond, 13 => Silver, 7 => Gemstone
// 4 => Earrings, 5 => Rings, 16 => Necklaces, 17 => Bangles, 18 => Bracelets
// 8 => Wedding, 9 => Gifting, 6 => Daily Wear
foreach ($products as $p) {
    $oldCatId = (int)$p['category_id'];
    $pNameLower = strtolower($p['name']);
    $pDescLower = strtolower($p['description'] ?? '');
    $pMetalLower = strtolower($p['metal'] ?? '');

    // A. Determine parent main category
    $parentSlug = 'gold'; // Default fallback

    if (strpos($pNameLower, 'silver') !== false || strpos($pDescLower, 'silver') !== false || strpos($pMetalLower, 'silver') !== false || $oldCatId === 13) {
        $parentSlug = 'silver';
    } elseif (strpos($pNameLower, 'diamond') !== false || strpos($pDescLower, 'diamond') !== false || strpos($pMetalLower, 'diamond') !== false || $oldCatId === 3) {
        $parentSlug = 'diamond';
    } elseif (strpos($pNameLower, 'gemstone') !== false || strpos($pDescLower, 'gemstone') !== false || strpos($pNameLower, 'ruby') !== false || strpos($pNameLower, 'emerald') !== false || $oldCatId === 7) {
        $parentSlug = 'gemstone';
    } elseif ($oldCatId === 2) {
        $parentSlug = 'gold';
    }

    $newParentId = $categoryMap[$parentSlug];

    // B. Determine subcategory name
    $subName = 'Rings'; // Default fallback

    // Map old subcategory IDs first
    if ($oldCatId === 4) {
        $subName = 'Earrings';
    } elseif ($oldCatId === 5) {
        $subName = 'Rings';
    } elseif ($oldCatId === 16) {
        $subName = 'Necklaces';
    } elseif ($oldCatId === 17) {
        $subName = 'Bangles';
    } elseif ($oldCatId === 18) {
        $subName = 'Bracelets';
    } else {
        // Fallback to name checks
        if (strpos($pNameLower, 'earring') !== false || strpos($pNameLower, 'jhumka') !== false || strpos($pNameLower, 'stud') !== false) {
            $subName = 'Earrings';
        } elseif (strpos($pNameLower, 'couple ring') !== false || strpos($pNameLower, 'couple band') !== false) {
            $subName = 'Couple Rings';
        } elseif (strpos($pNameLower, 'ring') !== false || strpos($pNameLower, 'band') !== false) {
            $subName = 'Rings';
        } elseif (strpos($pNameLower, 'necklace set') !== false) {
            $subName = 'Necklace Sets';
        } elseif (strpos($pNameLower, 'necklace') !== false || strpos($pNameLower, 'chain') !== false || strpos($pNameLower, 'choker') !== false || strpos($pNameLower, 'mangalsutra') !== false) {
            $subName = 'Necklaces';
        } elseif (strpos($pNameLower, 'bangle') !== false || strpos($pNameLower, 'kada') !== false) {
            $subName = 'Bangles';
        } elseif (strpos($pNameLower, 'bracelet') !== false) {
            $subName = 'Bracelets';
        } elseif (strpos($pNameLower, 'pendant') !== false || strpos($pNameLower, 'locket') !== false) {
            $subName = 'Pendants';
        }
    }

    $newSubId = $subMap[$parentSlug][$subName] ?? null;

    $stmtUpdate->execute([$newParentId, $newSubId, $p['id']]);
    $updatedCount++;
}

// Re-enable foreign key checks
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "Migration finished successfully. Updated $updatedCount products.\n";
