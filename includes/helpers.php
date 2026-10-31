<?php
declare(strict_types=1);

if (!function_exists('h')) {
  function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}

if (!function_exists('money_format')) {
  function money_format(float $n): string {
    return '₹' . number_format($n, 2, '.', ',');
  }
}

function product_catalog(): array {
  return require __DIR__ . '/products.php';
}

function find_product(array $products, string $id): ?array {
  foreach ($products as $p) {
    if (($p['id'] ?? '') === $id) return $p;
  }
  return null;
}

if (!function_exists('ensure_session')) {
  function ensure_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
      if (!headers_sent()) {
        ini_set('session.cookie_path', '/');
        ini_set('session.cookie_httponly', '1');
      }
      @session_start();
    }
  }
}

if (!function_exists('get_gold_rates')) {
  function get_gold_rates(): array {
    static $rates = null;
    if ($rates === null) {
      $rates = [];
      try {
        if (!function_exists('db')) {
          require_once __DIR__ . '/db.php';
        }
        $pdo = db();
        $rows = $pdo->query("SELECT metal_type, rate_per_gram FROM gold_rates")->fetchAll(PDO::FETCH_KEY_PAIR);
        $rates = $rows;
      } catch (Throwable $e) {
        // Ignore, fallback to defaults
      }
      $rates['gold_24k'] = (float)($rates['gold_24k'] ?? 7400.00);
      $rates['gold_22k'] = (float)($rates['gold_22k'] ?? 6800.00);
      $rates['gold_18k'] = (float)($rates['gold_18k'] ?? 5600.00);
      $rates['silver'] = (float)($rates['silver'] ?? 95.00);
    }
    return $rates;
  }
}

if (!function_exists('base_price_now')) {
  function base_price_now(array $p): float {
    $priceType = trim((string)($p['price_type'] ?? 'fixed'));
    $base = 0.0;
    
    if ($priceType === 'dynamic') {
      $rates = get_gold_rates();
      $weight = (float)($p['weight_grams'] ?? 0);
      $purity = strtolower(trim((string)($p['purity'] ?? '')));
      
      $key = '';
      if ($purity === '24k' || $purity === '22k' || $purity === '18k') {
        $key = 'gold_' . $purity;
      } else if ($purity === 'silver') {
        $key = 'silver';
      }
      
      $rate = (float)($rates[$key] ?? 0);
      if ($rate > 0) {
        $metalValue = $weight * $rate;
        $makingPercent = (float)($p['making_charge_percent'] ?? 8.00);
        $makingCharges = $metalValue * ($makingPercent / 100);
        $base = round($metalValue + $makingCharges, 2);
      } else {
        $base = (float)($p['base_price'] ?? $p['price'] ?? 0);
      }
    } else {
      $base = (float)($p['base_price'] ?? $p['price'] ?? 0);
    }
    return $base;
  }
}

if (!function_exists('price_now')) {
  function price_now(array $p): float {
    $base = base_price_now($p);
    $disc = (float)($p['discount_percent'] ?? 0);
    if ($disc > 0) {
      return round($base * (1 - ($disc / 100)), 2);
    }
    return $base;
  }
}

if (!function_exists('product_image')) {
  function product_image(?string $path): string {
    if (!$path) {
      return '';
    }
    
    $path = str_replace('\\', '/', $path);
    $rootPath = dirname(__DIR__) . '/';
    $fullPath = $rootPath . $path;
    
    if (file_exists($fullPath)) {
      return $path;
    }
    
    $pathInfo = pathinfo($path);
    $dir = $pathInfo['dirname'];
    $filename = $pathInfo['filename'];
    
    $extensions = ['webp', 'jpeg', 'jpg', 'png', 'jfif', 'PNG', 'JPG', 'JPEG', 'WEBP'];
    foreach ($extensions as $ext) {
      $testPath = $dir . '/' . $filename . '.' . $ext;
      if (file_exists($rootPath . $testPath)) {
        return $testPath;
      }
    }
    
    return $path;
  }
}

