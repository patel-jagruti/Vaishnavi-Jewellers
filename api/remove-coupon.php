<?php
/**
 * Remove Coupon API
 * Removes applied coupon from cart session
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', '1');
    @session_start();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false]);
    exit;
}

// Remove coupon from session
unset($_SESSION['coupon_code']);
unset($_SESSION['coupon_discount']);

echo json_encode(['ok' => true]);