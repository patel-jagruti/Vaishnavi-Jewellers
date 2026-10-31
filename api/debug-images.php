<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', '0');

try {
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/catalog_db.php';
    $pdo = db();
    ensure_catalog_seeded($pdo);

    $images = $pdo->query("SELECT product_id, image_url FROM product_images ORDER BY product_id LIMIT 20")->fetchAll();
    echo json_encode(['product_images' => $images], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
exit;