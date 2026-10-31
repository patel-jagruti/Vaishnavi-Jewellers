<?php
declare(strict_types=1);

// Prevent HTML output on errors
error_reporting(0);
ini_set('display_errors', '0');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', '1');
    @session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/cart_db.php';
require_once __DIR__ . '/../includes/catalog_db.php';

try {
    $pdo = db();
    header('X-Cart-Guest-ID: ' . guest_session_id());
    header('X-Cart-Session-ID: ' . session_id());
    
    // Get parameters
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $action = $_REQUEST['action'] ?? 'fetch';
    $productId = $_REQUEST['product_id'] ?? '';
    $qty = isset($_REQUEST['qty']) ? (int)$_REQUEST['qty'] : 1;
    
    if ($action === 'add' && $method === 'POST') {
        if ($productId !== '') {
            cart_add($pdo, (string)$productId, $qty);
        }
    } elseif ($action === 'update' && $method === 'POST') {
        if ($productId !== '') {
            cart_set_qty($pdo, (string)$productId, $qty);
        }
    } elseif ($action === 'remove' && $method === 'POST') {
        if ($productId !== '') {
            cart_remove($pdo, (string)$productId);
        }
    }
    
    // Fetch all current items
    $dbItems = get_cart_items($pdo);
    $cartCount = cart_count($pdo);
    
    $items = [];
    $subtotal = 0;
    
    foreach ($dbItems as $item) {
        $price = (float)$item['effective_price'];
        $itemTotal = $price * (int)$item['quantity'];
        $subtotal += $itemTotal;
        
        $img = $item['primary_image_url'] ? asset($item['primary_image_url']) : '';
        
        $items[] = [
            'id' => (string)$item['product_id'],
            'name' => (string)$item['product_name'],
            'quantity' => (int)$item['quantity'],
            'price' => $price,
            'total' => $itemTotal,
            'image_url' => $img,
            'sku' => (string)$item['sku']
        ];
    }
    
    $freeShippingThreshold = 50000;
    $taxRate = 3.0; // 3% GST
    
    $shipping = ($subtotal >= $freeShippingThreshold || $subtotal === 0) ? 0 : 500;
    $tax = $subtotal * ($taxRate / 100);
    $total = $subtotal + $shipping + $tax;
    
    echo json_encode([
        'ok' => true,
        'cart_count' => $cartCount,
        'items' => $items,
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'tax' => $tax,
        'total' => $total,
        'formatted_subtotal' => number_format($subtotal),
        'formatted_shipping' => $shipping > 0 ? 'Rs. ' . number_format($shipping) : 'FREE',
        'formatted_tax' => number_format($tax, 2),
        'formatted_total' => number_format($total, 2)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}
