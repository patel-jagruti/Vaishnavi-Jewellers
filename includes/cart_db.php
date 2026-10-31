<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/functions.php';

/**
 * DB-backed cart + wishlist helpers.
 *
 * Supports:
 * - logged-in users: cart.user_id = users.id
 * - guests: cart.session_id = a stable guest id stored in session
 *
 * IMPORTANT:
 * - The rest of the app must call ensure_session() before using this.
 */

function guest_session_id(): string {
  ensure_session();

  // Primary: stable long-lived cookie
  if (!empty($_COOKIE['vj_guest_sid'])) {
    $_SESSION['guest_sid'] = $_COOKIE['vj_guest_sid'];
    return (string)$_COOKIE['vj_guest_sid'];
  }

  // Secondary: session fallback
  if (!empty($_SESSION['guest_sid'])) {
    $sid = $_SESSION['guest_sid'];
    @setcookie('vj_guest_sid', $sid, time() + (3600 * 24 * 365), '/');
    return $sid;
  }

  // Generate new stable guest ID
  $newSid = 'g_' . bin2hex(random_bytes(12));
  $_SESSION['guest_sid'] = $newSid;
  $_COOKIE['vj_guest_sid'] = $newSid; // Force immediate availability
  @setcookie('vj_guest_sid', $newSid, time() + (3600 * 24 * 365), '/'); // 1 year
  
  return $newSid;
}

function current_user_id(): ?int {
  ensure_session();
  if (!empty($_SESSION['user']['id'])) {
    return (int)$_SESSION['user']['id'];
  }
  return null;
}

function cart_key(): array {
  $uid = current_user_id();
  if ($uid !== null) {
    return ['user_id' => $uid, 'session_id' => null];
  }

  return ['user_id' => null, 'session_id' => guest_session_id()];
}

function wishlist_key(): array {
  // Same concept as cart.
  return cart_key();
}

function get_or_create_cart(PDO $pdo): int {
  $key = cart_key();

  // Ensure cart table exists.
  ensure_cart_table($pdo);

  if ($key['user_id'] !== null) {
    $stmt = $pdo->prepare('SELECT id FROM cart WHERE user_id = :uid ORDER BY id DESC LIMIT 1');
    $stmt->execute([':uid' => $key['user_id']]);
    $row = $stmt->fetch();
    if ($row && isset($row['id'])) return (int)$row['id'];

    $stmt = $pdo->prepare('INSERT INTO cart (user_id, session_id) VALUES (:uid, NULL)');
    $stmt->execute([':uid' => $key['user_id']]);
    return (int)$pdo->lastInsertId();
  }

  $sid = (string)$key['session_id'];
  $stmt = $pdo->prepare('SELECT id FROM cart WHERE session_id = :sid ORDER BY id DESC LIMIT 1');
  $stmt->execute([':sid' => $sid]);
  $row = $stmt->fetch();
  if ($row && isset($row['id'])) return (int)$row['id'];

  $stmt = $pdo->prepare('INSERT INTO cart (user_id, session_id) VALUES (NULL, :sid)');
  $stmt->execute([':sid' => $sid]);
  return (int)$pdo->lastInsertId();
}

