<?php
declare(strict_types=1);

/**
 * Delivery Check API
 * Check if we deliver to a pincode
 */

header('Content-Type: application/json; charset=utf-8');

$pincode = trim((string)($_GET['pincode'] ?? ''));

if (strlen($pincode) < 4) {
  echo json_encode([
    'deliverable' => false,
    'message' => 'Please enter a valid pincode (6 digits).',
    'delivery_date' => null,
    'shipping_charge' => 0,
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

// Sample pincode serviceability - in production, integrate with shipping provider API
// Coverage for major Indian cities
$serviceable = [
  '400' => ['city' => 'Mumbai', 'state' => 'Maharashtra', 'days' => 1, 'charge' => 0],
  '110' => ['city' => 'Delhi', 'state' => 'Delhi', 'days' => 2, 'charge' => 0],
  '560' => ['city' => 'Bangalore', 'state' => 'Karnataka', 'days' => 2, 'charge' => 0],
  '500' => ['city' => 'Hyderabad', 'state' => 'Telangana', 'days' => 2, 'charge' => 50],
  '600' => ['city' => 'Chennai', 'state' => 'Tamil Nadu', 'days' => 2, 'charge' => 0],
  '380' => ['city' => 'Ahmedabad', 'state' => 'Gujarat', 'days' => 3, 'charge' => 50],
  '411' => ['city' => 'Pune', 'state' => 'Maharashtra', 'days' => 2, 'charge' => 0],
  '700' => ['city' => 'Kolkata', 'state' => 'West Bengal', 'days' => 3, 'charge' => 50],
  '122' => ['city' => 'Gurgaon', 'state' => 'Haryana', 'days' => 2, 'charge' => 0],
  '500' => ['city' => 'Secunderabad', 'state' => 'Telangana', 'days' => 2, 'charge' => 50],
];

$prefix = substr($pincode, 0, 3);
$info = $serviceable[$prefix] ?? null;

if ($info) {
  $deliveryDate = date('d M Y', strtotime('+' . $info['days'] . ' days'));
  echo json_encode([
    'deliverable' => true,
    'message' => "Delivery available in {$info['city']}, {$info['state']}",
    'delivery_date' => $deliveryDate,
    'shipping_charge' => $info['charge'],
    'free_shipping' => $info['charge'] === 0,
  ], JSON_UNESCAPED_UNICODE);
} else {
  echo json_encode([
    'deliverable' => false,
    'message' => 'Delivery not available to this pincode. Contact us for assistance.',
    'delivery_date' => null,
    'shipping_charge' => 0,
  ], JSON_UNESCAPED_UNICODE);
}
exit;