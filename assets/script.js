// ReStep JavaScript (JavaScript lecture: getElementById, events, RegExp, form validation).
// These checks give quick feedback in the browser. PHP checks everything again on the server.

// Show a message in an error box (or clear it with "")
function showError(id, message) {
    document.getElementById(id).innerHTML = message;
}

// ---------- Product page ----------

// Update the subtotal when the quantity changes
function updateSubtotal() {
    var price = parseFloat(document.getElementById("unit-price").value);
    var quantity = parseInt(document.getElementById("quantity").value);

    if (isNaN(quantity) || quantity < 1) {
        document.getElementById("subtotal").innerHTML = "-";
    } else {
        document.getElementById("subtotal").innerHTML = "S$" + (price * quantity).toFixed(2);
    }
}

// Tell the customer how many pairs are left when they pick a size
function showStock(stock) {
    var note = document.getElementById("stock-note");
    if (stock <= 3) {
        note.innerHTML = "Hurry, only " + stock + " left in this size!";
    } else {
        note.innerHTML = "In stock.";
    }
    showError("product-error", "");
    document.getElementById("quantity").max = Math.min(stock, 10);
}

function checkProductForm() {
    var sizes = document.getElementsByName("size_id");
    var chosen = false;
    for (var i = 0; i < sizes.length; i++) {
        if (sizes[i].checked) {
            chosen = true;
        }
    }
    if (!chosen) {
        showError("product-error", "Please choose your size first.");
        return false;
    }

    var quantity = document.getElementById("quantity");
    var max = parseInt(quantity.max);
    if (!/^[0-9]+$/.test(quantity.value) || quantity.value < 1 || quantity.value > max) {
        showError("product-error", "Please choose a quantity from 1 to " + max + ".");
        quantity.focus();
        return false;
    }
    return true;
}

// ---------- Register form ----------

function checkRegisterForm() {
    var password = document.getElementById("reg-password").value;
    var confirmPassword = document.getElementById("reg-confirm").value;

    if (password.length < 8) {
        showError("register-error", "Your password needs at least 8 characters.");
        return false;
    }
    if (password != confirmPassword) {
        showError("register-error", "The two passwords do not match.");
        document.getElementById("reg-confirm").focus();
        return false;
    }
    return true;
}

// ---------- Checkout ----------

// Show the card boxes only when "Credit / debit card" is chosen
function showCardFields(show) {
    if (show) {
        document.getElementById("card-fields").style.display = "block";
    } else {
        document.getElementById("card-fields").style.display = "none";
    }
}

// Check phone number and postal code (used on checkout and order pages)
function checkDeliveryFields(errorId) {
    var phone = document.getElementById("phone");
    var postal = document.getElementById("postal_code");

    if (!/^[689][0-9]{7}$/.test(phone.value)) {
        showError(errorId, "Please enter an 8-digit Singapore mobile number, e.g. 91234567.");
        phone.focus();
        return false;
    }
    if (!/^[0-9]{6}$/.test(postal.value)) {
        showError(errorId, "Please enter a 6-digit postal code.");
        postal.focus();
        return false;
    }
    return true;
}

function checkCheckoutForm() {
    showError("checkout-error", "");
    if (!checkDeliveryFields("checkout-error")) {
        return false;
    }

    if (document.getElementById("card-fields").style.display != "none") {
        var number = document.getElementById("card_number");
        var expiry = document.getElementById("card_expiry");
        var cvv = document.getElementById("card_cvv");

        if (!/^[0-9]{16}$/.test(number.value.replace(/ /g, ""))) {
            showError("checkout-error", "Please enter the 16-digit card number.");
            number.focus();
            return false;
        }
        if (!/^(0[1-9]|1[0-2])\/[0-9]{2}$/.test(expiry.value)) {
            showError("checkout-error", "Please enter the expiry date as MM/YY, e.g. 08/28.");
            expiry.focus();
            return false;
        }
        if (!/^[0-9]{3}$/.test(cvv.value)) {
            showError("checkout-error", "Please enter the 3-digit security code from the back of the card.");
            cvv.focus();
            return false;
        }
    }
    return true;
}

// ---------- Order page ----------

function checkOrderForm() {
    showError("order-error", "");
    return checkDeliveryFields("order-error");
}
