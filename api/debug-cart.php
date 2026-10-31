<?php
declare(strict_types=1);
error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/catalog_db.php';
require_once __DIR__ . '/../includes/cart_db.php';

$result = [
    'session_cart' => $_SESSION['cart'] ?? [],
    'db_connected' => false,
    'cart_id' => null,
    'cart_count' => 0,
    'cart_items' => [],
    'products' => [],
    'errors' => []
];

try {
    $pdo = db();
    $result['db_connected'] = true;

    // Ensure catalog is seeded
    ensure_catalog_seeded($pdo);

    // Get products
    $stmt = $pdo->query('SELECT id, name, sku, is_active FROM products ORDER BY id LIMIT 20');
    $result['products'] = $stmt->fetchAll();

    // Ensure cart table
    ensure_cart_table($pdo);

    // Get cart ID
    $result['cart_id'] = get_or_create_cart($pdo);

    // Get cart items
    $result['cart_items'] = get_cart_items($pdo);
    $result['cart_count'] = cart_count($pdo);

} catch (Exception $e) {
    $result['errors'][] = $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT);
exit;