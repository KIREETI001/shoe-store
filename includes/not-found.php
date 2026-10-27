<?php
// A whole "not found" page. Set these before including it:
// $page_title, $heading, $text, $link, $link_text
include 'includes/header.php';
?>

<div class="empty">
    <h1><?php echo e($heading); ?></h1>
    <p><?php echo e($text); ?></p>
    <a class="button" href="<?php echo $link; ?>"><?php echo e($link_text); ?></a>
</div>

<?php include 'includes/footer.php'; ?>
