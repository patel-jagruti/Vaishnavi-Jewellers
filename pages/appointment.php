<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<base href="/visnavi jwallers/">
<title>Book Store Visit | Vaishnavi Jewellers</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
:root{
  --maroon:#7A1E3A;
  --maroon-dark:#5a1529;
  --gold:#D4AF37;
  --ivory:#FAF9F7;
  --dark:#1A1A1A;
  --gray:#666;
  --white:#fff;
  --radius:12px;
}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Poppins,sans-serif;background:var(--ivory);color:var(--dark);line-height:1.6;}
h1,h2,h3,h4{font-family:Playfair Display,serif;font-weight:700}
a{text-decoration:none;color:inherit;transition:0.3s}

/* Navigation Header link bar styling overrides */
.navbar {
  background: #fff;
  box-shadow: 0 2px 25px rgba(0,0,0,0.06);
  padding: 15px 0;
}
.navbar-brand {
  font-family: Playfair Display, serif;
  font-size: 26px;
  color: var(--maroon);
}
.breadcrumb {
  background: var(--ivory);
  padding: 15px 0;
  margin-bottom: 0;
  font-size: 13px;
  color: #888;
}
.breadcrumb a {
  color: var(--maroon);
}

.appointment-hero {
  background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
  padding: 80px 0;
  color: #fff;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.appointment-hero h1 {
  font-size: 46px;
  margin-bottom: 12px;
}
.appointment-hero p {
  opacity: 0.9;
  font-size: 16px;
}

.booking-section {
  padding: 80px 0;
  background: #fff;
}
.booking-card {
  background: var(--white);
  border-radius: 24px;
  padding: 45px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.05);
  border: 1px solid rgba(212,175,55,0.15);
}
.booking-card h3 {
  font-size: 28px;
  margin-bottom: 25px;
  text-align: center;
  color: #1A1A1A;
}

.store-select {
  padding: 14px 20px;
  border-radius: var(--radius);
  border: 1px solid #ddd;
  margin-bottom: 25px;
  width: 100%;
  font-size: 14px;
  outline: none;
  font-family: 'Poppins', sans-serif;
  background-color: #FAF9F7;
  cursor: pointer;
}

/* Premium Calendar Styling */
.calendar-widget {
  background: #fff;
  border: 1px solid rgba(212, 175, 55, 0.25);
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 25px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.01);
}
.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  border-bottom: 1px solid rgba(0,0,0,0.05);
  padding-bottom: 10px;
}
.calendar-title {
  font-family: 'Playfair Display', serif;
  font-size: 16px;
  font-weight: 700;
  color: var(--dark);
}
.calendar-nav-btn {
  background: none;
  border: none;
  font-size: 13px;
  color: var(--maroon);
  cursor: pointer;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s;
}
.calendar-nav-btn:hover {
  background: rgba(122,30,58,0.05);
}
.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 6px;
  text-align: center;
}
.calendar-day-header {
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  color: var(--gray);
  padding: 5px 0;
  letter-spacing: 1px;
}
.calendar-day {
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 500;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.2s ease;
  color: var(--dark);
  border: 1px solid transparent;
}
.calendar-day.empty {
  cursor: default;
  pointer-events: none;
}
.calendar-day.disabled {
  color: #ccc;
  cursor: not-allowed;
  pointer-events: none;
  text-decoration: line-through;
}
.calendar-day:hover:not(.disabled):not(.empty) {
  background: rgba(122,30,58,0.05);
  border-color: rgba(122,30,58,0.2);
}
.calendar-day.active {
  background: var(--maroon) !important;
  color: #fff !important;
  font-weight: 600;
  box-shadow: 0 4px 10px rgba(122,30,58,0.2);
}

/* Time Slots layout styling */
.slot-group-title {
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  color: var(--gray);
  letter-spacing: 1.5px;
  margin-top: 15px;
  margin-bottom: 8px;
  display: block;
}
.time-slots {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 25px;
}
.time-slot {
  padding: 10px 20px;
  border: 1px solid #ddd;
  border-radius: 30px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 500;
  transition: all 0.3s ease;
  background: #fff;
}
.time-slot:hover {
  border-color: var(--maroon);
  color: var(--maroon);
}
.time-slot.active {
  background: var(--maroon) !important;
  color: #fff !important;
  border-color: var(--maroon) !important;
  box-shadow: 0 4px 10px rgba(122,30,58,0.15);
}

