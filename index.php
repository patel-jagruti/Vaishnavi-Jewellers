<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/includes/catalog_db.php';
require_once __DIR__ . '/includes/cart_db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/base_url.php';

ensure_session();
$pdo = db();
ensure_catalog_seeded($pdo);
ensure_cart_table($pdo);

// Categories
$categories = [
    ['label' => 'Gold', 'href' => 'pages/shop.php?category=Gold', 'img' => 'assets/images/gold.png'],
    ['label' => 'Diamond', 'href' => 'pages/shop.php?category=Diamond', 'img' => 'assets/images/Diamond.png'],
    ['label' => 'Silver', 'href' => 'pages/shop.php?category=Silver', 'img' => 'assets/images/silver/WhatsApp Image 2026-06-29 at 9.11.20 PM (3).jpeg'],
    ['label' => 'Earrings', 'href' => 'pages/shop.php?category=Earrings', 'img' => 'assets/images/Earrings.png'],
    ['label' => 'Rings', 'href' => 'pages/shop.php?category=Rings', 'img' => 'assets/images/Rings.png'],
    ['label' => 'Necklaces', 'href' => 'pages/shop.php?category=Necklaces', 'img' => 'assets/images/Necklaces.png'],
    ['label' => 'Bangles', 'href' => 'pages/shop.php?category=Bangles', 'img' => 'assets/images/Bangles.png'],
    ['label' => 'Bracelets', 'href' => 'pages/shop.php?category=Bracelets', 'img' => 'assets/images/Bracelets.png'],
];
 
$products = catalog_products();
$cartQty = cart_count($pdo);
$wishlistProductIds = [];
try {
  ensure_wishlist_table($pdo);
  $wishlistProductIds = array_map(function($item) {
    return (string)$item['id'];
  }, wishlist_items($pdo));
} catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <base href="/visnavi jwallers/">
  <title>Vaishnavi Jewellers | Premium Luxury Jewellery</title>
  <meta name="description" content="Vaishnavi Jewellers - Premium luxury jewellery since 1985. Gold, diamond, and handcrafted designs for every celebration.">
  <meta name="keywords" content="vaishnavi jewellers, gold jewellery, diamond rings, premium necklaces, bridal collection, bangles, luxury indian jewellery">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= BASE_URL ?>/">
  <meta property="og:title" content="Vaishnavi Jewellers | Premium Luxury Jewellery">
  <meta property="og:description" content="Vaishnavi Jewellers - Premium luxury jewellery since 1985. Gold, diamond, and handcrafted designs for every celebration.">
  <meta property="og:image" content="<?= BASE_URL ?>/assets/images/logo.png">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?= BASE_URL ?>/">
  <meta property="twitter:title" content="Vaishnavi Jewellers | Premium Luxury Jewellery">
  <meta property="twitter:description" content="Vaishnavi Jewellers - Premium luxury jewellery since 1985. Gold, diamond, and handcrafted designs for every celebration.">
  <meta property="twitter:image" content="<?= BASE_URL ?>/assets/images/logo.png">

  <!-- JSON-LD Structured Data for Organization & Local Business -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "JewelryStore",
    "name": "Vaishnavi Jewellers",
    "description": "Premium luxury jewellery since 1985. Gold, diamond, and handcrafted designs for every celebration.",
    "url": "<?= BASE_URL ?>/",
    "logo": "<?= BASE_URL ?>/assets/images/logo.png",
    "telephone": "+91-9876543210",
    "priceRange": "₹₹₹₹",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "Vaishnavi Jewellers Main Road",
      "addressLocality": "Surat",
      "addressRegion": "Gujarat",
      "postalCode": "395003",
      "addressCountry": "IN"
    },
    "openingHoursSpecification": {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": [
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday"
      ],
      "opens": "10:00",
      "closes": "20:00"
    }
  }
  </script>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/premium-home.css">
</head>
<body>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<style>
/* Remove empty spacer gap between header/nav and hero slider */
.main-content {
  display: none !important;
}

/* Hero Banner Layout & Alignment Fixes */
.hero-slider {
  overflow: hidden;
  position: relative;
  /* Push the slider exactly below the fixed header stack */
  margin-top: calc(var(--header-height, 90px) + var(--category-height, 55px) + var(--topbar-height, 38px));
  height: calc(100vh - (var(--header-height, 90px) + var(--category-height, 55px) + var(--topbar-height, 38px)));
  min-height: 550px;
  max-height: 850px;
}
.hero-slide {
  position: absolute;
  inset: 0;
  opacity: 0;
  transition: opacity 0.8s ease-in-out;
  z-index: 1;
  display: block !important;
  background-size: cover;
  background-position: center;
  height: 100%;
}
.hero-slide.active {
  opacity: 1;
  z-index: 2;
}
.hero-slide-video video {
  position: absolute;
  top: 50%;
  left: 50%;
  min-width: 100%;
  min-height: 100%;
  width: auto;
  height: auto;
  transform: translate(-50%, -50%);
  object-fit: cover;
  z-index: 1;
}
.hero-slide-video .overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(0,0,0,0.5) 0%, rgba(0,0,0,0.2) 50%, rgba(0,0,0,0.7) 100%),
              linear-gradient(90deg, rgba(0,0,0,0.6) 0%, transparent 60%);
  z-index: 2;
}
.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(0,0,0,0.4) 0%, rgba(0,0,0,0.1) 50%, rgba(0,0,0,0.6) 100%),
              linear-gradient(90deg, rgba(0,0,0,0.6) 0%, transparent 60%);
}
.hero-content {
  display: flex;
  flex-direction: column;
  justify-content: center;
  height: 100%;
  max-width: 600px;
  text-align: left;
  padding: 40px 15px;
  color: #fff;
  position: relative;
  z-index: 3;
  box-sizing: border-box;
}
.hero-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 11px;
  font-weight: 600;
  color: #D4AF37;
  letter-spacing: 3px;
  text-transform: uppercase;
  margin-bottom: 15px;
  text-shadow: 0 1px 3px rgba(0,0,0,0.3);
  align-self: flex-start; /* Keep tag from stretching full-width */
  width: fit-content;
}
.hero-content h1 {
  font-family: 'Playfair Display', serif;
  font-size: 42px; /* Sleeker, more elegant size */
  font-weight: 600; /* Lighter weight for high-end luxury feel */
  line-height: 1.3;
  color: #fff;
  margin-bottom: 15px;
  text-shadow: 0 2px 8px rgba(0,0,0,0.4);
  letter-spacing: 0.5px;
}
.hero-content h1 span {
  color: #D4AF37;
  display: block;
  font-family: 'Cormorant Garamond', serif;
  font-style: italic; /* Luxurious italic font highlight */
  font-weight: 500;
  font-size: 48px; /* Slightly larger display for italics */
  margin-top: 4px;
}
.hero-content p {
  font-size: 15px; /* Clean, legible body size */
  font-weight: 300;
  line-height: 1.7;
  color: rgba(255,255,255,0.85);
  margin-bottom: 30px;
  max-width: 480px;
  text-shadow: 0 1px 4px rgba(0,0,0,0.3);
}

