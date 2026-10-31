<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = db();
    echo "Connected to database successfully.\n\n";

    // 1. Alter orders table to modify order_status and add new tracking/return columns
    echo "Altering 'orders' table...\n";
    
    // Check if columns exist first to ensure idempotency
    $columns = $pdo->query("SHOW COLUMNS FROM orders")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('tracking_number', $columns, true)) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN tracking_number VARCHAR(100) NULL AFTER order_status");
        echo "- Added column 'tracking_number'\n";
    }
    if (!in_array('shipping_carrier', $columns, true)) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN shipping_carrier VARCHAR(100) NULL AFTER tracking_number");
        echo "- Added column 'shipping_carrier'\n";
    }
    if (!in_array('return_status', $columns, true)) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN return_status ENUM('NONE', 'REQUESTED', 'APPROVED', 'REJECTED') NOT NULL DEFAULT 'NONE' AFTER shipping_carrier");
        echo "- Added column 'return_status'\n";
    }
    if (!in_array('return_reason', $columns, true)) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN return_reason TEXT NULL AFTER return_status");
        echo "- Added column 'return_reason'\n";
    }

    // Modify order_status enum to ensure 'PROCESSING' and 'RETURNED' are present
    echo "Modifying 'order_status' ENUM definitions...\n";
    $pdo->exec("ALTER TABLE orders MODIFY COLUMN order_status ENUM('PLACED', 'CONFIRMED', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'CANCELLED', 'RETURNED') NOT NULL DEFAULT 'PLACED'");
    echo "- Modified column 'order_status'\n";

    // 2. Create notification_logs table
    echo "Creating 'notification_logs' table...\n";
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
    echo "- Created 'notification_logs' table successfully.\n\n";

    echo "Migration completed successfully!\n";
} catch (Throwable $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
