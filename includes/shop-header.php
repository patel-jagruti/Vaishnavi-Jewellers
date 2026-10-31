<?php
declare(strict_types=1);

// Include required files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

ensure_session();

$cfg = require __DIR__ . '/config.php';
$appName = (string)($cfg['app_name'] ?? 'Vaishnavi Jewellers');

// Get current user
$user = $_SESSION['user'] ?? null;

// Get cart count
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    foreach (($_SESSION['cart'] ?? []) as $qty) {
        $cartCount += max(0, (int)$qty);
    }
}

// Get wishlist count
$wishCount = isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0;

// Current page
$activePage = basename($_SERVER['PHP_SELF'] ?? '');

// Categories for mega menu
$categories = [
    'Gold' => ['Gold Rings', 'Gold Necklaces', 'Gold Earrings', 'Gold Bangles', 'Gold Pendants', 'Gold Coins'],
    'Diamond' => ['Diamond Rings', 'Diamond Necklaces', 'Diamond Earrings', 'Diamond Pendants', 'Solitaire'],
    'Earrings' => ['Studs', 'Drops', 'Jhumkas', 'Chandbalis'],
    'Rings' => ['Engagement', 'Wedding', 'Fashion', 'Daily Wear'],
    'Necklaces' => ['Chains', 'Sets', 'Half Neck', 'Mangalsutra'],
    'Bangles' => ['Gold Bangles', 'Diamond Bangles', 'Kada'],
    'Bracelets' => ['Gold', 'Diamond', 'Pearl'],

    'Wedding' => ['Bangles', 'Necklace Sets', 'Maang Tikka'],
];

// Price ranges
$priceRanges = [
    ['label' => 'Under ₹10,000', 'min' => 0, 'max' => 10000],
    ['label' => '₹10,000 - ₹25,000', 'min' => 10000, 'max' => 25000],
    ['label' => '₹25,000 - ₹50,000', 'min' => 25000, 'max' => 50000],
    ['label' => '₹50,000 - ₹1,00,000', 'min' => 50000, 'max' => 100000],
    ['label' => 'Above ₹1,00,000', 'min' => 100000, 'max' => null],
];

// Metal options
$metals = ['Gold', 'Diamond', 'Platinum', 'Gemstone', 'Pearl'];

