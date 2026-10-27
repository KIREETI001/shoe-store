<?php // One product card. Include this inside a loop that sets $product. ?>
<div class="product-card">
    <a href="product.php?id=<?php echo $product['id']; ?>">
        <img src="assets/images/<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>">
    </a>
    <p class="card-type"><?php echo e($product['category']); ?> / <?php echo e($product['activity']); ?></p>
    <h3><a href="product.php?id=<?php echo $product['id']; ?>"><?php echo e($product['name']); ?></a></h3>
    <p class="card-colour"><?php echo e($product['colour']); ?></p>
    <p class="card-price"><?php echo money($product['price']); ?></p>
</div>
