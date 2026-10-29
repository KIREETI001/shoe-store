<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($page_title); ?> | <?php echo e($site_name); ?></title>
    <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="topline">Free delivery across Singapore on every order</div>

<header class="site-header">
    <a class="logo" href="index.php"><img src="assets/restep-logo.svg" alt="ReStep home" width="144" height="41"></a>

    <nav>
        <a href="products.php">Shop all</a>
        <a href="products.php?category=Sport">Sport</a>
        <a href="products.php?category=Lifestyle">Lifestyle</a>
        <a href="index.php#our-story">Our story</a>
        <a href="contact.php#help">Help</a>
    </nav>

    <form class="header-search" action="products.php" method="get">
        <input type="search" name="search" placeholder="Search shoes" aria-label="Search shoes">
    </form>

    <div class="header-links">
        <?php if (isset($_SESSION['user_id'])) { ?>
            <a href="account.php">Hi, <?php echo e($_SESSION['user_name']); ?></a>
            <?php if (is_admin_user($db)) { ?>
                <a href="admin.php">Admin</a>
            <?php } ?>
        <?php } else { ?>
            <a href="login.php">Sign in</a>
        <?php } ?>
        <a class="bag-link" href="cart.php">Bag <span><?php echo cart_count(); ?></span></a>
    </div>
</header>

<main>

<?php // One-time message from the previous page (e.g. "Added to your bag") ?>
<?php if (isset($_SESSION['message'])) { ?>
    <p class="message"><?php echo e($_SESSION['message']); ?></p>
    <?php unset($_SESSION['message']); ?>
<?php } ?>
