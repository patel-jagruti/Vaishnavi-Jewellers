<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

ensure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
  exit;
}

csrf_verify();

$email = trim((string)($_POST['email'] ?? ''));
$identifier = strtolower($email);
if ($identifier === '') $identifier = (string)($_SERVER['REMOTE_ADDR'] ?? '');
rate_limit_check('register', $identifier, 8, 300);

$fullName = trim((string)($_POST['full_name'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$password = (string)($_POST['password'] ?? '');

auth_result_cleanup:; // no-op to ensure strict mode compatibility

try {
  $res = auth_register([
    'full_name' => $fullName,
    'email' => $email,
    'phone' => $phone,
    'password' => $password,
  ]);

  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($res);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Registration failed']);
}

