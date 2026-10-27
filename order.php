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
$order = $db->query("SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id")->fetch_assoc();
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

// Handle the buttons. Only a Pending order can be changed.
if ($order['status'] == 'Pending') {
    if ($action == 'cancel') {
        cancel_order($db, $order_id, 'Your order has been cancelled as requested.');
        set_message('Your order has been cancelled.');
        header('Location: order.php?id=' . $order_id);
        exit;
    }

    if ($action == 'save' || $action == 'confirm') {
        $errors = save_order_changes($db, $order_id, $_POST);

        // Reload the order to see what was saved
        $order = $db->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();

        if ($order['status'] == 'Cancelled') {
            set_message('You removed every item, so the order has been cancelled.');
            header('Location: order.php?id=' . $order_id);
            exit;
        }
        if (count($errors) == 0) {
            if ($action == 'confirm') {
                confirm_order($db, $order_id);
                set_message('Your order is confirmed. We have emailed you the final details.');
            } else {
                send_order_email($db, $order_id, 'Order updated', 'Your changes have been saved. Here is your updated order.');
                set_message('Your changes have been saved and an updated email has been sent.');
            }
            header('Location: order.php?id=' . $order_id);
            exit;
        }
    }
}

$items = $db->query("SELECT oi.*, p.image, p.colour FROM order_items oi
                     JOIN products p ON oi.product_id = p.id
                     WHERE oi.order_id = $order_id");
$can_edit = ($order['status'] == 'Pending');

$page_title = 'Order #' . $order_id;
include 'includes/header.php';
?>

<p class="breadcrumb"><a href="account.php#orders">My orders</a> / Order #<?php echo $order_id; ?></p>
<h1>Order #<?php echo $order_id; ?> <span class="status status-<?php echo strtolower($order['status']); ?>"><?php echo e($order['status']); ?></span></h1>
<p class="hint">Placed on <?php echo date('j M Y, g:i a', strtotime($order['created_at'])); ?></p>

<?php if ($can_edit) { ?>
    <p class="steps"><span>1. Bag</span> &rarr; <span>2. Delivery &amp; payment</span> &rarr; <strong>3. Check your order</strong></p>
    <div class="notice">
        <strong>Please check your order.</strong> Look at each shoe, size and quantity below.
        You can change anything, then press <em>Confirm order</em>. Once confirmed, the order cannot be changed.
    </div>
<?php } elseif ($order['status'] == 'Confirmed') { ?>
    <div class="notice success">Your order is confirmed and will be delivered in 3 to 5 working days.</div>
<?php } else { ?>
    <div class="notice">This order was cancelled. You have not been charged.</div>
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

<form action="order.php?id=<?php echo $order_id; ?>" method="post" onsubmit="return checkOrderForm();">
    <div class="order-items">
        <?php while ($item = $items->fetch_assoc()) { ?>
            <div class="order-item">
                <img src="assets/images/<?php echo e($item['image']); ?>" alt="<?php echo e($item['product_name']); ?>">
                <div>
                    <h3><a href="product.php?id=<?php echo $item['product_id']; ?>"><?php echo e($item['product_name']); ?></a></h3>
                    <p class="hint">Colour: <?php echo e($item['colour']); ?> &middot; <?php echo money($item['price']); ?> each</p>

                    <?php if ($can_edit) { ?>
                        <label>Size (EU)
                            <select name="size[<?php echo $item['id']; ?>]">
                                <?php foreach (get_sizes($db, $item['product_id']) as $s) { ?>
                                    <?php $available = $s['stock'] + ($s['id'] == $item['size_id'] ? $item['quantity'] : 0); ?>
                                    <option value="<?php echo $s['id']; ?>"
                                        <?php if ($s['id'] == $item['size_id']) echo 'selected'; ?>
                                        <?php if ($available < 1) echo 'disabled'; ?>>
                                        <?php echo $s['size']; ?><?php if ($available < 1) echo ' (sold out)'; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </label>
                        <label>Quantity
                            <input class="qty" type="number" name="quantity[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" max="10">
                        </label>
                        <label class="inline"><input type="checkbox" name="remove[<?php echo $item['id']; ?>]" value="1"> Remove this shoe</label>
                    <?php } else { ?>
                        <p>Size EU <?php echo $item['size']; ?> &times; <?php echo $item['quantity']; ?></p>
                    <?php } ?>
                    <p><strong><?php echo money($item['price'] * $item['quantity']); ?></strong></p>
                </div>
            </div>
        <?php } ?>
    </div>

    <div class="cart-layout">
        <fieldset>
            <legend>Delivery details</legend>
            <p><?php echo e($order['name']); ?><br><?php echo e($order['email']); ?></p>
            <?php if ($can_edit) { ?>
                <label for="phone">Mobile number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo e($order['phone']); ?>" maxlength="8" required>
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?php echo e($order['address']); ?>" required>
                <label for="postal_code">Postal code</label>
                <input type="text" id="postal_code" name="postal_code" value="<?php echo e($order['postal_code']); ?>" maxlength="6" required>
            <?php } else { ?>
                <p><?php echo e($order['phone']); ?><br><?php echo e($order['address']); ?><br>Singapore <?php echo e($order['postal_code']); ?></p>
            <?php } ?>
        </fieldset>

        <aside class="summary">
            <h2>Summary</h2>
            <p>Payment <span><?php echo e($order['payment_method']); ?></span></p>
            <p>Delivery <span>Free</span></p>
            <p class="summary-total">Total <span><?php echo money($order['total']); ?></span></p>
            <?php if ($can_edit) { ?>
                <p id="order-error" class="error-text"></p>
                <button class="button wide" type="submit" name="action" value="confirm">Everything is correct &ndash; confirm order</button>
                <button class="button secondary wide" type="submit" name="action" value="save">Save changes</button>
            <?php } ?>
        </aside>
    </div>
</form>

<?php if ($can_edit) { ?>
    <form action="order.php?id=<?php echo $order_id; ?>" method="post" onsubmit="return confirm('Cancel this whole order?');">
        <input type="hidden" name="action" value="cancel">
        <button class="link-button" type="submit">Cancel this order</button>
    </form>
<?php } ?>

<p><a class="text-link" href="products.php">Continue shopping</a></p>

<?php include 'includes/footer.php'; ?>
