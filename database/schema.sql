-- ReStep database tables.
-- In phpMyAdmin: create an empty database called restep, select it,
-- then import this file first and seed.sql second.

-- Registered customers
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL DEFAULT '',
    address VARCHAR(255) NOT NULL DEFAULT '',
    postal_code VARCHAR(10) NOT NULL DEFAULT ''
);

-- The shoes we sell
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(20) NOT NULL,      -- Sport or Lifestyle
    activity VARCHAR(50) NOT NULL,      -- Running, Court, Loafers ...
    colour VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(150) NOT NULL,        -- file name inside assets/images
    price DECIMAL(8,2) NOT NULL
);

-- How many pairs of each size are in stock
CREATE TABLE product_sizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    size INT NOT NULL,                  -- EU size, 36 to 46
    stock INT NOT NULL DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- One row per order. status is Pending (customer can still change it),
-- Confirmed (customer has checked and locked it) or Cancelled.
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    postal_code VARCHAR(10) NOT NULL,
    payment_method VARCHAR(30) NOT NULL,
    total DECIMAL(8,2) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- The shoes inside each order. Name and price are copied so the
-- order history does not change if the product is edited later.
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    size_id INT NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    size INT NOT NULL,
    price DECIMAL(8,2) NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (size_id) REFERENCES product_sizes(id)
);

-- Messages sent from the Contact Us page
CREATE TABLE enquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
