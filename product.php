<?php
require_once 'includes/functions.php';

$product_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = $db->query("SELECT * FROM products WHERE id = $product_id")->fetch_assoc();

if (!$product) {
    $page_title = 'Shoe not found';
    $heading = 'Sorry, we could not find that shoe.';
    $text = 'Have a look at the rest of our collection instead.';
    $link = 'products.php';
    $link_text = 'Back to all shoes';
    include 'includes/not-found.php';
    exit;
}

$sizes = get_sizes($db, $product_id);

// Up to four other shoes of the same activity for "You may also like"
$activity = $db->real_escape_string($product['activity']);
$related = $db->query("SELECT * FROM products WHERE activity = '$activity' AND id <> $product_id LIMIT 4");

$page_title = $product['name'];
include 'includes/header.php';
?>

<p class="breadcrumb">
    <a href="products.php">All shoes</a> /
    <a href="products.php?category=<?php echo e($product['category']); ?>"><?php echo e($product['category']); ?></a> /
    <?php echo e($product['name']); ?>
</p>

<div class="product-layout">
    <div class="product-photo">
        <img src="assets/images/<?php echo e($product['image']); ?>" alt="<?php echo e($product['name'] . ' in ' . $product['colour']); ?>">
    </div>

    <div class="product-info">
        <p class="card-type"><?php echo e($product['category'] . ' / ' . $product['activity']); ?></p>
        <h1><?php echo e($product['name']); ?></h1>
        <p class="product-colour">Colour: <?php echo e($product['colour']); ?></p>
        <p class="product-price"><?php echo money($product['price']); ?></p>
        <p><?php echo e($product['description']); ?></p>

        <form action="cart.php" method="post" onsubmit="return checkProductForm();">
            <input type="hidden" name="action" value="add">
            <input type="hidden" id="unit-price" value="<?php echo $product['price']; ?>">

            <p class="size-heading"><strong>Choose your size (EU)</strong> <a href="faq.php#size-guide">Size guide</a></p>
            <div class="sizes">
                <?php foreach ($sizes as $s) { ?>
                    <?php if ($s['stock'] > 0) { ?>
                        <label class="size">
                            <input type="radio" name="size_id" value="<?php echo $s['id']; ?>" onclick="showStock(<?php echo $s['stock']; ?>)">
                            <span><?php echo $s['size']; ?></span>
                        </label>
                    <?php } else { ?>
                        <label class="size sold-out" title="Sold out">
                            <input type="radio" disabled>
                            <span><?php echo $s['size']; ?></span>
                        </label>
                    <?php } ?>
                <?php } ?>
            </div>
            <p id="stock-note" class="hint">Crossed-out sizes are sold out.</p>

            <div class="quantity-row">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" value="1" min="1" max="10" oninput="updateSubtotal()">
                <span>Subtotal: <strong id="subtotal"><?php echo money($product['price']); ?></strong></span>
            </div>

            <p id="product-error" class="error-text"></p>
            <button class="button wide" type="submit">Add to bag</button>
        </form>

        <ul class="perks">
            <li>Free delivery in Singapore</li>
            <li>Check and change your order after you pay, before it ships</li>
        </ul>
    </div>
</div>

<?php if ($related->num_rows > 0) { ?>
<section class="section">
    <h2>You may also like</h2>
    <div class="product-grid">
        <?php // The main shoe is not needed any more, so $product can now hold each related shoe ?>
        <?php while ($product = $related->fetch_assoc()) { ?>
            <?php include 'includes/product-card.php'; ?>
        <?php } ?>
    </div>
</section>
<?php } ?>

<?php include 'includes/footer.php'; ?>
