<?php
declare(strict_types=1);

/**
 * EMI Calculator API
 * Calculate EMI for product purchase
 */

header('Content-Type: application/json; charset=utf-8');

$amount = (float)($_GET['amount'] ?? 0);
$months = (int)($_GET['months'] ?? 3);

if ($amount <= 0 || $months <= 0) {
  echo json_encode(['error' => 'Invalid amount or months'], JSON_UNESCAPED_UNICODE);
  exit;
}

// EMI interest rate (example - in production, integrate with payment provider)
$rates = [
  3 => 0,      // 3 months 0% EMI
  6 => 0,      // 6 months 0% EMI
  9 => 5,     // 9 months 5%
  12 => 8,     // 12 months 8%
];

$annualRate = $rates[$months] ?? 8;
$monthlyRate = $annualRate / 12 / 100;

if ($monthlyRate === 0) {
  $emi = $amount / $months;
  $total = $amount;
} else {
  // EMI = P * r * (1+r)^n / ((1+r)^n - 1)
  $r = $monthlyRate;
  $n = $months;
  $factor = pow(1 + $r, $n);
  $emi = $amount * $r * $factor / ($factor - 1);
  $total = $emi * $months;
}

echo json_encode([
  'amount' => $amount,
  'months' => $months,
  'annual_rate' => $annualRate,
  'monthly_emi' => round($emi, 2),
  'total_interest' => round($total - $amount, 2),
  'total' => round($total, 2),
], JSON_UNESCAPED_UNICODE);
exit;