<?php
// Shared rules for scalar form values. JavaScript uses the same checks.

// Casting an array to an array leaves its type and value unchanged.
// An ordinary form string has a different type after this cast (PHP casts/operators).
function is_array_value($value) {
    return $value === (array) $value;
}

// Cart and order forms deliberately use one-level maps of item IDs to values.
// Every other field must be a single string, including URL filter fields.
function valid_request_fields($values, $array_fields) {
    foreach ($values as $key => $value) {
        $expects_array = false;

        foreach ($array_fields as $field) {
            if ($key == $field) {
                $expects_array = true;
            }
        }

        if ($expects_array) {
            if (!is_array_value($value)) {
                return false;
            }

            foreach ($value as $item_id => $item_value) {
                if (!preg_match('/^[1-9][0-9]*$/', (string) $item_id) || is_array_value($item_value)) {
                    return false;
                }
            }
        } elseif (is_array_value($value)) {
            return false;
        }
    }

    return true;
}

function post_value($key, $default = '') {
    if (!isset($_POST[$key]) || is_array_value($_POST[$key])) {
        return $default;
    }

    return $_POST[$key];
}

function get_value($key, $default = '') {
    if (!isset($_GET[$key]) || is_array_value($_GET[$key])) {
        return $default;
    }

    return $_GET[$key];
}

// Count Unicode characters consistently with Array.from() in JavaScript.
function text_length($value) {
    $count = preg_match_all('/./us', $value, $matches);
    return $count === false ? PHP_INT_MAX : $count;
}

function is_valid_name($value) {
    return !is_array_value($value) && text_length($value) <= 100
        && preg_match('/\p{L}/u', $value) == 1
        && preg_match("/[^\p{L}\p{M} '\x{2019}-]/u", $value) == 0;
}

function is_valid_email($value) {
    if (is_array_value($value) || strlen($value) > 150) return false;
    $pattern = <<<'EMAIL'
/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~\-]+@(?:localhost|(?:[a-zA-Z0-9](?:[a-zA-Z0-9\-]*[a-zA-Z0-9])?\.)+[a-zA-Z]{2,63})$/D
EMAIL;
    $local = explode('@', $value)[0];
    return preg_match($pattern, $value) == 1 && $local !== ''
        && $local[0] != '.' && substr($local, -1) != '.' && strpos($local, '..') === false;
}

function valid_id($value) {
    return is_string($value) && preg_match('/^[1-9][0-9]{0,9}$/D', $value) == 1;
}

function hash_shop_password($password) {
    return 'sha256:' . password_hash(hash('sha256', $password), PASSWORD_DEFAULT);
}

function verify_shop_password($password, $hash) {
    if (strpos($hash, 'sha256:') === 0) return password_verify(hash('sha256', $password), substr($hash, 7));
    // Preserve the original byte limit for legacy bcrypt hashes to prevent truncation aliases.
    return strlen($password) <= 72 && password_verify($password, $hash);
}

function is_valid_phone($value) {
    return !is_array_value($value) && strlen($value) == 8 && preg_match('/^[689][0-9]{7}$/', $value) == 1;
}

function is_valid_postal_code($value) {
    return !is_array_value($value) && strlen($value) == 6 && preg_match('/^[0-9]{6}$/', $value) == 1;
}

function is_valid_quantity($value) {
    if (is_array_value($value) || $value == '' || preg_match('/[^0-9]/', $value) == 1) {
        return false;
    }

    // Check the raw characters before converting, so 1.5 never becomes 1.
    $quantity = (int) $value;
    return $quantity >= 1 && $quantity <= 10;
}

function is_valid_expiry($value) {
    if (is_array_value($value) || strlen($value) != 5
        || preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $value) != 1) {
        return false;
    }

    $month = (int) substr($value, 0, 2);
    $year = 2000 + (int) substr($value, 3, 2);
    $current_year = (int) date('Y');
    $current_month = (int) date('n');

    // A card remains valid throughout its printed expiry month.
    return $year > $current_year || ($year == $current_year && $month >= $current_month);
}
?>
