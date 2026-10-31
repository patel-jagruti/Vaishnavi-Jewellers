<?php
declare(strict_types=1);
if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/base_url.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/cart_db.php';


ensure_session();

$cfg = require __DIR__ . '/config.php';
$appName = (string)($cfg['app_name'] ?? 'Jewellers');

$user = $_SESSION['user'] ?? null;

$cartCount = 0;
$wishCount = 0;

try {
  $pdo = db();
  ensure_cart_table($pdo);
  ensure_wishlist_table($pdo);
  $cartCount = cart_count($pdo);
  $wishCount = wishlist_count($pdo);
} catch (Throwable $e) {
  // Fallback to session counts if DB query fails or database is not available
  if (!empty($_SESSION['cart'])) {
    foreach (($_SESSION['cart'] ?? []) as $qty) {
      $cartCount += max(0, (int)$qty);
    }
  }
  $wishCount = isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0;
}

$active = basename($_SERVER['PHP_SELF'] ?? '');
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <base href="/visnavi%20jwallers/">
  <title><?= h($appName) ?></title>
  <meta name="theme-color" content="#6B1E2D" />

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/visnavi jwallers/assets/css/style.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/visnavi jwallers/assets/css/header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/visnavi jwallers/assets/css/luxury-header.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/visnavi jwallers/assets/css/ui-better.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/visnavi jwallers/assets/css/cart-drawer.css?v=<?= time() ?>" />
  <style>
  /* Override premium styles */
  body{background:#FFF8F0}
  .product-card{transition:all 0.4s ease;border-radius:16px;overflow:hidden}
  .product-card:hover{transform:translateY(-10px);box-shadow:0 20px 50px rgba(107,30,45,0.2)}
  .btn{background:linear-gradient(135deg,#6B1E2D,#8B2940);border-radius:30px;padding:14px 28px;font-weight:600}
  .section-head h2{font-family:Playfair Display,Georgia,serif;font-size:32px;color:#1A1A1A}
  .price{color:#6B1E2D;font-weight:700}
  .footer{background:#1A1A1A}

  /* Account Dropdown CSS */
  .lux-header, 
  .main-header, 
  .main-header .container,
  .header-main-wrapper, 
  .lux-actions, 
  .lux-action-btn.has-dropdown {
    overflow: visible !important;
  }
  .lux-action-btn.has-dropdown {
    position: relative;
    cursor: pointer;
  }
  .account-dropdown-menu {
    position: absolute;
    top: 100%;
    right: -20px;
    width: 230px;
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #eee;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    padding: 10px 0;
    margin-top: 15px;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  }
  /* Tooltip pointer triangle */
  .account-dropdown-menu::before {
    content: '';
    position: absolute;
    top: -8px;
    right: 50px;
    width: 15px;
    height: 15px;
    background: #ffffff;
    transform: rotate(45deg);
    border-left: 1px solid #eee;
    border-top: 1px solid #eee;
    z-index: 99;
  }
  .lux-action-btn.has-dropdown:hover .account-dropdown-menu,
  .lux-action-btn.has-dropdown:focus-within .account-dropdown-menu,
  .account-dropdown-menu.show-menu {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translateY(0) !important;
    display: block !important;
  }
  .account-dropdown-menu .dropdown-user-name {
    padding: 15px 24px 10px;
    font-size: 15px;
    font-weight: 700;
    color: #222;
    font-family: 'Poppins', sans-serif;
    text-align: left;
  }
  .account-dropdown-menu .dropdown-divider {
    height: 1px;
    background: #eee;
    margin: 0 24px 12px;
  }
  .account-dropdown-menu a {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 11px 24px;
    color: #333;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    font-family: 'Poppins', sans-serif;
    text-align: left;
  }
  .account-dropdown-menu a i {
    font-size: 18px;
    color: #a77a42;
    width: 20px;
    text-align: center;
  }
  .account-dropdown-menu a:hover {
    background: #FAF8F5;
    color: #a77a42;
  }
  .account-dropdown-menu a.logout-link:hover {
    background: #FFF5F5;
    color: #C62828;
  }
  .account-dropdown-menu a.logout-link:hover i {
    color: #C62828;
  }
  </style>
  <?php if (basename($_SERVER['PHP_SELF']) === 'index.php'): ?>
    <link rel="stylesheet" href="/visnavi jwallers/assets/css/premium-home.css?v=<?= time() ?>" />
  <?php endif; ?>
</head>
<body>

<!-- ULTRA PREMIUM LUXURY HEADER -->
<header class="lux-header" id="mainHeader">
  <!-- Top Bar: Store timings -->
  <div class="top-bar">
    <div class="container">
      <div class="top-bar-flex">
        <span><i class="fas fa-clock"></i> Open Today: 10:00 AM - 8:00 PM</span>
        <span><i class="fas fa-map-marker-alt"></i> Mumbai | Delhi | Surat | Hyderabad</span>
      </div>
    </div>
  </div>

  <!-- Main Header -->
  <div class="main-header">
    <div class="container">
      <div class="header-main-wrapper">

        <!-- Logo Section -->
        <a class="lux-brand" href="<?= route('index.php') ?>">
          <div class="brand-logo" style="display: flex; align-items: center; justify-content: flex-start; height: 75px; width: 220px;">
            <img src="<?= asset('images/logo.png') ?>" alt="Vaishnavi Jewellers" style="height: 70px; max-height: 70px; max-width: 100%; object-fit: contain; filter: drop-shadow(0 2px 10px rgba(0,0,0,0.06)); transition: all 0.3s ease;">
          </div>
        </a>

        <!-- Search Bar -->
        <div class="lux-search-wrapper">
          <div class="lux-search-box">
            <div class="search-icon"><i class="fas fa-search"></i></div>
            <input type="text" class="lux-search-input" placeholder="Search for jewellery, diamonds, rings..." id="headerSearch">
            <div class="search-actions">
              <button class="search-action-btn" title="Camera Search" id="btnCameraSearch" type="button"><i class="fas fa-camera"></i></button>
              <input type="file" id="cameraSearchInput" accept="image/*" style="display:none;">
              <button class="search-action-btn" title="Voice Search" type="button"><i class="fas fa-microphone"></i></button>
            </div>
          </div>
          <!-- Search Suggestions Dropdown -->
          <div class="search-suggestions" id="searchSuggestions">
            <div class="suggestion-header">Popular Searches</div>
            <a href="/visnavi jwallers/pages/shop.php?category=Gold" class="suggestion-item"><i class="fas fa-ring"></i> Gold Rings</a>

            <a href="/visnavi jwallers/pages/shop.php?category=Necklaces" class="suggestion-item"><i class="fas fa-circle"></i> Necklace Sets</a>
            <a href="/visnavi jwallers/pages/shop.php?category=Wedding" class="suggestion-item"><i class="fas fa-certificate"></i> Wedding Collection</a>
          </div>
        </div>

        <!-- Header Actions -->
        <div class="lux-actions">

          <a href="/visnavi jwallers/pages/store.php" class="lux-action-btn" title="Store Locator">
            <div class="action-icon"><i class="fas fa-store"></i></div>
            <span class="action-label">Store</span>
          </a>

          <a href="/visnavi jwallers/pages/wishlist.php" class="lux-action-btn" title="Wishlist">

            <div class="action-icon">
              <i class="far fa-heart"></i>
              <?php if ($wishCount > 0): ?>
                <span class="action-badge" data-count-role="wishlist"><?= (int)$wishCount ?></span>
              <?php endif; ?>
            </div>
            <span class="action-label">Wishlist</span>
          </a>

          <div class="lux-action-btn has-dropdown" title="My Account" onclick="toggleAccountMenu(event)">
            <div class="action-icon"><i class="far fa-user"></i></div>
            <span class="action-label">Account</span>
            <div class="account-dropdown-menu">
              <?php if ($user): ?>
                <div class="dropdown-user-name"><?= h($user['full_name']) ?></div>
                <div class="dropdown-divider"></div>
                
                <?php if (($user['role'] ?? '') === 'admin'): ?>
                  <a href="/visnavi jwallers/admin/"><i class="far fa-user"></i> Dashboard</a>
                <?php else: ?>
                  <a href="/visnavi jwallers/pages/profile.php"><i class="far fa-user"></i> Dashboard</a>
                <?php endif; ?>
                
                <a href="/visnavi jwallers/pages/orders.php"><i class="fas fa-box"></i> Orders</a>
                <a href="/visnavi jwallers/pages/profile.php"><i class="fas fa-cog"></i> Profile</a>
                <a href="/visnavi jwallers/pages/logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
              <?php else: ?>
                <div class="dropdown-user-name">Welcome</div>
                <div class="dropdown-divider"></div>
                <a href="/visnavi jwallers/pages/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="/visnavi jwallers/pages/register.php"><i class="far fa-id-card"></i> Create Account</a>
              <?php endif; ?>
            </div>
          </div>

          <a href="/visnavi jwallers/pages/orders.php" class="lux-action-btn" title="Orders">
            <div class="action-icon">
              <i class="fas fa-box"></i>
            </div>
            <span class="action-label">Orders</span>
          </a>

          <a href="/visnavi jwallers/pages/cart.php" class="lux-action-btn" title="Cart" id="cartDrawerBtn">
            <div class="action-icon">
              <i class="fas fa-shopping-bag"></i>
              <?php if ($cartCount > 0): ?>
                <span class="action-badge" data-count-role="cart"><?= (int)$cartCount ?></span>
              <?php endif; ?>
            </div>
            <span class="action-label">Cart</span>
          </a>
        </div>

        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
          <span></span>
          <span></span>
          <span></span>
        </button>

      </div>
    </div>
  </div>

  <!-- Category Navigation -->
  <nav class="category-nav" id="categoryNav">
    <div class="container">
      <ul class="category-list">
        <li class="category-item has-mega">
          <a href="/visnavi jwallers/pages/shop.php">
            <svg class="category-icon" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            <span>All Jewellery</span>
            <i class="fas fa-chevron-down"></i>
          </a>
          <div class="mega-menu">
            <div class="mega-menu-grid">
              <div class="mega-col">
                <h4>Gold Jewelry</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Gold">Gold Rings</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gold">Gold Earrings</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gold">Gold Necklaces</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gold">Gold Bangles</a>
              </div>

              <div class="mega-col">
                <h4>Collections</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Wedding">Wedding Collection</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Daily wear">Daily Wear</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gemstone">Gemstone</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gifting">Gifting</a>
              </div>
              <div class="mega-col mega-feature">
                <img src="https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=300&q=80" alt="New Collection">
                <h4>New Arrivals</h4>
                <a href="/visnavi jwallers/pages/shop.php">Shop Now</a>
              </div>
            </div>
          </div>
        </li>
        <li class="category-item has-mega">
          <a href="/visnavi jwallers/pages/shop.php?category=Gold">
            <svg class="category-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4"/></svg>
            <span>Gold</span>
            <i class="fas fa-chevron-down"></i>
          </a>
          <!-- Mega Menu -->
          <div class="mega-menu">
            <div class="mega-menu-grid">
              <div class="mega-col">
                <h4>By Karat</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Gold">22K Gold</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gold">18K Gold</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gold">24K Gold</a>
              </div>
              <div class="mega-col">
                <h4>By Type</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Gold&search=chain">Gold Chains</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gold&search=ring">Gold Rings</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gold&search=bangle">Gold Bangles</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gold&search=pendant">Gold Pendants</a>
              </div>
              <div class="mega-col">
                <h4>By Collection</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Daily+wear">Daily Wear</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gold&search=festive">Festive Collection</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Wedding">Wedding</a>
              </div>
              <div class="mega-col mega-feature">
                <img src="https://images.unsplash.com/photo-1602173574767-37ac01994b2a?w=300&q=80" alt="Gold Collection">
                <h4>New Gold Collection</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Gold">Shop Now</a>
              </div>
            </div>
          </div>
        </li>

        <li class="category-item has-mega">
          <a href="/visnavi jwallers/pages/shop.php?category=Silver">
            <svg class="category-icon" viewBox="0 0 24 24" width="24" height="24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" fill="currentColor"/></svg>
            <span>Silver</span>
            <i class="fas fa-chevron-down"></i>
          </a>
          <!-- Mega Menu -->
          <div class="mega-menu">
            <div class="mega-menu-grid">
              <div class="mega-col">
                <h4>By Type</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Silver&search=payal">Silver Anklets (Payal)</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Silver&search=bracelet">Silver Bracelets</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Silver&search=chain">Silver Chains</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Silver&search=ring">Silver Rings</a>
              </div>
              <div class="mega-col">
                <h4>Collections</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Silver&search=kids">Kids Collection</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Silver&search=dailywear">Daily Wear</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Silver&search=challa">Traditional Challa</a>
              </div>
              <div class="mega-col mega-feature">
                <img src="/visnavi jwallers/assets/images/silver/WhatsApp%20Image%202026-06-29%20at%209.11.20%20PM%20(3).jpeg" alt="Silver Collection">
                <h4>New Silver Arrivals</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Silver">Shop Now</a>
              </div>
            </div>
          </div>
        </li>

        <li class="category-item has-mega">
          <a href="/visnavi jwallers/pages/shop.php?category=Diamond">
            <svg class="category-icon" viewBox="0 0 24 24"><path d="M6 3h12l4 6-10 13L2 9z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M11 3 8 9l4 13 4-13-3-6" fill="none" stroke="currentColor" stroke-width="2"/><path d="M2 9h20" fill="none" stroke="currentColor" stroke-width="2"/></svg>
            <span>Diamond</span>
            <i class="fas fa-chevron-down"></i>
          </a>
          <!-- Mega Menu -->
          <div class="mega-menu">
            <div class="mega-menu-grid">
              <div class="mega-col">
                <h4>By Category</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Diamond&search=ring">Diamond Rings</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Diamond&search=earring">Diamond Earrings</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Diamond&search=necklace">Diamond Necklaces</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Diamond&search=pendant">Diamond Pendants</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Diamond&search=solitaire">Solitaires</a>
              </div>
              <div class="mega-col">
                <h4>By Occasion</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Diamond&search=engagement">Engagement</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Diamond&search=daily">Daily Wear</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Diamond&search=party">Party Wear</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Diamond&search=anniversary">Anniversary</a>
              </div>
              <div class="mega-col">
                <h4>By Gender</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Diamond&gender=women">Women</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Diamond&gender=men">Men</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Diamond&gender=kids">Kids</a>
              </div>
              <div class="mega-col mega-feature">
                <img src="https://images.unsplash.com/photo-1601121141461-9d6647bca1ed?w=300&q=80" alt="Diamond Collection">
                <h4>New Diamond Collection</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Diamond">Shop Now</a>
              </div>
            </div>
          </div>
        </li>
        <li class="category-item has-mega">
          <a href="/visnavi jwallers/pages/shop.php?category=Daily wear">
            <svg class="category-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 5v14"/></svg>
            <span>Daily Wear</span>
            <i class="fas fa-chevron-down"></i>
          </a>
          <div class="mega-menu">
            <div class="mega-menu-grid">
              <div class="mega-col">
                <h4>By Type</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Daily wear">Chains</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Daily wear">Pendants</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Daily wear">Bangles</a>
              </div>
              <div class="mega-col">
                <h4>By Metal</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Daily wear">22K Gold</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Daily wear">18K Gold</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Daily wear">Gold Plated</a>
              </div>
              <div class="mega-col">
                <h4>By Collection</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Daily wear">Lite Collection</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Daily wear">Everyday Elegance</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Daily wear">Office Wear</a>
              </div>
            </div>
          </div>
        </li>
        <li class="category-item has-mega">
          <a href="/visnavi jwallers/pages/shop.php?category=Gemstone">
            <svg class="category-icon" viewBox="0 0 24 24"><path d="M12 2L2 9l10 13 10-13-10-7zM2 9l10 13 10-13" fill="none" stroke="currentColor" stroke-width="2"/></svg>
            <span>Gemstone</span>
            <i class="fas fa-chevron-down"></i>
          </a>
          <div class="mega-menu">
            <div class="mega-menu-grid">
              <div class="mega-col">
                <h4>Precious</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Gemstone">Emerald</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gemstone">Ruby</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gemstone">Sapphire</a>
              </div>
              <div class="mega-col">
                <h4>Semi-Precious</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Gemstone">Pearl</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gemstone">Opal</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gemstone">Topaz</a>
              </div>
              <div class="mega-col">
                <h4>By Jewelry</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Gemstone">Rings</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gemstone">Pendants</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gemstone">Earrings</a>
              </div>
            </div>
          </div>
        </li>
        <li class="category-item has-mega">
          <a href="/visnavi jwallers/pages/shop.php?category=Wedding">
            <svg class="category-icon" viewBox="0 0 24 24"><path d="M12 7c3 0 6 2 6 5 0 4-6 9-6 9s-6-5-6-9c0-3 3-5 6-5z" fill="none" stroke="currentColor" stroke-width="2"/></svg>
            <span>Wedding</span>
            <i class="fas fa-chevron-down"></i>
          </a>
          <div class="mega-menu">
            <div class="mega-menu-grid">
              <div class="mega-col">
                <h4>Bridal Sets</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Wedding">Chura</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Wedding">Mangalsutra</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Wedding">Maang Tikka</a>
              </div>
              <div class="mega-col">
                <h4>Wedding Rings</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Wedding">His & Hers Set</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Wedding">Engagement</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Wedding">Anniversary</a>
              </div>
              <div class="mega-col">
                <h4>By Material</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Wedding">22K Gold</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Wedding">18K Gold</a>
              </div>
              <div class="mega-col mega-feature">
                <img src="https://images.unsplash.com/photo-1617038260897-41a1f14a8ca0?w=300&q=80" alt="Wedding Collection">
                <h4>Bridal Collection</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Wedding">Shop Now</a>
              </div>
            </div>
          </div>
        </li>
        <li class="category-item has-mega">
          <a href="/visnavi jwallers/pages/shop.php?category=Gifting">
            <svg class="category-icon" viewBox="0 0 24 24"><rect x="3" y="8" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M7 8V6a2 2 0 012-2h6a2 2 0 012 2v2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 8V4" stroke="currentColor" stroke-width="2"/></svg>
            <span>Gifting</span>
            <i class="fas fa-chevron-down"></i>
          </a>
          <div class="mega-menu">
            <div class="mega-menu-grid">
              <div class="mega-col">
                <h4>By Occasion</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Gifting">Anniversary</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gifting">Birthday</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gifting">Wedding</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gifting">Festivals</a>
              </div>
              <div class="mega-col">
                <h4>By Recipient</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Gifting">For Her</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gifting">For Him</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gifting">For Mom</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gifting">For Wife</a>
              </div>
              <div class="mega-col">
                <h4>By Price</h4>
                <a href="/visnavi jwallers/pages/shop.php?category=Gifting">Under ₹10,000</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gifting">₹10,000 - ₹25,000</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gifting">₹25,000 - ₹50,000</a>
                <a href="/visnavi jwallers/pages/shop.php?category=Gifting">Above ₹50,000</a>
              </div>
            </div>
          </div>
        </li>
        <li class="category-item has-mega">
          <a href="#">
            <span>More</span>
            <i class="fas fa-chevron-down"></i>
          </a>
          <div class="mega-menu">
            <div class="mega-menu-grid">
              <div class="mega-col">
                <h4>Quick Links</h4>
                <a href="/visnavi jwallers/pages/store.php">Store Locator</a>
                <a href="/visnavi jwallers/pages/about.php">About Us</a>
                <a href="/visnavi jwallers/pages/contact.php">Contact Us</a>
                <a href="/visnavi jwallers/pages/appointment.php">Book Appointment</a>
              </div>
              <div class="mega-col">
                <h4>Customer Service</h4>
                <a href="/visnavi jwallers/pages/faq.php">FAQ</a>
                <a href="/visnavi jwallers/pages/shipping.php">Shipping Info</a>
                <a href="/visnavi jwallers/pages/gold-rate.php">Gold Rate</a>
                <a href="/visnavi jwallers/pages/forgot-password.php">Forgot Password</a>
              </div>
              <div class="mega-col">
                <h4>Policies</h4>
                <a href="/visnavi jwallers/pages/shipping.php">Privacy Policy</a>
                <a href="/visnavi jwallers/pages/shipping.php">Terms of Use</a>
                <a href="/visnavi jwallers/pages/shipping.php">Return Policy</a>
                <a href="/visnavi jwallers/pages/faq.php">BIS Hallmark</a>
              </div>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </nav>
</header>

<!-- Mobile Menu Overlay -->
<div class="mobile-menu-overlay" id="mobileOverlay"></div>

<!-- Mobile Menu Panel -->
<div class="mobile-menu-panel" id="mobilePanel">
  <div class="mobile-panel-header">
    <img src="<?= asset('images/logo.png') ?>" alt="Vaishnavi Jewellers" class="mobile-logo" style="height: 52px; max-width: 170px; object-fit: contain; filter: drop-shadow(0 2px 8px rgba(0,0,0,0.05));">
    <button class="close-mobile" id="closeMobile"><i class="fas fa-times"></i></button>
  </div>
  <div class="mobile-panel-links">
    <a href="/visnavi jwallers/pages/shop.php"><i class="fas fa-gem"></i> All Jewellery</a>
    <a href="/visnavi jwallers/pages/shop.php?category=Gold"><i class="fas fa-circle"></i> Gold</a>

    <a href="/visnavi jwallers/pages/shop.php?category=Diamond"><i class="fas fa-gem"></i> Diamond</a>
    <a href="/visnavi jwallers/pages/shop.php?category=Wedding"><i class="fas fa-certificate"></i> Wedding</a>

    <a href="/visnavi jwallers/pages/store.php"><i class="fas fa-store"></i> Store Locator</a>

  </div>
  <div class="mobile-panel-actions">
    <?php if ($user): ?>
      <a href="/visnavi jwallers/pages/profile.php" class="mobile-btn"><i class="fas fa-user"></i> My Account</a>
      <a href="/visnavi jwallers/pages/orders.php" class="mobile-btn"><i class="fas fa-box"></i> Orders</a>
      <a href="/visnavi jwallers/pages/cart.php" class="mobile-btn" id="mobileCartDrawerBtn"><i class="fas fa-shopping-bag"></i> Cart (<?= (int)$cartCount ?>)</a>
    <?php else: ?>
      <a href="/visnavi jwallers/pages/login.php" class="mobile-btn"><i class="fas fa-sign-in-alt"></i> Sign In</a>
      <a href="/visnavi jwallers/pages/register.php" class="mobile-btn secondary"><i class="fas fa-user-plus"></i> Register</a>
    <?php endif; ?>
  </div>
</div>

<main class="main-content"></main>

<style>
@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.2); }
  100% { transform: scale(1); }
}
.voice-recording {
  animation: pulse 1s infinite;
  color: #c62828 !important;
}
</style>
<script>
function toggleAccountMenu(event) {
  event.stopPropagation();
  const menu = document.querySelector('.account-dropdown-menu');
  if (menu) {
    menu.classList.toggle('show-menu');
  }
}
document.addEventListener('click', function() {
  const menu = document.querySelector('.account-dropdown-menu');
  if (menu) {
    menu.classList.remove('show-menu');
  }
});
document.addEventListener('DOMContentLoaded', function() {
  const menu = document.querySelector('.account-dropdown-menu');
  if (menu) {
    menu.addEventListener('click', function(e) {
      e.stopPropagation();
    });
  }

  // Live Search Suggestions & Voice Search
  const searchInput = document.getElementById('headerSearch');
  const suggestionsBox = document.getElementById('searchSuggestions');
  const btnVoiceSearch = document.querySelector('.search-action-btn[title="Voice Search"]');
  const btnCameraSearch = document.getElementById('btnCameraSearch');
  const cameraSearchInput = document.getElementById('cameraSearchInput');

  if (btnCameraSearch && cameraSearchInput) {
    btnCameraSearch.addEventListener('click', () => cameraSearchInput.click());
    cameraSearchInput.addEventListener('change', function() {
      const file = this.files[0];
      if (!file) return;

      // Simulate AI Image tagging based on file name & typical image structures
      const name = file.name.toLowerCase();
      let matchedCategory = 'Gold'; // default

      if (name.includes('ring')) matchedCategory = 'Rings';
      else if (name.includes('earring') || name.includes('ear')) matchedCategory = 'Earrings';
      else if (name.includes('necklace') || name.includes('pendant') || name.includes('chain')) matchedCategory = 'Necklaces';
      else if (name.includes('bangle') || name.includes('bracelet')) matchedCategory = 'Bangles';
      else if (name.includes('silver')) matchedCategory = 'Silver';
      else if (name.includes('diamond')) matchedCategory = 'Diamond';
      else if (name.includes('wedding') || name.includes('bridal')) matchedCategory = 'Wedding';

      // Alert user of simulated match and redirect
      alert('AI Visual Search Detected: ' + matchedCategory + '\nFiltering catalog...');
      window.location.href = '/visnavi jwallers/pages/shop.php?category=' + encodeURIComponent(matchedCategory);
    });
  }
  
  if (searchInput && suggestionsBox) {
    const popularHtml = suggestionsBox.innerHTML;
    let debounceTimer = null;

    // Prevent input blur when clicking inside suggestions
    suggestionsBox.addEventListener('mousedown', function(e) {
      e.preventDefault();
    });

    searchInput.addEventListener('input', function() {
      clearTimeout(debounceTimer);
      const query = searchInput.value.trim();

      if (query.length < 2) {
        suggestionsBox.innerHTML = popularHtml;
        return;
      }

      debounceTimer = setTimeout(() => {
        fetch('/visnavi jwallers/api/search.php?q=' + encodeURIComponent(query))
          .then(res => res.json())
          .then(data => {
            if (data.items && data.items.length > 0) {
              let html = '<div class="suggestion-header">Products Found (' + data.items.length + ')</div>';
              data.items.forEach(item => {
                const img = item.image_url ? '/visnavi jwallers/' + item.image_url : 'https://images.unsplash.com/photo-1617038260897-41a1f14a8ca0?w=50&q=80';
                html += `
                  <a href="/visnavi jwallers/${item.url}" class="suggestion-item" style="display:flex; align-items:center; gap:10px; padding:8px 10px;">
                    <img src="${img}" style="width:45px; height:45px; object-fit:cover; border-radius:6px;" alt="${item.name}">
                    <div style="flex: 1; min-width: 0;">
                      <div style="font-weight:500; font-size:13px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color:#222;">${item.name}</div>
                      <div style="font-size:11px; color:#888;">${item.category} • ₹${parseFloat(item.price).toLocaleString('en-IN')}</div>
                    </div>
                  </a>
                `;
              });
              suggestionsBox.innerHTML = html;
            } else {
              suggestionsBox.innerHTML = `
                <div class="suggestion-header">No Results Found</div>
                <div style="padding:15px; font-size:13px; color:#888; text-align:center;">No jewellery found for "${query}"</div>
              `;
            }
          })
          .catch(err => {
            console.error('Error fetching search results:', err);
          });
      }, 250);
    });

    // Voice search integration
    if (btnVoiceSearch) {
      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (SpeechRecognition) {
        const recognition = new SpeechRecognition();
        recognition.continuous = false;
        recognition.lang = 'en-IN';
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;
        
        let isRecording = false;

        btnVoiceSearch.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          if (isRecording) {
            recognition.stop();
          } else {
            recognition.start();
          }
        });

        recognition.onstart = function() {
          isRecording = true;
          btnVoiceSearch.querySelector('i').className = 'fas fa-microphone voice-recording';
          searchInput.placeholder = "Listening...";
          searchInput.focus();
        };

        recognition.onresult = function(event) {
          const transcript = event.results[0][0].transcript;
          // Clean period characters that some recognizers add
          const cleanTranscript = transcript.replace(/\.$/g, '');
          searchInput.value = cleanTranscript;
          searchInput.dispatchEvent(new Event('input'));
        };

        recognition.onerror = function(event) {
          console.error('Speech recognition error:', event.error);
          resetVoiceUI();
        };

        recognition.onend = function() {
          resetVoiceUI();
        };

        function resetVoiceUI() {
          isRecording = false;
          btnVoiceSearch.querySelector('i').className = 'fas fa-microphone';
          searchInput.placeholder = "Search for jewellery, diamonds, rings...";
        }
      } else {
        // Hide microphone if Web Speech API is not supported by browser
        btnVoiceSearch.style.display = 'none';
      }
    }
  }
});
</script>

<!-- ========== CART DRAWER HTML ========== -->
<div class="cart-drawer-overlay" id="cartDrawerOverlay"></div>
<div class="cart-drawer" id="cartDrawer">
  <div class="cart-drawer-header">
    <h3><i class="fas fa-shopping-bag"></i> Shopping Bag</h3>
    <button class="cart-drawer-close" id="cartDrawerClose"><i class="fas fa-times"></i></button>
  </div>
  
  <div class="cart-drawer-body">
    <div class="cart-drawer-items" id="cartDrawerItems"></div>
    <div class="cart-drawer-empty" id="cartDrawerEmpty" style="display: none;">
      <i class="fas fa-shopping-bag"></i>
      <h5>Your Bag is Empty</h5>
      <p>Fill it with handcrafted luxury jewellery from our collections.</p>
      <a href="/visnavi jwallers/pages/shop.php" class="cart-drawer-btn cart-drawer-btn--checkout" style="text-decoration:none;">Start Shopping</a>
    </div>
  </div>
  
  <div class="cart-drawer-footer" id="cartDrawerFooter">
    <div class="cart-drawer-row">
      <span>Subtotal</span>
      <span id="drawerSubtotal">&#8377;0.00</span>
    </div>
    <div class="cart-drawer-row">
      <span>GST (3%)</span>
      <span id="drawerTax">&#8377;0.00</span>
    </div>
    <div class="cart-drawer-row">
      <span>Delivery</span>
      <span id="drawerShipping">Free</span>
    </div>
    <div class="cart-drawer-row total">
      <span>Total</span>
      <span id="drawerTotal">&#8377;0.00</span>
    </div>
    <div class="cart-drawer-btns">
      <a href="/visnavi jwallers/pages/checkout.php" class="cart-drawer-btn cart-drawer-btn--checkout" style="text-decoration:none;">Proceed to Checkout</a>
      <a href="/visnavi jwallers/pages/cart.php" class="cart-drawer-btn cart-drawer-btn--cart" style="text-decoration:none;">View Full Bag</a>
    </div>
  </div>
</div>

<!-- ========== CART DRAWER JS ACTIONS ========== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const overlay = document.getElementById('cartDrawerOverlay');
  const drawer = document.getElementById('cartDrawer');
  const closeBtn = document.getElementById('cartDrawerClose');
  const mainCartBtn = document.getElementById('cartDrawerBtn');
  const mobileCartBtn = document.getElementById('mobileCartDrawerBtn');
  
  const drawerItems = document.getElementById('cartDrawerItems');
  const drawerEmpty = document.getElementById('cartDrawerEmpty');
  const drawerFooter = document.getElementById('cartDrawerFooter');
  
  const subtotalVal = document.getElementById('drawerSubtotal');
  const taxVal = document.getElementById('drawerTax');
  const shippingVal = document.getElementById('drawerShipping');
  const totalVal = document.getElementById('drawerTotal');

  // Open Drawer Function
  window.openCartDrawer = function() {
    overlay.classList.add('open');
    drawer.classList.add('open');
    document.body.style.overflow = 'hidden'; // Prevent main page scrolling
    fetchCartDrawerItems();
  };

  // Close Drawer Function
  window.closeCartDrawer = function() {
    overlay.classList.remove('open');
    drawer.classList.remove('open');
    document.body.style.overflow = '';
  };

  // Event Listeners
  if (mainCartBtn) {
    mainCartBtn.addEventListener('click', function(e) {
      e.preventDefault();
      openCartDrawer();
    });
  }
  if (mobileCartBtn) {
    mobileCartBtn.addEventListener('click', function(e) {
      e.preventDefault();
      openCartDrawer();
    });
  }
  if (closeBtn) closeBtn.addEventListener('click', closeCartDrawer);
  if (overlay) overlay.addEventListener('click', closeCartDrawer);

  // Fetch Items from API
  function fetchCartDrawerItems() {
    fetch('api/cart-data.php?action=fetch&_v=' + Date.now(), { credentials: 'same-origin' })
      .then(response => response.json())
      .then(data => {
        if (data.ok) {
          updateCartBadge(data.cart_count);
          renderDrawerItems(data.items);
          
          if (data.items.length === 0) {
            drawerEmpty.style.display = 'flex';
            drawerItems.style.display = 'none';
            drawerFooter.style.display = 'none';
          } else {
            drawerEmpty.style.display = 'none';
            drawerItems.style.display = 'flex';
            drawerFooter.style.display = 'flex';
            
            subtotalVal.innerHTML = '&#8377;' + data.formatted_subtotal;
            taxVal.innerHTML = '&#8377;' + data.formatted_tax;
            shippingVal.innerHTML = data.formatted_shipping === 'FREE' ? 'Free' : '&#8377;' + data.formatted_shipping;
            totalVal.innerHTML = '&#8377;' + data.formatted_total;
          }
        }
      })
      .catch(error => console.error('Error fetching cart items:', error));
  }

  // Render items inside the drawer
  function renderDrawerItems(items) {
    drawerItems.innerHTML = items.map(item => {
      let img = item.image_url || 'assets/images/Bangles.png';
      if (!img.startsWith('http') && !img.startsWith('/')) {
        img = '/visnavi jwallers/' + img;
      }
      return `
        <div class="cart-drawer-item" data-product-id="${item.id}">
          <img class="cart-drawer-item-img" src="${img}" alt="${item.name}" onerror="this.src='/visnavi jwallers/assets/images/Rings.png'">
          <div class="cart-drawer-item-details">
            <div>
              <div class="cart-drawer-item-name">${item.name}</div>
              <div class="cart-drawer-item-price">&#8377;${Number(item.price).toLocaleString()}</div>
            </div>
            <div class="cart-drawer-qty-row">
              <div class="cart-drawer-qty-box">
                <button class="cart-drawer-qty-btn decrease-qty" data-product-id="${item.id}">-</button>
                <span class="cart-drawer-qty-val">${item.quantity}</span>
                <button class="cart-drawer-qty-btn increase-qty" data-product-id="${item.id}">+</button>
              </div>
              <button class="cart-drawer-item-remove" data-product-id="${item.id}"><i class="fas fa-trash-alt"></i></button>
            </div>
          </div>
        </div>
      `;
    }).join('');

    // Attach actions to dynamically created items
    drawerItems.querySelectorAll('.increase-qty').forEach(btn => {
      btn.addEventListener('click', function() {
        const pid = this.getAttribute('data-product-id');
        const currentVal = parseInt(this.previousElementSibling.textContent);
        updateCartDrawerQty(pid, currentVal + 1);
      });
    });

    drawerItems.querySelectorAll('.decrease-qty').forEach(btn => {
      btn.addEventListener('click', function() {
        const pid = this.getAttribute('data-product-id');
        const currentVal = parseInt(this.nextElementSibling.textContent);
        if (currentVal > 1) {
          updateCartDrawerQty(pid, currentVal - 1);
        } else {
          removeCartDrawerItem(pid);
        }
      });
    });

    drawerItems.querySelectorAll('.cart-drawer-item-remove').forEach(btn => {
      btn.addEventListener('click', function() {
        const pid = this.getAttribute('data-product-id');
        removeCartDrawerItem(pid);
      });
    });
  }

  // Update Item Quantity API call
  function updateCartDrawerQty(productId, qty) {
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('product_id', productId);
    formData.append('qty', qty);

    fetch('/visnavi jwallers/api/cart-data.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.ok) fetchCartDrawerItems();
    });
  }

  // Remove Item API call
  function removeCartDrawerItem(productId) {
    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('product_id', productId);

    fetch('/visnavi jwallers/api/cart-data.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.ok) fetchCartDrawerItems();
    });
  }

  // Update Cart Badges (Desktop & Mobile)
  function updateCartBadge(count) {
    const badges = document.querySelectorAll('[data-count-role="cart"]');
    badges.forEach(badge => {
      badge.textContent = count;
      badge.style.display = count > 0 ? 'inline-block' : 'none';
    });

    const mobileCartLink = document.getElementById('mobileCartDrawerBtn');
    if (mobileCartLink) {
      mobileCartLink.innerHTML = `<i class="fas fa-shopping-bag"></i> Cart (${count})`;
    }
  }

  // Globally intercept "Add to Cart" events
  document.body.addEventListener('click', function(e) {
    // Intercept buttons with data-cart attribute (like homepage, shop, related, wishlist)
    const cartBtn = e.target.closest('[data-cart]');
    if (cartBtn) {
      e.preventDefault();
      const pid = cartBtn.getAttribute('data-cart');
      
      // Check if product quantity selector is present on the page (e.g. product details page)
      const qtyInput = document.getElementById('productQty');
      const qty = qtyInput ? parseInt(qtyInput.value) : 1;

      const formData = new FormData();
      formData.append('action', 'add');
      formData.append('product_id', pid);
      formData.append('qty', qty);

      fetch('/visnavi jwallers/api/cart-data.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.ok) {
          openCartDrawer();
        }
      });
      return;
    }

    // Intercept product.php standard .btn-add button
    const detailAddBtn = e.target.closest('.btn-add');
    if (detailAddBtn && !detailAddBtn.hasAttribute('data-cart')) {
      // Find product id from current page url (product.php?id=XXX)
      const urlParams = new URLSearchParams(window.location.search);
      const pid = urlParams.get('id');
      if (pid) {
        e.preventDefault();
        const qtyInput = document.getElementById('productQty') || { value: 1 };
        const qty = parseInt(qtyInput.value) || 1;

        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('product_id', pid);
        formData.append('qty', qty);

        fetch('/visnavi jwallers/api/cart-data.php', {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.ok) {
            openCartDrawer();
          }
        });
      }
    }
  });
});
</script>


