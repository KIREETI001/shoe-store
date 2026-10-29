// ReStep checks use the same rules as PHP. Entered text is kept as typed.
// This script is loaded in the footer, after the form elements exist.

function showError(id, message) {
    document.getElementById(id).innerHTML = message;
}

function isValidName(value) {
    return value.length <= 100 && /[a-zA-Z]/.test(value) && value.search(/[^a-zA-Z ]/) == -1;
}

function isValidEmail(value) {
    // CS04 username/domain rules, plus localhost for local Mercury mail.
    return value.length <= 150
        && /^[\w.-]+@((\w+\.){1,3}\w{2,3}|localhost)$/.test(value)
        && value.search(/\s/) == -1;
}

function isValidPhone(value) {
    return value.length == 8 && /^[689][0-9]{7}$/.test(value);
}

function isValidPostalCode(value) {
    return value.length == 6 && /^[0-9]{6}$/.test(value);
}

function isValidQuantity(value) {
    if (value == "" || value.search(/[^0-9]/) != -1) {
        return false;
    }
    var quantity = parseInt(value, 10);
    return quantity >= 1 && quantity <= 10;
}

function isValidExpiry(value) {
    if (value.length != 5 || !/^(0[1-9]|1[0-2])\/[0-9]{2}$/.test(value)) {
        return false;
    }
    var month = parseInt(value.substring(0, 2), 10);
    var year = 2000 + parseInt(value.substring(3, 5), 10);
    var today = new Date();
    return year > today.getFullYear()
        || (year == today.getFullYear() && month >= today.getMonth() + 1);
}

function checkNameField(id, errorId) {
    var name = document.getElementById(id);
    if (!isValidName(name.value)) {
        showError(errorId, "Please enter a name using letters and spaces, up to 100 characters.");
        name.focus();
        return false;
    }
    return true;
}

function checkEmailField(id, errorId) {
    var email = document.getElementById(id);
    if (!isValidEmail(email.value)) {
        showError(errorId, "Please enter a valid email address, up to 150 characters.");
        email.focus();
        return false;
    }
    return true;
}

// Product subtotal and stock feedback.
function updateSubtotal() {
    var price = parseFloat(document.getElementById("unit-price").value);
    var quantity = document.getElementById("quantity").value;
    if (!isValidQuantity(quantity)) {
        document.getElementById("subtotal").innerHTML = "-";
    } else {
        document.getElementById("subtotal").innerHTML = "S$" + (price * parseInt(quantity, 10)).toFixed(2);
    }
}

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
    showError("product-error", "");
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
    var max = parseInt(quantity.max, 10);
    if (!isValidQuantity(quantity.value) || parseInt(quantity.value, 10) > max) {
        showError("product-error", "Please choose a whole-number quantity from 1 to " + max + ".");
        quantity.focus();
        return false;
    }
    return true;
}

function checkLoginForm() {
    showError("login-error", "");
    if (!checkEmailField("login-email", "login-error")) {
        return false;
    }
    var password = document.getElementById("login-password");
    if (password.value == "" || password.value.length > 72) {
        showError("login-error", "Please enter your password, up to 72 characters.");
        password.focus();
        return false;
    }
    return true;
}

function checkRegisterForm() {
    showError("register-error", "");
    if (!checkNameField("reg-name", "register-error") || !checkEmailField("reg-email", "register-error")) {
        return false;
    }
    var password = document.getElementById("reg-password");
    var confirmPassword = document.getElementById("reg-confirm");
    if (password.value.length < 8 || password.value.length > 72) {
        showError("register-error", "Your password needs 8 to 72 characters.");
        password.focus();
        return false;
    }
    if (password.value != confirmPassword.value) {
        showError("register-error", "The two passwords do not match.");
        confirmPassword.focus();
        return false;
    }
    return true;
}

function checkAccountForm() {
    showError("account-error", "");
    if (!checkNameField("account-name", "account-error")) {
        return false;
    }
    var phone = document.getElementById("account-phone");
    var postal = document.getElementById("account-postal");
    var address = document.getElementById("account-address");
    if (phone.value != "" && !isValidPhone(phone.value)) {
        showError("account-error", "Please enter an 8-digit Singapore phone number, or leave it blank.");
        phone.focus();
        return false;
    }
    if (postal.value != "" && !isValidPostalCode(postal.value)) {
        showError("account-error", "Please enter a 6-digit postal code, or leave it blank.");
        postal.focus();
        return false;
    }
    if (address.value.length > 255) {
        showError("account-error", "Please keep your address within 255 characters.");
        address.focus();
        return false;
    }
    return true;
}

