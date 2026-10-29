<?php
// Contact form: the message is saved in the enquiries table (MySQL lecture: INSERT).
require_once 'includes/functions.php';

$name = '';
$email = '';
$subject = '';
$message = '';
$error = '';
$topics = array('Sizing and fit', 'My order', 'Delivery', 'Something else');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
    $message = isset($_POST['message']) ? $_POST['message'] : '';
    $valid_topic = false;
    foreach ($topics as $topic) {
        if ($subject == $topic) {
            $valid_topic = true;
        }
    }

    if (!is_valid_name($name)) {
        $error = 'Please enter a name using letters and spaces, up to 100 characters.';
    } elseif (!is_valid_email($email)) {
        $error = 'Please enter a valid email address, up to 150 characters.';
    } elseif (!$valid_topic) {
        $error = 'Please choose a listed topic.';
    } elseif (preg_match('/\S/', $message) != 1 || strlen($message) > 5000) {
        $error = 'Please enter a message, up to 5000 characters.';
    } else {
        $s_name = $db->real_escape_string($name);
        $s_email = $db->real_escape_string($email);
        $s_subject = $db->real_escape_string($subject);
        $s_message = $db->real_escape_string($message);
        $saved = $db->query(
            "INSERT INTO enquiries (name, email, subject, message)
             VALUES ('$s_name', '$s_email', '$s_subject', '$s_message')"
        );
        if ($saved) {
            set_message('Thanks, ' . $name . '! Your message has been received.');
            header('Location: contact.php');
            exit;
        }
        $error = 'Your message could not be saved. Please try again.';
    }
}

// Fill in the name and email for signed-in customers
if ($_SERVER['REQUEST_METHOD'] != 'POST' && isset($_SESSION['user_id'])) {
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

<div class="cart-layout contact-layout">
    <form id="contact-form" class="panel" action="contact.php" method="post">
        <?php if ($error != '') { ?>
            <p class="error"><?php echo e($error); ?></p>
        <?php } ?>
        <label for="contact-name">Name</label>
        <input type="text" id="contact-name" name="name" value="<?php echo e($name); ?>" maxlength="100" required>

        <label for="contact-email">Email</label>
        <input type="email" id="contact-email" name="email" value="<?php echo e($email); ?>" maxlength="150" required>

        <label for="contact-subject">Subject</label>
        <select id="contact-subject" name="subject" required>
            <option value="">Choose a topic</option>
            <?php foreach ($topics as $topic) { ?>
                <option <?php if ($subject == $topic) echo 'selected'; ?>><?php echo $topic; ?></option>
            <?php } ?>
        </select>

        <label for="contact-message">Message</label>
        <textarea id="contact-message" name="message" rows="6"
                  maxlength="5000" required><?php echo e($message); ?></textarea>

        <p id="contact-error" class="error-text"></p>
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
