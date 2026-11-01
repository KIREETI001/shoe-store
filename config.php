<?php
// Settings for the whole site. Change these to match your own XAMPP setup.

$site_name = 'ReStep';

// Address of the site, including the final slash, used in order emails.
// Change the port and folder if your own XAMPP setup differs.
$site_url = 'http://localhost:8000/IE4727/shoe-store/';

// MySQL connection details (XAMPP default: root with no password)
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'restep';
$db_port = 3306;

// Test mail goes only to the customer mailbox; the admin is the sender.
// Only these exact recipients may receive order mail. An empty array disables it.
$allowed_mail_recipients = array('f32ee@localhost');

// One explicit admin sender; blank or unapproved senders disable sending.
$shop_email = 'f31ee@localhost';

// Only f31ee is the administrator; f32ee is the customer test account.
// This list controls access separately from permission to receive local mail.
$admin_emails = array('f31ee@localhost');

// Optional settings for this computer only (not included in the submission)
if (file_exists(__DIR__ . '/config.local.php')) {
    include __DIR__ . '/config.local.php';
}
?>
