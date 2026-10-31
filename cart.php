<?php
// Set session cookie params BEFORE session starts
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', '1');
}
@session_start();

// Clear old cart if too many items (session may have old data)
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $totalItems = array_sum($_SESSION['cart']);
    if ($totalItems > 20) {
        // Reset if too many (likely corrupted old session)
        $_SESSION['cart'] = [];
    }
}

// Get BASE_URL for proper path resolution
$baseUrl = '/visnavi jwallers/';

// Include helpers for asset() function
require_once __DIR__ . '/includes/base_url.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<base href="<?php echo $baseUrl; ?>">
<title>Cart - Vaishnavi Jewellers</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Poppins:wght@300;400;500;600&display=swap">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Poppins', sans-serif; background: #fafafa; padding-top: 0 !important; }
/* Remove whitespace from header elements */
main.main-content, .lux-gold-ticker, .page-has-gold-ticker { display: none !important; }
a { text-decoration: none; color: inherit; }

.page-header { background: #fff; padding: 15px 0; border-bottom: 1px solid #eee; margin-top: 0; }
.page-title { font-family: 'Cormorant Garamond', serif; font-size: 32px; color: #333; }
.breadcrumb { font-size: 13px; color: #888; margin-top: 8px; }
.breadcrumb a { color: #a52a2a; }
.container { max-width: 1400px; margin: 0 auto; padding: 0 20px; }

.cart-grid { display: grid; grid-template-columns: 1fr 400px; gap: 30px; padding: 40px 0; }
@media (max-width: 992px) { .cart-grid { grid-template-columns: 1fr; } }

/* Cart Items Section */
.cart-items { background: #fff; border-radius: 12px; padding: 25px; }
.cart-title { font-family: 'Cormorant Garamond', serif; font-size: 24px; margin-bottom: 25px; }

.cart-item { display: grid; grid-template-columns: 100px 1fr auto auto; gap: 20px; padding: 20px 0; border-bottom: 1px solid #eee; align-items: center; }
.cart-item:last-child { border-bottom: none; }
.item-img { width: 100px; height: 100px; background: #f9f9f9; border-radius: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.item-img img { width: 100%; height: 100%; object-fit: cover; }
.item-img .img-letter { font-size: 36px; color: #aaa; font-family: 'Cormorant Garamond', serif; font-weight: 600; }
.item-details h4 { font-weight: 600; color: #333; margin-bottom: 5px; font-size: 16px; }
.item-sku { font-size: 13px; color: #888; }
.item-price { font-size: 17px; font-weight: 700; color: #a52a2a; }
.item-price .original { text-decoration: line-through; color: #aaa; font-size: 14px; font-weight: 400; margin-right: 8px; }
.item-qty { display: flex; align-items: center; gap: 10px; }
.qty-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid #ddd; background: #fff; cursor: pointer; font-size: 16px; transition: all 0.2s; }
.qty-btn:hover { background: #a52a2a; color: #fff; border-color: #a52a2a; }
.qty-value { font-size: 16px; font-weight: 600; min-width: 30px; text-align: center; }
.item-remove { color: #a52a2a; cursor: pointer; font-size: 18px; padding: 8px; opacity: 0.7; transition: opacity 0.2s; }
.item-remove:hover { opacity: 1; }
.line-total { font-size: 16px; font-weight: 600; color: #333; min-width: 90px; text-align: right; }

/* Cart Summary */
.cart-summary { background: #fff; border-radius: 12px; padding: 30px; height: fit-content; position: sticky; top: 20px; }
.summary-title { font-family: 'Cormorant Garamond', serif; font-size: 24px; margin-bottom: 25px; }

.coupon-section { margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
.coupon-label { font-size: 13px; color: #666; margin-bottom: 10px; display: block; }
.coupon-form { display: flex; gap: 10px; }
.coupon-input { flex: 1; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; outline: none; transition: border-color 0.2s; }
.coupon-input:focus { border-color: #a52a2a; }
.coupon-btn { padding: 12px 20px; background: #333; color: #fff; border: none; border-radius: 8px; font-size: 14px; cursor: pointer; transition: background 0.2s; }
.coupon-btn:hover { background: #555; }
.coupon-message { font-size: 13px; margin-top: 10px; }
.coupon-message.success { color: #28a745; }
.coupon-message.error { color: #dc3545; }
.coupon-applied { display: flex; justify-content: space-between; align-items: center; background: #f0fdf4; padding: 10px 15px; border-radius: 8px; margin-bottom: 15px; }
.coupon-applied .coupon-code { font-weight: 600; color: #28a745; }
.coupon-applied .remove-coupon { color: #dc3545; cursor: pointer; font-size: 13px; }

.summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; color: #555; }
.summary-row.savings { color: #28a745; }
.summary-row.shipping-free { color: #28a745; font-weight: 600; }
.summary-row.total { font-size: 20px; font-weight: 700; color: #333; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
.summary-row.total span:last-child { color: #a52a2a; }
.summary-row .strike { text-decoration: line-through; color: #aaa; }

.btn-checkout { background: linear-gradient(135deg, #a52a2a 0%, #8a2020 100%); color: #fff; padding: 16px; border: none; border-radius: 10px; width: 100%; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 20px; transition: transform 0.2s, box-shadow 0.2s; }
.btn-checkout:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(165, 42, 42, 0.3); }
.btn-continue { display: block; text-align: center; color: #666; margin-top: 15px; font-size: 14px; }
.btn-continue:hover { color: #a52a2a; }

/* Empty Cart */
.empty-cart { text-align: center; padding: 60px 20px; }
.empty-icon { font-size: 80px; color: #ddd; margin-bottom: 20px; }
.empty-title { font-family: 'Cormorant Garamond', serif; font-size: 28px; color: #333; margin-bottom: 10px; }
.empty-text { color: #666; margin-bottom: 25px; }
.btn-shop-now { background: #a52a2a; color: #fff; padding: 14px 30px; border: none; border-radius: 30px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
.btn-shop-now:hover { background: #8a2020; }

/* Trust Badges */
.trust-badges { display: flex; justify-content: center; gap: 20px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
.trust-badge { text-align: center; font-size: 12px; color: #888; }
.trust-badge i { font-size: 20px; color: #a52a2a; margin-bottom: 5px; display: block; }

/* Shipping Info */
.shipping-info { background: #f9f9f9; padding: 15px; border-radius: 8px; margin-top: 15px; font-size: 13px; color: #666; }
.shipping-info i { color: #28a745; margin-right: 8px; }
.free-shipping-progress { margin-top: 15px; }
.progress-label { font-size: 13px; color: #666; margin-bottom: 8px; }
.progress-bar-wrap { background: #e0e0e0; border-radius: 10px; height: 8px; overflow: hidden; }
.progress-bar { background: linear-gradient(90deg, #a52a2a, #d4a574); height: 100%; border-radius: 10px; transition: width 0.3s; }
.progress-text { font-size: 12px; color: #28a745; margin-top: 5px; font-weight: 600; }
</style>
</head>
<body>

<?php include __DIR__ . '/includes/header.php';

// Initialize billing variables
$cartItems = [];
$subtotal = 0;
$subtotalOriginal = 0;
$discount = 0;
$shipping = 0;
$tax = 0;
$total = 0;
$itemCount = 0;
$couponCode = '';
$couponDiscount = 0;
$freeShippingThreshold = 50000; // Rs. 50,000 for free shipping
$taxRate = 3; // GST 3%
$originalSubtotal = 0;
$finalSubtotal = 0;
$totalDiscount = 0;

// Try to fetch cart from database
$pdo = null;
$dbConnected = false;
$cartItems = [];
$itemCount = 0;

try {
    require_once __DIR__ . '/includes/db.php';
    require_once __DIR__ . '/includes/cart_db.php';
    $pdo = db();
    $cartItems = get_cart_items($pdo);
    
    // Map keys to match expected template structure
    foreach ($cartItems as &$item) {
        $item['product_id'] = (int)$item['product_id'];
    }
    
    $itemCount = cart_count($pdo);
    $dbConnected = true;
} catch (Throwable $e) {
    $dbConnected = false;
}

if (!$dbConnected) {
    $sessionCart = $_SESSION['cart'] ?? [];
    if (!empty($sessionCart)) {
        // Load products from database using actual product IDs
        require_once __DIR__ . '/includes/db.php';
        $pdo = db();

        $productIds = array_keys($sessionCart);
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));

        $stmt = $pdo->prepare("
            SELECT p.id, p.name, p.slug, p.sku, p.base_price, p.discount_percent,
                   (
                     SELECT pi.image_url
                     FROM product_images pi
                     WHERE pi.product_id = p.id
                     ORDER BY pi.is_primary DESC, pi.display_order ASC, pi.id ASC
                     LIMIT 1
                   ) AS primary_image_url
            FROM products p
            WHERE p.id IN ($placeholders)
        ");
        $stmt->execute($productIds);
        $dbProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Map products by ID
        $productMap = [];
        foreach ($dbProducts as $p) {
            $productMap[$p['id']] = $p;
        }

        $cartItems = [];
        $totalQty = 0;
        foreach ($sessionCart as $pid => $qty) {
            $pid = (int)$pid;
            if (isset($productMap[$pid])) {
                $p = $productMap[$pid];
                $unitPrice = (float)$p['base_price'];
                if ($p['discount_percent'] > 0) {
                    $unitPrice = $unitPrice * (1 - $p['discount_percent'] / 100);
                }
                $cartItems[] = [
                    'product_id' => $pid,
                    'sku' => $p['sku'],
                    'product_name' => $p['name'],
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'primary_image_url' => $p['primary_image_url']
                ];
                $totalQty += $qty;
            }
        }
        $itemCount = $totalQty;
    }
}

// DEBUG: show what's in session
$debugSession = $_SESSION['cart'] ?? [];
$debugDBConnected = $dbConnected ?? false;
$debugCartItems = count($cartItems ?? []);

// Calculate billing from cart items
foreach ($cartItems as $item) {
    $qty = (int)$item['quantity'];
    $unitPrice = isset($item['effective_price']) ? (float)$item['effective_price'] : (float)$item['unit_price'];
    $subtotal += $unitPrice * $qty;
}

// Store original subtotal
$originalSubtotal = $subtotal;

// Check for applied coupon
if (!empty($_SESSION['coupon_code'])) {
    $couponCode = $_SESSION['coupon_code'];
    $couponDiscount = (float)($_SESSION['coupon_discount'] ?? 0);
}

// Calculate totals
$finalSubtotal = max(0, $subtotal - $couponDiscount);
$shipping = ($finalSubtotal >= $freeShippingThreshold || $finalSubtotal === 0) ? 0 : 500;
$tax = $finalSubtotal * ($taxRate / 100);
$total = $finalSubtotal + $shipping + $tax;

?>

<!-- Page Header -->
<div class="page-header">
<div class="container">
<h1 class="page-title">Shopping Cart</h1>
<div class="breadcrumb"><a href="<?php echo $baseUrl; ?>">Home</a> / Cart</div>
</div>
</div>

<!-- Cart Section -->
<div class="container" id="cart-container-wrap">
<?php if (empty($cartItems)): ?>
<!-- Empty Cart -->
<div class="empty-cart" style="text-align: center; padding: 80px 20px; background: #fff; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); margin: 40px 0;">
<div class="empty-icon" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"><i class="fas fa-shopping-bag"></i></div>
<h3 class="empty-title" style="font-family: 'Cormorant Garamond', serif; font-size: 28px; color: #333; margin-bottom: 10px;">Your cart is empty</h3>
<p class="empty-text" style="color: #666; margin-bottom: 25px;">Looks like you haven't added anything to your cart yet.</p>
<a href="pages/shop.php" class="btn-shop-now" style="background: #a52a2a; color: #fff; padding: 14px 30px; border: none; border-radius: 30px; font-weight: 600; text-decoration: none; display: inline-block;">Shop Now</a>
</div>
<?php else: ?>
<div class="cart-grid">
<!-- Cart Items -->
<div class="cart-items">
<h2 class="cart-title">Your Cart (<?php echo $itemCount; ?> <?php echo $itemCount == 1 ? 'Item' : 'Items'; ?>)</h2>

<?php foreach ($cartItems as $item): ?>
<?php
$qty = (int)$item['quantity'];
$unitPrice = isset($item['effective_price']) ? (float)$item['effective_price'] : (float)$item['unit_price'];
$lineTotal = $unitPrice * $qty;
$firstLetter = strtoupper(substr($item['product_name'] ?? $item['product_name'], 0, 1));
?>
<div class="cart-item" data-item-row="<?php echo (int)$item['product_id']; ?>" data-unit-price="<?php echo $unitPrice; ?>">
<div class="item-img">
<?php
$imgUrl = $item['primary_image_url'] ?? '';
if (empty($imgUrl)) {
    $images = ['Rings.png','Earrings.png','Necklaces.png','Bangles.png','Bracelets.png','Pendants.png'];
    $imgUrl = asset('images/' . $images[($item['product_id'] ?? 0) % count($images)]);
}
?>
<img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
</div>
<div class="item-details">
<h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
<p class="item-sku">SKU: <?php echo htmlspecialchars($item['sku']); ?></p>
<p class="item-price">Rs. <?php echo number_format($unitPrice); ?></p>
</div>
<div class="item-qty">
<button class="qty-btn" data-cart-qty-minus data-product-id="<?php echo (int)$item['product_id']; ?>">-</button>
<span class="qty-value"><?php echo (int)$item['quantity']; ?></span>
<button class="qty-btn" data-cart-qty-plus data-product-id="<?php echo (int)$item['product_id']; ?>">+</button>
</div>
<div class="line-total">Rs. <?php echo number_format($lineTotal); ?></div>
<button class="item-remove" title="Remove" data-cart-remove data-product-id="<?php echo (int)$item['product_id']; ?>"><i class="fas fa-trash"></i></button>
</div>
<?php endforeach; ?>
</div>

<!-- Cart Summary -->
<div class="cart-summary">
<h3 class="summary-title">Order Summary</h3>

<!-- Coupon Section -->
<div class="coupon-section">
<label class="coupon-label">Have a coupon code?</label>
<form class="coupon-form" method="POST" action="<?php echo $baseUrl; ?>api/apply-coupon.php">
<input type="text" class="coupon-input" name="coupon_code" placeholder="Enter coupon code" value="<?php echo htmlspecialchars($couponCode); ?>">
<button type="submit" class="coupon-btn">Apply</button>
</form>
<?php if ($couponCode): ?>
<div class="coupon-applied">
<span class="coupon-code"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($couponCode); ?></span>
<span class="remove-coupon" onclick="removeCoupon()">Remove</span>
</div>
<?php endif; ?>
</div>

<!-- Billing Breakdown -->
<div id="billing-breakdown">
<div class="summary-row">
<span>Subtotal</span>
<span id="summary-subtotal">Rs. <?php echo number_format(isset($originalSubtotal) && $originalSubtotal > 0 ? $originalSubtotal : $subtotal); ?></span>
</div>

<div id="summary-coupon-row-wrap">
<?php if ($couponDiscount > 0): ?>
<div class="summary-row savings"><span>Coupon Discount</span><span>-Rs. <?php echo number_format($couponDiscount); ?></span></div>
<?php endif; ?>
</div>

<div id="summary-shipping-wrap">
<?php if ($finalSubtotal < $freeShippingThreshold && $finalSubtotal > 0): ?>
<?php $progress = ($finalSubtotal / $freeShippingThreshold) * 100; ?>
<div class="free-shipping-progress">
<div class="progress-label">Add Rs. <?php echo number_format($freeShippingThreshold - $finalSubtotal); ?> more for FREE shipping!</div>
<div class="progress-bar-wrap"><div class="progress-bar" style="width: <?php echo min($progress, 100); ?>%"></div></div>
<div class="progress-text"><?php echo round($progress); ?>% towards free shipping</div>
</div>
<?php else: ?>
<div class="summary-row shipping-free"><span>Shipping</span><span><i class="fas fa-truck"></i> FREE</span></div>
<?php endif; ?>
</div>

<div id="summary-shipping-fee-row-wrap">
<?php if ($shipping > 0): ?>
<div class="summary-row"><span>Shipping</span><span id="summary-shipping-fee">Rs. <?php echo number_format($shipping); ?></span></div>
<?php endif; ?>
</div>

<div class="summary-row"><span>GST (<?php echo $taxRate; ?>%)</span><span id="summary-gst">Rs. <?php echo number_format($tax, 2); ?></span></div>

<div class="summary-row total">
<span>Total</span>
<span id="summary-total">Rs. <?php echo number_format($total, 2); ?></span>
</div>
</div>

<!-- Shipping Info -->
<div class="shipping-info">
<p><i class="fas fa-check-circle"></i> Free shipping on orders above Rs. 50,000</p>
<p><i class="fas fa-check-circle"></i> Safe &secure payment with SSL encryption</p>
<p><i class="fas fa-check-circle"></i> 100% BIS Hallmarked jewellery</p>
</div>

<a href="<?php echo $baseUrl; ?>checkout.php" class="btn-checkout"><i class="fas fa-lock"></i> Proceed to Checkout</a>
<a href="<?php echo $baseUrl; ?>pages/shop.php" class="btn-continue">Continue Shopping</a>

<!-- Trust Badges -->
<div class="trust-badges">
<div class="trust-badge">
<i class="fas fa-shield-alt"></i>
<span>Secure<br>Checkout</span>
</div>
<div class="trust-badge">
<i class="fas fa-medal"></i>
<span>100%<br>Hallmarked</span>
</div>
<div class="trust-badge">
<i class="fas fa-undo"></i>
<span>Easy<br>Returns</span>
</div>
</div>
</div>
</div>
<?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/cart-ui.js?v=<?= time(); ?>"></script>
<script>
function removeCoupon() {
    fetch('<?php echo $baseUrl; ?>api/remove-coupon.php', { method: 'POST' })
        .then(() => window.location.reload())
        .catch(() => window.location.reload());
}
</script>
</body>
</html>