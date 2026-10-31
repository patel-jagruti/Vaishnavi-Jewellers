<!-- ===== WELCOME POPUP + EMAIL COLLECTOR (Feature D) ===== -->
<style>
.wp-overlay {
  position: fixed; inset: 0;
  background: rgba(10,5,2,0.75);
  backdrop-filter: blur(6px);
  z-index: 99997;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 16px;
}
.wp-overlay.show { display: flex; }
.wp-modal {
  width: 560px;
  max-width: 100%;
  background: #fff;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 24px 70px rgba(0,0,0,0.25);
  animation: wpUp 0.5s cubic-bezier(0.16,1,0.3,1);
  display: grid;
  grid-template-columns: 1fr 1fr;
}
@media(max-width:560px){
  .wp-modal { grid-template-columns: 1fr; }
  .wp-left  { display: none; }
}
@keyframes wpUp {
  from { transform: translateY(50px) scale(0.95); opacity: 0; }
  to   { transform: translateY(0) scale(1); opacity: 1; }
}
.wp-left {
  background: linear-gradient(160deg, #7A1E3A 0%, #3a0c1a 100%);
  padding: 36px 24px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.wp-left::before {
  content: '';
  position: absolute;
  top: -40px; right: -40px;
  width: 180px; height: 180px;
  border-radius: 50%;
  background: rgba(212,175,55,0.1);
}
.wp-left::after {
  content: '';
  position: absolute;
  bottom: -30px; left: -30px;
  width: 130px; height: 130px;
  border-radius: 50%;
  background: rgba(212,175,55,0.08);
}
.wp-gem { font-size: 52px; margin-bottom: 12px; }
.wp-left h3 {
  font-family: 'Playfair Display', serif;
  color: #F5E6A3;
  font-size: 22px;
  font-weight: 700;
  margin-bottom: 8px;
  line-height: 1.3;
}
.wp-left p {
  font-family: 'Poppins', sans-serif;
  font-size: 12px;
  color: rgba(255,255,255,0.7);
  line-height: 1.6;
}
.wp-badge {
  background: linear-gradient(135deg, #D4AF37, #F5E6A3, #D4AF37);
  color: #1a1a1a;
  font-size: 28px;
  font-weight: 900;
  padding: 14px 20px;
  border-radius: 14px;
  margin: 14px 0;
  font-family: 'Playfair Display', serif;
  box-shadow: 0 6px 20px rgba(212,175,55,0.3);
}
.wp-right {
  padding: 36px 28px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.wp-right h4 {
  font-family: 'Playfair Display', serif;
  font-size: 20px;
  font-weight: 700;
  color: #1A1A1A;
  margin-bottom: 6px;
}
.wp-right p {
  font-size: 12px;
  color: #888;
  font-family: 'Poppins', sans-serif;
  margin-bottom: 20px;
  line-height: 1.6;
}
.wp-input {
  width: 100%;
  padding: 12px 16px;
  border: 1.5px solid #eee;
  border-radius: 10px;
  font-size: 13px;
  font-family: 'Poppins', sans-serif;
  outline: none;
  margin-bottom: 10px;
  transition: border-color 0.2s;
  color: #333;
}
.wp-input:focus { border-color: #D4AF37; }
.wp-submit {
  width: 100%;
  padding: 13px;
  background: linear-gradient(135deg, #7A1E3A, #5a1529);
  color: #fff;
  border: none;
  border-radius: 30px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  font-family: 'Poppins', sans-serif;
  letter-spacing: 0.5px;
  box-shadow: 0 6px 18px rgba(122,30,58,0.25);
  transition: all 0.3s;
  margin-bottom: 12px;
}
.wp-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(122,30,58,0.3); }
.wp-skip {
  text-align: center;
  font-size: 11px;
  color: #aaa;
  cursor: pointer;
  background: none;
  border: none;
  font-family: 'Poppins', sans-serif;
  text-decoration: underline;
  display: block;
  width: 100%;
  transition: color 0.2s;
}
.wp-skip:hover { color: #7A1E3A; }
.wp-success {
  text-align: center;
  display: none;
  padding: 10px 0;
}
.wp-success .check { font-size: 40px; margin-bottom: 10px; }
.wp-success h5 {
  font-family: 'Playfair Display', serif;
  font-size: 18px;
  color: #1A1A1A;
  margin-bottom: 6px;
}
.wp-success p {
  font-size: 12px; color: #666;
  font-family: 'Poppins', sans-serif;
}
.wp-code {
  display: inline-block;
  background: #FAF9F7;
  border: 2px dashed #D4AF37;
  color: #7A1E3A;
  font-weight: 800;
  font-size: 18px;
  padding: 8px 20px;
  border-radius: 10px;
  letter-spacing: 3px;
  margin: 10px 0;
  font-family: 'Poppins', sans-serif;
}
.wp-timer-bar {
  height: 3px;
  background: linear-gradient(90deg, #D4AF37, #F5E6A3);
  border-radius: 2px;
  width: 100%;
  animation: wpTimer 10s linear forwards;
  margin-bottom: 12px;
}
@keyframes wpTimer {
  from { width: 100%; }
  to   { width: 0%; }
}
</style>

<div class="wp-overlay" id="wpOverlay">
  <div class="wp-modal">
    <!-- Left decorative panel -->
    <div class="wp-left">
      <div class="wp-gem">💎</div>
      <h3>Exclusive Welcome Offer</h3>
      <p>Join our family of jewellery lovers and get</p>
      <div class="wp-badge">5% OFF</div>
      <p>on your first order above &#8377;5,000</p>
    </div>
    <!-- Right form panel -->
    <div class="wp-right">
      <div class="wp-timer-bar"></div>
      <div id="wpForm">
        <h4>Claim Your Discount</h4>
        <p>Enter your details to receive your exclusive coupon code instantly.</p>
        <input type="text" class="wp-input" id="wpName" placeholder="Your Name" autocomplete="off">
        <input type="email" class="wp-input" id="wpEmail" placeholder="Your Email Address" autocomplete="off">
        <button class="wp-submit" id="wpSubmit">
          <i class="fas fa-gift me-2"></i>Get My 5% Off Coupon
        </button>
        <button class="wp-skip" id="wpSkip">No thanks, I'll pay full price</button>
      </div>
      <div class="wp-success" id="wpSuccess">
        <div class="check">🎉</div>
        <h5>Your Coupon is Ready!</h5>
        <p>Use this code at checkout</p>
        <div class="wp-code">WELCOME5</div>
        <p style="font-size:11px; color:#aaa; margin-top:6px;">Valid for 7 days · Min order &#8377;5,000</p>
        <button class="wp-submit" style="margin-top:14px;" onclick="closeWelcomePopup()">
          <i class="fas fa-shopping-bag me-2"></i>Start Shopping
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function closeWelcomePopup() {
  document.getElementById('wpOverlay').classList.remove('show');
  sessionStorage.setItem('vj_welcome_shown', '1');
}

document.addEventListener('DOMContentLoaded', function() {
  if (sessionStorage.getItem('vj_welcome_shown')) return;

  const overlay = document.getElementById('wpOverlay');
  const skipBtn = document.getElementById('wpSkip');
  const submitBtn = document.getElementById('wpSubmit');
  const wpForm = document.getElementById('wpForm');
  const wpSuccess = document.getElementById('wpSuccess');

  // Show after 10 seconds
  setTimeout(() => overlay.classList.add('show'), 10000);

  // Close on overlay click
  overlay.addEventListener('click', e => { if (e.target === overlay) closeWelcomePopup(); });
  skipBtn.addEventListener('click', closeWelcomePopup);

  submitBtn.addEventListener('click', function() {
    const name  = document.getElementById('wpName').value.trim();
    const email = document.getElementById('wpEmail').value.trim();
    if (!name)  { document.getElementById('wpName').focus(); return; }
    if (!email || !email.includes('@')) { document.getElementById('wpEmail').focus(); return; }

    // Store and show success
    localStorage.setItem('vj_email', email);
    localStorage.setItem('vj_coupon', 'WELCOME5');
    wpForm.style.display = 'none';
    wpSuccess.style.display = 'block';
    sessionStorage.setItem('vj_welcome_shown', '1');
  });
});
</script>