/* Center-aligned smaller text layout for video slide only */
.hero-content--video {
  margin: 0 auto !important;
  align-items: center !important;
  text-align: center !important;
  max-width: 650px !important;
}
.hero-content--video * {
  text-align: center !important;
}
.hero-content--video .hero-tag {
  font-size: 9px !important; /* Smaller tag */
  letter-spacing: 2px !important;
  padding: 6px 16px !important;
  align-self: center !important;
  margin-bottom: 15px !important;
  border: 1px solid rgba(212,175,55,0.4) !important;
  border-radius: 20px !important;
}
.hero-content--video h1 {
  font-size: 30px !important; /* Elegant smaller title */
  line-height: 1.3 !important;
  margin-bottom: 15px !important;
  letter-spacing: 0.5px !important;
}
.hero-content--video h1 span {
  font-size: 36px !important; /* Smaller italic span phrase */
  margin-top: 4px !important;
}
.hero-content--video p {
  font-size: 13px !important; /* Elegantly smaller body paragraph */
  max-width: 450px !important;
  margin-bottom: 25px !important;
  line-height: 1.6 !important;
}
.hero-content--video .hero-btns {
  display: flex !important;
  justify-content: center !important;
  align-items: center !important;
  align-self: center !important;
  gap: 12px !important;
  width: auto !important;
}
.hero-content--video .hero-btns .btn {
  padding: 10px 20px !important; /* Slightly smaller button sizing */
  font-size: 12px !important;
  border-radius: 20px !important;
}

/* Custom Gold Estimator CSS styles */
.btn-outline-gold {
  border: 1px solid #D4AF37 !important;
  color: #D4AF37 !important;
  background: transparent !important;
  transition: all 0.3s ease !important;
}
.btn-outline-gold:hover, .btn-outline-gold.active {
  background: linear-gradient(135deg, #D4AF37, #F5E6A3, #D4AF37) !important;
  color: #1a1a1a !important;
  border-color: transparent !important;
  box-shadow: 0 4px 15px rgba(212,175,55,0.2) !important;
}
.custom-gold-slider::-webkit-slider-thumb {
  background: #7A1E3A !important;
  box-shadow: 0 0 10px rgba(212,175,55,0.5) !important;
}
.custom-gold-slider::-moz-range-thumb {
  background: #7A1E3A !important;
  box-shadow: 0 0 10px rgba(212,175,55,0.5) !important;
}

/* Premium Hero Buttons Upgrade */
.hero-btns {
  display: flex;
  gap: 15px;
}
.hero-btns .btn-gold {
  background: linear-gradient(135deg, #D4AF37, #F5E6A3, #D4AF37) !important;
  color: #1a1a1a !important;
  border: none !important;
  box-shadow: 0 8px 24px rgba(212,175,55,0.35) !important;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
  cursor: pointer;
}
.hero-btns .btn-gold:hover {
  transform: translateY(-3px) !important;
  box-shadow: 0 12px 30px rgba(212,175,55,0.55) !important;
}
.hero-btns .btn-outline-light {
  border: 2px solid #fff !important;
  background: transparent !important;
  color: #fff !important;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
  cursor: pointer;
}
.hero-btns .btn-outline-light:hover {
  background: #fff !important;
  color: #1a1a1a !important;
  transform: translateY(-3px) !important;
  box-shadow: 0 10px 25px rgba(255,255,255,0.2) !important;
}

/* Responsive Mobile Layout Fixes for Hero Banner & Text */
@media (max-width: 768px) {
  .hero-slider {
    margin-top: calc(var(--header-height, 70px) + var(--topbar-height, 0px)); /* Category nav is hidden on mobile */
    height: 60vh;
    min-height: 400px;
  }
  .hero-content {
    padding: 20px 10px;
  }
  .hero-content h1 {
    font-size: 34px !important;
    margin-bottom: 12px !important;
  }
  .hero-content p {
    font-size: 14px !important;
    margin-bottom: 25px !important;
  }
  .hero-tag {
    font-size: 11px !important;
    margin-bottom: 10px !important;
  }
  .hero-btns {
    flex-direction: column;
    gap: 10px;
  }
  .hero-btns .btn {
    width: 100%;
    text-align: center;
    padding: 12px 20px !important;
  }
}
</style>

<!-- ========== HERO SECTION ========== -->
<section class="hero-slider" id="heroSlider">
  <!-- Slide 1 - Video -->
  <div class="hero-slide hero-video active">
    <video autoplay muted loop playsinline>
      <source src="assets/video/bridal%201.mp4" type="video/mp4">
    </video>
    <div class="overlay"></div>
    <div class="hero-content">
      <span class="hero-tag">✨ Exclusive Bridal Collection 2026</span>
      <h1>Every Bride <span>Deserves Timeless Elegance</span></h1>
      <p>Discover our handcrafted bridal jewellery, designed to make your special day unforgettable</p>
      <div class="hero-btns">
        <a href="pages/shop.php" class="btn btn-gold">Explore Collection</a>
        <a href="pages/store.php" class="btn btn-outline-light">Book Appointment</a>
      </div>
    </div>
  </div>

  <!-- Slide 2 - Photo -->
  <div class="hero-slide" style="background-image: url('assets/images/banar1.png')">
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <span class="hero-tag">✨ Diamond Collection</span>
      <h1>Diamonds That <span>Define Love</span></h1>
      <p>Exquisite diamond jewellery crafted for your most cherished moments</p>
      <div class="hero-btns">
        <a href="pages/shop.php?category=Diamond" class="btn btn-gold">Shop Diamond</a>
        <a href="pages/store.php" class="btn btn-outline-light">Book Appointment</a>
      </div>
    </div>
  </div>

  <!-- Slide 3 - Photo -->
<div class="hero-slide" style="background-image: url('assets/images/banar2.png')">
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <span class="hero-tag">✨ Gold Elegance</span>
      <h1>Pure Gold <span>Timeless Beauty</span></h1>
      <p>22K & 24K gold jewellery handcrafted by master artisans</p>
      <div class="hero-btns">
        <a href="pages/shop.php?category=Gold" class="btn btn-gold">Shop Gold</a>
        <a href="pages/shop.php?category=Wedding" class="btn btn-outline-light">Bridal Collection</a>
      </div>
    </div>
  </div>
</section>

<!-- Hero Controls -->
<div class="hero-nav">
  <button class="hero-arrow prev" onclick="changeSlide(-1)"><i class="fas fa-chevron-left"></i></button>
  <button class="hero-arrow next" onclick="changeSlide(1)"><i class="fas fa-chevron-right"></i></button>
</div>
<div class="hero-dots">
  <span class="active" onclick="goToSlide(0)"></span>
  <span onclick="goToSlide(1)"></span>
  <span onclick="goToSlide(2)"></span>
</div>

<!-- ========== GOLD RATE TICKER ========== -->
<section class="rate-ticker">
  <div class="container">
    <div class="rate-grid">
      <div class="rate-item">
        <i class="fas fa-coins"></i>
        <div>
          <span>24K Gold</span>
          <strong>₹7,850</strong>
          <small class="up">+0.5%</small>
        </div>
      </div>
      <div class="rate-item">
        <i class="fas fa-circle"></i>
        <div>
          <span>22K Gold</span>
          <strong>₹7,200</strong>
          <small class="up">+0.3%</small>
        </div>
      </div>
      <div class="rate-item">
        <i class="fas fa-circle"></i>
        <div>
          <span>18K Gold</span>
          <strong>₹5,900</strong>
          <small class="down">-0.2%</small>
        </div>
      </div>
      <div class="rate-item">
        <i class="fas fa-gem"></i>
        <div>
          <span>Silver</span>
          <strong>₹95</strong>
          <small class="up">+1.2%</small>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ========== SHOP BY CATEGORY ========== -->
<section class="category-section">
  <div class="container">
    <div class="section-header">
      <h2>Shop By Category</h2>
      <p>Discover our curated collections</p>
    </div>
    <div class="category-grid">
      <?php foreach ($categories as $cat): ?>
      <a href="<?= $cat['href'] ?>" class="category-card">
        <div class="category-img">
          <img src="<?= $cat['img'] ?>" alt="<?= $cat['label'] ?>">
        </div>
        <span class="category-name"><?= $cat['label'] ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ========== FEATURED COLLECTIONS ========== -->
<style>
/* Featured Collections - Flexbox Single Row Carousel */
.luxury-collection {
  width: 100%;
  max-width: 1800px;
  margin: 0 auto;
  padding: 20px 20px 40px;
  background: #F8F6F2;
  position: relative;
  overflow: hidden;
}
.luxury-collection::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, transparent, #D4AF37, transparent);
}
.luxury-collection__header {
  text-align: center;
  margin-bottom: 20px;
  position: relative;
  z-index: 10;
}
.luxury-collection__kicker {
  color: #D4AF37;
  font-family: 'Cormorant Garamond', serif;
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 4px;
  text-transform: uppercase;
  margin-bottom: 10px;
  display: inline-block;
}
.luxury-collection__title {
  color: #1a1a1a;
  font-family: 'Cormorant Garamond', serif;
  font-size: 42px;
  font-weight: 600;
  letter-spacing: 1px;
  margin: 0 0 16px;
  line-height: 1.2;
}
.luxury-collection__subtitle {
  color: #666;
  font-size: 17px;
  font-weight: 400;
  max-width: 650px;
  margin: 0 auto;
  line-height: 1.7;
}

/* Carousel Container - FLEXBOX_single row */
.luxury-collection__carousel {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-wrap: nowrap;
  overflow: hidden;
  height: 620px;
  width: 100%;
}

/* Cards Wrapper - SINGLE ROW flexbox */
.luxury-cards-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-wrap: nowrap;
  gap: 20px;
  width: 100%;
  height: 100%;
  overflow: visible;
}

