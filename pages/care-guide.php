<?php
declare(strict_types=1);

// UTF-8 encoding
header('Content-Type: text/html; charset=utf-8');

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Expert jewelry care guide from Vaishnavi Jewellers. Learn how to clean, store, and maintain your gold, diamond, gemstone, and pearl jewelry for a lifetime of brilliance.">
<title>Jewelry Care Guide | Vaishnavi Jewellers</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<base href="/visnavi jwallers/">
<style>
/* ===== ROOT DESIGN TOKENS ===== */
:root {
  --maroon: #7A1E3A;
  --maroon-dark: #5a1529;
  --gold: #D4AF37;
  --gold-light: #F5E6A3;
  --ivory: #FAF9F7;
  --dark: #1A1A1A;
  --gray: #666;
  --white: #fff;
  --radius: 16px;
}
*, *::before, *::after { box-sizing: border-box; }
body {
  font-family: 'Poppins', sans-serif;
  background: var(--ivory);
  color: var(--dark);
  line-height: 1.7;
}
h1, h2, h3, h4 {
  font-family: 'Playfair Display', serif;
  font-weight: 700;
}
a { text-decoration: none; color: inherit; transition: 0.3s; }

/* ===== NAV BAR ===== */
.lux-nav {
  background: #fff;
  box-shadow: 0 2px 25px rgba(0,0,0,0.06);
  padding: 15px 0;
  position: sticky;
  top: 0;
  z-index: 1000;
}
.lux-nav .brand {
  font-family: 'Playfair Display', serif;
  font-size: 24px;
  color: var(--maroon);
  font-weight: 700;
}

/* ===== HERO BANNER ===== */
.care-hero {
  background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 60%, #3a0c1f 100%);
  padding: 100px 0 80px;
  text-align: center;
  color: #fff;
  position: relative;
  overflow: hidden;
}
.care-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse at 20% 50%, rgba(212,175,55,0.08) 0%, transparent 60%),
    radial-gradient(ellipse at 80% 30%, rgba(212,175,55,0.06) 0%, transparent 50%);
  pointer-events: none;
}
.care-hero .hero-badge {
  display: inline-block;
  background: rgba(212,175,55,0.15);
  border: 1px solid rgba(212,175,55,0.4);
  color: var(--gold-light);
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 3px;
  text-transform: uppercase;
  padding: 8px 20px;
  border-radius: 30px;
  margin-bottom: 20px;
  backdrop-filter: blur(4px);
}
.care-hero h1 {
  font-size: clamp(36px, 6vw, 58px);
  font-weight: 700;
  color: #fff;
  margin-bottom: 15px;
  line-height: 1.15;
}
.care-hero h1 em {
  font-style: italic;
  color: var(--gold-light);
  font-family: 'Cormorant Garamond', serif;
}
.care-hero p {
  font-size: 15px;
  opacity: 0.85;
  max-width: 550px;
  margin: 0 auto;
}
.care-hero .scroll-hint {
  margin-top: 40px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  opacity: 0.6;
  font-size: 11px;
  letter-spacing: 2px;
  text-transform: uppercase;
  animation: bounceY 2s infinite;
}
@keyframes bounceY {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(6px); }
}

/* ===== TAB NAVIGATION ===== */
.care-tabs-section {
  position: sticky;
  top: 64px;
  background: #fff;
  z-index: 99;
  border-bottom: 2px solid rgba(212,175,55,0.15);
  box-shadow: 0 4px 20px rgba(0,0,0,0.04);
}
.care-tabs {
  display: flex;
  overflow-x: auto;
  gap: 0;
  scrollbar-width: none;
}
.care-tabs::-webkit-scrollbar { display: none; }
.care-tab {
  flex: 1;
  min-width: 140px;
  padding: 18px 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  background: transparent;
  font-family: 'Poppins', sans-serif;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.5px;
  color: var(--gray);
  position: relative;
  white-space: nowrap;
}
.care-tab::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  right: 0;
  height: 2px;
  background: var(--maroon);
  transform: scaleX(0);
  transform-origin: center;
  transition: transform 0.3s ease;
}
.care-tab:hover { color: var(--maroon); background: rgba(122,30,58,0.02); }
.care-tab.active { color: var(--maroon); }
.care-tab.active::after { transform: scaleX(1); }
.care-tab i { display: block; font-size: 20px; margin-bottom: 5px; }

/* ===== TAB PANELS ===== */
.care-content {
  padding: 70px 0;
}
.care-panel { display: none; }
.care-panel.active { display: block; }

/* ===== MATERIAL TYPE TITLE CARD ===== */
.material-intro {
  display: flex;
  align-items: center;
  gap: 30px;
  margin-bottom: 50px;
  flex-wrap: wrap;
}
.material-icon-badge {
  width: 90px;
  height: 90px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 36px;
  flex-shrink: 0;
  box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}
