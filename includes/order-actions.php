<?php
// Pending-order actions use the signed-in owner and check every required write.
// Separate queries are not atomic. Recovery below is best effort if a later query fails.

// Release only extra reservations which this request actually made.
function restore_order_reservations($db, $reserved) {
    $restored = true;

    foreach ($reserved as $size_id => $quantity) {
        if (!$db->query(
            "UPDATE product_sizes
             SET stock = stock + $quantity
             WHERE id = $size_id"
        ) || $db->affected_rows != 1) {
            $restored = false;
        }
    }

    return $restored;
}

// Undo successful writes after a save failure, without changing recorded prices/names.
function recover_order_changes(
    $db,
    $order,
    $changed_items,
    $removed_items,
    $reserved,
    $released,
    $header_changed,
    $saved_status
) {
    $restored = true;
    $order_id = (int) $order['id'];
    $user_id = (int) $order['user_id'];

    foreach ($released as $size_id => $quantity) {
        if (!$db->query(
            "UPDATE product_sizes
             SET stock = stock - $quantity
             WHERE id = $size_id
                 AND stock >= $quantity"
        ) || $db->affected_rows != 1) {
            $restored = false;
        }
    }

    foreach ($changed_items as $item) {
        $item_id = (int) $item['id'];
        $size_id = (int) $item['size_id'];
        $size = (int) $item['size'];
        $quantity = (int) $item['quantity'];

        if (!$db->query(
            "UPDATE order_items
             SET size_id = $size_id, size = $size, quantity = $quantity
             WHERE id = $item_id
                 AND order_id = $order_id"
        ) || $db->affected_rows != 1) {
            $restored = false;
        }
    }

    foreach ($removed_items as $item) {
        $item_id = (int) $item['id'];
        $product_id = (int) $item['product_id'];
        $size_id = (int) $item['size_id'];
        $name = $db->real_escape_string($item['product_name']);
        $size = (int) $item['size'];
        $price = (float) $item['price'];
        $quantity = (int) $item['quantity'];

        if (!$db->query(
            "INSERT INTO order_items (id, order_id, product_id, size_id, product_name, size, price, quantity)
             VALUES ($item_id, $order_id, $product_id, $size_id, '$name', $size, $price, $quantity)"
        )) {
            $restored = false;
        }
    }

    $phone = $db->real_escape_string($order['phone']);
    $address = $db->real_escape_string($order['address']);
    $postal_code = $db->real_escape_string($order['postal_code']);
    $total = (float) $order['total'];

    if ($header_changed) {
        if (!$db->query(
            "UPDATE orders
             SET phone = '$phone',
                 address = '$address',
                 postal_code = '$postal_code',
                 total = $total,
                 status = 'Pending'
             WHERE id = $order_id
                 AND user_id = $user_id
                 AND status = '$saved_status'"
        ) || $db->affected_rows != 1) {
            $restored = false;
        }
    }

    if (!restore_order_reservations($db, $reserved)) {
        $restored = false;
    }

    return $restored;
}

// A repeated cancellation changes no row, so it cannot restore the stock twice.
function cancel_order($db, $order_id, $reason) {
    $order_id = (int) $order_id;
    $user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
    $result = $db->query(
        "SELECT *
         FROM order_items
         WHERE order_id = $order_id"
    );

    if (!$result) {
        return array(
            'errors' => array('The order could not be read. Please try again.'),
            'changed' => false,
            'mail_sent' => null
        );
    }

    $items = array();

    while ($item = $result->fetch_assoc()) {
        $items[] = $item;
    }

    if (!$db->query(
        "UPDATE orders
         SET status = 'Cancelled'
         WHERE id = $order_id
             AND user_id = $user_id
             AND status = 'Pending'"
    )) {
        return array(
            'errors' => array('The order could not be cancelled. Please try again.'),
            'changed' => false,
            'mail_sent' => null
        );
    }

    if ($db->affected_rows != 1) {
        return array(
            'errors' => array(),
            'changed' => false,
            'mail_sent' => null
        );
    }

    $released = array();

    foreach ($items as $item) {
        $size_id = (int) $item['size_id'];
        $quantity = (int) $item['quantity'];

        if (!$db->query(
            "UPDATE product_sizes
             SET stock = stock + $quantity
             WHERE id = $size_id"
        ) || $db->affected_rows != 1) {
            // Try to return to Pending if cancelling could not release every hold.
            $restored = true;

            foreach ($released as $released_size_id => $released_quantity) {
                if (!$db->query(
                    "UPDATE product_sizes
                     SET stock = stock - $released_quantity
                     WHERE id = $released_size_id
                         AND stock >= $released_quantity"
                ) || $db->affected_rows != 1) {
                    $restored = false;
                }
            }

            if ($restored) {
                $restored = $db->query(
                    "UPDATE orders
                     SET status = 'Pending'
                     WHERE id = $order_id
                         AND user_id = $user_id
                         AND status = 'Cancelled'"
                ) && $db->affected_rows == 1;
            }

            if ($restored) {
                $error = 'The order could not be cancelled. Please try again.';
            } else {
                $error = 'Cancellation failed and some changes could not be restored.'
                    . ' Please contact the shop before trying again.';
            }

            return array(
                'errors' => array($error),
                'changed' => false,
                'mail_sent' => null
            );
        }

        if (!isset($released[$size_id])) {
            $released[$size_id] = 0;
        }

        $released[$size_id] += $quantity;
    }

    $mail_sent = send_order_email(
        $db,
        $order_id,
        'Order cancelled',
        $reason . ' This is a demonstration order; no real payment is taken.'
    );

    return array(
        'errors' => array(),
        'changed' => true,
        'mail_sent' => $mail_sent
    );
}

