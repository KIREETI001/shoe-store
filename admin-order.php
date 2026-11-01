<?php
require_once 'includes/functions.php';
require_login('admin.php');
require_valid_request();

if (!is_admin_user($db)) {
    http_response_code(403);
    $page_title = 'Admin Access';
    include 'includes/header.php';
    ?>
    <h1>Admin Access</h1>
    <p>This page is available only to the configured shop administrators.</p>
    <p><a class="text-link" href="account.php">Return To My Account</a></p>
    <?php
    include 'includes/footer.php';
    exit;
}

$order_id = get_value('id');
if (!preg_match('/^[1-9][0-9]{0,9}$/', $order_id)
    || preg_match('/[^0-9]/', $order_id)) {
    $order_id = 0;
} else {
    $order_id = (int) $order_id;
}

$order = false;
$items = false;
$read_error = '';
if ($order_id > 0) {
    $result = $db->query("SELECT * FROM orders WHERE id = $order_id");
    if (!$result) {
        $read_error = 'The order could not be read. Please try again.';
    } else {
        $order = $result->fetch_assoc();
        if ($order) {
            $items = $db->query(
                "SELECT oi.product_name, oi.size, oi.price, oi.quantity,
                        p.category
                 FROM order_items oi
                 LEFT JOIN products p ON p.id = oi.product_id
                 WHERE oi.order_id = $order_id
                 ORDER BY oi.id"
            );
            if (!$items) {
                $read_error = 'The order products could not be read. Please try again.';
            }
        }
    }
}

if ($read_error != '') {
    $page_title = 'Order Details';
    include 'includes/header.php';
    ?>
    <h1>Order Details</h1>
    <p class="error"><?php echo e($read_error); ?></p>
    <p><a class="text-link" href="admin.php#fulfilment">Return To Order Fulfilment</a></p>
    <?php
    include 'includes/footer.php';
    exit;
}

if (!$order) {
    http_response_code(404);
    $page_title = 'Order Not Found';
    $heading = 'We Could Not Find That Order.';
    $text = 'Return to order fulfilment and choose an order number from the list.';
    $link = 'admin.php#fulfilment';
    $link_text = 'Return To Order Fulfilment';
    include 'includes/not-found.php';
    exit;
}

$page_title = 'Order #' . $order['id'];
include 'includes/header.php';
?>

<h1>Order #<?php echo $order['id']; ?></h1>
<p><a class="text-link" href="admin.php#fulfilment">Return To Order Fulfilment</a></p>

<section class="panel admin-section">
    <h2>Order Details</h2>
    <p><strong>Customer:</strong> <?php echo e($order['name']); ?></p>
    <p><strong>Status:</strong> <?php echo e($order['status']); ?></p>
    <p><strong>Placed:</strong> <?php echo e($order['created_at']); ?></p>
    <p><strong>Delivery:</strong> <?php echo e($order['address']); ?>,
        Singapore <?php echo e($order['postal_code']); ?></p>
    <p><strong>Phone:</strong> <?php echo e($order['phone']); ?> &middot;
        <strong>Email:</strong> <?php echo e($order['email']); ?></p>
</section>

<section class="panel admin-section">
    <h2>Products </h2>
    <?php if ($items->num_rows == 0) { ?>
        <p>This order has no products.</p>
    <?php } else { ?>
        <div class="table-scroll">
            <table class="admin-table">
                <thead>
                    <tr><th>Product</th><th>Category</th><th>Size</th>
                        <th>Quantity</th><th>Unit price</th><th>Subtotal</th></tr>
                </thead>
                <tbody>
                    <?php while ($item = $items->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo e($item['product_name']); ?></td>
                            <td><?php echo e($item['category'] ? $item['category'] : 'Unavailable'); ?></td>
                            <td>EU <?php echo (int) $item['size']; ?></td>
                            <td><?php echo (int) $item['quantity']; ?></td>
                            <td><?php echo money($item['price']); ?></td>
                            <td><?php echo money($item['price'] * $item['quantity']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr><th colspan="5">Order total</th><td><?php echo money($order['total']); ?></td></tr>
                </tfoot>
            </table>
        </div>
    <?php } ?>
</section>

<?php include 'includes/footer.php'; ?>
