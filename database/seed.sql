-- Visnavi Jewellers (Premium Jewellery) - Demo seed data
-- IMPORTANT: Replaces the previous seed.sql to match the updated schema.
-- Target: MySQL 8+

-- =============================
-- 1) Categories
-- =============================
INSERT INTO categories (name, slug, gender, occasion, metal, stone, collection, display_order, is_active)
VALUES
  ('All Jewellery', 'all-jewellery', 'all', NULL, NULL, NULL, 'Elan', 0, 1),
  ('Gold', 'gold', 'all', NULL, 'Gold', NULL, NULL, 1, 1),
  ('Diamond', 'diamond', 'all', NULL, NULL, 'Diamond', 'Elan', 2, 1),
  ('Earrings', 'earrings', 'all', NULL, NULL, NULL, NULL, 3, 1),
  ('Rings', 'rings', 'all', NULL, NULL, NULL, NULL, 4, 1),
  ('Daily Wear', 'daily-wear', 'all', NULL, NULL, NULL, 'Daily', 5, 1),
  ('Gemstone', 'gemstone', 'all', NULL, NULL, 'Gemstone', NULL, 6, 1),
  ('Wedding', 'wedding', 'all', 'Wedding', NULL, NULL, NULL, 7, 1),
  ('Gifting', 'gifting', 'all', 'Gifting', NULL, NULL, NULL, 8, 1)
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  gender = VALUES(gender),
  occasion = VALUES(occasion),
  metal = VALUES(metal),
  stone = VALUES(stone),
  collection = VALUES(collection),
  display_order = VALUES(display_order),
  is_active = VALUES(is_active);

-- =============================
-- 2) Subcategories (minimal set)
-- =============================
INSERT INTO subcategories (category_id, name, slug, display_order, is_active)
SELECT c.id, s.name, s.slug, s.display_order, 1
FROM (
  SELECT 'gold' AS cat_slug, 'Gold Necklace' AS name, 'gold-necklace' AS slug, 1 AS display_order UNION ALL
  SELECT 'gold', 'Gold Ring', 'gold-ring', 1 UNION ALL
  SELECT 'diamond', 'Diamond Studs', 'diamond-studs', 1 UNION ALL
  SELECT 'earrings', 'Jhumkas', 'jhumkas', 1 UNION ALL
  SELECT 'rings', 'Solitaire', 'solitaire', 1
) s
JOIN categories c ON c.slug = s.cat_slug
WHERE c.id IS NOT NULL
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  display_order = VALUES(display_order),
  is_active = VALUES(is_active);

-- =============================
-- 3) Products (base seed matching current catalog APIs)
-- =============================
SET @cat_gold := (SELECT id FROM categories WHERE slug='gold' LIMIT 1);
SET @cat_diamond := (SELECT id FROM categories WHERE slug='diamond' LIMIT 1);
SET @cat_earrings := (SELECT id FROM categories WHERE slug='earrings' LIMIT 1);
SET @cat_rings := (SELECT id FROM categories WHERE slug='rings' LIMIT 1);
SET @cat_daily := (SELECT id FROM categories WHERE slug='daily-wear' LIMIT 1);
SET @cat_gem := (SELECT id FROM categories WHERE slug='gemstone' LIMIT 1);
SET @cat_wedding := (SELECT id FROM categories WHERE slug='wedding' LIMIT 1);
SET @cat_gifting := (SELECT id FROM categories WHERE slug='gifting' LIMIT 1);
SET @cat_all := (SELECT id FROM categories WHERE slug='all-jewellery' LIMIT 1);

