<?php
// Sign out (Sessions lecture: logout.php)
require_once 'includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] != 'POST') { header('Location: index.php'); exit; }
require_valid_request();
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
// Destroying session data alone leaves the old ID in the browser's cookie.
// XAMPP's PHPSESSID cookie uses the root path (Sessions lecture: cookie expiry).
setcookie('PHPSESSID', '', time() - 3600, '/');
session_destroy();

header('Location: index.php');
exit;
?>
