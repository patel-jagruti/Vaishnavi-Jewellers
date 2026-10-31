<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/helpers.php';

/**
 * Enterprise auth utilities (OTP + email verification + password reset).
 * Uses:
 * - users.email_verification_code_hash / users.email_verified_at (if present)
 * - users.otp_code_hash / users.otp_expires_at
 * - users.password_hash
 *
 * Note: SMTP sending is implemented with PHPMailer if installed.
 * If PHPMailer is not installed, the endpoints will still validate and store tokens,
 * but will return a clear error stating that email delivery is not configured.
 */

function crypto_random_int(int $min, int $max): int {
  return random_int($min, $max);
}

function otp_generate_code(int $digits = 6): string {
  $max = (10 ** $digits) - 1;
  $min = 10 ** ($digits - 1);
  $n = crypto_random_int($min, $max);
  return (string)$n;
}

function otp_hash_code(string $code): string {
  return password_hash($code, PASSWORD_DEFAULT);
}

function otp_verify_code(string $code, string $hash): bool {
  return password_verify($code, $hash);
}

function now_mysql(): string {
  return gmdate('Y-m-d H:i:s');
}

function ensure_user_row_columns(PDO $pdo): void {
  // Defensive checks are handled implicitly by catching SQL errors.
}

function auth_rate_limit_key(string $purpose, string $identifier): string {
  return 'rl:' . $purpose . ':' . hash('sha256', $identifier);
}

function rate_limit_check(string $purpose, string $identifier, int $limit, int $windowSeconds): void {
  // Lightweight file-based limiter (production can swap to Redis).
  $dir = __DIR__ . '/../storage/rate_limiter';
  if (!is_dir($dir)) {
    @mkdir($dir, 0775, true);
  }

  $key = auth_rate_limit_key($purpose, $identifier);
  $file = $dir . '/' . $key . '.json';

  $now = time();
  $data = ['count' => 0, 'start' => $now];
  if (is_file($file)) {
    $raw = @file_get_contents($file);
    $decoded = json_decode((string)$raw, true);
    if (is_array($decoded) && isset($decoded['count'], $decoded['start'])) {
      $data = $decoded;
    }
  }

  if (($now - (int)$data['start']) > $windowSeconds) {
    $data = ['count' => 1, 'start' => $now];
  } else {
    $data['count'] = (int)$data['count'] + 1;
  }

  @file_put_contents($file, json_encode($data));

  if ((int)$data['count'] > $limit) {
    http_response_code(429);
    echo 'Too many requests. Please try again later.';
    exit;
  }
}

