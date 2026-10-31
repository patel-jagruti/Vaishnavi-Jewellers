<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// ---- Config ----
$cacheDir = __DIR__ . '/../storage/cache';
$cacheTtlSeconds = 3600; // 1 hour

// TODO: Replace with your real provider URL.
// If you already have a known base endpoint, update $rateApiUrl.
$rateApiUrl = 'https://api.yesgoldapi.io/api/v1/rates/gold-silver';
$rateApiKey = 'yesgoldapi-d6a80910f68c928fbbce22acccacfe26-io';

function json_out(array $payload, int $statusCode = 200): void {
  http_response_code($statusCode);
  echo json_encode($payload, JSON_UNESCAPED_SLASHES);
  exit;
}

function read_cache(string $file): ?array {
  if (!is_file($file)) return null;
  $raw = @file_get_contents($file);
  if ($raw === false || $raw === '') return null;
  $data = json_decode($raw, true);
  return is_array($data) ? $data : null;
}

function write_cache(string $file, array $data): void {
  $tmp = $file . '.tmp';
  @file_put_contents($tmp, json_encode($data));
  @rename($tmp, $file);
}

function now_ist_time(): string {
  $dt = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
  return $dt->format('d M Y, h:i A');
}

try {
  if (!function_exists('get_gold_rates')) {
    require_once __DIR__ . '/../includes/helpers.php';
  }
  
  $rates = get_gold_rates();
  
  $payload = [
    'gold_24' => ['rate_per_g' => (float)$rates['gold_24k'], 'previous_per_g' => (float)$rates['gold_24k'] * 0.995],
    'gold_22' => ['rate_per_g' => (float)$rates['gold_22k'], 'previous_per_g' => (float)$rates['gold_22k'] * 0.997],
    'gold_18' => ['rate_per_g' => (float)$rates['gold_18k'], 'previous_per_g' => (float)$rates['gold_18k'] * 1.002],
    'silver' => ['rate_per_g' => (float)$rates['silver'], 'previous_per_g' => (float)$rates['silver'] * 0.988]
  ];
  
  json_out([
    'success' => true,
    'data' => $payload,
    'cached' => false,
    'fetched_at' => now_ist_time(),
  ]);
} catch (Throwable $e) {
  json_out(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}