/* Card - SAME SIZE for all cards, only scale changes */
.luxury-card {
  flex-shrink: 0;
  width: 380px;
  height: 580px;
  transition: all 0.5s ease;
  cursor: pointer;
}

/* Base card styling */
.luxury-card .luxury-card__inner {
  width: 100%;
  height: 100%;
}

/* Card Base */
.luxury-card__inner {
  position: relative;
  border-radius: 24px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}
.luxury-card:hover .luxury-card__inner {
  box-shadow: 0 30px 80px rgba(0,0,0,0.25);
}
.luxury-card__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.luxury-card__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(0,0,0,0) 30%, rgba(0,0,0,0.6) 80%, rgba(0,0,0,0.9) 100%);
  pointer-events: none;
}
.luxury-card__content {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 28px;
  z-index: 5;
}
.luxury-card__title {
  color: #fff;
  font-family: 'Cormorant Garamond', serif;
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 6px;
  line-height: 1.2;
}
.luxury-card__count {
  color: rgba(255,255,255,0.85);
  font-size: 13px;
  font-weight: 500;
  margin-bottom: 12px;
}
.luxury-card__btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 140px;
  height: 40px;
  background: linear-gradient(135deg, #D4AF37, #F0C75E, #D4AF37);
  border-radius: 999px;
  color: #1a1a1a;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  text-decoration: none;
  box-shadow: 0 6px 20px rgba(212,175,55,0.4);
  transition: all 0.3s ease;
}
.luxury-card__btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 30px rgba(212,175,55,0.55);
}

/* Center Active Card - SAME dimensions, only scale changes */
.luxury-card--center {
  transform: scale(1.15);
  z-index: 100;
  opacity: 1;
}
.luxury-card--center .luxury-card__inner {
  box-shadow: 0 30px 70px rgba(0,0,0,0.25);
}

/* Side Cards (adjacent to center) - SAME dimensions, only scale changes */
.luxury-card--side {
  transform: scale(0.9);
  opacity: 0.8;
}
.luxury-card--side:hover {
  transform: scale(0.95);
  opacity: 1;
}

/* Outer Cards (furthest from center) - SAME dimensions, only scale changes */
.luxury-card--outer {
  transform: scale(0.85);
  opacity: 0.7;
}
.luxury-card--outer:hover {
  transform: scale(0.9);
  opacity: 0.85;
}

/* Navigation Arrows */
.luxury-collection__nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: rgba(255,255,255,0.9);
  border: 1px solid rgba(212,175,55,0.5);
  backdrop-filter: blur(10px);
  color: #D4AF37;
  font-size: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 100;
  transition: all 0.3s ease;
}
.luxury-collection__nav:hover {
  background: rgba(212,175,55,0.15);
  transform: translateY(-50%) translateY(-2px);
}
.luxury-collection__nav--prev {
  left: 20px;
}
.luxury-collection__nav--next {
  right: 20px;
}

/* Responsive - ALL cards keep same dimensions */
@media (max-width: 1400px) {
  .luxury-card { width: 340px; height: 520px; }
  .luxury-collection__carousel { height: 580px; }
}
@media (max-width: 1024px) {
  .luxury-card { width: 300px; height: 460px; }
  .luxury-collection__carousel { height: 500px; }
  .luxury-collection__title { font-size: 36px; }
}
@media (max-width: 768px) {
  .luxury-collection__carousel { height: 450px; }
  .luxury-card { width: 260px; height: 400px; }
  .luxury-collection__title { font-size: 28px; }
  .luxury-card__inner { border-radius: 18px; }
  .luxury-card__title { font-size: 20px; }
  .luxury-card__btn { width: 120px; height: 36px; font-size: 10px; }
  .luxury-collection__nav { width: 40px; height: 40px; font-size: 14px; }
  .luxury-cards-wrapper { gap: 15px; }
}
</style>

<div class="luxury-collection">
  <div class="luxury-collection__header">
    <div class="luxury-collection__kicker">Featured Collections</div>
    <h2 class="luxury-collection__title">Discover Our Signature Jewellery Collections</h2>
    <p class="luxury-collection__subtitle">Premium designs curated for every celebration—crafted to shine with timeless elegance.</p>
  </div>

  <div class="luxury-collection__carousel" id="collectionCarousel">
    <button class="luxury-collection__nav luxury-collection__nav--prev" id="collectionPrev" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
    <div class="luxury-cards-wrapper" id="collectionCards"></div>
    <button class="luxury-collection__nav luxury-collection__nav--next" id="collectionNext" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
  </div>
</div>

