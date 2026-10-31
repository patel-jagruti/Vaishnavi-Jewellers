<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/helpers.php';

ensure_session();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding:22px 0 40px; max-width:820px;">
  <div class="section-head" style="margin-bottom:18px;">
    <h2 style="font-size:18px;">Forgot Password</h2>
    <p>We will send a reset link to your email</p>
  </div>

  <div class="newsletter" style="padding:18px;">
    <form id="forgotForm" method="post">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()); ?>" />

      <div style="margin-top:10px;">
        <div class="meta" style="margin-bottom:6px;">Email</div>
        <input class="form-input" style="width:100%; background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.14); padding:12px 14px; border-radius:14px; border:1px solid rgba(255,255,255,.14);" type="email" name="email" required />
      </div>

      <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
        <button class="btn btn-primary" type="submit">Send Reset Link</button>
        <a class="btn" href="/visnavi jwallers/pages/login.php">Back to login</a>
      </div>

      <div id="forgotResult" style="margin-top:12px; font-size:13px; color:var(--muted);"></div>
    </form>
  </div>
</div>

<script>
(() => {
  const form = document.getElementById('forgotForm');
  const resEl = document.getElementById('forgotResult');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    resEl.textContent = 'Sending...';

    const fd = new FormData(form);
    const payload = new URLSearchParams();
    for (const [k, v] of fd.entries()) payload.append(k, String(v));

    const r = await fetch('api/auth-forgot-password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: payload
    });

    const data = await r.json().catch(() => ({}));
    if (data && data.ok) {
      resEl.textContent = data.warning ? data.warning : 'If your email exists, a reset link has been sent.';
      return;
    }
    resEl.textContent = (data && data.error) ? data.error : 'Request failed.';
  });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

