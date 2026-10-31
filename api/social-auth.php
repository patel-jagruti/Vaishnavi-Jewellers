<?php
declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/base_url.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

ensure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = trim((string)($input['email'] ?? ''));
$name = trim((string)($input['name'] ?? ''));
$provider = trim((string)($input['provider'] ?? ''));

if ($email === '' || $name === '') {
    echo json_encode(['ok' => false, 'error' => 'Invalid email or name.']);
    exit;
}

try {
    $pdo = db();
    
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Create user with a dummy password since they logged in via social provider
        $dummyPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        $stmtInsert = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role, created_at) VALUES (:name, :email, :pass, 'customer', NOW())");
        $stmtInsert->execute([
            ':name' => $name,
            ':email' => $email,
            ':pass' => $dummyPassword
        ]);
        
        // Fetch new user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
    }

    // Set Session
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'role' => (string)$user['role'],
        'full_name' => (string)$user['full_name'],
        'email' => (string)$user['email']
    ];

    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
