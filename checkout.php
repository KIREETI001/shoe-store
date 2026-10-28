<?php
// Checkout: delivery details + payment, then save the order and email the customer.
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

// Start with the details saved in the customer's account
$user_id = (int) $_SESSION['user_id'];
$user = $db->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$name = $user['name'];
$email = $user['email'];
$phone = $user['phone'];
$address = $user['address'];
$postal_code = $user['postal_code'];
$payment = 'Card';
$errors = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $postal_code = trim($_POST['postal_code']);
    $payment = isset($_POST['payment']) ? $_POST['payment'] : '';

    // Check every field (the same checks also run in JavaScript before submitting)
    if ($name == '') {
        $errors[] = 'Please enter your name.';
    }
    if (!preg_match('/^[^@\s]+@[^@\s]+$/', $email)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (!preg_match('/^[689][0-9]{7}$/', $phone)) {
        $errors[] = 'Please enter an 8-digit Singapore phone number.';
    }
    if ($address == '') {
        $errors[] = 'Please enter your delivery address.';
    }
    if (!preg_match('/^[0-9]{6}$/', $postal_code)) {
        $errors[] = 'Please enter a 6-digit postal code.';
    }
    if ($payment == 'Card') {
        // Demo only: the card details are checked for format and then thrown away, never saved.
        $card_number = str_replace(' ', '', $_POST['card_number']);
        if (!preg_match('/^[0-9]{16}$/', $card_number)) {
            $errors[] = 'Please enter the 16-digit card number.';
        }
        if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', trim($_POST['card_expiry']))) {
            $errors[] = 'Please enter the card expiry date as MM/YY.';
        }
        if (!preg_match('/^[0-9]{3}$/', trim($_POST['card_cvv']))) {
            $errors[] = 'Please enter the 3-digit security code.';
        }
    } elseif ($payment != 'Cash on delivery') {
        $errors[] = 'Please choose a payment method.';
    }

    // Check the stock again in case someone else bought the last pair
    foreach ($items as $item) {
        if ($item['quantity'] > $item['stock']) {
            $errors[] = 'Sorry, only ' . $item['stock'] . ' pairs of ' . $item['name'] . ' in EU ' . $item['size'] . ' are left. Please update your bag.';
        }
    }

    if (count($errors) == 0) {
        // Save the order
        $s_name = $db->real_escape_string($name);
        $s_email = $db->real_escape_string($email);
        $s_address = $db->real_escape_string($address);
        $payment_label = ($payment == 'Card') ? 'Card ending ' . substr($card_number, -4) : 'Cash on delivery';

        $db->query("INSERT INTO orders (user_id, name, email, phone, address, postal_code, payment_method, total)
                    VALUES ($user_id, '$s_name', '$s_email', '$phone', '$s_address', '$postal_code', '$payment_label', $total)");
        $order_id = $db->insert_id;

        // Save each shoe in the order and take it out of stock
        foreach ($items as $item) {
            $s_product = $db->real_escape_string($item['name']);
            $db->query("INSERT INTO order_items (order_id, product_id, size_id, product_name, size, price, quantity)
                        VALUES ($order_id, {$item['product_id']}, {$item['size_id']}, '$s_product', {$item['size']}, {$item['price']}, {$item['quantity']})");
            $db->query("UPDATE product_sizes SET stock = stock - {$item['quantity']} WHERE id = {$item['size_id']}");
        }

        // Remember these details in the account for next time
        $db->query("UPDATE users SET phone = '$phone', address = '$s_address', postal_code = '$postal_code' WHERE id = $user_id");

        // Empty the bag, send the email and show the order page
        $_SESSION['cart'] = array();
        $sent = send_order_email($db, $order_id, 'Order received',
            'Thank you for your order! Please check that everything below is correct.');
        if ($sent) {
            set_message('Thank you! Your order is placed and a confirmation email has been sent to ' . $email . '.');
        } else {
            set_message('Thank you! Your order is placed. (The confirmation email could not be sent - is the mail server running?)');
        }
        header('Location: order.php?id=' . $order_id);
        exit;
    }
}

$page_title = 'Checkout';
include 'includes/header.php';
?>

<h1>Checkout</h1>
<p class="steps"><span>1. Bag</span> &rarr; <strong>2. Delivery &amp; payment</strong> &rarr; <span>3. Check your order</span></p>

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
    <form id="checkout-form" action="checkout.php" method="post" onsubmit="return checkCheckoutForm();">
        <fieldset>
            <legend>Delivery details</legend>
            <label for="name">Full name <span class="required">*</span></label>
            <input type="text" id="name" name="name" value="<?php echo e($name); ?>" required>

            <label for="email">Email (we send your order confirmation here) <span class="required">*</span></label>
            <input type="email" id="email" name="email" value="<?php echo e($email); ?>" required>

            <label for="phone">Mobile number <span class="required">*</span> </label>
            <input type="tel" id="phone" name="phone" value="<?php echo e($phone); ?>" placeholder="e.g. 91234567" maxlength="8" required>

            <label for="address">Address <span class="required">*</span></label>
            <input type="text" id="address" name="address" value="<?php echo e($address); ?>" placeholder="Block, street, unit number" required>

            <label for="postal_code">Postal code <span class="required">*</span></label>
            <input type="text" id="postal_code" name="postal_code" value="<?php echo e($postal_code); ?>" placeholder="e.g. 639798" maxlength="6" required>
        </fieldset>

        <fieldset>
            <legend>Payment</legend>
            <label class="inline"><input type="radio" name="payment" value="Card" onclick="showCardFields(true)" <?php if ($payment == 'Card') echo 'checked'; ?>> Credit / debit card</label>
            <label class="inline"><input type="radio" name="payment" value="Cash on delivery" onclick="showCardFields(false)" <?php if ($payment == 'Cash on delivery') echo 'checked'; ?>> Cash on delivery</label>

            <div id="card-fields" <?php if ($payment == 'Cash on delivery') echo 'style="display:none"'; ?>>
                <p class="hint">This is a demo shop: use a made-up number such as 4111 1111 1111 1111. Card details are never saved.</p>
                <label for="card_number">Card number <span class="required">*</span></label>
                <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19" autocomplete="off">
                <div class="two-columns">
                    <div>
                        <label for="card_expiry">Expiry (MM/YY) <span class="required">*</span></span></label>
                        <input type="text" id="card_expiry" name="card_expiry" placeholder="08/28" maxlength="5" autocomplete="off">
                    </div>
                    <div>
                        <label for="card_cvv">Security code <span class="required">*</span></label>
                        <input type="text" id="card_cvv" name="card_cvv" placeholder="123" maxlength="3" autocomplete="off">
                    </div>
                </div>
            </div>
        </fieldset>

        <p id="checkout-error" class="error-text"></p>
        <button class="button wide" type="submit">Place order &ndash; <?php echo money($total); ?></button>
        <p class="hint">After placing your order you can still check it and change sizes, quantities or your address before confirming.</p>
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

<script>
document.addEventListener("DOMContentLoaded", function () {
    const nameInput = document.getElementById("name");
    const phoneInput = document.getElementById("phone");
    const postalInput = document.getElementById("postal_code");
    
    // Card elements
    const cardNumberInput = document.getElementById("card_number");
    const cardExpiryInput = document.getElementById("card_expiry");
    const cardCvvInput = document.getElementById("card_cvv");

    // 1. FULL NAME VALIDATOR (Letters and Spaces only)
    if (nameInput) {
        nameInput.addEventListener("input", function (e) {
            const cleaned = this.value.replace(/[^a-zA-Z\s\-']/g, "");
            if (this.value !== cleaned) {
                this.value = cleaned;
                showLiveWarning(this, "Letters and spaces only (no numbers or symbols)");
            } else {
                clearLiveWarning(this);
            }
        });
    }

    // 2. MOBILE NUMBER VALIDATOR
    if (phoneInput) {
        phoneInput.addEventListener("input", function (e) {
            const cleaned = this.value.replace(/[^0-9]/g, "");
            if (this.value !== cleaned) {
                this.value = cleaned;
                showLiveWarning(this, "Numbers only (Singapore mobile format: 6, 8, or 9)");
            } else {
                clearLiveWarning(this);
            }
        });
    }

    // 3. POSTAL CODE VALIDATOR
    if (postalInput) {
        postalInput.addEventListener("input", function (e) {
            const cleaned = this.value.replace(/[^0-9]/g, "");
            if (this.value !== cleaned) {
                this.value = cleaned;
                showLiveWarning(this, "Numbers only (6-digit postal code)");
            } else {
                clearLiveWarning(this);
            }
        });
    }

    // 4. CARD NUMBER VALIDATOR (Numbers and Spaces only)
    if (cardNumberInput) {
        cardNumberInput.addEventListener("input", function (e) {
            const cleaned = this.value.replace(/[^0-9\s]/g, "");
            if (this.value !== cleaned) {
                this.value = cleaned;
                showLiveWarning(this, "Numbers and spaces only");
            } else {
                clearLiveWarning(this);
            }
        });
    }

    // 5. CARD EXPIRY VALIDATOR (Numbers and Slash only)
    if (cardExpiryInput) {
        cardExpiryInput.addEventListener("input", function (e) {
            const cleaned = this.value.replace(/[^0-9\/]/g, "");
            if (this.value !== cleaned) {
                this.value = cleaned;
                showLiveWarning(this, "Format MM/YY only");
            } else {
                clearLiveWarning(this);
            }
        });
    }

    // 6. CARD SECURITY CODE/CVV VALIDATOR (Numbers only)
    if (cardCvvInput) {
        cardCvvInput.addEventListener("input", function (e) {
            const cleaned = this.value.replace(/[^0-9]/g, "");
            if (this.value !== cleaned) {
                this.value = cleaned;
                showLiveWarning(this, "3-digit numeric security code only");
            } else {
                clearLiveWarning(this);
            }
        });
    }
});

function showLiveWarning(inputElement, message) {
    let warning = inputElement.nextElementSibling;
    
    if (!warning || !warning.classList.contains("input-warning")) {
        warning = document.createElement("span");
        warning.className = "input-warning";
        warning.style.color = "#cf4421"; // Matches your ReStep orange/red theme
        warning.style.fontSize = "12px";
        warning.style.fontWeight = "bold";
        warning.style.display = "block";
        warning.style.marginTop = "4px";
        warning.style.marginBottom = "8px";
        
        inputElement.parentNode.insertBefore(warning, inputElement.nextSibling);
    }
    warning.textContent = message;
}

function clearLiveWarning(inputElement) {
    const warning = inputElement.nextElementSibling;
    if (warning && warning.classList.contains("input-warning")) {
        warning.remove();
    }
}
</script>


<?php include 'includes/footer.php'; ?>