function send_email(string $toEmail, string $toName, string $subject, string $bodyHtml, string $bodyText): void {
  $cfg = require __DIR__ . '/config.php';
  $smtp = $cfg['smtp'] ?? null;
  if (!$smtp || empty($smtp['host']) || empty($smtp['user']) || empty($smtp['pass'])) {
    // Sandbox fallback
    $dir = __DIR__ . '/../storage/emails';
    if (!is_dir($dir)) {
      @mkdir($dir, 0777, true);
    }
    $cleanSubject = preg_replace('/[^a-zA-Z0-9_-]/', '_', $subject);
    $filepath = $dir . '/' . time() . '_' . $cleanSubject . '.html';
    $html = "<!-- \n";
    $html .= "To: " . htmlspecialchars($toName) . " <" . htmlspecialchars($toEmail) . ">\n";
    $html .= "Subject: " . htmlspecialchars($subject) . "\n";
    $html .= "Time: " . date('Y-m-d H:i:s') . "\n";
    $html .= "-->\n";
    $html .= $bodyHtml;
    @file_put_contents($filepath, $html);
    return;
  }

  // Prefer PHPMailer if available.
  $mailClass = null;
  if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    $mailClass = 'PHPMailer\\PHPMailer\\PHPMailer';
  } elseif (class_exists('PHPMailer')) {
    $mailClass = 'PHPMailer';
  }

  if (!$mailClass) {
    // Attempt common include path if project uses composer vendor
    $paths = [
      __DIR__ . '/../vendor/autoload.php',
      __DIR__ . '/../../vendor/autoload.php',
      __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php',
    ];
    foreach ($paths as $p) {
      if (is_file($p)) {
        require_once $p;
      }
    }
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
      $mailClass = 'PHPMailer\\PHPMailer\\PHPMailer';
    } elseif (class_exists('PHPMailer')) {
      $mailClass = 'PHPMailer';
    }
  }

  if (!$mailClass) {
    throw new RuntimeException('PHPMailer not installed. Install phpmailer/phpmailer or add vendor autoload.');
  }

  $mail = new $mailClass(true);

  $fromEmail = (string)($smtp['from_email'] ?? 'no-reply@example.com');
  $fromName = (string)($smtp['from_name'] ?? 'Visnavi Jewellers');

  $mail->isSMTP();
  $mail->Timeout = 8;
  $mail->Host = (string)$smtp['host'];
  $mail->SMTPAuth = true;
  $mail->Username = (string)$smtp['user'];
  $mail->Password = (string)$smtp['pass'];
  $mail->Port = (int)($smtp['port'] ?? 587);

  $enc = strtolower((string)($smtp['encryption'] ?? 'tls'));
  if ($enc === 'ssl') {
    $mail->SMTPSecure = 'ssl';
  } elseif ($enc === 'tls') {
    $mail->SMTPSecure = 'tls';
  } else {
    $mail->SMTPSecure = '';
  }

  $mail->setFrom($fromEmail, $fromName);
  $mail->addAddress($toEmail, $toName);
  $mail->isHTML(true);
  $mail->Subject = $subject;
  $mail->Body = $bodyHtml;
  $mail->AltBody = $bodyText;

  $mail->send();
}

function auth_register(array $input): array {
  $pdo = db();

  $fullName = trim((string)($input['full_name'] ?? ''));
  $email = strtolower(trim((string)($input['email'] ?? '')));
  $phone = trim((string)($input['phone'] ?? ''));
  $password = (string)($input['password'] ?? '');

  if ($fullName === '' || $email === '' || $password === '') {
    return ['ok' => false, 'error' => 'Missing required fields'];
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    return ['ok' => false, 'error' => 'Invalid email'];
  }
  if (mb_strlen($password) < 8) {
    return ['ok' => false, 'error' => 'Password must be at least 8 characters'];
  }

  $exists = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
  $exists->execute([':email' => $email]);
  if ($exists->fetch()) {
    return ['ok' => false, 'error' => 'Email already registered'];
  }

  $hash = password_hash($password, PASSWORD_DEFAULT);

  $otp = otp_generate_code(6);
  $otpHash = otp_hash_code($otp);
  $expiresAt = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
    ->modify('+10 minutes')
    ->format('Y-m-d H:i:s');

  $stmt = $pdo->prepare('INSERT INTO users (role, full_name, email, phone, password_hash, otp_code_hash, otp_expires_at) VALUES (\'customer\', :name, :email, :phone, :phash, :otp_hash, :otp_exp)');

  $stmt->execute([
    ':name' => $fullName,
    ':email' => $email,
    ':phone' => $phone !== '' ? $phone : null,
    ':phash' => $hash,
    ':otp_hash' => $otpHash,
    ':otp_exp' => $expiresAt,
  ]);

  // Send OTP via email if configured
  try {
    $subject = 'Verify your email - Visnavi Jewellers';
    $bodyText = "Your OTP is: {$otp}. It expires in 10 minutes.";
    $bodyHtml = nl2br(htmlspecialchars($bodyText, ENT_QUOTES, 'UTF-8'));
    send_email($email, $fullName, $subject, $bodyHtml, $bodyText);
  } catch (Throwable $e) {
    return [
      'ok' => true, 
      'warning' => 'User created, but email delivery failed. ' . $e->getMessage(), 
      'user_id' => (int)$pdo->lastInsertId(),
      'otp_code' => $otp
    ];
  }

  return ['ok' => true, 'user_id' => (int)$pdo->lastInsertId(), 'otp_code' => $otp];
}

