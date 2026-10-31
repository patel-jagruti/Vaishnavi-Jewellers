<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/cart_db.php';

ensure_session();
$pdo = db();
ensure_wishlist_table($pdo);
$items = wishlist_items($pdo);
$itemCount = count($items);

require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* Fix the empty main-content element from header.php which pushes content down */
main.main-content {
  display: none !important;
}

/* Wishlist Page Styling */
.wishlist-page {
  background: #FFF8F0;
  min-height: 70vh;
  padding: 40px 0 80px 0;
}

.wishlist-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 20px;
}

/* Page Header breadcrumbs */
.wish-breadcrumbs {
  font-size: 13px;
  color: #888;
  margin-bottom: 25px;
  text-transform: uppercase;
  letter-spacing: 1px;
}
.wish-breadcrumbs a {
  color: #6B1E2D;
  font-weight: 500;
  transition: color 0.3s;
}
.wish-breadcrumbs a:hover {
  color: #D4AF37;
}

.wishlist-main-title {
  font-family: 'Playfair Display', serif;
  font-size: 42px;
  color: #1A1A1A;
  margin-bottom: 8px;
  font-weight: 700;
}

.wishlist-subtitle {
  font-size: 15px;
  color: #666;
  margin-bottom: 45px;
  font-style: italic;
}

/* Grid Layout */
.wish-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 30px;
}

@media (max-width: 1200px) {
  .wish-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}
@media (max-width: 900px) {
  .wish-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
  }
  .wishlist-main-title {
    font-size: 32px;
  }
}
@media (max-width: 480px) {
  .wish-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }
}

/* Wishlist Card */
.wish-card {
  background: #ffffff;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
  position: relative;
  transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
  border: 1px solid rgba(107, 30, 45, 0.05);
}

.wish-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(107, 30, 45, 0.08);
  border-color: rgba(212, 175, 55, 0.3);
}

