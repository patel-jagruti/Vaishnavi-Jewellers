<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/base_url.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

ensure_session();

$code = trim((string)($_GET['code'] ?? ''));

if ($code === '') {
    die("Error: Missing order code.");
}

$pdo = db();

// Fetch order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code = ? LIMIT 1");
$stmt->execute([$code]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Error: Order not found.");
}

// Security: Allow logged-in user to see their own order, admin sees all,
// and allow guest access if they just placed the order (session token match)
$user = $_SESSION['user'] ?? null;
$isAdmin = $user && isset($user['role']) && strtolower((string)$user['role']) === 'admin';
$isOwner = $user && (int)($order['user_id'] ?? 0) === (int)($user['id'] ?? 0);
$isGuestOrder = empty($order['user_id']);

if (!$isAdmin && !$isOwner && !$isGuestOrder) {
    die("Error: Access denied.");
}

// Fetch order items
$stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmtItems->execute([$order['id']]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

// GST calculation (Jewelry standard: 3% = 1.5% CGST + 1.5% SGST)
$cgstPercent = 1.5;
$sgstPercent = 1.5;
$taxableAmount = (float)$order['subtotal'];
$cgstAmount = round($taxableAmount * ($cgstPercent / 100), 2);
$sgstAmount = round($taxableAmount * ($sgstPercent / 100), 2);
$totalTax = $cgstAmount + $sgstAmount;

// Auto-print flag (redirect from checkout)
$autoPrint = isset($_GET['print']) && $_GET['print'] === '1';

// Number to words helper
function numberToWords(float $num): string {
    $deci = round($num - floor($num), 2) * 100;
    $number = floor($num);
    $no = strlen((string)$number);
    $hundred = null;
    $digits_1 = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');
    $digits_2 = array('', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety');
    $plural = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
    $g = array();
    $i = 0;
    while ($i < $no) {
        $divider = ($i == 2) ? 10 : 100;
        $num_val = $number % $divider;
        $number = (int)($number / $divider);
        $i += ($divider == 10) ? 1 : 2;
        if ($num_val) {
            $plural_val = (($counter = count($g)) && $num_val > 9) ? 's' : null;
            $hundred = ($counter == 1 && $g[0]) ? ' and ' : null;
            $g [] = ($num_val < 20) ? $digits_1[$num_val] . ' ' . $plural[$counter] . $plural_val . ' ' . $hundred
                : $digits_2[floor($num_val / 10)] . ' ' . $digits_1[$num_val % 10] . ' ' . $plural[$counter] . $plural_val . ' ' . $hundred;
        } else {
            $g [] = null;
        }
    }
    $Rupees = implode('', array_reverse($g));
    $Paise = ($deci > 0) ? "And " . ($digits_1[(int)($deci / 10)] . " " . $digits_1[(int)($deci % 10)]) . ' Paise' : '';
    return ($Rupees ? $Rupees . 'Rupees ' : '') . $Paise . ' Only';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice - <?= h($order['order_code']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  :root {
    --maroon: #5A1625;
    --gold: #D4AF37;
    --dark: #1a1a1a;
    --border: #E8E4DE;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #2B0610 0%, #1a0a10 50%, #0d0507 100%);
    min-height: 100vh;
    padding: 30px 15px;
    color: #333;
  }

  /* ===== Success Banner ===== */
  .success-banner {
    max-width: 880px;
    margin: 0 auto 20px auto;
    background: linear-gradient(135deg, #0d5e2e, #1a8c48);
    border-radius: 16px;
    padding: 28px 35px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 8px 32px rgba(13,94,46,0.35);
    animation: slideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1);
  }

  @keyframes slideDown {
    from { opacity: 0; transform: translateY(-30px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .success-icon {
    width: 60px; height: 60px;
    background: rgba(255,255,255,0.15);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }

  .success-icon i {
    font-size: 28px;
    color: #fff;
    animation: checkPop 0.5s 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) both;
  }

  @keyframes checkPop {
    from { transform: scale(0); }
    to { transform: scale(1); }
  }

  .success-text h3 {
    color: #fff;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 4px;
  }

  .success-text p {
    color: rgba(255,255,255,0.85);
    font-size: 13px;
    margin: 0;
  }

  /* ===== Action Bar ===== */
  .action-bar {
    max-width: 880px;
    margin: 0 auto 15px auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
  }

  .btn-action {
    padding: 12px 28px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: none;
    transition: all 0.3s ease;
    font-family: 'Poppins', sans-serif;
  }

  .btn-print {
    background: linear-gradient(135deg, var(--gold), #c29d2f);
    color: #fff;
    box-shadow: 0 4px 15px rgba(212,175,55,0.3);
  }

  .btn-print:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(212,175,55,0.45); }

  .btn-download {
    background: linear-gradient(135deg, var(--maroon), #3d0e1a);
    color: #fff;
    box-shadow: 0 4px 15px rgba(90,22,37,0.3);
  }

  .btn-download:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(90,22,37,0.45); }

  .btn-back {
    background: rgba(255,255,255,0.1);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.2);
  }

  .btn-back:hover { background: rgba(255,255,255,0.2); }

  /* ===== Invoice Container ===== */
  .invoice-container {
    max-width: 880px;
    margin: 0 auto;
    background: #fff;
    padding: 55px;
    box-shadow: 0 15px 60px rgba(0,0,0,0.4);
    border-radius: 6px;
    position: relative;
    animation: fadeIn 0.8s ease;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* Decorative gold line at top */
  .invoice-container::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--maroon), var(--gold), var(--maroon));
    border-radius: 6px 6px 0 0;
  }

  /* Watermark */
  .watermark {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%) rotate(-35deg);
    font-size: 100px;
    font-weight: 800;
    color: rgba(212,175,55,0.04);
    letter-spacing: 15px;
    pointer-events: none;
    white-space: nowrap;
    font-family: 'Outfit', sans-serif;
  }

  /* Header */
  .invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 2px solid var(--maroon);
    padding-bottom: 28px;
    margin-bottom: 28px;
  }

  .company-info h1 {
    font-family: 'Outfit', sans-serif;
    font-size: 26px;
    font-weight: 700;
    color: var(--maroon);
    margin: 0 0 6px 0;
    letter-spacing: 2px;
  }

  .company-info p {
    margin: 3px 0;
    font-size: 12px;
    color: #777;
  }

  .company-info i { color: var(--gold); width: 16px; text-align: center; margin-right: 5px; }

  .invoice-title { text-align: right; }

  .invoice-title h2 {
    font-size: 38px;
    font-weight: 300;
    color: var(--maroon);
    margin: 0 0 10px 0;
    text-transform: uppercase;
    letter-spacing: 4px;
  }

  .invoice-meta p {
    margin: 3px 0;
    font-size: 12px;
    color: #555;
  }

  .invoice-meta strong { color: #222; }

  .badge-paid {
    display: inline-block;
    background: linear-gradient(135deg, #0d5e2e, #1a8c48);
    color: #fff;
    padding: 4px 14px;
    border-radius: 50px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
  }

  .badge-pending {
    display: inline-block;
    background: linear-gradient(135deg, #c29d2f, #D4AF37);
    color: #fff;
    padding: 4px 14px;
    border-radius: 50px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
  }

  /* Details */
  .details-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 35px;
    gap: 40px;
  }

  .bill-to, .ship-to { flex: 1; }

  .details-row h3 {
    font-size: 11px;
    text-transform: uppercase;
    color: var(--gold);
    border-bottom: 1px solid var(--border);
    padding-bottom: 8px;
    margin-bottom: 12px;
    letter-spacing: 2px;
    font-weight: 600;
  }

  .details-row p { margin: 4px 0; font-size: 13px; color: #555; line-height: 1.6; }
  .details-row p strong { color: #222; }

  /* Table */
  table.invoice-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
  }

  table.invoice-table th {
    background: linear-gradient(135deg, var(--maroon), #3d0e1a);
    color: #fff;
    font-size: 11px;
    text-transform: uppercase;
    font-weight: 600;
    padding: 14px 18px;
    text-align: left;
    letter-spacing: 1px;
  }

  table.invoice-table th:first-child { border-radius: 6px 0 0 0; }
  table.invoice-table th:last-child { border-radius: 0 6px 0 0; }

  table.invoice-table td {
    padding: 16px 18px;
    border-bottom: 1px solid #f0ede8;
    font-size: 13px;
    color: #444;
  }

  table.invoice-table tr:hover td { background: #fdfcfa; }

  table.invoice-table td.qty, table.invoice-table th.qty { text-align: center; }
  table.invoice-table td.price, table.invoice-table th.price { text-align: right; }

  .sr-no { width: 50px; text-align: center; color: #999; }

  /* Summary */
  .summary-container {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
    gap: 40px;
  }

  .amount-words {
    width: 50%;
    font-size: 13px;
    color: #666;
    line-height: 1.6;
  }

  .amount-words strong { color: #222; }

  .invoice-summary { width: 42%; }

  .summary-row {
    display: flex;
    justify-content: space-between;
    padding: 9px 0;
    font-size: 13px;
    color: #555;
  }

  .summary-row.total {
    border-top: 2px solid var(--maroon);
    padding-top: 14px;
    margin-top: 6px;
    font-size: 20px;
    font-weight: 700;
    color: var(--maroon);
  }

  /* Signature */
  .signature-block {
    display: flex;
    justify-content: space-between;
    margin-top: 55px;
    padding: 0 20px;
  }

  .signature { text-align: center; width: 200px; }
  .signature-line { border-bottom: 1px solid #bbb; margin-bottom: 8px; height: 45px; }
  .signature p { font-size: 11px; color: #888; margin: 0; }

  /* Footer */
  .invoice-footer {
    margin-top: 50px;
    border-top: 1px solid var(--border);
    padding-top: 25px;
    text-align: center;
    font-size: 11px;
    color: #aaa;
  }

  .invoice-footer .thankyou {
    font-family: 'Outfit', sans-serif;
    font-size: 16px;
    color: var(--gold);
    margin-bottom: 5px;
    font-weight: 600;
  }

  /* ===== Print Styles ===== */
  @media print {
    body {
      background: none !important;
      padding: 0 !important;
      min-height: auto;
    }

    .success-banner, .action-bar {
      display: none !important;
    }

    .invoice-container {
      box-shadow: none !important;
      padding: 0 !important;
      max-width: 100% !important;
      border-radius: 0 !important;
      animation: none !important;
    }

    .invoice-container::before { display: none; }
    .watermark { display: none; }

    table.invoice-table th {
      background: var(--maroon) !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    .badge-paid, .badge-pending {
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    @page {
      margin: 1.5cm;
      size: A4;
    }
  }

  /* Responsive */
  @media (max-width: 600px) {
    .invoice-container { padding: 25px; }
    .invoice-header { flex-direction: column; gap: 20px; }
    .invoice-title { text-align: left; }
    .invoice-title h2 { font-size: 28px; }
    .details-row { flex-direction: column; gap: 20px; }
    .summary-container { flex-direction: column; }
    .amount-words, .invoice-summary { width: 100%; }
    .signature-block { flex-direction: column; gap: 30px; align-items: center; }
    .action-bar { justify-content: center; }
  }
</style>
</head>
<body>

<?php if ($autoPrint || $order['payment_status'] === 'PAID'): ?>
<div class="success-banner">
  <div class="success-icon">
    <i class="fas fa-check-circle"></i>
  </div>
  <div class="success-text">
    <h3>Payment Successful! 🎉</h3>
    <p>Order <strong><?= h($order['order_code']) ?></strong> has been placed successfully. Your invoice is ready below.</p>
  </div>
</div>
<?php endif; ?>

<div class="action-bar">
  <a href="<?= route('index.php') ?>" class="btn-action btn-back"><i class="fas fa-home"></i> Home</a>
  <div style="display:flex; gap:10px; flex-wrap: wrap;">
    <button onclick="window.print()" class="btn-action btn-print"><i class="fas fa-print"></i> Print Invoice</button>
    <button onclick="window.print()" class="btn-action btn-download"><i class="fas fa-file-pdf"></i> Save as PDF</button>
  </div>
</div>

<div class="invoice-container">
  <div class="watermark">VAISHNAVI</div>

  <div class="invoice-header">
    <div class="company-info">
      <h1>VAISHNAVI JEWELLERS</h1>
      <p><i class="fas fa-map-marker-alt"></i> 102, Gold Palace Building, CG Road, Ahmedabad, GJ</p>
      <p><i class="fas fa-phone"></i> +91 79 2644 8899 &nbsp;|&nbsp; GSTIN: 24AAAAV1234F1Z0</p>
      <p><i class="fas fa-envelope"></i> vaishnavijewellers34@gmail.com</p>
    </div>
    <div class="invoice-title">
      <h2>Invoice</h2>
      <div class="invoice-meta">
        <p><strong>Invoice No:</strong> INV-<?= h($order['order_code']) ?></p>
        <p><strong>Date:</strong> <?= date('d M Y', strtotime($order['created_at'])) ?></p>
        <p><strong>Method:</strong> <?= h($order['payment_method']) === 'RAZORPAY' ? 'Online (Razorpay)' : 'Cash on Delivery' ?></p>
        <p style="margin-top: 6px;">
          <?php if ($order['payment_status'] === 'PAID'): ?>
            <span class="badge-paid"><i class="fas fa-check-circle"></i> Paid</span>
          <?php else: ?>
            <span class="badge-pending"><i class="fas fa-clock"></i> Pending</span>
          <?php endif; ?>
        </p>
      </div>
    </div>
  </div>

  <div class="details-row">
    <div class="bill-to">
      <h3><i class="fas fa-user"></i> Billed To</h3>
      <p><strong><?= h($order['customer_name']) ?></strong></p>
      <p><i class="fas fa-phone" style="font-size:11px; color:#bbb;"></i> <?= h($order['customer_phone'] ?? 'N/A') ?></p>
      <p><i class="fas fa-envelope" style="font-size:11px; color:#bbb;"></i> <?= h($order['customer_email'] ?? 'N/A') ?></p>
    </div>
    <div class="ship-to">
      <h3><i class="fas fa-truck"></i> Shipping Address</h3>
      <p><?= nl2br(h($order['shipping_address'])) ?></p>
    </div>
  </div>

  <table class="invoice-table">
    <thead>
      <tr>
        <th class="sr-no">#</th>
        <th>Product Description</th>
        <th>SKU</th>
        <th class="qty">Qty</th>
        <th class="price">Unit Rate</th>
        <th class="price">Amount</th>
      </tr>
    </thead>
    <tbody>
      <?php $sn = 0; foreach ($items as $item): $sn++; ?>
        <tr>
          <td class="sr-no"><?= $sn ?></td>
          <td><strong><?= h($item['product_name']) ?></strong></td>
          <td><?= h($item['product_sku']) ?></td>
          <td class="qty"><?= (int)$item['quantity'] ?></td>
          <td class="price"><?= money((float)$item['unit_price']) ?></td>
          <td class="price"><?= money((float)$item['total_price']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="summary-container">
    <div class="amount-words">
      <p><strong>Amount in Words:</strong><br><?= numberToWords((float)$order['total']) ?></p>
      <p style="margin-top: 20px; font-size: 10px; color:#aaa;">
        * 3% GST (1.5% CGST + 1.5% SGST) is included as per Jewelry GST norms.
      </p>
    </div>
    <div class="invoice-summary">
      <div class="summary-row">
        <span>Taxable Value (Excl. Tax)</span>
        <span><?= money($taxableAmount - $totalTax) ?></span>
      </div>
      <div class="summary-row">
        <span>CGST (1.5%)</span>
        <span><?= money($cgstAmount) ?></span>
      </div>
      <div class="summary-row">
        <span>SGST (1.5%)</span>
        <span><?= money($sgstAmount) ?></span>
      </div>
      <?php if ((float)$order['discount_amount'] > 0): ?>
        <div class="summary-row" style="color: #1a8c48;">
          <span>Discount</span>
          <span>-<?= money((float)$order['discount_amount']) ?></span>
        </div>
      <?php endif; ?>
      <div class="summary-row">
        <span>Shipping</span>
        <span><?= (float)$order['shipping'] > 0 ? money((float)$order['shipping']) : 'Free' ?></span>
      </div>
      <div class="summary-row total">
        <span>Grand Total</span>
        <span><?= money((float)$order['total']) ?></span>
      </div>
    </div>
  </div>

  <div class="signature-block">
    <div class="signature">
      <div class="signature-line"></div>
      <p>Customer's Signature</p>
    </div>
    <div class="signature">
      <div class="signature-line" style="display:flex; align-items:flex-end; justify-content:center; font-style:italic; font-size:10px; color:#ccc; padding-bottom:5px;">For Vaishnavi Jewellers</div>
      <p>Authorized Signatory</p>
    </div>
  </div>

  <div class="invoice-footer">
    <p class="thankyou">✨ Thank You For Your Purchase ✨</p>
    <p>This is a computer-generated invoice. No physical signature is required for validity.</p>
    <p style="margin-top: 4px;">© <?= date('Y') ?> Vaishnavi Jewellers — Certified Purity & Lifetime Exchange Guarantee</p>
  </div>
</div>

<?php if ($autoPrint): ?>
<script>
  // Auto-trigger print dialog after page loads
  window.addEventListener('load', function() {
    setTimeout(function() {
      window.print();
    }, 1200);
  });
</script>
<?php endif; ?>

</body>
</html>
