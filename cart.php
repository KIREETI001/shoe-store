<?php
// Shopping cart (Sessions lecture: A Simple Shopping Cart).
// $_SESSION['cart'] stores size_id => quantity.
require_once 'includes/functions.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';
// Only the Update form contains arrays; Add sends one size and one quantity.
$array_fields = $action === 'update' ? array('size', 'quantity') : array();
require_valid_request($array_fields);

// 1. Add a shoe from the product page
if ($action == 'add') {
    $size_id = isset($_POST['size_id']) ? $_POST['size_id'] : '';
    $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : '';

    if (!preg_match('/^[0-9]+$/', $size_id) || !is_valid_quantity($quantity)) {
        set_message('Please choose a size and a whole-number quantity from 1 to 10. Your bag was not changed.');
        header('Location: products.php');
        exit;
    }

    $size_id = (int) $size_id;
    $quantity = (int) $quantity;

    $result = $db->query(
        "SELECT s.stock, s.size, s.product_id, p.name
         FROM product_sizes s
         JOIN products p ON s.product_id = p.id
         WHERE s.id = $size_id"
    );
    $row = $result ? $result->fetch_assoc() : false;

    if (!$row) {
        set_message('Please choose a size before adding to your bag.');
        header('Location: products.php');
        exit;
    }

    // Add to what is already in the bag for this size
    $already = isset($_SESSION['cart'][$size_id]) ? $_SESSION['cart'][$size_id] : 0;
    $new_quantity = $already + $quantity;

    if ($new_quantity > 10) {
        set_message('Your bag can contain at most 10 pairs of the same shoe and size. Your bag was not changed.');
        header('Location: product.php?id=' . $row['product_id']);
        exit;
    }

    if ($new_quantity > $row['stock']) {
        set_message(
            'Sorry, only ' . $row['stock'] . ' pairs of ' . $row['name']
            . ' in EU ' . $row['size'] . ' are left.'
        );
        header('Location: product.php?id=' . $row['product_id']);
        exit;
    }

    $_SESSION['cart'][$size_id] = $new_quantity;
    set_message($row['name'] . ' (EU ' . $row['size'] . ') was added to your bag.');
    header('Location: cart.php');
    exit;
}

// 2. Save changes made in the bag table (new sizes and quantities)
if ($action == 'update') {
    $new_cart = array();
    $problems = array();

    foreach ($_SESSION['cart'] as $old_size_id => $old_quantity) {
        $new_size_id = isset($_POST['size'][$old_size_id]) ? $_POST['size'][$old_size_id] : '';
        $quantity = isset($_POST['quantity'][$old_size_id]) ? $_POST['quantity'][$old_size_id] : '';

        if (!preg_match('/^[0-9]+$/', $new_size_id) || !is_valid_quantity($quantity)) {
            $problems[] = 'Please choose a valid size and a whole-number quantity from 1 to 10 for every shoe.';
            continue;
        }

        $old_size_id = (int) $old_size_id;
        $new_size_id = (int) $new_size_id;
        $quantity = (int) $quantity;

        // Make sure the new size belongs to the same shoe
        $old_result = $db->query(
            "SELECT product_id
             FROM product_sizes
             WHERE id = $old_size_id"
        );
        $new_result = $db->query(
            "SELECT s.product_id, s.stock, s.size, p.name
             FROM product_sizes s
             JOIN products p ON s.product_id = p.id
             WHERE s.id = $new_size_id"
        );
        $old_row = $old_result ? $old_result->fetch_assoc() : false;
        $new_row = $new_result ? $new_result->fetch_assoc() : false;

        if (!$old_row || !$new_row || $old_row['product_id'] != $new_row['product_id']) {
            $problems[] = 'A chosen size is no longer available for that shoe.';
            continue;
        }

        if (isset($new_cart[$new_size_id])) {
            $quantity += $new_cart[$new_size_id];   // same size chosen twice: join them
        }

        if ($quantity > 10) {
            $problems[] = 'Choose at most 10 pairs of ' . $new_row['name']
                . ' in EU ' . $new_row['size'] . ' across the bag.';
        }

        if ($quantity > $new_row['stock']) {
            $problems[] = 'Only ' . $new_row['stock'] . ' left of ' . $new_row['name']
                . ' in EU ' . $new_row['size'] . '.';
        }

        $new_cart[$new_size_id] = $quantity;
    }

    if (count($problems) > 0) {
        set_message(implode(' ', $problems) . ' Your bag was not changed.');
    } else {
        $_SESSION['cart'] = $new_cart;
        set_message('Your bag has been updated.');
    }
    header('Location: cart.php');
    exit;
}