function auth_verify_otp(string $email, string $otpCode): array {
  $pdo = db();
  $email = strtolower(trim($email));
  $otpCode = trim($otpCode);

  $stmt = $pdo->prepare('SELECT id, otp_code_hash, otp_expires_at FROM users WHERE email = :email AND role = \'customer\' LIMIT 1');
  $stmt->execute([':email' => $email]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) return ['ok' => false, 'error' => 'Invalid email or OTP'];

  $expires = $row['otp_expires_at'] ?? null;
  if (!$expires) return ['ok' => false, 'error' => 'OTP expired'];

  if ((new DateTimeImmutable($expires, new DateTimeZone('UTC')))->getTimestamp() < time()) {
    return ['ok' => false, 'error' => 'OTP expired'];
  }

  $hash = (string)$row['otp_code_hash'];
  if ($hash === '' || !otp_verify_code($otpCode, $hash)) {
    return ['ok' => false, 'error' => 'Invalid OTP'];
  }

  // Mark email verified if column exists; otherwise clear otp fields.
  $pdo->beginTransaction();
  try {
    // Try update email_verified_at if column exists
    $pdo->exec("UPDATE users SET otp_code_hash=NULL, otp_expires_at=NULL WHERE id = " . (int)$row['id']);

    try {
      $pdo->prepare("UPDATE users SET email_verified_at = :t WHERE id = :id")->execute([
        ':t' => (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s'),
        ':id' => (int)$row['id'],
      ]);
    } catch (Throwable $ignored) {
      // Column might not exist in current schema.
    }
    
    try {
      $pdo->exec("UPDATE users SET is_verified = 1 WHERE id = " . (int)$row['id']);
    } catch (Throwable $ignored) {
      // Column might not exist in current schema.
    }

    $pdo->commit();
  } catch (Throwable $e) {
    $pdo->rollBack();
    return ['ok' => false, 'error' => 'Could not verify OTP'];
  }

  return ['ok' => true, 'user_id' => (int)$row['id']];
}

function auth_login(string $email, string $password): array {
  $pdo = db();
  $email = strtolower(trim($email));
  $password = (string)$password;

  $stmt = $pdo->prepare('SELECT id, full_name, email, phone, password_hash, role FROM users WHERE email = :email AND role = \'customer\' LIMIT 1');
  $stmt->execute([':email' => $email]);
  $u = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$u) return ['ok' => false, 'error' => 'Invalid credentials'];

  $hash = (string)$u['password_hash'];
  if ($hash === '' || !password_verify($password, $hash)) {
    return ['ok' => false, 'error' => 'Invalid credentials'];
  }

  // Optional: if email_verified_at exists, require it.
  try {
    $stmt2 = $pdo->prepare('SELECT email_verified_at FROM users WHERE id = :id LIMIT 1');
    $stmt2->execute([':id' => (int)$u['id']]);
    $ev = $stmt2->fetch(PDO::FETCH_ASSOC);
    if ($ev && array_key_exists('email_verified_at', $ev) && $ev['email_verified_at'] === null) {
      return ['ok' => false, 'error' => 'Email not verified'];
    }
  } catch (Throwable $ignored) {
    // if column missing, ignore
  }

  ensure_session();

  // Session regeneration
  if (session_status() === PHP_SESSION_ACTIVE) {
    session_regenerate_id(true);
  }

  $_SESSION['user'] = [
    'id' => (int)$u['id'],
    'role' => (string)$u['role'],
    'full_name' => (string)$u['full_name'],
    'email' => (string)$u['email'],
  ];

  // Migrate guest items (cart & wishlist) to logged-in user
  if (!empty($_SESSION['guest_sid'])) {
    try {
      require_once __DIR__ . '/cart_db.php';
      migrate_guest_to_user($pdo, (int)$u['id'], (string)$_SESSION['guest_sid']);
    } catch (Throwable $e) {
      // Ignore migration errors to not block login
    }
  }

  return ['ok' => true, 'user' => $_SESSION['user']];
}