INSERT INTO products (
  category_id,
  name,
  slug,
  sku,
  base_price,
  discount_percent,
  stock,
  gender,
  occasion,
  metal,
  stone,
  collection,
  short_description,
  description,
  rating_avg,
  rating_count,
  is_featured,
  is_active,
  display_order
) VALUES
  (@cat_rings, 'Solitaire Diamond Ring', 'solitaire-diamond-ring', 'SKU-RING-001', 69999, 10, 30, 'unisex', NULL, '18K Gold', 'Diamond', 'Elan',
   'A timeless solitaire ring with a brilliant sparkle.',
   'A premium solitaire diamond ring crafted in 18K gold for everyday elegance and special occasions.',
   4.7, 128, 1, 1, 1),

  (@cat_rings, 'Kundan Heritage Ring', 'kundan-heritage-ring', 'SKU-RING-002', 49999, 5, 25, 'unisex', NULL, 'Gold Plated', 'Gemstone', 'Heritage',
   'Traditional kundan detailing inspired by heritage.',
   'A kundan-inspired ring with refined detailing and a comfortable fit.',
   4.5, 90, 0, 1, 2),

  (@cat_earrings, 'Sleek Halo Earrings', 'sleek-halo-earrings', 'SKU-EAR-001', 29999, 0, 40, 'unisex', 'Everyday', 'Gold', 'Diamond', 'Elan',
   'Minimal halo earrings with everyday sparkle.',
   'Lightweight halo earrings designed for comfort and refined shine.',
   4.4, 76, 1, 1, 1),

  (@cat_earrings, 'Festive Jhumka Earrings', 'festive-jhumka-earrings', 'SKU-EAR-002', 35999, 8, 35, 'female', 'Wedding', 'Gold Plated', 'Gemstone', 'Sangeet',
   'Festive jhumkas with elegant movement.',
   'Swirling jhumka silhouettes crafted to be event-ready with a secure closure.',
   4.6, 112, 1, 1, 2),

  (@cat_gold, 'Pearl Drop Necklace', 'pearl-drop-necklace', 'SKU-NEC-001', 89999, 12, 18, 'female', 'Gifting', 'Gold', 'Pearl', 'Elan',
   'Graceful pearl drops with premium finish.',
   'A refined pearl drop necklace with a luxurious glow and adjustable chain.',
   4.8, 64, 1, 1, 1),

  (@cat_gold, 'Temple Blossom Necklace', 'temple-blossom-necklace', 'SKU-NEC-002', 74999, 6, 22, 'female', 'Wedding', '18K Gold', 'Diamond', 'Heritage',
   'Modern temple-inspired blossom motif.',
   'A modern blossom necklace with secure clasps and polished detailing.',
   4.5, 88, 0, 1, 2),

  (@cat_daily, 'Radiant Bangle Set', 'radiant-bangle-set', 'SKU-BAN-001', 39999, 0, 50, 'female', 'Everyday', '18K Gold', NULL, 'Daily',
   'A radiant bangle set with smooth finish.',
   'A premium bangle set designed for comfort fit and high shine.',
   4.3, 41, 1, 1, 1),

  (@cat_gem, 'Meenakari Elegance Bangles', 'meenakari-elegance-bangles', 'SKU-BAN-002', 44999, 7, 28, 'female', 'Gifting', 'Gold Plated', 'Gemstone', 'ColorPop',
   'Colorful meenakari-inspired bangles.',
   'Meenakari-inspired detailing that pairs beautifully with ethnic outfits.',
   4.6, 59, 0, 1, 2),

  (@cat_gifting, 'Twist Bracelet', 'twist-bracelet', 'SKU-BRC-001', 25999, 0, 60, 'unisex', 'Gifting', 'Gold', NULL, 'EveryMoment',
   'Modern twist bracelet with refined gloss.',
   'A minimal, versatile twist bracelet with secure clasp.',
   4.4, 35, 0, 1, 1),

  (@cat_wedding, 'Charm Line Bracelet', 'charm-line-bracelet', 'SKU-BRC-002', 31999, 4, 45, 'female', 'Wedding', 'Gold Plated', 'Gemstone', 'Sangeet',
   'Charm-inspired bracelet with subtle detailing.',
   'Adjustable fit bracelet with premium daily sophistication.',
   4.2, 27, 0, 1, 2)
ON DUPLICATE KEY UPDATE
  category_id = VALUES(category_id),
  base_price = VALUES(base_price),
  discount_percent = VALUES(discount_percent),
  stock = VALUES(stock),
  gender = VALUES(gender),
  occasion = VALUES(occasion),
  metal = VALUES(metal),
  stone = VALUES(stone),
  collection = VALUES(collection),
  short_description = VALUES(short_description),
  description = VALUES(description),
  rating_avg = VALUES(rating_avg),
  rating_count = VALUES(rating_count),
  is_featured = VALUES(is_featured),
  is_active = VALUES(is_active),
  display_order = VALUES(display_order);

-- =============================
-- 4) Product Images
-- =============================
SET @p1 := (SELECT id FROM products WHERE slug='solitaire-diamond-ring' LIMIT 1);
SET @p2 := (SELECT id FROM products WHERE slug='kundan-heritage-ring' LIMIT 1);
SET @p3 := (SELECT id FROM products WHERE slug='sleek-halo-earrings' LIMIT 1);
SET @p4 := (SELECT id FROM products WHERE slug='festive-jhumka-earrings' LIMIT 1);
SET @p5 := (SELECT id FROM products WHERE slug='pearl-drop-necklace' LIMIT 1);
SET @p6 := (SELECT id FROM products WHERE slug='temple-blossom-necklace' LIMIT 1);
SET @p7 := (SELECT id FROM products WHERE slug='radiant-bangle-set' LIMIT 1);
SET @p8 := (SELECT id FROM products WHERE slug='meenakari-elegance-bangles' LIMIT 1);
SET @p9 := (SELECT id FROM products WHERE slug='twist-bracelet' LIMIT 1);
SET @p10 := (SELECT id FROM products WHERE slug='charm-line-bracelet' LIMIT 1);

