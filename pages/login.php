<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/base_url.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

ensure_session();

// Redirect if already logged in
if (!empty($_SESSION['user'])) {
  if (($_SESSION['user']['role'] ?? '') === 'admin') {
    header('Location: /visnavi jwallers/admin/');
  } else {
    header('Location: /visnavi jwallers/index.php');
  }
  exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim((string)($_POST['email'] ?? ''));
  $password = (string)($_POST['password'] ?? '');

  if ($email === '' || $password === '') {
    $error = 'Please fill in all fields.';
  } else {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
      $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'role' => (string)$user['role'],
        'full_name' => (string)$user['full_name'],
        'email' => (string)$user['email']
      ];
      
      if ($user['role'] === 'admin') {
        header('Location: /visnavi jwallers/admin/');
      } else {
        header('Location: /visnavi jwallers/index.php');
      }
      exit;
    } else {
      $error = 'Invalid email or password.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<base href="/visnavi%20jwallers/">
<title>Sign In Vault — Vaishnavi Jewellers</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

<style>
:root {
  --maroon: #7A1E3A;
  --maroon-dark: #2B0610;
  --gold: #D4AF37;
  --gold-light: #F5E6A3;
  --dark: #070304;
  --glass: rgba(18, 11, 13, 0.45);
  --glass-card: rgba(18, 11, 13, 0.7);
  --glass-border: rgba(255, 255, 255, 0.08);
}

body {
  font-family: 'Poppins', sans-serif;
  background: radial-gradient(circle at center, #1b070c 0%, #070304 100%);
  margin: 0; padding: 0;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  overflow-x: hidden;
}

/* Background animated sparkles */
.sparkle-bg {
  position: fixed; inset: 0;
  pointer-events: none; z-index: 1;
  background-image: 
    radial-gradient(1px 1px at 20px 30px, #D4AF37, rgba(0,0,0,0)),
    radial-gradient(1px 1px at 150px 80px, #fff, rgba(0,0,0,0)),
    radial-gradient(2px 2px at 300px 20px, #D4AF37, rgba(0,0,0,0)),
    radial-gradient(1.5px 1.5px at 50px 250px, #fff, rgba(0,0,0,0)),
    radial-gradient(1px 1px at 220px 180px, #D4AF37, rgba(0,0,0,0));
  background-size: 350px 350px;
  animation: bgSparkles 40s linear infinite;
  opacity: 0.4;
}
@keyframes bgSparkles {
  from { background-position: 0 0; }
  to { background-position: 350px 700px; }
}

.login-wrapper {
  position: relative;
  z-index: 10;
  width: 1050px; max-width: 100%;
  min-height: 650px;
  margin: 25px;
  background: var(--glass);
  backdrop-filter: blur(30px);
  border: 1px solid var(--glass-border);
  border-radius: 35px;
  overflow: hidden;
  box-shadow: 0 40px 100px rgba(0,0,0,0.7);
  display: grid;
  grid-template-columns: 1.15fr 1fr;
}

@media (max-width: 950px) {
  .login-wrapper { grid-template-columns: 1fr; }
  .showcase-panel { display: none; }
}

/* Showcase Panel */
.showcase-panel {
  background: linear-gradient(135deg, rgba(43,6,16,0.3) 0%, rgba(7,3,4,0.7) 100%), 
              url('../assets/images/luxury_showcase.jpg') no-repeat center center;
  background-size: cover;
  padding: 50px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  border-right: 1px solid var(--glass-border);
  position: relative;
}
.showcase-logo {
  font-family: 'Playfair Display', serif;
  font-size: 24px; font-weight: 700; color: #fff;
  text-decoration: none; display: flex; align-items: center; gap: 10px;
  z-index: 5;
}
.showcase-logo i { color: var(--gold); }

/* Floating glass card inside image */
.showcase-glasscard {
  background: rgba(27, 6, 12, 0.45);
  backdrop-filter: blur(15px);
  border: 1px solid rgba(255,255,255,0.06);
  padding: 35px 30px;
  border-radius: 24px;
  max-width: 480px;
  margin-top: auto; margin-bottom: 30px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.5);
  z-index: 5;
}
.showcase-glasscard h2 {
  font-family: 'Playfair Display', serif;
  font-size: 38px; font-weight: 700; color: #fff;
  line-height: 1.25; margin-bottom: 14px;
}
.showcase-glasscard p {
  font-size: 15px; color: var(--gold-light);
  line-height: 1.6; font-style: italic; opacity: 0.9; margin: 0;
}
.showcase-footer { font-size: 11px; color: rgba(255,255,255,0.45); z-index: 5; }

/* Form Panel */
.form-panel {
  padding: 65px 55px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  background: rgba(10,5,6,0.2);
}
.form-panel h3 {
  font-family: 'Playfair Display', serif;
  font-size: 34px; font-weight: 700; color: #fff; margin-bottom: 8px;
  letter-spacing: 0.5px;
}
.form-panel .sub-text {
  font-size: 13.5px; color: #8c8587; margin-bottom: 40px;
}

/* Custom fields */
.form-group-custom {
  position: relative;
  margin-bottom: 26px;
}
.input-custom {
  width: 100%;
  background: rgba(255,255,255,0.02);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px;
  padding: 18px 22px;
  color: #fff;
  font-size: 14px;
  outline: none;
  transition: all 0.3s;
}
.input-custom:focus {
  border-color: var(--gold);
  background: rgba(255,255,255,0.05);
  box-shadow: 0 0 15px rgba(212,175,55,0.12);
}
.label-custom {
  position: absolute;
  left: 22px; top: 50%;
  transform: translateY(-50%);
  font-size: 14px; color: #666;
  pointer-events: none;
  transition: all 0.3s;
}
.input-custom:focus ~ .label-custom,
.input-custom:not(:placeholder-shown) ~ .label-custom {
  top: -10px; left: 14px;
  font-size: 11.5px; color: var(--gold);
  background: #0f0507; padding: 2px 10px;
  border-radius: 4px;
}

.forgot-link { text-align: right; margin-top: -10px; margin-bottom: 28px; }
.forgot-link a { color: var(--gold); font-size: 13px; text-decoration: none; font-weight: 500; transition: all 0.3s; }
.forgot-link a:hover { color: var(--gold-light); text-shadow: 0 0 8px rgba(212,175,55,0.3); }

.btn-login {
  background: linear-gradient(135deg, var(--gold) 0%, #b69527 100%);
  color: #000; font-weight: 700;
  padding: 16px; border-radius: 50px;
  border: none; font-size: 15px; cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 8px 25px rgba(212,175,55,0.25);
  width: 100%;
}
.btn-login:hover {
  transform: translateY(-2px);
  box-shadow: 0 15px 35px rgba(212,175,55,0.4);
  background: linear-gradient(135deg, var(--gold-light) 0%, var(--gold) 100%);
}

.or-divider {
  display: flex; align-items: center; gap: 15px; margin: 30px 0;
  color: #444; font-size: 12px; font-weight: 600;
}
.or-divider::before, .or-divider::after {
  content: ""; flex: 1; height: 1px; background: rgba(255,255,255,0.06);
}

.social-login { display: flex; gap: 15px; }
.social-btn {
  flex: 1; padding: 13px; border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.06);
  background: rgba(255,255,255,0.01);
  display: flex; align-items: center; justify-content: center; gap: 10px;
  cursor: pointer; font-size: 13px; color: #bbb;
  transition: all 0.3s;
}
.social-btn:hover { background: rgba(255,255,255,0.05); color: #fff; border-color: rgba(255,255,255,0.15); }

.signup-link { text-align: center; margin-top: 35px; color: #777; font-size: 14.5px; }
.signup-link a { color: var(--gold); font-weight: 600; text-decoration: none; }
.signup-link a:hover { color: var(--gold-light); }

.alert-danger-custom {
  background: rgba(220,38,38,0.08);
  border: 1px solid rgba(220,38,38,0.2);
  color: #f87171; padding: 15px; border-radius: 12px;
  font-size: 13px; margin-bottom: 24px;
  display: flex; align-items: center; gap: 10px;
}
</style>
</head>
<body>

<div class="sparkle-bg"></div>

<div class="login-wrapper">

  <!-- Left Showcase Panel -->
  <div class="showcase-panel">
    <div class="showcase-glasscard">
      <h2>Timeless Masterpieces. Crafted for Eternity.</h2>
      <p>"Jewellery is like a biography. A story that tells the many chapters of our lives."</p>
    </div>
    <div class="showcase-footer">
      <span>© <?= date('Y') ?> Vaishnavi Jewellers. Heritage & Luxury.</span>
    </div>
  </div>

  <!-- Right Form Panel -->
  <div class="form-panel">
    <div class="text-center mb-4">
      <a href="index.php">
        <img src="../assets/images/luxury_logo_new.png" alt="Vaishnavi Jewellers Logo" style="height: 100px; max-height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid var(--gold); box-shadow: 0 10px 30px rgba(212,175,55,0.25); transition: all 0.3s ease;">
      </a>
    </div>
    <h3>Sign In</h3>
    <p class="sub-text">Welcome back! Please enter your luxury vault access details.</p>

    <?php if ($error): ?>
      <div class="alert-danger-custom">
        <i class="fas fa-exclamation-circle"></i>
        <span><?= h($error) ?></span>
      </div>
    <?php endif; ?>

    <div style="display: flex; gap: 15px; border-bottom: 1px solid rgba(255,255,255,0.06); margin-bottom: 25px;">
      <button type="button" id="tabPassword" style="background:none; border:none; color:var(--gold); font-weight:600; padding:10px; font-size:14px; border-bottom:2px solid var(--gold); cursor:pointer; outline:none;">Password</button>
      <button type="button" id="tabOtp" style="background:none; border:none; color:#888; font-weight:500; padding:10px; font-size:14px; border-bottom:2px solid transparent; cursor:pointer; outline:none;">OTP Access</button>
    </div>

    <!-- Password Form -->
    <form id="formPassword" method="POST" action="">
      <div class="form-group-custom">
        <input type="email" name="email" class="input-custom" placeholder=" " required value="<?= isset($_POST['email']) ? h((string)$_POST['email']) : '' ?>">
        <label class="label-custom">Email Address</label>
      </div>

      <div class="form-group-custom">
        <input type="password" name="password" class="input-custom" placeholder=" " required>
        <label class="label-custom">Password</label>
      </div>

      <div class="forgot-link">
        <a href="pages/forgot-password.php">Forgot Password?</a>
      </div>

      <button type="submit" class="btn-login">Unlock Vault</button>
    </form>

    <!-- OTP Form (hidden by default) -->
    <form id="formOtp" style="display:none;" onsubmit="handleOtpVerify(event)">
      <div id="otpInputWrap">
        <div class="form-group-custom">
          <input type="text" id="otpIdentity" class="input-custom" placeholder=" " required>
          <label class="label-custom">Email or Mobile Number</label>
        </div>
        <button type="button" onclick="sendOtpCode()" id="btnSendOtp" class="btn-login" style="margin-top: 15px;">Send OTP Code</button>
      </div>

      <div id="otpVerifyWrap" style="display:none; margin-top:20px;">
        <div class="alert-danger-custom" id="otpFeedbackAlert" style="background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.2); color:var(--gold); display:none; flex-direction:column; align-items:flex-start; gap:4px; padding:12px 18px;">
          <div style="display:flex; align-items:center; gap:8px;">
            <i class="fas fa-info-circle"></i>
            <strong style="font-size:12px;">Sandbox Mode Alert</strong>
          </div>
          <span id="otpFeedbackText" style="font-size:11.5px; line-height:1.4;"></span>
        </div>
        <div class="form-group-custom" style="margin-top:15px;">
          <input type="text" id="otpCode" class="input-custom" placeholder=" " maxlength="6">
          <label class="label-custom">Enter 6-Digit OTP</label>
        </div>
        <button type="submit" id="btnVerifyOtp" class="btn-login" style="margin-top: 15px;">Verify & Enter</button>
      </div>
    </form>

    <div class="or-divider">SECURE ACCESS VIA</div>

    <div class="social-login">
      <div class="social-btn" id="btnGoogleLogin" style="flex: 1; height: 40px; margin: 0; padding: 0 10px; display: flex; align-items: center; justify-content: center; cursor:pointer;"><i class="fab fa-google" style="color:#ea4335; margin-right: 8px;"></i> Google</div>
      <div class="social-btn" id="btnFacebookLogin" style="flex: 1; height: 40px; margin: 0; padding: 0 10px; display: flex; align-items: center; justify-content: center; cursor:pointer;"><i class="fab fa-facebook" style="color:#1877f2; margin-right: 8px;"></i> Facebook</div>
    </div>

    <p class="signup-link">New collector? <a href="pages/register.php">Create Account</a></p>
  </div>

</div>

<!-- JavaScript for Secure Luxury Social Authentication -->
<script>
  // Google Login Click
  document.getElementById('btnGoogleLogin').addEventListener('click', function() {
    // Create a luxury simulated OAuth popup window to mock the Google Identity Flow
    const width = 500, height = 600;
    const left = (screen.width / 2) - (width / 2);
    const top = (screen.height / 2) - (height / 2);
    
    const popup = window.open('', 'GoogleSignIn', `width=${width},height=${height},top=${top},left=${left}`);
    
    // Write luxury themed authorization simulated content into the popup
    popup.document.write(`
      <html>
      <head>
        <title>Sign in with Google</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <style>
          body { font-family: 'Poppins', sans-serif; background: #0F090A; color: #fff; margin: 0; padding: 40px 20px; text-align: center; }
          .logo { font-size: 32px; color: #D4AF37; margin-bottom: 20px; }
          h2 { margin-bottom: 10px; font-weight: 500; }
          p { color: #888; font-size: 13px; margin-bottom: 30px; }
          .account-box {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px; padding: 15px; margin: 15px auto;
            max-width: 320px; cursor: pointer; display: flex; align-items: center; gap: 15px;
            transition: all 0.3s;
          }
          .account-box:hover { background: rgba(255,255,255,0.08); border-color: #D4AF37; }
          .avatar { width: 40px; height: 40px; border-radius: 50%; background: #D4AF37; color: #000; font-weight: bold; display: flex; align-items: center; justify-content: center; }
          .info { text-align: left; }
          .info .name { font-size: 14px; font-weight: 600; }
          .info .email { font-size: 12px; color: #aaa; }
          .footer { font-size: 11px; color: #555; margin-top: 50px; }
        </style>
      </head>
      <body>
        <div class="logo"><i class="fab fa-google"></i></div>
        <h2>Sign in with Google</h2>
        <p>to continue to <strong>Vaishnavi Jewellers</strong></p>
        
        <div class="account-box" onclick="selectAccount('Kirtan Kachhiya', 'kirtankachhiya06@gmail.com')">
          <div class="avatar">KK</div>
          <div class="info">
            <div class="name">Kirtan Kachhiya</div>
            <div class="email">kirtankachhiya06@gmail.com</div>
          </div>
        </div>

        <div class="account-box" onclick="selectAccount('Guest Collector', 'guest.collector@vaishnavijewellers.com')">
          <div class="avatar">GC</div>
          <div class="info">
            <div class="name">Guest Collector</div>
            <div class="email">guest.collector@vaishnavijewellers.com</div>
          </div>
        </div>

        <div class="footer">
          To continue, Google will share your name, email address, and profile picture with Vaishnavi Jewellers.
        </div>

        <script>
          function selectAccount(name, email) {
            window.opener.postMessage({ provider: 'google', name: name, email: email }, window.location.origin);
            window.close();
          }
        <\/script>
      </body>
      </html>
    `);
  });

  // Facebook Login Click
  document.getElementById('btnFacebookLogin').addEventListener('click', function() {
    const width = 500, height = 600;
    const left = (screen.width / 2) - (width / 2);
    const top = (screen.height / 2) - (height / 2);
    
    const popup = window.open('', 'FacebookSignIn', `width=${width},height=${height},top=${top},left=${left}`);
    
    popup.document.write(`
      <html>
      <head>
        <title>Log in with Facebook</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <style>
          body { font-family: 'Poppins', sans-serif; background: #0A0F1D; color: #fff; margin: 0; padding: 40px 20px; text-align: center; }
          .logo { font-size: 36px; color: #1877f2; margin-bottom: 20px; }
          h2 { margin-bottom: 10px; font-weight: 500; }
          p { color: #888; font-size: 13px; margin-bottom: 30px; }
          .btn-fb-confirm {
            background: #1877f2; color: #fff; border: none; padding: 14px 28px; border-radius: 8px;
            font-weight: 600; cursor: pointer; font-size: 14px; transition: all 0.3s;
          }
          .btn-fb-confirm:hover { background: #166fe5; }
          .cancel { display: block; color: #888; text-decoration: none; margin-top: 20px; font-size: 13px; }
        </style>
      </head>
      <body>
        <div class="logo"><i class="fab fa-facebook"></i></div>
        <h2>Log in with Facebook</h2>
        <p>Vaishnavi Jewellers is requesting access to your public profile and email address.</p>
        
        <button class="btn-fb-confirm" onclick="confirmLogin()">Continue as Kirtan</button>
        <a href="#" class="cancel" onclick="window.close()">Cancel</a>

        <script>
          function confirmLogin() {
            window.opener.postMessage({ provider: 'facebook', name: 'Kirtan Kachhiya', email: 'kirtankachhiya06@gmail.com' }, window.location.origin);
            window.close();
          }
        <\/script>
      </body>
      </html>
    `);
  });

  // Listen to message from simulated popup
  window.addEventListener('message', function(event) {
    if (event.origin !== window.location.origin) return;
    
    const data = event.data;
    if (data && data.email) {
      handleSocialAuth(data.name, data.email, data.provider);
    }
  });

  // Post to local server backend session setter
  function handleSocialAuth(name, email, provider) {
    fetch('api/social-auth.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        name: name,
        email: email,
        provider: provider
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.ok) {
        alert('Welcome ' + name + '!\nSecure session established.');
        window.location.href = 'index.php';
      } else {
        alert('Authentication failed: ' + data.error);
      }
    })
    .catch(err => {
      console.error(err);
      alert('Network error connecting to luxury vault.');
    });
  }

  // OTP Login Toggle & Scripting
  const tabPassword = document.getElementById('tabPassword');
  const tabOtp = document.getElementById('tabOtp');
  const formPassword = document.getElementById('formPassword');
  const formOtp = document.getElementById('formOtp');

  tabPassword.addEventListener('click', () => {
    tabPassword.style.color = 'var(--gold)';
    tabPassword.style.borderBottom = '2px solid var(--gold)';
    tabOtp.style.color = '#888';
    tabOtp.style.borderBottom = '2px solid transparent';
    formPassword.style.display = 'block';
    formOtp.style.display = 'none';
  });

  tabOtp.addEventListener('click', () => {
    tabOtp.style.color = 'var(--gold)';
    tabOtp.style.borderBottom = '2px solid var(--gold)';
    tabPassword.style.color = '#888';
    tabPassword.style.borderBottom = '2px solid transparent';
    formOtp.style.display = 'block';
    formPassword.style.display = 'none';
  });

  function sendOtpCode() {
    const identity = document.getElementById('otpIdentity').value.trim();
    if (!identity) {
      alert('Please enter your email or mobile number.');
      return;
    }

    const btn = document.getElementById('btnSendOtp');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    btn.disabled = true;

    fetch('api/send-otp.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ identity: identity })
    })
    .then(res => res.json())
    .then(data => {
      btn.innerHTML = 'Send OTP Code';
      btn.disabled = false;

      if (data.success) {
        document.getElementById('otpVerifyWrap').style.display = 'block';
        const alertBox = document.getElementById('otpFeedbackAlert');
        const alertText = document.getElementById('otpFeedbackText');
        alertBox.style.display = 'flex';
        alertText.textContent = data.message;
      } else {
        alert(data.message);
      }
    })
    .catch(err => {
      btn.innerHTML = 'Send OTP Code';
      btn.disabled = false;
      console.error(err);
      alert('Error sending OTP.');
    });
  }

  function handleOtpVerify(event) {
    event.preventDefault();
    const identity = document.getElementById('otpIdentity').value.trim();
    const otp = document.getElementById('otpCode').value.trim();
    if (!identity || !otp) {
      alert('Please fill in all OTP verification fields.');
      return;
    }

    const btn = document.getElementById('btnVerifyOtp');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
    btn.disabled = true;

    fetch('api/auth-verify-otp.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ identity: identity, otp: otp })
    })
    .then(res => res.json())
    .then(data => {
      btn.innerHTML = 'Verify & Enter';
      btn.disabled = false;

      if (data.ok) {
        alert('Welcome back! OTP verified successfully.');
        window.location.href = 'index.php';
      } else {
        alert('Error: ' + (data.error || 'Invalid OTP code.'));
      }
    })
    .catch(err => {
      btn.innerHTML = 'Verify & Enter';
      btn.disabled = false;
      console.error(err);
      alert('Connection failed. Please try again.');
    });
  }
</script>
</body>
</html>
