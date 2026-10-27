<?php
// Contact form: the message is saved in the enquiries table (MySQL lecture: INSERT).
require_once 'includes/functions.php';

$name = '';
$email = '';
$subject = '';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if ($name == '' || $subject == '' || $message == '') {
        $error = 'Please fill in every field.';
    } elseif (!preg_match('/^[^@\s]+@[^@\s]+$/', $email)) {
        $error = 'Please enter a valid email address so we can reply.';
    } else {
        $s_name = $db->real_escape_string($name);
        $s_email = $db->real_escape_string($email);
        $s_subject = $db->real_escape_string($subject);
        $s_message = $db->real_escape_string($message);
        $db->query("INSERT INTO enquiries (name, email, subject, message) VALUES ('$s_name', '$s_email', '$s_subject', '$s_message')");
        set_message('Thanks, ' . $name . '! We have received your message and will reply within 2 working days.');
        header('Location: contact.php');
        exit;
    }
}

// Fill in the name and email for signed-in customers
if ($name == '' && isset($_SESSION['user_id'])) {
    $user_id = (int) $_SESSION['user_id'];
    $user = $db->query("SELECT name, email FROM users WHERE id = $user_id")->fetch_assoc();
    $name = $user['name'];
    $email = $user['email'];
}

$page_title = 'Contact us';
include 'includes/header.php';
?>

<h1>Contact us</h1>
<p>Questions about sizes, an order or anything else? Send us a message.</p>

<div class="cart-layout">
    <form class="panel" action="contact.php" method="post">
        <?php if ($error != '') { ?>
            <p class="error"><?php echo e($error); ?></p>
        <?php } ?>
        <label for="contact-name">Name</label>
        <input type="text" id="contact-name" name="name" value="<?php echo e($name); ?>" required>

        <label for="contact-email">Email</label>
        <input type="email" id="contact-email" name="email" value="<?php echo e($email); ?>" required>

        <label for="contact-subject">Subject</label>
        <select id="contact-subject" name="subject" required>
            <option value="">Choose a topic</option>
            <?php foreach (array('Sizing and fit', 'My order', 'Delivery', 'Something else') as $topic) { ?>
                <option <?php if ($subject == $topic) echo 'selected'; ?>><?php echo $topic; ?></option>
            <?php } ?>
        </select>

        <label for="contact-message">Message</label>
        <textarea id="contact-message" name="message" rows="6" required><?php echo e($message); ?></textarea>

        <button class="button" type="submit">Send message</button>
    </form>

    <aside class="summary">
        <h2>Quick help</h2>
        <p><a class="text-link" href="faq.php#size-guide">Size guide</a></p>
        <p><a class="text-link" href="account.php#orders">Check or change an order</a></p>
        <p><a class="text-link" href="faq.php">Frequently asked questions</a></p>
        <p class="hint">We reply Monday to Friday, 9am to 6pm.</p>
    </aside>
</div>

<?php include 'includes/footer.php'; ?>
