<?php
// My account: saved details and order history (members-only page).
require_once 'includes/functions.php';
require_login('account.php');

$user_id = (int) $_SESSION['user_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $postal_code = trim($_POST['postal_code']);

    if ($name == '') {
        $error = 'Please enter your name.';
    } elseif ($phone != '' && !preg_match('/^[689][0-9]{7}$/', $phone)) {
        $error = 'Please enter an 8-digit Singapore phone number, or leave it blank.';
    } elseif ($postal_code != '' && !preg_match('/^[0-9]{6}$/', $postal_code)) {
        $error = 'Please enter a 6-digit postal code, or leave it blank.';
    } else {
        $safe_name = $db->real_escape_string($name);
        $safe_address = $db->real_escape_string($address);
        $db->query("UPDATE users SET name = '$safe_name', phone = '$phone', address = '$safe_address', postal_code = '$postal_code' WHERE id = $user_id");
        $_SESSION['user_name'] = $name;
        set_message('Your details have been saved.');
        header('Location: account.php');
        exit;
    }
}

$user = $db->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$orders = $db->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY id DESC");

$page_title = 'My account';
include 'includes/header.php';
?>

<h1>My account</h1>
<p>Hello, <?php echo e($user['name']); ?>. <a class="text-link" href="logout.php">Sign out</a></p>

<div class="panel" id="orders">
    <h2>My orders</h2>
    <?php if ($orders->num_rows == 0) { ?>
        <p>You have not placed any orders yet.</p>
        <a class="button" href="products.php">Start shopping</a>
    <?php } else { ?>
        <table>
            <tr>
                <th>Order</th>
                <th>Date</th>
                <th>Total</th>
                <th>Status</th>
                <th></th>
            </tr>
            <?php while ($order = $orders->fetch_assoc()) { ?>
            <tr>
                <td>#<?php echo $order['id']; ?></td>
                <td><?php echo date('j M Y', strtotime($order['created_at'])); ?></td>
                <td><?php echo money($order['total']); ?></td>
                <td><span class="status status-<?php echo strtolower($order['status']); ?>"><?php echo e($order['status']); ?></span></td>
                <td>
                    <a class="text-link" href="order.php?id=<?php echo $order['id']; ?>">
                        <?php echo $order['status'] == 'Pending' ? 'Check and confirm' : 'View'; ?>
                    </a>
                </td>
            </tr>
            <?php } ?>
        </table>
    <?php } ?>
</div>

<div class="panel">
    <h2>My details</h2>
    <p class="hint">These are filled in for you at checkout.</p>
    <?php if ($error != '') { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>
    <form action="account.php" method="post">
        <label for="account-email">Email (used to sign in)</label>
        <input type="email" id="account-email" value="<?php echo e($user['email']); ?>" disabled>

        <label for="account-name">Name</label>
        <input type="text" id="account-name" name="name" value="<?php echo e($user['name']); ?>" required>

        <label for="account-phone">Mobile number</label>
        <input type="tel" id="account-phone" name="phone" value="<?php echo e($user['phone']); ?>" maxlength="8">

        <label for="account-address">Address</label>
        <input type="text" id="account-address" name="address" value="<?php echo e($user['address']); ?>">

        <label for="account-postal">Postal code</label>
        <input type="text" id="account-postal" name="postal_code" value="<?php echo e($user['postal_code']); ?>" maxlength="6">

        <button class="button" type="submit">Save details</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
