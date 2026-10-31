<?php
declare(strict_types=1);

require_once __DIR__ . '/base_url.php';

if (!function_exists('h')) {
  function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
  }
}


if (!function_exists('json_response')) {
  function json_response(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
  }
}

if (!function_exists('money')) {
  function money(float $n): string {
    return '₹' . number_format($n, 2, '.', ',');
  }
}


if (!function_exists('ensure_session')) {
  function ensure_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
      if (!headers_sent()) {
        ini_set('session.cookie_path', '/');
        ini_set('session.cookie_httponly', '1');
      }
      @session_start();
    }
  }
}



function csrf_token(): string {
  ensure_session();
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return (string)$_SESSION['csrf_token'];
}

function csrf_verify(): void {
  ensure_session();
  $sent = $_POST['csrf_token'] ?? '';
  $token = (string)($_SESSION['csrf_token'] ?? '');
  if (!$sent || !hash_equals($token, (string)$sent)) {
    http_response_code(419);
    echo 'CSRF validation failed.';
    exit;
  }
}

function require_login(): void {
  ensure_session();
  if (empty($_SESSION['user'])) {
    header('Location: /visnavi jwallers/pages/login.php');
    exit;
  }
}

function is_admin(): bool {
  ensure_session();
  return !empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function require_admin(): void {
  ensure_session();
  if (!is_admin()) {
    http_response_code(403);
    echo 'Forbidden.';
    exit;
  }
}

function trigger_order_notifications(PDO $pdo, int $orderId, string $status, ?string $carrier = null, ?string $tracking = null): void {
  // Fetch order and customer details
  $stmt = $pdo->prepare("SELECT order_code, customer_name, customer_email, customer_phone FROM orders WHERE id = ? LIMIT 1");
  $stmt->execute([$orderId]);
  $order = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$order) return;

  $name = $order['customer_name'];
  $code = $order['order_code'];
  $phone = $order['customer_phone'] ?? 'N/A';
  $email = $order['customer_email'] ?? 'N/A';

  $waMsg = "";
  $emailMsg = "";

  switch (strtoupper($status)) {
    case 'CONFIRMED':
      $waMsg = "Namaste {$name}, your Order #{$code} has been CONFIRMED by Vaishnavi Jewellers. We are preparing your exquisite jewelry. Thank you!";
      $emailMsg = "Dear {$name},\n\nWe are pleased to inform you that your Order #{$code} has been officially confirmed.\n\nOur artisans are now crafting/preparing your selection. You can track progress here: " . route('pages/orders.php') . "\n\nBest Regards,\nVaishnavi Jewellers";
      break;

    case 'PROCESSING':
      $waMsg = "Namaste {$name}, your Order #{$code} is now in DESIGNING & QUALITY CHECK. We ensure maximum perfection for your jewelry!";
      $emailMsg = "Dear {$name},\n\nYour Order #{$code} has entered the processing stage (Designing & Quality audit).\n\nBest Regards,\nVaishnavi Jewellers";
      break;

    case 'SHIPPED':
      $carrierName = $carrier ?? 'Standard Delivery';
      $trackingID = $tracking ?? 'N/A';
      $waMsg = "Great news {$name}! Your Order #{$code} has been SHIPPED via {$carrierName} (Tracking ID: {$trackingID}). Track here: " . route('pages/orders.php');
      $emailMsg = "Dear {$name},\n\nGood news! Your Order #{$code} has been shipped.\n\nCourier Partner: {$carrierName}\nTracking Number: {$trackingID}\n\nYou can monitor its journey directly on your orders panel: " . route('pages/orders.php') . "\n\nBest Regards,\nVaishnavi Jewellers";
      break;

    case 'DELIVERED':
      $waMsg = "Namaste {$name}, your Order #{$code} has been DELIVERED! We hope you love your new jewelry. Tag us on Instagram or leave a review!";
      $emailMsg = "Dear {$name},\n\nYour Order #{$code} has been delivered successfully.\n\nThank you for choosing Vaishnavi Jewellers! We look forward to serving you again.\n\nBest Regards,\nVaishnavi Jewellers";
      break;

    case 'CANCELLED':
      $waMsg = "Order #{$code} has been CANCELLED. If this was a mistake, please place a new order or contact support.";
      $emailMsg = "Dear {$name},\n\nThis email confirms that your Order #{$code} has been cancelled.\n\nFor any inquiries or refunds, please reply to this email.\n\nBest Regards,\nVaishnavi Jewellers";
      break;
      
    case 'RETURNED':
      $waMsg = "Namaste {$name}, your return request for Order #{$code} has been approved, and the refund has been processed.";
      $emailMsg = "Dear {$name},\n\nYour return/refund for Order #{$code} has been successfully completed.\n\nBest Regards,\nVaishnavi Jewellers";
      break;
  }

  if ($waMsg !== "") {
    $ins = $pdo->prepare("INSERT INTO notification_logs (order_id, type, recipient, message) VALUES (?, 'WHATSAPP', ?, ?)");
    $ins->execute([$orderId, $phone, $waMsg]);
  }
  if ($emailMsg !== "") {
    $ins = $pdo->prepare("INSERT INTO notification_logs (order_id, type, recipient, message) VALUES (?, 'EMAIL', ?, ?)");
    $ins->execute([$orderId, $email, $emailMsg]);

    // Dispatch actual email or write sandbox file
    if (!empty($email) && $email !== 'N/A') {
      try {
        if (!function_exists('send_email')) {
          require_once __DIR__ . '/auth.php';
        }
        send_email($email, $name, "Order Update: #{$code} - Vaishnavi Jewellers", nl2br(htmlspecialchars($emailMsg)), $emailMsg);
      } catch (Throwable $e) {}
    }
  }
}


