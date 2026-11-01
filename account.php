<?php
// My account: saved details and order history (members-only page).
require_once 'includes/functions.php';
require_login('account.php');
require_valid_request();

$user_id = (int) $_SESSION['user_id'];
$error = '';
$user = $db->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$name = $user['name'];
$phone = $user['phone'];
$address = $user['address'];
$postal_code = $user['postal_code'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $address = isset($_POST['address']) ? $_POST['address'] : '';
    $postal_code = isset($_POST['postal_code']) ? $_POST['postal_code'] : '';

    if (!is_valid_name($name)) {
        $error = 'Please enter a name using letters, spaces, apostrophes or hyphens, up to 100 characters.';
    } elseif ($phone != '' && !is_valid_phone($phone)) {
        $error = 'Please enter an 8-digit Singapore phone number, or leave it blank.';
    } elseif ($postal_code != '' && !is_valid_postal_code($postal_code)) {
        $error = 'Please enter a 6-digit postal code, or leave it blank.';
    } elseif (text_length($address) > 255) {
        $error = 'Please keep your address within 255 characters.';
    } else {
        $safe_name = $db->real_escape_string($name);
        $safe_address = $db->real_escape_string($address);
        $saved = $db->query(
            "UPDATE users
             SET name = '$safe_name', phone = '$phone', address = '$safe_address', postal_code = '$postal_code'
             WHERE id = $user_id"
        );
        if ($saved) {
            $_SESSION['user_name'] = $name;
            set_message('Your details have been saved.');
            header('Location: account.php');
            exit;
        }
        $error = 'Your details could not be saved. Please try again.';
    }
}

$orders = $db->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY id DESC");

$page_title = 'My Account';
include 'includes/header.php';
?>

<div class="page-intro">
    <div>
        <p class="eyebrow">MY RESTEP</p>
        <h1>My Account</h1>
        <div class="account-greeting"><span>Hello, <?php echo e($user['name']); ?>.</span> <form class="inline-form" action="logout.php" method="post"><?php echo csrf_field(); ?><button class="link-button" type="submit">Sign Out</button></form></div>
        <p>Review your orders and keep your delivery details up to date.</p>
    </div>
    <img src="assets/images/003_sports_marathon-racing_coral-and-cream.jpg"
         alt="Coral and cream ReStep Tempo running shoe" width="220" height="220">
</div>

<div class="panel" id="orders">
    <h2>My Orders</h2>
    <?php if ($orders->num_rows == 0) { ?>
        <p>You have not placed any orders yet.</p>
        <a class="button" href="products.php">Start Shopping</a>
    <?php } else { ?>
        <div class="table-scroll" tabindex="0" role="region" aria-label="Your order history">
            <table class="account-orders">
                <caption>Your orders, newest first</caption>
                <thead>
                    <tr>
                        <th scope="col">Order</th>
                        <th scope="col">Date</th>
                        <th scope="col">Total</th>
                        <th scope="col">Status</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()) { ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo date('j M Y', strtotime($order['created_at'])); ?></td>
                        <td><?php echo money($order['total']); ?></td>
                        <td>
                            <span class="status status-<?php echo strtolower($order['status']); ?>">
                                <?php echo e($order['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a class="text-link" href="order.php?id=<?php echo $order['id']; ?>">
                                <?php echo $order['status'] == 'Pending' ? 'Check and confirm' : 'View'; ?>
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr><td colspan="5">Pending orders can still be checked and changed.</td></tr>
                </tfoot>
            </table>
        </div>
    <?php } ?>
</div>

<div class="panel">
    <h2>My Details</h2>
    <p class="hint">These are filled in for you at checkout.</p>
    <?php if ($error != '') { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>
    <form id="account-form" action="account.php" method="post">
        <?php echo csrf_field(); ?>
        <label for="account-email">Email (used to sign in)</label>
        <input type="email" id="account-email" value="<?php echo e($user['email']); ?>" disabled>

        <label for="account-name">Name</label>
        <input type="text" id="account-name" name="name" value="<?php echo e($name); ?>" maxlength="200" required>

        <label for="account-phone">Mobile number</label>
        <input type="tel" id="account-phone" name="phone" value="<?php echo e($phone); ?>"
               maxlength="8" pattern="[689][0-9]{7}">

        <label for="account-address">Address</label>
        <input type="text" id="account-address" name="address" value="<?php echo e($address); ?>" maxlength="510">

        <label for="account-postal">Postal code</label>
        <input type="text" id="account-postal" name="postal_code" value="<?php echo e($postal_code); ?>"
               maxlength="6" pattern="[0-9]{6}">

        <p id="account-error" class="error-text"></p>
        <button class="button" type="submit">Save details</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
