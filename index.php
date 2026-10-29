<?php
require_once 'includes/functions.php';
require_valid_request();
$page_title = 'Shop Restep';

// Count sold pairs across confirmed orders; pending reviews and cancellations do not count.
$featured = $db->query(
    "SELECT p.id, p.name, p.category, p.activity, p.colour, p.image, p.price,
            SUM(oi.quantity) AS pairs_sold
     FROM products p
     JOIN order_items oi ON oi.product_id = p.id
     JOIN orders o ON o.id = oi.order_id
     WHERE o.status IN ('Confirmed', 'Shipped', 'Delivered')
     GROUP BY p.id, p.name, p.category, p.activity, p.colour, p.image, p.price
     ORDER BY pairs_sold DESC, p.id
     LIMIT 4"
);
$popular_title = 'Popular right now';

// A new shop has no sales yet. Do not present a fixed selection as popularity.
if (!$featured || $featured->num_rows == 0) {
    $popular_title = 'Featured shoes';
    $featured = $db->query('SELECT * FROM products ORDER BY id LIMIT 4');
}

// The hero must also reflect an admin price change.
$hero_result = $db->query('SELECT id, name, colour, image, price FROM products WHERE id = 3');
$hero = $hero_result ? $hero_result->fetch_assoc() : false;

include 'includes/header.php';
?>

<section class="hero">
    <div class="hero-text">
        <p class="eyebrow">THE RESTEP COLLECTION</p>
        <h1>Make your next move.</h1>
        <p>Running shoes, court shoes, loafers and boots &ndash; we have them all in one place.</p>
        <a class="button" href="products.php">Shop all shoes</a>
    </div>
    <?php if ($hero) { ?>
        <a class="hero-image" href="product.php?id=<?php echo $hero['id']; ?>">
            <img src="assets/images/<?php echo e($hero['image']); ?>"
                 alt="<?php echo e($hero['name'] . ' in ' . $hero['colour']); ?>">
            <span><?php echo e($hero['name']); ?> &ndash; <?php echo money($hero['price']); ?></span>
        </a>
    <?php } ?>
</section>

<section class="section">
    <h2>Shop by collection</h2>
    <div class="collections">
        <a class="collection" href="products.php?category=Sport">
            <img src="assets/images/001_sports_road-running-mesh_white-and-cobalt.jpg" alt="White and cobalt running shoe">
            <span><strong>Sport</strong> Running, court, training and outdoor</span>
        </a>
        <a class="collection" href="products.php?category=Lifestyle">
            <img src="assets/images/032_lifestyle_suede-terrace_tan-and-cream.jpg" alt="Tan and cream suede shoe">
            <span><strong>Lifestyle</strong> Sneakers, loafers, flats and boots</span>
        </a>
    </div>
</section>

<section class="section">
    <h2><?php echo $popular_title; ?></h2>
    <?php if ($featured && $featured->num_rows > 0) { ?>
        <div class="product-grid">
            <?php while ($product = $featured->fetch_assoc()) { ?>
                <?php include 'includes/product-card.php'; ?>
            <?php } ?>
        </div>
    <?php } else { ?>
        <p>No shoes are available to display right now.</p>
    <?php } ?>
    <p><a class="text-link" href="products.php">See all shoes &rarr;</a></p>
</section>

<section class="why">
    <div>
        <h3>Find your fit</h3>
        <p>Filter by size and see straight away which sizes are in stock.</p>
    </div>
    <div>
        <h3>Check before it ships</h3>
        <p>After ordering you get an email with a link to review your order and make last changes.</p>
    </div>
    <div>
        <h3>Free delivery</h3>
        <p>Every order is delivered free anywhere in Singapore.</p>
    </div>
</section>

<section class="about section" id="our-story">
    <img src="assets/images/056_lifestyle_retro-casual-low-top_cream-and-burgundy.jpg"
         alt="Cream and burgundy ReStep Retro shoe">
    <div>
        <p class="eyebrow">OUR STORY</p>
        <h2>For the way you move.</h2>
        <p>ReStep brings sport and lifestyle shoes together in one place. Morning runs, court sessions,
           the office and the weekend &ndash; your day does not stay in one lane, and your shoes should not either.</p>
        <p>We keep shopping simple: filter by the size you need, see what is in stock, and check your
           order again after you pay so you know it is exactly right before it ships.</p>
        <a class="button" href="products.php">Browse the collection</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
