<?php
// Case Study 05: update prices and read daily sales from saved orders.
require_once 'includes/functions.php';
require_login('admin.php');
require_valid_request();

if (!is_admin_user($db)) {
    $page_title = 'Admin access';
    include 'includes/header.php';
    ?>
    <h1>Admin access</h1>
    <p>This page is available only to the configured shop administrators.</p>
    <p><a class="text-link" href="account.php">Return to my account</a></p>
    <?php
    include 'includes/footer.php';
    exit;
}

// Check a calendar date with the operators and arrays used in the PHP lecture.
function admin_valid_date($value) {
    if (strlen($value) != 10 || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $value)) {
        return false;
    }

    $year = (int) substr($value, 0, 4);
    $month = (int) substr($value, 5, 2);
    $day = (int) substr($value, 8, 2);
    if ($year < 1000 || $month < 1 || $month > 12) {
        return false;
    }

    $days = array(1 => 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    if ($year % 400 == 0 || ($year % 4 == 0 && $year % 100 != 0)) {
        $days[2] = 29;
    }

    return $day >= 1 && $day <= $days[$month];
}

function admin_valid_price($value) {
    return preg_match('/^(0|[1-9][0-9]{0,5})(\.[0-9]{1,2})?$/', $value) == 1
        && preg_match('/[^0-9.]/', $value) == 0
        && (float) $value >= 0.01
        && (float) $value <= 999999.99;
}

$error = '';
$action = post_value('action');
$selected_id = get_value('product_id');
$report_date = get_value('report_date', date('Y-m-d'));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $report_date = post_value('report_date', $report_date);
    if ($action == 'price') {
        $selected_id = post_value('product_id');
        $new_price = post_value('price');
        $old_price = post_value('current_price');

        if (!preg_match('/^[1-9][0-9]{0,9}$/', $selected_id)
            || preg_match('/[^0-9]/', $selected_id)) {
            $error = 'Please choose a product from the list.';
        } elseif (!admin_valid_price($new_price) || !admin_valid_price($old_price)) {
            $error = 'Enter a price from 0.01 to 999999.99, with no more than two decimal places.';
        } else {
            $product_id = (int) $selected_id;
            $saved = $db->query(
                "UPDATE products
                 SET price = '$new_price'
                 WHERE id = $product_id AND price = '$old_price'"
            );

            if (!$saved) {
                $error = 'The price could not be saved. Please try again.';
            } else {
                $changed = $db->affected_rows;
                $result = $db->query("SELECT name, price FROM products WHERE id = $product_id");
                $updated = $result ? $result->fetch_assoc() : false;

                if (!$updated) {
                    $error = 'The product could not be read. Please reload the page before trying again.';
                } elseif ($changed == 1) {
                    set_message($updated['name'] . ' now costs ' . money($updated['price']) . '.');
                } elseif ((float) $updated['price'] == (float) $old_price
                    && (float) $new_price == (float) $old_price) {
                    set_message('That product already has this price.');
                } else {
                    $error = 'This product price has changed. Review the current price and submit again.';
                }
            }
        }
    } elseif ($action == 'status') {
        $selected_id = post_value('product_id', $selected_id);
        $order_id = post_value('order_id');
        $old_status = post_value('current_status');
        $new_status = post_value('status');
        $valid_change = ($old_status == 'Confirmed' && $new_status == 'Shipped')
            || ($old_status == 'Shipped' && $new_status == 'Delivered');

        if (!preg_match('/^[1-9][0-9]{0,9}$/', $order_id)
            || preg_match('/[^0-9]/', $order_id) || !$valid_change) {
            $error = 'Choose the next fulfilment step for a confirmed order.';
        } else {
            $order_id = (int) $order_id;
            $saved = $db->query(
                "UPDATE orders
                 SET status = '$new_status'
                 WHERE id = $order_id AND status = '$old_status'"
            );

            if (!$saved) {
                $error = 'The order status could not be saved. Please try again.';
            } elseif ($db->affected_rows != 1) {
                $error = 'This order has already changed. Review its current status below.';
            } else {
                $message = 'Order #' . $order_id . ' is now ' . $new_status . '.';
                $intro = $new_status == 'Shipped'
                    ? 'Your demonstration order has been shipped. No real payment is taken.'
                    : 'Your demonstration order has been delivered. No real payment is taken.';
                if (send_order_email($db, $order_id, 'Order ' . strtolower($new_status), $intro)) {
                    $message .= ' The local mail server accepted the email.';
                } else {
                    $message .= ' The email could not be sent. The saved status is available in My orders.';
                }
                set_message($message);
            }
        }
    } else {
        $error = 'Please use one of the admin forms to continue.';
    }

    if ($error == '') {
        header('Location: admin.php?product_id=' . urlencode($selected_id)
            . '&report_date=' . urlencode($report_date));
        exit;
    }
}

