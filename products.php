<?php
require_once 'includes/functions.php';
require_valid_request();

// Read the filter values from the URL (the filter form uses method="get")
$search   = isset($_GET['search'])   ? trim($_GET['search'])   : '';
$category = isset($_GET['category']) ? $_GET['category']       : '';
$raw_size = get_value('size');
$raw_max = get_value('max');
$size = in_array($raw_size, array_map('strval', range(36, 46)), true) ? (int) $raw_size : 0;
$max = in_array($raw_max, array('100', '120', '150', '180'), true) ? (int) $raw_max : 0;
$sort = get_value('sort', 'featured');
if (!in_array($sort, array('featured', 'price-low', 'price-high', 'name'), true)) $sort = 'featured';

// Build the SQL query one condition at a time
$query = "SELECT products.* FROM products";
if ($category == 'Popular') {
    $query .= " JOIN (SELECT oi.product_id, SUM(oi.quantity) AS pairs_sold
                 FROM order_items oi JOIN orders o ON o.id = oi.order_id
                 WHERE o.status IN ('Confirmed', 'Shipped', 'Delivered')
                 GROUP BY oi.product_id) sales ON sales.product_id = products.id";
}
$query .= " WHERE 1 = 1";

if ($search != '') {
    $safe = $db->real_escape_string($search);
    $query .= " AND (name LIKE '%$safe%' OR colour LIKE '%$safe%' OR activity LIKE '%$safe%' OR description LIKE '%$safe%')";
}
if ($category == 'Sport' || $category == 'Lifestyle') {
    $query .= " AND category = '$category'";
} elseif ($category != 'Popular') {
    $category = '';
}
if ($size > 0) {
    // Only shoes that still have this size in stock
    $query .= " AND id IN (SELECT product_id FROM product_sizes WHERE size = $size AND stock > 0)";
}
if ($max > 0) {
    $query .= " AND price <= $max";
}

if ($sort == 'price-low') {
    $query .= " ORDER BY price ASC";
} elseif ($sort == 'price-high') {
    $query .= " ORDER BY price DESC";
} elseif ($sort == 'name') {
    $query .= " ORDER BY name ASC";
} else {
    $query .= $category == 'Popular' ? " ORDER BY pairs_sold DESC, products.id" : " ORDER BY products.id";
}

$result = $db->query($query);
$num_results = $result->num_rows;

if ($search != '') {
    $page_title = 'Search Results';
} elseif ($category != '') {
    $page_title = $category . ' Shoes';
} else {
    $page_title = 'All Shoes';
}

include 'includes/header.php';
?>

<h1><?php echo e($page_title); ?></h1>

<div class="tabs">
    <a href="products.php" <?php if ($category == '') echo 'class="selected"'; ?>>All</a>
    <a href="products.php?category=Sport" <?php if ($category == 'Sport') echo 'class="selected"'; ?>>Sport</a>
    <a href="products.php?category=Lifestyle" <?php if ($category == 'Lifestyle') echo 'class="selected"'; ?>>Lifestyle</a>
</div>

<div class="shop-layout">
    <details class="filters" id="filter-box" open>
        <summary>Filter And Sort</summary>
        <!-- Changing any drop-down submits the form straight away -->
        <form action="products.php" method="get">
            <label for="search">Search</label>
            <input type="search" id="search" name="search" value="<?php echo e($search); ?>" placeholder="Name, colour or activity">

            <label for="category">Collection</label>
            <select id="category" name="category" onchange="this.form.requestSubmit()">
                <option value="">All</option>
                <option <?php if ($category == 'Sport') echo 'selected'; ?>>Sport</option>
                <option <?php if ($category == 'Lifestyle') echo 'selected'; ?>>Lifestyle</option>
                <option <?php if ($category == 'Popular') echo 'selected'; ?>>Popular</option>
            </select>

            <label for="size">Size in stock (EU)</label>
            <select id="size" name="size" onchange="this.form.requestSubmit()">
                <option value="">Any size</option>
                <?php for ($i = 36; $i <= 46; $i++) { ?>
                    <option value="<?php echo $i; ?>" <?php if ($size == $i) echo 'selected'; ?>>EU <?php echo $i; ?></option>
                <?php } ?>
            </select>

            <label for="max">Maximum price</label>
            <select id="max" name="max" onchange="this.form.requestSubmit()">
                <option value="">Any price</option>
                <?php foreach (array(100, 120, 150, 180) as $price) { ?>
                    <option value="<?php echo $price; ?>" <?php if ($max == $price) echo 'selected'; ?>>Up to S$<?php echo $price; ?></option>
                <?php } ?>
            </select>

            <label for="sort">Sort by</label>
            <select id="sort" name="sort" onchange="this.form.requestSubmit()">
                <option value="featured">Featured</option>
                <option value="price-low" <?php if ($sort == 'price-low') echo 'selected'; ?>>Price: low to high</option>
                <option value="price-high" <?php if ($sort == 'price-high') echo 'selected'; ?>>Price: high to low</option>
                <option value="name" <?php if ($sort == 'name') echo 'selected'; ?>>Name: A to Z</option>
            </select>

            <button class="button" type="submit">Apply</button>
            <a class="text-link" href="products.php">Clear All Filters</a>
        </form>
    </details>
    <script>
        // On phones, start with the filters folded away so the shoes are visible first
        if (window.innerWidth < 600) {
            document.getElementById("filter-box").removeAttribute("open");
        }
    </script>

    <section class="results">
        <p class="results-count"><?php echo $num_results; ?> shoe<?php if ($num_results != 1) echo 's'; ?> found</p>

        <?php if ($num_results == 0) { ?>
            <div class="empty">
                <h2>No Shoes Match Your Filters</h2>
                <p>Try a different size, a higher price or a shorter search word.</p>
                <a class="button" href="products.php">Show All Shoes</a>
            </div>
        <?php } else { ?>
            <div class="product-grid">
                <?php while ($product = $result->fetch_assoc()) { ?>
                    <?php include 'includes/product-card.php'; ?>
                <?php } ?>
            </div>
        <?php } ?>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