function ensure_cart_table(PDO $pdo): void {
  // Ensure cart table exists with proper schema
  $cartExists = true;
  try {
    $pdo->query('SELECT 1 FROM cart LIMIT 1');
  } catch (Throwable $e) {
    $cartExists = false;
  }

  if (!$cartExists) {
    $pdo->exec('CREATE TABLE IF NOT EXISTS cart (
      id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
      user_id BIGINT UNSIGNED NULL,
      session_id VARCHAR(120) NULL,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
  }

  // Ensure cart_items table exists
  $cartItemsExists = true;
  try {
    $pdo->query('SELECT 1 FROM cart_items LIMIT 1');
  } catch (Throwable $e) {
    $cartItemsExists = false;
  }

  if (!$cartItemsExists) {
    $pdo->exec('CREATE TABLE IF NOT EXISTS cart_items (
      id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
      cart_id BIGINT UNSIGNED NOT NULL,
      product_id BIGINT UNSIGNED NOT NULL,
      variant_id BIGINT UNSIGNED NULL,
      quantity INT NOT NULL DEFAULT 1,
      unit_price DECIMAL(12,2) NOT NULL,
      gold_price_locked DECIMAL(12,2) NULL,
      gold_price_locked_at TIMESTAMP NULL,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      UNIQUE KEY uniq_cart_item (cart_id, product_id, variant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
  } else {
    // Add gold price lock columns if table exists
    try {
      $pdo->query('SELECT gold_price_locked FROM cart_items LIMIT 1');
    } catch (Throwable $e) {
      $pdo->exec('ALTER TABLE cart_items ADD COLUMN gold_price_locked DECIMAL(12,2) NULL AFTER unit_price');
      $pdo->exec('ALTER TABLE cart_items ADD COLUMN gold_price_locked_at TIMESTAMP NULL AFTER gold_price_locked');
    }
  }
}

function get_cart_items(PDO $pdo): array {
  $cartId = get_or_create_cart($pdo);

  // Query with product image (from product_images table)
  $stmt = $pdo->prepare(
    'SELECT c.id AS cart_item_id,
            c.product_id,
            c.variant_id,
            c.quantity,
            c.unit_price,
            c.gold_price_locked,
            c.gold_price_locked_at,
            p.name AS product_name,
            p.sku AS sku,
            p.base_price,
            p.price_type,
            p.purity,
            p.making_charge_percent,
            p.weight_grams,
            p.discount_percent,
            (
              SELECT pi.image_url
              FROM product_images pi
              WHERE pi.product_id = c.product_id
              ORDER BY pi.is_primary DESC, pi.display_order ASC, pi.id ASC
              LIMIT 1
            ) AS primary_image_url
     FROM cart_items c
     JOIN products p ON p.id = c.product_id
     WHERE c.cart_id = :cid
       AND p.is_active = 1
     ORDER BY c.id DESC'
  );
  $stmt->execute([':cid' => $cartId]);
  $items = $stmt->fetchAll();

  // Calculate effective price for each item (locked price if valid, otherwise current price)
  foreach ($items as &$item) {
    $currentPrice = price_now($item);
    $effectivePrice = $currentPrice;
    if (!empty($item['gold_price_locked_at'])) {
      $lockTime = strtotime($item['gold_price_locked_at']);
      $now = time();
      if ($now - $lockTime < GOLD_PRICE_LOCK_DURATION) {
        $effectivePrice = (float)$item['gold_price_locked'];
      }
    }
    $item['effective_price'] = $effectivePrice;
  }

  return $items;
}

function cart_count(PDO $pdo): int {
  $cartId = get_or_create_cart($pdo);
  $stmt = $pdo->prepare('SELECT COALESCE(SUM(quantity),0) AS cnt FROM cart_items WHERE cart_id = :cid');
  $stmt->execute([':cid' => $cartId]);
  $row = $stmt->fetch();
  return (int)($row['cnt'] ?? 0);
}

// Lock duration in seconds (5 minutes)
define('GOLD_PRICE_LOCK_DURATION', 300);

function cart_add(PDO $pdo, string $productId, int $qty, ?string $variantId = null): void {
  $qty = max(1, min(9999, $qty));

  // First ensure catalog is seeded with products
  ensure_catalog_seeded($pdo);

  // Ensure product exists and is active.
  $p = $pdo->prepare('SELECT * FROM products WHERE id = :pid AND is_active=1 LIMIT 1');
  $p->execute([':pid' => $productId]);
  $prod = $p->fetch();
  if (!$prod) {
    throw new RuntimeException('Invalid product_id');
  }

  // Calculate current price (after discount and dynamic gold rate conversions)
  $unitPrice = price_now($prod);

  $cartId = get_or_create_cart($pdo);
  $variantId = $variantId !== null && $variantId !== '' ? $variantId : null;

  // Upsert by unique key (cart_id, product_id, variant_id). variant_id can be NULL.
  if ($variantId === null) {
    $stmt = $pdo->prepare(
      'SELECT id, quantity, gold_price_locked, gold_price_locked_at FROM cart_items
       WHERE cart_id = :cid AND product_id = :pid AND variant_id IS NULL
       LIMIT 1'
    );
    $stmt->execute([':cid' => $cartId, ':pid' => $productId]);
  } else {
    $stmt = $pdo->prepare(
      'SELECT id, quantity, gold_price_locked, gold_price_locked_at FROM cart_items
       WHERE cart_id = :cid AND product_id = :pid AND variant_id = :vid
       LIMIT 1'
    );
    $stmt->execute([':cid' => $cartId, ':pid' => $productId, ':vid' => $variantId]);
  }
  $row = $stmt->fetch();

  // Check if existing item has valid locked price (within 5 minutes)
  $hasValidLock = false;
  if ($row && isset($row['id']) && !empty($row['gold_price_locked_at'])) {
    $lockTime = strtotime($row['gold_price_locked_at']);
    $now = time();
    if ($now - $lockTime < GOLD_PRICE_LOCK_DURATION) {
      $hasValidLock = true;
    }
  }

  if ($row && isset($row['id'])) {
    $newQty = (int)$row['quantity'] + $qty;
    // Keep existing locked price if valid, otherwise use current price
    $priceToUse = $hasValidLock ? $row['gold_price_locked'] : $unitPrice;
    $lockedAt = $hasValidLock ? $row['gold_price_locked_at'] : date('Y-m-d H:i:s');

    $upd = $pdo->prepare('UPDATE cart_items SET quantity = :q, unit_price = :up, gold_price_locked = :lp, gold_price_locked_at = :la WHERE id = :id');
    $upd->execute([
      ':q' => $newQty,
      ':up' => $priceToUse,
      ':lp' => $hasValidLock ? $row['gold_price_locked'] : $unitPrice,
      ':la' => $lockedAt,
      ':id' => (int)$row['id']
    ]);
    return;
  }

  // Insert new item with locked gold price
  $ins = $pdo->prepare(
    'INSERT INTO cart_items (cart_id, product_id, variant_id, quantity, unit_price, gold_price_locked, gold_price_locked_at)
     VALUES (:cid, :pid, :vid, :q, :up, :lp, :la)'
  );
  $ins->execute([
    ':cid' => $cartId,
    ':pid' => $productId,
    ':vid' => $variantId,
    ':q' => $qty,
    ':up' => $unitPrice,
    ':lp' => $unitPrice,
    ':la' => date('Y-m-d H:i:s'),
  ]);
}

// Get effective price for cart item (returns locked price if valid, otherwise current price)
function cart_get_effective_price(PDO $pdo, int $cartItemId): float {
  $stmt = $pdo->prepare('SELECT gold_price_locked, gold_price_locked_at, unit_price FROM cart_items WHERE id = :id');
  $stmt->execute([':id' => $cartItemId]);
  $row = $stmt->fetch();

  if (!$row) return 0;

  // If locked within 5 minutes, use locked price
  if (!empty($row['gold_price_locked_at'])) {
    $lockTime = strtotime($row['gold_price_locked_at']);
    $now = time();
    if ($now - $lockTime < GOLD_PRICE_LOCK_DURATION) {
      return (float)$row['gold_price_locked'];
    }
  }

  // Otherwise use current price
  return (float)$row['unit_price'];
}

// Check if cart price is locked and valid
function cart_is_price_locked(PDO $pdo, int $cartItemId): bool {
  $stmt = $pdo->prepare('SELECT gold_price_locked_at FROM cart_items WHERE id = :id');
  $stmt->execute([':id' => $cartItemId]);
  $row = $stmt->fetch();

  if (!$row || empty($row['gold_price_locked_at'])) return false;

  $lockTime = strtotime($row['gold_price_locked_at']);
  $now = time();
  return ($now - $lockTime < GOLD_PRICE_LOCK_DURATION);
}

function cart_remove(PDO $pdo, string $productId, ?string $variantId = null): void {
  $cartId = get_or_create_cart($pdo);
  $variantId = $variantId !== null && $variantId !== '' ? $variantId : null;

  if ($variantId === null) {
    $stmt = $pdo->prepare('DELETE FROM cart_items WHERE cart_id = :cid AND product_id = :pid AND variant_id IS NULL');
    $stmt->execute([':cid' => $cartId, ':pid' => $productId]);
  } else {
    $stmt = $pdo->prepare('DELETE FROM cart_items WHERE cart_id = :cid AND product_id = :pid AND variant_id = :vid');
    $stmt->execute([':cid' => $cartId, ':pid' => $productId, ':vid' => $variantId]);
  }
}

function cart_set_qty(PDO $pdo, string $productId, int $qty, ?string $variantId = null): void {
  $qty = max(0, min(9999, $qty));
  if ($qty <= 0) {
    cart_remove($pdo, $productId, $variantId);
    return;
  }

  $cartId = get_or_create_cart($pdo);
  $variantId = $variantId !== null && $variantId !== '' ? $variantId : null;

  if ($variantId === null) {
    $stmt = $pdo->prepare('UPDATE cart_items SET quantity = :q WHERE cart_id = :cid AND product_id = :pid AND variant_id IS NULL');
    $stmt->execute([':q' => $qty, ':cid' => $cartId, ':pid' => $productId]);
  } else {
    $stmt = $pdo->prepare('UPDATE cart_items SET quantity = :q WHERE cart_id = :cid AND product_id = :pid AND variant_id = :vid');
    $stmt->execute([':q' => $qty, ':cid' => $cartId, ':pid' => $productId, ':vid' => $variantId]);
  }
}

// Wishlist
function ensure_wishlist_table(PDO $pdo): void {
  $exists = true;
  try {
    $pdo->query('SELECT 1 FROM wishlists LIMIT 1');
  } catch (Throwable $e) {
    $exists = false;
  }

  if (!$exists) {
    // Check if users table exists first
    try {
      $pdo->query('SELECT 1 FROM users LIMIT 1');
    } catch (Throwable $e) {
      $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(190) NOT NULL UNIQUE,
        phone VARCHAR(30) NULL,
        name VARCHAR(150) NULL,
        password_hash VARCHAR(255) NOT NULL,
        is_verified TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS wishlists (
      id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
      user_id BIGINT UNSIGNED NULL,
      session_id VARCHAR(120) NULL,
      product_id BIGINT UNSIGNED NOT NULL,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      UNIQUE KEY uniq_wishlist_line (user_id, session_id, product_id),
      CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
      CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
  }
}

function get_or_create_wishlist(PDO $pdo): int {
  ensure_wishlist_table($pdo);
  return 0;
}

function wishlist_toggle(PDO $pdo, string $productId, string $action): void {
  ensure_wishlist_table($pdo);
  $uid = current_user_id();
  $sid = guest_session_id();

  // Ensure product exists.
  $p = $pdo->prepare('SELECT id FROM products WHERE id = :pid AND is_active=1 LIMIT 1');
  $p->execute([':pid' => $productId]);
  if (!$p->fetch()) {
    throw new RuntimeException('Invalid product_id');
  }

  if ($action === 'remove') {
    if ($uid !== null) {
      $stmt = $pdo->prepare('DELETE FROM wishlists WHERE user_id = :uid AND product_id = :pid');
      $stmt->execute([':uid' => $uid, ':pid' => $productId]);
    } else {
      $stmt = $pdo->prepare('DELETE FROM wishlists WHERE session_id = :sid AND product_id = :pid');
      $stmt->execute([':sid' => $sid, ':pid' => $productId]);
    }
    return;
  }

  // add
  // Prevent duplicate guest/user rows in MySQL since UNIQUE KEY allows multiple NULLs
  if ($uid !== null) {
    $chk = $pdo->prepare('SELECT 1 FROM wishlists WHERE user_id = :uid AND product_id = :pid LIMIT 1');
    $chk->execute([':uid' => $uid, ':pid' => $productId]);
  } else {
    $chk = $pdo->prepare('SELECT 1 FROM wishlists WHERE session_id = :sid AND product_id = :pid LIMIT 1');
    $chk->execute([':sid' => $sid, ':pid' => $productId]);
  }
  if ($chk->fetch()) {
    return; // Already in wishlist, do not insert duplicate
  }

  if ($uid !== null) {
    // Upsert-like: insert ignore using unique key (user_id, session_id, product_id)
    $ins = $pdo->prepare('INSERT INTO wishlists (user_id, session_id, product_id) VALUES (:uid, NULL, :pid)');
    try {
      $ins->execute([':uid' => $uid, ':pid' => $productId]);
    } catch (Throwable $e) {
      // ignore duplicates
    }
  } else {
    $ins = $pdo->prepare('INSERT INTO wishlists (user_id, session_id, product_id) VALUES (NULL, :sid, :pid)');
    try {
      $ins->execute([':sid' => $sid, ':pid' => $productId]);
    } catch (Throwable $e) {
      // ignore duplicates
    }
  }
}

function wishlist_count(PDO $pdo): int {
  ensure_wishlist_table($pdo);
  $uid = current_user_id();
  if ($uid !== null) {
    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM wishlists WHERE user_id = :uid');
    $stmt->execute([':uid' => $uid]);
  } else {
    $sid = guest_session_id();
    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM wishlists WHERE session_id = :sid');
    $stmt->execute([':sid' => $sid]);
  }

  $row = $stmt->fetch();
  return (int)($row['cnt'] ?? 0);
}

function wishlist_items(PDO $pdo): array {
  ensure_wishlist_table($pdo);
  $uid = current_user_id();
  if ($uid !== null) {
    $stmt = $pdo->prepare(
      'SELECT p.*,
              c.name AS category_name,
              (
                SELECT pi.image_url
                FROM product_images pi
                WHERE pi.product_id = p.id
                ORDER BY pi.is_primary DESC, pi.display_order ASC, pi.id ASC
                LIMIT 1
              ) AS primary_image_url
       FROM wishlists w
       JOIN products p ON p.id = w.product_id
       LEFT JOIN categories c ON c.id = p.category_id
       WHERE w.user_id = :uid AND p.is_active = 1
       ORDER BY w.id DESC'
    );
    $stmt->execute([':uid' => $uid]);
  } else {
    $sid = guest_session_id();
    $stmt = $pdo->prepare(
      'SELECT p.*,
              c.name AS category_name,
              (
                SELECT pi.image_url
                FROM product_images pi
                WHERE pi.product_id = p.id
                ORDER BY pi.is_primary DESC, pi.display_order ASC, pi.id ASC
                LIMIT 1
              ) AS primary_image_url
       FROM wishlists w
       JOIN products p ON p.id = w.product_id
       LEFT JOIN categories c ON c.id = p.category_id
       WHERE w.session_id = :sid AND p.is_active = 1
       ORDER BY w.id DESC'
    );
    $stmt->execute([':sid' => $sid]);
  }

  return $stmt->fetchAll();
}

/**
 * Migrates all guest cart items and wishlist items to the authenticated user's ID.
 */
function migrate_guest_to_user(PDO $pdo, int $userId, string $guestSid): void {
  ensure_cart_table($pdo);
  ensure_wishlist_table($pdo);

  // --- Migrate Cart ---
  // Get guest cart ID
  $stmt = $pdo->prepare('SELECT id FROM cart WHERE session_id = :sid LIMIT 1');
  $stmt->execute([':sid' => $guestSid]);
  $guestCartId = $stmt->fetchColumn();

  if ($guestCartId) {
    $guestCartId = (int)$guestCartId;
    
    // Get user cart ID
    $stmt = $pdo->prepare('SELECT id FROM cart WHERE user_id = :uid LIMIT 1');
    $stmt->execute([':uid' => $userId]);
    $userCartId = $stmt->fetchColumn();

    if ($userCartId) {
      $userCartId = (int)$userCartId;
      // Merge guest cart items into user cart
      $stmt = $pdo->prepare('SELECT product_id, variant_id, quantity, unit_price, gold_price_locked, gold_price_locked_at FROM cart_items WHERE cart_id = :cid');
      $stmt->execute([':cid' => $guestCartId]);
      $guestItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach ($guestItems as $item) {
        $pid = $item['product_id'];
        $vid = $item['variant_id'];
        $qty = (int)$item['quantity'];

        // Check if item exists in user cart
        if ($vid === null) {
          $chk = $pdo->prepare('SELECT id, quantity FROM cart_items WHERE cart_id = :cid AND product_id = :pid AND variant_id IS NULL LIMIT 1');
          $chk->execute([':cid' => $userCartId, ':pid' => $pid]);
        } else {
          $chk = $pdo->prepare('SELECT id, quantity FROM cart_items WHERE cart_id = :cid AND product_id = :pid AND variant_id = :vid LIMIT 1');
          $chk->execute([':cid' => $userCartId, ':pid' => $pid, ':vid' => $vid]);
        }
        $existing = $chk->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
          // Update quantity
          $newQty = (int)$existing['quantity'] + $qty;
          $upd = $pdo->prepare('UPDATE cart_items SET quantity = :q WHERE id = :id');
          $upd->execute([':q' => $newQty, ':id' => (int)$existing['id']]);
        } else {
          // Move item to user cart
          $ins = $pdo->prepare('INSERT INTO cart_items (cart_id, product_id, variant_id, quantity, unit_price, gold_price_locked, gold_price_locked_at) VALUES (:cid, :pid, :vid, :q, :up, :lp, :la)');
          $ins->execute([
            ':cid' => $userCartId,
            ':pid' => $pid,
            ':vid' => $vid,
            ':q' => $qty,
            ':up' => $item['unit_price'],
            ':lp' => $item['gold_price_locked'],
            ':la' => $item['gold_price_locked_at']
          ]);
        }
      }
      // Delete guest cart items and guest cart
      $pdo->prepare('DELETE FROM cart_items WHERE cart_id = :cid')->execute([':cid' => $guestCartId]);
      $pdo->prepare('DELETE FROM cart WHERE id = :cid')->execute([':cid' => $guestCartId]);
    } else {
      // No user cart yet, simply associate the guest cart with user
      $upd = $pdo->prepare('UPDATE cart SET user_id = :uid, session_id = NULL WHERE id = :cid');
      $upd->execute([':uid' => $userId, ':cid' => $guestCartId]);
    }
  }

  // --- Migrate Wishlist ---
  $stmt = $pdo->prepare('SELECT product_id FROM wishlists WHERE session_id = :sid');
  $stmt->execute([':sid' => $guestSid]);
  $guestWishItems = $stmt->fetchAll(PDO::FETCH_COLUMN);

  foreach ($guestWishItems as $pid) {
    // Check if user already has it
    $chk = $pdo->prepare('SELECT 1 FROM wishlists WHERE user_id = :uid AND product_id = :pid LIMIT 1');
    $chk->execute([':uid' => $userId, ':pid' => $pid]);
    if (!$chk->fetchColumn()) {
      $ins = $pdo->prepare('INSERT INTO wishlists (user_id, session_id, product_id) VALUES (:uid, NULL, :pid)');
      try {
        $ins->execute([':uid' => $userId, ':pid' => $pid]);
      } catch (Throwable $e) {}
    }
  }
  // Delete guest wishlist items
  $pdo->prepare('DELETE FROM wishlists WHERE session_id = :sid')->execute([':sid' => $guestSid]);
}

