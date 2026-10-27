<?php
require_once 'includes/functions.php';
$page_title = 'Sport and lifestyle shoes';

// Four hand-picked shoes for the "Popular right now" section
$featured = $db->query("SELECT * FROM products WHERE id IN (3, 6, 31, 35) ORDER BY id");

include 'includes/header.php';
?>

<section class="hero">
    <div class="hero-text">
        <p class="eyebrow">THE RESTEP COLLECTION</p>
        <h1>Make your next move.</h1>
        <p>Running shoes, court shoes, loafers and boots &ndash; 60 styles in EU sizes 36 to 46, all in one place.</p>
        <a class="button" href="products.php">Shop all shoes</a>
    </div>
    <a class="hero-image" href="product.php?id=3">
        <img src="assets/images/003_sports_marathon-racing_coral-and-cream.jpg" alt="ReStep Tempo running shoe in coral and cream">
        <span>ReStep Tempo &ndash; S$128.00</span>
    </a>
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
    <h2>Popular right now</h2>
    <div class="product-grid">
        <?php while ($product = $featured->fetch_assoc()) { ?>
            <?php include 'includes/product-card.php'; ?>
        <?php } ?>
    </div>
    <p><a class="text-link" href="products.php">See all 60 shoes &rarr;</a></p>
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

<?php include 'includes/footer.php'; ?>