INSERT INTO product_images (product_id, image_url, is_primary, display_order)
VALUES
  (@p1, '/assets/images/demo-ring.svg', 1, 0), (@p1, '/assets/images/demo-ring.svg', 0, 1),
  (@p2, '/assets/images/demo-ring.svg', 1, 0), (@p2, '/assets/images/demo-ring.svg', 0, 1),
  (@p3, '/assets/images/demo-ring.svg', 1, 0), (@p3, '/assets/images/demo-ring.svg', 0, 1),
  (@p4, '/assets/images/demo-ring.svg', 1, 0), (@p4, '/assets/images/demo-ring.svg', 0, 1),
  (@p5, '/assets/images/demo-ring.svg', 1, 0), (@p5, '/assets/images/demo-ring.svg', 0, 1),
  (@p6, '/assets/images/demo-ring.svg', 1, 0), (@p6, '/assets/images/demo-ring.svg', 0, 1),
  (@p7, '/assets/images/demo-ring.svg', 1, 0), (@p7, '/assets/images/demo-ring.svg', 0, 1),
  (@p8, '/assets/images/demo-ring.svg', 1, 0), (@p8, '/assets/images/demo-ring.svg', 0, 1),
  (@p9, '/assets/images/demo-ring.svg', 1, 0), (@p9, '/assets/images/demo-ring.svg', 0, 1),
  (@p10,'/assets/images/demo-ring.svg', 1, 0), (@p10,'/assets/images/demo-ring.svg', 0, 1)
ON DUPLICATE KEY UPDATE
  image_url = VALUES(image_url),
  is_primary = VALUES(is_primary),
  display_order = VALUES(display_order);

-- =============================
-- 5) Banners
-- =============================
INSERT INTO banners (title, image_url, link_url, display_order, is_active)
VALUES
  ('Elan - My World. My Story.', '/assets/images/hero1.svg', '/pages/shop.php?category=All%20Jewellery', 0, 1),
  ('Graceful layers for modern luxury', '/assets/images/hero2.svg', '/pages/shop.php?category=Gold', 1, 1),
  ('Soft silhouettes with a polished glow', '/assets/images/hero3.svg', '/pages/shop.php?category=Diamond', 2, 1)
ON DUPLICATE KEY UPDATE
  title = VALUES(title),
  image_url = VALUES(image_url),
  link_url = VALUES(link_url),
  display_order = VALUES(display_order),
  is_active = VALUES(is_active);

-- Optional: seed sliders (for future hero video support)
INSERT INTO sliders (title, subtitle, image_url, video_url, poster_url,
                     cta_primary, cta_primary_url, cta_secondary, cta_secondary_url,
                     display_order, is_active)
VALUES
  ('Diamond Collection', 'Sparkle crafted for unforgettable moments.', '/assets/images/hero1.svg', NULL, NULL,
   'Shop Diamond', '/pages/shop.php?category=Diamond', 'Explore Sets', '/pages/shop.php?category=Diamond', 0, 1),
  ('Bridal Collection', 'Designed to glow on your most beautiful day.', '/assets/images/hero2.svg', NULL, NULL,
   'Shop Bridal', '/pages/shop.php?category=Wedding', 'View Wedding', '/pages/shop.php?category=Wedding', 1, 1),
  ('Gold Collection', 'Timeless gold shine, modern craftsmanship.', '/assets/images/hero3.svg', NULL, NULL,
   'Shop Gold', '/pages/shop.php?category=Gold', 'Find Gifts', '/pages/shop.php?category=Gifting', 2, 1)
ON DUPLICATE KEY UPDATE
  title = VALUES(title), subtitle = VALUES(subtitle),
  image_url = VALUES(image_url),
  video_url = VALUES(video_url),
  poster_url = VALUES(poster_url),
  cta_primary = VALUES(cta_primary), cta_primary_url = VALUES(cta_primary_url),
  cta_secondary = VALUES(cta_secondary), cta_secondary_url = VALUES(cta_secondary_url),
  display_order = VALUES(display_order),
  is_active = VALUES(is_active);

-- =============================
-- 6) Coupons
-- =============================
INSERT INTO coupons (code, coupon_type, discount_value, min_order_amount, start_date, end_date, usage_limit, used_count, is_active)
VALUES
  ('VISNAVI10', 'PERCENT', 10, 50000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 1000, 0, 1),
  ('FIRSTGIFT200', 'FLAT', 200, 20000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 500, 0, 1)
ON DUPLICATE KEY UPDATE
  coupon_type = VALUES(coupon_type),
  discount_value = VALUES(discount_value),
  min_order_amount = VALUES(min_order_amount),
  start_date = VALUES(start_date),
  end_date = VALUES(end_date),
  usage_limit = VALUES(usage_limit),
  used_count = VALUES(used_count),
  is_active = VALUES(is_active);

COMMIT;

