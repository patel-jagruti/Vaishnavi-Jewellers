<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/helpers.php';

ensure_session();

$token = trim((string)($_GET['token'] ?? ''));
$email = trim((string)($_GET['email'] ?? ($_SESSION['reset_email'] ?? '')));
if ($email !== '') $_SESSION['reset_email'] = $email;

require_once __DIR__ . '/../includes/header.php';

?>

<div class="container" style="padding:22px 0 40px; max-width:820px;">
  <div class="section-head" style="margin-bottom:18px;">
    <h2 style="font-size:18px;">Reset Password</h2>
    <p>Choose a new secure password</p>
  </div>

  <div class="newsletter" style="padding:18px;">
    <form id="resetForm" method="post">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()); ?>" />
      <input type="hidden" name="token" value="<?= h($token) ?>" />

      <div style="margin-top:10px;">
        <div class="meta" style="margin-bottom:6px;">Email</div>
        <input class="form-input" style="width:100%; background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.14); padding:12px 14px; border-radius:14px; border:1px solid rgba(255,255,255,.14);" type="email" name="email" value="<?= h($email) ?>" required />
      </div>

      <div style="margin-top:10px;">
        <div class="meta" style="margin-bottom:6px;">New Password</div>
        <input class="form-input" style="width:100%; background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.14); padding:12px 14px; border-radius:14px; border:1px solid rgba(255,255,255,.14);" type="password" name="new_password" required minlength="8" />
      </div>

      <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
        <button class="btn btn-primary" type="submit">Update Password</button>
        <a class="btn" href="/visnavi jwallers/pages/login.php">Back to login</a>
      </div>

      <div id="resetResult" style="margin-top:12px; font-size:13px; color:var(--muted);"></div>
    </form>
  </div>
</div>

<script>
(() => {
  const form = document.getElementById('resetForm');
  const resEl = document.getElementById('resetResult');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    resEl.textContent = 'Updating...';

    const fd = new FormData(form);
    const payload = new URLSearchParams();
    for (const [k, v] of fd.entries()) payload.append(k, String(v));

    const r = await fetch('api/auth-reset-password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: payload
    });

    const data = await r.json().catch(() => ({}));
    if (data && data.ok) {
      window.location.href = 'pages/login.php?reset=1';
      return;
    }
    resEl.textContent = (data && data.error) ? data.error : 'Reset failed.';
  });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