<script>
(function(){
  var collections = [
      {"title":"Temple Jewellery","href":"pages/shop.php?category=Temple","count":"120+ Designs","img":"assets/images/Bangles.png"},
      {"title":"Kundan Collection","href":"pages/shop.php?category=Kundan","count":"95+ Designs","img":"assets/images/Necklaces.png"},
      {"title":"Polki Collection","href":"pages/shop.php?category=Polki","count":"85+ Designs","img":"assets/images/Pendants.png"},
      {"title":"Jadau Jewellery","href":"pages/shop.php?category=Jadau","count":"75+ Designs","img":"assets/images/Earrings.png"},
      {"title":"Diamond Collection","href":"pages/shop.php?category=Diamond","count":"160+ Designs","img":"assets/images/Diamond.png"},
      {"title":"Antique Collection","href":"pages/shop.php?category=Antique","count":"90+ Designs","img":"assets/images/Bracelets.png"},
      {"title":"Bridal Collection","href":"pages/shop.php?category=Bridal","count":"130+ Designs","img":"assets/images/Rings.png"},
      {"title":"Wedding Special","href":"pages/shop.php?category=Wedding","count":"110+ Designs","img":"assets/images/gold.png"}
    ];
  const wrapper = document.getElementById('collectionCards');
  const prevBtn = document.getElementById('collectionPrev');
  const nextBtn = document.getElementById('collectionNext');
  let currentIndex = 0;
  let autoplayInterval;

  function getPositionClass(index, offset) {
    if (offset === 0) return 'luxury-card--center';
    if (offset === -1 || offset === 1) return 'luxury-card--side';
    return 'luxury-card--outer';
  }

  function getVisibleCards() {
    return [-2,-1,0,1,2].map(offset => ({
      index: (currentIndex + offset + collections.length) % collections.length,
      offset: offset,
      cls: getPositionClass(currentIndex, offset)
    }));
  }

  function renderCards() {
    if (!wrapper) return;
    wrapper.innerHTML = getVisibleCards().map(v => {
      const c = collections[v.index];
      return `
        <div class="luxury-card ${v.cls}" data-index="${v.index}">
          <div class="luxury-card__inner">
            <img class="luxury-card__img" src="${c.img}" alt="${c.title}" loading="lazy">
            <div class="luxury-card__overlay"></div>
            <div class="luxury-card__content">
              <div class="luxury-card__title">${c.title}</div>
              <div class="luxury-card__count">${c.count}</div>
              <a class="luxury-card__btn" href="${c.href}">Explore</a>
            </div>
          </div>
        </div>
      `;
    }).join('');
  }

  function slideNext() {
    currentIndex = (currentIndex + 1) % collections.length;
    renderCards();
  }

  function slidePrev() {
    currentIndex = (currentIndex - 1 + collections.length) % collections.length;
    renderCards();
  }

  function startAutoplay() {
    autoplayInterval = setInterval(slideNext, 4000);
  }

  function stopAutoplay() {
    clearInterval(autoplayInterval);
  }

  if (prevBtn && nextBtn) {
    prevBtn.addEventListener('click', () => { stopAutoplay(); slidePrev(); startAutoplay(); });
    nextBtn.addEventListener('click', () => { stopAutoplay(); slideNext(); startAutoplay(); });
  }

  const carousel = document.getElementById('collectionCarousel');
  if (carousel) {
    carousel.addEventListener('mouseenter', stopAutoplay);
    carousel.addEventListener('mouseleave', startAutoplay);
  }

  renderCards();
  startAutoplay();
})();
</script>

