<?php
// Every page includes this file first.
// It starts the session, connects to the database and holds small helper functions.
session_start();
require_once __DIR__ . '/db.php';

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
    if (!isset($_SESSION['user_id'])) {
        set_message('Please sign in to continue.');
        header('Location: login.php?next=' . urlencode($return_to));
        exit;
    }
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
    global $site_name, $site_url, $shop_email;

    $order_id = (int) $order_id;
    $order = $db->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
    $items = $db->query("SELECT * FROM order_items WHERE order_id = $order_id");

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
    $message .= "Deliver to:\n" . $order['name'] . "\n" . $order['address'] . "\nSingapore " . $order['postal_code'] . "\n\n";
    if ($order['status'] == 'Pending') {
        $message .= "Please check your order here. You can still change sizes, quantities\n";
        $message .= "or the delivery address until you confirm it:\n";
    } else {
        $message .= "View your order here:\n";
    }
    $message .= $site_url . "order.php?id=" . $order_id . "\n\n";
    $message .= "Thank you for shopping with " . $site_name . "!\n";

    $headers = 'From: ' . $shop_email . "\r\n" .
               'Reply-To: ' . $shop_email . "\r\n" .
               'X-Mailer: PHP/' . phpversion();

    // The @ hides the PHP warning if no mail server is running; we report it on the page instead.
    return @mail($order['email'], $site_name . ' - ' . $subject . ' #' . $order_id, $message, $headers, '-f' . $shop_email);
}
?>