/* Floating Remove Button */
.wish-card .remove-wish-btn {
  position: absolute;
  top: 15px;
  right: 15px;
  background: #ffffff;
  border: 1px solid rgba(0, 0, 0, 0.05);
  width: 38px;
  height: 38px;
  border-radius: 50%;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  color: #666;
  font-size: 15px;
  z-index: 5;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.wish-card .remove-wish-btn:hover {
  background: #6B1E2D;
  color: #ffffff;
  border-color: #6B1E2D;
  transform: scale(1.08);
}

/* Product Thumbnail */
.wish-thumb {
  height: 240px;
  background: linear-gradient(135deg, #FAF8F6 0%, #FFF8F0 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 30px;
  position: relative;
  overflow: hidden;
}

.wish-thumb img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.wish-card:hover .wish-thumb img {
  transform: scale(1.06);
}

.wish-discount-badge {
  position: absolute;
  top: 15px;
  left: 15px;
  background: #D4AF37;
  color: #1A1A1A;
  padding: 5px 12px;
  border-radius: 30px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.5px;
  box-shadow: 0 4px 8px rgba(212, 175, 55, 0.2);
}

/* Card Body */
.wish-body {
  padding: 24px;
}

.wish-cat {
  font-size: 11px;
  color: #D4AF37;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  font-weight: 600;
  margin-bottom: 8px;
}

.wish-title {
  font-family: 'Playfair Display', serif;
  font-size: 17px;
  font-weight: 600;
  margin-bottom: 12px;
  line-height: 1.4;
  height: 48px;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.wish-title a {
  color: #1A1A1A;
  transition: color 0.3s;
}

.wish-title a:hover {
  color: #6B1E2D;
}

.wish-price-row {
  display: flex;
  align-items: baseline;
  gap: 10px;
  margin-bottom: 20px;
}

.wish-price-current {
  font-size: 20px;
  font-weight: 700;
  color: #6B1E2D;
}

.wish-price-original {
  font-size: 14px;
  color: #aaa;
  text-decoration: line-through;
}

/* Add to Cart button style */
.btn-wish-add-cart {
  width: 100%;
  background: #6B1E2D;
  color: #ffffff;
  padding: 13px;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(107, 30, 45, 0.15);
}

.btn-wish-add-cart:hover {
  background: #D4AF37;
  color: #1A1A1A;
  box-shadow: 0 6px 20px rgba(212, 175, 55, 0.25);
  transform: translateY(-2px);
}

/* Empty State Styling */
.wish-empty-wrap {
  text-align: center;
  padding: 80px 40px;
  background: #ffffff;
  border-radius: 30px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.02);
  border: 1px solid rgba(107, 30, 45, 0.05);
  max-width: 650px;
  margin: 40px auto;
}

.wish-empty-icon {
  width: 100px;
  height: 100px;
  background: rgba(107, 30, 45, 0.04);
  color: #6B1E2D;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 40px;
  margin: 0 auto 30px auto;
  border: 1px solid rgba(107, 30, 45, 0.1);
}

.wish-empty-title {
  font-family: 'Playfair Display', serif;
  font-size: 28px;
  color: #1A1A1A;
  margin-bottom: 12px;
  font-weight: 700;
}

.wish-empty-desc {
  font-size: 15px;
  color: #666;
  line-height: 1.6;
  margin-bottom: 35px;
}

.btn-wish-shop {
  background: #6B1E2D;
  color: #ffffff;
  padding: 14px 35px;
  border-radius: 30px;
  font-weight: 600;
  font-size: 15px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(107, 30, 45, 0.2);
  display: inline-block;
  text-decoration: none;
}

.btn-wish-shop:hover {
  background: #D4AF37;
  color: #1A1A1A;
  box-shadow: 0 6px 20px rgba(212, 175, 55, 0.3);
  transform: translateY(-3px);
}
</style>

<div class="wishlist-page">
  <div class="wishlist-container">
    
    <div class="wish-breadcrumbs">
      <a href="index.php">Home</a> / <span>Wishlist</span>
    </div>

    <div id="wishlist-container-wrap">
      <?php if ($itemCount === 0): ?>
        <!-- Empty Wishlist State -->
        <div class="wish-empty-wrap">
          <div class="wish-empty-icon">
            <i class="far fa-heart"></i>
          </div>
          <h3 class="wish-empty-title">Your Wishlist is Empty</h3>
          <p class="wish-empty-desc">
            Explore our exquisite collections of gold, diamond, and silver jewellery. Add items you love to your wishlist to keep track of them.
          </p>
          <a href="pages/shop.php" class="btn-wish-shop">Explore Collection</a>
        </div>
      <?php else: ?>
        <h1 class="wishlist-main-title">My Wishlist</h1>
        <p class="wishlist-subtitle">You have <?= $itemCount ?> premium <?= $itemCount === 1 ? 'item' : 'items' ?> saved in your collection.</p>

        <div class="wish-grid">
          <?php foreach ($items as $p): ?>
            <?php
              // Resolve image URL
              $imgUrl = $p['primary_image_url'] ?? $p['primary_image'] ?? '';
              if (empty($imgUrl)) {
                $images = ['Rings.png','Earrings.png','Necklaces.png','Bangles.png','Bracelets.png','Pendants.png'];
                $imgUrl = 'assets/images/' . $images[((int)$p['id']) % count($images)];
              }
              $imgUrl = asset($imgUrl);
              
              $discount = (float)($p['discount_percent'] ?? 0);
              $basePrice = (float)($p['base_price'] ?? $p['price'] ?? 0);
              $salePrice = $discount > 0 ? round($basePrice * (1 - ($discount/100))) : $basePrice;
            ?>
            <div class="wish-card" data-item-row="<?= (int)$p['id'] ?>">
              <!-- Remove from Wishlist Button -->
              <button class="remove-wish-btn" title="Remove from Wishlist" data-wishlist-remove data-product-id="<?= (int)$p['id'] ?>">
                <i class="fas fa-trash-alt"></i>
              </button>

              <!-- Product Image -->
              <div class="wish-thumb">
                <a href="pages/product.php?id=<?= (int)$p['id'] ?>">
                  <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars((string)$p['name']) ?>">
                </a>
                <?php if ($discount > 0): ?>
                  <span class="wish-discount-badge"><?= $discount ?>% OFF</span>
                <?php endif; ?>
              </div>

              <!-- Product Details -->
              <div class="wish-body">
                <div class="wish-cat"><?= htmlspecialchars((string)($p['category_name'] ?? $p['metal'] ?? 'Gold')) ?></div>
                <h4 class="wish-title">
                  <a href="pages/product.php?id=<?= (int)$p['id'] ?>"><?= htmlspecialchars((string)$p['name']) ?></a>
                </h4>
                
                <div class="wish-price-row">
                  <span class="wish-price-current">₹<?= number_format($salePrice) ?></span>
                  <?php if ($discount > 0): ?>
                    <span class="wish-price-original">₹<?= number_format($basePrice) ?></span>
                  <?php endif; ?>
                </div>

                <!-- Add to Cart Button -->
                <button class="btn-wish-add-cart" data-cart-add data-product-id="<?= (int)$p['id'] ?>">
                  <i class="fas fa-shopping-bag"></i> Add to Cart
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script src="<?= asset('js/cart-ui.js') ?>?v=<?= time(); ?>"></script>
</body>
</html>
