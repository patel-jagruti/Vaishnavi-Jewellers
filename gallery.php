<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Diamond Images Gallery</title>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #fafafa; padding: 20px; }
        h1 { text-align: center; color: #333; }
        .folder-section { margin-bottom: 40px; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .folder-title { font-size: 24px; color: #6B1E2D; border-bottom: 2px solid #D4AF37; padding-bottom: 8px; margin-bottom: 20px; text-transform: uppercase; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .card { background: #fff; border: 1px solid #eee; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; align-items: center; padding: 15px; }
        .card img { max-width: 100%; height: 200px; object-fit: contain; border-radius: 4px; }
        .card-name { margin-top: 12px; font-size: 11px; color: #888; text-align: center; word-break: break-all; }
        .card-real-name { margin-top: 8px; font-size: 14px; color: #333; text-align: center; font-weight: 600; font-family: 'Poppins', sans-serif; }
        .card-price { margin-top: 6px; font-size: 16px; color: #6B1E2D; font-weight: 700; }
        .card-carat { margin-top: 4px; font-size: 12px; color: #a77a42; font-weight: 600; background: #FFF8F0; padding: 2px 10px; border-radius: 20px; border: 1px solid #E8C547; }
    </style>
</head>
<body>
    <h1>Vaishnavi Jewellers - Diamond Catalog Images</h1>
    <?php
    $catalog = [
        // Bracelets
        'WhatsApp Image 2026-07-04 at 7.30.51 PM' => ['name' => 'Classic Diamond Tennis Bracelet', 'price' => '₹1,85,000', 'carat' => '2.50 Carat'],
        'WhatsApp Image 2026-07-04 at 7.30.52 PM' => ['name' => 'Elegant Floral Diamond Bracelet', 'price' => '₹1,45,000', 'carat' => '1.80 Carat'],
        'WhatsApp Image 2026-07-04 at 7.30.53 PM (1)' => ['name' => 'Luxury Rose Gold Diamond Bracelet', 'price' => '₹2,10,000', 'carat' => '3.00 Carat'],
        'WhatsApp Image 2026-07-04 at 7.30.53 PM' => ['name' => 'Sparkling Diamond Bangle Bracelet', 'price' => '₹1,95,000', 'carat' => '2.20 Carat'],
        'WhatsApp Image 2026-07-04 at 7.30.54 PM' => ['name' => 'Princess Cut Diamond Link Bracelet', 'price' => '₹2,40,000', 'carat' => '3.50 Carat'],
        'WhatsApp Image 2026-07-04 at 7.30.55 PM (1)' => ['name' => 'Dainty Diamond Chain Bracelet', 'price' => '₹95,000', 'carat' => '0.85 Carat'],
        'WhatsApp Image 2026-07-04 at 7.30.55 PM' => ['name' => 'Infinity Link Diamond Bracelet', 'price' => '₹1,25,000', 'carat' => '1.20 Carat'],
        'WhatsApp Image 2026-07-04 at 7.30.56 PM (1)' => ['name' => 'Vintage Diamond Filigree Bracelet', 'price' => '₹1,65,000', 'carat' => '1.50 Carat'],
        'WhatsApp Image 2026-07-04 at 7.30.56 PM' => ['name' => 'Modern Minimalist Diamond Cuff', 'price' => '₹1,10,000', 'carat' => '1.00 Carat'],

        // Necklaces
        'DIAMOND NECKLACE' => ['name' => 'Rose Gold Delicate V-Shape Diamond Necklace Set', 'price' => '₹4,50,000', 'carat' => '5.20 Carat'],
        'WhatsApp Image 2026-06-28 at 1.14.58 PM' => ['name' => 'Crescent Moon Diamond Pendant Necklace', 'price' => '₹65,000', 'carat' => '0.50 Carat'],
        'WhatsApp Image 2026-06-28 at 1.15.00 PM (1)' => ['name' => 'Pear-Shaped Solitaire Diamond Drop Tennis Necklace', 'price' => '₹5,80,000', 'carat' => '7.50 Carat'],
        'WhatsApp Image 2026-06-28 at 1.15.00 PM' => ['name' => 'Oval-Cut Diamond Double Halo Pendant Necklace', 'price' => '₹1,25,000', 'carat' => '1.25 Carat'],
        'WhatsApp Image 2026-06-28 at 1.15.02 PM (1)' => ['name' => 'Natural Pearl and Diamond Floral Bridal Necklace', 'price' => '₹6,20,000', 'carat' => '6.80 Carat'],
        'WhatsApp Image 2026-06-28 at 1.22.04 PM' => ['name' => '18K Gold Linked Diamond Flower Necklace with Pear Drop', 'price' => '₹3,90,000', 'carat' => '4.50 Carat'],
        'download (15)' => ['name' => 'Royal Gold Emerald and Diamond Statement Collar', 'price' => '₹8,50,000', 'carat' => '10.20 Carat'],
        'download (16)' => ['name' => 'Platinum Marquise Diamond Foliage Necklace with Pear Drop', 'price' => '₹9,80,000', 'carat' => '12.50 Carat'],

        // Earrings
        'Diamond Hoop Earrings _ Everyday Jewelry' => ['name' => 'Yellow Gold Diamond Huggie Hoop Earrings', 'price' => '₹45,000', 'carat' => '0.40 Carat'],
        'Elegant Gold Diamond Hoop Earrings _ Wink of Gold' => ['name' => 'Yellow Gold Micro-Pave Diamond Hoop Earrings', 'price' => '₹75,000', 'carat' => '0.80 Carat'],
        'WhatsApp Image 2026-06-28 at 3.06.30 PM' => ['name' => 'Diamond Teardrop Dangle Earrings', 'price' => '₹1,20,000', 'carat' => '1.50 Carat'],
        'WhatsApp Image 2026-06-28 at 3.06.40 PM' => ['name' => 'Sparkling Diamond Cluster Studs', 'price' => '₹85,000', 'carat' => '1.00 Carat'],
        'WhatsApp Image 2026-06-28 at 3.08.52 PM' => ['name' => 'Rose Gold Diamond Drop Earrings', 'price' => '₹98,000', 'carat' => '1.20 Carat'],

        // Diamond Sets
        '9f536355-9d5f-4be8-bfce-fe0b5be43990' => ['name' => 'Yellow Gold Cascading Teardrop Diamond Necklace Set', 'price' => '₹5,50,000', 'carat' => '6.00 Carat'],
        'Conjunto de Joias Luxuoso com Diamantes Brilhantes' => ['name' => 'Luxury Complete Diamond Bridal Set', 'price' => '₹8,90,000', 'carat' => '9.50 Carat'],
        'WhatsApp Image 2026-06-28 at 1.15.01 PM' => ['name' => 'Sleek Diamond Floral Necklace & Three-Tier Earrings Set', 'price' => '₹4,80,000', 'carat' => '5.00 Carat'],
        'WhatsApp Image 2026-06-28 at 1.15.02 PM' => ['name' => 'Royal Emerald-Cut Blue Sapphire & Diamond Jewelry Set', 'price' => '₹7,50,000', 'carat' => '8.20 Carat'],
        'WhatsApp Image 2026-06-28 at 10.40.26 PM' => ['name' => 'Delicate Silver Leaf & Diamond Flower Jewelry Set', 'price' => '₹3,20,000', 'carat' => '3.80 Carat'],
        'download (11)' => ['name' => 'Gold Arched Links & Leaf Spray Diamond Necklace Set', 'price' => '₹3,80,000', 'carat' => '4.20 Carat'],
        '✨ A picture is worth a thousand words—make yours unforgettable_ ✨' => ['name' => 'Ruby/Pink Gemstone and Diamond Foliage Necklace Set', 'price' => '₹5,10,000', 'carat' => '5.50 Carat'],

        // Pendant Sets
        'DIAMOND PENDANT' => ['name' => 'Classic Diamond Solitaire Pendant Necklace', 'price' => '₹6,000', 'carat' => '0.50 Carat'],
        'Dazzling Pink Ruby Gemstone Butterfly Design Pendant Necklace, Fancy Pear Cut Gemstone Pendant, Daily Outfit Pendant,14k Gold Plated Pendant' => ['name' => 'Dazzling Pink Ruby Gemstone Butterfly Pendant Set', 'price' => '₹1,45,000', 'carat' => '1.50 Carat'],
        'De Beers Jewellers _ Diamonds - Marina Sobovenko' => ['name' => 'De Beers Classic Diamond Pendant Necklace', 'price' => '₹1,80,000', 'carat' => '1.80 Carat'],
        'WhatsApp Image 2026-06-28 at 1.14.57 PM (1)' => ['name' => 'Floral Diamond Heart Pendant Set', 'price' => '₹1,10,000', 'carat' => '1.10 Carat'],
        'WhatsApp Image 2026-06-28 at 1.14.57 PM' => ['name' => 'Infinity Symbol Diamond Pendant Set', 'price' => '₹85,000', 'carat' => '0.80 Carat'],
        'WhatsApp Image 2026-06-28 at 1.14.58 PM' => ['name' => 'Teardrop Blue Sapphire Diamond Pendant Set', 'price' => '₹1,65,000', 'carat' => '1.75 Carat'],
        'WhatsApp Image 2026-06-28 at 1.15.00 PM' => ['name' => 'Oval Cut Emerald Diamond Pendant Set', 'price' => '₹1,75,000', 'carat' => '1.90 Carat'],
        'download (10)' => ['name' => 'Graceful Swan Diamond Pendant Necklace', 'price' => '₹95,000', 'carat' => '0.90 Carat'],
        'download (7)' => ['name' => 'Miniature Diamond Key Pendant Necklace', 'price' => '₹70,000', 'carat' => '0.60 Carat'],
        'download (8)' => ['name' => 'Princess Cut Diamond Halo Pendant Set', 'price' => '₹1,30,000', 'carat' => '1.30 Carat'],
        'download (9)' => ['name' => 'Lucky Clover Diamond Pendant Necklace', 'price' => '₹80,000', 'carat' => '0.75 Carat'],

        // Rings
        'WhatsApp Image 2026-06-28 at 1.22.29 PM' => ['name' => 'Yellow Gold V-Shape Diamond Wishbone Ring', 'price' => '₹55,000', 'carat' => '0.50 Carat'],
        'WhatsApp Image 2026-06-28 at 1.22.30 PM' => ['name' => 'Yellow Gold Cross-Over Diamond Infinity Band', 'price' => '₹75,000', 'carat' => '0.75 Carat'],
        'WhatsApp Image 2026-06-28 at 1.22.31 PM' => ['name' => 'Three-Stone Diamond Past Present Future Ring', 'price' => '₹1,20,000', 'carat' => '1.20 Carat'],
        'WhatsApp Image 2026-06-28 at 1.22.32 PM' => ['name' => 'Sparkling Princess Cut Diamond Ring', 'price' => '₹95,000', 'carat' => '0.90 Carat'],
        'WhatsApp Image 2026-06-28 at 1.22.33 PM (1)' => ['name' => 'Infinity Band Diamond Stacking Ring', 'price' => '₹45,000', 'carat' => '0.40 Carat'],
        'WhatsApp Image 2026-06-28 at 1.22.33 PM' => ['name' => 'Vintage Floral Diamond Engagement Ring', 'price' => '₹1,35,000', 'carat' => '1.30 Carat'],
        'WhatsApp Image 2026-06-28 at 1.22.34 PM (1)' => ['name' => 'Rose Gold Diamond Stackable Band', 'price' => '₹38,000', 'carat' => '0.30 Carat'],
        'WhatsApp Image 2026-06-28 at 1.22.34 PM' => ['name' => 'Emerald Cut Diamond Halo Engagement Ring', 'price' => '₹1,85,000', 'carat' => '1.80 Carat'],
        'WhatsApp Image 2026-06-28 at 1.22.36 PM' => ['name' => 'Marquise Cut Diamond Solitaire Ring', 'price' => '₹2,20,000', 'carat' => '2.00 Carat'],

        // Couple Rings
        'WhatsApp Image 2026-06-28 at 1.22.34 PM (2)' => ['name' => 'Yellow Gold Diamond Halo Engagement Ring & Eternity Band Set', 'price' => '₹1,65,000', 'carat' => '1.70 Carat'],
        'WhatsApp Image 2026-06-28 at 1.22.35 PM (1)' => ['name' => 'His & Hers Sapphire and Diamond Couple Bands', 'price' => '₹1,95,000', 'carat' => '2.00 Carat'],
        'WhatsApp Image 2026-06-28 at 1.22.35 PM (2)' => ['name' => 'Two-Tone Rose Gold & Platinum Diamond Couple Bands', 'price' => '₹1,80,000', 'carat' => '1.80 Carat'],
        'WhatsApp Image 2026-06-28 at 1.22.35 PM (3)' => ['name' => 'Sleek Platinum Diamond Solitaire Twist Couple Rings', 'price' => '₹1,50,000', 'carat' => '1.50 Carat'],
        'WhatsApp Image 2026-06-28 at 1.22.35 PM' => ['name' => 'His & Hers Emerald and Diamond Couple Bands', 'price' => '₹2,10,000', 'carat' => '2.10 Carat'],

        // Tops
        'WhatsApp Image 2026-06-28 at 1.14.56 PM (1)' => ['name' => 'Rose Gold Triangular Diamond Floral Stud Earrings', 'price' => '₹65,000', 'carat' => '0.60 Carat'],
        'WhatsApp Image 2026-06-28 at 1.14.56 PM' => ['name' => 'Gold Arch Emerald and Diamond Stud Earrings', 'price' => '₹85,000', 'carat' => '0.85 Carat'],
        'WhatsApp Image 2026-06-28 at 2.42.08 PM' => ['name' => 'Pear-Cut Blue Sapphire and Diamond Drop Earrings', 'price' => '₹1,10,000', 'carat' => '1.10 Carat'],
        'WhatsApp Image 2026-06-28 at 3.07.58 PM' => ['name' => 'Heart-Shaped Solitaire Diamond Stud Earrings', 'price' => '₹95,000', 'carat' => '0.90 Carat'],
    ];

    $baseDir = 'assets/images/diamond';
    if (is_dir($baseDir)) {
        $folders = scandir($baseDir);
        foreach ($folders as $folder) {
            if ($folder === '.' || $folder === '..') continue;
            $folderPath = $baseDir . '/' . $folder;
            if (is_dir($folderPath)) {
                echo "<div class='folder-section'>";
                echo "<div class='folder-title'>Folder: " . htmlspecialchars($folder) . "</div>";
                echo "<div class='grid'>";
                $files = scandir($folderPath);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') continue;
                    $filePath = $folderPath . '/' . $file;
                    if (is_file($filePath)) {
                        $webPath = 'assets/images/diamond/' . rawurlencode($folder) . '/' . rawurlencode($file);
                        
                        $name = 'Diamond Jewel Item';
                        $price = '₹85,000';
                        $carat = '0.90 Carat';
                        
                        $fileKey = pathinfo($file, PATHINFO_FILENAME);
                        if (isset($catalog[$fileKey])) {
                            $name = $catalog[$fileKey]['name'];
                            $price = $catalog[$fileKey]['price'];
                            $carat = $catalog[$fileKey]['carat'];
                        }
                        
                        echo "<div class='card'>";
                        echo "<img src='" . htmlspecialchars($webPath) . "' alt='jewellery'>";
                        echo "<div class='card-real-name'>" . htmlspecialchars($name) . "</div>";
                        echo "<div class='card-price'>" . htmlspecialchars($price) . "</div>";
                        echo "<div class='card-carat'>" . htmlspecialchars($carat) . "</div>";
                        echo "<div class='card-name'>File: " . htmlspecialchars($file) . "</div>";
                        echo "</div>";
                    }
                }
                echo "</div>";
                echo "</div>";
            }
        }
    } else {
        echo "<p style='text-align:center;color:red;'>Directory $baseDir not found.</p>";
    }
    ?>
</body>
</html>
