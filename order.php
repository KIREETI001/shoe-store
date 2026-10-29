<?php
// Order page: the link in the order email opens this page.
// While an order is "Pending" the customer can check it, change sizes, quantities
// or the delivery address, then confirm it. After that it can no longer be changed.
require_once 'includes/functions.php';
require_once 'includes/order-actions.php';

$order_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
require_login('order.php?id=' . $order_id);
$user_id = (int) $_SESSION['user_id'];

// Customers may only open their own orders
$result = $db->query(
    "SELECT *
     FROM orders
     WHERE id = $order_id
         AND user_id = $user_id"
);
$order = $result ? $result->fetch_assoc() : false;

if (!$order) {
    $page_title = 'Order not found';
    $heading = 'We could not find that order.';
    $text = 'Make sure you are signed in with the account you used to order.';
    $link = 'account.php#orders';
    $link_text = 'See my orders';
    include 'includes/not-found.php';
    exit;
}

$errors = array();
$action = isset($_POST['action']) ? $_POST['action'] : '';

if (isset($_POST['remove_item_id'])) {
    $remove_item_id = $_POST['remove_item_id'];

    if (!is_string($remove_item_id) || !preg_match('/^[1-9][0-9]*$/', $remove_item_id)) {
        $errors[] = 'Please choose a valid order item to remove.';
    } else {
        $remove_item_id = (int) $remove_item_id;
        $remove_result = $db->query(
            "SELECT id
             FROM order_items
             WHERE id = $remove_item_id
                 AND order_id = $order_id"
        );

        if (!$remove_result || !$remove_result->fetch_assoc()) {
            $errors[] = 'That item could not be found in this order.';
        } else {
            $_POST['remove'] = array($remove_item_id => '1');
            $action = 'save';
        }
    }
}

// Handle the buttons. Only a Pending order can be changed.
if ($order['status'] == 'Pending') {
    if ($action == 'cancel') {
        $result = cancel_order($db, $order_id, 'Your order has been cancelled as requested.');
        $errors = $result['errors'];

        if (count($errors) == 0) {
            $message = $result['changed']
                ? 'Your order has been cancelled.'
                : 'This order has already been processed.';

            if ($result['mail_sent'] === true) {
                $message .= ' The local mail server accepted the cancellation email.';
            } elseif ($result['mail_sent'] === false) {
                $message .= ' The email could not be sent. You can review the order here.';
            }
            set_message($message);
            header('Location: order.php?id=' . $order_id);
            exit;
        }
    }

    if (($action == 'save' || $action == 'confirm') && count($errors) == 0) {
        $result = save_order_changes($db, $order_id, $_POST);
        $errors = $result['errors'];
        $changed = $result['changed'];

        // Reload with the owner condition before reporting the actual saved state.
        $read_result = $db->query(
            "SELECT *
             FROM orders
             WHERE id = $order_id
                 AND user_id = $user_id"
        );
        $saved_order = $read_result ? $read_result->fetch_assoc() : false;

        if ($saved_order) {
            $order = $saved_order;
        } else {
            $errors[] = 'The order could not be read after saving. Please reload this page.';
        }

        if (count($errors) == 0) {
            $mail_sent = null;

            if ($order['status'] == 'Cancelled' && $changed) {
                $message = 'You removed every item, so the order has been cancelled.';
                $mail_sent = send_order_email(
                    $db,
                    $order_id,
                    'Order cancelled',
                    'You removed every item, so your demonstration order has been cancelled. No real payment is taken.'
                );
            } elseif ($action == 'confirm') {
                $result = confirm_order($db, $order_id);
                $errors = $result['errors'];
                $mail_sent = $result['mail_sent'];
                $message = $result['changed']
                    ? 'Your order is confirmed.'
                    : 'This order has already been processed.';
            } elseif ($changed) {
                $message = 'Your changes have been saved.';
                $mail_sent = send_order_email(
                    $db,
                    $order_id,
                    'Order updated',
                    'Your changes have been saved. Here is your updated demonstration order. No real payment is taken.'
                );
            } else {
                $message = 'There were no changes to save.';
            }

            if (count($errors) == 0) {
                if ($mail_sent === true) {
                    $message .= ' The local mail server accepted the email.';
                } elseif ($mail_sent === false) {
                    $message .= ' The email could not be sent. You can review the order here.';
                }
                set_message($message);
                header('Location: order.php?id=' . $order_id);
                exit;
            }
        }
    }

    if ($action != '' && $action != 'cancel' && $action != 'save' && $action != 'confirm') {
        $errors[] = 'Please use one of the order buttons to continue.';
    }
}

