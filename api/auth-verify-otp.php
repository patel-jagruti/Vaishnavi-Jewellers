<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

ensure_session();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $identity = trim((string)($input['identity'] ?? ''));
    $otp = trim((string)($input['otp'] ?? ''));

    if (empty($identity) || empty($otp)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Missing identity or OTP code.']);
        exit;
    }

    // Verify OTP code matches session
    $savedOtp = $_SESSION['otp_code'] ?? null;
    $savedIdentity = $_SESSION['otp_identity'] ?? null;
    $expires = $_SESSION['otp_expires'] ?? 0;

    if (!$savedOtp || !$savedIdentity || $savedIdentity !== $identity) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid OTP request or identity mismatch.']);
        exit;
    }

    if (time() > $expires) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'OTP has expired. Please request a new one.']);
        exit;
    }

    if ($savedOtp !== $otp) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Incorrect OTP code. Please try again.']);
        exit;
    }

    // Clear OTP variables from session now that it is verified
    unset($_SESSION['otp_code'], $_SESSION['otp_identity'], $_SESSION['otp_expires']);

    // Find or create user row
    $pdo = db();
    $email = strpos($identity, '@') !== false ? strtolower($identity) : null;
    $phone = strpos($identity, '@') !== false ? null : $identity;

    $stmt = null;
    if ($email) {
        $stmt = $pdo->prepare('SELECT id, full_name, email, role FROM users WHERE email = :email AND role = \'customer\' LIMIT 1');
        $stmt->execute([':email' => $email]);
    } else {
        $stmt = $pdo->prepare('SELECT id, full_name, email, role FROM users WHERE phone = :phone AND role = \'customer\' LIMIT 1');
        $stmt->execute([':phone' => $phone]);
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Auto-register user
        $fullName = 'Valued Customer';
        $finalEmail = $email ?: $phone . '@temp.vaishnavijewellers.com';
        $passHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

        $ins = $pdo->prepare('INSERT INTO users (role, email, phone, full_name, password_hash, is_verified) VALUES (\'customer\', :email, :phone, :full_name, :pass_hash, 1)');
        $ins->execute([
            ':email' => $finalEmail,
            ':phone' => $phone,
            ':full_name' => $fullName,
            ':pass_hash' => $passHash
        ]);

        $userId = (int)$pdo->lastInsertId();
        $user = [
            'id' => $userId,
            'full_name' => $fullName,
            'email' => $finalEmail,
            'role' => 'customer'
        ];
    }

    // Start session log-in
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'role' => (string)$user['role'],
        'full_name' => (string)$user['full_name'],
        'email' => (string)$user['email'],
    ];

    // Migrate guest cart items
    if (!empty($_SESSION['guest_sid'])) {
        try {
            require_once __DIR__ . '/../includes/cart_db.php';
            migrate_guest_to_user($pdo, (int)$user['id'], (string)$_SESSION['guest_sid']);
        } catch (Throwable $ignored) {}
    }

    echo json_encode(['ok' => true, 'user' => $_SESSION['user']]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server verification failed: ' . $e->getMessage()]);
}
