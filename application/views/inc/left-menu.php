<!-- Left sidebar -->
<aside class="main-sidebar">
  <section class="sidebar">
    <div class="user-panel">
      <div class="pull-left image">
        <img src="<?php echo base_url() ?>asset/images/user.jpg" class="img-circle" alt="User Image">
      </div>
      <div class="pull-left info">
        <p><?php echo htmlspecialchars($this->session->userdata(SESS_HEAD.'_user_name')); ?></p>
        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
      </div>
    </div>

    <ul class="sidebar-menu" data-widget="tree">
      <li class="header">MAIN NAVIGATION</li>

      <li <?php if ($page === 'dashboard') echo 'class="active"'; ?>>
        <a href="<?php echo site_url('admin') ?>">
          <i class="fa fa-dashboard"></i> <span>Dashboard</span>
        </a>
      </li>

      <li <?php if ($page === 'products') echo 'class="active"'; ?>>
        <a href="<?php echo site_url('admin-products') ?>">
          <i class="fa fa-cubes"></i> <span>Products</span>
        </a>
      </li>

      <li <?php if ($page === 'categories') echo 'class="active"'; ?>>
        <a href="<?php echo site_url('admin-categories') ?>">
          <i class="fa fa-tags"></i> <span>Categories</span>
        </a>
      </li>

      <li <?php if ($page === 'orders') echo 'class="active"'; ?>>
        <a href="<?php echo site_url('admin-orders') ?>">
          <i class="fa fa-shopping-cart"></i> <span>Orders</span>
        </a>
      </li>

      <li <?php if ($page === 'customers') echo 'class="active"'; ?>>
        <a href="<?php echo site_url('admin-customers') ?>">
          <i class="fa fa-users"></i> <span>Customers</span>
        </a>
      </li>

      <li <?php if ($page === 'brands') echo 'class="active"'; ?>>
        <a href="<?php echo site_url('admin-brands') ?>">
          <i class="fa fa-bookmark"></i> <span>Brands</span>
        </a>
      </li>

      <li <?php if ($page === 'coupons') echo 'class="active"'; ?>>
        <a href="<?php echo site_url('admin-coupons') ?>">
          <i class="fa fa-ticket"></i> <span>Coupons</span>
        </a>
      </li>

      <li <?php if ($page === 'banners') echo 'class="active"'; ?>>
        <a href="<?php echo site_url('admin-banners') ?>">
          <i class="fa fa-image"></i> <span>Banners</span>
        </a>
      </li>

      <li <?php if ($page === 'cms') echo 'class="active"'; ?>>
        <a href="<?php echo site_url('admin-cms') ?>">
          <i class="fa fa-file-text"></i> <span>CMS Pages</span>
        </a>
      </li>

      <li <?php if ($page === 'returns') echo 'class="active"'; ?>>
        <a href="<?php echo site_url('admin-returns') ?>">
          <i class="fa fa-undo"></i>
          <span>Returns</span>
          <?php if (isset($pending_returns) && $pending_returns > 0): ?>
            <span class="pull-right-container"><small class="badge pull-right bg-yellow"><?php echo $pending_returns; ?></small></span>
          <?php endif; ?>
        </a>
      </li>

      <li <?php if ($page === 'contacts') echo 'class="active"'; ?>>
        <a href="<?php echo site_url('admin-contacts') ?>">
          <i class="fa fa-envelope"></i>
          <span>Contacts</span>
          <?php if (isset($unread_contacts) && $unread_contacts > 0): ?>
            <span class="pull-right-container"><small class="badge pull-right bg-red"><?php echo $unread_contacts; ?></small></span>
          <?php endif; ?>
        </a>
      </li>

      <li <?php if ($page === 'shipping') echo 'class="active"'; ?>>
        <a href="<?php echo site_url('admin-shipping') ?>">
          <i class="fa fa-truck"></i> <span>Shipping</span>
        </a>
      </li>

      <?php if (isset($admin_role) && $admin_role === 'admin'): ?>
      <li <?php if ($page === 'roles') echo 'class="active"'; ?>>
        <a href="<?php echo site_url('admin-roles') ?>">
          <i class="fa fa-user-secret"></i> <span>Admin Roles</span>
        </a>
      </li>
      <?php endif; ?>

      <li <?php if ($page === 'reports') echo 'class="active"'; ?>>
        <a href="<?php echo site_url('admin-reports') ?>">
          <i class="fa fa-bar-chart"></i> <span>Reports</span>
        </a>
      </li>

      <?php if (isset($admin_role) && $admin_role === 'admin'): ?>
      <li <?php if ($page === 'settings') echo 'class="active"'; ?>>
        <a href="<?php echo site_url('admin-settings') ?>">
          <i class="fa fa-cog"></i> <span>App Settings</span>
        </a>
      </li>
      <?php endif; ?>

      <li class="divider"></li>

      <li>
        <a href="<?php echo site_url('home') ?>">
          <i class="fa fa-globe"></i> <span>View Store</span>
        </a>
      </li>

      <li>
        <a href="<?php echo site_url('logout') ?>">
          <i class="fa fa-sign-out"></i> <span>Logout</span>
        </a>
      </li>
    </ul>
  </section>
</aside>
