<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: application/json; charset=utf-8');

session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/catalog_db.php';
require_once __DIR__ . '/../includes/cart_db.php';

$result = [
    'session_wishlist' => $_SESSION['wishlist'] ?? [],
    'db_connected' => false,
    'wishlist_count' => 0,
    'wishlist_items' => [],
    'errors' => []
];

try {
    $pdo = db();
    $result['db_connected'] = true;
    ensure_wishlist_table($pdo);
    $result['wishlist_items'] = wishlist_items($pdo);
    $result['wishlist_count'] = wishlist_count($pdo);
} catch (Exception $e) {
    $result['errors'][] = $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT);
exit;