if (!function_exists('send_order_success_email')) {
  function send_order_success_email(int $orderId): bool {
    try {
      if (!function_exists('db')) {
        require_once __DIR__ . '/db.php';
      }
      if (!function_exists('send_email')) {
        require_once __DIR__ . '/auth.php';
      }
      
      $pdo = db();
      
      // Fetch order
      $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
      $stmt->execute([':id' => $orderId]);
      $order = $stmt->fetch(PDO::FETCH_ASSOC);
      if (!$order) return false;
      
      // Fetch order items
      $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
      $stmtItems->execute([':order_id' => $orderId]);
      $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
      
      $name = htmlspecialchars((string)$order['customer_name']);
      $email = htmlspecialchars((string)($order['customer_email'] ?? ''));
      $code = htmlspecialchars((string)$order['order_code']);
      $date = date('d M Y, h:i A', strtotime($order['created_at']));
      
      if (empty($email)) return false;
      
      // Build HTML items rows
      $itemsHtml = '';
      foreach ($items as $item) {
        $pName = htmlspecialchars((string)$item['product_name']);
        $sku = htmlspecialchars((string)$item['product_sku']);
        $qty = (int)$item['quantity'];
        $price = number_format((float)$item['unit_price'], 2);
        $total = number_format((float)$item['total_price'], 2);
        
        $itemsHtml .= "
        <tr>
          <td style='padding: 12px; border-bottom: 1px solid #EEEEEE;'>
            <div style='font-weight: 600; color: #333333;'>{$pName}</div>
            <div style='font-size: 11px; color: #888888;'>SKU: {$sku}</div>
          </td>
          <td style='padding: 12px; border-bottom: 1px solid #EEEEEE; text-align: center; color: #555555;'>{$qty}</td>
          <td style='padding: 12px; border-bottom: 1px solid #EEEEEE; text-align: right; color: #555555;'>₹{$price}</td>
          <td style='padding: 12px; border-bottom: 1px solid #EEEEEE; text-align: right; font-weight: 600; color: #7A1E3A;'>₹{$total}</td>
        </tr>";
      }
      
      $subtotal = number_format((float)$order['subtotal'], 2);
      $discount = number_format((float)$order['discount_amount'], 2);
      $shipping = number_format((float)$order['shipping'], 2);
      $grandTotal = number_format((float)$order['total'], 2);
      $address = nl2br(htmlspecialchars((string)$order['shipping_address']));
      $paymentMethod = htmlspecialchars((string)$order['payment_method']);
      
      $bodyHtml = "
      <!DOCTYPE html>
      <html>
      <head>
        <meta charset='UTF-8'>
        <title>Order Confirmation - #{$code}</title>
      </head>
      <body style='margin: 0; padding: 0; background-color: #FAF9F7; font-family: \"Poppins\", Arial, sans-serif; -webkit-font-smoothing: antialiased;'>
        <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background-color: #FAF9F7; padding: 40px 20px;'>
          <tr>
            <td align='center'>
              <table width='600' border='0' cellspacing='0' cellpadding='0' style='background-color: #FFFFFF; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #E8E0D6;'>
                
                <!-- HEADER BANNER -->
                <tr>
                  <td align='center' style='background: linear-gradient(135deg, #7A1E3A 0%, #2B0610 100%); padding: 35px 20px; border-bottom: 4px solid #D4AF37;'>
                    <div style='font-family: \"Playfair Display\", Georgia, serif; font-size: 28px; font-weight: 700; color: #FFFFFF; letter-spacing: 1px;'>VAISHNAVI</div>
                    <div style='font-size: 10px; color: #D4AF37; letter-spacing: 4px; font-weight: 600; margin-top: 5px; text-transform: uppercase;'>Jewellers</div>
                  </td>
                </tr>

                <!-- EMAIL CONTENT -->
                <tr>
                  <td style='padding: 40px 30px;'>
                    <div style='text-align: center; margin-bottom: 30px;'>
                      <div style='font-size: 40px; color: #D4AF37;'>✦</div>
                      <h1 style='font-family: \"Playfair Display\", Georgia, serif; font-size: 24px; color: #7A1E3A; margin: 10px 0 5px 0;'>Order Confirmed!</h1>
                      <p style='font-size: 13px; color: #888888; margin: 0;'>Thank you for choosing Vaishnavi Jewellers. Your payment has been secured.</p>
                    </div>

                    <div style='background-color: #FAF9F7; border: 1px solid #E8E0D6; border-radius: 12px; padding: 18px; margin-bottom: 30px;'>
                      <table width='100%' border='0' cellspacing='0' cellpadding='0' style='font-size: 13px; color: #555555; line-height: 1.6;'>
                        <tr>
                          <td style='font-weight: 600; color: #7A1E3A;'>Order Code:</td>
                          <td style='text-align: right; font-weight: 700;'>#{$code}</td>
                        </tr>
                        <tr>
                          <td style='font-weight: 600; color: #7A1E3A;'>Date & Time:</td>
                          <td style='text-align: right;'>{$date}</td>
                        </tr>
                        <tr>
                          <td style='font-weight: 600; color: #7A1E3A;'>Payment Method:</td>
                          <td style='text-align: right;'>{$paymentMethod} (Paid)</td>
                        </tr>
                      </table>
                    </div>

                    <!-- PRODUCTS TABLE -->
                    <h3 style='font-family: \"Playfair Display\", Georgia, serif; font-size: 16px; color: #7A1E3A; border-bottom: 2px solid #D4AF37; padding-bottom: 8px; margin-top: 0; margin-bottom: 15px;'>Items Summary</h3>
                    <table width='100%' border='0' cellspacing='0' cellpadding='0' style='font-size: 13px; border-collapse: collapse;'>
                      <thead>
                        <tr style='background-color: #FAF9F7;'>
                          <th style='padding: 10px 12px; text-align: left; font-weight: 700; color: #7A1E3A; border-bottom: 2px solid #E8E0D6;'>Item</th>
                          <th style='padding: 10px 12px; text-align: center; font-weight: 700; color: #7A1E3A; border-bottom: 2px solid #E8E0D6;'>Qty</th>
                          <th style='padding: 10px 12px; text-align: right; font-weight: 700; color: #7A1E3A; border-bottom: 2px solid #E8E0D6;'>Price</th>
                          <th style='padding: 10px 12px; text-align: right; font-weight: 700; color: #7A1E3A; border-bottom: 2px solid #E8E0D6;'>Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        {$itemsHtml}
                      </tbody>
                    </table>

                    <!-- PRICING SUMMARY -->
                    <table width='100%' border='0' cellspacing='0' cellpadding='0' style='font-size: 13px; margin-top: 20px; line-height: 1.8; color: #555555;'>
                      <tr>
                        <td style='padding-left: 12px;'>Subtotal</td>
                        <td style='text-align: right; padding-right: 12px;'>₹{$subtotal}</td>
                      </tr>
                      <tr>
                        <td style='padding-left: 12px;'>Discount</td>
                        <td style='text-align: right; padding-right: 12px; color: #16A34A;'>-₹{$discount}</td>
                      </tr>
                      <tr>
                        <td style='padding-left: 12px;'>Shipping</td>
                        <td style='text-align: right; padding-right: 12px;'>₹{$shipping}</td>
                      </tr>
                      <tr style='font-size: 16px; font-weight: 700; color: #7A1E3A;'>
                        <td style='padding: 12px 12px 0 12px; border-top: 2px solid #D4AF37;'>Grand Total</td>
                        <td style='text-align: right; padding: 12px 12px 0 12px; border-top: 2px solid #D4AF37;'>₹{$grandTotal}</td>
                      </tr>
                    </table>

                    <!-- SHIPPING DETAILS -->
                    <h3 style='font-family: \"Playfair Display\", Georgia, serif; font-size: 16px; color: #7A1E3A; border-bottom: 2px solid #D4AF37; padding-bottom: 8px; margin-top: 35px; margin-bottom: 15px;'>Shipping Address</h3>
                    <div style='background-color: #FAF9F7; border: 1px solid #E8E0D6; border-radius: 12px; padding: 18px; font-size: 13px; color: #555555; line-height: 1.6;'>
                      <strong style='color: #7A1E3A;'>{$name}</strong><br>
                      {$address}
                    </div>

                    <!-- BUTTON -->
                    <div style='text-align: center; margin-top: 40px;'>
                      <a href='http://localhost:8000/visnavi%20jwallers/pages/orders.php' style='background: linear-gradient(135deg, #7A1E3A 0%, #2B0610 100%); color: #FFFFFF; text-decoration: none; padding: 14px 30px; border-radius: 50px; font-weight: 600; font-size: 13px; display: inline-block; box-shadow: 0 4px 15px rgba(122,30,58,0.25); border: 1px solid #D4AF37;'>Track Your Order</a>
                    </div>
                  </td>
                </tr>

                <!-- FOOTER -->
                <tr>
                  <td style='background-color: #FAF9F7; border-top: 1px solid #E8E0D6; padding: 30px 20px; text-align: center;'>
                    <div style='font-size: 11px; color: #888888;'>© 2026 Vaishnavi Jewellers. Heritage & Luxury.</div>
                    <div style='font-size: 10px; color: #AAAAAA; margin-top: 5px;'>If you have any questions, reply directly to this email.</div>
                  </td>
                </tr>

              </table>
            </td>
          </tr>
        </table>
      </body>
      </html>";
      
      $bodyText = "Dear {$name},\n\nThank you for choosing Vaishnavi Jewellers. Your Order #{$code} has been confirmed successfully.\n\nGrand Total: ₹{$grandTotal}\n\nBest Regards,\nVaishnavi Jewellers";
      
      send_email($email, $name, "Order Confirmed - #{$code} - Vaishnavi Jewellers", $bodyHtml, $bodyText);
      return true;
    } catch (Throwable $e) {
      // Fail silently to not crash the payment flow
      return false;
    }
  }
}