// Reload products after a price update; previous orders keep their copied prices.
$products = array();
$result = $db->query('SELECT id, name, price, image FROM products ORDER BY name, id');
$product_error = !$result;
if ($result) {
    while ($product = $result->fetch_assoc()) {
        $products[$product['id']] = $product;
        if ($selected_id == '') {
            $selected_id = $product['id'];
        }
    }
}

$selected_product = false;
if (preg_match('/^[1-9][0-9]{0,9}$/', $selected_id)
    && !preg_match('/[^0-9]/', $selected_id) && isset($products[(int) $selected_id])) {
    $selected_product = $products[(int) $selected_id];
}

$orders = $db->query('SELECT * FROM orders ORDER BY id DESC');
$report_error = '';
$product_sales = array();
$category_sales = array(
    'Sport' => array('quantity' => 0, 'revenue' => 0),
    'Lifestyle' => array('quantity' => 0, 'revenue' => 0)
);
$sales_orders = array();
$total_quantity = 0;
$total_revenue = 0;
$best_product_id = 0;
$popular_size = 0;
$popular_size_quantity = 0;

if (!admin_valid_date($report_date)) {
    $report_error = 'Please choose a valid report date.';
} else {
    // Revenue uses the order price, not today's product price. Pending reviews
    // and cancelled orders do not count as sales. The day is the order date.
    $sales = $db->query(
        "SELECT o.id AS order_id, oi.product_id, oi.product_name, oi.price,
                oi.quantity, oi.size, p.category
         FROM orders o
         JOIN order_items oi ON oi.order_id = o.id
         JOIN products p ON p.id = oi.product_id
         WHERE o.created_at >= '$report_date 00:00:00'
             AND o.created_at <= '$report_date 23:59:59'
             AND o.status IN ('Confirmed', 'Shipped', 'Delivered')
         ORDER BY oi.product_name, oi.product_id, oi.size"
    );

    if (!$sales) {
        $report_error = 'The sales report could not be read. Please try again.';
    } else {
        while ($sale = $sales->fetch_assoc()) {
            $product_id = (int) $sale['product_id'];
            $size = (int) $sale['size'];
            $quantity = (int) $sale['quantity'];
            // DECIMAL(8,2) is returned with two decimal places. Sum whole cents.
            $revenue = (int) str_replace('.', '', $sale['price']) * $quantity;
            $category = $sale['category'];
            if (!isset($product_sales[$product_id])) {
                $product_sales[$product_id] = array(
                    'name' => $sale['product_name'],
                    'quantity' => 0,
                    'revenue' => 0,
                    'sizes' => array()
                );
            }
            if (!isset($product_sales[$product_id]['sizes'][$size])) {
                $product_sales[$product_id]['sizes'][$size] = 0;
            }
            if (!isset($category_sales[$category])) {
                $category_sales[$category] = array('quantity' => 0, 'revenue' => 0);
            }

            $product_sales[$product_id]['quantity'] += $quantity;
            $product_sales[$product_id]['revenue'] += $revenue;
            $product_sales[$product_id]['sizes'][$size] += $quantity;
            $category_sales[$category]['quantity'] += $quantity;
            $category_sales[$category]['revenue'] += $revenue;
            $sales_orders[$sale['order_id']] = true;
            $total_quantity += $quantity;
            $total_revenue += $revenue;
        }

        foreach ($product_sales as $product_id => $sale) {
            if ($best_product_id == 0
                || $sale['revenue'] > $product_sales[$best_product_id]['revenue']
                || ($sale['revenue'] == $product_sales[$best_product_id]['revenue']
                    && $product_id < $best_product_id)) {
                $best_product_id = $product_id;
            }
        }
        if ($best_product_id != 0) {
            foreach ($product_sales[$best_product_id]['sizes'] as $size => $quantity) {
                if ($quantity > $popular_size_quantity
                    || ($quantity == $popular_size_quantity && $size < $popular_size)) {
                    $popular_size = $size;
                    $popular_size_quantity = $quantity;
                }
            }
        }
    }
}

