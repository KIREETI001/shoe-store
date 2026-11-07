</main>

<footer class="site-footer">
    <div class="footer-columns">
        <div>
            <img src="assets/restep-logo-light.svg" alt="ReStep" width="144" height="41">
            <p>Sport and lifestyle shoes for the way you move.</p>
        </div>
        <div>
            <h2>Shop</h2>
            <a href="products.php?category=Sport">Sport Shoes</a>
            <a href="products.php?category=Lifestyle">Lifestyle Shoes</a>
            <a href="products.php">All Shoes</a>
        </div>
        <div>
            <h2>Help</h2>
            <a href="contact.php#help">FAQ</a>
            <a href="contact.php#size-guide">Size Guide</a>
            <a href="contact.php#delivery">Delivery &amp; Returns</a>
            <a href="contact.php#contact-us">Contact Us</a>
        </div>
        <div>
            <h2>My ReStep</h2>
            <a href="account.php">My Account</a>
            <a href="account.php#orders">My Orders</a>
            <a href="cart.php">My Bag</a>
        </div>
    </div>
    <p class="footer-note">&copy; <?php echo date('Y'); ?> ReStep. IE4727 student project &ndash; no real payments are taken.</p>
</footer>

<script src="assets/script.js?v=<?php echo filemtime(__DIR__ . '/../assets/script.js'); ?>"></script>
</body>
</html>
