# Deployment Guide — Visnavi Jewellers (Enterprise Luxury E-Commerce)

## 1) Requirements
- PHP 8.3+
- MySQL 8+
- Apache (XAMPP on Windows supported)

## 2) Database Setup
1. Import schema: `database/schema.sql`
2. Import seed data: `database/seed.sql`

## 3) Configure Environment
Edit `includes/config.php` (do not commit secrets).
- DB DSN, username, password
- SMTP settings (for email verification, OTP, reset)
- Razorpay / Stripe keys (for payment integration)
- Cloudinary credentials (for image transforms)
- Google Maps API key (store locator)

## 4) Security
- Set `cookie_secure=true` when running behind HTTPS.
- Use strong session secret (if later added) and CSRF verification.

## 5) Assets
- Place images under:
  - `assets/images/products/`
  - `assets/images/banners/`
- For production, connect Cloudinary and update image_url generation.

## 6) Running Locally
- Start Apache
- Browse: http://localhost/visnavi-jwallers/

## 7) Verify Endpoints
- `api/products.php`
- `api/products-filter.php`
- `api/search.php`
- `api/cart-toggle.php`
- `api/wishlist-toggle.php`

