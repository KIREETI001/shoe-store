<?php
// Connect to the MySQL database (MySQL lecture, step 2).
require_once __DIR__ . '/../config.php';

// Approved PHP 8 compatibility: keep the lecture's false-return query checks.
mysqli_report(MYSQLI_REPORT_OFF);

@ $db = new mysqli($db_host, $db_user, $db_password, $db_name, $db_port);

if (mysqli_connect_errno()) {
?>
    <h1>Sorry, the shop database is not available right now.</h1>
    <p>Check that MySQL is running and that database/schema.sql and database/seed.sql have been imported.</p>
<?php
    exit;
}
?>
