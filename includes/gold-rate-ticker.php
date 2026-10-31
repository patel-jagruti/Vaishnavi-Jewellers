<?php
// Premium Luxury Gold Rate Ticker (sticky)
// Admin controls via queryless config in markup
$enabled = '1';
$speed = 'normal';
$position = 'top'; // top|bottom
?>

<link rel="stylesheet" href="assets/css/gold-ticker.css">
<!-- Chart.js CDN for Gold Price Graph -->
<script src="https://cdn.jsdelivr.net/npm/chart.js" crossorigin="anonymous"></script>

<div class="lux-gold-ticker" data-enabled="<?= htmlspecialchars($enabled) ?>" data-speed="<?= htmlspecialchars($speed) ?>" data-position="<?= htmlspecialchars($position) ?>">
  <div class="lux-gold-ticker__inner" style="display:flex; align-items:center; width:100%; justify-content:space-between; padding-right:15px;">
    <div style="display:flex; align-items:center;">
      <div class="lux-gold-ticker__brand" aria-label="Gold rate ticker">
        <span class="lux-gold-ticker__icon" title="Gold"><i class="fas fa-gem"></i></span>
        <div>
          <div class="title">🔥 LIVE GOLD RATE TODAY</div>
          <div class="badge">24K • 22K • 18K • Silver</div>
        </div>
      </div>

      <div class="lux-gold-ticker__marqueeWrap" aria-hidden="false">
        <div class="lux-gold-ticker__marquee" role="marquee">
          <!-- Repeat twice for seamless 50% translate -->
          <div class="lux-gold-ticker__row">
            <div class="lux-gold-ticker__item">
              <span class="lux-gold-ticker__label">24K Gold</span>
              <span class="lux-gold-ticker__value" data-rate-key="gold24">--</span>
              <span class="lux-gold-ticker__label">/g</span>
              <span class="lux-gold-ticker__arrow" data-rate-arrow="gold24"></span>
            </div>
            <div class="lux-gold-ticker__item">
              <span class="lux-gold-ticker__label">22K Gold</span>
              <span class="lux-gold-ticker__value" data-rate-key="gold22">--</span>
              <span class="lux-gold-ticker__label">/g</span>
              <span class="lux-gold-ticker__arrow" data-rate-arrow="gold22"></span>
            </div>
            <div class="lux-gold-ticker__item">
              <span class="lux-gold-ticker__label">18K Gold</span>
              <span class="lux-gold-ticker__value" data-rate-key="gold18">--</span>
              <span class="lux-gold-ticker__label">/g</span>
              <span class="lux-gold-ticker__arrow" data-rate-arrow="gold18"></span>
            </div>
            <div class="lux-gold-ticker__item" style="border-right:none;">
              <span class="lux-gold-ticker__icon" title="Silver"><i class="fas fa-coins"></i></span>
              <span class="lux-gold-ticker__label">Silver</span>
              <span class="lux-gold-ticker__value" data-rate-key="silver">--</span>
              <span class="lux-gold-ticker__label">/g</span>
              <span class="lux-gold-ticker__arrow" data-rate-arrow="silver"></span>
            </div>
          </div>

          <div class="lux-gold-ticker__row" aria-hidden="true">
            <div class="lux-gold-ticker__item">
              <span class="lux-gold-ticker__label">24K Gold</span>
              <span class="lux-gold-ticker__value" data-rate-key="gold24">--</span>
              <span class="lux-gold-ticker__label">/g</span>
              <span class="lux-gold-ticker__arrow" data-rate-arrow="gold24"></span>
            </div>
            <div class="lux-gold-ticker__item">
              <span class="lux-gold-ticker__label">22K Gold</span>
              <span class="lux-gold-ticker__value" data-rate-key="gold22">--</span>
              <span class="lux-gold-ticker__label">/g</span>
              <span class="lux-gold-ticker__arrow" data-rate-arrow="gold22"></span>
            </div>
            <div class="lux-gold-ticker__item">
              <span class="lux-gold-ticker__label">18K Gold</span>
              <span class="lux-gold-ticker__value" data-rate-key="gold18">--</span>
              <span class="lux-gold-ticker__label">/g</span>
              <span class="lux-gold-ticker__arrow" data-rate-arrow="gold18"></span>
            </div>
            <div class="lux-gold-ticker__item" style="border-right:none;">
              <span class="lux-gold-ticker__icon" title="Silver"><i class="fas fa-coins"></i></span>
              <span class="lux-gold-ticker__label">Silver</span>
              <span class="lux-gold-ticker__value" data-rate-key="silver">--</span>
              <span class="lux-gold-ticker__label">/g</span>
              <span class="lux-gold-ticker__arrow" data-rate-arrow="silver"></span>
            </div>
          </div>
        </div>
      </div>

      <div class="lux-gold-ticker__meta" data-fetched-at>Updated: --</div>
    </div>

    <!-- Chart Trigger Button -->
    <button type="button" id="btnGoldTrend" style="background:rgba(255,255,255,0.12); color:#fff; border:1px solid rgba(255,255,255,0.2); border-radius:30px; font-size:10px; font-weight:700; padding:6px 14px; cursor:pointer; font-family:'Poppins',sans-serif; display:inline-flex; align-items:center; gap:6px; transition:all 0.3s; height:28px;">
      <i class="fas fa-chart-line" style="color:#D4AF37;"></i> Price Trend
    </button>
  </div>