// 3. Remove one item, or empty the whole bag (links use GET, like the lecture example)
if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][(int) $_GET['remove']]);
    set_message('The item was removed from your bag.');
    header('Location: cart.php');
    exit;
}

if (isset($_GET['empty'])) {
    $_SESSION['cart'] = array();
    header('Location: cart.php');
    exit;
}

$items = get_cart_items($db);
$total = 0;

foreach ($items as $item) {
    $total += $item['subtotal'];
}

$page_title = 'Your bag';
include 'includes/header.php';
?>

<h1>Your bag</h1>

<?php if (count($items) == 0) { ?>
    <div class="empty">
        <h2>Your bag is empty</h2>
        <p>Find a pair you like and add it here.</p>
        <a class="button" href="products.php">Start shopping</a>
    </div>
<?php } else { ?>

<div class="cart-layout">
    <form id="cart-form" class="cart-table" action="cart.php" method="post">
        <input type="hidden" name="action" value="update">
        <table>
            <tr>
                <th>Shoe</th>
                <th>Size (EU)</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Subtotal</th>
                <th></th>
            </tr>
            <?php foreach ($items as $item) { ?>
            <tr>
                <td class="cart-shoe">
                    <a href="product.php?id=<?php echo $item['product_id']; ?>">
                        <img src="assets/images/<?php echo e($item['image']); ?>" alt="">
                    </a>
                    <div>
                        <a href="product.php?id=<?php echo $item['product_id']; ?>">
                            <strong><?php echo e($item['name']); ?></strong>
                        </a><br>
                        <span class="hint"><?php echo e($item['colour']); ?></span>
                    </div>
                </td>
                <td>
                    <select name="size[<?php echo $item['size_id']; ?>]" aria-label="Size">
                        <?php foreach (get_sizes($db, $item['product_id']) as $s) { ?>
                            <option value="<?php echo $s['id']; ?>"
                                <?php if ($s['id'] == $item['size_id']) echo 'selected'; ?>
                                <?php if ($s['stock'] < 1) echo 'disabled'; ?>>
                                <?php echo $s['size']; ?><?php if ($s['stock'] < 1) echo ' (sold out)'; ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
                <td>
                    <input id="cart-quantity-<?php echo $item['size_id']; ?>" class="qty" type="number"
                        name="quantity[<?php echo $item['size_id']; ?>]" value="<?php echo $item['quantity']; ?>"
                        min="1" max="10" step="1" required aria-label="Quantity">
                </td>
                <td><?php echo money($item['price']); ?></td>
                <td><strong><?php echo money($item['subtotal']); ?></strong></td>
                <td>
                    <a class="remove" href="cart.php?remove=<?php echo $item['size_id']; ?>"
                        onclick="return confirm('Remove <?php
                            echo e(addslashes($item['name']));
                        ?> from your bag?');">Remove</a>
                </td>
            </tr>
            <?php } ?>
        </table>
        <p class="cart-buttons">
            <button class="button secondary" type="submit">Update bag</button>
            <a class="text-link" href="cart.php?empty=1"
                onclick="return confirm('Remove everything from your bag?');">Empty bag</a>
        </p>
    </form>

    <aside class="summary">
        <h2>Summary</h2>
        <p>Items <span><?php echo cart_count(); ?></span></p>
        <p>Delivery <span>Free</span></p>
        <p class="summary-total">Total <span><?php echo money($total); ?></span></p>
        <a class="button wide" href="checkout.php">Checkout</a>
        <a class="text-link" href="products.php">Continue shopping</a>
    </aside>
</div>

<?php } ?>

<?php include 'includes/footer.php'; ?>
