<?php
// Every page includes this file first.
// It starts the session, connects to the database and holds small helper functions.
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/validation.php';

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

// Expiration runs on site traffic; timestamps use the database clock.
function expire_pending_orders($db) {
    if (!$db->begin_transaction()) return;
    try {
        $expired = $db->query("SELECT id FROM orders WHERE status = 'Pending'
            AND created_at <= DATE_SUB(NOW(), INTERVAL 30 MINUTE) ORDER BY id FOR UPDATE");
        if (!$expired) throw new Exception('Could not check reservations.');
        while ($order = $expired->fetch_assoc()) {
            $id = (int) $order['id'];
            $items = $db->query("SELECT size_id, SUM(quantity) AS quantity FROM order_items
                WHERE order_id = $id GROUP BY size_id ORDER BY size_id");
            if (!$items) throw new Exception('Could not read reservations.');
            while ($item = $items->fetch_assoc()) {
                $size = (int) $item['size_id']; $quantity = (int) $item['quantity'];
                if (!$db->query("UPDATE product_sizes SET stock = stock + $quantity WHERE id = $size")
                    || $db->affected_rows != 1) throw new Exception('Could not release reservations.');
            }
            if (!$db->query("UPDATE orders SET status = 'Cancelled' WHERE id = $id AND status = 'Pending'")
                || $db->affected_rows != 1) throw new Exception('Could not expire order.');
        }
        if (!$db->commit()) throw new Exception('Could not commit expiration.');
    } catch (Throwable $error) { $db->rollback(); }
}

expire_pending_orders($db);

// The shopping cart lives in the session: $_SESSION['cart'][size_id] = quantity
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Make text safe to print inside HTML
function e($text) {
    return htmlspecialchars($text);
}

// Format a price, e.g. 102 -> S$102.00
function money($amount) {
    return 'S$' . number_format($amount, 2);
}

function cart_snapshot($items) {
    $lines = array();
    foreach ($items as $item) {
        $lines[(int) $item['size_id']] = array((int) $item['quantity'], number_format((float) $item['price'], 2, '.', ''));
    }
    ksort($lines);
    return hash_hmac('sha256', json_encode($lines), csrf_token());
}

// Total number of pairs in the cart (shown on the Bag link)
function cart_count() {
    return array_sum($_SESSION['cart']);
}

// Show a one-time message on the next page, e.g. "Added to your bag"
function set_message($text) {
    $_SESSION['message'] = $text;
}

// Send the visitor to the sign-in page if they are not logged in
function require_login($return_to) {
    global $db;
    if (isset($_SESSION['user_id'])) {
        $id = (int) $_SESSION['user_id'];
        $result = $db->query("SELECT id FROM users WHERE id = $id");
        if (!$result || !$result->fetch_assoc()) {
            unset($_SESSION['user_id'], $_SESSION['user_name']);
        }
    }
    if (!isset($_SESSION['user_id'])) {
        set_message('Please sign in to continue.');
        header('Location: login.php?next=' . urlencode($return_to));
        exit;
    }
}

// Stop malformed requests before a handler can change any saved data.
function require_valid_request($array_fields = array()) {
    global $db, $site_name;

    if ($_SERVER['REQUEST_METHOD'] == 'POST'
        && (!isset($_SESSION['csrf_token']) || !is_string(post_value('csrf_token'))
            || !hash_equals($_SESSION['csrf_token'], post_value('csrf_token')))) {
        http_response_code(403);
        $page_title = $heading = 'Please Reload The Form';
        $text = 'This form has expired or could not be verified, possibly because you signed in in another tab. Nothing was saved. Reopen the form below to continue.';
        $page = basename($_SERVER['SCRIPT_NAME']);
        $link = in_array($page, array('login.php', 'account.php', 'checkout.php', 'contact.php', 'cart.php', 'admin.php', 'order.php'), true) ? $page : 'index.php';
        if ($page == 'order.php' && valid_id(get_value('id'))) $link .= '?id=' . urlencode(get_value('id'));
        if ($page == 'login.php') {
            $next = post_value('next', get_value('next', 'account.php'));
            if (preg_match('/^(account|checkout|order|admin)\.php(\?id=[0-9]+)?$/D', $next)) $link .= '?next=' . urlencode($next);
        }
        if ($page == 'contact.php') $link .= '#contact-us';
        $link_text = 'Reopen This Form';
        include __DIR__ . '/not-found.php';
        exit;
    }

    if (!valid_request_fields($_GET, array()) || !valid_request_fields($_POST, $array_fields)) {
        http_response_code(400);
        $page_title = 'Please Check The Form';
        $heading = 'The Form Could Not Be Read.';
        $text = 'Please return to the site and enter one value in each field. Nothing was saved.';
        $link = 'index.php';
        $link_text = 'Back To Home';
        include __DIR__ . '/not-found.php';
        exit;
    }
}

// Admin access comes from the saved account, never from a posted role or email.
function is_admin_user($db) {
    global $admin_emails;

    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    $user_id = (int) $_SESSION['user_id'];
    $result = $db->query("SELECT email, is_admin FROM users WHERE id = $user_id");
    $user = $result ? $result->fetch_assoc() : false;

    if (!$user || $user['is_admin'] != 1) {
        return false;
    }

    foreach ($admin_emails as $email) {
        if ($user['email'] === $email) {
            return true;
        }
    }

    return false;
}

// Read the cart from the session and look up each item's details in the database.
// Only IDs and quantities are kept in the session; prices always come from MySQL.
function get_cart_items($db) {
    $items = array();
    foreach ($_SESSION['cart'] as $size_id => $quantity) {
        $size_id = (int) $size_id;
        $query = "SELECT s.id AS size_id, s.size, s.stock, p.id AS product_id,
                         p.name, p.colour, p.price, p.image
                  FROM product_sizes s JOIN products p ON s.product_id = p.id
                  WHERE s.id = $size_id";
        $result = $db->query($query);
        $row = $result->fetch_assoc();
        if ($row) {
            $row['quantity'] = $quantity;
            $row['subtotal'] = $row['price'] * $quantity;
            $items[] = $row;
        }
    }
    return $items;
}

// All sizes of one product, smallest first
function get_sizes($db, $product_id) {
    $product_id = (int) $product_id;
    $result = $db->query("SELECT * FROM product_sizes WHERE product_id = $product_id ORDER BY size");
    $sizes = array();
    while ($row = $result->fetch_assoc()) {
        $sizes[] = $row;
    }
    return $sizes;
}

// Email the customer a summary of their order with a link to check and change it.
// (Sessions lecture: Sending Emails.) Returns true if the mail server accepted it.
function send_order_email($db, $order_id, $subject, $intro) {
    global $site_name, $site_url, $shop_email, $allowed_mail_recipients, $admin_emails;

    if (isset($GLOBALS['order_mail_queue'])) {
        $GLOBALS['order_mail_queue'][] = array($order_id, $subject, $intro);
        return true;
    }

    $order_id = (int) $order_id;
    $result = $db->query("SELECT * FROM orders WHERE id = $order_id");

    if (!$result) {
        return false;
    }

    $order = $result->fetch_assoc();

    // The brief permits only the student's own local account, not every
    // address ending in @localhost. Do not call mail() for another recipient.
    if (!$order) {
        return false;
    }

    $recipient = '';

    foreach ($allowed_mail_recipients as $allowed_address) {
        if ($allowed_address == trim($allowed_address)
            && preg_match('/^[\w.-]+@localhost$/', $allowed_address)
            && $order['email'] == $allowed_address) {
            $recipient = $allowed_address;
        }
    }

    if ($recipient == '') {
        return false;
    }

    $sender = $shop_email;

    if ($sender == '' || count($admin_emails) != 1 || $sender !== $admin_emails[0]) {
        return false;
    }

    if ($sender != trim($sender) || !preg_match('/^[\w.-]+@localhost$/', $sender)) {
        return false;
    }

    $items = $db->query("SELECT * FROM order_items WHERE order_id = $order_id");

    if (!$items) {
        return false;
    }

    $message = "Hi " . $order['name'] . ",\n\n";
    $message .= $intro . "\n\n";
    $message .= "Order #" . $order_id . " (" . $order['status'] . ")\n";
    $message .= "----------------------------------------\n";
    while ($item = $items->fetch_assoc()) {
        $message .= $item['product_name'] . ", EU " . $item['size'] . " x " . $item['quantity']
                  . "  " . money($item['price'] * $item['quantity']) . "\n";
    }
    $message .= "----------------------------------------\n";
    $message .= "Total: " . money($order['total']) . "\n";
    $message .= "Payment: " . $order['payment_method'] . "\n\n";
    $message .= "Deliver to:\n" . $order['name'] . "\n" . $order['address']
              . "\nSingapore " . $order['postal_code'] . "\n\n";
    if ($order['status'] == 'Pending') {
        $message .= "Please check your order here. You can still change sizes, quantities\n";
        $message .= "or the delivery address until you confirm it:\n";
    } else {
        $message .= "View your order here:\n";
    }
    $message .= $site_url . "order.php?id=" . $order_id . "\n\n";
    $message .= "Thank you for shopping with " . $site_name . "!\n";

    $headers = 'From: ' . $sender . "\r\n" .
               'Reply-To: ' . $sender . "\r\n" .
               'X-Mailer: PHP/' . phpversion();

    // The @ hides the PHP warning if no mail server is running; we report it on the page instead.
    return @mail(
        $recipient,
        $site_name . ' - ' . $subject . ' #' . $order_id,
        $message,
        $headers,
        '-f' . $sender
    );
}
?>
