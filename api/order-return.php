<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

ensure_session();

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Authentication required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed.']);
    exit;
}

$user = $_SESSION['user'];
$orderId = (int)($_POST['order_id'] ?? 0);
$type = trim((string)($_POST['type'] ?? ''));
$reason = trim((string)($_POST['reason'] ?? ''));
$comments = trim((string)($_POST['comments'] ?? ''));

if (!$orderId || !in_array($type, ['RETURN', 'EXCHANGE'], true) || !$reason || !$comments) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing or invalid parameters.']);
    exit;
}

try {
    $pdo = db();
    
    // Fetch order to verify ownership
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Order not found.']);
        exit;
    }

    // Security check: Only the customer who placed the order can request return
    if ((int)$order['user_id'] !== (int)$user['id']) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Access denied. You do not own this order.']);
        exit;
    }

    // Check if order is DELIVERED and has no return status set
    if ($order['order_status'] !== 'DELIVERED') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Returns are only available for delivered orders.']);
        exit;
    }

    if ($order['return_status'] !== 'NONE') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'A return request has already been submitted for this order.']);
        exit;
    }

    $formattedReason = "Type: " . $type . "\nReason: " . $reason . "\nComments: " . $comments;

    // Begin Transaction to save request & log simulated notifications
    $pdo->beginTransaction();

    // 1. Update order return status
    $upd = $pdo->prepare("UPDATE orders SET return_status = 'REQUESTED', return_reason = ? WHERE id = ?");
    $upd->execute([$formattedReason, $orderId]);

    // 2. Insert Simulated WhatsApp alert
    $waMsg = "Namaste " . $order['customer_name'] . ", we have received your " . strtolower($type) . " request for Order #" . $order['order_code'] . ". Our QA team will audit the details and notify you. Thank you, Vaishnavi Jewellers.";
    $insWA = $pdo->prepare("INSERT INTO notification_logs (order_id, type, recipient, message) VALUES (?, 'WHATSAPP', ?, ?)");
    $insWA->execute([$orderId, $order['customer_phone'] ?? 'N/A', $waMsg]);

    // 3. Insert Simulated Email alert
    $emailMsg = "Dear " . $order['customer_name'] . ",\n\nThis email confirms that your " . strtolower($type) . " request for Order #" . $order['order_code'] . " has been submitted successfully.\n\nDetails:\n" . $formattedReason . "\n\nWe will update you once our team reviews this request.\n\nBest Regards,\nVaishnavi Jewellers Support Team";
    $insEmail = $pdo->prepare("INSERT INTO notification_logs (order_id, type, recipient, message) VALUES (?, 'EMAIL', ?, ?)");
    $insEmail->execute([$orderId, $order['customer_email'] ?? 'N/A', $emailMsg]);

    $pdo->commit();

    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
