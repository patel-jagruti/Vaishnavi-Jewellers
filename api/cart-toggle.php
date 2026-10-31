<?php
declare(strict_types=1);

// Prevent any HTML output on errors
error_reporting(0);
ini_set('display_errors', '0');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', '1');
    @session_start();
}

// Ensure proper JSON headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/cart_db.php';

// Get input parameters
$action = isset($_POST['action']) ? (string)$_POST['action'] : 'add';
$productId = isset($_POST['product_id']) ? trim((string)$_POST['product_id']) : '';
$qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

// Validate
if ($productId === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Missing product_id']);
    exit;
}

$qty = max(1, min(99, $qty));

try {
    $pdo = db();
    header('X-Cart-Guest-ID: ' . guest_session_id());
    header('X-Cart-Session-ID: ' . session_id());
    
    if ($action === 'remove') {
        cart_remove($pdo, $productId);
        $message = 'Item removed';
    } elseif ($action === 'remove_one') {
        // Fetch current qty
        $items = get_cart_items($pdo);
        $currentQty = 0;
        foreach ($items as $item) {
            if ((string)$item['product_id'] === (string)$productId) {
                $currentQty = (int)$item['quantity'];
                break;
            }
        }
        if ($currentQty <= 1) {
            cart_remove($pdo, $productId);
        } else {
            cart_set_qty($pdo, $productId, $currentQty - 1);
        }
        $message = 'Quantity updated';
    } else {
        // Add action
        cart_add($pdo, $productId, $qty);
        $message = 'Product added to cart';
    }
    
    // Get new count
    $cartCount = cart_count($pdo);
    
    echo json_encode([
        'ok' => true,
        'message' => $message,
        'cart_count' => $cartCount
    ]);
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}