// A failed cancellation/recovery can have changed status, so show the stored state.
if (count($errors) > 0) {
    $read_result = $db->query(
        "SELECT *
         FROM orders
         WHERE id = $order_id
             AND user_id = $user_id"
    );
    $saved_order = $read_result ? $read_result->fetch_assoc() : false;

    if ($saved_order) {
        $order = $saved_order;
    }
}

$items = $db->query(
    "SELECT oi.*, p.image, p.colour
     FROM order_items oi
     JOIN products p ON oi.product_id = p.id
     WHERE oi.order_id = $order_id"
);
$can_edit = ($order['status'] == 'Pending');
$held_by_size = array();
// A different line's existing hold may be reused when sizes are swapped.
$held_result = $db->query(
    "SELECT *
     FROM order_items
     WHERE order_id = $order_id"
);

if ($held_result) {
    while ($held_item = $held_result->fetch_assoc()) {
        $held_size_id = (int) $held_item['size_id'];

        if (!isset($held_by_size[$held_size_id])) {
            $held_by_size[$held_size_id] = 0;
        }

        $held_by_size[$held_size_id] += (int) $held_item['quantity'];
    }
}

if (!$items || !$held_result) {
    $errors[] = 'The order items could not be read. Please reload this page.';
    $can_edit = false;
}

$page_title = 'Order #' . $order_id;
include 'includes/header.php';
?>

<p class="breadcrumb"><a href="account.php#orders">My orders</a> / Order #<?php echo $order_id; ?></p>
<h1>
    Order #<?php echo $order_id; ?>
    <span class="status status-<?php echo strtolower($order['status']); ?>"><?php echo e($order['status']); ?></span>
</h1>
<p class="hint">Placed on <?php echo date('j M Y, g:i a', strtotime($order['created_at'])); ?></p>

<?php if ($can_edit) { ?>
    <p class="steps">
        <span>1. Bag</span> &rarr; <span>2. Delivery &amp; payment</span>
        &rarr; <strong>3. Check your order</strong>
    </p>
    <div class="notice">
        <strong>Please check your order.</strong> Look at each shoe, size and quantity below.
        You can change anything, then press <em>Confirm order</em>. Once confirmed, the order cannot be changed.
    </div>
<?php } elseif ($order['status'] == 'Confirmed') { ?>
    <div class="notice success">Your demonstration order is confirmed. No real payment is taken.</div>
<?php } elseif ($order['status'] == 'Cancelled') { ?>
    <div class="notice">This demonstration order was cancelled. No real payment is taken.</div>
<?php } else { ?>
    <div class="notice">Your order is currently unavailable for changes.</div>
<?php } ?>

