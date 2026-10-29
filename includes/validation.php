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

function is_valid_name($value) {
    return !is_array_value($value) && strlen($value) <= 100
        && preg_match('/[a-zA-Z]/', $value) == 1
        && preg_match('/[^a-zA-Z ]/', $value) == 0;
}

function is_valid_email($value) {
    // CS04 username/domain rules, plus localhost for local Mercury mail.
    return !is_array_value($value) && strlen($value) <= 150
        && preg_match('/^[\w.-]+@((\w+\.){1,3}\w{2,3}|localhost)$/', $value) == 1
        && preg_match('/\s/', $value) == 0;
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
