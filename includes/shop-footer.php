<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
?>

<!-- Related Products -->
<section class="products-section" style="background:#FAF9F7;">
  <div class="container">
    <div class="section-header">
      <h2>You May Also Like</h2>
      <p>Discover more exquisite pieces from our collection</p>
    </div>
    <div class="products-grid">
      <?php for ($i = 1; $i <= 4; $i++): ?>
      <div class="product-card">
        <div class="product-image">
          <img src="https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=400" alt="Product">
          <div class="product-badges">
            <span class="product-badge badge-new">New</span>
          </div>
          <button class="product-wishlist"><i class="far fa-heart"></i></button>
        </div>
        <div class="product-info">
          <p class="product-category">Diamond</p>
          <h3 class="product-name"><a href="#">Elegant Diamond Pendant</a></h3>
          <div class="product-rating">
            <div class="rating-stars">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star-half-alt"></i>
            </div>
            <span class="rating-count">(24)</span>
          </div>
          <div class="product-price">
            <span class="current-price">₹45,000</span>
            <span class="original-price">₹55,000</span>
            <span class="discount-percent">18% Off</span>
          </div>
        </div>
      </div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<!-- Newsletter -->
<section class="newsletter-section">
  <div class="container">
    <div class="newsletter-grid">
      <div>
        <h2 class="newsletter-title">Subscribe to Our Newsletter</h2>
        <p class="newsletter-description">Get the latest updates on new collections and exclusive offers.</p>
      </div>
      <form class="newsletter-form" id="frmNewsletter">
        <input type="email" id="txtNewsletterEmail" class="newsletter-input" placeholder="Enter your email" required>
        <button type="submit" class="newsletter-btn" id="btnNewsletterSubmit">Subscribe</button>
      </form>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="premium-footer">
  <!-- Gold Rate Ticker -->
  <div class="gold-rate-ticker">
    <div class="container">
      <div class="ticker-content">
        <div class="ticker-item">
          <span class="ticker-label">24K Gold</span>
          <div class="ticker-value">
            <strong>₹72,500</strong>
            <span class="ticker-change up"><i class="fas fa-arrow-up"></i> 0.5%</span>
          </div>
        </div>
        <div class="ticker-item">
          <span class="ticker-label">22K Gold</span>
          <div class="ticker-value">
            <strong>₹66,500</strong>
            <span class="ticker-change up"><i class="fas fa-arrow-up"></i> 0.5%</span>
          </div>
        </div>
        <div class="ticker-item">
          <span class="ticker-label">18K Gold</span>
          <div class="ticker-value">
            <strong>₹54,500</strong>
            <span class="ticker-change down"><i class="fas fa-arrow-down"></i> 0.2%</span>
          </div>
        </div>
        <div class="ticker-item">
          <span class="ticker-label">Silver</span>
          <div class="ticker-value">
            <strong>₹985</strong>
            <span class="ticker-change up"><i class="fas fa-arrow-up"></i> 0.3%</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Footer -->
  <div class="footer-main">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="footer-logo">
          <img src="assets/images/logo.svg" alt="Vaishnavi Jewellers">
        </div>
        <p class="footer-description">Discover exquisite jewellery crafted with passion since 1990. Our legacy of excellence continues with every piece.</p>
        <div class="footer-social">
          <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
          <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
          <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
        </div>
      </div>

      <div class="footer-column">
        <h4>Shop</h4>
        <ul class="footer-links">
          <li><a href="#">Gold Jewellery</a></li>
          <li><a href="#">Diamond Jewellery</a></li>
          <li><a href="#">Engagement Rings</a></li>
          <li><a href="#">Wedding Collection</a></li>
          <li><a href="#">Gold Coins</a></li>
          <li><a href="#">New Arrivals</a></li>
        </ul>
      </div>

      <div class="footer-column">
        <h4>Help</h4>
        <ul class="footer-links">
          <li><a href="#">Contact Us</a></li>
          <li><a href="#">FAQs</a></li>
          <li><a href="#">Shipping Policy</a></li>
          <li><a href="#">Returns & Exchange</a></li>
          <li><a href="#">Jewellery Care</a></li>
          <li><a href="#">Store Locator</a></li>
        </ul>
      </div>

      <div class="footer-column">
        <h4>Company</h4>
        <ul class="footer-links">
          <li><a href="#">About Us</a></li>
          <li><a href="#">Our Heritage</a></li>
          <li><a href="#">Careers</a></li>
          <li><a href="#">Press</a></li>
          <li><a href="#">Sustainability</a></li>
          <li><a href="#">Investor Relations</a></li>
        </ul>
      </div>

      <div class="footer-column footer-newsletter">
        <h4>Download App</h4>
        <p>Experience shopping on the go with our mobile app.</p>
        <div class="app-download">
          <a href="#" class="app-btn">
            <i class="fab fa-apple"></i>
            <div class="app-btn-text">
              <span>Download on</span>
              <span>App Store</span>
            </div>
          </a>
          <a href="#" class="app-btn">
            <i class="fab fa-google-play"></i>
            <div class="app-btn-text">
              <span>Get it on</span>
              <span>Google Play</span>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bottom Bar -->
  <div class="container">
    <div class="footer-bottom">
      <p class="copyright">&copy; <?= date('Y') ?> Vaishnavi Jewellers. All rights reserved.</p>
      <div class="payment-methods">
        <span>We Accept</span>
        <div class="payment-icons">
          <div class="payment-icon"><i class="fab fa-cc-visa"></i></div>
          <div class="payment-icon"><i class="fab fa-cc-mastercard"></i></div>
          <div class="payment-icon"><i class="fab fa-cc-amex"></i></div>
          <div class="payment-icon"><i class="fab fa-cc-paypal"></i></div>
          <div class="payment-icon"><i class="fas fafa">UPI</i></div>
        </div>
      </div>
    </div>

    <div class="quick-links">
      <a href="#">Privacy Policy</a>
      <a href="#">Terms of Service</a>
      <a href="#">Cookie Policy</a>
    </div>
  </div>
