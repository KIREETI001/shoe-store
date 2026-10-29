# ReStep – online shoe shop (IE4727 Theme 2)

A web portal for buying sport and lifestyle shoes, built with HTML, CSS, JavaScript, PHP and MySQL.

## Set up in XAMPP

1. Keep the `shoe-store` folder in `C:\xampp\htdocs\IE4727\`.
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Open phpMyAdmin, create a database called `restep`, select it, then import
   `database/schema.sql` first and `database/seed.sql` second.
4. Check `config.php` and your private `config.local.php` override. The current setup
   uses MySQL on port **3306** and Apache on port **8000**.
5. Open http://localhost:8000/IE4727/shoe-store/ and create an account.

Use a different port or folder if your XAMPP setup requires it, and update `$site_url`
to the full application URL including its trailing slash. Order emails use this URL.

An example private override is:

```php
<?php
$site_url = 'http://localhost:8000/IE4727/shoe-store/';
$db_host = '127.0.0.1';
$db_port = 3306;
$db_name = 'restep';

$allowed_mail_recipients = array(
    'f31ee@localhost',
    'f32ee@localhost'
);

$shop_email = '';
?>
```

Keep any machine-specific database login in the private override. Do not include
that file in the submission. Import the schema/seed only into an empty database;
do not re-import them over existing orders to prepare a test.

## Local order mail

The team confirmed **f31ee@localhost** and **f32ee@localhost** as its two permitted
Mercury accounts. Only these exact recipients may receive an order email. Another
`@localhost` account and every external address are rejected before `mail()`.

Register using the appropriate confirmed account to receive the acknowledgement
in Thunderbird. Checkout always uses the saved account email and ignores any
posted replacement. Other valid account addresses can use the site but cannot
receive order mail.

An empty `$allowed_mail_recipients` array disables mail. A blank `$shop_email` uses
the matched permitted recipient as the sender; a configured sender must also be
a local address. A successful `mail()` return means the mail server accepted the
message, not that the inbox received it. The order pages report mail failure
separately from a successful order action.

## Pages

| Page | What it does |
| --- | --- |
| `index.php` | Home page with collections and popular shoes |
| `products.php` | All shoes with search, collection, activity, size, price and sort filters |
| `product.php` | One shoe: photo, sizes in stock, quantity, live subtotal, add to bag |
| `cart.php` | Bag: change size or quantity, remove items, see total |
| `login.php` / `logout.php` | Sign in, register, sign out |
| `checkout.php` | Delivery details and payment, places the order and sends the email |
| `order.php` | Opened from the email link: check the order, change sizes / quantities / address, then confirm or cancel |
| `account.php` | Saved details and order history |
| `faq.php`, `about.php`, `contact.php` | Help and size guide, our story, contact form (saved to the database) |

Shared code is in `includes/` (`functions.php` starts the session and connects to the database,
`header.php` and `footer.php` are on every page).

## How an order works

1. The customer adds shoes to the bag (`$_SESSION['cart']` stores size id => quantity).
2. At checkout they enter delivery and payment details. PHP checks every field, checks stock,
   reserves available stock, saves the order in `orders` and `order_items`, and
   empties the bag only after the required saves succeed.
3. `mail()` sends an acknowledgement email with a link to `order.php?id=...`.
4. The order is **Pending**. On the order page the customer can see each shoe they ordered and
   change the size, quantity or delivery address, remove a shoe, or cancel the order.
   A complete valid change adjusts stock by the quantity difference and attempts
   an updated email. An unchanged save sends no extra email. An invalid edit is
   rejected before any delivery/item/stock write.
5. When everything is right they press **Confirm order**. The order becomes **Confirmed**
   and can no longer be changed.

## Database tables

`users`, `products`, `product_sizes` (stock per EU size), `orders`, `order_items`, `enquiries`.
See the comments in `database/schema.sql`.

## Test accounts and data

There are no ready-made users. Register a new account to test. Some sizes have zero stock on
purpose so you can see "sold out" sizes (for example ReStep Zip in EU 46).
Use a made-up card number such as `4111 1111 1111 1111`; card details are only checked for
format and are never saved.

The first four execution steps add these shared form rules:

- Names contain letters and spaces, with at least one letter, up to 100 characters
  (the selected Case Study 04 rule).
- Email follows the Case Study 04 pattern with a `localhost` exception, up to 150
  characters. Receiving mail still requires an exact permitted address.
- Phone is eight digits starting with 6, 8 or 9; postal code is six digits. Optional
  account phone/postal fields may be blank. Address is limited to 255 characters.
- Registration passwords are 8 to 72 characters for the inherited hash; contact
  messages are limited to 5000 characters. PHP measures string limits in bytes.
- Each product/size has a combined quantity of 1 to 10 in the bag and order. Decimal
  quantities are rejected before conversion.
- Card expiry must be MM/YY and not earlier than the current month. The printed
  current month is valid through month end. Use a future demo expiry when testing.

Use a separate disposable test database and record stock before and after tests.
Automated checks must capture mail rather than actually sending to a prohibited
recipient. The implementation results are recorded in `docs/STEPS_1_TO_4_RESULTS.md`.

## Recovery limits and remaining work

Stock reservations use conditional UPDATE and `affected_rows`. Order changes are
fully validated before writes; later write failures attempt to restore earlier
values/reservations using ordinary queries. These are separate queries, so a crash
or concurrent edit can still leave incomplete recovery. This is not an atomic SQL
transaction. The implementation uses the supplied module's demonstrated patterns.

The user approved one PHP 8 compatibility setting in `includes/db.php`:

```php
mysqli_report(MYSQLI_REPORT_OFF);
```

It makes failed queries return false so the lecture-style result checks and
recovery branches can run. PHP 8's default mysqli exceptions would otherwise
bypass them. This setting was not found in the supplied notes and is documented
as an approved compatibility exception, not as a taught course example.

Array-valued forged POST fields are outside the current scalar-form checks. PHP
byte lengths and JavaScript character lengths can differ for non-ASCII input.
These limits are not claims of comprehensive production security.

Simulated declined payment and a duplicate-purchase attempt guard remain with the
teammate's step 5. Page imagery, table markup, FAQ/About content, Admin and the
modern version remain separate later work. This change does not claim the entire
Base Version has passed final acceptance.

## Submission package

Include the application PHP files, `includes/`, local `assets/`, `database/` and
the setup README. Supply the required report, design documents and executed test
evidence separately as directed by the course.

Exclude `.git/`, `config.local.php`, credentials, logs, temporary test tools and the
internal `docs/CODEX_HANDOVER.md`. The unused JSON image manifest has been removed;
its 60 source references are preserved in `docs/IMAGE_ATTRIBUTION.txt` for the
report. The site serves local JPEGs and has no dependency on those source URLs.
