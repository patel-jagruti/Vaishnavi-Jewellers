-- Visnavi Jewellers (Premium Jewellery) - Database schema
-- Target: MySQL 8+
-- Notes:
-- 1) Store uploaded images in /assets/images/... and reference via URL/path in DB.
-- 2) Designed for: products, filters, auth, cart, wishlist, compare, recently viewed, orders, admin CRUD.
-- 3) Includes ONLY DDL (no seeds). Run this file then run database/seed.sql.

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- USERS & AUTH
-- ----------------------------
CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  role ENUM('customer','admin') NOT NULL DEFAULT 'customer',

  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(30) NULL,

  password_hash VARCHAR(255) NOT NULL,
  gender ENUM('female','male','unisex') NULL,

  -- OTP (optional flow)
  otp_code_hash VARCHAR(255) NULL,
  otp_expires_at DATETIME NULL,

  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS addresses (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,

  full_name VARCHAR(150) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  line1 VARCHAR(220) NOT NULL,
  line2 VARCHAR(220) NULL,
  city VARCHAR(120) NOT NULL,
  state VARCHAR(120) NOT NULL,
  pincode VARCHAR(20) NOT NULL,
  country VARCHAR(80) NOT NULL DEFAULT 'India',

  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_addresses_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- CATALOG
-- ----------------------------
CREATE TABLE IF NOT EXISTS categories (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL UNIQUE,
  slug VARCHAR(140) NOT NULL UNIQUE,

  gender ENUM('female','male','unisex','all') NOT NULL DEFAULT 'all',
  occasion VARCHAR(80) NULL,
  metal VARCHAR(80) NULL,
  stone VARCHAR(80) NULL,
  collection VARCHAR(80) NULL,

  display_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,

  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS subcategories (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  category_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL,
  display_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_category_slug (category_id, slug),
  CONSTRAINT fk_subcat_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS products (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  category_id BIGINT UNSIGNED NULL,
  subcategory_id BIGINT UNSIGNED NULL,

  name VARCHAR(200) NOT NULL,
  slug VARCHAR(240) NOT NULL UNIQUE,

  sku VARCHAR(80) NOT NULL UNIQUE,

  base_price DECIMAL(12,2) NOT NULL,
  discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
  stock INT NOT NULL DEFAULT 100,

  -- Attributes
  gender ENUM('female','male','unisex') NOT NULL DEFAULT 'unisex',
  occasion VARCHAR(80) NULL,
  metal VARCHAR(80) NULL,
  stone VARCHAR(80) NULL,
  collection VARCHAR(80) NULL,

  weight_grams DECIMAL(8,2) NULL,

  short_description VARCHAR(300) NULL,
  description TEXT NULL,

  rating_avg DECIMAL(3,2) NOT NULL DEFAULT 0,
  rating_count INT NOT NULL DEFAULT 0,

  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  display_order INT NOT NULL DEFAULT 0,

  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  CONSTRAINT fk_product_subcategory FOREIGN KEY (subcategory_id) REFERENCES subcategories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- VARIANTS
-- ----------------------------
CREATE TABLE IF NOT EXISTS product_variants (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  product_id BIGINT UNSIGNED NOT NULL,

  variant_sku VARCHAR(90) NOT NULL UNIQUE,
  label VARCHAR(120) NULL,

  weight_grams DECIMAL(8,2) NULL,
  price_delta DECIMAL(12,2) NOT NULL DEFAULT 0,

  stock INT NOT NULL DEFAULT 0,

  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_product_variants_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  INDEX idx_product_variants_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- IMAGES
-- ----------------------------
CREATE TABLE IF NOT EXISTS product_images (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  product_id BIGINT UNSIGNED NOT NULL,
  image_url VARCHAR(255) NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  display_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  INDEX idx_product_images_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- MEDIA: BANNERS + SLIDERS
-- ----------------------------
CREATE TABLE IF NOT EXISTS banners (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(160) NULL,
  image_url VARCHAR(255) NOT NULL,
  link_url VARCHAR(255) NULL,
  display_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sliders (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(180) NULL,
  subtitle VARCHAR(240) NULL,

  image_url VARCHAR(255) NULL,
  video_url VARCHAR(255) NULL,
  poster_url VARCHAR(255) NULL,

  cta_primary VARCHAR(120) NULL,
  cta_primary_url VARCHAR(255) NULL,
  cta_secondary VARCHAR(120) NULL,
  cta_secondary_url VARCHAR(255) NULL,

  display_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- WISHLIST / COMPARE / RECENT
-- ----------------------------
CREATE TABLE IF NOT EXISTS wishlists (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  session_id VARCHAR(120) NULL,

  product_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  UNIQUE KEY uniq_wishlist_line (user_id, session_id, product_id),
  CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS compare_products (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  session_id VARCHAR(120) NULL,

  product_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  UNIQUE KEY uniq_compare_line (user_id, session_id, product_id),
  CONSTRAINT fk_compare_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_compare_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS recent_views (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  session_id VARCHAR(120) NULL,

  product_id BIGINT UNSIGNED NOT NULL,
  viewed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  UNIQUE KEY uniq_recent_line (user_id, session_id, product_id),
  CONSTRAINT fk_recent_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_recent_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- CART
-- ----------------------------
CREATE TABLE IF NOT EXISTS cart_items (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  cart_id BIGINT UNSIGNED NOT NULL,

  product_id BIGINT UNSIGNED NOT NULL,
  variant_id BIGINT UNSIGNED NULL,

  quantity INT NOT NULL DEFAULT 1,
  unit_price DECIMAL(12,2) NOT NULL,

  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_cart_items_cart FOREIGN KEY (cart_id) REFERENCES cart(id) ON DELETE CASCADE,
  CONSTRAINT fk_cart_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  CONSTRAINT fk_cart_items_variant FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
  UNIQUE KEY uniq_cart_item (cart_id, product_id, variant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cart (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  session_id VARCHAR(120) NULL,

  coupon_code VARCHAR(60) NULL,
  gift_wrap TINYINT(1) NOT NULL DEFAULT 0,
  tax_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  shipping_amount DECIMAL(12,2) NOT NULL DEFAULT 0,

  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Legacy columns to avoid breaking existing code paths
ALTER TABLE cart
  ADD COLUMN IF NOT EXISTS product_id BIGINT UNSIGNED NULL,
  ADD COLUMN IF NOT EXISTS quantity INT NULL,
  ADD COLUMN IF NOT EXISTS unit_price DECIMAL(12,2) NULL;

-- ----------------------------
-- ORDERS
-- ----------------------------
CREATE TABLE IF NOT EXISTS orders (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  order_code VARCHAR(40) NOT NULL UNIQUE,

  user_id BIGINT UNSIGNED NULL,
  address_id BIGINT UNSIGNED NULL,

  customer_name VARCHAR(150) NOT NULL,
  customer_email VARCHAR(190) NULL,
  customer_phone VARCHAR(30) NULL,

  shipping_address TEXT NOT NULL,

  subtotal DECIMAL(12,2) NOT NULL,
  shipping DECIMAL(12,2) NOT NULL DEFAULT 0,
  discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL,

  coupon_code VARCHAR(60) NULL,
  payment_method ENUM('COD','Razorpay','Stripe','PayU') NOT NULL DEFAULT 'COD',
  payment_status ENUM('PENDING','PAID','FAILED','REFUNDED') NOT NULL DEFAULT 'PENDING',

  order_status ENUM('PLACED','CONFIRMED','SHIPPED','DELIVERED','CANCELLED','RETURNED') NOT NULL DEFAULT 'PLACED',

  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_orders_address FOREIGN KEY (address_id) REFERENCES addresses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  order_id BIGINT UNSIGNED NOT NULL,

  product_id BIGINT UNSIGNED NULL,
  variant_id BIGINT UNSIGNED NULL,

  product_name VARCHAR(200) NOT NULL,
  sku VARCHAR(80) NULL,
  unit_price DECIMAL(12,2) NOT NULL,
  discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
  quantity INT NOT NULL,
  line_total DECIMAL(12,2) NOT NULL,

  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
  CONSTRAINT fk_order_items_variant FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- REVIEWS
-- ----------------------------
CREATE TABLE IF NOT EXISTS reviews (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  product_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  rating INT NOT NULL,
  title VARCHAR(160) NULL,
  body TEXT NULL,
  is_verified_purchase TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- COUPONS
-- ----------------------------
CREATE TABLE IF NOT EXISTS coupons (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(60) NOT NULL UNIQUE,
  coupon_type ENUM('PERCENT','FLAT') NOT NULL DEFAULT 'PERCENT',

  discount_value DECIMAL(12,2) NOT NULL,
  min_order_amount DECIMAL(12,2) NOT NULL DEFAULT 0,

  start_date DATE NULL,
  end_date DATE NULL,

  usage_limit INT NOT NULL DEFAULT 0,
  used_count INT NOT NULL DEFAULT 0,

  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- CMS & SEO
-- ----------------------------
CREATE TABLE IF NOT EXISTS cms_pages (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  slug VARCHAR(180) NOT NULL UNIQUE,
  title VARCHAR(220) NOT NULL,
  content LONGTEXT NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seo_settings (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  page_type ENUM('home','shop','product','category','cms','custom') NOT NULL,
  page_slug VARCHAR(200) NULL,

  meta_title VARCHAR(240) NULL,
  meta_description VARCHAR(500) NULL,

  canonical_url VARCHAR(260) NULL,

  og_title VARCHAR(240) NULL,
  og_description VARCHAR(500) NULL,
  og_image VARCHAR(255) NULL,

  twitter_card ENUM('summary','summary_large_image') NULL,
  twitter_title VARCHAR(240) NULL,
  twitter_description VARCHAR(500) NULL,
  twitter_image VARCHAR(255) NULL,

  schema_json LONGTEXT NULL,

  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY uniq_seo (page_type, page_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Helpful indexes
-- ----------------------------
CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id);
CREATE INDEX IF NOT EXISTS idx_products_active ON products(is_active, display_order);
CREATE INDEX IF NOT EXISTS idx_product_images_product ON product_images(product_id);
CREATE INDEX IF NOT EXISTS idx_orders_order_code ON orders(order_code);

SET FOREIGN_KEY_CHECKS = 1;

