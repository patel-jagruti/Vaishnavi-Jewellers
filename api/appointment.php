<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Method not allowed']);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
  $input = $_POST;
}

$required = ['city', 'store', 'name', 'phone', 'email', 'date'];
foreach ($required as $field) {
  if (empty($input[$field])) {
    echo json_encode(['success' => false, 'message' => "Please fill all required fields"]);
    exit;
  }
}

$city = trim($input['city']);
$store = trim($input['store']);
$name = trim($input['name']);
$phone = trim($input['phone']);
$email = trim($input['email']);
$date = trim($input['date']);
$message = trim($input['message'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['success' => false, 'message' => 'Invalid email address']);
  exit;
}

try {
  $pdo = db();
  $stmt = $pdo->prepare("INSERT INTO appointments (city, store, name, phone, email, preferred_date, message, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
  $stmt->execute([$city, $store, $name, $phone, $email, $date, $message]);

  echo json_encode(['success' => true, 'message' => 'Appointment booked successfully!']);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Failed to book appointment. Please try again.']);
}