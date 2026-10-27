<?php
// Settings for the whole site. Change these to match your own XAMPP setup.

$site_name = 'ReStep';

// Address of the site, used for the link inside order emails
$site_url = 'http://localhost/shoe-store/';

// MySQL connection details (XAMPP default: root with no password)
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'restep';
$db_port = 3306;

// Order emails are sent FROM this address (the lab Mercury account)
$shop_email = 'f31ee@localhost';

// Optional settings for this computer only (not included in the submission)
if (file_exists(__DIR__ . '/config.local.php')) {
    include __DIR__ . '/config.local.php';
}
?>