<style>
.luxury-trending {
  padding: 80px 0 100px;
  background: linear-gradient(180deg, #faf9f6 0%, #fffcf5 100%);
  position: relative;
  overflow: hidden;
}
.luxury-trending__header {
  text-align: center;
  margin-bottom: 40px;
}
.luxury-trending__kicker {
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 3px;
  text-transform: uppercase;
  color: #c9a227;
  margin-bottom: 12px;
}
.luxury-trending__title {
  font-family: 'Cormorant Garamond', serif;
  font-size: 42px;
  font-weight: 500;
  color: #1a1a1a;
  margin: 0 0 12px;
  position: relative;
  display: inline-block;
}
.luxury-trending__title::after {
  content: '';
  position: absolute;
  bottom: -8px;
  left: 50%;
  transform: translateX(-50%);
  width: 50px;
  height: 2px;
  background: linear-gradient(90deg, #D4AF37, #F0C75E, #D4AF37);
  border-radius: 2px;
}
.luxury-trending__subtitle {
  font-size: 15px;
  color: #666;
  margin: 18px 0 0;
}
.luxury-trending__carousel {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-wrap: nowrap;
  overflow: hidden;
  height: 620px;
  width: 100%;
}
.luxury-trending__cards {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-wrap: nowrap;
  gap: 20px;
  width: 100%;
  height: 580px;
  overflow: visible;
}
.luxury-trend-card {
  flex-shrink: 0;
  width: 380px;
  height: 580px;
  transition: all 0.5s ease;
  cursor: pointer;
  display: flex;
  align-items: stretch;
}
.luxury-trend-card__inner {
  width: 100%;
  height: 100%;
  background: #fff;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0,0,0,0.12);
  position: relative;
  display: flex;
  flex-direction: column;
}
.luxury-trend-card--center {
  transform: scale(1.15);
  z-index: 100;
  opacity: 1;
}
.luxury-trend-card--center .luxury-trend-card__inner {
  box-shadow: 0 30px 70px rgba(0,0,0,0.25);
}
.luxury-trend-card--side {
  transform: scale(0.9);
  opacity: 0.8;
  z-index: 50;
}
.luxury-trend-card--side:hover {
  transform: scale(0.95);
  opacity: 1;
}
.luxury-trend-card--side .luxury-trend-card__inner {
  box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}
.luxury-trend-card--outer {
  transform: scale(0.85);
  opacity: 0.7;
  z-index: 10;
}
.luxury-trend-card--outer:hover {
  transform: scale(0.9);
  opacity: 0.85;
}
.luxury-trend-card--outer .luxury-trend-card__inner {
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.luxury-trend-card__img-wrap {
  width: 100%;
  height: 340px;
  overflow: hidden;
  background: #f5f5f5;
  position: relative;
}
.luxury-trend-card__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}
.luxury-trend-card:hover .luxury-trend-card__img {
  transform: scale(1.08);
}
.luxury-trend-card__badge {
  position: absolute;
  top: 12px;
  left: 0;
  padding: 5px 14px;
  font-size: 9px;
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
  background: linear-gradient(135deg, #D4AF37, #F0C75E);
  color: #1a1a1a;
  border-radius: 0 12px 12px 0;
  z-index: 5;
}
.luxury-trend-card__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, transparent 50%, rgba(0,0,0,0.5));
  opacity: 0;
  transition: all 0.3s ease;
}
.luxury-trend-card:hover .luxury-trend-card__overlay {
  opacity: 1;
}
.luxury-trend-card__actions {
  position: absolute;
  bottom: 15px;
  left: 15px;
  right: 15px;
  display: flex;
  gap: 8px;
  opacity: 0;
  transform: translateY(15px);
  transition: all 0.4s ease;
}
.luxury-trend-card:hover .luxury-trend-card__actions {
  opacity: 1;
  transform: translateY(0);
}
.luxury-trend-card__action {
  flex: 1;
  padding: 10px;
  border: none;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.5px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}
.luxury-trend-card__action--wish {
  background: rgba(255,255,255,0.95);
  color: #1a1a1a;
}
.luxury-trend-card__action--wish:hover {
  background: #ffeaea;
  color: #e53935;
}
.luxury-trend-card__action--cart {
  background: linear-gradient(135deg, #c9a227, #e8d078);
  color: #0a0a0a;
}
.luxury-trend-card__action--cart:hover {
  box-shadow: 0 6px 20px rgba(201,162,39,0.45);
}
.luxury-trend-card__info {
  padding: 18px;
  text-align: center;
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.luxury-trend-card__cat {
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #c9a227;
  margin-bottom: 8px;
}
.luxury-trend-card__name {
  font-family: 'Cormorant Garamond', serif;
  font-size: 20px;
  font-weight: 600;
  color: #1a1a1a;
  margin: 0 0 10px;
  line-height: 1.3;
}
.luxury-trend-card__price-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}
.luxury-trend-card__price {
  font-size: 22px;
  font-weight: 700;
  color: #1a1a1a;
}
.luxury-trend-card__old-price {
  font-size: 13px;
  color: #999;
  text-decoration: line-through;
}
.luxury-trend-card__discount {
  font-size: 10px;
  font-weight: 600;
  color: #4caf50;
  padding: 2px 6px;
  background: rgba(76,175,80,0.12);
  border-radius: 4px;
}
.luxury-trending__nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: rgba(255,255,255,0.9);
  border: 1px solid rgba(212,175,55,0.5);
  backdrop-filter: blur(10px);
  color: #D4AF37;
  font-size: 18px;
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 200;
  display: flex;
  align-items: center;
  justify-content: center;
}
.luxury-trending__nav:hover {
  background: rgba(212,175,55,0.15);
  transform: translateY(-50%) translateY(-2px);
}
.luxury-trending__nav--prev { left: 20px; }
.luxury-trending__nav--next { right: 20px; }
@media (max-width: 1400px) {
  .luxury-trend-card { width: 340px; height: 520px; }
}
@media (max-width: 1024px) {
  .luxury-trend-card { width: 300px; height: 460px; }
  .luxury-trending__title { font-size: 36px; }
}
@media (max-width: 768px) {
  .luxury-trend-card { width: 260px; height: 400px; }
  .luxury-trending__title { font-size: 28px; }
  .luxury-trending__nav { width: 40px; height: 40px; font-size: 14px; }
  .luxury-trend-card__inner { border-radius: 18px; }
  .luxury-trend-card__name { font-size: 18px; }
  .luxury-trend-card__action { padding: 8px; font-size: 10px; }
}
</style>

<div class="luxury-trending">
  <div class="luxury-trending__header">
    <div class="luxury-trending__kicker">Trending Collection</div>
    <h2 class="luxury-trending__title">Most Loved Designs This Season</h2>
    <p class="luxury-trending__subtitle">Discover what's capturing hearts this season</p>
  </div>

  <div class="luxury-trending__carousel" id="trendingCarousel">
    <button class="luxury-trending__nav luxury-trending__nav--prev" id="trendingPrev" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
    <div class="luxury-trending__cards" id="trendingCards"></div>
    <button class="luxury-trending__nav luxury-trending__nav--next" id="trendingNext" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
  </div>
</div>

<?php
$trendingProducts = [];
try {
    $db = db();
    $trendingStmt = $db->query("
      SELECT p.id, p.name, c.name AS category_name, 
             p.base_price, p.price_type, p.purity, p.making_charge_percent, p.weight_grams,
             p.discount_percent,
             (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC LIMIT 1) AS image_url
      FROM products p
      LEFT JOIN categories c ON c.id = p.category_id
      WHERE p.is_active = 1
      ORDER BY p.is_featured DESC, p.id DESC
      LIMIT 8
    ");
    $dbTrending = $trendingStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($dbTrending as $tp) {
        $price = base_price_now($tp);
        $salePrice = price_now($tp);
        $disc = (float)$tp['discount_percent'];
        $trendingProducts[] = [
            'id' => (string)$tp['id'],
            'name' => (string)$tp['name'],
            'category' => (string)($tp['category_name'] ?? 'Jewellery'),
            'price' => $price,
            'sale_price' => $salePrice,
            'discount_percent' => $disc,
            'image_url' => $tp['image_url'] ? asset(product_image($tp['image_url'])) : ''
        ];
    }
} catch (Throwable $e) {}

if (empty($trendingProducts)) {
    $trendingProducts = [
        ["id" => "1", "name" => "Temple Silk Kundan Set", "category" => "Temple", "price" => 45000, "sale_price" => 38000, "discount_percent" => 15, "image_url" => ""],
        ["id" => "2", "name" => "Polki Meena Necklace", "category" => "Polki", "price" => 85000, "sale_price" => 72000, "discount_percent" => 15, "image_url" => ""],
        ["id" => "3", "name" => "Jadau Bridal Set", "category" => "Jadau", "price" => 120000, "sale_price" => 95000, "discount_percent" => 20, "image_url" => ""],
        ["id" => "4", "name" => "Diamond Solitaire Ring", "category" => "Diamond", "price" => 65000, "sale_price" => 55000, "discount_percent" => 15, "image_url" => ""],
        ["id" => "5", "name" => "Antique Kada Bracelet", "category" => "Antique", "price" => 35000, "sale_price" => 28000, "discount_percent" => 20, "image_url" => ""],
        ["id" => "6", "name" => "Bridal Haar Necklace", "category" => "Bridal", "price" => 95000, "sale_price" => 80000, "discount_percent" => 15, "image_url" => ""],
        ["id" => "7", "name" => "Wedding Chooda Set", "category" => "Wedding", "price" => 28000, "sale_price" => 22000, "discount_percent" => 20, "image_url" => ""],
        ["id" => "8", "name" => "Kundan Jhumka Earrings", "category" => "Kundan", "price" => 18000, "sale_price" => 15000, "discount_percent" => 15, "image_url" => ""]
    ];
}
?>
<script>
window.wishlistProductIds = new Set(<?= json_encode($wishlistProductIds) ?>);
(function(){
  var products = <?= json_encode($trendingProducts) ?>;
  var wrapper = document.getElementById('trendingCards');
  var prevBtn = document.getElementById('trendingPrev');
  var nextBtn = document.getElementById('trendingNext');
  var currentIndex = 0;
  var autoplayInterval;

  function getPositionClass(offset) {
    if (offset === 0) return 'luxury-trend-card--center';
    if (offset === -1 || offset === 1) return 'luxury-trend-card--side';
    return 'luxury-trend-card--outer';
  }

  function getVisibleCards() {
    return [-2,-1,0,1,2].map(function(offset) {
      var idx = (currentIndex + offset + products.length) % products.length;
      return { index: idx, offset: offset, cls: getPositionClass(offset) };
    });
  }

  var categoryImages = {
    'Temple': 'assets/images/Bangles.png',
    'Kundan': 'assets/images/Necklaces.png',
    'Polki': 'assets/images/Pendants.png',
    'Jadau': 'assets/images/Earrings.png',
    'Diamond': 'assets/images/Diamond.png',
    'Antique': 'assets/images/Bracelets.png',
    'Bridal': 'assets/images/Rings.png',
    'Wedding': 'assets/images/gold.png'
  };

  function getCategoryImage(cat) {
    var c = cat || '';
    return categoryImages[c] || categoryImages['Temple'];
  }

  function renderCards() {
    if (!wrapper) return;
    wrapper.innerHTML = getVisibleCards().map(function(v) {
      var p = products[v.index];
      var price = p.sale_price && p.sale_price > 0 ? p.sale_price : p.price;
      var oldPrice = p.price;
      var discount = p.discount_percent || 0;
      var img = p.image_url ? p.image_url : getCategoryImage(p.category);
      var badge = v.offset === 0 && v.index < 3 ? '<span class="luxury-trend-card__badge">Hot</span>' : '';
      return '<div class="luxury-trend-card '+v.cls+'" data-index="'+v.index+'" data-product-id="'+p.id+'">'+
        '<div class="luxury-trend-card__inner">'+
          '<div class="luxury-trend-card__img-wrap">'+
            '<img class="luxury-trend-card__img" src="'+img+'" alt="'+p.name+'" loading="lazy">'+
            badge+'<div class="luxury-trend-card__overlay"></div>'+
            '<div class="luxury-trend-card__actions">'+
              '<button class="luxury-trend-card__action luxury-trend-card__action--wish" data-wishlist-toggle data-product-id="'+p.id+'"><i class="'+((window.wishlistProductIds && window.wishlistProductIds.has(String(p.id))) ? 'fas' : 'far')+' fa-heart"></i> Wish</button>'+
              '<button class="luxury-trend-card__action luxury-trend-card__action--cart" data-cart="'+p.id+'"><i class="fas fa-shopping-bag"></i> Add</button>'+
            '</div>'+
          '</div>'+
          '<div class="luxury-trend-card__info">'+
            '<div class="luxury-trend-card__cat">'+p.category+'</div>'+
            '<div class="luxury-trend-card__name">'+p.name+'</div>'+
            '<div class="luxury-trend-card__price-row">'+
                            '<span class="luxury-trend-card__price">\u20B9'+price.toLocaleString()+'</span>'+
              (discount > 0 ? '<span class="luxury-trend-card__old-price">\u20B9'+oldPrice.toLocaleString()+'</span><span class="luxury-trend-card__discount">-'+Math.floor(discount)+'%</span>' : '')+
            '</div>'+
          '</div>'+
        '</div>'+
      '</div>';
    }).join('');
  }

  function slideNext() {
    currentIndex = (currentIndex + 1) % products.length;
    renderCards();
  }

  function slidePrev() {
    currentIndex = (currentIndex - 1 + products.length) % products.length;
    renderCards();
  }

  function startAutoplay() {
    autoplayInterval = setInterval(slideNext, 3500);
  }

  function stopAutoplay() {
    clearInterval(autoplayInterval);
  }

  if (prevBtn && nextBtn) {
    prevBtn.addEventListener('click', function() { stopAutoplay(); slidePrev(); startAutoplay(); });
    nextBtn.addEventListener('click', function() { stopAutoplay(); slideNext(); startAutoplay(); });
  }

  var trendingCarousel = document.getElementById('trendingCarousel');
  if (trendingCarousel) {
    trendingCarousel.addEventListener('mouseenter', stopAutoplay);
    trendingCarousel.addEventListener('mouseleave', startAutoplay);
  }

  renderCards();
  startAutoplay();
})();
</script>

<!-- ========== WEDDING BANNER ========== -->
<section class="wedding-banner">
  <div class="container">
    <div class="wedding-content">
      <span>Wedding Collection</span>
      <h2>Celebrate Your Forever</h2>
      <p>Exquisite designs for your most cherished moments</p>
      <a href="pages/shop.php?category=Wedding" class="btn btn-gold">Explore Now</a>
    </div>
  </div>
</section>

<!-- ========== WHY CHOOSE US ========== -->
<section class="features-section">
  <div class="container">
    <div class="section-header">
      <h2>Why Choose Us</h2>
      <p>Our commitment to excellence since 1985</p>
    </div>
    <div class="features-grid">
      <div class="feature-box">
        <i class="fas fa-certificate"></i>
        <h4>BIS Hallmark</h4>
        <p>Authentic purity guaranteed</p>
      </div>
      <div class="feature-box">
        <i class="fas fa-shield-alt"></i>
        <h4>Secure Payment</h4>
        <p>Protected transactions</p>
      </div>
      <div class="feature-box">
        <i class="fas fa-undo"></i>
        <h4>Easy Returns</h4>
        <p>30-day return policy</p>
      </div>
      <div class="feature-box">
        <i class="fas fa-truck"></i>
        <h4>Free Shipping</h4>
        <p>Pan India delivery</p>
      </div>
      <div class="feature-box">
        <i class="fas fa-gem"></i>
        <h4>Lifetime Exchange</h4>
        <p>Upgrade anytime</p>
      </div>
    </div>
  </div>
</section>

<!-- ========== TESTIMONIALS ========== -->
<section class="testimonial-section">
  <div class="container">
    <div class="section-header">
      <h2>What Our Clients Say</h2>
      <p>Real stories from happy customers</p>
    </div>

    <div class="testimonial-grid">
      <div class="testimonial-card">
        <div class="testimonial-head">
          <div class="testimonial-avatar">PS</div>
          <div class="testimonial-meta">
            <h5>Priya Sharma</h5>
            <span>Mumbai</span>
          </div>
          <div class="stars" aria-label="5 star rating">
            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
          </div>
        </div>
        <p>“The craftsmanship is exceptional. My engagement ring looked even more beautiful than I imagined.”</p>
        <div class="testimonial-tag"><i class="fas fa-gem"></i> Bridal Special</div>
      </div>

      <div class="testimonial-card">
        <div class="testimonial-head">
          <div class="testimonial-avatar">AM</div>
          <div class="testimonial-meta">
            <h5>Arun Mehta</h5>
            <span>Delhi</span>
          </div>
          <div class="stars" aria-label="5 star rating">
            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
          </div>
        </div>
        <p>“Quality and customer service are consistently excellent. Our family trusts Vaishnavi for every celebration.”</p>
        <div class="testimonial-tag"><i class="fas fa-shield-alt"></i> Verified Quality</div>
      </div>

      <div class="testimonial-card">
        <div class="testimonial-head">
          <div class="testimonial-avatar">SJ</div>
          <div class="testimonial-meta">
            <h5>Sonia Jain</h5>
            <span>Bangalore</span>
          </div>
          <div class="stars" aria-label="5 star rating">
            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
          </div>
        </div>
        <p>“We bought a necklace for my daughter’s wedding. The design was stunning and the finishing was perfect.”</p>
        <div class="testimonial-tag"><i class="fas fa-heart"></i> Loved by Family</div>
      </div>
    </div>
  </div>
</section>


<!-- ========== INSTAGRAM ========== -->
<section class="insta-section">
  <div class="container">
    <div class="section-header">
      <h2>Follow Us @VaishnaviJewellers</h2>
      <p>#VaishnaviMoments</p>
    </div>
  </div>
  <div class="insta-grid">
    <div class="insta-item" style="background-image: url('assets/images/bat3.png')"><i class="fab fa-instagram"></i></div>
    <div class="insta-item" style="background-image: url('assets/images/bat2.png')"><i class="fab fa-instagram"></i></div>
    <div class="insta-item" style="background-image: url('assets/images/bat5.png')"><i class="fab fa-instagram"></i></div>
    <div class="insta-item" style="background-image: url('assets/images/bat4.png')"><i class="fab fa-instagram"></i></div>
    <div class="insta-item" style="background-image: url('assets/images/bat6.png')"><i class="fab fa-instagram"></i></div>
    <div class="insta-item" style="background-image: url('assets/images/bat7.png')"><i class="fab fa-instagram"></i></div>
  </div>
</section>
<!-- ========== OCCASION COLLECTIONS (Feature F) ========== -->
<section style="background:#fff; padding:60px 0;">
<div class="container">
  <div style="text-align:center; margin-bottom:36px;">
    <span style="font-size:10px; font-weight:700; letter-spacing:3px; text-transform:uppercase; color:#D4AF37; display:block; margin-bottom:10px;">Shop By Moment</span>
    <h2 style="font-family:'Playfair Display',serif; font-size:32px; font-weight:700; color:#1A1A1A; margin-bottom:10px;">Curated For Every Occasion</h2>
    <p style="font-size:14px; color:#888; font-family:'Poppins',sans-serif; max-width:500px; margin:0 auto; line-height:1.7;">From grand weddings to intimate celebrations — discover jewellery that speaks the language of your moment.</p>
  </div>

  <!-- Occasion Tabs -->
  <div style="display:flex; justify-content:center; gap:10px; flex-wrap:wrap; margin-bottom:32px;" id="occasionTabs">
    <button class="occ-tab active" data-occ="wedding" style="padding:10px 24px; border-radius:30px; font-size:12px; font-weight:700; cursor:pointer; transition:all 0.3s; border:1.5px solid #7A1E3A; background:linear-gradient(135deg,#7A1E3A,#5a1529); color:#fff; font-family:'Poppins',sans-serif; box-shadow:0 4px 14px rgba(122,30,58,0.2);">💍 Wedding</button>
    <button class="occ-tab" data-occ="anniversary" style="padding:10px 24px; border-radius:30px; font-size:12px; font-weight:700; cursor:pointer; transition:all 0.3s; border:1.5px solid #ddd; background:#fff; color:#666; font-family:'Poppins',sans-serif;">💑 Anniversary</button>
    <button class="occ-tab" data-occ="birthday" style="padding:10px 24px; border-radius:30px; font-size:12px; font-weight:700; cursor:pointer; transition:all 0.3s; border:1.5px solid #ddd; background:#fff; color:#666; font-family:'Poppins',sans-serif;">🎂 Birthday</button>
    <button class="occ-tab" data-occ="festival" style="padding:10px 24px; border-radius:30px; font-size:12px; font-weight:700; cursor:pointer; transition:all 0.3s; border:1.5px solid #ddd; background:#fff; color:#666; font-family:'Poppins',sans-serif;">✨ Festival</button>
  </div>

  <!-- Occasion Panels -->
  <?php
  $occasions = [
    'wedding'     => ['emoji'=>'💍','label'=>'Wedding Collection','desc'=>'Timeless bridal sets crafted for your most special day','keywords'=>['bridal','necklace','set','ring','bangles'],'color'=>'#7A1E3A'],
    'anniversary' => ['emoji'=>'💑','label'=>'Anniversary Gifts','desc'=>'Celebrate milestones with jewels as enduring as your love','keywords'=>['diamond','pendant','bracelet','earring'],'color'=>'#9B2C4E'],
    'birthday'    => ['emoji'=>'🎂','label'=>'Birthday Specials','desc'=>'Make their day unforgettable with a glittering surprise','keywords'=>['pendant','ring','gold','earring'],'color'=>'#B8860B'],
    'festival'    => ['emoji'=>'✨','label'=>'Festival Collection','desc'=>'Shine bright at Navratri, Diwali, Akha Teej & more','keywords'=>['gold','bangles','necklace','earring','set'],'color'=>'#D4AF37'],
  ];
  foreach($occasions as $key => $occ):
  ?>
  <div class="occ-panel" id="occ-<?= $key ?>" style="display:<?= $key==='wedding'?'block':'none' ?>; animation:occFade 0.4s ease;">
    <div style="text-align:center; margin-bottom:24px;">
      <div style="font-size:36px; margin-bottom:6px;"><?= $occ['emoji'] ?></div>
      <h3 style="font-family:'Playfair Display',serif; font-size:22px; font-weight:700; color:#1A1A1A; margin-bottom:4px;"><?= $occ['label'] ?></h3>
      <p style="font-size:12px; color:#888; font-family:'Poppins',sans-serif;"><?= $occ['desc'] ?></p>
    </div>
    <?php
    $occProducts = [];
    try {
      $db2 = db();
      $stmt2 = $db2->prepare("SELECT p.id, p.name, p.base_price, p.discount_percent,
        (SELECT pi.image_url FROM product_images pi WHERE pi.product_id=p.id ORDER BY pi.is_primary DESC LIMIT 1) AS image_url
        FROM products p ORDER BY RAND() LIMIT 4");
      $stmt2->execute();
      $occProducts = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e) { $occProducts = []; }
    ?>
    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:20px; max-width:900px; margin:0 auto;">
      <?php foreach($occProducts as $op):
        $opImg = !empty($op['image_url']) ? asset($op['image_url']) : asset('images/Rings.png');
        $opPrice = price_now($op);
        $opDisc = (float)($op['discount_percent']??0);
      ?>
      <a href="pages/product.php?id=<?= (int)$op['id'] ?>" style="text-decoration:none; display:block; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.06); transition:all 0.3s; border:1px solid rgba(0,0,0,0.04);" onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 12px 32px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='';this.style.boxShadow='0 4px 20px rgba(0,0,0,0.06)';">
        <div style="height:180px; background:linear-gradient(135deg,#f8f5f0,#FAF8F6); overflow:hidden; position:relative;">
          <img src="<?= htmlspecialchars($opImg) ?>" alt="<?= htmlspecialchars($op['name']) ?>" style="width:100%;height:100%;object-fit:cover;transition:transform 0.4s;" onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='';">
          <?php if($opDisc>0): ?>
          <span style="position:absolute;top:10px;left:10px;background:#D4AF37;color:#1a1a1a;font-size:10px;font-weight:700;padding:4px 10px;border-radius:8px;">-<?= $opDisc ?>%</span>
          <?php endif; ?>
        </div>
        <div style="padding:14px 16px;">
          <div style="font-size:11px;color:#888;font-family:'Poppins',sans-serif;margin-bottom:3px;"><?= $occ['label'] ?></div>
          <div style="font-size:14px;font-weight:700;color:#1A1A1A;font-family:'Playfair Display',serif;margin-bottom:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($op['name']) ?></div>
          <div style="font-size:15px;font-weight:800;color:<?= $occ['color'] ?>;font-family:'Poppins',sans-serif;">&#8377;<?= number_format($opPrice) ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center; margin-top:28px;">
      <a href="pages/shop.php" style="display:inline-flex;align-items:center;gap:8px;padding:12px 30px;background:linear-gradient(135deg,<?= $occ['color'] ?>,#5a1529);color:#fff;border-radius:30px;font-size:13px;font-weight:700;text-decoration:none;font-family:'Poppins',sans-serif;box-shadow:0 6px 18px rgba(122,30,58,0.2);transition:all 0.3s;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='';">Explore Full <?= $occ['label'] ?> <i class="fas fa-arrow-right"></i></a>
    </div>
  </div>
  <?php endforeach; ?>
</div>
</section>

<style>
@keyframes occFade { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var occTabs = document.querySelectorAll('.occ-tab');
  occTabs.forEach(function(tab) {
    tab.addEventListener('click', function() {
      var occ = this.dataset.occ;
      occTabs.forEach(function(t) {
        t.style.background = '#fff'; t.style.color = '#666';
        t.style.borderColor = '#ddd'; t.style.boxShadow = 'none';
      });
      this.style.background = 'linear-gradient(135deg,#7A1E3A,#5a1529)';
      this.style.color = '#fff';
      this.style.borderColor = '#7A1E3A';
      this.style.boxShadow = '0 4px 14px rgba(122,30,58,0.2)';
      document.querySelectorAll('.occ-panel').forEach(function(p){ p.style.display='none'; });
      var panel = document.getElementById('occ-'+occ);
      if(panel){ panel.style.display='block'; panel.style.animation='none'; void panel.offsetWidth; panel.style.animation='occFade 0.4s ease'; }
    });
  });
});
</script>

<!-- ========== NEWSLETTER ========== -->
<section class="newsletter-section">
  <div class="container">
    <div class="newsletter-box">
      <div class="icon-badge"><i class="far fa-paper-plane"></i></div>
      <h2>Stay Updated</h2>
      <p>Subscribe for new arrivals and exclusive luxury offers</p>
      <form class="newsletter-form" onsubmit="return subscribeNews(this)">
        <input type="email" placeholder="Enter your email" required>
        <button type="submit">Subscribe</button>
      </form>
    </div>
  </div>
</section>

<!-- ========== FOOTER ========== -->
<footer class="lux-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-col">
        <h3>Vaishnavi Jewellers</h3>
        <p>Premium jewellery since 1985. Crafting luxury for generations.</p>
        <div class="social-links">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Shop</h4>
        <ul>
          <li><a href="pages/shop.php">All Jewellery</a></li>
          <li><a href="pages/shop.php?category=Gold">Gold</a></li>
          <li><a href="pages/shop.php?category=Diamond">Diamond</a></li>
          <li><a href="pages/shop.php?category=Wedding">Wedding</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Account</h4>
        <ul>
          <li><a href="pages/profile.php">My Profile</a></li>
          <li><a href="pages/orders.php">Orders</a></li>
          <li><a href="pages/wishlist.php">Wishlist</a></li>
          <li><a href="pages/cart.php">Cart</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Contact</h4>
        <ul>
          <li><i class="fas fa-phone"></i> 1800-XXX-XXXX</li>
          <li><i class="fas fa-envelope"></i> care@vaishnavi.com</li>
          <li><i class="fas fa-map-marker-alt"></i> Mumbai, India</li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> Vaishnavi Jewellers. All rights reserved.</p>
      <div class="footer-links">
        <a href="#">Privacy</a>
        <a href="#">Terms</a>
        <a href="#">FAQ</a>
      </div>
    </div>
  </div>
</footer>

<!-- ========== JAVASCRIPT ========== -->
<script>
// Hero Slider
let heroIndex = 0;
const heroSlides = document.querySelectorAll('.hero-slide');

function changeSlide(dir) {
  heroSlides[heroIndex].classList.remove('active');
  heroIndex = (heroIndex + dir + heroSlides.length) % heroSlides.length;
  heroSlides[heroIndex].classList.add('active');
  updateDots();
}

function goToSlide(idx) {
  heroSlides[heroIndex].classList.remove('active');
  heroIndex = idx;
  heroSlides[heroIndex].classList.add('active');
  updateDots();
}

function updateDots() {
  document.querySelectorAll('.hero-dots span').forEach((dot, i) => {
    dot.classList.toggle('active', i === heroIndex);
  });
}

setInterval(() => changeSlide(1), 5000);

// Search
function doSearch() {
  var q = document.getElementById('headerSearch').value.trim();
  if(q) window.location.href = 'pages/shop.php?search=' + encodeURIComponent(q);
}

document.getElementById('headerSearch')?.addEventListener('keypress', function(e) {
  if(e.key === 'Enter') doSearch();
});

// Header scroll
window.addEventListener('scroll', () => {
  const header = document.getElementById('mainHeader');
  if(header) header.classList.toggle('scrolled', window.scrollY > 50);
});

// Cart add - use event delegation to handle dynamically created carousel buttons
document.addEventListener('click', function(e) {
  const btn = e.target.closest('[data-cart]');
  if (btn) {
    e.preventDefault();
    e.stopPropagation();
    const pid = btn.getAttribute('data-cart');
    var formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', pid);
    formData.append('qty', '1');
    fetch('api/cart-toggle.php', {
      method: 'POST',
      body: formData
    }).then(function(res) { return res.json(); })
    .then(function(data) {
      console.log('Cart response:', data);
      if (data.ok) {
        window.location.href = 'cart.php';
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(function(err) {
      console.error(err);
      alert('Error adding to cart');
    });
  }
});

// Navigate card click to product details page, unless a button inside was clicked
document.addEventListener('click', function(e) {
  const card = e.target.closest('.luxury-trend-card');
  if (card) {
    const actionBtn = e.target.closest('.luxury-trend-card__action');
    if (actionBtn) {
      // Allow button events to handle it separately
      return;
    }
    // Navigate to product detail page
    const pid = card.getAttribute('data-product-id');
    if (pid) {
      window.location.href = 'pages/product.php?id=' + encodeURIComponent(pid);
    }
  }
});



// Newsletter
function subscribeNews(form) {
  const emailInput = form.querySelector('input[type="email"]');
  const btn = form.querySelector('button[type="submit"]');
  if (!emailInput || !emailInput.value.trim()) return false;
  
  const email = emailInput.value.trim();
  const origText = btn.innerHTML;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
  btn.disabled = true;
  
  let apiUrl = 'api/subscribe.php';
  const path = window.location.pathname;
  if (path.includes('/visnavi%20jwallers/visnavi%20jwallers/') || path.includes('/visnavi jwallers/visnavi jwallers/')) {
    apiUrl = '/visnavi%20jwallers/visnavi%20jwallers/api/subscribe.php';
  } else if (path.includes('/visnavi%20jwallers/') || path.includes('/visnavi jwallers/')) {
    apiUrl = '/visnavi%20jwallers/api/subscribe.php';
  }
  
  fetch(apiUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email: email })
  })
  .then(res => res.json())
  .then(data => {
    btn.innerHTML = origText;
    btn.disabled = false;
    if (data.success) {
      showLuxuryToast('Success', 'You have successfully subscribed. We\'ve sent a welcome member preview email.', 'success');
      emailInput.value = '';
    } else {
      showLuxuryToast('Error', data.message || 'Subscription failed.', 'error');
    }
  })
  .catch(err => {
    btn.innerHTML = origText;
    btn.disabled = false;
    console.error(err);
    showLuxuryToast('Error', 'Connection error. Please try again.', 'error');
  });
  
  return false;
}

function showLuxuryToast(title, message, type = 'success') {
  const container = document.getElementById('luxury-toast-container') || (() => {
    const div = document.createElement('div');
    div.id = 'luxury-toast-container';
    div.style.cssText = 'position: fixed; top: 30px; right: 30px; z-index: 10000; display: flex; flex-direction: column; gap: 12px; pointer-events: none; max-width: 350px;';
    document.body.appendChild(div);
    return div;
  })();

  const toast = document.createElement('div');
  toast.style.cssText = `
    background: #210408;
    border-left: 4px solid #D4AF37;
    border-radius: 8px;
    padding: 16px 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    color: #FFFFFF;
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    opacity: 0;
    transform: translateX(100px);
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    pointer-events: auto;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    border: 1px solid rgba(212, 175, 55, 0.15);
  `;

  const icon = type === 'success' ? '✦' : '✕';
  const iconColor = type === 'success' ? '#D4AF37' : '#FF4D4D';

  toast.innerHTML = `
    <span style="font-size: 18px; color: ${iconColor}; line-height: 1;">${icon}</span>
    <div>
      <strong style="display: block; font-family: 'Playfair Display', Georgia, serif; color: #D4AF37; margin-bottom: 3px; font-size: 14px;">${title}</strong>
      <span style="color: #E8E0D6; line-height: 1.4;">${message}</span>
    </div>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '1';
    toast.style.transform = 'translateX(0)';
  }, 50);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-20px)';
    setTimeout(() => {
      toast.remove();
    }, 500);
  }, 4500);
}

// Mobile menu
document.getElementById('mobileMenuToggle')?.addEventListener('click', function() {
  document.getElementById('mainNav')?.classList.toggle('mobile-active');
});

// Mobile nav toggle
document.getElementById('navMobileToggle')?.addEventListener('click', function() {
  document.getElementById('mainNav')?.classList.toggle('active');
  this.classList.toggle('active');
});
</script>
<script src="assets/js/cart-ui.js?v=<?= time(); ?>"></script>
</body>
</html>
