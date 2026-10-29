<?php
// Shared rules for scalar form values. JavaScript uses the same checks.

function is_valid_name($value) {
    return strlen($value) <= 100
        && preg_match('/[a-zA-Z]/', $value) == 1
        && preg_match('/[^a-zA-Z ]/', $value) == 0;
}

function is_valid_email($value) {
    // CS04 username/domain rules, plus localhost for local Mercury mail.
    return strlen($value) <= 150
        && preg_match('/^[\w.-]+@((\w+\.){1,3}\w{2,3}|localhost)$/', $value) == 1
        && preg_match('/\s/', $value) == 0;
}

function is_valid_phone($value) {
    return strlen($value) == 8 && preg_match('/^[689][0-9]{7}$/', $value) == 1;
}

function is_valid_postal_code($value) {
    return strlen($value) == 6 && preg_match('/^[0-9]{6}$/', $value) == 1;
}

function is_valid_quantity($value) {
    if ($value == '' || preg_match('/[^0-9]/', $value) == 1) {
        return false;
    }

    // Check the raw characters before converting, so 1.5 never becomes 1.
    $quantity = (int) $value;
    return $quantity >= 1 && $quantity <= 10;
}

function is_valid_expiry($value) {
    if (strlen($value) != 5 || preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $value) != 1) {
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
