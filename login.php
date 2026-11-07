<?php
// Sign in and register on one page (Sessions lecture).
require_once 'includes/functions.php';
require_valid_request();

$next = post_value('next', get_value('next', 'account.php'));
if (!preg_match('/^(account|checkout|order|admin)\.php(\?id=[0-9]+)?$/', $next)) {
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
    $login_email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (!is_valid_email($login_email)) {
        $login_error = 'Enter a valid email using gmail.com, outlook.com or localhost (maximum 150 characters).';
    } elseif ($password == '' || text_length($password) > 72) {
        $login_error = 'Please enter your password, up to 72 characters.';
    } else {
        $safe_email = $db->real_escape_string($login_email);
        $result = $db->query("SELECT * FROM users WHERE email = '$safe_email'");
        $user = $result ? $result->fetch_assoc() : false;

        // Keep the inherited password verification against the saved hash.
        if ($user && verify_shop_password($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
    $reg_email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (!is_valid_name($reg_name)) {
        $reg_error = 'Please enter a name using letters, spaces, apostrophes or hyphens, up to 100 characters.';
    } elseif (!is_valid_email($reg_email)) {
        $reg_error = 'Enter a valid email using gmail.com, outlook.com or localhost (maximum 150 characters).';
    } elseif (text_length($password) < 8 || text_length($password) > 72) {
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
            // Prehash new passwords so Unicode passwords are not truncated by bcrypt.
            $hash = hash_shop_password($password);
            $safe_name = $db->real_escape_string($reg_name);
            $saved = $db->query(
                "INSERT INTO users (name, email, password)
                 VALUES ('$safe_name', '$safe_email', '$hash')"
            );

            if ($saved) {
                session_regenerate_id(true);
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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

$page_title = 'Sign In';
include 'includes/header.php';
?>

<div class="page-intro">
    <div>
        <p class="eyebrow">MY RESTEP</p>
        <h1>Sign In Or Create An Account</h1>
        <p>Save your delivery details and keep your shoe orders together.</p>
    </div>
    <img src="assets/images/056_lifestyle_retro-casual-low-top_cream-and-burgundy.jpg"
         alt="Cream and burgundy ReStep Retro shoe from the lifestyle collection" width="220" height="220">
</div>
<?php if ($next == 'checkout.php') { ?>
    <p>Sign in to check out. Your bag will be kept.</p>
<?php } ?>

<p class="hint">Fields marked <span class="required">*</span> are required.</p>
<div class="two-panels">
    <div class="panel">
        <h2>Sign In</h2>
        <?php if ($login_error != '') { ?>
            <p class="error"><?php echo e($login_error); ?></p>
        <?php } ?>
        <form id="login-form" action="login.php" method="post">
        <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="next" value="<?php echo e($next); ?>">

            <label for="login-email">Email <span class="required">*</span></label>
            <input type="email" id="login-email" name="email" value="<?php echo e($login_email); ?>"
                   maxlength="150" required>

            <label for="login-password">Password <span class="required">*</span></label>
            <input type="password" id="login-password" name="password" maxlength="144" required>

            <p id="login-error" class="error-text"></p>
            <button class="button wide" type="submit">Sign in</button>
        </form>
    </div>

    <div class="panel" id="register">
        <h2>New To ReStep?</h2>
        <?php if ($reg_error != '') { ?>
            <p class="error"><?php echo e($reg_error); ?></p>
        <?php } ?>
        <p class="hint">Order emails in this demo are sent only to f32ee@localhost.</p>
        <form id="register-form" action="login.php" method="post">
        <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="register">
            <input type="hidden" name="next" value="<?php echo e($next); ?>">

            <label for="reg-name">Name <span class="required">*</span></label>
            <input type="text" id="reg-name" name="name" value="<?php echo e($reg_name); ?>" maxlength="200" required>

            <label for="reg-email">Email <span class="required">*</span></label>
            <input type="email" id="reg-email" name="email" value="<?php echo e($reg_email); ?>"
                   maxlength="150" required>

            <label for="reg-password">Password (8 to 72 characters) <span class="required">*</span></label>
            <input type="password" id="reg-password" name="password" minlength="8" maxlength="144" required>

            <label for="reg-confirm">Confirm password <span class="required">*</span></label>
            <input type="password" id="reg-confirm" name="confirm_password" minlength="8" maxlength="144" required>

            <p id="register-error" class="error-text"></p>
            <button class="button wide" type="submit">Create account</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
