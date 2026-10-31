<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<base href="/visnavi jwallers/">
<title>Store Locator - Vaishnavi Jewellers</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Poppins:wght@300;400;500;600&display=swap">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>
.main-content {
  display: none !important;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Poppins', sans-serif; background: #fafafa; }
a { text-decoration: none; color: inherit; }
.container { max-width: 1400px; margin: 0 auto; padding: 0 20px; }
.page-header { background: #fff; padding: 20px 0; border-bottom: 1px solid #eee; }
.page-title { font-family: 'Cormorant Garamond', serif; font-size: 32px; color: #333; }
.breadcrumb { font-size: 13px; color: #888; margin-top: 8px; }
.breadcrumb a { color: #a52a2a; }
.store-grid { display: grid; grid-template-columns: 1fr 400px; gap: 40px; padding: 40px 0; }
.store-list { display: flex; flex-direction: column; gap: 20px; }
.store-card { background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); cursor: pointer; transition: .3s; }
.store-card:hover, .store-card.active { border: 2px solid #a52a2a; }
.store-name { font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 600; color: #333; margin-bottom: 8px; }
.store-address { color: #555; font-size: 14px; line-height: 1.6; margin-bottom: 10px; }
.store-phone { font-size: 14px; color: #888; margin-bottom: 8px; }
.store-hours { font-size: 13px; color: #666; }
.store-badge { display: inline-block; background: #d4edda; color: #155724; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-top: 10px; }
.book-section { background: #fff; border-radius: 12px; padding: 30px; height: fit-content; }
.book-title { font-family: 'Cormorant Garamond', serif; font-size: 24px; margin-bottom: 20px; color: #333; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-weight: 500; margin-bottom: 8px; color: #333; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
.form-group textarea { min-height: 100px; resize: vertical; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #a52a2a; }
.btn-book { background: #a52a2a; color: #fff; padding: 14px; border: none; border-radius: 8px; width: 100%; font-size: 16px; font-weight: 600; cursor: pointer; }
.btn-book:hover { background: #8a2020; }
.cities-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 40px; }
.city-card { background: #fff; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.06); cursor: pointer; transition: .3s; }
.city-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
.city-icon { font-size: 30px; margin-bottom: 10px; }
.city-name { font-weight: 600; color: #333; }
.city-count { font-size: 13px; color: #888; }
@media (max-width: 992px) { .store-grid { grid-template-columns: 1fr; } .cities-grid { grid-template-columns: repeat(2, 1fr); } }
</style>
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="page-header">
<div class="container">
<h1 class="page-title">Store Locator</h1>
<div class="breadcrumb"><a href="/">Home</a> / Store</div>
</div>
</div>

<div class="container">
<h2 class="page-title" style="font-size:28px;margin:40px 0 20px;">Find a Store Near You</h2>

<div class="cities-grid">
<div class="city-card"><div class="city-icon">🏙️</div><div class="city-name">Vadodara</div><div class="city-count">4 Stores</div></div>
<div class="city-card"><div class="city-icon">🏙️</div><div class="city-name">Ahmedabad</div><div class="city-count">2 Stores</div></div>
<div class="city-card"><div class="city-icon">🏙️</div><div class="city-name">Surat</div><div class="city-count">1 Store</div></div>
<div class="city-card"><div class="city-icon">🏙️</div><div class="city-name">Rajkot</div><div class="city-count">3 Stores</div></div>
</div>

<div class="store-grid">
<div class="store-list">
<!-- Vadodara Stores (Main) -->
<div class="store-card active">
<div class="store-name">Vaishnavi Jewellers - Alkapuri</div>
<div class="store-address">K-12, Alkapuri Arcade, Vadodara<br>Vadodara - 390007</div>
<div class="store-phone">📞 +91 265 2345 6792</div>
<div class="store-hours"><strong>Hours:</strong> Mon-Sat: 10AM-8PM</div>
</div>

<div class="store-card">
<div class="store-name">Vaishnavi Jewellers - Mandvi</div>
<div class="store-address">Shop 22, Mandvi Main Market, Vadodara<br>Vadodara - 390002</div>
<div class="store-phone">📞 +91 265 2345 6793</div>
<div class="store-hours"><strong>Hours:</strong> Mon-Sat: 10AM-8PM, Sun: 11AM-7PM</div>
<span class="store-badge">Open Now</span>
</div>

<div class="store-card">
<div class="store-name">Vaishnavi Jewellers - Manjalpur</div>
<div class="store-address">Shop 18, Manjalpur Main Road<br>Vadodara - 390011</div>
<div class="store-phone">📞 +91 265 2345 6794</div>
<div class="store-hours"><strong>Hours:</strong> Mon-Sat: 10AM-8PM</div>
</div>

<div class="store-card">
<div class="store-name">Vaishnavi Jewellers - Sayajigunj</div>
<div class="store-address">Unit 8, Sayajigunj Textile Market<br>Vadodara - 390005</div>
<div class="store-phone">📞 +91 265 2345 6795</div>
<div class="store-hours"><strong>Hours:</strong> Mon-Sat: 10AM-8PM</div>
</div>

<!-- Ahmedabad -->
<div class="store-card">
<div class="store-name">Vaishnavi Jewellers - CG Road</div>
<div class="store-address">Shop No. 12, Alpha Commercial, CG Road<br>Ahmedabad - 380009</div>
<div class="store-phone">📞 +91 79 2345 6789</div>
<div class="store-hours"><strong>Hours:</strong> Mon-Sat: 10AM-8PM, Sun: 11AM-7PM</div>
<span class="store-badge">Open Now</span>
</div>

<div class="store-card">
<div class="store-name">Vaishnavi Jewellers - SG Highway</div>
<div class="store-address">Unit 45, 2nd Floor, Ahmedabad Central<br>Ahmedabad - 380015</div>
<div class="store-phone">📞 +91 79 2345 6790</div>
<div class="store-hours"><strong>Hours:</strong> Mon-Sun: 10AM-9PM</div>
<span class="store-badge">Open Now</span>
</div>

<!-- Surat -->
<div class="store-card">
<div class="store-name">Vaishnavi Jewellers - Varachha</div>
<div class="store-address">Shop 8, Varachha Market, Surat<br>Surat - 395006</div>
<div class="store-phone">📞 +91 261 2345 6791</div>
<div class="store-hours"><strong>Hours:</strong> Mon-Sat: 10AM-8PM</div>
</div>

<!-- Rajkot -->
<div class="store-card">
<div class="store-name">Vaishnavi Jewellers - Soni Bazar</div>
<div class="store-address">Shop 15, Soni Bazar Main Road<br>Rajkot - 360001</div>
<div class="store-phone">📞 +91 281 2345 6793</div>
<div class="store-hours"><strong>Hours:</strong> Mon-Sat: 10AM-8PM, Sun: 11AM-7PM</div>
<span class="store-badge">Open Now</span>
</div>

<div class="store-card">
<div class="store-name">Vaishnavi Jewellers - Palace Road</div>
<div class="store-address">K-9, Palace Road, Gandhigram<br>Rajkot - 360002</div>
<div class="store-phone">📞 +91 281 2345 6794</div>
<div class="store-hours"><strong>Hours:</strong> Mon-Sat: 10AM-8PM</div>
</div>

<div class="store-card">
<div class="store-name">Vaishnavi Jewellers - Race Course</div>
<div class="store-address">Shop 22, Race Course Ring Road<br>Rajkot - 360001</div>
<div class="store-phone">📞 +91 281 2345 6795</div>
<div class="store-hours"><strong>Hours:</strong> Mon-Sat: 10AM-8PM</div>
</div>
</div>

<div class="book-section">
<h3 class="book-title">Book an Appointment</h3>
<form id="appointmentForm">
<div class="form-group">
<label>Select City</label>
<select id="citySelect" name="city" required>
<option value="">Select City</option>
<option value="Vadodara">Vadodara</option>
<option value="Ahmedabad">Ahmedabad</option>
<option value="Surat">Surat</option>
<option value="Rajkot">Rajkot</option>
</select>
</div>

<div class="form-group">
<label>Select Store</label>
<select id="storeSelect" name="store" required>
<option value="">Select Store</option>
<option value="CG Road" data-city="Ahmedabad">CG Road</option>
<option value="SG Highway" data-city="Ahmedabad">SG Highway</option>
<option value="Varachha" data-city="Surat">Varachha</option>
<option value="Alkapuri" data-city="Vadodara">Alkapuri</option>
<option value="Mandvi" data-city="Vadodara">Mandvi</option>
<option value="Manjalpur" data-city="Vadodara">Manjalpur</option>
<option value="Sayajigunj" data-city="Vadodara">Sayajigunj</option>
<option value="Soni Bazar" data-city="Rajkot">Soni Bazar</option>
<option value="Palace Road" data-city="Rajkot">Palace Road</option>
<option value="Race Course" data-city="Rajkot">Race Course</option>
</select>
</div>

<div class="form-group">
<label>Your Name</label>
<input type="text" name="name" placeholder="Enter your name" required>
</div>

<div class="form-group">
<label>Phone</label>
<input type="tel" name="phone" placeholder="+91 98765 43210" required>
</div>

<div class="form-group">
<label>Email</label>
<input type="email" name="email" placeholder="email@example.com" required>
</div>

<div class="form-group">
<label>Preferred Date</label>
<input type="date" name="date" required>
</div>

<div class="form-group">
<label>Message (Optional)</label>
<textarea name="message" placeholder="Tell us about your requirement..."></textarea>
</div>

<button type="submit" class="btn-book">📅 Book Appointment</button>
<div id="appointmentMessage" style="margin-top:15px;font-weight:500;"></div>
</form>
</div>
</div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('citySelect').addEventListener('change', function() {
  var selectedCity = this.value;
  var storeSelect = document.getElementById('storeSelect');
  var options = storeSelect.querySelectorAll('option');

  options.forEach(function(option) {
    if (option.value === '') return;
    if (selectedCity === '' || option.getAttribute('data-city') === selectedCity) {
      option.style.display = '';
    } else {
      option.style.display = 'none';
    }
  });

  storeSelect.value = '';
});

// Appointment form submission
document.getElementById('appointmentForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  var btn = this.querySelector('.btn-book');
  var msgEl = document.getElementById('appointmentMessage');
  var formData = new FormData(this);

  btn.disabled = true;
  btn.textContent = 'Booking...';
  msgEl.textContent = '';
  msgEl.style.color = '';

  try {
    var response = await fetch('/visnavi jwallers/api/appointment.php', {
      method: 'POST',
      body: formData
    });
    var result = await response.json();

    if (result.success) {
      msgEl.style.color = '#28a745';
      msgEl.textContent = result.message;
      this.reset();
    } else {
      msgEl.style.color = '#dc3545';
      msgEl.textContent = result.message;
    }
  } catch (err) {
    msgEl.style.color = '#dc3545';
    msgEl.textContent = 'Something went wrong. Please try again.';
  }

  btn.disabled = false;
  btn.textContent = '📅 Book Appointment';
});
</script>
</body>
</html>