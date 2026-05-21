-- ============================================================
--  myeoncasuals – Full E-Commerce Database
--  No foreign key constraints
--  mysql -u root -p spicemart < database.sql
-- ============================================================

DROP DATABASE IF EXISTS spicemart;
CREATE DATABASE spicemart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE spicemart;

-- ── users ────────────────────────────────────────────────────
CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    phone         VARCHAR(20)  DEFAULT NULL,
    password      VARCHAR(255) NOT NULL,
    address       TEXT         DEFAULT NULL,
    role          ENUM('admin','staff','customer') NOT NULL DEFAULT 'customer',
    permissions   VARCHAR(500) DEFAULT NULL,
    is_blocked    TINYINT(1)   DEFAULT 0,
    reset_token   VARCHAR(64)  DEFAULT NULL,
    reset_expires DATETIME     DEFAULT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
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

-- ── categories ───────────────────────────────────────────────
CREATE TABLE categories (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id INT UNSIGNED DEFAULT NULL,
    name      VARCHAR(100) NOT NULL,
    slug      VARCHAR(120) NOT NULL UNIQUE,
    image     VARCHAR(255) DEFAULT NULL,
    status    TINYINT(1)   DEFAULT 1
) ENGINE=InnoDB;

-- ── products ─────────────────────────────────────────────────
CREATE TABLE products (
    id           INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    product_code VARCHAR(20)   DEFAULT NULL UNIQUE,
    category_id  INT UNSIGNED  NOT NULL,
    brand_id     INT UNSIGNED  DEFAULT NULL,
    name         VARCHAR(150)  NOT NULL,
    slug         VARCHAR(160)  NOT NULL UNIQUE,
    description TEXT          DEFAULT NULL,
    price       DECIMAL(10,2) NOT NULL,
    offer_price DECIMAL(10,2) DEFAULT NULL,
    gst         DECIMAL(5,2)  DEFAULT 0.00,
    stock_qty   INT           NOT NULL DEFAULT 0,
    weight      VARCHAR(50)   DEFAULT NULL,
    image       VARCHAR(255)  DEFAULT NULL,
    tags        VARCHAR(300)  DEFAULT NULL,
    meta_title  VARCHAR(200)  DEFAULT NULL,
    meta_desc   TEXT          DEFAULT NULL,
    is_featured TINYINT(1)    DEFAULT 0,
    status      TINYINT(1)    NOT NULL DEFAULT 1,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── product_images ───────────────────────────────────────────
CREATE TABLE product_images (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    image      VARCHAR(255) NOT NULL,
    is_primary TINYINT(1)   DEFAULT 0,
    sort_order INT          DEFAULT 0
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
    color_hex      VARCHAR(10)   DEFAULT NULL
) ENGINE=InnoDB;

-- ── orders ───────────────────────────────────────────────────
CREATE TABLE orders (
    id               INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    user_id          INT UNSIGNED  NOT NULL,
    total_amount     DECIMAL(10,2) NOT NULL,
    shipping_charge  DECIMAL(10,2) DEFAULT 0.00,
    coupon_code      VARCHAR(50)   DEFAULT NULL,
    coupon_discount  DECIMAL(10,2) DEFAULT 0.00,
    fazaa_program    VARCHAR(10)   DEFAULT NULL,
    fazaa_member_no  VARCHAR(50)   DEFAULT NULL,
    fazaa_discount   DECIMAL(10,2) DEFAULT 0.00,
    shipping_address TEXT          NOT NULL,
    payment_method   ENUM('cod','razorpay','payu','wallet') NOT NULL DEFAULT 'cod',
    payment_status   ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    transaction_id   VARCHAR(200)  DEFAULT NULL,
    status           ENUM('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    tracking_no      VARCHAR(100)  DEFAULT NULL,
    courier_name     VARCHAR(100)  DEFAULT NULL,
    notes            TEXT          DEFAULT NULL,
    created_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── order_items ──────────────────────────────────────────────
CREATE TABLE order_items (
    id            INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    order_id      INT UNSIGNED  NOT NULL,
    product_id    INT UNSIGNED  NOT NULL,
    product_name  VARCHAR(150)  NOT NULL,
    variant_label VARCHAR(200)  DEFAULT NULL,
    sku           VARCHAR(100)  DEFAULT NULL,
    quantity      INT UNSIGNED  NOT NULL DEFAULT 1,
    unit_price    DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB;

-- ── cart ─────────────────────────────────────────────────────
CREATE TABLE cart (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NOT NULL,
    product_id    INT UNSIGNED NOT NULL,
    variant_id    INT UNSIGNED DEFAULT NULL,
    variant_label VARCHAR(200) DEFAULT NULL,
    quantity      INT UNSIGNED NOT NULL DEFAULT 1,
    UNIQUE KEY uq_cart_item (user_id, product_id, variant_id)
) ENGINE=InnoDB;

-- ── wishlist ─────────────────────────────────────────────────
CREATE TABLE wishlist (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_wishlist (user_id, product_id)
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
    is_default   TINYINT(1)   DEFAULT 0
) ENGINE=InnoDB;

-- ── reviews ──────────────────────────────────────────────────
CREATE TABLE reviews (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    rating     TINYINT(1)   NOT NULL,
    comment    TEXT         DEFAULT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_review (product_id, user_id)
) ENGINE=InnoDB;

-- ── coupons ──────────────────────────────────────────────────
CREATE TABLE coupons (
    id            INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    code          VARCHAR(50)   NOT NULL UNIQUE,
    type          ENUM('percent','flat') NOT NULL DEFAULT 'percent',
    value         DECIMAL(10,2) NOT NULL,
    min_order     DECIMAL(10,2) DEFAULT 0.00,
    max_discount  DECIMAL(10,2) DEFAULT NULL,
    uses_limit    INT           DEFAULT NULL,
    uses_count    INT           DEFAULT 0,
    uses_per_user INT           DEFAULT NULL,
    restrict_to   ENUM('all','staff','specific') DEFAULT 'all',
    expires_at    DATE          DEFAULT NULL,
    status        TINYINT(1)    DEFAULT 1,
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── coupon_users (email allow-list for specific-user coupons) ──
CREATE TABLE coupon_users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coupon_id  INT UNSIGNED NOT NULL,
    user_email VARCHAR(150) NOT NULL,
    UNIQUE KEY uq_coupon_user (coupon_id, user_email)
) ENGINE=InnoDB;

-- ── coupon_usage (per-user usage tracking) ────────────────────
CREATE TABLE coupon_usage (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT UNSIGNED NOT NULL,
    user_id   INT UNSIGNED NOT NULL,
    order_id  INT UNSIGNED NOT NULL,
    used_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    KEY idx_coupon_user (coupon_id, user_id)
) ENGINE=InnoDB;

-- ── user_loyalty (points balance + tier per customer) ────────
CREATE TABLE user_loyalty (
    user_id         INT UNSIGNED NOT NULL PRIMARY KEY,
    points_balance  INT          NOT NULL DEFAULT 0,
    points_earned   INT          NOT NULL DEFAULT 0,
    points_redeemed INT          NOT NULL DEFAULT 0,
    tier            ENUM('bronze','silver','gold','platinum') DEFAULT 'bronze',
    birthday        DATE         DEFAULT NULL,
    updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── loyalty_ledger (every points transaction) ─────────────────
CREATE TABLE loyalty_ledger (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    points     INT          NOT NULL,
    type       ENUM('earned','redeemed','expired','bonus','adjusted') DEFAULT 'earned',
    ref_type   ENUM('order','redemption','admin','campaign','expiry') DEFAULT 'order',
    ref_id     INT UNSIGNED DEFAULT NULL,
    note       VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    KEY idx_ll_user (user_id)
) ENGINE=InnoDB;

-- ── campaigns (promotion campaigns) ──────────────────────────
CREATE TABLE campaigns (
    id            INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(150)  NOT NULL,
    type          ENUM('general','birthday','anniversary','festival') DEFAULT 'general',
    description   TEXT          DEFAULT NULL,
    offer_type    ENUM('points_bonus','percent_off','flat_off','free_shipping') DEFAULT 'points_bonus',
    offer_value   DECIMAL(10,2) DEFAULT 0,
    coupon_code   VARCHAR(50)   DEFAULT NULL,
    target        ENUM('all','new','frequent','highvalue','inactive') DEFAULT 'all',
    festival_date DATE          DEFAULT NULL,
    trigger_days  INT           DEFAULT 0,
    start_date    DATE          DEFAULT NULL,
    end_date      DATE          DEFAULT NULL,
    message       TEXT          DEFAULT NULL,
    status        ENUM('draft','active','paused','completed') DEFAULT 'draft',
    sent_count    INT           DEFAULT 0,
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── campaign_logs (tracks delivery per user) ──────────────────
CREATE TABLE campaign_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    status      ENUM('sent','converted') DEFAULT 'sent',
    sent_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    KEY idx_cl_campaign (campaign_id),
    KEY idx_cl_user (user_id)
) ENGINE=InnoDB;

-- ── returns ──────────────────────────────────────────────────
CREATE TABLE returns (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id   INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    type       ENUM('cancel','return') NOT NULL DEFAULT 'cancel',
    reason     TEXT         NOT NULL,
    status     ENUM('pending','approved','rejected','resolved') DEFAULT 'pending',
    admin_note TEXT         DEFAULT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── pos_api_keys ──────────────────────────────────────────────
CREATE TABLE pos_api_keys (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    label        VARCHAR(100) NOT NULL,
    api_key      VARCHAR(64)  NOT NULL UNIQUE,
    pos_system   VARCHAR(80)  DEFAULT NULL,
    sync_stock   TINYINT(1)   DEFAULT 1,
    sync_price   TINYINT(1)   DEFAULT 1,
    sync_coupon  TINYINT(1)   DEFAULT 1,
    sync_avail   TINYINT(1)   DEFAULT 1,
    last_sync_at TIMESTAMP    DEFAULT NULL,
    status       TINYINT(1)   DEFAULT 1,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── pos_sync_logs ─────────────────────────────────────────────
CREATE TABLE pos_sync_logs (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    api_key_id       INT UNSIGNED DEFAULT NULL,
    sync_type        ENUM('stock','price','coupon','availability','full') NOT NULL,
    source           ENUM('webhook','manual','scheduled') DEFAULT 'webhook',
    records_sent     INT UNSIGNED DEFAULT 0,
    records_updated  INT UNSIGNED DEFAULT 0,
    records_failed   INT UNSIGNED DEFAULT 0,
    status           ENUM('running','success','failed','partial') DEFAULT 'running',
    request_ip       VARCHAR(45)  DEFAULT NULL,
    payload_summary  TEXT         DEFAULT NULL,
    error_message    TEXT         DEFAULT NULL,
    started_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    completed_at     TIMESTAMP    DEFAULT NULL,
    KEY idx_psl_type   (sync_type),
    KEY idx_psl_status (status),
    KEY idx_psl_key    (api_key_id)
) ENGINE=InnoDB;

-- ── fazaa_settings ───────────────────────────────────────────
CREATE TABLE fazaa_settings (
    id           INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    program      VARCHAR(10)   NOT NULL,
    label        VARCHAR(60)   NOT NULL,
    enabled      TINYINT(1)    DEFAULT 1,
    discount_pct DECIMAL(5,2)  DEFAULT 10.00,
    max_discount DECIMAL(10,2) DEFAULT 100.00,
    min_order    DECIMAL(10,2) DEFAULT 0.00,
    api_url      VARCHAR(255)  DEFAULT NULL,
    api_key      VARCHAR(255)  DEFAULT NULL,
    otp_enabled  TINYINT(1)    DEFAULT 0,
    logo_url     VARCHAR(255)  DEFAULT NULL,
    created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_program (program)
) ENGINE=InnoDB;

-- ── fazaa_usages ──────────────────────────────────────────────
CREATE TABLE fazaa_usages (
    id           INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    program      VARCHAR(10)   NOT NULL,
    member_no    VARCHAR(50)   NOT NULL,
    user_id      INT UNSIGNED  DEFAULT NULL,
    order_id     INT UNSIGNED  DEFAULT NULL,
    discount_pct DECIMAL(5,2)  NOT NULL,
    discount_amt DECIMAL(10,2) NOT NULL,
    order_total  DECIMAL(10,2) NOT NULL,
    verified_at  DATETIME      DEFAULT NULL,
    created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    KEY idx_fu_program  (program),
    KEY idx_fu_user     (user_id),
    KEY idx_fu_order    (order_id)
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

-- ── shipping_settings ────────────────────────────────────────
CREATE TABLE shipping_settings (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key_name  VARCHAR(100) NOT NULL UNIQUE,
    key_value TEXT         NOT NULL
) ENGINE=InnoDB;

-- ── site_settings ────────────────────────────────────────────
CREATE TABLE site_settings (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key_name  VARCHAR(100) NOT NULL UNIQUE,
    key_value TEXT         DEFAULT NULL,
    key_group VARCHAR(50)  DEFAULT 'general'
) ENGINE=InnoDB;

-- ── why_choose_us ────────────────────────────────────────────
CREATE TABLE why_choose_us (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    icon        VARCHAR(10)  DEFAULT '🌿',
    title       VARCHAR(150) NOT NULL,
    description TEXT         NOT NULL,
    sort_order  INT          DEFAULT 0,
    status      TINYINT(1)   DEFAULT 1
) ENGINE=InnoDB;

-- ── testimonials ─────────────────────────────────────────────
CREATE TABLE testimonials (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    rating        TINYINT(1)   NOT NULL DEFAULT 5,
    quote         TEXT         NOT NULL,
    sort_order    INT          DEFAULT 0,
    status        TINYINT(1)   DEFAULT 1
) ENGINE=InnoDB;

-- ============================================================
--  SEED DATA
-- ============================================================

-- Users
INSERT INTO users (name, email, phone, password, role) VALUES
('Admin User',   'admin@myeoncasuals.com', '9876543210', 'Admin@123', 'admin'),
('Priya Sharma', 'priya@example.com',      '9123456780', 'Test@123',  'customer');
UPDATE users SET address = '12, Gandhi Nagar, Chennai, Tamil Nadu - 600001' WHERE id = 2;

-- Brands
INSERT INTO brands (name, slug, status) VALUES
('myeoncasuals Original', 'myeoncasuals-original', 1),
('FarmFresh',             'farmfresh',              1),
('Pure Harvest',          'pure-harvest',           1);

-- Categories (main)
INSERT INTO categories (parent_id, name, slug, status) VALUES
(NULL, 'Men',        'men',        1),
(NULL, 'Women',      'women',      1),
(NULL, 'Kids',       'kids',       1),
(NULL, 'Accessories','accessories',1),
(NULL, 'Footwear',   'footwear',   1);

-- Sub-categories
INSERT INTO categories (parent_id, name, slug, status) VALUES
(1, 'T-Shirts',   't-shirts',   1),
(1, 'Shirts',     'shirts',     1),
(2, 'Kurtas',     'kurtas',     1),
(2, 'Dresses',    'dresses',    1),
(4, 'Bags',       'bags',       1);

-- Products
INSERT INTO products (category_id, brand_id, name, slug, description, price, offer_price, gst, stock_qty, weight, image, is_featured, status) VALUES
(6, 1, 'Classic Casual Tee',        'classic-casual-tee',        'Comfortable 100% cotton casual t-shirt for everyday wear.',        599.00,  499.00,  5.00, 150, NULL, NULL, 1, 1),
(6, 2, 'Premium Polo Tee',          'premium-polo-tee',          'Stylish polo t-shirt with ribbed collar and cuffs.',               799.00,  NULL,    5.00, 120, NULL, NULL, 0, 1),
(7, 1, 'Oxford Button-Down Shirt',  'oxford-button-down-shirt',  'Classic Oxford weave shirt for smart casual occasions.',          1299.00, 1099.00, 5.00, 80,  NULL, NULL, 1, 1),
(7, 3, 'Linen Summer Shirt',        'linen-summer-shirt',        'Breathable linen shirt perfect for warm weather.',                 999.00,  NULL,    5.00, 100, NULL, NULL, 0, 1),
(8, 1, 'Straight Fit Kurta',        'straight-fit-kurta',        'Elegant cotton kurta for festive and daily wear.',                 849.00,  749.00,  5.00, 90,  NULL, NULL, 1, 1),
(8, 2, 'Anarkali Kurta',            'anarkali-kurta',            'Beautiful anarkali style kurta with floral embroidery.',          1199.00, NULL,    5.00, 60,  NULL, NULL, 0, 1),
(9, 1, 'Floral Midi Dress',         'floral-midi-dress',         'Flowy midi dress with vibrant floral print.',                     1099.00, 949.00,  5.00, 70,  NULL, NULL, 1, 1),
(9, 3, 'Wrap Maxi Dress',           'wrap-maxi-dress',           'Elegant wrap maxi dress for evening occasions.',                  1499.00, NULL,    5.00, 45,  NULL, NULL, 0, 1),
(10, 1,'Leather Tote Bag',          'leather-tote-bag',          'Premium faux leather tote bag with multiple compartments.',       1899.00, 1699.00, 18.00,50,  NULL, NULL, 1, 1),
(10, 2,'Canvas Crossbody Bag',      'canvas-crossbody-bag',      'Lightweight canvas crossbody bag for everyday use.',               699.00,  NULL,    18.00,80,  NULL, NULL, 0, 1);

-- Product Variants (size, color, weight for all 10 products)
INSERT INTO product_variants (product_id, variant_type, variant_value, price_modifier, stock_qty, color_hex) VALUES

-- ── Product 1: Classic Casual Tee ────────────────────────────
-- size
(1, 'size',   'S',                0.00,  35, NULL),
(1, 'size',   'M',                0.00,  50, NULL),
(1, 'size',   'L',                0.00,  45, NULL),
(1, 'size',   'XL',               0.00,  25, NULL),
(1, 'size',   'XXL',             50.00,  15, NULL),
-- color
(1, 'color',  'White',            0.00,  40, '#FFFFFF'),
(1, 'color',  'Black',            0.00,  40, '#1A1A1A'),
(1, 'color',  'Navy Blue',        0.00,  35, '#1F3A6E'),
(1, 'color',  'Charcoal Grey',    0.00,  30, '#4A4A4A'),
(1, 'color',  'Olive Green',      0.00,  20, '#6B7645'),
-- weight (fabric GSM)
(1, 'weight', '180 GSM (Light)', -30.00, 45, NULL),
(1, 'weight', '220 GSM (Regular)',0.00,  80, NULL),
(1, 'weight', '280 GSM (Heavy)', 40.00,  25, NULL),

-- ── Product 2: Premium Polo Tee ──────────────────────────────
-- size
(2, 'size',   'S',                0.00,  25, NULL),
(2, 'size',   'M',                0.00,  40, NULL),
(2, 'size',   'L',                0.00,  35, NULL),
(2, 'size',   'XL',               0.00,  20, NULL),
(2, 'size',   'XXL',             50.00,  10, NULL),
-- color
(2, 'color',  'White',            0.00,  30, '#FFFFFF'),
(2, 'color',  'Black',            0.00,  30, '#1A1A1A'),
(2, 'color',  'Navy Blue',        0.00,  25, '#1F3A6E'),
(2, 'color',  'Burgundy',         0.00,  20, '#7B1D1D'),
(2, 'color',  'Sky Blue',         0.00,  15, '#87CEEB'),
-- weight (fabric GSM)
(2, 'weight', '200 GSM (Light)', -20.00, 40, NULL),
(2, 'weight', '240 GSM (Regular)',0.00,  60, NULL),
(2, 'weight', '300 GSM (Heavy)', 50.00,  20, NULL),

-- ── Product 3: Oxford Button-Down Shirt ──────────────────────
-- size
(3, 'size',   'S',                0.00,  20, NULL),
(3, 'size',   'M',                0.00,  30, NULL),
(3, 'size',   'L',                0.00,  20, NULL),
(3, 'size',   'XL',               0.00,  10, NULL),
(3, 'size',   'XXL',             80.00,   5, NULL),
-- color
(3, 'color',  'White',            0.00,  25, '#FFFFFF'),
(3, 'color',  'Light Blue',       0.00,  20, '#AEC6CF'),
(3, 'color',  'Pale Pink',        0.00,  15, '#F4C2C2'),
(3, 'color',  'Mint Green',       0.00,  10, '#98D8C8'),
-- weight (fabric GSM)
(3, 'weight', '120 GSM (Light)', -50.00, 30, NULL),
(3, 'weight', '160 GSM (Regular)',0.00,  50, NULL),

-- ── Product 4: Linen Summer Shirt ────────────────────────────
-- size
(4, 'size',   'S',                0.00,  25, NULL),
(4, 'size',   'M',                0.00,  35, NULL),
(4, 'size',   'L',                0.00,  25, NULL),
(4, 'size',   'XL',               0.00,  15, NULL),
-- color
(4, 'color',  'White',            0.00,  30, '#FFFFFF'),
(4, 'color',  'Beige',            0.00,  25, '#F5F5DC'),
(4, 'color',  'Sage Green',       0.00,  20, '#B2AC88'),
(4, 'color',  'Sky Blue',         0.00,  25, '#87CEEB'),
-- weight (fabric GSM)
(4, 'weight', '100 GSM (Sheer)', -40.00, 30, NULL),
(4, 'weight', '140 GSM (Regular)',0.00,  60, NULL),
(4, 'weight', '180 GSM (Heavy)', 30.00,  10, NULL),

-- ── Product 5: Straight Fit Kurta ────────────────────────────
-- size
(5, 'size',   'XS',               0.00,  15, NULL),
(5, 'size',   'S',                0.00,  25, NULL),
(5, 'size',   'M',                0.00,  30, NULL),
(5, 'size',   'L',                0.00,  20, NULL),
(5, 'size',   'XL',               0.00,  10, NULL),
(5, 'size',   'XXL',             50.00,   5, NULL),
-- color
(5, 'color',  'Ivory',            0.00,  25, '#FFFFF0'),
(5, 'color',  'Mint Green',       0.00,  20, '#98D8C8'),
(5, 'color',  'Dusty Rose',       0.00,  20, '#DCAE96'),
(5, 'color',  'Navy Blue',        0.00,  15, '#1F3A6E'),
(5, 'color',  'Marigold Yellow',  0.00,  10, '#FBCE2C'),
-- weight (fabric GSM)
(5, 'weight', '150 GSM (Light)', -30.00, 35, NULL),
(5, 'weight', '200 GSM (Regular)',0.00,  55, NULL),

-- ── Product 6: Anarkali Kurta ────────────────────────────────
-- size
(6, 'size',   'XS',               0.00,  10, NULL),
(6, 'size',   'S',                0.00,  15, NULL),
(6, 'size',   'M',                0.00,  20, NULL),
(6, 'size',   'L',                0.00,  10, NULL),
(6, 'size',   'XL',               0.00,   5, NULL),
-- color
(6, 'color',  'Royal Blue',       0.00,  15, '#4169E1'),
(6, 'color',  'Emerald Green',    0.00,  12, '#2E8B57'),
(6, 'color',  'Maroon',           0.00,  12, '#800000'),
(6, 'color',  'Peach',            0.00,  10, '#FFCBA4'),
(6, 'color',  'Purple',           0.00,   8, '#7B2D8B'),
-- weight (fabric GSM)
(6, 'weight', '180 GSM (Light)', -50.00, 20, NULL),
(6, 'weight', '240 GSM (Regular)',0.00,  40, NULL),

-- ── Product 7: Floral Midi Dress ─────────────────────────────
-- size
(7, 'size',   'XS',               0.00,  12, NULL),
(7, 'size',   'S',                0.00,  20, NULL),
(7, 'size',   'M',                0.00,  22, NULL),
(7, 'size',   'L',                0.00,  10, NULL),
(7, 'size',   'XL',               0.00,   6, NULL),
-- color
(7, 'color',  'Pink Floral',      0.00,  20, '#F4A7B9'),
(7, 'color',  'Yellow Floral',    0.00,  18, '#F5DEB3'),
(7, 'color',  'Blue Floral',      0.00,  15, '#B0C4DE'),
(7, 'color',  'White Floral',     0.00,  17, '#FAFAFA'),
-- weight (fabric GSM)
(7, 'weight', '120 GSM (Flowy)', -30.00, 35, NULL),
(7, 'weight', '160 GSM (Regular)',0.00,  35, NULL),

-- ── Product 8: Wrap Maxi Dress ───────────────────────────────
-- size
(8, 'size',   'XS',               0.00,   8, NULL),
(8, 'size',   'S',                0.00,  12, NULL),
(8, 'size',   'M',                0.00,  15, NULL),
(8, 'size',   'L',                0.00,   8, NULL),
(8, 'size',   'XL',               0.00,   5, NULL),
-- color
(8, 'color',  'Burgundy',         0.00,  12, '#800020'),
(8, 'color',  'Forest Green',     0.00,  10, '#228B22'),
(8, 'color',  'Midnight Blue',    0.00,  10, '#191970'),
(8, 'color',  'Terracotta',       0.00,   8, '#C0673A'),
(8, 'color',  'Blush Pink',       0.00,   5, '#FFB6C1'),
-- weight (fabric GSM)
(8, 'weight', '140 GSM (Flowy)', -50.00, 20, NULL),
(8, 'weight', '190 GSM (Regular)',0.00,  25, NULL),

-- ── Product 9: Leather Tote Bag ──────────────────────────────
-- color
(9, 'color',  'Black',            0.00,  15, '#1A1A1A'),
(9, 'color',  'Tan Brown',        0.00,  12, '#C4874A'),
(9, 'color',  'Nude Beige',       0.00,  10, '#E8C4A0'),
(9, 'color',  'Burgundy',         0.00,   8, '#800020'),
-- size (bag capacity)
(9, 'size',   'Small (10L)',    -200.00, 15, NULL),
(9, 'size',   'Medium (15L)',     0.00,  20, NULL),
(9, 'size',   'Large (20L)',    200.00,  15, NULL),
-- weight (bag weight)
(9, 'weight', '400g (Light)',   -100.00, 15, NULL),
(9, 'weight', '600g (Standard)',  0.00,  25, NULL),
(9, 'weight', '800g (Heavy)',   100.00,  10, NULL),

-- ── Product 10: Canvas Crossbody Bag ─────────────────────────
-- color
(10,'color',  'Black',            0.00,  20, '#1A1A1A'),
(10,'color',  'Navy Blue',        0.00,  18, '#1F3A6E'),
(10,'color',  'Olive Green',      0.00,  15, '#6B7645'),
(10,'color',  'Cream',            0.00,  12, '#FAF0E8'),
(10,'color',  'Rust Orange',      0.00,  10, '#B85C38'),
-- size (bag capacity)
(10,'size',   'Small (6L)',      -100.00,20, NULL),
(10,'size',   'Medium (10L)',      0.00, 35, NULL),
-- weight (bag weight)
(10,'weight', '250g (Light)',    -50.00, 30, NULL),
(10,'weight', '350g (Standard)',   0.00, 40, NULL),
(10,'weight', '450g (Padded)',   50.00,  10, NULL);

-- Coupons
INSERT INTO coupons (code, type, value, min_order, max_discount, uses_limit, expires_at, status) VALUES
('WELCOME10', 'percent', 10.00,  500.00,  100.00, 100, '2027-12-31', 1),
('FLAT50',    'flat',    50.00,  999.00,   50.00,  50, '2027-12-31', 1),
('STYLE20',   'percent', 20.00, 1500.00,  300.00,  25, '2027-06-30', 1);

-- CMS Pages
INSERT INTO cms_pages (slug, title, content, status) VALUES
('about',         'About Us',           '<h4>About myeoncasuals</h4><p>myeoncasuals was founded with a passion for bringing contemporary fashion directly to you. We source the finest fabrics and designs, ensuring quality with every stitch. Our collection blends comfort with style for everyday wear.</p>', 1),
('terms',         'Terms & Conditions', '<h4>Terms &amp; Conditions</h4><p>By using myeoncasuals you agree to these terms. Products are sold for personal use only. We reserve the right to modify prices and availability at any time. All orders are subject to confirmation.</p>',             1),
('privacy',       'Privacy Policy',     '<h4>Privacy Policy</h4><p>myeoncasuals collects personal information such as name, email, and address for order processing only. We do not share your data with third parties. Payment details are processed securely.</p>',                            1),
('return-policy', 'Return Policy',      '<h4>Return Policy</h4><p>We accept returns within 7 days of delivery for damaged or incorrect products. Items must be unworn and in original packaging. Refunds are processed within 5-7 business days.</p>',                                         1);

-- Site Settings
INSERT INTO site_settings (key_name, key_value, key_group) VALUES
('site_name',        'myeoncasuals',                                                                              'general'),
('site_tagline',     'Style That Speaks',                                                                         'general'),
('site_logo',        NULL,                                                                                        'general'),
('top_strip_text',   '🚚 Free shipping on orders above ₹499 | Easy Returns | Cash on Delivery available',        'general'),
('contact_phone',    '+91 98765 43210',                                                                           'contact'),
('contact_email',    'hello@myeoncasuals.com',                                                                    'contact'),
('contact_address',  'T. Nagar, Chennai – 600017',                                                               'contact'),
('footer_about',     'Bringing contemporary fashion to your doorstep. Comfort meets style in every piece we craft.', 'footer'),
('footer_copyright', 'myeoncasuals. All rights reserved.',                                                        'footer'),
('social_facebook',  '#',                                                                                         'social'),
('social_instagram', '#',                                                                                         'social'),
('social_youtube',   '#',                                                                                         'social'),
('social_whatsapp',  '#',                                                                                         'social'),
('social_twitter',   '#',                                                                                         'social'),
('meta_title',       'myeoncasuals – Contemporary Fashion',                                                       'seo'),
('meta_desc',        'Shop the latest fashion collection at myeoncasuals. Quality clothing for men, women and kids.', 'seo'),
('google_analytics', '',                                                                                          'seo');

-- Shipping Settings
INSERT INTO shipping_settings (key_name, key_value) VALUES
('free_shipping_above', '499'),
('standard_charge',     '60'),
('express_charge',      '120'),
('razorpay_key_id',     'rzp_test_placeholder'),
('razorpay_key_secret', 'placeholder_secret'),
('estimated_days',      '3-5');

-- Loyalty Settings (1 point per ₹10 spent; 100 points = ₹10 discount; min 100 to redeem; expire in 365 days)
INSERT INTO shipping_settings (key_name, key_value) VALUES
('loyalty_earn_rate',   '1'),
('loyalty_earn_per',    '10'),
('loyalty_redeem_rate', '100'),
('loyalty_redeem_value','10'),
('loyalty_min_redeem',  '100'),
('loyalty_expiry_days', '365')
ON DUPLICATE KEY UPDATE key_value = VALUES(key_value);

-- Banners
INSERT INTO banners (title, subtitle, image, link_url, btn_text, type, sort_order, status) VALUES
('New Arrivals This Season', 'Fresh styles for men and women — crafted for comfort and class', 'banner1.svg', 'shop',               'Shop Now',  'slider', 1, 1),
('Women\'s Collection',      'Elegant kurtas, dresses and more — discover your style',         'banner2.svg', 'shop?category=women','Explore',   'slider', 2, 1),
('Flat ₹50 Off',             'Use code FLAT50 on orders above ₹999',                          'banner3.svg', 'shop',               'Grab Deal', 'offer',  1, 1);

-- Reviews
INSERT INTO reviews (product_id, user_id, rating, comment) VALUES
(1, 2, 5, 'Great quality tee! The fabric is super soft and the fit is perfect.'),
(3, 2, 5, 'Excellent Oxford shirt. Very professional look. Totally worth the price.'),
(5, 2, 4, 'Beautiful kurta, great stitching. Ordered M and it fits perfectly.'),
(9, 2, 5, 'The leather tote bag is stunning. Very spacious and sturdy. Highly recommend!');

-- Why Choose Us
INSERT INTO why_choose_us (icon, title, description, sort_order, status) VALUES
('✨', 'Premium Quality',   'Every piece is crafted with the finest fabrics, ensuring lasting comfort and style you can feel.', 1, 1),
('🚚', 'Fast Delivery',     'Same-day dispatch on most orders. Free shipping above ₹499 — straight to your doorstep.',         2, 1),
('🔄', 'Easy Returns',      'Not satisfied? Return within 7 days, no questions asked. We make shopping risk-free.',            3, 1),
('💳', 'Secure Payments',   'Pay with COD, Razorpay, or UPI. Your payment details are always encrypted and safe.',             4, 1);

-- Testimonials
INSERT INTO testimonials (customer_name, rating, quote, sort_order, status) VALUES
('Priya Sharma',  5, 'Absolutely love the quality! The kurta I ordered fits perfectly and the fabric is so comfortable. Will definitely order more.', 1, 1),
('Rahul Mehta',   5, 'Best online fashion store I have tried. The Oxford shirt exceeded my expectations — great stitching and premium feel.',          2, 1),
('Ananya Singh',  4, 'Beautiful collection and quick delivery. The floral dress was exactly as shown. Packaging was also very neat and clean.',         3, 1),
('Vikram Reddy',  5, 'myeoncasuals never disappoints. The tote bag I ordered is stunning — so spacious and looks very premium. Highly recommended!',   4, 1);

-- Fazaa / Isaad Settings
INSERT INTO fazaa_settings (program, label, enabled, discount_pct, max_discount, min_order, otp_enabled) VALUES
('fazaa', 'Fazaa',  1, 10.00, 100.00, 0.00, 0),
('isaad', 'Isaad',  1, 10.00, 100.00, 0.00, 0)
ON DUPLICATE KEY UPDATE label = VALUES(label);
