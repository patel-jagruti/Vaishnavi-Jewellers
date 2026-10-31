<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $identity = trim((string)($input['identity'] ?? ''));

    if (empty($identity)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid mobile number or email address.']);
        exit;
    }

    // Generate a secure 6-digit OTP
    $otp = (string)random_int(100000, 999999);

    // Save in session
    $_SESSION['otp_identity'] = $identity;
    $_SESSION['otp_code'] = $otp;
    $_SESSION['otp_expires'] = time() + 300; // 5 minutes expiry

    // Return response with the OTP included (for easy sandbox simulation testing)
    echo json_encode([
        'success' => true,
        'message' => 'OTP sent successfully! (For testing, your OTP is: ' . $otp . ')',
        'otp' => $otp
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