// Purity options
$purities = ['24K', '22K', '18K', '14K'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($appName) ?> | Premium Jewellery</title>
  <meta name="description" content="Discover exquisite jewellery at Vaishnavi Jewellers. Fine gold, diamond, and gemstone jewellery collections.">
  <meta name="theme-color" content="#7A1E3A">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Swiper -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/premium-header.css">
  <link rel="stylesheet" href="assets/css/premium-shop.css">

  <style>
    /* Override for shop header */
    .lux-header { position: relative; margin-top: 0; }
    .shop-page { margin-top: 0; padding-top: 160px; }
  </style>
</head>
<body>

<!-- TOP BAR -->
<div class="lux-topbar">
  <div class="container">
    <div class="topbar-flex">
      <div class="topbar-links">
        <a href="tel:+918888888888"><i class="fas fa-phone-alt"></i> +91 888 888 8888</a>
        <a href="mailto:care@vaishnavijewellers.com"><i class="fas fa-envelope"></i> care@vaishnavijewellers.com</a>
      </div>
      <div class="topbar-right">
        <a href="pages/store.php" class="gold-rate-link"><i class="fas fa-map-marker-alt"></i> Store Locator</a>
        <a href="pages/appointment.php"><i class="far fa-calendar-check"></i> Book Appointment</a>
      </div>
    </div>
  </div>
</div>

<!-- HEADER -->
<header class="lux-header scrolled">
  <div class="header-main">
    <div class="container">
      <div style="display:flex;align-items:center;justify-content:space-between;">

        <!-- Logo -->
        <a href="index.php" class="lux-logo">
          <img src="assets/images/logo.svg" alt="<?= h($appName) ?>">
          <div class="lux-logo-text">
            <b><?= h($appName) ?></b>
            <span>EST. 1990</span>
          </div>
        </a>

        <!-- Search -->
        <div class="lux-search">
          <div class="search-input-wrapper">
            <input type="text" placeholder="Search for gold jewellery, diamond rings, necklaces...">
            <div class="search-buttons">
              <button class="search-btn" data-image title="Image Search"><i class="fas fa-camera"></i></button>
              <button class="search-btn" data-voice title="Voice Search"><i class="fas fa-microphone"></i></button>
              <button class="search-btn search-submit" title="Search"><i class="fas fa-search"></i></button>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="header-actions">
          <a href="pages/wishlist.php" class="header-action" title="Wishlist">
            <i class="far fa-heart"></i>
            <?php if ($wishCount > 0): ?>
              <span class="cart-count"><?= (int)$wishCount ?></span>
            <?php endif; ?>
          </a>
          <a href="pages/store.php" class="header-action" title="Store Locator">
            <i class="fas fa-map-marker-alt"></i>
          </a>
          <?php if ($user): ?>
            <a href="pages/profile.php" class="header-action" title="My Account">
              <i class="far fa-user"></i>
            </a>
          <?php else: ?>
            <a href="pages/login.php" class="header-action" title="Login">
              <i class="far fa-user"></i>
            </a>
          <?php endif; ?>
          <a href="pages/orders.php" class="header-action" title="Orders">
            <i class="fas fa-box"></i>
          </a>
          <a href="pages/cart.php" class="header-action" title="Cart">
            <i class="fas fa-shopping-bag"></i>
            <?php if ($cartCount > 0): ?>
              <span class="cart-count"><?= (int)$cartCount ?></span>
            <?php endif; ?>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="lux-navbar">
    <div class="container">
      <ul class="lux-nav">
        <li class="nav-item">
          <a href="pages/shop.php" class="nav-link">All Jewellery</a>
          <div class="mega-menu">
            <div class="mega-menu-inner">
              <div class="mega-col">
                <h4>By Category</h4>
                <ul>
                  <li><a href="pages/shop.php?category=Gold">Gold Jewellery</a></li>

                  <li><a href="pages/shop.php?category=Platinum">Platinum</a></li>
                  <li><a href="pages/shop.php?category=Gemstone">Gemstone</a></li>
                  <li><a href="pages/shop.php?category=Pearl">Pearl</a></li>
                </ul>
              </div>
              <div class="mega-col">
                <h4>By Price</h4>
                <ul>
                  <li><a href="pages/shop.php?price=0-10000">Under ₹10K</a></li>
                  <li><a href="pages/shop.php?price=10000-25000">₹10K - ₹25K</a></li>
                  <li><a href="pages/shop.php?price=25000-50000">₹25K - ₹50K</a></li>
                  <li><a href="pages/shop.php?price=50000-100000">₹50K - ₹1L</a></li>
                  <li><a href="pages/shop.php?price=100000+">Above ₹1L</a></li>
                </ul>
              </div>
              <div class="mega-col">
                <h4>By Occasion</h4>
                <ul>
                  <li><a href="pages/shop.php?occasion=Wedding">Wedding</a></li>
                  <li><a href="pages/shop.php?occasion=Festival">Festival</a></li>
                  <li><a href="pages/shop.php?occasion=Daily">Daily Wear</a></li>
                  <li><a href="pages/shop.php?occasion=Office">Office Wear</a></li>
                  <li><a href="pages/shop.php?occasion=Gifting">Gifting</a></li>
                </ul>
              </div>
              <div class="mega-col">
                <h4>By Gender</h4>
                <ul>
                  <li><a href="pages/shop.php?gender=Women">Women</a></li>
                  <li><a href="pages/shop.php?gender=Men">Men</a></li>
                  <li><a href="pages/shop.php?gender=Kids">Kids</a></li>
                  <li><a href="pages/shop.php?gender=Couples">Couples</a></li>
                </ul>
              </div>
              <div class="mega-col mega-banner">
                <img src="https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=300" class="mega-banner-img" alt="New Arrivals">
                <h5>New Arrivals</h5>
                <p>Explore the latest designs</p>
                <a href="pages/shop.php?new=1" class="btn-shop">Shop Now</a>
              </div>
            </div>
          </div>
        </li>
        <li class="nav-item">
          <a href="pages/shop.php?category=Gold" class="nav-link">Gold</a>
        </li>

        <li class="nav-item">
          <a href="pages/shop.php?category=Diamond" class="nav-link">Diamond</a>
        </li>
        <li class="nav-item">
          <a href="pages/shop.php?category=Necklaces" class="nav-link">Necklaces</a>
        </li>
        <li class="nav-item">
          <a href="pages/shop.php?category=Bangles" class="nav-link">Bangles</a>
        </li>
        <li class="nav-item">
          <a href="pages/shop.php?category=Wedding" class="nav-link">Wedding</a>
        </li>
        <li class="nav-item">
          <a href="pages/shop.php?category=Coins" class="nav-link">Coins</a>
        </li>
        <li class="nav-item">
          <a href="pages/gold-rate.php" class="nav-link">Gold Rate</a>
        </li>
        <li class="nav-item">
          <a href="pages/shop.php?sale=1" class="nav-link" style="color:var(--hp-gold);">Sale</a>
        </li>
      </ul>
    </div>
  </nav>
</header>

<main class="shop-page">
  <div class="container">
    <div class="shop-layout">
      <!-- Filter Sidebar -->
      <aside class="filter-sidebar">
        <form id="filterForm" method="GET" action="pages/shop.php">
          <!-- Categories -->
          <div class="filter-section">
            <h4 class="filter-title">Categories</h4>
            <ul class="category-list">
              <li class="category-item">
                <a href="pages/shop.php" class="category-link <?= (!isset($_GET['category'])) ? 'active' : '' ?>">
                  All Jewellery
                  <span class="category-count">245</span>
                </a>
              </li>
              <li class="category-item">
                <a href="pages/shop.php?category=Gold" class="category-link <?= ($_GET['category'] ?? '') === 'Gold' ? 'active' : '' ?>">
                  Gold Jewellery
                  <span class="category-count">89</span>
                </a>
              </li>

              <li class="category-item">
                <a href="pages/shop.php?category=Diamond" class="category-link <?= ($_GET['category'] ?? '') === 'Diamond' ? 'active' : '' ?>">
                  Diamond Jewellery
                  <span class="category-count">65</span>
                </a>
              </li>
              <li class="category-item">
                <a href="pages/shop.php?category=Necklaces" class="category-link <?= ($_GET['category'] ?? '') === 'Necklaces' ? 'active' : '' ?>">
                  Necklaces
                  <span class="category-count">52</span>
                </a>
              </li>
              <li class="category-item">
                <a href="pages/shop.php?category=Bangles" class="category-link <?= ($_GET['category'] ?? '') === 'Bangles' ? 'active' : '' ?>">
                  Bangles
                  <span class="category-count">29</span>
                </a>
              </li>
              <li class="category-item">
                <a href="pages/shop.php?category=Wedding" class="category-link <?= ($_GET['category'] ?? '') === 'Wedding' ? 'active' : '' ?>">
                  Wedding
                  <span class="category-count">34</span>
                </a>
              </li>
            </ul>
          </div>

          <!-- Price Range -->
          <div class="filter-section">
            <h4 class="filter-title">Price Range</h4>
            <div class="price-range-container">
              <div class="price-inputs">
                <input type="number" class="price-input" name="min_price" placeholder="Min" value="<?= (int)($_GET['min_price'] ?? 0) ?>">
                <span>to</span>
                <input type="number" class="price-input" name="max_price" placeholder="Max" value="<?= (int)($_GET['max_price'] ?? 1000000) ?>">
              </div>
              <input type="range" class="form-range" min="0" max="500000" step="5000" value="<?= (int)($_GET['max_price'] ?? 100000) ?>">
              <p class="price-display">₹0 - ₹5,00,000+</p>
            </div>
          </div>

          <!-- Gender -->
          <div class="filter-section">
            <h4 class="filter-title">Gender</h4>
            <div class="checkbox-list">
              <label class="checkbox-item">
                <input type="radio" name="gender" value="women" <?= ($_GET['gender'] ?? '') === 'women' ? 'checked' : '' ?>>
                <span class="checkbox-custom"><i class="fas fa-check"></i></span>
                <span class="checkbox-label">Women</span>
              </label>
              <label class="checkbox-item">
                <input type="radio" name="gender" value="men" <?= ($_GET['gender'] ?? '') === 'men' ? 'checked' : '' ?>>
                <span class="checkbox-custom"><i class="fas fa-check"></i></span>
                <span class="checkbox-label">Men</span>
              </label>
              <label class="checkbox-item">
                <input type="radio" name="gender" value="kids" <?= ($_GET['gender'] ?? '') === 'kids' ? 'checked' : '' ?>>
                <span class="checkbox-custom"><i class="fas fa-check"></i></span>
                <span class="checkbox-label">Kids</span>
              </label>
            </div>
          </div>

          <!-- Metal -->
          <div class="filter-section">
            <h4 class="filter-title">Metal</h4>
            <div class="checkbox-list">
              <label class="checkbox-item">
                <input type="checkbox" name="metal" value="gold" checked>
                <span class="checkbox-custom"><i class="fas fa-check"></i></span>
                <span class="checkbox-label">Gold (22K)</span>
              </label>
              <label class="checkbox-item">
                <input type="checkbox" name="metal" value="18k">
                <span class="checkbox-custom"><i class="fas fa-check"></i></span>
                <span class="checkbox-label">Gold (18K)</span>
              </label>
              <label class="checkbox-item">
                <input type="checkbox" name="metal" value="diamond">
                <span class="checkbox-custom"><i class="fas fa-check"></i></span>
                <span class="checkbox-label">Diamond</span>
              </label>
              <label class="checkbox-item">
                <input type="checkbox" name="metal" value="platinum">
                <span class="checkbox-custom"><i class="fas fa-check"></i></span>
                <span class="checkbox-label">Platinum</span>
              </label>
            </div>
          </div>

          <!-- Occasion -->
          <div class="filter-section">
            <h4 class="filter-title">Occasion</h4>
            <div class="checkbox-list">
              <label class="checkbox-item">
                <input type="checkbox" name="occasion" value="daily">
                <span class="checkbox-custom"><i class="fas fa-check"></i></span>
                <span class="checkbox-label">Daily Wear</span>
              </label>
              <label class="checkbox-item">
                <input type="checkbox" name="occasion" value="party">
                <span class="checkbox-custom"><i class="fas fa-check"></i></span>
                <span class="checkbox-label">Party Wear</span>
              </label>
              <label class="checkbox-item">
                <input type="checkbox" name="occasion" value="wedding">
                <span class="checkbox-custom"><i class="fas fa-check"></i></span>
                <span class="checkbox-label">Wedding</span>
              </label>
              <label class="checkbox-item">
                <input type="checkbox" name="occasion" value="office">
                <span class="checkbox-custom"><i class="fas fa-check"></i></span>
                <span class="checkbox-label">Office Wear</span>
              </label>
            </div>
          </div>

          <!-- Rating -->
          <div class="filter-section">
            <h4 class="filter-title">Rating</h4>
            <div class="rating-list">
              <label class="rating-item">
                <input type="radio" name="rating" value="4">
                <div class="rating-stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="far fa-star"></i>
                </div>
                <span class="rating-label">& Up</span>
              </label>
              <label class="rating-item">
                <input type="radio" name="rating" value="3">
                <div class="rating-stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="far fa-star"></i>
                  <i class="far fa-star"></i>
                </div>
                <span class="rating-label">& Up</span>
              </label>
            </div>
          </div>

          <!-- Actions -->
          <div class="filter-actions">
            <button type="submit" class="btn-apply">Apply Filters</button>
            <button type="button" class="btn-reset" onclick="window.location='pages/shop.php'">Reset</button>
          </div>
        </form>
      </aside>

      <!-- Shop Content -->