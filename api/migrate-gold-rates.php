<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = db();
    echo "Connected to database successfully.\n\n";

    // 1. Create gold_rates table
    echo "Creating 'gold_rates' table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS gold_rates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            metal_type VARCHAR(20) NOT NULL UNIQUE,
            rate_per_gram DECIMAL(10,2) NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
    echo "- 'gold_rates' table checked/created.\n";

    // Seed default rates if not present
    $check = $pdo->query("SELECT COUNT(*) FROM gold_rates")->fetchColumn();
    if ((int)$check === 0) {
        echo "Seeding default metal rates...\n";
        $seed = $pdo->prepare("INSERT INTO gold_rates (metal_type, rate_per_gram) VALUES (?, ?)");
        $seed->execute(['gold_24k', 7400.00]);
        $seed->execute(['gold_22k', 6800.00]);
        $seed->execute(['gold_18k', 5600.00]);
        $seed->execute(['silver', 95.00]);
        echo "- Seeded 24K, 22K, 18K Gold and Silver default rates.\n";
    }

    // 2. Alter products table
    echo "Altering 'products' table...\n";
    $columns = $pdo->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('price_type', $columns, true)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN price_type ENUM('fixed', 'dynamic') NOT NULL DEFAULT 'fixed' AFTER base_price");
        echo "- Added column 'price_type'\n";
    }
    if (!in_array('purity', $columns, true)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN purity ENUM('24k', '22k', '18k', 'silver') NULL AFTER price_type");
        echo "- Added column 'purity'\n";
    }
    if (!in_array('making_charge_percent', $columns, true)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN making_charge_percent DECIMAL(5,2) NOT NULL DEFAULT 8.00 AFTER purity");
        echo "- Added column 'making_charge_percent'\n";
    }

    echo "\nGold rates migration completed successfully!\n";
} catch (Throwable $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
