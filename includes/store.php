<?php
declare(strict_types=1);

// Lightweight persistence without MySQL/Laravel.
// Uses SQLite (recommended) when PDO Sqlite is enabled.

function store_dir(): string {
  return dirname(__DIR__) . '/storage';
}

function db_path(): string {
  return store_dir() . '/store.sqlite';
}

function ensure_storage_dir(): void {
  $dir = store_dir();
  if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
  }
}

function get_pdo(): PDO {
  ensure_storage_dir();

  $path = db_path();

  try {
    $pdo = new PDO('sqlite:' . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create tables once
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      order_code TEXT NOT NULL,
      customer_name TEXT NOT NULL,
      customer_email TEXT,
      customer_phone TEXT,
      shipping_address TEXT,
      items_json TEXT NOT NULL,
      subtotal REAL NOT NULL,
      shipping REAL NOT NULL,
      total REAL NOT NULL,
      payment_method TEXT NOT NULL,
      payment_status TEXT NOT NULL,
      order_status TEXT NOT NULL,
      created_at TEXT NOT NULL
    );");

    return $pdo;
  } catch (Throwable $e) {
    // Store the detailed error for other pages to show a friendly message.
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
      ini_set('session.cookie_path', '/');
      ini_set('session.cookie_httponly', '1');
      @session_start();
    }

    $_SESSION['db_error'] = [
      'message' => $e->getMessage(),
      'hint' => 'Make sure PHP extension pdo_sqlite/sqlite3 is enabled, and that c:/xampp/htdocs/visnavi jwallers/storage is writable.',
    ];

    throw $e;
  }
}


