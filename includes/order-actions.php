<?php
// The three things a customer can do to a Pending order on order.php.

// Cancel the whole order and put every pair back into stock
function cancel_order($db, $order_id, $reason) {
    $items = $db->query("SELECT * FROM order_items WHERE order_id = $order_id");
    while ($item = $items->fetch_assoc()) {
        $db->query("UPDATE product_sizes SET stock = stock + {$item['quantity']} WHERE id = {$item['size_id']}");
    }
    $db->query("UPDATE orders SET status = 'Cancelled' WHERE id = $order_id");
    send_order_email($db, $order_id, 'Order cancelled', $reason . ' You have not been charged.');
}

// Lock the order so it can no longer be changed
function confirm_order($db, $order_id) {
    $db->query("UPDATE orders SET status = 'Confirmed' WHERE id = $order_id");
    send_order_email($db, $order_id, 'Order confirmed', 'Thank you for checking your order. It is now confirmed and will be delivered in 3 to 5 working days.');
}

// Save new delivery details, sizes and quantities from the order form.
// Returns a list of problems (an empty list means everything was saved).
function save_order_changes($db, $order_id, $form) {
    $errors = array();
    $phone = trim($form['phone']);
    $address = trim($form['address']);
    $postal_code = trim($form['postal_code']);

    if (!preg_match('/^[689][0-9]{7}$/', $phone)) {
        $errors[] = 'Please enter an 8-digit Singapore phone number.';
    }
    if ($address == '') {
        $errors[] = 'Please enter your delivery address.';
    }
    if (!preg_match('/^[0-9]{6}$/', $postal_code)) {
        $errors[] = 'Please enter a 6-digit postal code.';
    }
    if (count($errors) > 0) {
        return $errors;
    }

    $s_address = $db->real_escape_string($address);
    $db->query("UPDATE orders SET phone = '$phone', address = '$s_address', postal_code = '$postal_code' WHERE id = $order_id");

    // Go through each shoe in the order one at a time
    $items = $db->query("SELECT * FROM order_items WHERE order_id = $order_id");
    while ($item = $items->fetch_assoc()) {
        $item_id = $item['id'];
        $old_size_id = $item['size_id'];
        $old_quantity = $item['quantity'];
        $new_size_id = isset($form['size'][$item_id]) ? (int) $form['size'][$item_id] : $old_size_id;
        $new_quantity = isset($form['quantity'][$item_id]) ? (int) $form['quantity'][$item_id] : $old_quantity;

        // Step 1: put the old pairs back into stock
        $db->query("UPDATE product_sizes SET stock = stock + $old_quantity WHERE id = $old_size_id");

        // Removed by the customer: delete the line and move on
        if (isset($form['remove'][$item_id])) {
            $db->query("DELETE FROM order_items WHERE id = $item_id");
            continue;
        }

        // Step 2: check the chosen size (it must be the same shoe) and take the new pairs out of stock
        $new_size = $db->query("SELECT * FROM product_sizes WHERE id = $new_size_id AND product_id = {$item['product_id']}")->fetch_assoc();
        if ($new_size && $new_quantity >= 1 && $new_quantity <= 10 && $new_quantity <= $new_size['stock']) {
            $db->query("UPDATE product_sizes SET stock = stock - $new_quantity WHERE id = $new_size_id");
            $db->query("UPDATE order_items SET size_id = $new_size_id, size = {$new_size['size']}, quantity = $new_quantity WHERE id = $item_id");
        } else {
            // Not possible: keep the old choice and tell the customer why
            $db->query("UPDATE product_sizes SET stock = stock - $old_quantity WHERE id = $old_size_id");
            if ($new_size && $new_quantity > $new_size['stock']) {
                $errors[] = 'Only ' . $new_size['stock'] . ' pairs of ' . $item['product_name'] . ' in EU ' . $new_size['size'] . ' are available, so that change was not made.';
            } else {
                $errors[] = 'Please choose a quantity from 1 to 10 for ' . $item['product_name'] . '.';
            }
        }
    }

    // Work out the new total. If every shoe was removed, the order is cancelled.
    $row = $db->query("SELECT SUM(price * quantity) AS total, COUNT(*) AS item_count FROM order_items WHERE order_id = $order_id")->fetch_assoc();
    if ($row['item_count'] == 0) {
        $db->query("UPDATE orders SET total = 0, status = 'Cancelled' WHERE id = $order_id");
        send_order_email($db, $order_id, 'Order cancelled', 'You removed every item, so your order has been cancelled. You have not been charged.');
    } else {
        $db->query("UPDATE orders SET total = {$row['total']} WHERE id = $order_id");
    }

    return $errors;
}
?>
