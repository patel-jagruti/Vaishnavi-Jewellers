<?php
declare(strict_types=1);

// Production-ready base URL handling for local paths with spaces.
// Your app lives under: /visnavi jwallers/
// In URLs spaces must be URL-encoded as %20.

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $protocol . $host . '/visnavi%20jwallers');

define('ASSET_URL', BASE_URL . '/assets');

define('APP_ROUTE_URL', BASE_URL);

function asset(string $pathFromAssets): string {
  if (strpos($pathFromAssets, 'http://') === 0 || strpos($pathFromAssets, 'https://') === 0) {
    return $pathFromAssets;
  }
  if (strpos($pathFromAssets, '/visnavi%20jwallers') === 0) {
    return $pathFromAssets;
  }
  if (strpos($pathFromAssets, '/visnavi jwallers') === 0) {
    return str_replace('/visnavi jwallers', '/visnavi%20jwallers', $pathFromAssets);
  }
  $trimmed = ltrim($pathFromAssets, '/');
  if (strpos($trimmed, 'assets/') === 0) {
    return BASE_URL . '/' . $trimmed;
  }
  return ASSET_URL . '/' . $trimmed;
}

function route(string $pathFromRoot): string {
  $pathFromRoot = '/' . ltrim($pathFromRoot, '/');
  return APP_ROUTE_URL . $pathFromRoot;
}

