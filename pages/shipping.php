<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/base_url.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

ensure_session();

$pageTitle = 'Shipping Policy | Vaishnavi Jewellers';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
.shipping-hero {
  background: linear-gradient(135deg, #5A1626 0%, #7A1F2A 100%);
  color: #fff;
  padding: 80px 0;
  text-align: center;
}

.shipping-hero h1 {
  font-family: 'Playfair Display', serif;
  font-size: 48px;
  font-weight: 700;
  margin-bottom: 16px;
  letter-spacing: 1px;
}

.shipping-hero p {
  font-size: 16px;
  opacity: 0.9;
  max-width: 600px;
  margin: 0 auto;
}

.shipping-content {
  background: #FAF8F6;
  padding: 60px 0;
}

.shipping-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 30px;
  max-width: 1000px;
  margin: 0 auto;
  padding: 0 20px;
}

.shipping-card {
  background: #fff;
  border-radius: 20px;
  padding: 35px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.03);
  transition: all 0.3s ease;
  border: 1px solid #F0ECE7;
}

.shipping-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 40px rgba(0,0,0,0.06);
}

.shipping-card h3 {
  font-family: 'Playfair Display', serif;
  font-size: 22px;
  color: #5A1626;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.shipping-card h3 i {
  color: #D4AF37;
  font-size: 20px;
}

.shipping-card p {
  color: #666;
  font-size: 15px;
  line-height: 1.6;
  margin-bottom: 12px;
}

.shipping-card ul {
  padding-left: 20px;
  margin-top: 10px;
}

.shipping-card li {
  color: #666;
  font-size: 14px;
  margin-bottom: 8px;
  line-height: 1.5;
}

@media (max-width: 768px) {
  .shipping-grid {
    grid-template-columns: 1fr;
  }
  .shipping-hero h1 {
    font-size: 36px;
  }
}
</style>

<div class="shipping-hero">
  <div class="container">
    <h1>Shipping & Delivery Policy</h1>
    <p>We ensure fully insured, secure, and prompt delivery of your precious ornaments right to your doorstep.</p>
  </div>
</div>

<div class="shipping-content">
  <div class="shipping-grid">
    
    <div class="shipping-card">
      <h3><i class="fas fa-shipping-fast"></i> Delivery Options</h3>
      <p>Choose from our multiple shipping modes configured for your convenience:</p>
      <ul>
        <li><strong>Standard Delivery:</strong> 5-7 business days. Completely free on orders above ₹50,000.</li>
        <li><strong>Express Delivery:</strong> 2-3 business days. Flat rate of ₹500.</li>
        <li><strong>Same Day Delivery:</strong> Available in select metro cities. Flat rate of ₹1,000 (Orders must be placed before 12:00 PM).</li>
      </ul>
    </div>

    <div class="shipping-card">
      <h3><i class="fas fa-shield-alt"></i> Insured Transit</h3>
      <p>Every piece of jewellery shipped from Vaishnavi Jewellers is 100% insured against loss or damage during transit.</p>
      <p>To ensure maximum safety, your shipment is packed under high-security CCTV cameras in tamper-proof packages.</p>
      <p>Please do not accept packages that look opened, damaged, or tampered with.</p>
    </div>

    <div class="shipping-card">
      <h3><i class="fas fa-map-marker-alt"></i> Order Tracking</h3>
      <p>Stay informed about your shipment's journey:</p>
      <p>As soon as your package leaves our fulfillment center, we will send you a confirmation message via SMS and Email containing your tracking number and a live tracking link.</p>
      <p>You can also track status under your User Profile Orders page.</p>
    </div>

    <div class="shipping-card">
      <h3><i class="fas fa-undo"></i> Easy Return Policy</h3>
      <p>We offer a hassle-free 30-day return policy on all unused products in their original packaging condition.</p>
      <p>Our customer service team will schedule a secure pickup at your convenience, and your return will be processed upon successful inspection.</p>
    </div>

  </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