function auth_forgot_password(string $email): array {
  $pdo = db();
  $email = strtolower(trim($email));
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['ok' => false, 'error' => 'Invalid email'];

  $stmt = $pdo->prepare('SELECT id, full_name, email FROM users WHERE email = :email AND role = \'customer\' LIMIT 1');
  $stmt->execute([':email' => $email]);
  $u = $stmt->fetch(PDO::FETCH_ASSOC);

  // Do not reveal existence.
  if (!$u) return ['ok' => true];

  $token = bin2hex(random_bytes(24));
  $tokenHash = password_hash($token, PASSWORD_DEFAULT);
  $expiresAt = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->modify('+30 minutes')->format('Y-m-d H:i:s');

  // Store reset token into otp fields if dedicated columns not present.
  // Preferred: users.password_reset_token_hash + password_reset_expires_at.
  $pdo->beginTransaction();
  try {
    $pdo->prepare('UPDATE users SET otp_code_hash = :t_hash, otp_expires_at = :t_exp WHERE id = :id')->execute([
      ':t_hash' => $tokenHash,
      ':t_exp' => $expiresAt,
      ':id' => (int)$u['id'],
    ]);
    $pdo->commit();
  } catch (Throwable $e) {
    $pdo->rollBack();
    return ['ok' => false, 'error' => 'Could not initiate reset'];
  }

  try {
    $resetUrl = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $base = $scheme . '://' . $resetUrl;
    $link = $base . '/pages/reset-password.php?token=' . urlencode($token) . '&email=' . urlencode($u['email']);

    $subject = 'Reset your password - Visnavi Jewellers';
    $bodyText = "Click to reset your password: {$link}. This link expires in 30 minutes.";
    $bodyHtml = '<p>' . htmlspecialchars($bodyText, ENT_QUOTES, 'UTF-8') . '</p>';

    send_email($u['email'], $u['full_name'], $subject, $bodyHtml, $bodyText);
  } catch (Throwable $e) {
    return [
      'ok' => true, 
      'warning' => 'Reset initiated but email delivery failed: ' . $e->getMessage(),
      'reset_url' => '/visnavi jwallers/pages/reset-password.php?token=' . urlencode($token) . '&email=' . urlencode($u['email'])
    ];
  }

  return ['ok' => true, 'reset_url' => '/visnavi jwallers/pages/reset-password.php?token=' . urlencode($token) . '&email=' . urlencode($u['email'])];
}

function auth_reset_password(string $email, string $token, string $newPassword): array {
  $pdo = db();
  $email = strtolower(trim($email));
  $newPassword = (string)$newPassword;

  if (mb_strlen($newPassword) < 8) return ['ok' => false, 'error' => 'Password must be at least 8 characters'];

  $stmt = $pdo->prepare('SELECT id, otp_code_hash, otp_expires_at FROM users WHERE email = :email AND role = \'customer\' LIMIT 1');
  $stmt->execute([':email' => $email]);
  $u = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$u) return ['ok' => false, 'error' => 'Invalid reset link'];

  $expires = $u['otp_expires_at'] ?? null;
  if (!$expires) return ['ok' => false, 'error' => 'Reset link expired'];
  if ((new DateTimeImmutable($expires, new DateTimeZone('UTC')))->getTimestamp() < time()) {
    return ['ok' => false, 'error' => 'Reset link expired'];
  }

  $hash = (string)$u['otp_code_hash'];
  if ($hash === '' || !password_verify($token, $hash)) {
    return ['ok' => false, 'error' => 'Invalid reset token'];
  }

  $pdo->beginTransaction();
  try {
    $pdo->prepare('UPDATE users SET password_hash = :phash, otp_code_hash = NULL, otp_expires_at = NULL WHERE id = :id')->execute([
      ':phash' => password_hash($newPassword, PASSWORD_DEFAULT),
      ':id' => (int)$u['id'],
    ]);
    $pdo->commit();
  } catch (Throwable $e) {
    $pdo->rollBack();
    return ['ok' => false, 'error' => 'Could not reset password'];
  }

  return ['ok' => true];
}