$page_title = 'Shop admin';
include 'includes/header.php';
?>

<h1>Shop admin</h1>
<nav class="admin-nav" aria-label="Admin sections">
    <a class="text-link" href="#prices">Product prices</a>
    <a class="text-link" href="#fulfilment">Order fulfilment</a>
    <a class="text-link" href="#sales">Daily sales</a>
</nav>

<?php if ($error != '') { ?>
    <p class="error"><?php echo e($error); ?></p>
<?php } ?>

<section class="panel admin-section" id="prices">
    <h2>Product prices</h2>
    <p>Choose a shoe, then enter its new price. Existing orders keep the price agreed at checkout.</p>
    <?php if ($product_error) { ?>
        <p class="error">The products could not be read. Please reload this page.</p>
    <?php } elseif (count($products) == 0) { ?>
        <p>No products are available.</p>
    <?php } else { ?>
        <form class="admin-form" action="admin.php#prices" method="get">
            <input type="hidden" name="report_date" value="<?php echo e($report_date); ?>">
            <label for="admin-product">Product</label>
            <select id="admin-product" name="product_id" required>
                <?php foreach ($products as $product) { ?>
                    <option value="<?php echo $product['id']; ?>"
                        <?php if ($selected_id == $product['id']) echo 'selected'; ?>>
                        <?php echo e($product['name']) . ' - ' . money($product['price']); ?>
                    </option>
                <?php } ?>
            </select>
            <button class="button" type="submit">Choose product</button>
        </form>

        <?php if ($selected_product) { ?>
            <div class="page-intro admin-product">
                <img src="assets/images/<?php echo e($selected_product['image']); ?>"
                     alt="<?php echo e($selected_product['name']); ?>" width="180" height="180">
                <div>
                    <h3><?php echo e($selected_product['name']); ?></h3>
                    <p>Current price: <strong><?php echo money($selected_product['price']); ?></strong></p>
                    <form id="admin-price-form" class="admin-form" action="admin.php#prices" method="post">
                        <input type="hidden" name="action" value="price">
                        <input type="hidden" name="product_id"
                               value="<?php echo $selected_product['id']; ?>">
                        <input type="hidden" name="report_date" value="<?php echo e($report_date); ?>">
                        <input type="hidden" name="current_price"
                               value="<?php echo e($selected_product['price']); ?>">
                        <label for="admin-price">New price (S$)</label>
                        <input type="number" id="admin-price" name="price" min="0.01" max="999999.99"
                               step="0.01" value="<?php echo e($selected_product['price']); ?>" required>
                        <p id="admin-price-error" class="error-text"></p>
                        <button class="button" type="submit">Save price</button>
                    </form>
                </div>
            </div>
        <?php } else { ?>
            <p class="error">Please choose a product from the list.</p>
        <?php } ?>
    <?php } ?>
</section>

