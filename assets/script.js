// ReStep checks use the same rules as PHP. Entered text is kept as typed.
// This script is loaded in the footer, after the form elements exist.

function showError(id, message) {
    document.getElementById(id).innerHTML = message;
}

function textLength(value) { return Array.from(value).length; }

function isValidName(value) {
    return textLength(value) <= 100 && /\p{L}/u.test(value) && !/[^\p{L}\p{M} '\u2019\-]/u.test(value);
}

function isValidEmail(value) {
    value = value.trim();
    return value.length <= 150 && /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~\-]+@(?:localhost|(?:[a-zA-Z0-9](?:[a-zA-Z0-9\-]*[a-zA-Z0-9])?\.)+[a-zA-Z]{2,63})$/.test(value)
        && !value.split('@')[0].startsWith('.') && !value.split('@')[0].endsWith('.') && !value.split('@')[0].includes('..');
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
    if (!isValidQuantity(quantity) || Number(quantity) > Number(document.getElementById("quantity").max)) {
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
            if (!isValidQuantity(input.value)) {
                showError("order-error", "Please choose a whole-number quantity from 1 to 10"
                    + " for every shoe you are keeping.");
                input.focus();
                return false;
            }
        }
    }
    return checkDeliveryFields("order-error");
}

function checkAdminPriceForm() {
    showError("admin-price-error", "");
    var price = document.getElementById("admin-price");
    if (!/^(0|[1-9][0-9]{0,5})(\.[0-9]{1,2})?$/.test(price.value)
        || price.value.search(/[^0-9.]/) != -1
        || parseFloat(price.value) < 0.01 || parseFloat(price.value) > 999999.99) {
        showError("admin-price-error", "Enter a price from 0.01 to 999999.99,"
            + " with no more than two decimal places.");
        price.focus();
        return false;
    }
    return true;
}

