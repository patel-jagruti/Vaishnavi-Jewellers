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
$token = trim((string)($_POST['token'] ?? ''));
$newPassword = (string)($_POST['new_password'] ?? '');

if ($email === '' || $token === '' || $newPassword === '') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Missing fields']);
  exit;
}

rate_limit_check('reset_pw', strtolower($email), 5, 900);

try {
  $res = auth_reset_password($email, $token, $newPassword);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($res);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Reset failed']);
}

