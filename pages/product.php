<?php
// Redirect all product.php page requests to shop.php with open query parameter
header("Location: /visnavi jwallers/pages/shop.php" . (isset($_GET['id']) ? "?open=" . (int)$_GET['id'] : ""));
exit;
?>