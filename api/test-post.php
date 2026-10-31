<?php
header('Content-Type: application/json');
session_start();

// Echo back what was received
echo json_encode([
    'POST' => $_POST,
    'SESSION cart' => $_SESSION['cart'] ?? [],
    'SERVER' => [
        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
        'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? 'unknown'
    ]
]);
exit;