.material-intro-text h2 {
  font-size: 34px;
  margin-bottom: 6px;
  color: var(--dark);
}
.material-intro-text p {
  color: var(--gray);
  font-size: 14px;
  max-width: 600px;
}

/* ===== CARE CARDS GRID ===== */
.care-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 25px;
  margin-bottom: 50px;
}
.care-card {
  background: #fff;
  border-radius: var(--radius);
  padding: 30px;
  border: 1px solid rgba(0,0,0,0.06);
  box-shadow: 0 4px 20px rgba(0,0,0,0.03);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}
.care-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--maroon), var(--gold));
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.4s ease;
}
.care-card:hover {
  box-shadow: 0 12px 35px rgba(0,0,0,0.07);
  transform: translateY(-3px);
}
.care-card:hover::before { transform: scaleX(1); }
.care-card-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  margin-bottom: 18px;
  background: rgba(122,30,58,0.06);
  color: var(--maroon);
}
.care-card h4 {
  font-size: 16px;
  margin-bottom: 10px;
  color: var(--dark);
}
.care-card p {
  font-size: 13px;
  color: var(--gray);
  line-height: 1.7;
}

/* ===== DO / DON'T LISTS ===== */
.do-donts-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 25px;
  margin-bottom: 50px;
}
.do-card, .dont-card {
  border-radius: var(--radius);
  padding: 30px;
}
.do-card {
  background: rgba(16, 122, 64, 0.04);
  border: 1px solid rgba(16, 122, 64, 0.15);
}
.dont-card {
  background: rgba(220, 38, 38, 0.04);
  border: 1px solid rgba(220, 38, 38, 0.12);
}
.do-card h5, .dont-card h5 {
  font-size: 14px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  margin-bottom: 18px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.do-card h5 { color: #107a40; }
.dont-card h5 { color: #dc2626; }
.rule-list {
  list-style: none;
  padding: 0;
  margin: 0;
}
.rule-list li {
  font-size: 13px;
  color: var(--gray);
  padding: 8px 0;
  border-bottom: 1px solid rgba(0,0,0,0.05);
  display: flex;
  align-items: flex-start;
  gap: 10px;
  line-height: 1.5;
}
.rule-list li:last-child { border-bottom: none; }
.rule-list li i { margin-top: 2px; flex-shrink: 0; font-size: 11px; }
.do-card .rule-list li i { color: #107a40; }
.dont-card .rule-list li i { color: #dc2626; }

/* ===== STORAGE GUIDE TIMELINE ===== */
.storage-timeline {
  position: relative;
  padding-left: 40px;
  margin-bottom: 50px;
}
.storage-timeline::before {
  content: '';
  position: absolute;
  left: 12px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: linear-gradient(to bottom, var(--maroon), var(--gold));
  border-radius: 2px;
}
.timeline-step {
  position: relative;
  margin-bottom: 30px;
}
.timeline-step::before {
  content: attr(data-num);
  position: absolute;
  left: -40px;
  top: 2px;
  width: 26px;
  height: 26px;
  background: var(--maroon);
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 0 4px rgba(122,30,58,0.12);
}
.timeline-step h5 {
  font-size: 15px;
  font-weight: 600;
  color: var(--dark);
  margin-bottom: 4px;
}
.timeline-step p {
  font-size: 13px;
  color: var(--gray);
  margin: 0;
}

/* ===== SEASONAL CHART ===== */
.seasonal-card {
  background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
  color: #fff;
  border-radius: var(--radius);
  padding: 40px;
  margin-bottom: 50px;
  position: relative;
  overflow: hidden;
}
.seasonal-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 80% 20%, rgba(212,175,55,0.15), transparent 60%);
}
.seasonal-card h3 {
  font-size: 22px;
  color: var(--gold-light);
  margin-bottom: 25px;
  position: relative;
}
.seasonal-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
  position: relative;
}
.season-item {
  background: rgba(255,255,255,0.07);
  border: 1px solid rgba(212,175,55,0.2);
  border-radius: 12px;
  padding: 20px;
  backdrop-filter: blur(4px);
}
.season-item h6 {
  color: var(--gold-light);
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.season-item p {
  font-size: 12px;
  color: rgba(255,255,255,0.7);
  line-height: 1.6;
  margin: 0;
}

/* ===== CTA PROMO STRIP ===== */
.care-cta {
  background: var(--ivory);
  border: 1px solid rgba(212,175,55,0.2);
  border-radius: var(--radius);
  padding: 40px;
  text-align: center;
  margin-top: 20px;
}
.care-cta h3 {
  font-size: 26px;
  color: var(--dark);
  margin-bottom: 10px;
}
.care-cta p { color: var(--gray); font-size: 14px; margin-bottom: 25px; }
.btn-maroon {
  display: inline-block;
  background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
  color: #fff;
  padding: 14px 40px;
  border-radius: 30px;
  font-weight: 600;
  font-size: 13px;
  letter-spacing: 1px;
  text-transform: uppercase;
  box-shadow: 0 8px 24px rgba(122,30,58,0.2);
  transition: all 0.3s;
}
.btn-maroon:hover {
  color: #fff;
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(122,30,58,0.3);
}
.btn-gold {
  display: inline-block;
  background: linear-gradient(135deg, var(--gold), var(--gold-light), var(--gold));
  color: #1a1a1a;
  padding: 14px 40px;
  border-radius: 30px;
  font-weight: 700;
  font-size: 13px;
  letter-spacing: 1px;
  text-transform: uppercase;
  box-shadow: 0 8px 24px rgba(212,175,55,0.2);
  transition: all 0.3s;
}
.btn-gold:hover {
  color: #1a1a1a;
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(212,175,55,0.35);
}

/* ===== FOOTER ===== */
.lux-footer {
  background: var(--dark);
  color: #fff;
  padding: 70px 0 30px;
  margin-top: 0;
}
.lux-footer h3 { color: var(--gold); font-size: 20px; margin-bottom: 20px; }
.lux-footer h5 { color: #fff; font-size: 14px; margin-bottom: 18px; font-family: 'Poppins', sans-serif; font-weight: 600; }
.lux-footer a { color: #999; font-size: 13px; display: block; padding: 5px 0; }
.lux-footer a:hover { color: var(--gold); }
.footer-btm {
  border-top: 1px solid #333;
  padding-top: 25px;
  margin-top: 50px;
  text-align: center;
  color: #555;
  font-size: 12px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
  .do-donts-grid { grid-template-columns: 1fr; }
  .care-hero h1 { font-size: 36px; }
  .care-card { padding: 20px; }
  .booking-card { padding: 25px; }
  .material-intro { flex-direction: column; }
}
</style>
</head>
<body>

<!-- Navigation -->
<nav class="lux-nav">
  <div class="container d-flex align-items-center justify-content-between">
    <a class="brand" href="index.php">
      <img src="assets/images/logo.svg" alt="Vaishnavi Jewellers" style="height: 40px;">
    </a>
    <ul class="nav gap-3 d-none d-md-flex">
      <li><a class="nav-link" href="index.php" style="font-size:13px; font-weight:500;">Home</a></li>
      <li><a class="nav-link" href="pages/shop.php" style="font-size:13px; font-weight:500;">Shop</a></li>
      <li><a class="nav-link" href="pages/appointment.php" style="font-size:13px; font-weight:500;">Book Visit</a></li>
      <li><a class="nav-link" href="pages/contact.php" style="font-size:13px; font-weight:500;">Contact</a></li>
    </ul>
    <a href="pages/shop.php" class="btn-maroon" style="font-size:11px; padding:10px 22px;">Shop Now</a>
  </div>
</nav>

<!-- Hero Banner -->
<section class="care-hero">
  <div class="container">
    <div class="hero-badge">✦ Jewelry Preservation Specialists</div>
    <h1>The Art of <em>Caring</em><br>for Your Jewelry</h1>
    <p>Expert guidance on preserving the radiance, luster, and brilliance of your precious handcrafted pieces for generations.</p>
    <div class="scroll-hint">
      <i class="fas fa-chevron-down"></i>
      <span>Explore Guide</span>
    </div>
  </div>
</section>

<!-- Sticky Tab Navigation -->
<div class="care-tabs-section">
  <div class="container">
    <div class="care-tabs" id="careTabs">
      <button class="care-tab active" data-panel="gold">
        <i class="fas fa-ring"></i> Gold Jewelry
      </button>
      <button class="care-tab" data-panel="diamond">
        <i class="fas fa-gem"></i> Diamonds
      </button>
      <button class="care-tab" data-panel="gemstone">
        <i class="fas fa-star"></i> Gemstones
      </button>
      <button class="care-tab" data-panel="pearl">
        <i class="fas fa-circle"></i> Pearls
      </button>
      <button class="care-tab" data-panel="storage">
        <i class="fas fa-box-open"></i> Storage Tips
      </button>
    </div>
  </div>
</div>

<!-- Main Content Area -->
<main class="care-content">
  <div class="container">

    <!-- ==================== GOLD PANEL ==================== -->
    <div class="care-panel active" id="panel-gold">
      <div class="material-intro">
        <div class="material-icon-badge" style="background: linear-gradient(135deg, #D4AF37, #F5E6A3);">💛</div>
        <div class="material-intro-text">
          <h2>Gold Jewelry Care</h2>
          <p>Gold is the most enduring precious metal, but even the most brilliantly crafted gold piece requires a gentle hand and mindful care to maintain its rich lustre through the decades.</p>
        </div>
      </div>

      <div class="care-cards-grid">
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-tint"></i></div>
          <h4>Gentle Cleaning Method</h4>
          <p>Mix a few drops of mild dish soap in warm water. Dip a soft-bristled toothbrush and gently scrub the gold surface in small circular motions. Rinse under lukewarm water and pat dry with a lint-free microfibre cloth.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-sun"></i></div>
          <h4>Air Drying</h4>
          <p>After cleaning, lay the piece flat on a clean cotton cloth in a well-ventilated spot, away from direct sunlight. Allow to air dry completely before storage to prevent moisture trapping in clasps or prongs.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-shield-alt"></i></div>
          <h4>Polishing & Luster</h4>
          <p>Use a certified jewelry polishing cloth designed for gold. Rub gently in one direction to restore shine. Avoid circular rubbing on high-karat gold (22K–24K) as it may cause surface wear marks over time.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-flask"></i></div>
          <h4>Chemical Exposure</h4>
          <p>Always remove gold jewelry before swimming in chlorinated pools, using cleaning products, or applying perfume, hairspray, and lotions. Chemical compounds can discolor or tarnish gold alloy surfaces.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-tools"></i></div>
          <h4>Professional Polishing</h4>
          <p>Visit your jeweller annually for an ultrasonic cleaning and professional polishing session. This removes deep-set tarnish, tightens loose prongs, and restores the original factory finish of your jewelry.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-weight-hanging"></i></div>
          <h4>Wear & Weight Mindfulness</h4>
          <p>Higher karat gold (22K–24K) is softer and more malleable. Avoid wearing delicate gold pieces while performing heavy physical activities. Rings and bangles are particularly vulnerable to bending from impact.</p>
        </div>
      </div>

      <div class="do-donts-grid">
        <div class="do-card">
          <h5><i class="fas fa-check-circle"></i> Do's for Gold</h5>
          <ul class="rule-list">
            <li><i class="fas fa-check"></i> Clean monthly with mild soap and warm water</li>
            <li><i class="fas fa-check"></i> Store each piece separately to prevent scratching</li>
            <li><i class="fas fa-check"></i> Use anti-tarnish strips in your jewelry box</li>
            <li><i class="fas fa-check"></i> Remove before vigorous physical activity</li>
            <li><i class="fas fa-check"></i> Get professional service once a year</li>
            <li><i class="fas fa-check"></i> Insure your high-value pieces</li>
          </ul>
        </div>
        <div class="dont-card">
          <h5><i class="fas fa-times-circle"></i> Don'ts for Gold</h5>
          <ul class="rule-list">
            <li><i class="fas fa-times"></i> Never use bleach or ammonia-based cleaners</li>
            <li><i class="fas fa-times"></i> Avoid abrasive toothpaste or scrubbing pads</li>
            <li><i class="fas fa-times"></i> Don't wear in chlorinated pools or hot springs</li>
            <li><i class="fas fa-times"></i> Don't apply perfume or lotion while wearing</li>
            <li><i class="fas fa-times"></i> Avoid ultrasonic cleaners for pieces with loose stones</li>
            <li><i class="fas fa-times"></i> Don't store in rubber or plastic bags</li>
          </ul>
        </div>
      </div>

      <div class="seasonal-card">
        <h3>✦ Seasonal Care Calendar</h3>
        <div class="seasonal-grid">
          <div class="season-item">
            <h6><i class="fas fa-sun"></i> Summer (Apr – Jun)</h6>
            <p>Sweat and UV rays can dull gold. Wipe pieces with a damp cloth after wear. Reduce wearing during beach or pool outings.</p>
          </div>
          <div class="season-item">
            <h6><i class="fas fa-cloud-rain"></i> Monsoon (Jul – Sep)</h6>
            <p>Humidity and moisture can cause tarnish. Store with silica gel sachets. Avoid getting pieces soaked in rain.</p>
          </div>
          <div class="season-item">
            <h6><i class="fas fa-leaf"></i> Autumn (Oct – Nov)</h6>
            <p>Best time for professional cleaning ahead of festive and wedding seasons. Repair clasps or prongs if needed.</p>
          </div>
          <div class="season-item">
            <h6><i class="fas fa-snowflake"></i> Winter (Dec – Mar)</h6>
            <p>Low humidity keeps gold looking sharp. Ideal time for daily wear. Inspect for wear marks or discoloration from festive use.</p>
          </div>
        </div>
      </div>

      <div class="care-cta">
        <h3>Schedule a Gold Jewelry Service</h3>
        <p>Book a complimentary expert cleaning and inspection with our certified gem consultants at a Vaishnavi Jewellers boutique near you.</p>
        <a href="pages/appointment.php" class="btn-maroon me-3">Book Boutique Appointment</a>
        <a href="pages/shop.php?category=Gold" class="btn-gold">Shop Gold Collections</a>
      </div>
    </div>

    <!-- ==================== DIAMOND PANEL ==================== -->
    <div class="care-panel" id="panel-diamond">
      <div class="material-intro">
        <div class="material-icon-badge" style="background: linear-gradient(135deg, #e8f4ff, #c5e2ff);">💎</div>
        <div class="material-intro-text">
          <h2>Diamond Care</h2>
          <p>Diamonds are the hardest natural substance on earth, yet they attract grease and oils that cloud their brilliance. Proper cleaning restores their iconic sparkle and fire.</p>
        </div>
      </div>

      <div class="care-cards-grid">
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-soap"></i></div>
          <h4>Cleaning Diamonds</h4>
          <p>Soak the piece in a mixture of warm water and ammonia-free jewelry cleaner for 15 minutes. Use a soft brush to gently clean under the setting where grease accumulates. Rinse and dry thoroughly.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-expand-arrows-alt"></i></div>
          <h4>Ultrasonic Cleaning</h4>
          <p>Ultrasonic cleaners work excellently for solitaire diamonds without fractures. However, avoid this method for fracture-filled or treated diamonds, which may be damaged by vibrations.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-search"></i></div>
          <h4>Prong Inspection</h4>
          <p>Have a jeweller inspect prongs every 6–12 months. Worn or bent prongs can cause a diamond to loosen and fall out. Early detection saves the stone from costly loss.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-tshirt"></i></div>
          <h4>Reduce Lotion Buildup</h4>
          <p>Moisturizers, sunscreen, and cosmetics form a film under diamond settings over time. Remove rings before applying these products and wipe down jewelry before storing each evening.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-warehouse"></i></div>
          <h4>Separate Storage</h4>
          <p>Despite being the hardest material, diamonds can scratch each other or softer stones. Store each piece in an individual fabric-lined pouch or compartmentalized jewelry case.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-file-certificate"></i></div>
          <h4>Certificate & Insurance</h4>
          <p>Keep the GIA or IGI certificate for your diamonds stored safely. Insure all diamonds above 0.30ct. Update valuation certificates every 5 years as market prices fluctuate significantly.</p>
        </div>
      </div>

      <div class="do-donts-grid">
        <div class="do-card">
          <h5><i class="fas fa-check-circle"></i> Do's for Diamonds</h5>
          <ul class="rule-list">
            <li><i class="fas fa-check"></i> Clean every 2–4 weeks for maximum sparkle</li>
            <li><i class="fas fa-check"></i> Store each piece individually</li>
            <li><i class="fas fa-check"></i> Check prongs and settings biannually</li>
            <li><i class="fas fa-check"></i> Get diamonds re-polished if they appear dull</li>
            <li><i class="fas fa-check"></i> Keep certification documents safely</li>
            <li><i class="fas fa-check"></i> Insure high-carat pieces</li>
          </ul>
        </div>
        <div class="dont-card">
          <h5><i class="fas fa-times-circle"></i> Don'ts for Diamonds</h5>
          <ul class="rule-list">
            <li><i class="fas fa-times"></i> Don't touch the diamond surface with fingers</li>
            <li><i class="fas fa-times"></i> Avoid chlorine, which can erode gold prongs</li>
            <li><i class="fas fa-times"></i> Don't clean fracture-filled diamonds ultrasonically</li>
            <li><i class="fas fa-times"></i> Never use abrasives or harsh chemicals</li>
            <li><i class="fas fa-times"></i> Avoid wearing during construction or gardening</li>
            <li><i class="fas fa-times"></i> Don't store loose with other pieces</li>
          </ul>
        </div>
      </div>

      <div class="care-cta">
        <h3>Free Diamond Inspection Service</h3>
        <p>Visit any Vaishnavi Jewellers boutique for a complimentary prong check, cleaning, and grading refresh to keep your diamonds as brilliant as the day you bought them.</p>
        <a href="pages/appointment.php" class="btn-maroon me-3">Book Diamond Service</a>
        <a href="pages/shop.php?category=Diamond" class="btn-gold">Explore Diamond Collections</a>
      </div>
    </div>

    <!-- ==================== GEMSTONE PANEL ==================== -->
    <div class="care-panel" id="panel-gemstone">
      <div class="material-intro">
        <div class="material-icon-badge" style="background: linear-gradient(135deg, #ffe8ff, #f3c5f0);">🔮</div>
        <div class="material-intro-text">
          <h2>Gemstone Jewelry Care</h2>
          <p>Coloured gemstones — rubies, emeralds, sapphires, and more — each have unique hardness levels, optical properties, and sensitivity to heat, light, and chemicals. Tailor your care approach to each stone's specific characteristics.</p>
        </div>
      </div>

      <div class="care-cards-grid">
        <div class="care-card">
          <div class="care-card-icon" style="color: #e63946;"><i class="fas fa-gem"></i></div>
          <h4>Ruby Care</h4>
          <p>Rubies rate 9 on the Mohs scale. Clean with warm soapy water and a soft brush. Avoid ultrasonic cleaners if the ruby has inclusions or fractures. Store separately from diamonds that may scratch the surface.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon" style="color: #2a9d8f;"><i class="fas fa-gem"></i></div>
          <h4>Emerald Care</h4>
          <p>Emeralds are naturally included (jardin) and often oiled. Never use ultrasonic or steam cleaners. Clean with a damp cloth only — no soap. Keep away from heat and solvents that can remove the filler treatment.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon" style="color: #1a6eb5;"><i class="fas fa-gem"></i></div>
          <h4>Sapphire Care</h4>
          <p>Blue sapphires are very durable (Mohs 9). Safe for mild ultrasonic cleaning if unheated/untreated. Wipe off everyday oils. Heat-treated sapphires are common and generally safe for soap-and-water cleaning.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon" style="color: #9333ea;"><i class="fas fa-gem"></i></div>
          <h4>Amethyst & Quartz</h4>
          <p>Moderately hard (Mohs 7). Sensitive to prolonged UV exposure which can fade the purple colour. Store away from sunlight. Clean gently with mild soap. Avoid sudden temperature changes.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon" style="color: #d97706;"><i class="fas fa-gem"></i></div>
          <h4>Citrine & Topaz</h4>
          <p>Topaz (Mohs 8) is hard but has a perfect cleavage plane making it fragile under impact. Citrine can fade in strong sunlight. Both are safe for careful soap-water cleaning but avoid steam or ultrasonic.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon" style="color: #16a34a;"><i class="fas fa-gem"></i></div>
          <h4>General Gemstone Rule</h4>
          <p>When in doubt, the safest cleaning method for any gemstone is lukewarm water with a drop of pH-neutral soap and a soft brush. Always consult your jeweller before attempting cleaning of treated or delicate stones.</p>
        </div>
      </div>

      <div class="do-donts-grid">
        <div class="do-card">
          <h5><i class="fas fa-check-circle"></i> Do's for Gemstones</h5>
          <ul class="rule-list">
            <li><i class="fas fa-check"></i> Know your gemstone's hardness (Mohs scale)</li>
            <li><i class="fas fa-check"></i> Use a soft, damp cloth for cleaning</li>
            <li><i class="fas fa-check"></i> Store individually in soft fabric pouches</li>
            <li><i class="fas fa-check"></i> Ask your jeweller about treatment before cleaning</li>
            <li><i class="fas fa-check"></i> Re-oil emeralds every 3–5 years professionally</li>
            <li><i class="fas fa-check"></i> Keep receipts and certificates for coloured stones</li>
          </ul>
        </div>
        <div class="dont-card">
          <h5><i class="fas fa-times-circle"></i> Don'ts for Gemstones</h5>
          <ul class="rule-list">
            <li><i class="fas fa-times"></i> Never use abrasives on soft stones (opal, pearl)</li>
            <li><i class="fas fa-times"></i> Avoid ultrasonic cleaners for most coloured gems</li>
            <li><i class="fas fa-times"></i> Don't expose to prolonged sunlight or UV lamps</li>
            <li><i class="fas fa-times"></i> Avoid storing loose — they scratch each other</li>
            <li><i class="fas fa-times"></i> Never use bleach, acetone, or alcohol</li>
            <li><i class="fas fa-times"></i> Don't expose to extreme heat changes</li>
          </ul>
        </div>
      </div>

      <div class="care-cta">
        <h3>Gemstone Re-oiling & Restoration</h3>
        <p>Trust Vaishnavi Jewellers' expert gemologists for professional coloured stone restoration, re-setting, and treatment services available at all boutique locations.</p>
        <a href="pages/appointment.php" class="btn-maroon me-3">Book Gemstone Consultation</a>
        <a href="pages/shop.php" class="btn-gold">Shop Our Gemstone Range</a>
      </div>
    </div>

    <!-- ==================== PEARL PANEL ==================== -->
    <div class="care-panel" id="panel-pearl">
      <div class="material-intro">
        <div class="material-icon-badge" style="background: linear-gradient(135deg, #f0f0f0, #e0e8ff);">🤍</div>
        <div class="material-intro-text">
          <h2>Pearl Jewelry Care</h2>
          <p>Pearls are organic gems with only a 2.5–4.5 Mohs hardness. They are uniquely sensitive to acids, perfumes, cosmetics, and even natural skin oils. Pearls require the most attentive and gentle care of all precious jewelry materials.</p>
        </div>
      </div>

      <div class="care-cards-grid">
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-hand-sparkles"></i></div>
          <h4>After Every Wear</h4>
          <p>Gently wipe your pearls with a damp, clean chamois or silk cloth after each wearing to remove traces of perspiration, oils, and makeup before storage. This single habit extends pearl life dramatically.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-water"></i></div>
          <h4>Cleaning Pearls</h4>
          <p>Use a very slightly damp cloth with no soap, or a specialist pearl cleaner. Never submerge pearl strands in water — the silk thread will stretch and weaken. Lay flat to dry before storing.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-thread"></i></div>
          <h4>Re-stringing</h4>
          <p>Pearl necklaces and bracelets should be re-strung on silk thread every 1–3 years depending on frequency of wear. The knots between each pearl prevent mass loss if the thread breaks.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-spray-can"></i></div>
          <h4>Cosmetics & Perfume</h4>
          <p>Pearls should be the last thing you put on before leaving and the first you take off on return. Let perfume, hairspray, and makeup dry and settle completely before adorning your pearl jewelry.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-box"></i></div>
          <h4>Breathing Space</h4>
          <p>Unlike most gems, pearls need to breathe. Avoid storing in airtight plastic bags — the lack of humidity will cause them to become brittle and crack over time. Use a soft cloth pouch or open jewelry box.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-temperature-high"></i></div>
          <h4>Temperature Sensitivity</h4>
          <p>Pearls are sensitive to extreme heat and dryness. Never store near heating vents or leave in a hot car. Moderate humidity keeps the nacre layers supple and prevents surface crazing or cracking.</p>
        </div>
      </div>

      <div class="do-donts-grid">
        <div class="do-card">
          <h5><i class="fas fa-check-circle"></i> Do's for Pearls</h5>
          <ul class="rule-list">
            <li><i class="fas fa-check"></i> Wipe with a damp cloth after every wear</li>
            <li><i class="fas fa-check"></i> Store in soft cloth pouch with breathing room</li>
            <li><i class="fas fa-check"></i> Re-string on knotted silk every 1–3 years</li>
            <li><i class="fas fa-check"></i> Put pearls on last when dressing</li>
            <li><i class="fas fa-check"></i> Keep away from other hard jewelry</li>
            <li><i class="fas fa-check"></i> Have the clasps checked annually</li>
          </ul>
        </div>
        <div class="dont-card">
          <h5><i class="fas fa-times-circle"></i> Don'ts for Pearls</h5>
          <ul class="rule-list">
            <li><i class="fas fa-times"></i> Never submerge in water or cleaning solutions</li>
            <li><i class="fas fa-times"></i> Avoid acidic substances — even lemon juice</li>
            <li><i class="fas fa-times"></i> Don't use ultrasonic or steam cleaners</li>
            <li><i class="fas fa-times"></i> Never store in airtight bags or rubber</li>
            <li><i class="fas fa-times"></i> Don't wear while swimming or showering</li>
            <li><i class="fas fa-times"></i> Avoid direct sunlight for prolonged periods</li>
          </ul>
        </div>
      </div>

      <div class="care-cta">
        <h3>Pearl Re-stringing Service</h3>
        <p>Our master craftsmen offer expert pearl re-stringing, repair, and restoration at all Vaishnavi Jewellers boutiques. Walk in or book an appointment for a complimentary pearl assessment.</p>
        <a href="pages/appointment.php" class="btn-maroon me-3">Book Pearl Service</a>
        <a href="pages/shop.php?category=Pearls" class="btn-gold">Browse Pearl Collections</a>
      </div>
    </div>

    <!-- ==================== STORAGE PANEL ==================== -->
    <div class="care-panel" id="panel-storage">
      <div class="material-intro">
        <div class="material-icon-badge" style="background: linear-gradient(135deg, #fff3cd, #fde68a);">🗃️</div>
        <div class="material-intro-text">
          <h2>Jewelry Storage Mastery</h2>
          <p>How you store your jewelry when you're not wearing it is just as important as how you care for it during use. The right storage environment protects against scratches, tarnish, tangling, and moisture damage.</p>
        </div>
      </div>

      <div class="storage-timeline">
        <div class="timeline-step" data-num="1">
          <h5>Choose the Right Jewelry Box</h5>
          <p>Invest in a quality jewelry box lined with anti-tarnish fabric (such as felt or Pacific Cloth). Individual compartments prevent pieces from scratching each other. A locking mechanism adds security for high-value items.</p>
        </div>
        <div class="timeline-step" data-num="2">
          <h5>Separate by Material Type</h5>
          <p>Always store gold, silver, diamonds, gemstones, and pearls in separate sections or pouches. Diamond-set pieces can scratch softer metals and coloured stones. Gold pieces can tangle and dent if piled together.</p>
        </div>
        <div class="timeline-step" data-num="3">
          <h5>Control Humidity and Light</h5>
          <p>Store your jewelry in a cool, dry room with consistent temperature. Use silica gel desiccant sachets in your jewelry box to absorb excess moisture. Keep the box away from windows and direct sunlight to prevent fading.</p>
        </div>
        <div class="timeline-step" data-num="4">
          <h5>Use Anti-Tarnish Strips</h5>
          <p>Place anti-tarnish strips or tabs in your jewelry box, particularly for silver and gold-alloy pieces. Replace them every 3–6 months. Charcoal sachets are a natural and effective tarnish inhibitor for closed storage boxes.</p>
        </div>
        <div class="timeline-step" data-num="5">
          <h5>Hang Chains Properly</h5>
          <p>Store necklaces and chains hung vertically on a jewelry stand or hooks to prevent tangles and kinks. For very fine or delicate chains, insert the chain through a plastic drinking straw before folding to avoid tangling.</p>
        </div>
        <div class="timeline-step" data-num="6">
          <h5>Travel Storage Precautions</h5>
          <p>Use a dedicated travel jewelry organizer with padded compartments. Wrap each piece individually in microfibre cloth. Keep jewelry in your carry-on bag when flying — never in checked luggage — to avoid loss or damage.</p>
        </div>
      </div>

      <div class="care-cards-grid">
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-box-open"></i></div>
          <h4>Jewelry Boxes & Armoires</h4>
          <p>High-end jewelry armoires with felt-lined drawers and ring rolls are ideal for large collections. Look for models with lockable doors and anti-tarnish interiors for lasting protection.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-bag-shopping" style="color:var(--maroon)"></i></div>
          <h4>Individual Pouches</h4>
          <p>Soft velvet or silk drawstring pouches are excellent for individual piece storage, particularly for pearls, soft gemstones, and delicate gold pieces that might be scratched in a shared compartment.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-lock"></i></div>
          <h4>Home Safes</h4>
          <p>For high-value pieces, a fireproof home safe is worth the investment. Store them inside individual pouches even within the safe. Register high-value pieces with your insurer and keep updated photos.</p>
        </div>
        <div class="care-card">
          <div class="care-card-icon"><i class="fas fa-temperature-low"></i></div>
          <h4>Climate Considerations</h4>
          <p>The ideal jewelry storage environment is 60–70°F (15–21°C) with 40–50% relative humidity. Avoid bathrooms and kitchens where moisture and heat fluctuate dramatically with daily use.</p>
        </div>
      </div>

      <div class="care-cta">
        <h3>Preserve Your Legacy Pieces</h3>
        <p>Ready to protect and maintain your precious jewelry collection? Our expert jewelry consultants at Vaishnavi Jewellers are always available to help you with professional cleaning, servicing, and bespoke care advice.</p>
        <a href="pages/appointment.php" class="btn-maroon me-3">Book a Care Consultation</a>
        <a href="pages/contact.php" class="btn-gold">Contact Our Experts</a>
      </div>
    </div>

  </div>
</main>

<!-- Footer -->
<footer class="lux-footer">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-4">
        <h3>Vaishnavi Jewellers</h3>
        <p style="color:#888; font-size:13px; line-height:1.7;">Handcrafted luxury jewelry celebrated for its artisanship, purity, and elegance since 1985. BIS Hallmarked. GIA Certified Diamonds.</p>
      </div>
      <div class="col-lg-2 col-6">
        <h5>Collections</h5>
        <a href="pages/shop.php?category=Gold">Gold Jewelry</a>
        <a href="pages/shop.php?category=Diamond">Diamond Sets</a>
        <a href="pages/shop.php?category=Gemstone">Gemstone Jewelry</a>
        <a href="pages/shop.php?category=Wedding">Bridal Collection</a>
      </div>
      <div class="col-lg-2 col-6">
        <h5>Services</h5>
        <a href="pages/appointment.php">Book Store Visit</a>
        <a href="pages/care-guide.php" style="color: var(--gold);">Jewelry Care Guide</a>
        <a href="pages/contact.php">Contact Experts</a>
      </div>
      <div class="col-lg-4">
        <h5>Visit Our Boutiques</h5>
        <p style="color:#888; font-size:13px;"><i class="fas fa-map-marker-alt me-2" style="color:var(--gold);"></i>Mumbai Phoenix Mall, Lower Parel</p>
        <p style="color:#888; font-size:13px;"><i class="fas fa-map-marker-alt me-2" style="color:var(--gold);"></i>Delhi Saket Select City Walk</p>
        <p style="color:#888; font-size:13px;"><i class="fas fa-phone me-2" style="color:var(--gold);"></i>+91 22 4901 0291</p>
        <p style="color:#888; font-size:13px;"><i class="fas fa-envelope me-2" style="color:var(--gold);"></i>care@vaishnavijewellers.com</p>
      </div>
    </div>
    <div class="footer-btm">
      &copy; 2026 Vaishnavi Jewellers. All Rights Reserved. &nbsp;|&nbsp;
      <a href="pages/care-guide.php" style="color: #666; font-size:12px;">Jewelry Care Guide</a>
    </div>
  </div>
</footer>

<!-- ========== CARE GUIDE TAB JS ========== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const tabs = document.querySelectorAll('.care-tab');
  const panels = document.querySelectorAll('.care-panel');

  tabs.forEach(tab => {
    tab.addEventListener('click', function() {
      const panelId = 'panel-' + this.getAttribute('data-panel');

      // Deactivate all tabs and panels
      tabs.forEach(t => t.classList.remove('active'));
      panels.forEach(p => p.classList.remove('active'));

      // Activate clicked tab and its panel
      this.classList.add('active');
      const target = document.getElementById(panelId);
      if (target) target.classList.add('active');
      
      // Smooth scroll to top of content area
      document.querySelector('.care-content').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
});
</script>
</body>
</html>
