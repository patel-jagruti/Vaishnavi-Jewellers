<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function db(): PDO {
  static $pdo = null;
  static $catalogSeeded = false;
  if ($pdo instanceof PDO && $catalogSeeded) return $pdo;

  $cfg = require __DIR__ . '/config.php';
  $dbCfg = $cfg['db'];

  try {
    $pdo = new PDO(
      $dbCfg['dsn'],
      $dbCfg['user'],
      $dbCfg['pass'],
      [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
      ]
    );

    // Ensure catalog is seeded on first connect
    if (!$catalogSeeded) {
      require_once __DIR__ . '/catalog_db.php';
      ensure_catalog_seeded($pdo);
      ensure_addresses_schema($pdo);
      ensure_orders_schema($pdo);
      ensure_appointments_schema($pdo);
      $catalogSeeded = true;
    }

    return $pdo;
  } catch (PDOException $e) {
    // Try creating database if it doesn't exist
    $dsn = $dbCfg['dsn'];
    $dbName = 'visnavi_jewellers';
    $dsnNoDb = preg_replace('/dbname=[^;]+/', '', $dsn);
    $pdo = new PDO(
      $dsnNoDb,
      $dbCfg['user'],
      $dbCfg['pass'],
      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbName`");

    // Reconnect with database
    $pdo = new PDO(
      $dsn,
      $dbCfg['user'],
      $dbCfg['pass'],
      [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
      ]
    );

    // Ensure catalog is seeded
    require_once __DIR__ . '/catalog_db.php';
    ensure_catalog_seeded($pdo);
    ensure_addresses_schema($pdo);
    ensure_orders_schema($pdo);

    return $pdo;
  }
}