// One message per editable control. Keep validation rules aligned with PHP.
function getFieldError(field) {
    if (field.disabled || field.readOnly || field.type == "hidden") return "";
    var value = field.value;
    var id = field.id;
    var name = field.name;
    if (/^card_/.test(id) && selectedPayment() != "Card") return "";
    if (field.type == "search") return ""; // Search accepts optional free text.
    if (field.type == "radio") {
        var group = field.form.querySelectorAll('input[type="radio"]');
        for (var i = 0; i < group.length; i++) {
            if (group[i].name == name && group[i].checked && !group[i].disabled) return "";
        }
        return name == "size_id" ? "Please choose an available shoe size." : "Please choose a payment method.";
    }
    var cardRequired = /^card_/.test(id) && selectedPayment() == "Card";
    if (value == "" && !field.required && !cardRequired) return "";
    if (field.tagName == "SELECT") {
        var option = field.options[field.selectedIndex];
        return option && !option.disabled && (!field.required || value != "")
            ? "" : "Please choose an available option from the list.";
    }
    if (name == "name") return isValidName(value) ? "" : "Use letters and spaces, apostrophes or hyphens, up to 100 characters (e.g. Alex Tan).";
    if (field.type == "email") return isValidEmail(value) ? "" : "Enter a valid email, up to 150 characters (e.g. alex@example.com or f31ee@localhost).";
    if (name == "phone") return isValidPhone(value) ? "" : "Enter exactly 8 digits beginning with 6, 8 or 9 (e.g. 91234567).";
    if (name == "postal_code") return isValidPostalCode(value) ? "" : "Enter exactly 6 digits (e.g. 639798).";
    if (name == "address") return /\S/.test(value) && textLength(value) <= 255 ? "" : "Enter an address, up to 255 characters (e.g. block, street and unit number).";
    if (name == "message") return /\S/.test(value) && textLength(value) <= 5000 ? "" : "Enter a message containing text, up to 5000 characters.";
    if (field.type == "password") {
        if (id == "reg-confirm") {
            return textLength(value) >= 8 && textLength(value) <= 72 && value == document.getElementById("reg-password").value
                ? "" : "Repeat your password exactly; it must contain 8 to 72 characters.";
        }
        var minimum = id == "login-password" ? 1 : 8;
        return textLength(value) >= minimum && textLength(value) <= 72 ? "" :
            (minimum == 1 ? "Enter your password, up to 72 characters." : "Enter a password containing 8 to 72 characters.");
    }
    if (id == "card_number") return value.length <= 19 && /^[0-9]{16}$/.test(value.replace(/ /g, "")) ? "" : "Enter 16 card digits, with optional spaces (e.g. 4111 1111 1111 1111).";
    if (id == "card_expiry") return isValidExpiry(value) ? "" : "Enter a current or future expiry as MM/YY (e.g. 12/28).";
    if (id == "card_cvv") return /^[0-9]{3}$/.test(value) ? "" : "Enter exactly 3 security-code digits (e.g. 123).";
    if (name == "price") {
        return /^(0|[1-9][0-9]{0,5})(\.[0-9]{1,2})?$/.test(value) && Number(value) >= 0.01 && Number(value) <= 999999.99
            ? "" : "Enter a price from 0.01 to 999999.99, with at most 2 decimal places (e.g. 99.90).";
    }
    if (name == "quantity" || /^quantity\[/.test(name)) {
        var max = Math.min(10, field.max === "" ? 10 : Number(field.max));
        if (max < 1) return "This size is out of stock. Please choose another available size.";
        return isValidQuantity(value) && Number(value) <= max ? "" : "Enter a whole-number quantity from 1 to " + max + ".";
    }
    if (field.type == "date") {
        return /^\d{4}-\d{2}-\d{2}$/.test(value) && !field.validity.badInput &&
            (!field.min || value >= field.min) && (!field.max || value <= field.max)
            ? "" : "Choose a valid date from " + field.min + " to " + field.max + " (YYYY-MM-DD).";
    }
    if (field.required && !/\S/.test(value)) return "Please fill in this required field.";
    if (field.maxLength > 0 && value.length > field.maxLength) return "Enter no more than " + field.maxLength + " characters.";
    return field.validity.valid ? "" : "Please enter a value in the requested format.";
}

function validateField(field) {
    var message = getFieldError(field);
    field.setAttribute("aria-invalid", message ? "true" : "false");
    var output = document.getElementById(field.dataset.errorId);
    if (output) {
        output.textContent = message;
        output.hidden = !message;
    }
    return message == "";
}

function validateForm(form) {
    var fields = form.querySelectorAll('[data-error-id]');
    var firstInvalid = null;
    for (var i = 0; i < fields.length; i++) {
        fields[i].dataset.touched = "true";
        if (!validateField(fields[i]) && !firstInvalid) firstInvalid = fields[i];
    }
    if (firstInvalid) firstInvalid.focus();
    return !firstInvalid;
}

function refreshDependentFields(field) {
    if (field.id == "reg-password") {
        var confirm = document.getElementById("reg-confirm");
        if (confirm.dataset.touched) validateField(confirm);
    }
    if (field.name == "payment") {
        updateCardFields();
        var cards = document.querySelectorAll('#card-fields input');
        for (var i = 0; i < cards.length; i++) {
            if (cards[i].dataset.touched || selectedPayment() != "Card") validateField(cards[i]);
        }
    }
    if (field.name == "size_id") {
        var quantity = document.getElementById("quantity");
        validateField(quantity);
        updateSubtotal();
    }
    if (/^size\[/.test(field.name)) {
        var row = field.closest("tr");
        var qty = row && row.querySelector('input[type="number"]');
        var option = field.options[field.selectedIndex];
        if (qty && option) {
            qty.max = Math.min(10, Number(option.dataset.stock));
            validateField(qty);
        }
    }
}

function formatPaymentInput(field, inputType) {
    if (field.id != "card_number" && field.id != "card_expiry") return;
    var original = field.value;
    var position = field.selectionStart == null ? original.length : field.selectionStart;
    var digitsBeforeCaret = original.slice(0, position).replace(/\D/g, "").length;
    var digits = original.replace(/\D/g, "");
    var deleting = (inputType || "").indexOf("delete") == 0;
    var formatted;
    if (field.id == "card_number") {
        digits = digits.slice(0, 16);
        formatted = digits.replace(/(.{4})(?=.)/g, "$1 ");
        if (!deleting && digits.length > 0 && digits.length < 16 && digits.length % 4 == 0) formatted += " ";
    } else {
        digits = digits.slice(0, 4);
        formatted = digits.length > 2 ? digits.slice(0, 2) + "/" + digits.slice(2) : digits;
        if (!deleting && digits.length == 2) formatted += "/";
    }
    if (formatted == original) return;
    field.value = formatted;
    var caret = 0;
    var seen = 0;
    while (caret < formatted.length && seen < digitsBeforeCaret) {
        if (/\d/.test(formatted.charAt(caret))) seen++;
        caret++;
    }
    if (!deleting && caret > 0 && /[ /]/.test(formatted.charAt(caret))) caret++;
    field.setSelectionRange(caret, caret);
}

var editableFields = document.querySelectorAll('form input:not([type="hidden"]):not([type="submit"]):not([type="button"]):not(:disabled):not([readonly]), form select:not(:disabled), form textarea:not(:disabled):not([readonly])');
var radioOutputs = {};
for (var fieldIndex = 0; fieldIndex < editableFields.length; fieldIndex++) {
    var field = editableFields[fieldIndex];
    if (!field.id) field.id = "input-" + fieldIndex;
    var groupKey = field.form.id + ":" + field.name;
    var output = field.type == "radio" ? radioOutputs[groupKey] : null;
    if (!output) {
        output = document.createElement("span");
        output.id = field.id + "-feedback";
        output.className = "field-error";
        output.setAttribute("aria-live", "polite");
        output.hidden = true;
        // Keep the original input + size-box adjacency intact and share group errors.
        var anchor = field.type == "radio" ? field.closest(".sizes") || field.closest("label") || field : field;
        anchor.insertAdjacentElement("afterend", output);
        if (field.type == "radio") radioOutputs[groupKey] = output;
    }
    field.dataset.errorId = output.id;
    field.setAttribute("aria-describedby", ((field.getAttribute("aria-describedby") || "") + " " + output.id).trim());
    field.form.noValidate = true;
    ["input", "change", "blur"].forEach(function (eventName) {
        field.addEventListener(eventName, function (event) {
            if (event.isComposing) return;
            if (eventName == "input") formatPaymentInput(this, event.inputType);
            this.dataset.touched = "true";
            validateField(this);
            if (this.type == "radio") {
                var radios = this.form.querySelectorAll('input[type="radio"]');
                for (var i = 0; i < radios.length; i++) {
                    if (radios[i].name == this.name) validateField(radios[i]);
                }
            }
            refreshDependentFields(this);
        });
    });
    if (field.tagName == "SELECT" && /^size\[/.test(field.name)) refreshDependentFields(field);
}

document.querySelectorAll("form").forEach(function (form) {
    form.addEventListener("submit", function (event) {
        if (event.submitter && ["remove_item_id", "remove", "empty"].indexOf(event.submitter.name) != -1) return;
        if (!validateForm(form)) event.preventDefault();
    });
});
if (document.getElementById("card-fields")) updateCardFields();
