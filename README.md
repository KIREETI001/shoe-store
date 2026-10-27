# ReStep – online shoe shop (IE4727 Theme 2)

A web portal for buying sport and lifestyle shoes, built with HTML, CSS, JavaScript, PHP and MySQL.

## Set up in XAMPP

1. Copy the `shoe-store` folder into `C:\xampp\htdocs\`.
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Open phpMyAdmin, create a database called `restep`, select it, then import
   `database/schema.sql` first and `database/seed.sql` second.
4. Check `config.php` (database login, `$site_url` and `$shop_email`).
5. Open http://localhost/shoe-store/ and create an account.

For order emails in the lab, register with your Mercury account (e.g. `f31ee@localhost`)
so the email arrives in Thunderbird.

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
   saves the order in `orders` and `order_items`, reduces stock and empties the bag.
3. `mail()` sends an acknowledgement email with a link to `order.php?id=...`.
4. The order is **Pending**. On the order page the customer can see each shoe they ordered and
   change the size, quantity or delivery address, remove a shoe, or cancel the order.
   Every change updates the stock and sends an updated email.
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
