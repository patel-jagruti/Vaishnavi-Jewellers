<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', '1');
    @session_start();
}

$errors = [];
$success = false;
$cartItems = [];
$subtotal = 0;
$shipping = 0;
$tax = 0;
$total = 0;
$itemCount = 0;

// Try DB-backed cart first
$dbConnected = false;
$cartItems = [];
$itemCount = 0;
$subtotal = 0;

try {
    require_once __DIR__ . '/includes/db.php';
    require_once __DIR__ . '/includes/cart_db.php';
    $pdoCheckout = db();
    $dbItems = get_cart_items($pdoCheckout);
    if (!empty($dbItems)) {
        $cartItems = [];
        foreach ($dbItems as $item) {
            $cartItems[] = [
                'product_id' => (int)$item['product_id'],
                'sku' => $item['sku'],
                'product_name' => $item['product_name'],
                'quantity' => (int)$item['quantity'],
                'unit_price' => (float)$item['effective_price']
            ];
            $itemCount += (int)$item['quantity'];
            $subtotal += (float)$item['effective_price'] * (int)$item['quantity'];
        }
        $dbConnected = true;
    }
} catch (Throwable $e) {
    $dbConnected = false;
}

if (!$dbConnected) {
    $sessionCart = $_SESSION['cart'] ?? [];
    if (!empty($sessionCart)) {
        require_once __DIR__ . '/includes/db.php';
        $pdoCheckout = db();

        $productIds = array_keys($sessionCart);
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));

        $stmt = $pdoCheckout->prepare("SELECT id, name, slug, sku, base_price, discount_percent FROM products WHERE id IN ($placeholders)");
        $stmt->execute($productIds);
        $dbProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $productMap = [];
        foreach ($dbProducts as $p) {
            $productMap[$p['id']] = $p;
        }

        $cartItems = [];
        $itemCount = 0;
        $subtotal = 0;
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
                    'unit_price' => $unitPrice
                ];
                $itemCount += $qty;
                $subtotal += $unitPrice * $qty;
            }
        }
    }
}

$taxRate = 3;
$freeShippingThreshold = 50000;
$shipping = ($subtotal >= $freeShippingThreshold || $subtotal === 0) ? 0 : 500;
$tax = $subtotal * ($taxRate / 100);
$total = $subtotal + $shipping + $tax;

// Keep PDO for order saving (optional)
$pdo = null;
try {
    require_once __DIR__ . '/includes/db.php';
    $pdo = db();
} catch (Exception $e) {
    $pdo = null;
}

// Get user data
$userData = $_SESSION['user'] ?? [];

