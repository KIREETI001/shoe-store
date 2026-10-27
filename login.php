<?php
// Sign in and register on one page (Sessions lecture: Member Registration and Login).
require_once 'includes/functions.php';

// Where to go after signing in, e.g. checkout.php or order.php?id=5
$next = isset($_REQUEST['next']) ? $_REQUEST['next'] : 'account.php';
if (!preg_match('/^(account|checkout|order)\.php(\?id=[0-9]+)?$/', $next)) {
    $next = 'account.php';
}

if (isset($_SESSION['user_id'])) {
    header('Location: ' . $next);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$login_email = '';
$login_error = '';
$reg_name = '';
$reg_email = '';
$reg_error = '';

if ($action == 'login') {
    $login_email = trim($_POST['email']);
    $password = $_POST['password'];

    $safe_email = $db->real_escape_string($login_email);
    $user = $db->query("SELECT * FROM users WHERE email = '$safe_email'")->fetch_assoc();

    // password_verify() checks the typed password against the saved hash
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        set_message('Welcome back, ' . $user['name'] . '!');
        header('Location: ' . $next);
        exit;
    }
    $login_error = 'That email and password do not match. Please try again.';
}

if ($action == 'register') {
    $reg_name = trim($_POST['name']);
    $reg_email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    $safe_email = $db->real_escape_string($reg_email);
    $existing = $db->query("SELECT id FROM users WHERE email = '$safe_email'");

    if ($reg_name == '') {
        $reg_error = 'Please enter your name.';
    } elseif (!preg_match('/^[^@\s]+@[^@\s]+$/', $reg_email)) {
        $reg_error = 'Please enter a valid email address.';
    } elseif ($existing->num_rows > 0) {
        $reg_error = 'An account with this email already exists. Please sign in instead.';
    } elseif (strlen($password) < 8) {
        $reg_error = 'Your password needs at least 8 characters.';
    } elseif ($password != $confirm) {
        $reg_error = 'The two passwords do not match.';
    } else {
        // Never store the real password: password_hash() turns it into a one-way hash
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $safe_name = $db->real_escape_string($reg_name);
        $db->query("INSERT INTO users (name, email, password) VALUES ('$safe_name', '$safe_email', '$hash')");

        $_SESSION['user_id'] = $db->insert_id;
        $_SESSION['user_name'] = $reg_name;
        set_message('Welcome to ReStep, ' . $reg_name . '! Your account has been created.');
        header('Location: ' . $next);
        exit;
    }
}

$page_title = 'Sign in';
include 'includes/header.php';
?>

<h1>Sign in or create an account</h1>
<?php if ($next == 'checkout.php') { ?>
    <p>Sign in to check out. Your bag will be kept.</p>
<?php } ?>

<div class="two-panels">
    <div class="panel">
        <h2>Sign in</h2>
        <?php if ($login_error != '') { ?>
            <p class="error"><?php echo e($login_error); ?></p>
        <?php } ?>
        <form action="login.php" method="post">
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="next" value="<?php echo e($next); ?>">

            <label for="login-email">Email</label>
            <input type="email" id="login-email" name="email" value="<?php echo e($login_email); ?>" required>

            <label for="login-password">Password</label>
            <input type="password" id="login-password" name="password" required>

            <button class="button wide" type="submit">Sign in</button>
        </form>
    </div>

    <div class="panel" id="register">
        <h2>New to ReStep?</h2>
        <?php if ($reg_error != '') { ?>
            <p class="error"><?php echo e($reg_error); ?></p>
        <?php } ?>
        <form action="login.php" method="post" onsubmit="return checkRegisterForm();">
            <input type="hidden" name="action" value="register">
            <input type="hidden" name="next" value="<?php echo e($next); ?>">

            <label for="reg-name">Name</label>
            <input type="text" id="reg-name" name="name" value="<?php echo e($reg_name); ?>" required>

            <label for="reg-email">Email</label>
            <input type="email" id="reg-email" name="email" value="<?php echo e($reg_email); ?>" required>

            <label for="reg-password">Password (at least 8 characters)</label>
            <input type="password" id="reg-password" name="password" minlength="8" required>

            <label for="reg-confirm">Confirm password</label>
            <input type="password" id="reg-confirm" name="confirm_password" minlength="8" required>

            <p id="register-error" class="error-text"></p>
            <button class="button wide" type="submit">Create account</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