</div>

<!-- Gold Price Trend Popup Modal -->
<div id="goldTrendOverlay" style="position:fixed; inset:0; background:rgba(10,5,2,0.85); backdrop-filter:blur(8px); z-index:99999; display:none; align-items:center; justify-content:center; padding:16px;">
  <div style="background:#fff; border-radius:24px; width:520px; max-width:100%; box-shadow:0 24px 70px rgba(0,0,0,0.3); overflow:hidden; animation:trendUp 0.4s cubic-bezier(0.16,1,0.3,1);">
    <div style="background:linear-gradient(135deg,#7A1E3A,#5a1529); color:#fff; padding:20px 24px; display:flex; justify-content:between; align-items:center;">
      <div>
        <h3 style="font-family:'Playfair Display',serif; font-size:18px; font-weight:700; margin:0; color:#fff;">Gold Price Analysis</h3>
        <p style="font-size:10px; color:rgba(255,255,255,0.7); margin:3px 0 0; font-family:'Poppins',sans-serif;">Historical 15-day price changes per gram (22K)</p>
      </div>
      <button type="button" id="btnCloseTrend" style="background:none; border:none; color:#fff; font-size:20px; cursor:pointer; padding:0 10px;"><i class="fas fa-times"></i></button>
    </div>
    <div style="padding:24px;">
      <div style="height:240px; width:100%; position:relative;">
        <canvas id="goldTrendChart"></canvas>
      </div>
      <div style="margin-top:20px; display:grid; grid-template-columns:1fr 1fr; gap:10px; background:#FAF9F7; border-radius:12px; padding:14px;">
        <div style="text-align:center; border-right:1px solid #eee;">
          <div style="font-size:9px; text-transform:uppercase; letter-spacing:1px; color:#aaa; font-weight:600;">15-Day Low</div>
          <div style="font-size:16px; font-weight:800; color:#16a34a; font-family:'Playfair Display',serif; margin-top:2px;">&#8377;6,250</div>
        </div>
        <div style="text-align:center;">
          <div style="font-size:9px; text-transform:uppercase; letter-spacing:1px; color:#aaa; font-weight:600;">15-Day High</div>
          <div style="font-size:16px; font-weight:800; color:#dc2626; font-family:'Playfair Display',serif; margin-top:2px;">&#8377;6,580</div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
@keyframes trendUp {
  from { transform: translateY(30px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const trendBtn = document.getElementById('btnGoldTrend');
  const overlay = document.getElementById('goldTrendOverlay');
  const closeBtn = document.getElementById('btnCloseTrend');
  let myChart = null;

  if (trendBtn) {
    trendBtn.addEventListener('click', function() {
      overlay.style.display = 'flex';
      renderTrendChart();
    });
  }
  if (closeBtn) {
    closeBtn.addEventListener('click', function() {
      overlay.style.display = 'none';
    });
  }
  if (overlay) {
    overlay.addEventListener('click', function(e) {
      if (e.target === this) overlay.style.display = 'none';
    });
  }

  function renderTrendChart() {
    const ctx = document.getElementById('goldTrendChart').getContext('2d');
    if (myChart) myChart.destroy();

    // Mock 15-day price history
    const dates = ['14 Jul','15 Jul','16 Jul','17 Jul','18 Jul','19 Jul','20 Jul','21 Jul','22 Jul','23 Jul','24 Jul','25 Jul','26 Jul','27 Jul','Today'];
    const prices = [6250, 6280, 6270, 6310, 6340, 6330, 6390, 6420, 6410, 6450, 6500, 6480, 6520, 6550, 6580];

    myChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: dates,
        datasets: [{
          label: '22K Gold Rate',
          data: prices,
          borderColor: '#D4AF37',
          backgroundColor: 'rgba(212,175,55,0.08)',
          borderWidth: 2,
          fill: true,
          tension: 0.4,
          pointRadius: 3,
          pointBackgroundColor: '#7A1E3A'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: {
            grid: { color: 'rgba(0,0,0,0.04)' },
            ticks: { font: { size: 9, family: 'Poppins' } }
          },
          x: {
            grid: { display: false },
            ticks: { font: { size: 9, family: 'Poppins' } }
          }
        }
      }
    });
  }
});
</script>
<script src="assets/js/script.js"></script>
