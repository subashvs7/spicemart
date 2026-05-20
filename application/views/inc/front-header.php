<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$cfg       = isset($app_settings) ? $app_settings : array();
$site_name = !empty($cfg['site_name'])    ? $cfg['site_name']    : APP_NAME;
$site_tag  = !empty($cfg['site_tagline']) ? $cfg['site_tagline'] : 'Pure & Natural';
$top_strip = isset($cfg['top_strip_text']) ? $cfg['top_strip_text']
             : '🚚 Free shipping on orders above ₹499 | Trendy Styles | Cash on Delivery available';
$logo_file = !empty($cfg['site_logo']) && file_exists(FCPATH.'uploads/logo/'.$cfg['site_logo'])
             ? base_url('uploads/logo/'.$cfg['site_logo']) : '';
$meta_title_default = !empty($cfg['meta_title']) ? $cfg['meta_title'] : $site_name . ' – Trendy Casual Wear';
$meta_desc_default  = !empty($cfg['meta_desc'])  ? $cfg['meta_desc']  : '';
$ga_id              = !empty($cfg['google_analytics']) ? $cfg['google_analytics'] : '';

$page_title = isset($page_title) ? $page_title . ' | ' . $site_name : $meta_title_default;

/* Build parent → children map for the nav */
$nav_parents  = array();
$nav_children = array();
if (!empty($all_categories)) {
    foreach ($all_categories as $cat) {
        if (empty($cat['parent_id'])) {
            $nav_parents[] = $cat;
        } else {
            $nav_children[(int)$cat['parent_id']][] = $cat;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($page_title); ?></title>
  <?php if ($meta_desc_default): ?>
  <meta name="description" content="<?php echo htmlspecialchars($meta_desc_default); ?>">
  <?php endif; ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="<?php echo base_url('public/css/custom.css'); ?>" rel="stylesheet">
  <?php if ($ga_id): ?>
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo htmlspecialchars($ga_id); ?>"></script>
  <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?php echo htmlspecialchars($ga_id); ?>');</script>
  <?php endif; ?>
</head>
<body>

<!-- Top Strip -->
<?php if (!empty($top_strip)): ?>
<div class="top-strip text-center py-1 d-none d-md-block">
  <small><?php echo htmlspecialchars($top_strip); ?></small>
</div>
<?php endif; ?>

<!-- Main Navbar -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top shadow-sm" id="mainNav">
  <div class="container">

    <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo site_url('home'); ?>">
      <?php if ($logo_file): ?>
        <img src="<?php echo $logo_file; ?>"
             alt="<?php echo htmlspecialchars($site_name); ?>"
             style="height:42px;object-fit:contain;max-width:160px">
      <?php else: ?>
        <span class="brand-icon">🛍️</span>
        <div>
          <span class="brand-name"><?php echo htmlspecialchars($site_name); ?></span>
          <span class="brand-tagline d-block"><?php echo htmlspecialchars($site_tag); ?></span>
        </div>
      <?php endif; ?>
    </a>

    <!-- Mobile cart + toggler -->
    <div class="d-flex align-items-center gap-3 d-lg-none">
      <a href="<?php echo site_url('cart'); ?>" class="nav-icon-btn position-relative">
        <i class="bi bi-bag fs-5"></i>
        <?php if (!empty($cart_count)): ?>
          <span class="cart-badge"><?php echo $cart_count; ?></span>
        <?php endif; ?>
      </a>
      <button class="navbar-toggler border-0" type="button"
              data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>

    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav mx-auto gap-1">

        <li class="nav-item">
          <a class="nav-link" href="<?php echo site_url('home'); ?>">Home</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="<?php echo site_url('shop'); ?>">Shop</a>
        </li>

        <!-- Dynamic hierarchical Categories dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="<?php echo site_url('shop'); ?>"
             data-bs-toggle="dropdown" data-bs-auto-close="outside">
            Categories
          </a>
          <ul class="dropdown-menu shadow border-0" style="min-width:220px">

            <li>
              <a class="dropdown-item fw-500" href="<?php echo site_url('shop'); ?>">
                <i class="bi bi-grid-fill me-2 text-saffron"></i>All Products
              </a>
            </li>
            <li><hr class="dropdown-divider my-1"></li>

            <?php foreach ($nav_parents as $parent):
              $children = isset($nav_children[$parent['id']]) ? $nav_children[$parent['id']] : array();
            ?>

              <?php if (!empty($children)): ?>
                <!-- Parent with sub-menu -->
                <li class="dropend">
                  <a class="dropdown-item d-flex justify-content-between align-items-center"
                     href="<?php echo site_url('shop'); ?>?category=<?php echo htmlspecialchars($parent['slug']); ?>">
                    <?php echo htmlspecialchars($parent['name']); ?>
                    <i class="bi bi-chevron-right ms-2" style="font-size:.7rem;opacity:.5"></i>
                  </a>
                  <ul class="dropdown-menu shadow border-0" style="min-width:190px">
                    <li>
                      <a class="dropdown-item text-saffron fw-500"
                         href="<?php echo site_url('shop'); ?>?category=<?php echo htmlspecialchars($parent['slug']); ?>">
                        <i class="bi bi-collection me-1"></i> All <?php echo htmlspecialchars($parent['name']); ?>
                      </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <?php foreach ($children as $child): ?>
                      <li>
                        <a class="dropdown-item"
                           href="<?php echo site_url('shop'); ?>?category=<?php echo htmlspecialchars($child['slug']); ?>">
                          <?php echo htmlspecialchars($child['name']); ?>
                        </a>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </li>

              <?php else: ?>
                <!-- Parent with no children — direct link -->
                <li>
                  <a class="dropdown-item"
                     href="<?php echo site_url('shop'); ?>?category=<?php echo htmlspecialchars($parent['slug']); ?>">
                    <?php echo htmlspecialchars($parent['name']); ?>
                  </a>
                </li>
              <?php endif; ?>

            <?php endforeach; ?>

          </ul>
        </li>

        <!-- CMS pages in nav -->
        <li class="nav-item">
          <a class="nav-link" href="<?php echo site_url('contact'); ?>">Contact</a>
        </li>

      </ul>

      <!-- Search bar -->
      <form class="d-flex search-form me-3" action="<?php echo site_url('shop'); ?>" method="get">
        <div class="input-group input-group-sm">
          <input class="form-control" type="search" name="q"
                 placeholder="Search products…"
                 value="<?php echo htmlspecialchars($this->input->get('q') ?: ''); ?>">
          <button class="btn btn-saffron" type="submit"><i class="bi bi-search"></i></button>
        </div>
      </form>

      <!-- Right icons -->
      <div class="d-flex align-items-center gap-3">
        <a href="<?php echo site_url('cart'); ?>"
           class="nav-icon-btn position-relative d-none d-lg-flex">
          <i class="bi bi-bag fs-5"></i>
          <span class="cart-badge" id="cartBadge"
                <?php if (empty($cart_count)) echo 'style="display:none"'; ?>>
            <?php echo isset($cart_count) ? $cart_count : 0; ?>
          </span>
        </a>

        <?php if (!empty($is_logged_in)): ?>
        <a href="<?php echo site_url('wishlist'); ?>"
           class="nav-icon-btn position-relative d-none d-lg-flex">
          <i class="bi bi-heart fs-5"></i>
          <span class="cart-badge" id="wishlistBadge"
                <?php if (empty($wishlist_count)) echo 'style="display:none"'; ?>>
            <?php echo isset($wishlist_count) ? $wishlist_count : 0; ?>
          </span>
        </a>
        <?php endif; ?>

        <?php if (!empty($is_logged_in)): ?>
          <div class="dropdown">
            <a class="nav-icon-btn dropdown-toggle d-flex align-items-center gap-1"
               href="#" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle fs-5"></i>
              <span class="d-none d-xl-inline"><?php echo htmlspecialchars($user_name ?? ''); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
              <li><a class="dropdown-item" href="<?php echo site_url('account'); ?>">
                <i class="bi bi-person me-2"></i>My Account</a></li>
              <li><a class="dropdown-item" href="<?php echo site_url('account'); ?>?tab=orders">
                <i class="bi bi-box me-2"></i>My Orders</a></li>
              <li><a class="dropdown-item" href="<?php echo site_url('wishlist'); ?>">
                <i class="bi bi-heart me-2"></i>Wishlist</a></li>
              <?php if (isset($user_role) && in_array($user_role, array('admin','staff'))): ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?php echo site_url('admin'); ?>">
                  <i class="bi bi-speedometer2 me-2"></i>Admin Panel</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?php echo site_url('logout'); ?>">
                <i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a href="<?php echo site_url('login'); ?>" class="btn btn-sm btn-outline-saffron">Login</a>
          <a href="<?php echo site_url('register'); ?>" class="btn btn-sm btn-saffron d-none d-xl-inline-flex">Register</a>
        <?php endif; ?>
      </div>
    </div><!-- /navbar-collapse -->

  </div><!-- /container -->
</nav>

<!-- Flash Messages -->
<?php
$flash_success = $this->session->flashdata('success');
$flash_warning = $this->session->flashdata('warning');
$flash_danger  = $this->session->flashdata('danger');
if ($flash_success): ?>
  <div class="container mt-2">
    <div class="alert alert-success alert-dismissible fade show">
      <?php echo htmlspecialchars($flash_success); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  </div>
<?php endif; if ($flash_warning): ?>
  <div class="container mt-2">
    <div class="alert alert-warning alert-dismissible fade show">
      <?php echo htmlspecialchars($flash_warning); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  </div>
<?php endif; if ($flash_danger): ?>
  <div class="container mt-2">
    <div class="alert alert-danger alert-dismissible fade show">
      <?php echo htmlspecialchars($flash_danger); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  </div>
<?php endif; ?>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3"
     id="toastContainer" style="z-index:1100"></div>
