<?php
// Sign in and register on one page (Sessions lecture).
require_once 'includes/functions.php';

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
    $login_email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (!is_valid_email($login_email)) {
        $login_error = 'Please enter a valid email address, up to 150 characters.';
    } elseif ($password == '' || strlen($password) > 72) {
        $login_error = 'Please enter your password, up to 72 characters.';
    } else {
        $safe_email = $db->real_escape_string($login_email);
        $result = $db->query("SELECT * FROM users WHERE email = '$safe_email'");
        $user = $result ? $result->fetch_assoc() : false;

        // Keep the inherited password verification against the saved hash.
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            set_message('Welcome back, ' . $user['name'] . '!');
            header('Location: ' . $next);
            exit;
        }
        $login_error = 'That email and password do not match. Please try again.';
    }
}

if ($action == 'register') {
    $reg_name = isset($_POST['name']) ? $_POST['name'] : '';
    $reg_email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (!is_valid_name($reg_name)) {
        $reg_error = 'Please enter a name using letters and spaces, up to 100 characters.';
    } elseif (!is_valid_email($reg_email)) {
        $reg_error = 'Please enter a valid email address, up to 150 characters.';
    } elseif (strlen($password) < 8 || strlen($password) > 72) {
        $reg_error = 'Your password needs 8 to 72 characters.';
    } elseif ($password != $confirm) {
        $reg_error = 'The two passwords do not match.';
    } else {
        $safe_email = $db->real_escape_string($reg_email);
        $existing = $db->query("SELECT id FROM users WHERE email = '$safe_email'");

        if (!$existing) {
            $reg_error = 'Your account could not be created. Please try again.';
        } elseif ($existing->num_rows > 0) {
            $reg_error = 'An account with this email already exists. Please sign in instead.';
        } else {
            // Keep the inherited hash. The 72-character limit avoids bcrypt truncation.
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $safe_name = $db->real_escape_string($reg_name);
            $saved = $db->query(
                "INSERT INTO users (name, email, password)
                 VALUES ('$safe_name', '$safe_email', '$hash')"
            );

            if ($saved) {
                $_SESSION['user_id'] = $db->insert_id;
                $_SESSION['user_name'] = $reg_name;
                set_message('Welcome to ReStep, ' . $reg_name . '! Your account has been created.');
                header('Location: ' . $next);
                exit;
            }
            $reg_error = 'Your account could not be created. Please try again.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action != 'login' && $action != 'register') {
    $login_error = 'Please choose Sign in or Create account.';
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
        <form id="login-form" action="login.php" method="post">
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="next" value="<?php echo e($next); ?>">

            <label for="login-email">Email</label>
            <input type="email" id="login-email" name="email" value="<?php echo e($login_email); ?>"
                   maxlength="150" required>

            <label for="login-password">Password</label>
            <input type="password" id="login-password" name="password" maxlength="72" required>

            <p id="login-error" class="error-text"></p>
            <button class="button wide" type="submit">Sign in</button>
        </form>
    </div>

    <div class="panel" id="register">
        <h2>New to ReStep?</h2>
        <?php if ($reg_error != '') { ?>
            <p class="error"><?php echo e($reg_error); ?></p>
        <?php } ?>
        <p class="hint">Order emails in this demo are sent only to f31ee@localhost or f32ee@localhost.</p>
        <form id="register-form" action="login.php" method="post">
            <input type="hidden" name="action" value="register">
            <input type="hidden" name="next" value="<?php echo e($next); ?>">

            <label for="reg-name">Name</label>
            <input type="text" id="reg-name" name="name" value="<?php echo e($reg_name); ?>" maxlength="100" required>

            <label for="reg-email">Email</label>
            <input type="email" id="reg-email" name="email" value="<?php echo e($reg_email); ?>"
                   maxlength="150" required>

            <label for="reg-password">Password (8 to 72 characters)</label>
            <input type="password" id="reg-password" name="password" minlength="8" maxlength="72" required>

            <label for="reg-confirm">Confirm password</label>
            <input type="password" id="reg-confirm" name="confirm_password" minlength="8" maxlength="72" required>

            <p id="register-error" class="error-text"></p>
            <button class="button wide" type="submit">Create account</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
