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

// The two local Mercury accounts confirmed for this project team.
// Only these exact recipients may receive order mail. An empty array disables it.
$allowed_mail_recipients = array(
    'f31ee@localhost',
    'f32ee@localhost'
);

// Optional local sender. An empty value uses the matched permitted recipient.
$shop_email = '';

// These team accounts can use Admin after signing in with their saved password.
// This list controls access separately from permission to receive local mail.
$admin_emails = array(
    'f31ee@localhost',
    'f32ee@localhost'
);

// Optional settings for this computer only (not included in the submission)
if (file_exists(__DIR__ . '/config.local.php')) {
    include __DIR__ . '/config.local.php';
}
?>
