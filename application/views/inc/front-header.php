<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$page_title = isset($page_title) ? $page_title . ' | ' . APP_NAME : APP_NAME . ' – Pure Spices & Masala';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($page_title); ?></title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="<?php echo base_url('public/css/custom.css'); ?>" rel="stylesheet">
</head>
<body>

<!-- Top Strip -->
<div class="top-strip text-center py-1 d-none d-md-block">
  <small>🚚 Free shipping on orders above ₹499 &nbsp;|&nbsp; 100% Pure & Natural &nbsp;|&nbsp; Cash on Delivery available</small>
</div>

<!-- Main Navbar -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top shadow-sm" id="mainNav">
  <div class="container">

    <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo site_url('home'); ?>">
      <span class="brand-icon">🌶️</span>
      <div>
        <span class="brand-name">SpiceMart</span>
        <span class="brand-tagline d-block">Pure &amp; Natural</span>
      </div>
    </a>

    <!-- Mobile cart + toggler -->
    <div class="d-flex align-items-center gap-3 d-lg-none">
      <a href="<?php echo site_url('cart'); ?>" class="nav-icon-btn position-relative">
        <i class="bi bi-bag fs-5"></i>
        <?php if (isset($cart_count) && $cart_count > 0): ?>
          <span class="cart-badge"><?php echo $cart_count; ?></span>
        <?php endif; ?>
      </a>
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>

    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item"><a class="nav-link" href="<?php echo site_url('home'); ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo site_url('shop'); ?>">Shop</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Categories</a>
          <ul class="dropdown-menu shadow-sm border-0">
            <?php if (isset($all_categories)): foreach ($all_categories as $cat): ?>
              <li>
                <a class="dropdown-item" href="<?php echo site_url('shop'); ?>?category=<?php echo htmlspecialchars($cat['slug']); ?>">
                  <?php echo htmlspecialchars($cat['name']); ?>
                </a>
              </li>
            <?php endforeach; endif; ?>
          </ul>
        </li>
      </ul>

      <!-- Search -->
      <form class="d-flex search-form me-3" action="<?php echo site_url('shop'); ?>" method="get">
        <div class="input-group input-group-sm">
          <input class="form-control" type="search" name="q"
                 placeholder="Search spices…"
                 value="<?php echo htmlspecialchars($this->input->get('q') ?: ''); ?>">
          <button class="btn btn-saffron" type="submit"><i class="bi bi-search"></i></button>
        </div>
      </form>

      <!-- Right icons -->
      <div class="d-flex align-items-center gap-3">
        <a href="<?php echo site_url('cart'); ?>" class="nav-icon-btn position-relative d-none d-lg-flex">
          <i class="bi bi-bag fs-5"></i>
          <span class="cart-badge" id="cartBadge" <?php if (empty($cart_count)) echo 'style="display:none"'; ?>>
            <?php echo isset($cart_count) ? $cart_count : 0; ?>
          </span>
        </a>

        <?php if (!empty($is_logged_in)): ?>
        <a href="<?php echo site_url('wishlist'); ?>" class="nav-icon-btn position-relative d-none d-lg-flex">
          <i class="bi bi-heart fs-5"></i>
          <span class="cart-badge" id="wishlistBadge" <?php if (empty($wishlist_count)) echo 'style="display:none"'; ?>>
            <?php echo isset($wishlist_count) ? $wishlist_count : 0; ?>
          </span>
        </a>
        <?php endif; ?>

        <?php if (!empty($is_logged_in)): ?>
          <div class="dropdown">
            <a class="nav-icon-btn dropdown-toggle d-flex align-items-center gap-1" href="#" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle fs-5"></i>
              <span class="d-none d-xl-inline"><?php echo htmlspecialchars($user_name ?? ''); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
              <li><a class="dropdown-item" href="<?php echo site_url('account'); ?>"><i class="bi bi-person me-2"></i>My Account</a></li>
              <li><a class="dropdown-item" href="<?php echo site_url('account'); ?>?tab=orders"><i class="bi bi-box me-2"></i>My Orders</a></li>
              <?php if (isset($user_role) && $user_role === 'admin'): ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?php echo site_url('admin'); ?>"><i class="bi bi-speedometer2 me-2"></i>Admin Panel</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?php echo site_url('logout'); ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a href="<?php echo site_url('login'); ?>" class="btn btn-sm btn-outline-saffron">Login</a>
          <a href="<?php echo site_url('register'); ?>" class="btn btn-sm btn-saffron d-none d-xl-inline-flex">Register</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<!-- Flash Messages -->
<?php
$flash_success = $this->session->flashdata('success');
$flash_warning = $this->session->flashdata('warning');
$flash_danger  = $this->session->flashdata('danger');
if ($flash_success): ?>
  <div class="container mt-2">
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php echo htmlspecialchars($flash_success); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  </div>
<?php endif;
if ($flash_warning): ?>
  <div class="container mt-2">
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <?php echo htmlspecialchars($flash_warning); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  </div>
<?php endif;
if ($flash_danger): ?>
  <div class="container mt-2">
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php echo htmlspecialchars($flash_danger); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  </div>
<?php endif; ?>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer" style="z-index:1100;"></div>