<section class="panel admin-section" id="fulfilment">
    <h2>Order fulfilment</h2>
    <p>Customers confirm their own Pending orders. Admin can then mark them Shipped and Delivered.</p>
    <?php if (!$orders) { ?>
        <p class="error">The orders could not be read. Please reload this page.</p>
    <?php } elseif ($orders->num_rows == 0) { ?>
        <p>No orders have been placed yet.</p>
    <?php } else { ?>
        <div class="table-scroll">
            <table class="admin-table">
                <caption>Orders and their current fulfilment status</caption>
                <thead>
                    <tr><th>Order</th><th>Customer</th><th>Placed</th><th>Total</th><th>Status</th><th>Next step</th></tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()) { ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td>
                                <?php echo e($order['name']); ?>
                                <details>
                                    <summary>Delivery details</summary>
                                    <p><?php echo e($order['address']); ?><br>
                                        Singapore <?php echo e($order['postal_code']); ?><br>
                                        <?php echo e($order['phone']); ?><br>
                                        <?php echo e($order['email']); ?></p>
                                </details>
                            </td>
                            <td><?php echo e($order['created_at']); ?></td>
                            <td><?php echo money($order['total']); ?></td>
                            <td><?php echo e($order['status']); ?></td>
                            <td>
                                <?php if ($order['status'] == 'Confirmed' || $order['status'] == 'Shipped') { ?>
                                    <?php $next_status = $order['status'] == 'Confirmed' ? 'Shipped' : 'Delivered'; ?>
                                    <form class="admin-status-form" action="admin.php#fulfilment" method="post">
                                        <input type="hidden" name="action" value="status">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="product_id" value="<?php echo e($selected_id); ?>">
                                        <input type="hidden" name="report_date" value="<?php echo e($report_date); ?>">
                                        <input type="hidden" name="current_status"
                                               value="<?php echo e($order['status']); ?>">
                                        <button class="button" type="submit" name="status"
                                                value="<?php echo $next_status; ?>">
                                            Mark <?php echo strtolower($next_status); ?>
                                        </button>
                                    </form>
                                <?php } elseif ($order['status'] == 'Pending') { ?>
                                    Awaiting customer review
                                <?php } else { ?>
                                    No further action
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr><td colspan="6">Status updates send mail only to the permitted local project accounts.</td></tr>
                </tfoot>
            </table>
        </div>
    <?php } ?>
</section>

<section class="panel admin-section" id="sales">
    <h2>Daily sales</h2>
    <form class="admin-form" action="admin.php#sales" method="get">
        <input type="hidden" name="product_id" value="<?php echo e($selected_id); ?>">
        <label for="report-date">Order date</label>
        <input type="date" id="report-date" name="report_date" min="1000-01-01" max="9999-12-31"
               value="<?php echo e($report_date); ?>" required>
        <button class="button" type="submit">Show report</button>
    </form>
    <p class="hint">Sales include Confirmed, Shipped and Delivered orders placed on the selected date.
        Pending and Cancelled orders are excluded. Revenue uses each saved order price.</p>

    <?php if ($report_error != '') { ?>
        <p class="error"><?php echo e($report_error); ?></p>
    <?php } elseif (count($product_sales) == 0) { ?>
        <p>No confirmed sales for <?php echo e($report_date); ?>.</p>
    <?php } else { ?>
        <p><?php echo count($sales_orders); ?> orders &middot; <?php echo $total_quantity; ?> pairs &middot;
            <?php echo money($total_revenue / 100); ?> revenue</p>
        <div class="table-scroll">
            <table>
                <caption>Sales by product for <?php echo e($report_date); ?></caption>
                <thead><tr><th>Product</th><th>Pairs sold</th><th>Revenue</th></tr></thead>
                <tbody>
                    <?php foreach ($product_sales as $product_id => $sale) { ?>
                        <tr>
                            <td><?php echo e($sale['name']); ?></td>
                            <td><?php echo $sale['quantity']; ?></td>
                            <td><?php echo money($sale['revenue'] / 100); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr><th>Total</th><td><?php echo $total_quantity; ?></td>
                        <td><?php echo money($total_revenue / 100); ?></td></tr>
                </tfoot>
            </table>
        </div>
        <div class="table-scroll">
            <table>
                <caption>Sales by category for <?php echo e($report_date); ?></caption>
                <thead><tr><th>Category</th><th>Pairs sold</th><th>Revenue</th></tr></thead>
                <tbody>
                    <?php foreach ($category_sales as $category => $sale) { ?>
                        <tr>
                            <td><?php echo e($category); ?></td>
                            <td><?php echo $sale['quantity']; ?></td>
                            <td><?php echo money($sale['revenue'] / 100); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr><th>Total</th><td><?php echo $total_quantity; ?></td>
                        <td><?php echo money($total_revenue / 100); ?></td></tr>
                </tfoot>
            </table>
        </div>
        <h3>Best-selling product</h3>
        <p><strong><?php echo e($product_sales[$best_product_id]['name']); ?></strong>
            has the highest revenue: <?php echo money($product_sales[$best_product_id]['revenue'] / 100); ?>.
            Its most popular size is EU <?php echo $popular_size; ?>
            (<?php echo $popular_size_quantity; ?> pairs).</p>
        <p class="hint">Equal revenue is resolved by product number; equal size quantities use the smaller EU size.</p>
    <?php } ?>
</section>

<?php include 'includes/footer.php'; ?>
