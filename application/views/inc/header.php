<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo isset($page_title) ? $page_title . ' | ' : ''; echo APP_NAME; ?> Admin</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="<?php echo base_url() ?>asset/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?php echo base_url() ?>asset/bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="<?php echo base_url() ?>asset/bower_components/Ionicons/css/ionicons.min.css">
  <!-- AdminLTE -->
  <link rel="stylesheet" href="<?php echo base_url() ?>asset/dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="<?php echo base_url() ?>asset/dist/css/skins/_all-skins.min.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700">
  <style>
    .skin-blue .main-header .logo { background: #2C1810; }
    .skin-blue .main-header .navbar { background: #3d2214; }
    .skin-blue .main-sidebar, .skin-blue .left-side { background: #222d32; }
    .btn-saffron { background:#FF6B35; color:#fff; border-color:#FF6B35; }
    .btn-saffron:hover { background:#e55a25; color:#fff; }
    .text-saffron { color:#FF6B35 !important; }
    .stat-card { background:#fff; border-radius:10px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,.07); }
    .stat-value { font-size:2rem; font-weight:700; }
    .form-card { background:#fff; border-radius:10px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,.07); margin-bottom:20px; }
    .admin-thumb { width:48px; height:48px; object-fit:cover; border-radius:8px; }
    .admin-table th { font-size:.78rem; text-transform:uppercase; letter-spacing:.5px; }
  </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <header class="main-header">
    <a href="<?php echo site_url('admin') ?>" class="logo">
      <span class="logo-mini">SM</span>
      <span class="logo-lg"><?php echo APP_NAME; ?></span>
    </a>
    <nav class="navbar navbar-static-top">
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="<?php echo base_url() ?>asset/images/user.jpg" class="user-image" alt="User">
              <span class="hidden-xs"><?php echo htmlspecialchars($this->session->userdata(SESS_HEAD.'_user_name')); ?></span>
            </a>
            <ul class="dropdown-menu">
              <li class="user-header">
                <img src="<?php echo base_url() ?>asset/images/user.jpg" class="img-circle" alt="User">
                <p>
                  <?php echo htmlspecialchars($this->session->userdata(SESS_HEAD.'_user_name')); ?>
                  <small><?php echo date('d M Y'); ?></small>
                </p>
              </li>
              <li class="user-footer">
                <div class="pull-right">
                  <a href="<?php echo site_url('logout') ?>" class="btn btn-default btn-flat">Sign out</a>
                </div>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>
  </header>

  <?php include_once('left-menu.php'); ?>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <section class="content-header">
      <h1><?php
        $titles = array(
          'dashboard'  => 'Dashboard',
          'products'   => 'Product Management',
          'orders'     => 'Order Management',
          'categories' => 'Category Management',
          'customers'  => 'Customer Management',
          'reports'    => 'Sales Reports',
        );
        echo $titles[$page] ?? 'Admin';
      ?></h1>
    </section>
    <section class="content">
