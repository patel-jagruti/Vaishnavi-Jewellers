<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/base_url.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

ensure_session();

$pageTitle = 'Frequently Asked Questions | Vaishnavi Jewellers';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
.faq-hero {
  background: linear-gradient(135deg, #5A1626 0%, #7A1F2A 100%);
  color: #fff;
  padding: 80px 0;
  text-align: center;
}

.faq-hero h1 {
  font-family: 'Playfair Display', serif;
  font-size: 48px;
  font-weight: 700;
  margin-bottom: 16px;
  letter-spacing: 1px;
}

.faq-hero p {
  font-size: 16px;
  opacity: 0.9;
  max-width: 600px;
  margin: 0 auto;
}

.faq-content {
  background: #FAF8F6;
  padding: 60px 0;
}

.faq-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 20px;
}

.faq-card {
  background: #fff;
  border-radius: 16px;
  margin-bottom: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.02);
  border: 1px solid #F0ECE7;
  overflow: hidden;
  transition: all 0.3s ease;
}

.faq-header {
  padding: 24px;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  user-select: none;
  background: #fff;
}

.faq-header h3 {
  font-family: 'Playfair Display', serif;
  font-size: 18px;
  font-weight: 600;
  color: #222;
  margin: 0;
  transition: color 0.3s ease;
}

.faq-toggle {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #FAF8F6;
  color: #5A1626;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  font-size: 14px;
}

.faq-body {
  padding: 0 24px;
  max-height: 0;
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0, 1, 0, 1);
}

.faq-body p {
  color: #666;
  font-size: 15px;
  line-height: 1.6;
  margin: 0;
  padding-bottom: 24px;
}

/* Active State */
.faq-card.active {
  box-shadow: 0 10px 30px rgba(90, 22, 38, 0.05);
  border-color: #E2D9D2;
}

.faq-card.active .faq-header h3 {
  color: #5A1626;
}

.faq-card.active .faq-toggle {
  background: #5A1626;
  color: #fff;
  transform: rotate(45deg);
}

.faq-card.active .faq-body {
  max-height: 1000px;
  transition: all 0.3s cubic-bezier(1, 0, 1, 0);
}

@media (max-width: 768px) {
  .faq-hero h1 {
    font-size: 36px;
  }
  .faq-header h3 {
    font-size: 16px;
  }
}
</style>

<div class="faq-hero">
  <div class="container">
    <h1>Frequently Asked Questions</h1>
    <p>Find answers to common queries about our premium jewellery products, delivery details, and service policies.</p>
  </div>
</div>

<div class="faq-content">
  <div class="faq-container">

    <div class="faq-card">
      <div class="faq-header">
        <h3>What is BIS Hallmark?</h3>
        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
      </div>
      <div class="faq-body">
        <p>BIS Hallmark is the government-approved hallmarking system for gold and silver jewellery in India. It guarantees the absolute purity of the metal (e.g., 22K or 18K) as per Indian Standard specifications. Every single ornament sold by Vaishnavi Jewellers carries a certified BIS Hallmark laser logo for 100% guarantee.</p>
      </div>
    </div>

    <div class="faq-card">
      <div class="faq-header">
        <h3>Do you offer a lifetime exchange policy?</h3>
        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
      </div>
      <div class="faq-body">
        <p>Yes! We offer a transparent Lifetime Exchange Policy on all gold and diamond jewellery purchases. You can exchange your ornament at full market rate (minus making charges and taxes) based on the current metal weight and karat purity at any of our store locations.</p>
      </div>
    </div>

    <div class="faq-card">
      <div class="faq-header">
        <h3>How long does order delivery take?</h3>
        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
      </div>
      <div class="faq-body">
        <p>Standard delivery generally takes 5-7 business days across India. Express courier services take 2-3 business days. If you need same-day courier dispatch in selected metropolitan zones, feel free to choose our Same Day Shipping option during checkout.</p>
      </div>
    </div>

    <div class="faq-card">
      <div class="faq-header">
        <h3>Are purchase EMI schemes available?</h3>
        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
      </div>
      <div class="faq-body">
        <p>Yes, we support card-based and cardless 0% EMI financing plans for 3, 6, or 9 months. To avail of EMI benefits, choose your preferred payment option at checkout, or get in touch with our support representatives.</p>
      </div>
    </div>

    <div class="faq-card">
      <div class="faq-header">
        <h3>What is your return policy?</h3>
        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
      </div>
      <div class="faq-body">
        <p>We provide a 30-day, no-questions-asked refund or exchange on all unused items returned in their original luxury box packing along with our authentic invoice card. Custom and engraved designs are not eligible for standard returns.</p>
      </div>
    </div>

    <div class="faq-card">
      <div class="faq-header">
        <h3>Do you ship orders internationally?</h3>
        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
      </div>
      <div class="faq-body">
        <p>Yes, we ship to selected international destinations including the US, UK, and UAE. International shipments require specific custom clearances and take between 10-15 business days. Additional delivery tariffs may apply.</p>
      </div>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const cards = document.querySelectorAll('.faq-card');
  cards.forEach(card => {
    const header = card.querySelector('.faq-header');
    header.addEventListener('click', () => {
      // Toggle active class
      card.classList.toggle('active');
    });
  });
});
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
