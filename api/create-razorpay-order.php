<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/cart_db.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', '1');
    @session_start();
}

try {
    $pdo = db();
    
    // 1. Calculate Cart Total (Same logic as checkout.php)
    $cartItems = [];
    $subtotal = 0;
    
    $dbItems = get_cart_items($pdo);
    if (!empty($dbItems)) {
        foreach ($dbItems as $item) {
            $cartItems[] = [
                'id' => (string)$item['product_id'],
                'name' => (string)$item['product_name'],
                'effective_price' => (float)$item['effective_price'],
                'quantity' => (int)$item['quantity']
            ];
            $subtotal += (float)$item['effective_price'] * (int)$item['quantity'];
        }
    } else {
        $sessionCart = $_SESSION['cart'] ?? [];
        if (!empty($sessionCart)) {
            $productIds = array_keys($sessionCart);
            $inQuery = implode(',', array_fill(0, count($productIds), '?'));
            $stmt = $pdo->prepare("SELECT p.id, p.name, p.base_price, p.price_type, p.purity, p.making_charge_percent, p.weight_grams, p.discount_percent FROM products p WHERE p.id IN ($inQuery)");
            $stmt->execute(array_map('intval', $productIds));
            $dbProds = $stmt->fetchAll();
            
            foreach ($dbProds as $p) {
                $pid = (string)$p['id'];
                $qty = (int)($sessionCart[$pid] ?? 1);
                $unitPrice = price_now($p);
                $cartItems[] = [
                    'id' => $pid,
                    'name' => (string)$p['name'],
                    'effective_price' => $unitPrice,
                    'quantity' => $qty
                ];
                $subtotal += $unitPrice * $qty;
            }
        }
    }
    
    if (empty($cartItems)) {
        http_response_code(400);
        echo json_encode(['error' => 'Cart is empty.']);
        exit;
    }
    
    $freeShippingThreshold = 150000;
    $taxRate = 3;
    $shipping = ($subtotal >= $freeShippingThreshold || $subtotal === 0) ? 0 : 500;
    $tax = $subtotal * ($taxRate / 100);
    $total = $subtotal + $shipping + $tax;
    
    // Amount in paise for Razorpay
    $amountInPaise = (int)round($total * 100);
    
    // 2. Load Razorpay Credentials
    $config = require __DIR__ . '/../includes/config.php';
    $rzpConfig = $config['payments']['razorpay'] ?? [];
    $keyId = $rzpConfig['key_id'] ?? '';
    $keySecret = $rzpConfig['key_secret'] ?? '';
    
    $receipt = 'rcpt_' . uniqid();
    
    // If keys are placeholders/dummy, fallback to simulation mode
    if (empty($keyId) || strpos($keyId, 'dummy') !== false) {
        echo json_encode([
            'simulated' => true,
            'key_id' => $keyId ?: 'rzp_test_dummyId12345',
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'id' => 'order_sim_' . uniqid(),
            'receipt' => $receipt
        ]);
        exit;
    }
    
    // 3. Request Order Creation from Razorpay API
    $ch = curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, $keyId . ':' . $keySecret);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'amount' => $amountInPaise,
        'currency' => 'INR',
        'receipt' => $receipt
    ]));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 || $httpCode === 201) {
        $rzpOrder = json_decode($response, true);
        echo json_encode([
            'simulated' => false,
            'key_id' => $keyId,
            'amount' => $rzpOrder['amount'],
            'currency' => $rzpOrder['currency'],
            'id' => $rzpOrder['id'],
            'receipt' => $rzpOrder['receipt']
        ]);
    } else {
        // Fallback to simulated mode if API request fails (e.g. invalid keys)
        echo json_encode([
            'simulated' => true,
            'key_id' => $keyId,
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'id' => 'order_sim_err_' . uniqid(),
            'receipt' => $receipt,
            'api_error' => $response
        ]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create order.', 'details' => $e->getMessage()]);
}