</footer>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="assets/js/premium-header.js"></script>
<script src="assets/js/app.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const newsForm = document.getElementById('frmNewsletter');
  if (newsForm) {
    newsForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const emailInput = document.getElementById('txtNewsletterEmail');
      const submitBtn = document.getElementById('btnNewsletterSubmit');
      
      if (!emailInput || !emailInput.value.trim()) return;
      
      const email = emailInput.value.trim();
      
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';
      submitBtn.disabled = true;
      
      let apiUrl = 'api/subscribe.php';
      const path = window.location.pathname;
      if (path.includes('/visnavi%20jwallers/visnavi%20jwallers/') || path.includes('/visnavi jwallers/visnavi jwallers/')) {
        apiUrl = '/visnavi%20jwallers/visnavi%20jwallers/api/subscribe.php';
      } else if (path.includes('/visnavi%20jwallers/') || path.includes('/visnavi jwallers/')) {
        apiUrl = '/visnavi%20jwallers/api/subscribe.php';
      } else if (path.includes('/pages/')) {
        apiUrl = '../api/subscribe.php';
      }

      fetch(apiUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email: email })
      })
      .then(res => res.json())
      .then(data => {
        submitBtn.innerHTML = 'Subscribe';
        submitBtn.disabled = false;
        
        if (data.success) {
          showLuxuryToast('Success', 'You have successfully subscribed. We\'ve sent a welcome member preview email.', 'success');
          emailInput.value = '';
        } else {
          showLuxuryToast('Error', data.message || 'Subscription failed. Please try again.', 'error');
        }
      })
      .catch(err => {
        submitBtn.innerHTML = 'Subscribe';
        submitBtn.disabled = false;
        console.error(err);
        showLuxuryToast('Error', 'Connection error. Please try again.', 'error');
      });
    });
  }
});

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
</script>
</body>
</html>