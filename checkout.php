<?php
// Validate delivery and demo card details before reserving stock or saving data.
require_once 'includes/functions.php';
require_login('checkout.php');

$items = get_cart_items($db);

if (count($items) == 0) {
    header('Location: cart.php');
    exit;
}

$total = 0;

foreach ($items as $item) {
    $total += $item['subtotal'];
}

$user_id = (int) $_SESSION['user_id'];
$user = $db->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

if (!$user) {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    set_message('Please sign in again before checking out.');
    header('Location: login.php?next=checkout.php');
    exit;
}

$name = $user['name'];
$email = $user['email'];
$phone = $user['phone'];
$address = $user['address'];
$postal_code = $user['postal_code'];
$payment = 'Card';
$errors = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $address = isset($_POST['address']) ? $_POST['address'] : '';
    $postal_code = isset($_POST['postal_code']) ? $_POST['postal_code'] : '';
    $payment = isset($_POST['payment']) ? $_POST['payment'] : '';

    // The email stays with the signed-in account. Ignore any posted replacement.
    $email = $user['email'];

    if (!is_valid_name($name)) {
        $errors[] = 'Enter a name of up to 100 letters and spaces, with at least one letter.';
    }

    if (!is_valid_email($email)) {
        $errors[] = 'Your account does not have a valid email address.';
    }

    if (!is_valid_phone($phone)) {
        $errors[] = 'Please enter an 8-digit Singapore phone number.';
    }

    if (trim($address) == '' || strlen($address) > 255) {
        $errors[] = 'Please enter a delivery address of up to 255 characters.';
    }

    if (!is_valid_postal_code($postal_code)) {
        $errors[] = 'Please enter a 6-digit postal code.';
    }

    $card_number = '';

    if ($payment == 'Card') {
        $card_number = isset($_POST['card_number']) ? $_POST['card_number'] : '';
        $card_expiry = isset($_POST['card_expiry']) ? $_POST['card_expiry'] : '';
        $card_cvv = isset($_POST['card_cvv']) ? $_POST['card_cvv'] : '';
        $card_number_length = strlen($card_number);
        $card_number = str_replace(' ', '', $card_number);

        // Demo only: do not save full card numbers, expiry dates or security codes.
        if ($card_number_length > 19 || strlen($card_number) != 16
            || !preg_match('/^[0-9]{16}$/', $card_number)) {
            $errors[] = 'Please enter the 16-digit card number.';
        }

        if (!is_valid_expiry($card_expiry)) {
            $errors[] = 'Please enter a current or future expiry month as MM/YY.';
        }

        if (!preg_match('/^[0-9]{3}$/', $card_cvv) || strlen($card_cvv) != 3) {
            $errors[] = 'Please enter the 3-digit security code.';
        }
    } elseif ($payment != 'Cash on delivery') {
        $errors[] = 'Please choose a payment method.';
    }

    // Validate the complete bag before the first stock or order write.
    foreach ($items as $item) {
        if (!is_valid_quantity($item['quantity'])) {
            $errors[] = 'Please choose a quantity from 1 to 10 for each size in your bag.';
        } elseif ($item['quantity'] > $item['stock']) {
            $errors[] = 'Sorry, only ' . $item['stock'] . ' pairs of '
                      . $item['name'] . ' in EU ' . $item['size']
                      . ' are left. Please update your bag.';
        }
    }

    $reserved = array();
    $order_id = 0;

    if (count($errors) == 0) {
        // Each conditional UPDATE protects one size against a competing purchase.
        foreach ($items as $item) {
            $size_id = (int) $item['size_id'];
            $quantity = (int) $item['quantity'];
            $saved = $db->query("UPDATE product_sizes
                                 SET stock = stock - $quantity
                                 WHERE id = $size_id AND stock >= $quantity");

            if (!$saved || $db->affected_rows != 1) {
                $errors[] = 'Stock changed while checking out. Please review your bag and try again.';
                break;
            }

            $reserved[$size_id] = $quantity;
        }
    }

    if (count($errors) == 0) {
        $safe_name = $db->real_escape_string($name);
        $safe_email = $db->real_escape_string($email);
        $safe_address = $db->real_escape_string($address);
        $payment_label = 'Cash on delivery';

        if ($payment == 'Card') {
            $payment_label = 'Card ending ' . substr($card_number, -4);
        }

        $saved = $db->query("INSERT INTO orders
                            (user_id, name, email, phone, address, postal_code, payment_method, total)
                            VALUES
                            ($user_id, '$safe_name', '$safe_email', '$phone',
                             '$safe_address', '$postal_code', '$payment_label', $total)");

        if ($saved) {
            $order_id = (int) $db->insert_id;
        } else {
            $errors[] = 'Your order could not be saved. Your bag has been kept.';
        }
    }

    if (count($errors) == 0) {
        foreach ($items as $item) {
            $product_id = (int) $item['product_id'];
            $size_id = (int) $item['size_id'];
            $size = (int) $item['size'];
            $quantity = (int) $item['quantity'];
            $price = $item['price'];
            $safe_product = $db->real_escape_string($item['name']);

            $saved = $db->query("INSERT INTO order_items
                                (order_id, product_id, size_id, product_name, size, price, quantity)
                                VALUES
                                ($order_id, $product_id, $size_id, '$safe_product',
                                 $size, $price, $quantity)");

            if (!$saved) {
                $errors[] = 'An order item could not be saved. Your bag has been kept.';
                break;
            }
        }
    }

    if (count($errors) == 0) {
        $saved = $db->query("UPDATE users
                             SET phone = '$phone', address = '$safe_address',
                                 postal_code = '$postal_code'
                             WHERE id = $user_id");

        if (!$saved) {
            $errors[] = 'Your delivery details could not be saved. Your bag has been kept.';
        }
    }

    if (count($errors) > 0 && count($reserved) > 0) {
        // Best-effort recovery with taught queries. Separate writes are not atomic.
        $restore_stock = true;

        if ($order_id > 0) {
            $removed_items = $db->query("DELETE FROM order_items WHERE order_id = $order_id");

            if ($removed_items) {
                $removed_order = $db->query("DELETE FROM orders
                                             WHERE id = $order_id AND user_id = $user_id");
                $restore_stock = $removed_order;
            } else {
                $restore_stock = false;
            }
        }

        if ($restore_stock) {
            foreach ($reserved as $size_id => $quantity) {
                $restored = $db->query("UPDATE product_sizes
                                        SET stock = stock + $quantity
                                        WHERE id = $size_id");

                if (!$restored || $db->affected_rows != 1) {
                    $errors[] = 'Stock recovery was incomplete. Please contact the shop before retrying.';
                    break;
                }
            }
        } else {
            $errors[] = 'Order recovery was incomplete. Please contact the shop before retrying.';
        }
    }

    if (count($errors) == 0) {
        $_SESSION['cart'] = array();
        $sent = send_order_email(
            $db,
            $order_id,
            'Order received',
            'Thank you for your order! Please check that everything below is correct.'
        );

        if ($sent) {
            set_message('Your order is placed. Its acknowledgement was accepted by the local mail server.');
        } else {
            set_message('Your order is placed, but no acknowledgement email was accepted. '
                      . 'Only the two approved local accounts may receive mail; check the account and Mercury setup.');
        }

        header('Location: order.php?id=' . $order_id);
        exit;
    }
}

$page_title = 'Checkout';
include 'includes/header.php';
?>

<h1>Checkout</h1>
<p class="steps">
    <span>1. Bag</span> &rarr;
    <strong>2. Delivery &amp; payment</strong> &rarr;
    <span>3. Check your order</span>
</p>

<?php if (count($errors) > 0) { ?>
    <div class="error">
        <p><strong>Please fix the following:</strong></p>
        <ul>
            <?php foreach ($errors as $error) { ?>
                <li><?php echo e($error); ?></li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>

<div class="cart-layout">
    <form id="checkout-form" action="checkout.php" method="post">
        <fieldset>
            <legend>Delivery details</legend>
            <label for="name">Full name <span class="required">*</span></label>
            <input type="text" id="name" name="name" value="<?php echo e($name); ?>" maxlength="100" required>

            <label for="email">Account email (local order acknowledgement) <span class="required">*</span></label>
            <input type="email" id="email" name="email" value="<?php echo e($email); ?>" readonly>
            <p class="hint">Order email is sent only to f31ee@localhost or f32ee@localhost.</p>

            <label for="phone">Mobile number <span class="required">*</span> </label>
            <input type="tel" id="phone" name="phone" value="<?php echo e($phone); ?>"
                   placeholder="e.g. 91234567" maxlength="8" required>

            <label for="address">Address <span class="required">*</span></label>
            <input type="text" id="address" name="address" value="<?php echo e($address); ?>"
                   placeholder="Block, street, unit number" maxlength="255" required>

            <label for="postal_code">Postal code <span class="required">*</span></label>
            <input type="text" id="postal_code" name="postal_code" value="<?php echo e($postal_code); ?>"
                   placeholder="e.g. 639798" maxlength="6" required>
        </fieldset>

        <fieldset>
            <legend>Payment</legend>
            <label class="inline">
                <input type="radio" name="payment" value="Card"
                       <?php if ($payment == 'Card') echo 'checked'; ?>>
                Credit / debit card
            </label>
            <label class="inline">
                <input type="radio" name="payment" value="Cash on delivery"
                       <?php if ($payment == 'Cash on delivery') echo 'checked'; ?>>
                Cash on delivery
            </label>

            <div id="card-fields" <?php if ($payment == 'Cash on delivery') echo 'style="display:none"'; ?>>
                <p class="hint">
                    This is a demo shop: use a made-up number such as 4111 1111 1111 1111.
                    Card details are never saved.
                </p>
                <label for="card_number">Card number <span class="required">*</span></label>
                <input type="text" id="card_number" name="card_number"
                       placeholder="1234 5678 9012 3456" maxlength="19" autocomplete="off">
                <div class="two-columns">
                    <div>
                        <label for="card_expiry">Expiry (MM/YY) <span class="required">*</span></label>
                        <input type="text" id="card_expiry" name="card_expiry"
                               placeholder="08/28" maxlength="5" autocomplete="off">
                    </div>
                    <div>
                        <label for="card_cvv">Security code <span class="required">*</span></label>
                        <input type="text" id="card_cvv" name="card_cvv"
                               placeholder="123" maxlength="3" autocomplete="off">
                    </div>
                </div>
            </div>
        </fieldset>

        <p id="checkout-error" class="error-text"></p>
        <button class="button wide" type="submit">Place order &ndash; <?php echo money($total); ?></button>
        <p class="hint">
            After placing your order you can still check it and change sizes,
            quantities or your address before confirming.
        </p>
    </form>

    <aside class="summary">
        <h2>Your order</h2>
        <?php foreach ($items as $item) { ?>
            <div class="summary-item">
                <img src="assets/images/<?php echo e($item['image']); ?>" alt="">
                <div>
                    <strong><?php echo e($item['name']); ?></strong><br>
                    EU <?php echo $item['size']; ?> &times; <?php echo $item['quantity']; ?><br>
                    <?php echo money($item['subtotal']); ?>
                </div>
            </div>
        <?php } ?>
        <p>Delivery <span>Free</span></p>
        <p class="summary-total">Total <span><?php echo money($total); ?></span></p>
        <a class="text-link" href="cart.php">Edit bag</a>
    </aside>
</div>

<?php include 'includes/footer.php'; ?>
