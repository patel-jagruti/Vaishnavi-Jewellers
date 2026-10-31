<!-- ===== SPIN & WIN LUCKY WHEEL (Feature 1) ===== -->
<style>
.wheel-overlay {
  position: fixed; inset: 0;
  background: rgba(10,5,2,0.8);
  backdrop-filter: blur(8px);
  z-index: 99998;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 16px;
}
.wheel-overlay.show { display: flex; }
.wheel-container {
  background: #fff;
  border-radius: 24px;
  width: 680px; max-width: 100%;
  box-shadow: 0 24px 70px rgba(0,0,0,0.3);
  overflow: hidden;
  animation: wheelUp 0.5s cubic-bezier(0.16,1,0.3,1);
  display: grid;
  grid-template-columns: 1fr 1.2fr;
}
@media(max-width: 600px) {
  .wheel-container { grid-template-columns: 1fr; }
  .wheel-left { display: none; }
}
@keyframes wheelUp {
  from { transform: translateY(40px) scale(0.96); opacity: 0; }
  to   { transform: translateY(0) scale(1); opacity: 1; }
}
.wheel-left {
  background: linear-gradient(160deg, #7A1E3A 0%, #3a0c1a 100%);
  padding: 30px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  position: relative;
}
.wheel-box {
  position: relative;
  width: 240px; height: 240px;
  border-radius: 50%;
  border: 8px solid #D4AF37;
  box-shadow: 0 8px 24px rgba(212,175,55,0.3);
  overflow: hidden;
}
.wheel-canvas {
  width: 100%; height: 100%;
  transition: transform 6s cubic-bezier(0.1, 0.8, 0.1, 1);
  transform: rotate(0deg);
}
.wheel-pointer {
  position: absolute;
  top: -12px; left: 50%;
  transform: translateX(-50%);
  width: 0; height: 0;
  border-left: 15px solid transparent;
  border-right: 15px solid transparent;
  border-top: 25px solid #D4AF37;
  z-index: 10;
  filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));
}
.wheel-center-pin {
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  width: 24px; height: 24px;
  background: #D4AF37;
  border-radius: 50%;
  border: 3px solid #fff;
  z-index: 5;
}
.wheel-right {
  padding: 36px 28px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.wheel-right h3 {
  font-family: 'Playfair Display', serif;
  font-size: 24px; font-weight: 700; color: #1a1a1a;
  margin-bottom: 6px;
}
.wheel-right p {
  font-size: 12px; color: #666; font-family: 'Poppins', sans-serif;
  line-height: 1.6; margin-bottom: 20px;
}
.wheel-input {
  width: 100%; padding: 12px 16px; border: 1.5px solid #eee;
  border-radius: 10px; font-size: 13px; font-family: 'Poppins', sans-serif;
  outline: none; margin-bottom: 12px; transition: border-color 0.2s;
  color: #333;
}
.wheel-input:focus { border-color: #D4AF37; }
.wheel-btn {
  width: 100%; padding: 13px;
  background: linear-gradient(135deg, #7A1E3A, #5a1529);
  color: #fff; border: none; border-radius: 30px;
  font-size: 13px; font-weight: 700; cursor: pointer;
  font-family: 'Poppins', sans-serif; letter-spacing: 0.5px;
  box-shadow: 0 6px 18px rgba(122,30,58,0.25);
  transition: all 0.3s; margin-bottom: 12px;
}
.wheel-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(122,30,58,0.3); }
.wheel-skip {
  text-align: center; font-size: 11px; color: #aaa;
  cursor: pointer; background: none; border: none;
  font-family: 'Poppins', sans-serif; text-decoration: underline;
  display: block; width: 100%;
}
.wheel-success { display: none; text-align: center; }
.wheel-success h4 { font-family: 'Playfair Display', serif; font-size: 20px; color:#1a1a1a; margin-bottom: 8px; }
.wheel-code-box {
  display: inline-block; background: #FAF9F7; border: 2px dashed #D4AF37;
  color: #7A1E3A; font-weight: 800; font-size: 18px;
  padding: 8px 24px; border-radius: 10px; letter-spacing: 2px;
  margin: 12px 0; font-family: 'Poppins', sans-serif;
}
</style>

<div class="wheel-overlay" id="wheelOverlay">
  <div class="wheel-container">
    <!-- Wheel panel -->
    <div class="wheel-left">
      <div class="wheel-pointer"></div>
      <div class="wheel-center-pin"></div>
      <div class="wheel-box">
        <canvas class="wheel-canvas" id="wheelCanvas" width="300" height="300"></canvas>
      </div>
    </div>
    <!-- Details panel -->
    <div class="wheel-right">
      <div id="wheelForm">
        <h3>Try Your Luck!</h3>
        <p>Spin the luxury wheel to unlock exclusive gold making charge discounts, free gifts, or cash coupons!</p>
        <input type="text" class="wheel-input" id="wheelName" placeholder="Your Name">
        <input type="email" class="wheel-input" id="wheelEmail" placeholder="Your Email Address">
        <button class="wheel-btn" id="btnSpin">Spin The Wheel</button>
        <button class="wheel-skip" id="btnSkipWheel">No thanks, just shopping</button>
      </div>
      <div class="wheel-success" id="wheelSuccess">
        <div style="font-size: 40px; margin-bottom: 10px;">🎉</div>
        <h4 id="wheelWinTitle">You Won!</h4>
        <p id="wheelWinDesc">Use this exclusive code at checkout:</p>
        <div class="wheel-code-box" id="wheelWinCode">VJ-SPIN20</div>
        <button class="wheel-btn" style="margin-top:14px;" onclick="closeWheelPopup()">Claim & Start Shopping</button>
      </div>
    </div>
  </div>
</div>

<script>
function closeWheelPopup() {
  document.getElementById('wheelOverlay').classList.remove('show');
  sessionStorage.setItem('vj_wheel_shown', '1');
}

document.addEventListener('DOMContentLoaded', function() {
  if (sessionStorage.getItem('vj_wheel_shown')) return;

  const overlay  = document.getElementById('wheelOverlay');
  const canvas   = document.getElementById('wheelCanvas');
  const ctx      = canvas.getContext('2d');
  const spinBtn  = document.getElementById('btnSpin');
  const skipBtn  = document.getElementById('btnSkipWheel');
  const formBox  = document.getElementById('wheelForm');
  const succBox  = document.getElementById('wheelSuccess');

  // Segments
  const prizes = [
    { text: 'Free Silver Coin', code: 'SILVERCOIN', color: '#7A1E3A', textColor: '#fff' },
    { text: '10% OFF Making',  code: 'MAKING10',   color: '#D4AF37', textColor: '#1a1a1a' },
    { text: 'Free Gold Polish', code: 'FREEPOLISH', color: '#FAF9F7', textColor: '#7A1E3A' },
    { text: '20% OFF Making',  code: 'MAKING20',   color: '#7A1E3A', textColor: '#fff' },
    { text: 'Better Luck Next Time', code: 'TRYAGAIN',color: '#D4AF37', textColor: '#1a1a1a' },
    { text: 'Rs. 1,000 Coupon', code: 'CASH1000',  color: '#FAF9F7', textColor: '#7A1E3A' }
  ];

  const totalSegments = prizes.length;
  const arcSize = (2 * Math.PI) / totalSegments;

  // Draw segments
  function drawWheel() {
    prizes.forEach((p, idx) => {
      const angle = idx * arcSize;
      ctx.fillStyle = p.color;
      ctx.beginPath();
      ctx.moveTo(150, 150);
      ctx.arc(150, 150, 140, angle, angle + arcSize);
      ctx.lineTo(150, 150);
      ctx.fill();

      // Draw label
      ctx.save();
      ctx.translate(150, 150);
      ctx.rotate(angle + arcSize / 2);
      ctx.textAlign = 'right';
      ctx.fillStyle = p.textColor;
      ctx.font = 'bold 11px Poppins';
      ctx.fillText(p.text, 130, 4);
      ctx.restore();
    });
  }
  drawWheel();

  // Show after 15 seconds
  setTimeout(() => overlay.classList.add('show'), 15000);

  skipBtn.addEventListener('click', closeWheelPopup);

  spinBtn.addEventListener('click', function() {
    const name = document.getElementById('wheelName').value.trim();
    const email = document.getElementById('wheelEmail').value.trim();
    if (!name) { document.getElementById('wheelName').focus(); return; }
    if (!email || !email.includes('@')) { document.getElementById('wheelEmail').focus(); return; }

    spinBtn.disabled = true;
    spinBtn.textContent = 'Spinning...';

    // Target a prize (exclude "Better luck")
    const validPrizes = [0, 1, 2, 3, 5];
    const targetIdx = validPrizes[Math.floor(Math.random() * validPrizes.length)];
    
    // Calculate rotation: spins (5 rounds) + offset to align targetIdx with pointer (top, 270 deg)
    const extraSpins = 5 * 360;
    const prizeAngle = (targetIdx * arcSize * 180 / Math.PI);
    const targetRotation = extraSpins + (270 - prizeAngle - (arcSize * 180 / Math.PI / 2));

    canvas.style.transition = 'transform 5s cubic-bezier(0.1, 0.8, 0.1, 1)';
    canvas.style.transform = `rotate(${targetRotation}deg)`;

    setTimeout(() => {
      // Show success
      formBox.style.display = 'none';
      succBox.style.display = 'block';

      const won = prizes[targetIdx];
      document.getElementById('wheelWinTitle').textContent = `You Won: ${won.text}!`;
      document.getElementById('wheelWinCode').textContent = won.code;

      // Save user coupon
      localStorage.setItem('vj_wheel_coupon', won.code);
    }, 5200);
  });
});
</script>