function confirm_order($db, $order_id) {
    $order_id = (int) $order_id;
    $user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

    if (!$db->query(
        "UPDATE orders
         SET status = 'Confirmed'
         WHERE id = $order_id
             AND user_id = $user_id
             AND status = 'Pending'"
    )) {
        return array(
            'errors' => array('The order could not be confirmed. Please try again.'),
            'changed' => false,
            'mail_sent' => null
        );
    }

    if ($db->affected_rows != 1) {
        return array(
            'errors' => array(),
            'changed' => false,
            'mail_sent' => null
        );
    }

    $mail_sent = send_order_email(
        $db,
        $order_id,
        'Order confirmed',
        'Thank you for checking your demonstration order. It is now confirmed. No real payment is taken.'
    );

    return array(
        'errors' => array(),
        'changed' => true,
        'mail_sent' => $mail_sent
    );
}

// Returns errors and whether a complete, actual change was saved. Mail is sent by order.php.
function save_order_changes($db, $order_id, $form) {
    $errors = array();
    $order_id = (int) $order_id;
    $user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
    $result = $db->query(
        "SELECT *
         FROM orders
         WHERE id = $order_id
             AND user_id = $user_id
             AND status = 'Pending'"
    );
    $order = $result ? $result->fetch_assoc() : false;

    if (!$order) {
        return array(
            'errors' => array('This order is no longer available for changes.'),
            'changed' => false
        );
    }

    $phone = isset($form['phone']) ? $form['phone'] : '';
    $address = isset($form['address']) ? $form['address'] : '';
    $postal_code = isset($form['postal_code']) ? $form['postal_code'] : '';

    if (!is_valid_phone($phone)) {
        $errors[] = 'Please enter an 8-digit Singapore phone number starting with 6, 8 or 9.';
    }

    if (trim($address) == '' || strlen($address) > 255) {
        $errors[] = 'Please enter a delivery address of up to 255 characters.';
    }

    if (!is_valid_postal_code($postal_code)) {
        $errors[] = 'Please enter a 6-digit postal code.';
    }

    $result = $db->query(
        "SELECT *
         FROM order_items
         WHERE order_id = $order_id"
    );

    if (!$result) {
        return array(
            'errors' => array('The order items could not be read. Please try again.'),
            'changed' => false
        );
    }

    $old_items = array();
    $proposed = array();
    $old_holds = array();
    $new_demand = array();
    $sizes = array();
    $total = 0;
    $changed = $phone != $order['phone']
        || $address != $order['address']
        || $postal_code != $order['postal_code'];

    // Build the entire request before changing delivery, lines or stock.
    while ($item = $result->fetch_assoc()) {
        $item_id = (int) $item['id'];
        $old_size_id = (int) $item['size_id'];
        $old_items[$item_id] = $item;

        if (!isset($old_holds[$old_size_id])) {
            $old_holds[$old_size_id] = 0;
        }

        $old_holds[$old_size_id] += (int) $item['quantity'];

        if (isset($form['remove'][$item_id])) {
            if ($form['remove'][$item_id] !== '1') {
                $errors[] = 'Please use the Remove this shoe checkbox to remove an item.';
            }

            $changed = true;
            continue;
        }

        $new_size_id = isset($form['size'][$item_id]) ? $form['size'][$item_id] : '';
        $quantity = isset($form['quantity'][$item_id]) ? $form['quantity'][$item_id] : '';

        if (!preg_match('/^[0-9]+$/', $new_size_id) || !is_valid_quantity($quantity)) {
            $errors[] = 'Please choose a valid size and a whole-number quantity from 1 to 10 for '
                . $item['product_name'] . '.';
            continue;
        }

        $new_size_id = (int) $new_size_id;
        $quantity = (int) $quantity;
        $product_id = (int) $item['product_id'];
        $size_result = $db->query(
            "SELECT *
             FROM product_sizes
             WHERE id = $new_size_id
                 AND product_id = $product_id"
        );
        $size = $size_result ? $size_result->fetch_assoc() : false;

        if (!$size) {
            $errors[] = 'Please choose an available size of ' . $item['product_name'] . '.';
            continue;
        }

        $sizes[$new_size_id] = $size;

        if (!isset($new_demand[$new_size_id])) {
            $new_demand[$new_size_id] = 0;
        }

        $new_demand[$new_size_id] += $quantity;
        $proposed[$item_id] = array(
            'size_id' => $new_size_id,
            'size' => (int) $size['size'],
            'quantity' => $quantity
        );
        $total += $item['price'] * $quantity;

        if ($new_size_id != $old_size_id || $quantity != $item['quantity']) {
            $changed = true;
        }
    }

    foreach ($new_demand as $size_id => $quantity) {
        $held = isset($old_holds[$size_id]) ? $old_holds[$size_id] : 0;

        if ($quantity > 10) {
            $errors[] = 'Choose at most 10 pairs of the same shoe and size across the order.';
        }

        if ($quantity > $sizes[$size_id]['stock'] + $held) {
            $errors[] = 'There are not enough pairs available in EU ' . $sizes[$size_id]['size']
                . ' for that complete change.';
        }
    }

    if ($total > 999999.99) {
        $errors[] = 'The proposed total is too large. Please reduce the order.';
    }

    if (count($old_items) == 0) {
        $errors[] = 'This order has no items to change.';
    }

    if (count($errors) > 0 || !$changed) {
        return array(
            'errors' => $errors,
            'changed' => false
        );
    }

    // Reserve only the extra demand. Old holds remain in place during validation.
    $reserved = array();

    foreach ($new_demand as $size_id => $quantity) {
        $held = isset($old_holds[$size_id]) ? $old_holds[$size_id] : 0;
        $extra = $quantity - $held;

        if ($extra > 0) {
            if (!$db->query(
                "UPDATE product_sizes
                 SET stock = stock - $extra
                 WHERE id = $size_id
                     AND stock >= $extra"
            ) || $db->affected_rows != 1) {
                $restored = restore_order_reservations($db, $reserved);

                if ($restored) {
                    $error = 'Stock changed before this order could be saved. Please review the sizes and try again.';
                } else {
                    $error = 'The save failed and some stock could not be restored.'
                        . ' Please contact the shop before trying again.';
                }

                return array(
                    'errors' => array($error),
                    'changed' => false
                );
            }

            $reserved[$size_id] = $extra;
        }
    }

    $changed_items = array();
    $removed_items = array();
    $released = array();
    $header_changed = false;
    $saved = true;

    foreach ($old_items as $item_id => $item) {
        if (!isset($proposed[$item_id])) {
            if (!$db->query(
                "DELETE
                 FROM order_items
                 WHERE id = $item_id
                     AND order_id = $order_id"
            ) || $db->affected_rows != 1) {
                $saved = false;
                break;
            }

            $removed_items[] = $item;
        } else {
            $line = $proposed[$item_id];

            if ($line['size_id'] != $item['size_id'] || $line['quantity'] != $item['quantity']) {
                if (!$db->query(
                    "UPDATE order_items
                     SET size_id = {$line['size_id']}, size = {$line['size']}, quantity = {$line['quantity']}
                     WHERE id = $item_id
                         AND order_id = $order_id"
                ) || $db->affected_rows != 1) {
                    $saved = false;
                    break;
                }

                $changed_items[] = $item;
            }
        }
    }

    $status = count($proposed) == 0 ? 'Cancelled' : 'Pending';
    $s_address = $db->real_escape_string($address);

    if ($saved) {
        $saved = $db->query(
            "UPDATE orders
             SET phone = '$phone',
                 address = '$s_address',
                 postal_code = '$postal_code',
                 total = $total,
                 status = '$status'
             WHERE id = $order_id
                 AND user_id = $user_id
                 AND status = 'Pending'"
        );
        $header_changed = $saved && $db->affected_rows == 1;
        // An unchanged delivery/total legitimately affects zero rows on a size swap.
        if ($saved && $db->affected_rows == 0) {
            $check = $db->query(
                "SELECT *
                 FROM orders
                 WHERE id = $order_id
                     AND user_id = $user_id
                     AND status = 'Pending'"
            );
            $saved = $check && $check->fetch_assoc();
        }
    }

    if ($saved) {
        foreach ($old_holds as $size_id => $held) {
            $demand = isset($new_demand[$size_id]) ? $new_demand[$size_id] : 0;
            $excess = $held - $demand;

            if ($excess > 0) {
                if (!$db->query(
                    "UPDATE product_sizes
                     SET stock = stock + $excess
                     WHERE id = $size_id"
                ) || $db->affected_rows != 1) {
                    $saved = false;
                    break;
                }

                $released[$size_id] = $excess;
            }
        }
    }

    if (!$saved) {
        $restored = recover_order_changes(
            $db,
            $order,
            $changed_items,
            $removed_items,
            $reserved,
            $released,
            $header_changed,
            $status
        );

        if ($restored) {
            $error = 'The changes could not be saved. The earlier values were restored; please try again.';
        } else {
            $error = 'The save failed and some changes could not be restored.'
                . ' Please contact the shop before trying again.';
        }

        return array(
            'errors' => array($error),
            'changed' => false
        );
    }

    return array(
        'errors' => array(),
        'changed' => true
    );
}
?>
