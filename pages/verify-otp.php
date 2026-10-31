<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/base_url.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

ensure_session();

// If already logged in, redirect
if (!empty($_SESSION['user'])) {
  header('Location: /visnavi jwallers/index.php');
  exit;
}

$email = $_SESSION['verify_email'] ?? '';
if ($email === '') {
  header('Location: /visnavi jwallers/pages/register.php');
  exit;
}

$error = '';
$success = '';
$otpCodeLocal = $_SESSION['verify_otp_code'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $otp = trim((string)($_POST['otp'] ?? ''));
  
  if ($otp === '') {
    $error = 'Please enter the 6-digit OTP code.';
  } else {
    $res = auth_verify_otp($email, $otp);
    
    if ($res['ok']) {
      // Fetch user info to log in
      $pdo = db();
      $stmt = $pdo->prepare("SELECT id, full_name, email, role FROM users WHERE email = ? LIMIT 1");
      $stmt->execute([$email]);
      $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
      
      if ($userRow) {
        $_SESSION['user'] = [
          'id' => (int)$userRow['id'],
          'role' => $userRow['role'],
          'full_name' => $userRow['full_name'],
          'email' => $userRow['email']
        ];
      }
      
      // Cleanup session verify data
      unset($_SESSION['verify_email'], $_SESSION['verify_otp_code']);
      
      header('Location: /visnavi jwallers/index.php?verified=1');
      exit;
    } else {
      $error = $res['error'] ?? 'Invalid OTP code. Please try again.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<base href="/visnavi jwallers/">
<title>Verify OTP | Vaishnavi Jewellers</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
:root {
  --maroon: #5A1625;
  --gold: #D4AF37;
  --dark: #1A1A1A;
  --light-gold: #F4EAE1;
  --ivory: #FFFDFB;
  --gray: #777;
}

body {
  font-family: 'Poppins', sans-serif;
  background-color: var(--ivory);
  margin: 0;
  padding: 0;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

.otp-box {
  width: 100%;
  max-width: 440px;
  background-color: #fff;
  border-radius: 20px;
  padding: 40px;
  box-shadow: 0 15px 40px rgba(0,0,0,0.06);
  border: 1px solid #f3eeea;
  text-align: center;
}

.otp-box h3 {
  font-family: 'Playfair Display', serif;
  font-size: 32px;
  font-weight: 700;
  color: var(--maroon);
  margin-bottom: 8px;
}

.otp-box p.subtitle {
  font-size: 14px;
  color: var(--gray);
  margin-bottom: 25px;
}

.btn-gold {
  background-color: var(--maroon);
  color: #fff;
  border: none;
  border-radius: 30px;
  padding: 12px;
  font-weight: 600;
  font-size: 15px;
  width: 100%;
  transition: all 0.3s;
}

.btn-gold:hover {
  background-color: var(--gold);
  color: #1a1a1a;
}
</style>
</head>
<body>

<div class="container d-flex justify-content-center">
  <div class="otp-box">
    
    <?php if ($otpCodeLocal !== ''): ?>
      <div class="alert alert-warning text-start" role="alert" style="border-radius: 12px; font-size: 13px;">
        <i class="fas fa-exclamation-triangle me-2"></i><strong>Local Testing Fallback:</strong><br>
        Email delivery is disabled. Use OTP: <strong><?= htmlspecialchars($otpCodeLocal) ?></strong>
      </div>
    <?php endif; ?>

    <h3>Verify Your Email</h3>
    <p class="subtitle">We have sent a 6-digit OTP code to <br><strong><?= htmlspecialchars($email) ?></strong></p>

    <?php if ($error !== ''): ?>
      <div class="alert alert-danger" role="alert" style="font-size: 13px; border-radius: 10px;">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="post" action="pages/verify-otp.php">
      <div class="mb-4">
        <input type="text" class="form-control text-center" name="otp" placeholder="Enter 6-Digit OTP" maxlength="6" style="font-size: 20px; font-weight: 600; letter-spacing: 5px; border-radius: 12px; height: 55px; outline: none; border: 1.5px solid #ddd;" required autofocus>
      </div>

      <button type="submit" class="btn btn-gold mb-3">Verify & Login</button>
      <a href="pages/register.php" style="font-size: 13px; color: var(--maroon); text-decoration: none;">Back to Register</a>
    </form>
  </div>
</div>

</body>
</html>
