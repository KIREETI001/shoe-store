<?php
// Contact form: the message is saved in the enquiries table (MySQL lecture: INSERT).
require_once 'includes/functions.php';
require_valid_request();

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

$page_title = 'Contact & help';
include 'includes/header.php';
?>

<h1>Contact &amp; help</h1>
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
        <img class="help-photo" src="assets/images/032_lifestyle_suede-terrace_tan-and-cream.jpg"
             alt="Tan and cream ReStep shoe shown side-on for the sizing and fit guide" width="280" height="280">
        <h2>Quick help</h2>
        <p><a class="text-link" href="contact.php#size-guide">Size guide</a></p>
        <p><a class="text-link" href="account.php#orders">Check or change an order</a></p>
        <p><a class="text-link" href="contact.php#help">Frequently asked questions</a></p>
        <p class="hint">We reply Monday to Friday, 9am to 6pm.</p>
    </aside>
</div>

<section class="faq section" id="help">
    <h2>Help &amp; Size Guide</h2>
    <h3>Shopping</h3>
    <details>
        <summary>How do I find the right pair?</summary>
        <p>Choose Sport or Lifestyle, then use the filters on the left to pick an activity, your size and a price.
           Only shoes that have your size in stock are shown.</p>
    </details>
    <details>
        <summary>Do I need an account?</summary>
        <p>You can browse and fill your bag without one. You sign in or create an account at checkout so we can
           send you your order and you can find it again later.</p>
    </details>

    <h3 id="size-guide">Size Guide</h3>
    <p>All our shoes use EU sizes. Measure your foot from heel to longest toe while standing, then find your size
       below. If you are between sizes, choose the bigger one.</p>

    <div class="size-table-container table-scroll" tabindex="0"
         role="region" aria-label="Shoe size conversion guide">
        <table class="size-table">
            <caption>EU shoe sizes and approximate foot lengths</caption>
            <thead>
                <tr>
                    <th scope="col">EU size</th>
                    <th scope="col">UK size</th>
                    <th scope="col">US men</th>
                    <th scope="col">US women</th>
                    <th scope="col">Foot length (cm)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>36</td><td>3.5</td><td>4.5</td><td>6</td><td>22.5</td></tr>
                <tr><td>37</td><td>4</td><td>5</td><td>6.5</td><td>23.0</td></tr>
                <tr><td>38</td><td>5</td><td>6</td><td>7.5</td><td>24.0</td></tr>
                <tr><td>39</td><td>6</td><td>7</td><td>8.5</td><td>24.5</td></tr>
                <tr><td>40</td><td>6.5</td><td>7.5</td><td>9</td><td>25.0</td></tr>
                <tr><td>41</td><td>7.5</td><td>8.5</td><td>10</td><td>26.0</td></tr>
                <tr><td>42</td><td>8</td><td>9</td><td>10.5</td><td>26.5</td></tr>
                <tr><td>43</td><td>9</td><td>10</td><td>11.5</td><td>27.5</td></tr>
                <tr><td>44</td><td>9.5</td><td>10.5</td><td>12</td><td>28.0</td></tr>
                <tr><td>45</td><td>10.5</td><td>11.5</td><td>13</td><td>29.0</td></tr>
                <tr><td>46</td><td>11</td><td>12</td><td>13.5</td><td>29.5</td></tr>
            </tbody>
            <tfoot>
                <tr><td colspan="5">If you are between sizes, choose the bigger one.</td></tr>
            </tfoot>
        </table>
    </div>

    <h3 id="delivery">Orders, delivery &amp; returns</h3>
    <details>
        <summary>What happens after I place an order?</summary>
        <p>You get an email with your order and a link to check it. On that page you can still change sizes,
           quantities or your address, or remove a shoe. When everything looks right, press <em>Confirm
           order</em>. You also get an email after every change.</p>
    </details>
    <details>
        <summary>Can I change my order after confirming it?</summary>
        <p>No. Once confirmed, the order is packed for delivery. Please <a href="contact.php">contact us</a> if
           something is wrong.</p>
    </details>
    <details>
        <summary>How long does delivery take?</summary>
        <p>Delivery is free anywhere in Singapore and takes 3 to 5 working days after you confirm your order.</p>
    </details>
    <details>
        <summary>Can I return shoes?</summary>
        <p>Yes, unworn shoes can be returned within 30 days. <a href="contact.php">Send us a message</a> with your
           order number.</p>
    </details>
    <details>
        <summary>Which payment methods can I use?</summary>
        <p>Credit or debit card, or cash on delivery.</p>
    </details>
</section>

<?php include 'includes/footer.php'; ?>
