<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'home';
$route['404_override']       = '';
$route['translate_uri_dashes'] = FALSE;

// ── Auth ──────────────────────────────────────────────────────
$route['logout']           = 'login/logout';
$route['register']         = 'login/register';
$route['forgot-password']  = 'login/forgot_password';

// ── Frontend ──────────────────────────────────────────────────
$route['home']    = 'home/index';
$route['account'] = 'home/account';

// ── Wishlist ──────────────────────────────────────────────────
$route['wishlist']                 = 'home/wishlist';
$route['wishlist/toggle/(:num)']   = 'home/wishlist_toggle/$1';

// ── Addresses ─────────────────────────────────────────────────
$route['my-addresses']             = 'home/my_addresses';
$route['address/delete/(:num)']    = 'home/delete_address/$1';

// ── Orders ────────────────────────────────────────────────────
$route['cancel-order/(:num)']  = 'home/cancel_order/$1';
$route['return-order/(:num)']  = 'home/return_order/$1';
$route['track-order/(:num)']   = 'home/track_order/$1';
$route['invoice/(:num)']       = 'home/invoice/$1';

// ── Contact & CMS ─────────────────────────────────────────────
$route['contact']              = 'home/contact';
$route['page/(:any)']          = 'home/cms_page/$1';

// ── Shop ──────────────────────────────────────────────────────
$route['shop']             = 'shop/index';
$route['shop/(:num)']      = 'shop/index/$1';
$route['shop-ajax']        = 'shop/ajax_products';
$route['product/(:num)']   = 'shop/product/$1';

// ── Cart & Checkout ───────────────────────────────────────────
$route['cart']                     = 'cart/index';
$route['cart-ajax']                = 'cart/ajax_cart';
$route['cart/apply-coupon']        = 'cart/apply_coupon';
$route['cart/remove-coupon']       = 'cart/remove_coupon';
$route['checkout']                 = 'cart/checkout';
$route['order-success/(:num)']     = 'cart/order_success/$1';
$route['razorpay-callback']        = 'cart/razorpay_callback';

// ── Admin ─────────────────────────────────────────────────────
$route['admin']              = 'admin/index';
$route['admin-product/(:num)']        = 'admin/product_edit/$1';
$route['admin-get-variants/(:num)']   = 'admin/get_product_variants/$1';
$route['admin-get-gallery/(:num)']    = 'admin/get_product_gallery/$1';
$route['admin-variant-save']          = 'admin/variant_save';
$route['admin-variant-delete/(:num)'] = 'admin/variant_delete/$1';
$route['admin-image-upload']          = 'admin/image_upload';
$route['admin-image-delete/(:num)']   = 'admin/image_delete/$1';
$route['admin-image-primary/(:num)']  = 'admin/image_set_primary/$1';
$route['admin-products']     = 'admin/products';
$route['admin-orders']       = 'admin/orders';
$route['admin-categories']   = 'admin/categories';
$route['admin-brands']       = 'admin/brands';
$route['admin-customers']    = 'admin/customers';
$route['admin-reports']      = 'admin/reports';
$route['admin-coupons']      = 'admin/coupons';
$route['admin-banners']      = 'admin/banners';
$route['admin-cms']          = 'admin/cms_pages';
$route['admin-why-choose-us']= 'admin/why_choose_us';
$route['admin-testimonials'] = 'admin/testimonials';
$route['admin-shipping']     = 'admin/shipping';
$route['admin-payments']     = 'admin/payment_settings';
$route['admin-loyalty']      = 'admin/loyalty';
$route['admin-returns']      = 'admin/returns';
$route['admin-contacts']     = 'admin/contacts';
$route['admin-roles']        = 'admin/admin_roles';
$route['admin-settings']     = 'admin/site_settings';

// ── POS Integration ───────────────────────────────────────────
$route['admin-pos']             = 'admin/pos';
$route['pos-sync']              = 'api/pos_sync';
$route['pos-sync/(:any)']       = 'api/pos_sync/$1';

// ── Fazaa / Isaad ─────────────────────────────────────────────
$route['admin-fazaa']           = 'admin/fazaa_settings';
$route['admin-fazaa-report']    = 'admin/fazaa_report';

// ── AJAX endpoints (zero-refresh) ────────────────────────────
$route['ajax/pos-manual-sync']  = 'admin/ajax_pos_sync';
$route['ajax/returns-update']   = 'admin/ajax_returns_update';
$route['ajax/orders-update']    = 'admin/ajax_orders_update';
$route['admin-reviews']         = 'admin/reviews';
$route['ajax/review-action']    = 'admin/ajax_review_action';
$route['ajax/fazaa-verify']     = 'api/fazaa_verify';
$route['ajax/fazaa-otp-confirm']= 'api/fazaa_otp_confirm';
$route['ajax/fazaa-remove']     = 'api/fazaa_remove';
