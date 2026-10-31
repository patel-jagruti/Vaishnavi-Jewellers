<?php
declare(strict_types=1);

/**
 * Mega Menu Data for Luxury Jewelry Website
 * Tanishq-Style Full-Width Mega Menu
 */

function mega_menu_data(): array {
  return [
    'all-jewellery' => [
      'title' => 'All Jewellery',
      'filters' => [
        'category' => ['All Jewellery', 'Gold', 'Diamond', 'Gemstone', 'Wedding'],
        'price' => ['Under ₹10,000', '₹10,000 - ₹25,000', '₹25,000 - ₹50,000', '₹50,000 - ₹1L', 'Above ₹1L'],
        'occasion' => ['Daily Wear', 'Party Wear', 'Wedding', 'Festive', 'Office Wear'],
        'gender' => ['Women', 'Men', 'Kids', 'Couple'],
      ],
      'cards' => [
        ['name' => 'Gold', 'icon' => '??', 'href' => 'pages/shop.php?category=Gold'],
        ['name' => 'Diamond', 'icon' => '??', 'href' => 'pages/shop.php?category=Diamond'],
        ['name' => 'Gemstone', 'icon' => '??', 'href' => 'pages/shop.php?category=Gemstone'],
        ['name' => 'Daily Wear', 'icon' => '?', 'href' => 'pages/shop.php?category=Daily%20Wear'],
        ['name' => 'Wedding', 'icon' => '??', 'href' => 'pages/shop.php?category=Wedding'],
        ['name' => 'Earrings', 'icon' => '?', 'href' => 'pages/shop.php?category=Earrings'],
      ],
      'promo_banner' => ['title' => 'New Arrivals', 'subtitle' => 'Explore our latest collection', 'cta' => 'Shop Now', 'href' => 'pages/shop.php'],
      'promo_large' => ['title' => 'Festive Collection', 'subtitle' => 'Elegance Redefined', 'cta' => 'View All', 'href' => 'pages/shop.php', 'image' => 'assets/images/hero1.svg'],
    ],

    'gold' => [
      'title' => 'Gold',
      'filters' => [
        'category' => ['Bangles', 'Bracelets', 'Chains', 'Earrings', 'Necklaces', 'Pendants', 'Rings', 'Mangalsutras', 'Nose Pins', 'Kadas'],
        'price' => ['Under ₹10,000', '₹10,000 - ₹25,000', '₹25,000 - ₹50,000', 'Above ₹50,000'],
        'occassion' => ['Daily Wear', 'Party Wear', 'Wedding'],
        'gender' => ['Women', 'Men', 'Kids'],
      ],
      'cards' => [
        ['name' => 'Bangles', 'icon' => '?', 'href' => 'pages/shop.php?category=Gold&metal=18K'],
        ['name' => 'Bracelets', 'icon' => '???', 'href' => 'pages/shop.php?category=Gold&metal=18K'],
        ['name' => 'Chains', 'icon' => '??', 'href' => 'pages/shop.php?category=Gold&metal=18K'],
        ['name' => 'Earrings', 'icon' => '?', 'href' => 'pages/shop.php?category=Gold&metal=18K'],
        ['name' => 'Necklaces', 'icon' => '??', 'href' => 'pages/shop.php?category=Gold&metal=18K'],
        ['name' => 'Pendants', 'icon' => '??', 'href' => 'pages/shop.php?category=Gold&metal=18K'],
        ['name' => 'Rings', 'icon' => '??', 'href' => 'pages/shop.php?category=Gold&metal=18K'],
        ['name' => 'Mangalsutras', 'icon' => '??', 'href' => 'pages/shop.php?category=Gold&metal=18K'],
        ['name' => 'Nose Pins', 'icon' => '??', 'href' => 'pages/shop.php?category=Gold&metal=18K'],
      ],
      'promo_banner' => ['title' => '22K Gold', 'subtitle' => 'Pure elegance, timeless value', 'cta' => 'Explore', 'href' => 'pages/shop.php?category=Gold'],
      'promo_large' => ['title' => 'Gold Jewellery', 'subtitle' => 'Crafted to perfection', 'cta' => 'Shop Now', 'href' => 'pages/shop.php?category=Gold', 'image' => 'assets/images/hero2.svg'],
    ],

    'diamond' => [
      'title' => 'Diamond',
      'filters' => [
        'category' => ['Solitaire', 'Kids', 'Mens', 'Womens'],
        'price' => ['Under ₹25,000', '₹25,000 - ₹50,000', '₹50,000 - ₹1L', 'Above ₹1L'],
        'occassion' => ['Engagement', 'Party Wear', 'Anniversary', 'Daily Wear'],
        'gender' => ['Women', 'Men', 'Kids'],
      ],
      'cards' => [
        ['name' => 'Solitaire', 'icon' => '??', 'href' => 'pages/shop.php?category=Diamond&collection=Solitaire'],
        ['name' => 'Engagement', 'icon' => '??', 'href' => 'pages/shop.php?category=Diamond&occasion=Engagement'],
        ['name' => 'Daily Wear', 'icon' => '?', 'href' => 'pages/shop.php?category=Diamond&occasion=Daily'],
        ['name' => 'Party Wear', 'icon' => '?', 'href' => 'pages/shop.php?category=Diamond&occasion=Party'],
        ['name' => 'Mens', 'icon' => '??', 'href' => 'pages/shop.php?category=Diamond&gender=male'],
        ['name' => 'Kids', 'icon' => '??', 'href' => 'pages/shop.php?category=Diamond&gender=kids'],
      ],
      'promo_banner' => ['title' => 'Lab Certified', 'subtitle' => 'IGI & GIA certified diamonds', 'cta' => 'Know More', 'href' => 'pages/shop.php?category=Diamond'],
      'promo_large' => ['title' => 'Diamond Collection', 'subtitle' => 'Sparkle with every move', 'cta' => 'Shop Now', 'href' => 'pages/shop.php?category=Diamond', 'image' => 'assets/images/hero3.svg'],
    ],

    'earrings' => [
      'title' => 'Earrings',
      'filters' => [
        'category' => ['Studs & Tops', 'Drop & Danglers', 'Hoop & Huggies', 'Jhumkas', 'Pear Drops', 'Chandelier'],
        'price' => ['Under ₹5,000', '₹5,000 - ₹15,000', '₹15,000 - ₹30,000', 'Above ₹30,000'],
        'occassion' => ['Daily Wear', 'Party Wear', 'Wedding', 'Festive'],
        'gender' => ['Women', 'Kids'],
      ],
      'cards' => [
        ['name' => 'All Earrings', 'icon' => '?', 'href' => 'pages/shop.php?category=Earrings'],
        ['name' => 'Drop & Danglers', 'icon' => '?', 'href' => 'pages/shop.php?category=Earrings&occasion=Drop'],
        ['name' => 'Hoop & Huggies', 'icon' => '?', 'href' => 'pages/shop.php?category=Earrings&collection=Hoop'],
        ['name' => 'Jhumkas', 'icon' => '?', 'href' => 'pages/shop.php?category=Earrings&occasion=Jhumka'],
        ['name' => 'Studs & Tops', 'icon' => '?', 'href' => 'pages/shop.php?category=Earrings&collection=Stud'],
      ],
      'promo_banner' => ['title' => 'Earrings For You', 'subtitle' => 'Crafted with precision', 'cta' => 'View All', 'href' => 'pages/shop.php?category=Earrings'],
      'promo_large' => ['title' => 'Singular Brilliance', 'subtitle' => 'Infinite charm', 'cta' => 'Shop Now', 'href' => 'pages/shop.php?category=Earrings', 'image' => 'assets/images/hero1.svg'],
    ],

    'rings' => [
      'title' => 'Rings',
      'filters' => [
        'category' => ['Engagement', 'Wedding', 'Daily Wear', 'Fashion', 'Casual'],
        'price' => ['Under ₹10,000', '₹10,000 - ₹25,000', '₹25,000 - ₹50,000', 'Above ₹50,000'],
        'occassion' => ['Engagement', 'Wedding', 'Party', 'Daily Wear'],
        'gender' => ['Women', 'Men'],
      ],
      'cards' => [
        ['name' => 'Engagement Rings', 'icon' => '??', 'href' => 'pages/shop.php?category=Rings&occasion=Engagement'],
        ['name' => 'Wedding Rings', 'icon' => '??', 'href' => 'pages/shop.php?category=Rings&occasion=Wedding'],
        ['name' => 'Diamond Rings', 'icon' => '??', 'href' => 'pages/shop.php?category=Rings&metal=Diamond'],
        ['name' => 'Gold Rings', 'icon' => '??', 'href' => 'pages/shop.php?category=Rings&metal=Gold'],
        ['name' => 'Gemstone', 'icon' => '??', 'href' => 'pages/shop.php?category=Rings&metal=Gemstone'],
      ],
      'promo_banner' => ['title' => 'Engagement Rings', 'subtitle' => 'Make it a moment to remember', 'cta' => 'View All', 'href' => 'pages/shop.php?category=Rings'],
      'promo_large' => ['title' => 'Ring Your Love', 'subtitle' => 'Eternity begins here', 'cta' => 'Shop Now', 'href' => 'pages/shop.php?category=Rings', 'image' => 'assets/images/Rings.png'],
    ],

    'daily-wear' => [
      'title' => 'Daily Wear',
      'filters' => [
        'category' => ['Bangles', 'Bracelets', 'Chains', 'Pendants', 'Rings', 'Earrings'],
        'price' => ['Under ₹5,000', '₹5,000 - ₹10,000', '₹10,000 - ₹25,000', 'Above ₹25,000'],
        'gender' => ['Women', 'Men'],
      ],
      'cards' => [
        ['name' => 'Daily Bangles', 'icon' => '?', 'href' => 'pages/shop.php?category=Daily%20Wear&type=bangles'],
        ['name' => 'Daily Chains', 'icon' => '??', 'href' => 'pages/shop.php?category=Daily%20Wear&type=chains'],
        ['name' => 'Daily Bracelets', 'icon' => '???', 'href' => 'pages/shop.php?category=Daily%20Wear&type=bracelets'],
        ['name' => 'Stud Earrings', 'icon' => '?', 'href' => 'pages/shop.php?category=Daily%20Wear&type=earrings'],
        ['name' => 'Pendants', 'icon' => '??', 'href' => 'pages/shop.php?category=Daily%20Wear&type=pendants'],
      ],
      'promo_banner' => ['title' => 'Everyday Elegance', 'subtitle' => 'Lightweight comfort jewellery', 'cta' => 'Explore', 'href' => 'pages/shop.php?category=Daily%20Wear'],
      'promo_large' => ['title' => 'Daily Luxury', 'subtitle' => 'Wear it every day', 'cta' => 'Shop Now', 'href' => 'pages/shop.php?category=daily%20Wear', 'image' => 'assets/images/hero2.svg'],
    ],

    'gemstone' => [
      'title' => 'Gemstone',
      'filters' => [
        'category' => ['Rings', 'Earrings', 'Pendants', 'Bangles', 'Necklaces'],
        'stone' => ['Emerald', 'Ruby', 'Sapphire', 'Pearl', 'Topaz', 'Amethyst'],
        'price' => ['Under ₹10,000', '₹10,000 - ₹25,000', 'Above ₹25,000'],
        'occassion' => ['Wedding', 'Festival', 'Party Wear'],
      ],
      'cards' => [
        ['name' => 'Emerald', 'icon' => '??', 'href' => 'pages/shop.php?category=Gemstone&stone=Emerald'],
        ['name' => 'Ruby', 'icon' => '??', 'href' => 'pages/shop.php?category=Gemstone&stone=Ruby'],
        ['name' => 'Sapphire', 'icon' => '??', 'href' => 'pages/shop.php?category=Gemstone&stone=Sapphire'],
        ['name' => 'Pearl', 'icon' => '??', 'href' => 'pages/shop.php?category=Gemstone&stone=Pearl'],
        ['name' => 'Topaz', 'icon' => '??', 'href' => 'pages/shop.php?category=Gemstone&stone=Topaz'],
      ],
      'promo_banner' => ['title' => 'Precious Gems', 'subtitle' => 'Vibrant colours, precious stones', 'cta' => 'View All', 'href' => 'pages/shop.php?category=Gemstone'],
      'promo_large' => ['title' => 'Gemstone Beauty', 'subtitle' => 'Colours of nature', 'cta' => 'Shop Now', 'href' => 'pages/shop.php?category=Gemstone', 'image' => 'assets/images/hero3.svg'],
    ],

    'wedding' => [
      'title' => 'Wedding',
      'filters' => [
        'category' => ['Engagement', 'Wedding', 'Bridal', 'Groom', 'Sangeet', 'Mehendi'],
        'price' => ['Under ₹50,000', '₹50,000 - ₹1L', '₹1L - ₹5L', 'Above ₹5L'],
        'function' => ['Engagement', 'Wedding', 'Reception', 'Sangeet'],
      ],
      'cards' => [
        ['name' => 'Engagement', 'icon' => '??', 'href' => 'pages/shop.php?category=Wedding&occasion=Engagement'],
        ['name' => 'Bridal Set', 'icon' => '??', 'href' => 'pages/shop.php?category=Wedding&type=Bridal'],
        ['name' => 'Wedding Rings', 'icon' => '??', 'href' => 'pages/shop.php?category=Wedding&occasion=Wedding'],
        ['name' => 'Maang Tikka', 'icon' => '??', 'href' => 'pages/shop.php?category=Wedding&type=Maang%20Tikka'],
        ['name' => 'Nose Ring', 'icon' => '??', 'href' => 'pages/shop.php?category=Wedding&type=Nose%20Ring'],
        ['name' => 'Bajuband', 'icon' => '???', 'href' => 'pages/shop.php?category=Wedding&type=Bajuband'],
      ],
      'promo_banner' => ['title' => 'Wedding Collection', 'subtitle' => 'For your big day', 'cta' => 'View All', 'href' => 'pages/shop.php?category=Wedding'],
      'promo_large' => ['title' => 'Forever Together', 'subtitle' => 'Celebrate your love', 'cta' => 'Shop Now', 'href' => 'pages/shop.php?category=Wedding', 'image' => 'assets/images/hero1.svg'],
    ],

    'gifting' => [
      'title' => 'Gifting',
      'filters' => [
        'category' => ['For Her', 'For Him', 'For Kids', 'For Couple'],
        'occassion' => ['Birthday', 'Anniversary', 'Festival', 'Valentine', 'Mothers Day'],
        'price' => ['Under ₹5,000', '₹5,000 - ₹15,000', '₹15,000 - ₹30,000', 'Above ₹30,000'],
      ],
      'cards' => [
        ['name' => 'For Her', 'icon' => '?', 'href' => 'pages/shop.php?category=Gifting&gender=female'],
        ['name' => 'For Him', 'icon' => '??', 'href' => 'pages/shop.php?category=Gifting&gender=male'],
        ['name' => 'For Kids', 'icon' => '??', 'href' => 'pages/shop.php?category=Gifting&gender=kids'],
        ['name' => 'For Couple', 'icon' => '??', 'href' => 'pages/shop.php?category=Gifting&type=Couple'],
        ['name' => 'Birthday Gifts', 'icon' => '??', 'href' => 'pages/shop.php?category=Gifting&occasion=Birthday'],
      ],
      'promo_banner' => ['title' => 'Gift of Love', 'subtitle' => 'Perfect for every occasion', 'cta' => 'Find Gift', 'href' => 'pages/shop.php?category=Gifting'],
      'promo_large' => ['title' => 'Gift Ideas', 'subtitle' => 'Something for everyone', 'cta' => 'Shop Now', 'href' => 'pages/shop.php?category=Gifting', 'image' => 'assets/images/hero2.svg'],
    ],
  ];
}

function mega_menu_by_slug(string $slug): ?array {
  $data = mega_menu_data();
  $key = strtolower($slug);
  return $data[$key] ?? null;
}

