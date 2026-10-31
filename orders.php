<?php
declare(strict_types=1);

// Redirect to the premium orders page under the pages/ directory, keeping query parameters (like order codes)
$queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: pages/orders.php' . $queryString);
exit;