// Handle form submission
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($cartItems)) {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? 'COD';

    if (!$firstName) $errors[] = 'First name is required';
    if (!$lastName) $errors[] = 'Last name is required';
    if (!$email) $errors[] = 'Email is required';
    if (!$phone) $errors[] = 'Phone is required';
    if (!$address) $errors[] = 'Address is required';
    if (!$city) $errors[] = 'City is required';
    if (!$state) $errors[] = 'State is required';
    if (!$pincode) $errors[] = 'PIN code is required';

    if (empty($errors)) {
        if ($paymentMethod === 'RAZORPAY') {
            $razorpayPaymentId = $_POST['razorpay_payment_id'] ?? '';
            $razorpayOrderId = $_POST['razorpay_order_id'] ?? '';
            $razorpaySignature = $_POST['razorpay_signature'] ?? '';
            
            // Perform signature verification if this is not a simulated order
            if ($razorpaySignature && strpos($razorpayOrderId, 'order_sim_') === false) {
                $config = require __DIR__ . '/includes/config.php';
                $keySecret = $config['payments']['razorpay']['key_secret'] ?? '';
                
                $expectedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, $keySecret);
                
                if (!hash_equals($expectedSignature, $razorpaySignature)) {
                    $errors[] = 'Payment signature verification failed. Please try again.';
                }
            }
        }
    }

    if (empty($errors) && $pdo instanceof PDO) {
        try {
            $userId = !empty($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;

            $stmt = $pdo->prepare("INSERT INTO addresses (user_id, full_name, phone, line1, city, state, pincode, country) VALUES (?, ?, ?, ?, ?, ?, ?, 'India')");
            $stmt->execute([$userId, $firstName . ' ' . $lastName, $phone, $address, $city, $state, $pincode]);
            $addressId = (int)$pdo->lastInsertId();

            $orderCode = 'VJ-' . strtoupper(bin2hex(random_bytes(4)));
            $shippingAddr = "$address, $city, $state - $pincode";

            $paymentStatus = ($_POST['payment_status'] ?? 'PENDING') === 'PAID' ? 'PAID' : 'PENDING';
            $stmt = $pdo->prepare("INSERT INTO orders (order_code, user_id, address_id, customer_name, customer_email, customer_phone, shipping_address, subtotal, shipping, discount_amount, total, coupon_code, payment_method, payment_status, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PLACED')");
            $stmt->execute([$orderCode, $userId, $addressId, $firstName . ' ' . $lastName, $email, $phone, $shippingAddr, $subtotal, $shipping, 0, $total, null, $paymentMethod, $paymentStatus]);
            $orderId = (int)$pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_sku, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($cartItems as $item) {
                $unitPrice = isset($item['effective_price']) ? $item['effective_price'] : $item['unit_price'];
                $stmt->execute([$orderId, $item['product_id'] ?? null, $item['product_name'], $item['sku'] ?? '', $item['quantity'], $unitPrice, $unitPrice * $item['quantity']]);
            }

            // Clear session and database cart after successful order
            if ($pdo instanceof PDO) {
                try {
                    require_once __DIR__ . '/includes/cart_db.php';
                    $cartId = get_or_create_cart($pdo);
                    $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?")->execute([$cartId]);
                } catch (Throwable $e) {}
            }
            $_SESSION['cart'] = [];
            unset($_SESSION['coupon_code'], $_SESSION['coupon_discount']);

            // Send Order Confirmation Email
            try {
                send_order_success_email($orderId);
            } catch (Throwable $ignored) {}

            header('Location: pages/invoice.php?code=' . $orderCode . '&print=1');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Error placing order: ' . $e->getMessage();
        }
    } elseif (empty($errors)) {
        $success = true;
    }
}

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout | Vaishnavi Jewellers</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root { --burgundy: #7A1F35; --gold: #D4AF37; --ivory: #FFF8F0; --dark: #1A1A1A; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Poppins', sans-serif; background: var(--ivory); }
h1, h2, h3 { font-family: 'Playfair Display', serif; }
.navbar { background: #fff; box-shadow: 0 2px 25px rgba(0,0,0,0.06); padding: 15px 0; }
.navbar-brand { font-family: 'Playfair Display', serif; font-size: 28px; color: var(--burgundy); }
.checkout-sec { padding: 80px 0; background: #fff; }
.form-card { background: #fff; border-radius: 25px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); }
.form-card h3 { font-size: 28px; margin-bottom: 30px; }
.form-label { font-weight: 500; margin-bottom: 10px; }
.form-control { padding: 16px 20px; border-radius: 12px; border: 1px solid #ddd; margin-bottom: 20px; }
.form-control:focus { border-color: var(--burgundy); box-shadow: none; }
.summary { 
  background: linear-gradient(135deg, #2D050B 0%, #1A0206 100%); 
  border: 1px solid var(--gold); 
  border-radius: 25px; 
  padding: 40px 35px; 
  color: #ffffff; 
  position: sticky; 
  top: 20px; 
  box-shadow: 0 15px 35px rgba(45, 5, 11, 0.4);
}
.summary h3 { 
  font-family: 'Playfair Display', serif; 
  font-size: 26px; 
  font-weight: 700; 
  color: var(--gold); 
  border-bottom: 2px solid var(--gold); 
  padding-bottom: 12px; 
  margin-bottom: 25px; 
  letter-spacing: 0.5px;
}
.summary-row { 
  display: flex; 
  justify-content: space-between; 
  align-items: center; 
  padding: 14px 0; 
  border-bottom: 1px solid rgba(255, 255, 255, 0.1); 
  font-size: 14.5px; 
  font-family: 'Poppins', sans-serif;
  color: rgba(255, 255, 255, 0.9);
}
.summary-row span:last-child {
  font-weight: 600;
  color: var(--gold);
}
.summary-row.total { 
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 15px 20px;
  font-size: 18px; 
  font-weight: 700; 
  border: 1px solid rgba(212, 175, 55, 0.2); 
  margin-top: 20px;
  color: #ffffff;
}
.summary-row.total span:last-child {
  color: var(--gold);
  font-size: 22px;
}
.place-btn { 
  background: linear-gradient(135deg, var(--gold) 0%, #B69527 100%); 
  color: #1a1a1a; 
  padding: 16px; 
  border-radius: 50px; 
  font-weight: 700; 
  width: 100%; 
  border: none; 
  font-size: 16px; 
  margin-top: 25px; 
  text-transform: uppercase; 
  letter-spacing: 1px; 
  cursor: pointer; 
  transition: all 0.3s ease; 
  box-shadow: 0 8px 20px rgba(212, 175, 55, 0.2);
}
.place-btn:hover { 
  background: #ffffff; 
  color: #1a1a1a;
  transform: translateY(-2px); 
  box-shadow: 0 12px 25px rgba(255, 255, 255, 0.25);
}
.alert-danger { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
.alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
.payment-option { display: flex; align-items: center; padding: 15px; border: 1px solid #ddd; border-radius: 12px; margin-bottom: 10px; cursor: pointer; transition: all 0.2s ease; }
.payment-option:hover { border-color: var(--burgundy); }
.payment-option:has(input[type="radio"]:checked) { border-color: var(--burgundy) !important; background-color: #faf4f5; box-shadow: 0 0 0 2px rgba(122, 31, 42, 0.15); }
.payment-option input { margin-right: 15px; cursor: pointer; }
.payment-option i { margin-right: 10px; color: var(--burgundy); }
.footer { background: var(--dark); color: #fff; padding: 50px 0 25px; }
.footer-btm { border-top: 1px solid #222; padding: 20px 0; margin-top: 40px; text-align: center; color: #555; }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
<div class="container">
<a class="navbar-brand" href="index.php"><img src="assets/images/logo.svg" alt="Vaishnavi Jewellers" style="height:40px;"></a>
<div class="collapse navbar-collapse">
<ul class="navbar-nav ms-auto">
<li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
<li class="nav-item"><a class="nav-link" href="pages/shop.php">Shop</a></li>
<li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
</ul>
</div>
</div>
</nav>

<section class="checkout-sec">
<div class="container">
<h1 style="margin-bottom: 40px; font-size: 48px;">Checkout</h1>

<?php if ($success): ?>
<div class="alert-success">
<h4>Order Placed Successfully!</h4>
<p>Thank you for your order. You will receive a confirmation email shortly.</p>
<a href="orders.php" class="btn btn-success mt-3">View Orders</a>
</div>
<?php elseif (empty($cartItems)): ?>
<div class="alert alert-warning">
<h4>Your cart is empty</h4>
<p>Please add items to your cart before checkout.</p>
<a href="pages/shop.php" class="btn btn-primary mt-3">Continue Shopping</a>
</div>
<?php else: ?>

<form method="POST" action="checkout.php" id="checkoutForm">
<input type="hidden" name="payment_status" id="paymentStatusInput" value="PENDING">
<div class="row g-5">
<div class="col-lg-7">
<div class="form-card">
<h3>Shipping Address</h3>

<?php if ($errors): ?>
<div class="alert-danger">
<?php foreach ($errors as $err): ?><p class="mb-0"><?php echo $err; ?></p><?php endforeach; ?>
</div>
<?php endif; ?>

<div class="row">
<div class="col-md-6">
<label class="form-label">First Name *</label>
<input type="text" class="form-control" name="first_name" value="<?php echo h($userData['full_name'] ?? ''); ?>" required>
</div>
<div class="col-md-6">
<label class="form-label">Last Name *</label>
<input type="text" class="form-control" name="last_name" required>
</div>
</div>

<label class="form-label">Email *</label>
<input type="email" class="form-control" name="email" value="<?php echo h($userData['email'] ?? ''); ?>" required>

<label class="form-label">Phone *</label>
<input type="tel" class="form-control" name="phone" value="<?php echo h($userData['phone'] ?? ''); ?>" required>

<label class="form-label">Address *</label>
<textarea class="form-control" name="address" rows="3" required></textarea>

<div class="row">
<div class="col-md-4">
<label class="form-label">City *</label>
<input type="text" class="form-control" name="city" required>
</div>
<div class="col-md-4">
<label class="form-label">State *</label>
<input type="text" class="form-control" name="state" required>
</div>
<div class="col-md-4">
<label class="form-label">PIN Code *</label>
<input type="text" class="form-control" name="pincode" required>
</div>
</div>

<h3 style="margin-top: 40px;">Payment Method</h3>
<label class="payment-option">
<input type="radio" name="payment_method" value="COD" checked>
<i class="fas fa-money-bill-wave"></i>
<div><strong>Cash on Delivery</strong><p class="mb-0 text-muted">Pay when you receive</p></div>
</label>
<label class="payment-option">
<input type="radio" name="payment_method" value="RAZORPAY">
<i class="fas fa-credit-card"></i>
<div><strong>Online Payment</strong><p class="mb-0 text-muted">Pay via Razorpay</p></div>
</label>
</div>
</div>

<div class="col-lg-5">
<div class="summary">
<h3>Order Summary</h3>

<?php foreach ($cartItems as $item): ?>
<div class="summary-row">
<span><?php echo h($item['product_name']); ?> x <?php echo (int)$item['quantity']; ?></span>
<span>Rs. <?php $u = isset($item['effective_price']) ? $item['effective_price'] : $item['unit_price']; echo number_format($u * $item['quantity']); ?></span>
</div>
<?php endforeach; ?>

<div class="summary-row"><span>Subtotal</span><span>Rs. <?php echo number_format($subtotal); ?></span></div>

<?php if ($shipping > 0): ?>
<div class="summary-row"><span>Shipping</span><span>Rs. <?php echo number_format($shipping); ?></span></div>
<?php else: ?>
<div class="summary-row"><span>Shipping</span><span>FREE</span></div>
<?php endif; ?>

<div class="summary-row"><span>GST (3%)</span><span>Rs. <?php echo number_format($tax, 2); ?></span></div>

<div class="summary-row total"><span>Total</span><span>Rs. <?php echo number_format($total, 2); ?></span></div>

<button type="submit" class="place-btn"><i class="fas fa-lock"></i> Place Order</button>
<p class="text-center mt-3" style="font-size: 13px; opacity: 0.8;"><i class="fas fa-shield-alt"></i> Secure checkout</p>
</div>
</div>
</form>

<?php endif; ?>
</div>
</section>

<footer class="footer">
<div class="container">
<div class="footer-btm"><p>&copy; 2026 Vaishnavi Jewellers</p></div>
</div>
</footer>

<!-- Simulated Payment Modal -->
<div class="modal fade" id="paymentSimulateModal" tabindex="-1" aria-labelledby="paymentSimulateModalLabel" aria-hidden="true" style="z-index:999999;">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
    <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 15px 50px rgba(107,30,45,0.2);">
      <div class="modal-header" style="border-bottom: none; padding: 25px 25px 0 25px; justify-content: center; text-align: center; flex-direction: column; position: relative; border-radius:20px 20px 0 0;">
        <div style="background: rgba(122,31,53,0.06); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
          <i class="fas fa-shield-alt" style="font-size: 28px; color: #7A1F35;"></i>
        </div>
        <h5 class="modal-title" id="paymentSimulateModalLabel" style="font-family: 'Playfair Display', serif; font-weight: 700; color: #222; font-size: 22px; margin:0;">Razorpay Secure Checkout</h5>
        <p style="font-size: 13px; color: #888; font-family: 'Poppins', sans-serif; margin-top: 5px; margin-bottom: 0;">Simulated Secure Sandbox Payment</p>
      </div>
      <div class="modal-body" style="padding: 25px; text-align: center;">
        <p style="font-size: 14px; color: #555; font-family: 'Poppins', sans-serif; margin-bottom: 25px;">You are paying <strong><span id="paymentTotalVal">0.00</span></strong> to Vaishnavi Jewellers.<br><span style="font-size: 12px; color: #888;">Select a mock result to complete the transaction:</span></p>
        <div class="d-flex flex-column gap-2">
          <button type="button" id="btnPaySuccess" class="btn btn-gold" style="background:#7A1F35; color:#fff; border:none; border-radius:30px; padding:12px; font-weight:600; font-size:14px; font-family:'Poppins', sans-serif;">Success (Mock Paid)</button>
          <button type="button" id="btnPayFail" class="btn btn-outline-danger" style="border-radius:30px; padding:12px; font-weight:600; font-size:14px; font-family:'Poppins', sans-serif;">Cancel / Failure (Mock Failed)</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('checkoutForm');
  const modal = document.getElementById('paymentSimulateModal');
  const paySuccessBtn = document.getElementById('btnPaySuccess');
  const payFailBtn = document.getElementById('btnPayFail');
  const totalValSpan = document.getElementById('paymentTotalVal');
  const paymentStatusInput = document.getElementById('paymentStatusInput');
  const btnSubmit = form ? form.querySelector('button[type="submit"]') : null;
  
  if (form) {
    form.addEventListener('submit', function(e) {
      const paymentMethodInput = form.querySelector('input[name="payment_method"]:checked');
      if (paymentMethodInput && paymentMethodInput.value === 'RAZORPAY') {
        // Prevent submission until payment goes through
        e.preventDefault();
        
        if (btnSubmit) {
          btnSubmit.disabled = true;
          btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Initiating Payment...';
        }
        
        // Call API to create Razorpay Order
        let apiUrl = '/visnavi%20jwallers/api/create-razorpay-order.php';
        const path = window.location.pathname;
        if (path.includes('/visnavi%20jwallers/visnavi%20jwallers/') || path.includes('/visnavi jwallers/visnavi jwallers/')) {
          apiUrl = '/visnavi%20jwallers/visnavi%20jwallers/api/create-razorpay-order.php';
        }
        
        fetch(apiUrl)
          .then(res => res.json())
          .then(data => {
            if (data.error) {
              alert('Order error: ' + data.error);
              if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = 'Place Order';
              }
              return;
            }
            
            // Check if simulated
            if (data.simulated) {
              // Open Simulated Checkout Modal
              const totalText = document.querySelector('.summary-row.total span:last-child').textContent.trim();
              if (totalValSpan) {
                totalValSpan.textContent = totalText;
              }
              const modalInstance = new bootstrap.Modal(modal);
              modalInstance.show();
            } else {
              // Open Real Razorpay Window
              const options = {
                key: data.key_id,
                amount: data.amount,
                currency: data.currency,
                name: "Vaishnavi Jewellers",
                description: "Handcrafted Luxury Jewellery Purchase",
                order_id: data.id,
                handler: function (response) {
                  paymentStatusInput.value = 'PAID';
                  
                  // Append Razorpay parameters
                  const pId = document.createElement('input');
                  pId.type = 'hidden';
                  pId.name = 'razorpay_payment_id';
                  pId.value = response.razorpay_payment_id;
                  form.appendChild(pId);

                  const oId = document.createElement('input');
                  oId.type = 'hidden';
                  oId.name = 'razorpay_order_id';
                  oId.value = response.razorpay_order_id;
                  form.appendChild(oId);

                  const sig = document.createElement('input');
                  sig.type = 'hidden';
                  sig.name = 'razorpay_signature';
                  sig.value = response.razorpay_signature;
                  form.appendChild(sig);
                  
                  form.submit();
                },
                prefill: {
                  name: (document.querySelector('input[name="first_name"]')?.value || '') + ' ' + (document.querySelector('input[name="last_name"]')?.value || ''),
                  email: document.querySelector('input[name="email"]')?.value || '',
                  contact: document.querySelector('input[name="phone"]')?.value || ''
                },
                theme: {
                  color: "#7A1F35"
                },
                modal: {
                  ondismiss: function() {
                    if (btnSubmit) {
                      btnSubmit.disabled = false;
                      btnSubmit.innerHTML = 'Place Order';
                    }
                  }
                }
              };
              if (typeof Razorpay === 'undefined') {
                throw new Error('Razorpay SDK is not loaded. Please check your internet connection or disable adblockers blocking checkout.razorpay.com.');
              }
              const rzp = new Razorpay(options);
              rzp.open();
            }
          })
          .catch(err => {
            console.error('Razorpay Error:', err);
            alert('Failed to initiate online payment: ' + err.message + '\nDetails: ' + err.stack);
            if (btnSubmit) {
              btnSubmit.disabled = false;
              btnSubmit.innerHTML = 'Place Order';
            }
          });
      }
    });
  }
  
  if (paySuccessBtn) {
    paySuccessBtn.addEventListener('click', function() {
      paymentStatusInput.value = 'PAID';
      
      const modalInstance = bootstrap.Modal.getInstance(modal);
      if (modalInstance) modalInstance.hide();
      
      form.submit();
    });
  }
  
  if (payFailBtn) {
    payFailBtn.addEventListener('click', function() {
      const modalInstance = bootstrap.Modal.getInstance(modal);
      if (modalInstance) modalInstance.hide();
      
      alert('Payment cancelled / failed. Please select a different payment option or try again.');
      if (btnSubmit) {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = 'Place Order';
      }
    });
  }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>