<?php if (count($errors) > 0) { ?>
    <div class="error">
        <ul>
            <?php foreach ($errors as $error) { ?>
                <li><?php echo e($error); ?></li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>

<form id="order-form" action="order.php?id=<?php echo $order_id; ?>" method="post">
    <div class="order-items">
        <table class="order-items-table">
            <thead>
                <tr>
                    <th scope="col">Shoe</th>
                    <th scope="col">Size (EU)</th>
                    <th class="order-quantity-cell" scope="col">Quantity</th>
                    <th class="order-price-cell" scope="col">Unit price</th>
                    <th class="order-price-cell" scope="col">Subtotal</th>
                    <?php if ($can_edit) { ?>
                        <th class="order-remove-cell" scope="col">Remove</th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($items && ($item = $items->fetch_assoc())) { ?>
                    <tr>
                        <td>
                            <div class="order-product">
                                <img src="assets/images/<?php echo e($item['image']); ?>"
                                    alt="<?php echo e($item['product_name']); ?>">
                                <div>
                                    <a href="product.php?id=<?php echo $item['product_id']; ?>">
                                        <strong><?php echo e($item['product_name']); ?></strong>
                                    </a>
                                    <div class="hint"><?php echo e($item['colour']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($can_edit) { ?>
                                <select name="size[<?php echo $item['id']; ?>]"
                                    aria-label="Size for <?php echo e($item['product_name']); ?>">
                                    <?php foreach (get_sizes($db, $item['product_id']) as $s) { ?>
                                        <?php
                                        $available = $s['stock']
                                            + (isset($held_by_size[$s['id']]) ? $held_by_size[$s['id']] : 0);
                                        ?>
                                        <option value="<?php echo $s['id']; ?>"
                                            <?php if ($s['id'] == $item['size_id']) echo 'selected'; ?>
                                            <?php if ($available < 1) echo 'disabled'; ?>>
                                            <?php echo $s['size']; ?><?php if ($available < 1) echo ' (sold out)'; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            <?php } else { ?>
                                <?php echo $item['size']; ?>
                            <?php } ?>
                        </td>
                        <td class="order-quantity-cell">
                            <?php if ($can_edit) { ?>
                                <input id="order-quantity-<?php echo $item['id']; ?>" class="qty" type="number"
                                    name="quantity[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity']; ?>"
                                    min="1" max="10" step="1" required
                                    aria-label="Quantity for <?php echo e($item['product_name']); ?>">
                            <?php } else { ?>
                                <?php echo $item['quantity']; ?>
                            <?php } ?>
                        </td>
                        <td class="order-price-cell"><?php echo money($item['price']); ?></td>
                        <td class="order-price-cell"><strong><?php echo money($item['price'] * $item['quantity']); ?></strong></td>
                        <?php if ($can_edit) { ?>
                        <td class="order-remove-cell">
                            <button type="submit"
                                    name="remove_item_id"
                                    value="<?php echo $item['id']; ?>"
                                    class="order-remove-button"
                                    onclick="return confirm('Remove <?php echo e($item['product_name']); ?> from your order?');">
                                ✕ Remove
                            </button>
                        </td>
                    <?php } ?>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="cart-layout">
        <fieldset>
            <legend>Delivery details</legend>
            <p><?php echo e($order['name']); ?><br><?php echo e($order['email']); ?></p>
            <?php if ($can_edit) { ?>
                <label for="phone">Mobile number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo e($order['phone']); ?>"
                    maxlength="8" pattern="[689][0-9]{7}" required>
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?php echo e($order['address']); ?>"
                    maxlength="255" required>
                <label for="postal_code">Postal code</label>
                <input type="text" id="postal_code" name="postal_code" value="<?php echo e($order['postal_code']); ?>"
                    maxlength="6" pattern="[0-9]{6}" required>
            <?php } else { ?>
                <p>
                    <?php echo e($order['phone']); ?><br>
                    <?php echo e($order['address']); ?><br>
                    Singapore <?php echo e($order['postal_code']); ?>
                </p>
            <?php } ?>
        </fieldset>

        <aside class="summary">
            <h2>Summary</h2>
            <p>Payment <span><?php echo e($order['payment_method']); ?></span></p>
            <p>Delivery <span>Free</span></p>
            <p class="summary-total">Total <span><?php echo money($order['total']); ?></span></p>
            <?php if ($can_edit) { ?>
                <p id="order-error" class="error-text"></p>
                <button class="button wide" type="submit" name="action" value="confirm">
                    Everything is correct &ndash; confirm order
                </button>
                <button class="button secondary wide" type="submit" name="action" value="save">Save changes</button>
            <?php } ?>
        </aside>
    </div>
</form>

<?php if ($can_edit) { ?>
    <form action="order.php?id=<?php echo $order_id; ?>" method="post"
        onsubmit="return confirm('Cancel this whole order?');">
        <input type="hidden" name="action" value="cancel">
        <button class="link-button" type="submit">Cancel this order</button>
    </form>
<?php } ?>

<p><a class="text-link" href="products.php">Continue shopping</a></p>

<?php include 'includes/footer.php'; ?>
