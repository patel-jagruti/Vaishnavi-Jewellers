<?php
/**
 * Apply Coupon API
 * Validates and applies coupon code to cart
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', '1');
    @session_start();
}
$response = ['ok' => false, 'message' => '', 'discount' => 0];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode($response);
    exit;
}

$couponCode = isset($_POST['coupon_code']) ? strtoupper(trim($_POST['coupon_code'])) : '';

if (empty($couponCode)) {
    $response['message'] = 'Please enter a coupon code';
    echo json_encode($response);
    exit;
}

try {
    $pdo = include __DIR__ . '/../includes/db.php';

    if (!($pdo instanceof PDO)) {
        // Demo mode - simulate coupon validation
        $demoCoupons = [
            'SAVE10' => 10, // 10% off
            'SAVE20' => 20, // 20% off
            'FLAT1000' => 1000, // Flat Rs. 1000 off
            'WELCOME500' => 500, // Welcome discount
        ];

        if (isset($demoCoupons[$couponCode])) {
            $_SESSION['coupon_code'] = $couponCode;
            $_SESSION['coupon_discount'] = $demoCoupons[$couponCode];
            $response['ok'] = true;
            $response['message'] = 'Coupon applied successfully!';
            $response['discount'] = $demoCoupons[$couponCode];
        } else {
            $response['message'] = 'Invalid coupon code';
        }
        echo json_encode($response);
        exit;
    }

    // Check coupon in database
    $stmt = $pdo->prepare("
        SELECT id, coupon_type, discount_value, min_order_amount, is_active, start_date, end_date, usage_limit, used_count
        FROM coupons
        WHERE code = :code AND is_active = 1
        LIMIT 1
    ");
    $stmt->execute([':code' => $couponCode]);
    $coupon = $stmt->fetch();

    if (!$coupon) {
        $response['message'] = 'Invalid coupon code';
        echo json_encode($response);
        exit;
    }

    // Check if coupon is still valid
    $now = new DateTime();
    if ($coupon['start_date']) {
        $startDate = new DateTime($coupon['start_date']);
        if ($now < $startDate) {
            $response['message'] = 'Coupon is not yet active';
            echo json_encode($response);
            exit;
        }
    }
    if ($coupon['end_date']) {
        $endDate = new DateTime($coupon['end_date']);
        if ($now > $endDate) {
            $response['message'] = 'Coupon has expired';
            echo json_encode($response);
            exit;
        }
    }

    // Check usage limit
    if ($coupon['usage_limit'] > 0 && $coupon['used_count'] >= $coupon['usage_limit']) {
        $response['message'] = 'Coupon usage limit exceeded';
        echo json_encode($response);
        exit;
    }

    // Calculate cart subtotal for minimum order check
    require_once __DIR__ . '/../includes/cart_db.php';
    $cartItems = get_cart_items($pdo);
    $subtotal = 0;
    foreach ($cartItems as $item) {
        $subtotal += (float)$item['unit_price'] * (int)$item['quantity'];
    }

    // Check minimum order amount
    if ($coupon['min_order_amount'] > 0 && $subtotal < $coupon['min_order_amount']) {
        $response['message'] = 'Minimum order amount required: Rs. ' . number_format($coupon['min_order_amount']);
        echo json_encode($response);
        exit;
    }

    // Calculate discount
    $discount = 0;
    if ($coupon['coupon_type'] === 'PERCENT') {
        $discount = $subtotal * ($coupon['discount_value'] / 100);
    } else {
        $discount = min($coupon['discount_value'], $subtotal);
    }

    // Store coupon in session
    $_SESSION['coupon_code'] = $couponCode;
    $_SESSION['coupon_discount'] = $discount;

    // Update usage count
    $stmt = $pdo->prepare('UPDATE coupons SET used_count = used_count + 1 WHERE id = :id');
    $stmt->execute([':id' => $coupon['id']]);

    $response['ok'] = true;
    $response['message'] = 'Coupon applied successfully!';
    $response['discount'] = $discount;

} catch (Exception $e) {
    $response['message'] = 'Error applying coupon';
}

echo json_encode($response);
exit;