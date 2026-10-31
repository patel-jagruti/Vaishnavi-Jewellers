<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/base_url.php';

if (!function_exists('route')) {
  function route(string $pathFromRoot): string {
    // base_url.php already defines route(); this is only a safety net.
    return $pathFromRoot;
  }
}

// Ensure session exists for $_SESSION checks.
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
  ini_set('session.cookie_path', '/');
  ini_set('session.cookie_httponly', '1');
  @session_start();
}

$appName = 'Vaishnavi Jewellers';
try {
  $cfgFile = __DIR__ . '/config.php';
  if (file_exists($cfgFile)) {
    $cfg = require $cfgFile;
    $appName = (string)($cfg['app_name'] ?? $appName);
  }
} catch (Throwable $e) {
  // ignore
}
?>

<style>
.footer-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; max-width: 1200px; margin: 0 auto; padding: 30px 15px; }
.footer-grid h3 { font-size: 14px; font-weight: 600; margin-bottom: 12px; color: #fff; border-bottom: 2px solid #D4AF37; padding-bottom: 8px; display: inline-block; }
.footer-grid p { font-size: 13px; margin-bottom: 8px; color: #ccc; }
.footer-grid a { color: #ccc; text-decoration: none; transition: 0.3s; }
.footer-grid a:hover { color: #D4AF37; }
.footer-bottom { text-align: center; padding: 15px; color: #888; font-size: 12px; border-top: 1px solid #333; }
@media (max-width: 768px) { .footer-grid { grid-template-columns: repeat(3, 1fr); gap: 15px; } }
@media (max-width: 480px) { .footer-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; } .footer-grid h3 { font-size: 12px; } .footer-grid p { font-size: 11px; } }
</style>

<footer class="footer" id="footer">
  <div class="footer-grid">
    <div>
      <h3><?= h($appName) ?></h3>
      <p>Premium jewellery collections.</p>
      <p>📍 Hours: 10AM - 8PM</p>
    </div>

    <div>
      <h3>Account</h3>
      <?php if (!empty($_SESSION['user'])): ?>
        <p><a href="<?= route('pages/profile.php') ?>">Profile</a></p>
        <p><a href="<?= route('pages/orders.php') ?>">Orders</a></p>
        <p><a href="<?= route('pages/wishlist.php') ?>">Wishlist</a></p>
        <p><a href="<?= route('pages/logout.php') ?>">Logout</a></p>
      <?php else: ?>
        <p><a href="<?= route('pages/login.php') ?>">Login</a></p>
        <p><a href="<?= route('pages/register.php') ?>">Register</a></p>
        <p><a href="<?= route('pages/orders.php') ?>">Orders</a></p>
        <p><a href="<?= route('pages/wishlist.php') ?>">Wishlist</a></p>
        <p><a href="<?= route('cart.php') ?>">Cart</a></p>
      <?php endif; ?>
    </div>

    <div>
      <h3>Store</h3>
      <p><a href="<?= route('pages/store.php') ?>">Nearest Store</a></p>
      <p><a href="<?= route('pages/store.php#appointment') ?>">Book Visit</a></p>
      <p>Hours: 10AM - 8PM</p>
      <p>📍 India</p>
    </div>

    <div>
      <h3>Links</h3>
      <p><a href="<?= route('pages/shop.php') ?>">Shop</a></p>
      <p><a href="<?= route('pages/about.php') ?>">About Us</a></p>
      <p><a href="<?= route('pages/wishlist.php') ?>">Wishlist</a></p>
      <p><a href="<?= route('pages/orders.php') ?>">Orders</a></p>
      <p><a href="<?= route('cart.php') ?>">Cart</a></p>
      <p><a href="<?= route('checkout.php') ?>">Checkout</a></p>
    </div>

    <div>
      <h3>Support</h3>
      <p><a href="<?= route('pages/shipping.html') ?>">Shipping Info</a></p>
      <p><a href="<?= route('pages/contact.php') ?>">Contact Us</a></p>
      <p><a href="<?= route('pages/faq.html') ?>">FAQ</a></p>
      <p><a href="<?= route('pages/gold-rate.php') ?>">Live Gold Rate</a></p>
      <p><a href="<?= route('pages/care-guide.php') ?>">Jewelry Care Guide</a></p>
      <p><a href="<?= route('pages/appointment.php') ?>">Book Store Visit</a></p>
    </div>
  </div>

  <div class="footer-bottom">© <?= date('Y') ?> Vaishnavi Jewellers. All rights reserved.</div>
</footer>

<?php require_once __DIR__ . '/quick-view.php'; ?>
<?php require_once __DIR__ . '/lucky-wheel.php'; ?>
<?php require_once __DIR__ . '/social-proof.php'; ?>
<?php require_once __DIR__ . '/welcome-popup.php'; ?>
<?php require_once __DIR__ . '/recently-viewed.php'; ?>

<!-- ===== WHATSAPP FLOATING BUTTON (Feature B) ===== -->
<style>
.wa-float {
  position: fixed;
  bottom: 28px;
  right: 28px;
  z-index: 88887;
  width: 56px; height: 56px;
  background: #25D366;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 6px 24px rgba(37,211,102,0.35);
  cursor: pointer;
  text-decoration: none;
  transition: all 0.3s ease;
  animation: waPulse 2.5s ease-in-out infinite;
}
.wa-float:hover {
  transform: scale(1.12);
  box-shadow: 0 10px 32px rgba(37,211,102,0.45);
  animation: none;
}
.wa-float svg { width: 28px; height: 28px; fill: #fff; }
@keyframes waPulse {
  0%,100% { box-shadow: 0 6px 24px rgba(37,211,102,0.35); }
  50%      { box-shadow: 0 6px 32px rgba(37,211,102,0.55), 0 0 0 10px rgba(37,211,102,0.08); }
}
.wa-tooltip {
  position: fixed;
  bottom: 94px;
  right: 28px;
  z-index: 88886;
  background: #fff;
  border-radius: 12px;
  padding: 10px 14px;
  box-shadow: 0 6px 22px rgba(0,0,0,0.1);
  font-size: 12px;
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  color: #1A1A1A;
  white-space: nowrap;
  opacity: 0;
  transform: translateY(6px);
  transition: all 0.3s ease;
  pointer-events: none;
}
.wa-tooltip::after {
  content: '';
  position: absolute;
  bottom: -6px; right: 20px;
  width: 12px; height: 6px;
  background: #fff;
  clip-path: polygon(0 0, 100% 0, 50% 100%);
}
.wa-float:hover ~ .wa-tooltip,
.wa-float-wrap:hover .wa-tooltip {
  opacity: 1;
  transform: translateY(0);
}
.wa-float-wrap { position: fixed; bottom: 28px; right: 28px; z-index: 88887; }
.wa-float-wrap .wa-float { position: static; }
</style>

<div class="wa-float-wrap">
  <a class="wa-float" href="https://wa.me/919876543210?text=Hello%20Vaishnavi%20Jewellers!%20I%20would%20like%20to%20enquire%20about%20your%20jewellery%20collection." target="_blank" rel="noopener" title="Chat on WhatsApp" aria-label="WhatsApp">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
      <path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.116 1.528 5.845L.057 23.888a.75.75 0 0 0 .974.899l6.188-1.989A11.94 11.94 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22.5c-1.96 0-3.788-.535-5.354-1.464l-.386-.226-3.973 1.278 1.208-3.862-.249-.397A10.485 10.485 0 0 1 1.5 12C1.5 6.201 6.201 1.5 12 1.5c5.8 0 10.5 4.7 10.5 10.5S17.8 22.5 12 22.5z"/>
    </svg>
  </a>
  <div class="wa-tooltip">💬 Chat with us on WhatsApp</div>
</div>

<!-- Shared JS (load only once) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<!-- Global luxury behaviors -->
<script src="<?= asset('js/app.js') ?>"></script>
<script src="<?= asset('js/home-ui.js') ?>"></script>

