<?php
require_once 'includes/functions.php';
require_valid_request();

// Read the filter values from the URL (the filter form uses method="get")
$search   = isset($_GET['search'])   ? trim($_GET['search'])   : '';
$category = isset($_GET['category']) ? $_GET['category']       : '';
$activity = isset($_GET['activity']) ? $_GET['activity']       : '';
$size     = isset($_GET['size'])     ? (int) $_GET['size']     : 0;
$max      = isset($_GET['max'])      ? (int) $_GET['max']      : 0;
$sort     = isset($_GET['sort'])     ? $_GET['sort']           : 'featured';

// Build the SQL query one condition at a time
$query = "SELECT * FROM products WHERE 1 = 1";

if ($search != '') {
    $safe = $db->real_escape_string($search);
    $query .= " AND (name LIKE '%$safe%' OR colour LIKE '%$safe%' OR activity LIKE '%$safe%' OR description LIKE '%$safe%')";
}
if ($category == 'Sport' || $category == 'Lifestyle') {
    $query .= " AND category = '$category'";
} else {
    $category = '';
}
if ($activity != '') {
    $safe = $db->real_escape_string($activity);
    $query .= " AND activity = '$safe'";
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
    $query .= " ORDER BY id";
}

$result = $db->query($query);
$num_results = $result->num_rows;

// Activities for the drop-down list (only those in the chosen collection)
$activity_query = "SELECT DISTINCT activity FROM products";
if ($category != '') {
    $activity_query .= " WHERE category = '$category'";
}
$activities = $db->query($activity_query . " ORDER BY activity");

if ($search != '') {
    $page_title = 'Search results';
} elseif ($category != '') {
    $page_title = $category . ' shoes';
} else {
    $page_title = 'All shoes';
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
        <summary>Filter and sort</summary>
        <!-- Changing any drop-down submits the form straight away -->
        <form action="products.php" method="get">
            <label for="search">Search</label>
            <input type="search" id="search" name="search" value="<?php echo e($search); ?>" placeholder="Name, colour or activity">

            <label for="category">Collection</label>
            <select id="category" name="category" onchange="this.form.activity.value = ''; this.form.submit()">
                <option value="">All</option>
                <option <?php if ($category == 'Sport') echo 'selected'; ?>>Sport</option>
                <option <?php if ($category == 'Lifestyle') echo 'selected'; ?>>Lifestyle</option>
            </select>

            <label for="activity">Activity / style</label>
            <select id="activity" name="activity" onchange="this.form.submit()">
                <option value="">All</option>
                <?php while ($row = $activities->fetch_assoc()) { ?>
                    <option <?php if ($activity == $row['activity']) echo 'selected'; ?>><?php echo e($row['activity']); ?></option>
                <?php } ?>
            </select>

            <label for="size">Size in stock (EU)</label>
            <select id="size" name="size" onchange="this.form.submit()">
                <option value="">Any size</option>
                <?php for ($i = 36; $i <= 46; $i++) { ?>
                    <option value="<?php echo $i; ?>" <?php if ($size == $i) echo 'selected'; ?>>EU <?php echo $i; ?></option>
                <?php } ?>
            </select>

            <label for="max">Maximum price</label>
            <select id="max" name="max" onchange="this.form.submit()">
                <option value="">Any price</option>
                <?php foreach (array(100, 120, 150, 180) as $price) { ?>
                    <option value="<?php echo $price; ?>" <?php if ($max == $price) echo 'selected'; ?>>Up to S$<?php echo $price; ?></option>
                <?php } ?>
            </select>

            <label for="sort">Sort by</label>
            <select id="sort" name="sort" onchange="this.form.submit()">
                <option value="featured">Featured</option>
                <option value="price-low" <?php if ($sort == 'price-low') echo 'selected'; ?>>Price: low to high</option>
                <option value="price-high" <?php if ($sort == 'price-high') echo 'selected'; ?>>Price: high to low</option>
                <option value="name" <?php if ($sort == 'name') echo 'selected'; ?>>Name: A to Z</option>
            </select>

            <button class="button" type="submit">Apply</button>
            <a class="text-link" href="products.php">Clear all filters</a>
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
                <h2>No shoes match your filters</h2>
                <p>Try a different size, a higher price or a shorter search word.</p>
                <a class="button" href="products.php">Show all shoes</a>
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