.form-control {
  padding: 14px 20px;
  border-radius: var(--radius);
  border: 1px solid #ddd;
  margin-bottom: 20px;
  font-size: 14px;
}
.form-control:focus {
  border-color: var(--maroon);
  box-shadow: none;
}

.btn-book {
  background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
  color: #fff;
  padding: 16px 50px;
  border-radius: 30px;
  font-weight: 600;
  border: none;
  width: 100%;
  font-size: 15px;
  letter-spacing: 1px;
  text-transform: uppercase;
  box-shadow: 0 8px 24px rgba(122,30,58,0.25);
  transition: all 0.3s ease;
}
.btn-book:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(122,30,58,0.35);
}

/* Success layout styles */
.success-card {
  text-align: center;
  padding: 40px 20px;
}
.success-icon {
  width: 90px;
  height: 90px;
  background: linear-gradient(135deg, #D4AF37, #F5E6A3, #D4AF37);
  color: #1a1a1a;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 40px;
  margin: 0 auto 25px;
  box-shadow: 0 8px 20px rgba(212,175,55,0.3);
}

.footer-section {
  background: var(--dark);
  color: #fff;
  padding: 80px 0 30px;
}
.footer-section h3 {
  color: var(--gold);
  font-size: 22px;
  margin-bottom: 25px;
}
.footer-section h4 {
  color: #fff;
  font-size: 16px;
  margin-bottom: 20px;
}
.footer-section a {
  color: #aaa;
  padding: 8px 0;
  display: block;
}
.footer-btm {
  border-top: 1px solid #333;
  padding: 25px 0;
  margin-top: 50px;
  text-align: center;
  color: #666;
  font-size: 13px;
}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <img src="assets/images/logo.svg" alt="Vaishnavi Jewellers" style="height:40px;">
    </a>
    <ul class="navbar-nav ms-auto d-none d-lg-flex flex-row gap-4" style="margin-right:30px;">
      <li><a class="nav-link" href="index.php" style="font-weight:500; font-size:14px;">Home</a></li>
      <li><a class="nav-link" href="pages/shop.php" style="font-weight:500; font-size:14px;">Shop</a></li>
      <li><a class="nav-link" href="pages/about.php" style="font-weight:500; font-size:14px;">About</a></li>
      <li><a class="nav-link" href="pages/contact.php" style="font-weight:500; font-size:14px;">Contact</a></li>
    </ul>
  </div>
</nav>

<nav class="breadcrumb">
  <div class="container">
    <a href="index.php">Home</a> / <span>Book Appointment</span>
  </div>
</nav>

<section class="appointment-hero">
  <div class="container">
    <h1>Book a Store Visit</h1>
    <p>Schedule an exclusive personalized shopping session with our jewelry consultants.</p>
  </div>
</section>

<section class="booking-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="booking-card" id="bookingCardContainer">
          
          <form id="appointmentForm">
            <h3>Schedule Your Visit</h3>
            
            <label class="form-label font-semibold text-xs text-uppercase mb-2" style="letter-spacing:1px; color:#666;">Select Boutique Store</label>
            <select class="store-select" id="boutiqueStore" required>
              <option value="Mumbai Phoenix Mall">Vaishnavi Jewellers - Mumbai Phoenix Mall</option>
              <option value="Delhi Saket">Vaishnavi Jewellers - Delhi Saket</option>
              <option value="Hyderabad Jubilee Hills">Vaishnavi Jewellers - Hyderabad Jubilee Hills</option>
            </select>
            
            <label class="form-label font-semibold text-xs text-uppercase mb-2" style="letter-spacing:1px; color:#666;">Select Appointment Date</label>
            <div class="calendar-widget">
              <div class="calendar-header">
                <button type="button" class="calendar-nav-btn" id="calPrevBtn"><i class="fas fa-chevron-left"></i></button>
                <div class="calendar-title" id="calMonthTitle">February 2026</div>
                <button type="button" class="calendar-nav-btn" id="calNextBtn"><i class="fas fa-chevron-right"></i></button>
              </div>
              <div class="calendar-grid" id="calendarGrid"></div>
            </div>
            
            <label class="form-label font-semibold text-xs text-uppercase mb-2" style="letter-spacing:1px; color:#666;">Select Time Slot</label>
            <div>
              <span class="slot-group-title">Morning</span>
              <div class="time-slots" id="morningSlots">
                <div class="time-slot" data-slot="10:00 AM">10:00 AM</div>
                <div class="time-slot" data-slot="11:30 AM">11:30 AM</div>
              </div>
              
              <span class="slot-group-title">Afternoon</span>
              <div class="time-slots" id="afternoonSlots">
                <div class="time-slot" data-slot="01:00 PM">01:00 PM</div>
                <div class="time-slot" data-slot="02:30 PM">02:30 PM</div>
                <div class="time-slot" data-slot="04:00 PM">04:00 PM</div>
              </div>
              
              <span class="slot-group-title">Evening</span>
              <div class="time-slots" id="eveningSlots">
                <div class="time-slot" data-slot="05:30 PM">05:30 PM</div>
                <div class="time-slot" data-slot="07:00 PM">07:00 PM</div>
              </div>
            </div>
            
            <!-- Hidden Fields for Booking Details -->
            <input type="hidden" id="selectedDateInput" required>
            <input type="hidden" id="selectedTimeInput" required>
            
            <label class="form-label font-semibold text-xs text-uppercase mb-2" style="letter-spacing:1px; color:#666;">Your Details</label>
            <div class="row">
              <div class="col-md-6">
                <input type="text" class="form-control" id="fullName" placeholder="Full Name" required>
              </div>
              <div class="col-md-6">
                <input type="tel" class="form-control" id="phoneNumber" placeholder="Phone Number" required>
              </div>
            </div>
            <input type="email" class="form-control" id="emailAddress" placeholder="Email Address" required>
            
            <label class="form-label font-semibold text-xs text-uppercase mb-2" style="letter-spacing:1px; color:#666;">Purpose of Visit</label>
            <select class="form-control" id="visitPurpose" required>
              <option value="Browse Gold Collection">Browse Gold Collection</option>
              <option value="Browse Diamond Collection">Browse Diamond Collection</option>
              <option value="Bridal Consultation">Bridal Jewellery Consultation</option>
              <option value="Ring Sizing & Fitting">Ring Sizing & Fitting</option>
              <option value="Gift Selection">Gift Selection</option>
            </select>
            
            <button type="submit" class="btn-book mt-3">Confirm Reservation</button>
          </form>
          
        </div>
      </div>
    </div>
  </div>
</section>

<footer class="footer-section">
  <div class="container">
    <div class="row g-5">
      <div class="col-md-4">
        <h3>Vaishnavi Jewellers</h3>
        <p style="color:#888; font-size:14px;">Bespoke luxury jewelry designed to celebrate your life's most precious occasions. BIS Hallmarked quality since 1985.</p>
      </div>
      <div class="col-md-4">
        <h4>Collection Links</h4>
        <a href="pages/shop.php?category=Gold">Handcrafted Gold Jewellery</a>
        <a href="pages/shop.php?category=Diamond">Exclusive Diamond Sets</a>
        <a href="pages/shop.php?category=Wedding">Royal Bridal Collection</a>
      </div>
      <div class="col-md-4">
        <h4>Contact Boutique</h4>
        <p style="color:#aaa; font-size:14px; margin-bottom:8px;"><i class="fas fa-envelope me-2"></i> info@vaishnavijewellers.com</p>
        <p style="color:#aaa; font-size:14px;"><i class="fas fa-phone me-2"></i> +91 22 4901 0291</p>
      </div>
    </div>
    <div class="footer-btm">
      <p>&copy; 2026 Vaishnavi Jewellers. All Rights Reserved.</p>
    </div>
  </div>
</footer>

<!-- ========== INTERACTIVE CALENDAR JAVASCRIPT ========== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const calMonthTitle = document.getElementById('calMonthTitle');
  const calGrid = document.getElementById('calendarGrid');
  const prevBtn = document.getElementById('calPrevBtn');
  const nextBtn = document.getElementById('calNextBtn');
  const dateInput = document.getElementById('selectedDateInput');
  const timeInput = document.getElementById('selectedTimeInput');
  const timeSlots = document.querySelectorAll('.time-slot');
  const form = document.getElementById('appointmentForm');
  const container = document.getElementById('bookingCardContainer');

  // Start calendar date: Feb 2026
  let currentYear = 2026;
  let currentMonth = 1; // February (0-indexed, so 1 is Feb)

  const monthNames = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
  ];

  const daysOfWeek = ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"];

  function renderCalendar() {
    calMonthTitle.textContent = `${monthNames[currentMonth]} ${currentYear}`;
    calGrid.innerHTML = '';

    // Day headers
    daysOfWeek.forEach(day => {
      const headerDiv = document.createElement('div');
      headerDiv.className = 'calendar-day-header';
      headerDiv.textContent = day;
      calGrid.appendChild(headerDiv);
    });

    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const totalDays = new Date(currentYear, currentMonth + 1, 0).getDate();

    // Empty cells before the 1st
    for (let i = 0; i < firstDay; i++) {
      const emptyDiv = document.createElement('div');
      emptyDiv.className = 'calendar-day empty';
      calGrid.appendChild(emptyDiv);
    }

    // Days cells
    const today = new Date();
    for (let day = 1; day <= totalDays; day++) {
      const cell = document.createElement('div');
      cell.className = 'calendar-day';
      cell.textContent = day;

      const cellDate = new Date(currentYear, currentMonth, day);
      
      // Disable past dates (keep active from Feb 20, 2026 onwards for simulation)
      const mockMinDate = new Date(2026, 1, 20); // Feb 20, 2026
      if (cellDate < mockMinDate) {
        cell.classList.add('disabled');
      }

      cell.addEventListener('click', function() {
        if (cell.classList.contains('disabled')) return;
        
        document.querySelectorAll('.calendar-day').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        
        const formattedDate = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        dateInput.value = formattedDate;
      });

      calGrid.appendChild(cell);
    }
  }

  // Prev / Next Month
  prevBtn.addEventListener('click', function() {
    // Limit navigation back past Feb 2026
    if (currentYear === 2026 && currentMonth === 1) return;
    
    currentMonth--;
    if (currentMonth < 0) {
      currentMonth = 11;
      currentYear--;
    }
    renderCalendar();
  });

  nextBtn.addEventListener('click', function() {
    currentMonth++;
    if (currentMonth > 11) {
      currentMonth = 0;
      currentYear++;
    }
    renderCalendar();
  });

  // Time slots selection
  timeSlots.forEach(slot => {
    slot.addEventListener('click', function() {
      timeSlots.forEach(s => s.classList.remove('active'));
      this.classList.add('active');
      timeInput.value = this.getAttribute('data-slot');
    });
  });

  // Form submission overlay success rendering
  form.addEventListener('submit', function(e) {
    e.preventDefault();

    if (!dateInput.value) {
      alert('Please select an appointment date on the calendar.');
      return;
    }
    if (!timeInput.value) {
      alert('Please select a preferred time slot.');
      return;
    }

    const store = document.getElementById('boutiqueStore').value;
    const dateSelected = dateInput.value;
    const timeSelected = timeInput.value;
    const name = document.getElementById('fullName').value;
    const phone = document.getElementById('phoneNumber').value;
    const email = document.getElementById('emailAddress').value;
    const purpose = document.getElementById('visitPurpose').value;

    // Output premium confirmation screen
    container.innerHTML = `
      <div class="success-card">
        <div class="success-icon"><i class="fas fa-check"></i></div>
        <h3 class="mb-3" style="font-family:'Playfair Display', serif; font-size:28px;">Booking Confirmed</h3>
        <p class="text-muted mb-4">Your personalized boutique appointment has been successfully scheduled.</p>
        
        <div class="summary-card text-start">
          <h4 style="font-family:'Playfair Display', serif; border-bottom: 1px solid rgba(0,0,0,0.1); padding-bottom:10px;">Visit Summary</h4>
          <div class="summary-row"><span>Boutique Store</span><span>Vaishnavi Jewellers - ${store}</span></div>
          <div class="summary-row"><span>Date</span><span>${dateSelected}</span></div>
          <div class="summary-row"><span>Time Slot</span><span>${timeSelected}</span></div>
          <div class="summary-row"><span>Purpose</span><span>${purpose}</span></div>
          <div class="summary-row"><span>Guest Name</span><span>${name}</span></div>
          <div class="summary-row"><span>Contact</span><span>${phone}</span></div>
          <div class="summary-row"><span>Status</span><span style="color:#28a745; font-weight:600;"><i class="fas fa-circle text-xs me-1"></i> VIP Confirmed</span></div>
        </div>
        
        <p class="text-muted mt-4" style="font-size:12px;"><i class="fas fa-info-circle me-1"></i> A confirmation invitation has been sent to <strong>${email}</strong>. Please bring a valid ID and this confirmation screen on your boutique visit.</p>
        <a href="index.php" class="btn-book mt-4" style="text-decoration:none; display:inline-block; max-width:280px;">Return Home</a>
      </div>
    `;
  });

  // Render on startup
  renderCalendar();
});
</script>
</body>
</html>