function checkContactForm() {
    showError("contact-error", "");
    if (!checkNameField("contact-name", "contact-error") || !checkEmailField("contact-email", "contact-error")) {
        return false;
    }
    var subject = document.getElementById("contact-subject");
    var topics = ["Sizing and fit", "My order", "Delivery", "Something else"];
    var validTopic = false;
    for (var i = 0; i < topics.length; i++) {
        if (subject.value == topics[i]) {
            validTopic = true;
        }
    }
    if (!validTopic) {
        showError("contact-error", "Please choose a listed topic.");
        subject.focus();
        return false;
    }
    var message = document.getElementById("contact-message");
    if (message.value.search(/\S/) == -1 || message.value.length > 5000) {
        showError("contact-error", "Please enter a message, up to 5000 characters.");
        message.focus();
        return false;
    }
    return true;
}

// Required delivery entries on checkout and order forms.
function checkDeliveryFields(errorId) {
    var phone = document.getElementById("phone");
    var postal = document.getElementById("postal_code");
    var address = document.getElementById("address");
    if (!isValidPhone(phone.value)) {
        showError(errorId, "Please enter an 8-digit Singapore phone number, e.g. 91234567.");
        phone.focus();
        return false;
    }
    if (!isValidPostalCode(postal.value)) {
        showError(errorId, "Please enter a 6-digit postal code.");
        postal.focus();
        return false;
    }
    if (address.value.search(/\S/) == -1 || address.value.length > 255) {
        showError(errorId, "Please enter your delivery address, up to 255 characters.");
        address.focus();
        return false;
    }
    return true;
}

function selectedPayment() {
    var methods = document.getElementsByName("payment");
    for (var i = 0; i < methods.length; i++) {
        if (methods[i].checked) {
            return methods[i].value;
        }
    }
    return "";
}

function updateCardFields() {
    if (selectedPayment() == "Card") {
        document.getElementById("card-fields").style.display = "block";
    } else {
        document.getElementById("card-fields").style.display = "none";
    }
}

function checkCheckoutForm() {
    showError("checkout-error", "");
    if (!checkNameField("name", "checkout-error") || !checkDeliveryFields("checkout-error")) {
        return false;
    }
    var payment = selectedPayment();
    if (payment == "Card") {
        var number = document.getElementById("card_number");
        var expiry = document.getElementById("card_expiry");
        var cvv = document.getElementById("card_cvv");
        var digits = number.value.replace(/ /g, "");
        if (number.value.length > 19 || digits.length != 16 || !/^[0-9]{16}$/.test(digits)) {
            showError("checkout-error", "Please enter the 16-digit card number, with optional spaces.");
            number.focus();
            return false;
        }
        if (!isValidExpiry(expiry.value)) {
            showError("checkout-error", "Please enter a current or future expiry month as MM/YY.");
            expiry.focus();
            return false;
        }
        if (cvv.value.length != 3 || !/^[0-9]{3}$/.test(cvv.value)) {
            showError("checkout-error", "Please enter the 3-digit security code.");
            cvv.focus();
            return false;
        }
    } else if (payment != "Cash on delivery") {
        showError("checkout-error", "Please choose a payment method.");
        return false;
    }
    return true;
}

function checkOrderForm() {
    showError("order-error", "");
    var form = document.getElementById("order-form");
    for (var i = 0; i < form.elements.length; i++) {
        var input = form.elements[i];
        if (input.id.search(/^order-quantity-/) == 0) {
            var itemId = input.id.replace("order-quantity-", "");
            var remove = document.getElementById("order-remove-" + itemId);
            if (!remove.checked && !isValidQuantity(input.value)) {
                showError("order-error", "Please choose a whole-number quantity from 1 to 10"
                    + " for every shoe you are keeping.");
                input.focus();
                return false;
            }
        }
    }
    return checkDeliveryFields("order-error");
}

// Returning false from these property handlers cancels an invalid submission.
var form = document.getElementById("login-form");
if (form) {
    form.onsubmit = checkLoginForm;
}
form = document.getElementById("register-form");
if (form) {
    form.onsubmit = checkRegisterForm;
}
form = document.getElementById("account-form");
if (form) {
    form.onsubmit = checkAccountForm;
}
form = document.getElementById("contact-form");
if (form) {
    form.onsubmit = checkContactForm;
}
form = document.getElementById("product-form");
if (form) {
    form.onsubmit = checkProductForm;
}
form = document.getElementById("order-form");
if (form && document.getElementById("order-error")) {
    form.onsubmit = checkOrderForm;
}
form = document.getElementById("checkout-form");
if (form) {
    form.onsubmit = checkCheckoutForm;
    var paymentMethods = document.getElementsByName("payment");
    for (var i = 0; i < paymentMethods.length; i++) {
        paymentMethods[i].addEventListener("change", updateCardFields, false);
    }
    updateCardFields();
}
