-- ============================================================
--  SpiceMart – Full-Featured E-Commerce Database v2
--  Passwords: plain text (no hashing)
--  mysql -u root -p < database.sql
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP DATABASE IF EXISTS spicemart;
CREATE DATABASE spicemart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE spicemart;

-- ── users ────────────────────────────────────────────────────
CREATE TABLE users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    phone           VARCHAR(20)  DEFAULT NULL,
    password        VARCHAR(255) NOT NULL,
    address         TEXT         DEFAULT NULL,
    role            ENUM('admin','staff','customer') NOT NULL DEFAULT 'customer',
    permissions     VARCHAR(500) DEFAULT NULL,
    is_blocked      TINYINT(1)   DEFAULT 0,
    reset_token     VARCHAR(64)  DEFAULT NULL,
    reset_expires   DATETIME     DEFAULT NULL,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── brands ───────────────────────────────────────────────────
CREATE TABLE brands (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    slug       VARCHAR(120) NOT NULL UNIQUE,
    image      VARCHAR(255) DEFAULT NULL,
    status     TINYINT(1)   DEFAULT 1,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── categories (with parent_id for sub-categories) ───────────
CREATE TABLE categories (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id INT UNSIGNED DEFAULT NULL,
    name      VARCHAR(100) NOT NULL,
    slug      VARCHAR(120) NOT NULL UNIQUE,
    image     VARCHAR(255) DEFAULT NULL,
    status    TINYINT(1)   DEFAULT 1,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── products ─────────────────────────────────────────────────
CREATE TABLE products (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id  INT UNSIGNED  NOT NULL,
    brand_id     INT UNSIGNED  DEFAULT NULL,
    name         VARCHAR(150)  NOT NULL,
    slug         VARCHAR(160)  NOT NULL UNIQUE,
    description  TEXT          DEFAULT NULL,
    price        DECIMAL(10,2) NOT NULL,
    offer_price  DECIMAL(10,2) DEFAULT NULL,
    gst          DECIMAL(5,2)  DEFAULT 0.00,
    stock_qty    INT UNSIGNED  NOT NULL DEFAULT 0,
    weight       VARCHAR(50)   DEFAULT NULL,
    image        VARCHAR(255)  DEFAULT NULL,
    tags         VARCHAR(300)  DEFAULT NULL,
    meta_title   VARCHAR(200)  DEFAULT NULL,
    meta_desc    TEXT          DEFAULT NULL,
    is_featured  TINYINT(1)    DEFAULT 0,
    status       TINYINT(1)    NOT NULL DEFAULT 1,
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (brand_id)    REFERENCES brands(id)     ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── product_images ───────────────────────────────────────────
CREATE TABLE product_images (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    image      VARCHAR(255) NOT NULL,
    is_primary TINYINT(1)   DEFAULT 0,
    sort_order INT          DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── product_variants ─────────────────────────────────────────
CREATE TABLE product_variants (
    id             INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    product_id     INT UNSIGNED  NOT NULL,
    variant_type   VARCHAR(50)   NOT NULL,
    variant_value  VARCHAR(100)  NOT NULL,
    price_modifier DECIMAL(10,2) DEFAULT 0.00,
    stock_qty      INT UNSIGNED  DEFAULT 0,
    sku            VARCHAR(100)  DEFAULT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── orders ───────────────────────────────────────────────────
CREATE TABLE orders (
    id               INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    user_id          INT UNSIGNED  NOT NULL,
    total_amount     DECIMAL(10,2) NOT NULL,
    shipping_charge  DECIMAL(10,2) DEFAULT 0.00,
    coupon_code      VARCHAR(50)   DEFAULT NULL,
    coupon_discount  DECIMAL(10,2) DEFAULT 0.00,
    shipping_address TEXT          NOT NULL,
    payment_method   ENUM('cod','razorpay','payu','wallet') NOT NULL DEFAULT 'cod',
    payment_status   ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    transaction_id   VARCHAR(200)  DEFAULT NULL,
    status           ENUM('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    tracking_no      VARCHAR(100)  DEFAULT NULL,
    courier_name     VARCHAR(100)  DEFAULT NULL,
    notes            TEXT          DEFAULT NULL,
    created_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── order_items ──────────────────────────────────────────────
CREATE TABLE order_items (
    id            INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    order_id      INT UNSIGNED  NOT NULL,
    product_id    INT UNSIGNED  NOT NULL,
    product_name  VARCHAR(150)  NOT NULL,
    variant_label VARCHAR(200)  DEFAULT NULL,
    quantity      INT UNSIGNED  NOT NULL DEFAULT 1,
    unit_price    DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── cart ─────────────────────────────────────────────────────
CREATE TABLE cart (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NOT NULL,
    product_id    INT UNSIGNED NOT NULL,
    variant_id    INT UNSIGNED DEFAULT NULL,
    variant_label VARCHAR(200) DEFAULT NULL,
    quantity      INT UNSIGNED NOT NULL DEFAULT 1,
    UNIQUE KEY uq_cart_item (user_id, product_id, variant_id),
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── wishlist ─────────────────────────────────────────────────
CREATE TABLE wishlist (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_wishlist (user_id, product_id),
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── addresses ────────────────────────────────────────────────
CREATE TABLE addresses (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL,
    label        VARCHAR(50)  DEFAULT 'Home',
    name         VARCHAR(100) NOT NULL,
    phone        VARCHAR(20)  NOT NULL,
    address_line TEXT         NOT NULL,
    city         VARCHAR(100) NOT NULL,
    state        VARCHAR(100) NOT NULL,
    pincode      VARCHAR(10)  NOT NULL,
    is_default   TINYINT(1)   DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── reviews ──────────────────────────────────────────────────
CREATE TABLE reviews (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    rating     TINYINT(1)   NOT NULL,
    comment    TEXT         DEFAULT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_review (product_id, user_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── coupons ──────────────────────────────────────────────────
CREATE TABLE coupons (
    id           INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    code         VARCHAR(50)   NOT NULL UNIQUE,
    type         ENUM('percent','flat') NOT NULL DEFAULT 'percent',
    value        DECIMAL(10,2) NOT NULL,
    min_order    DECIMAL(10,2) DEFAULT 0.00,
    max_discount DECIMAL(10,2) DEFAULT NULL,
    uses_limit   INT           DEFAULT NULL,
    uses_count   INT           DEFAULT 0,
    expires_at   DATE          DEFAULT NULL,
    status       TINYINT(1)    DEFAULT 1,
    created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── returns ──────────────────────────────────────────────────
CREATE TABLE returns (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id   INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    type       ENUM('cancel','return') NOT NULL DEFAULT 'cancel',
    reason     TEXT         NOT NULL,
    status     ENUM('pending','approved','rejected') DEFAULT 'pending',
    admin_note TEXT         DEFAULT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── banners ──────────────────────────────────────────────────
CREATE TABLE banners (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(200) DEFAULT NULL,
    subtitle   VARCHAR(300) DEFAULT NULL,
    image      VARCHAR(255) NOT NULL,
    link_url   VARCHAR(300) DEFAULT NULL,
    btn_text   VARCHAR(100) DEFAULT 'Shop Now',
    type       ENUM('slider','offer','popup') DEFAULT 'slider',
    sort_order INT          DEFAULT 0,
    status     TINYINT(1)   DEFAULT 1,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── cms_pages ────────────────────────────────────────────────
CREATE TABLE cms_pages (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug       VARCHAR(100) NOT NULL UNIQUE,
    title      VARCHAR(200) NOT NULL,
    content    LONGTEXT,
    meta_title VARCHAR(200) DEFAULT NULL,
    meta_desc  TEXT         DEFAULT NULL,
    status     TINYINT(1)   DEFAULT 1
) ENGINE=InnoDB;

-- ── contacts ─────────────────────────────────────────────────
CREATE TABLE contacts (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    phone      VARCHAR(20)  DEFAULT NULL,
    subject    VARCHAR(200) DEFAULT NULL,
    message    TEXT         NOT NULL,
    is_read    TINYINT(1)   DEFAULT 0,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── shipping_settings ────────────────────────────────────────
CREATE TABLE shipping_settings (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key_name  VARCHAR(100) NOT NULL UNIQUE,
    key_value TEXT         NOT NULL
) ENGINE=InnoDB;

-- ── otp_codes ────────────────────────────────────────────────
CREATE TABLE otp_codes (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(150) NOT NULL,
    otp        VARCHAR(6)   NOT NULL,
    token      VARCHAR(64)  NOT NULL,
    type       ENUM('login','reset') DEFAULT 'reset',
    expires_at DATETIME     NOT NULL,
    is_used    TINYINT(1)   DEFAULT 0,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  SEED DATA
-- ============================================================

-- Users (plain text passwords)
INSERT INTO users (name, email, phone, password, role) VALUES
('Admin User',  'admin@spice.com',    '9876543210', 'Admin@123', 'admin'),
('Priya Sharma','priya@example.com',  '9123456780', 'Test@123',  'customer');
UPDATE users SET address = '12, Gandhi Nagar, Chennai, Tamil Nadu - 600001' WHERE id = 2;

-- Brands
INSERT INTO brands (name, slug, status) VALUES
('SpiceMart Organic','spicemart-organic',1),
('FarmFresh',        'farmfresh',        1),
('Pure Harvest',     'pure-harvest',     1);

-- Categories (main)
INSERT INTO categories (parent_id, name, slug, status) VALUES
(NULL,'Whole Spices',  'whole-spices',  1),
(NULL,'Ground Masala', 'ground-masala', 1),
(NULL,'Blended Masala','blended-masala',1),
(NULL,'Seeds',         'seeds',         1),
(NULL,'Dry Fruits',    'dry-fruits',    1);

-- Sub-categories
INSERT INTO categories (parent_id, name, slug, status) VALUES
(1,'Kerala Spices',      'kerala-spices',       1),
(1,'Exotic Spices',      'exotic-spices',        1),
(2,'Single Spice Powder','single-spice-powder',  1),
(3,'South Indian Masala','south-indian-masala',  1),
(3,'North Indian Masala','north-indian-masala',  1);

-- Products
INSERT INTO products (category_id,brand_id,name,slug,description,price,offer_price,gst,stock_qty,weight,image,is_featured,status) VALUES
(1,1,'Premium Cardamom','premium-cardamom','Hand-picked green cardamom from Kerala. Intensely aromatic.',220.00,199.00,5.00,150,'100g','cardamom.jpg',1,1),
(1,2,'Ceylon Cinnamon Sticks','ceylon-cinnamon-sticks','True Ceylon cinnamon, delicate and sweet.',180.00,NULL,5.00,200,'200g','cinnamon.jpg',0,1),
(1,1,'Star Anise','star-anise','Whole star anise with bold liquorice fragrance.',150.00,135.00,5.00,120,'100g','staranise.jpg',0,1),
(2,1,'Pure Turmeric Powder','pure-turmeric-powder','Stone-ground Erode turmeric 3.5%+ curcumin.',95.00,85.00,5.00,300,'250g','turmeric.jpg',1,1),
(2,2,'Kashmiri Red Chilli Powder','kashmiri-red-chilli-powder','Mild heat, deep crimson colour.',130.00,NULL,5.00,250,'200g','kashmiri.jpg',0,1),
(2,3,'Coriander Powder','coriander-powder','Freshly ground, citrusy and nutty.',80.00,72.00,5.00,280,'250g','coriander.jpg',0,1),
(3,1,'Garam Masala Premium','garam-masala-premium','Signature blend of 12 whole spices.',175.00,155.00,12.00,180,'100g','garam.jpg',1,1),
(3,2,'Chicken Masala','chicken-masala','Restaurant-style chicken masala blend.',160.00,NULL,12.00,160,'100g','chicken_masala.jpg',0,1),
(3,1,'Biryani Masala','biryani-masala','Authentic biryani spice blend.',190.00,170.00,12.00,140,'100g','biryani.jpg',1,1),
(3,3,'Sambar Powder','sambar-powder','Traditional South Indian sambar powder.',120.00,NULL,12.00,200,'200g','sambar.jpg',0,1),
(4,1,'Black Mustard Seeds','black-mustard-seeds','Essential for South Indian tempering.',65.00,59.00,5.00,350,'250g','mustard.jpg',0,1),
(4,2,'Cumin Seeds (Jeera)','cumin-seeds-jeera','Rajasthani cumin, intense warm aroma.',110.00,NULL,5.00,300,'250g','cumin.jpg',1,1),
(4,3,'Fenugreek Seeds','fenugreek-seeds','Slightly bitter, maple-like aroma.',75.00,NULL,5.00,220,'200g','fenugreek.jpg',0,1),
(5,1,'Kashmiri Walnuts','kashmiri-walnuts','Premium paper-shell walnuts from Kashmir.',650.00,599.00,18.00,80,'500g','walnuts.jpg',1,1),
(5,2,'Seedless Raisins','seedless-raisins','Juicy plump raisins from Nashik.',220.00,NULL,5.00,150,'500g','raisins.jpg',0,1);

-- Product Variants (size variants for first few products)
INSERT INTO product_variants (product_id,variant_type,variant_value,price_modifier,stock_qty) VALUES
(4,'size','100g', -45.00,100),
(4,'size','250g',  0.00, 300),
(4,'size','500g',  85.00, 80),
(7,'size','50g',  -80.00,100),
(7,'size','100g',  0.00, 180),
(7,'size','200g',  155.00,60);

-- Coupons
INSERT INTO coupons (code,type,value,min_order,max_discount,uses_limit,expires_at,status) VALUES
('WELCOME10','percent',10.00, 200.00, 50.00,100,'2027-12-31',1),
('FLAT50',   'flat',   50.00, 500.00, 50.00, 50,'2027-12-31',1),
('SPICE20',  'percent',20.00,1000.00,200.00, 25,'2027-06-30',1);

-- CMS Pages
INSERT INTO cms_pages (slug,title,content,status) VALUES
('about','About Us','<h4>About SpiceMart</h4><p>SpiceMart was founded with a passion for bringing the finest farm-fresh spices directly to your kitchen. We source directly from farmers across India — Kerala, Kashmir, Rajasthan — ensuring 100% purity with no additives or preservatives.</p>',1),
('terms','Terms & Conditions','<h4>Terms & Conditions</h4><p>By using SpiceMart you agree to these terms. Products are sold for personal use only. We reserve the right to modify prices and availability at any time. All orders are subject to confirmation.</p>',1),
('privacy','Privacy Policy','<h4>Privacy Policy</h4><p>SpiceMart collects personal information such as name, email, and address for order processing only. We do not share your data with third parties. Payment details are processed securely.</p>',1),
('return-policy','Return Policy','<h4>Return Policy</h4><p>We accept returns within 7 days of delivery for damaged or incorrect products. Items must be unused and in original packaging. Refunds are processed within 5-7 business days.</p>',1);

-- Shipping Settings
INSERT INTO shipping_settings (key_name,key_value) VALUES
('free_shipping_above','499'),
('standard_charge',    '60'),
('express_charge',     '120'),
('razorpay_key_id',    'rzp_test_placeholder'),
('razorpay_key_secret','placeholder_secret'),
('estimated_days',     '3-5');

-- Banners
INSERT INTO banners (title,subtitle,image,link_url,btn_text,type,sort_order,status) VALUES
('Fresh Spices From Farm','Directly sourced from finest farms across India','banner1.jpg','/stack-change/spicemart/shop','Shop Now','slider',1,1),
('Premium Masala Collection','Authentic blends for restaurant-quality cooking','banner2.jpg','/stack-change/spicemart/shop?category=blended-masala','Explore','slider',2,1),
('Flat ₹50 Off','Use code FLAT50 on orders above ₹500','banner3.jpg','/stack-change/spicemart/shop','Grab Deal','offer',1,1);

-- Reviews
INSERT INTO reviews (product_id,user_id,rating,comment) VALUES
(1, 2,5,'Best cardamom I have ever bought. The aroma fills the whole kitchen!'),
(4, 2,5,'Pure and vibrant colour. No adulteration. Will definitely buy again.'),
(7, 2,4,'Amazing garam masala. Tastes just like home-made. Highly recommended.'),
(14,2,5,'Kashmiri walnuts are incredible. So fresh and buttery — worth every rupee.');
