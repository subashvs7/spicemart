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

    <?php
    $in_catalog   = in_array($page, array('products','categories','brands'));
    $in_sales     = in_array($page, array('orders','customers','returns'));
    $in_marketing = in_array($page, array('coupons','banners','loyalty'));
    $in_content   = in_array($page, array('cms','why_choose_us','testimonials'));
    $in_settings  = in_array($page, array('shipping','payments'));
    $in_admin_grp = in_array($page, array('roles','settings'));
    $in_fazaa     = $page === 'fazaa';
    $is_admin     = isset($admin_role) && $admin_role === 'admin';
    ?>

    <ul class="sidebar-menu" data-widget="tree">
      <li class="header">MAIN NAVIGATION</li>

      <!-- Dashboard -->
      <li <?php echo $page === 'dashboard' ? 'class="active"' : ''; ?>>
        <a href="<?php echo site_url('admin') ?>">
          <i class="fa fa-dashboard"></i> <span>Dashboard</span>
        </a>
      </li>

      <!-- ── Catalog ──────────────────────────────────────────── -->
      <li class="treeview <?php echo $in_catalog ? 'active' : ''; ?>">
        <a href="#">
          <i class="fa fa-cubes"></i>
          <span>Catalog</span>
          <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
        </a>
        <ul class="treeview-menu">
          <li <?php echo $page === 'products' ? 'class="active"' : ''; ?>>
            <a href="<?php echo site_url('admin-products') ?>">
              <i class="fa fa-cubes"></i> Products
            </a>
          </li>
          <li class="treeview-divider" style="padding:4px 15px 2px;font-size:10px;color:#999;text-transform:uppercase;letter-spacing:.8px;pointer-events:none">
            Masters
          </li>
          <li <?php echo $page === 'categories' ? 'class="active"' : ''; ?>>
            <a href="<?php echo site_url('admin-categories') ?>">
              <i class="fa fa-tags"></i> Categories
            </a>
          </li>
          <li <?php echo $page === 'brands' ? 'class="active"' : ''; ?>>
            <a href="<?php echo site_url('admin-brands') ?>">
              <i class="fa fa-bookmark"></i> Brands
            </a>
          </li>
        </ul>
      </li>

      <!-- ── Sales ────────────────────────────────────────────── -->
      <li class="treeview <?php echo $in_sales ? 'active' : ''; ?>">
        <a href="#">
          <i class="fa fa-shopping-cart"></i>
          <span>Sales</span>
          <?php if (isset($pending_returns) && $pending_returns > 0): ?>
            <span class="pull-right-container"><small class="badge pull-right bg-yellow"><?php echo $pending_returns; ?></small></span>
          <?php else: ?>
            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
          <?php endif; ?>
        </a>
        <ul class="treeview-menu">
          <li <?php echo $page === 'orders' ? 'class="active"' : ''; ?>>
            <a href="<?php echo site_url('admin-orders') ?>">
              <i class="fa fa-list-alt"></i> Orders
            </a>
          </li>
          <li <?php echo $page === 'customers' ? 'class="active"' : ''; ?>>
            <a href="<?php echo site_url('admin-customers') ?>">
              <i class="fa fa-users"></i> Customers
            </a>
          </li>
          <li <?php echo $page === 'returns' ? 'class="active"' : ''; ?>>
            <a href="<?php echo site_url('admin-returns') ?>">
              <i class="fa fa-undo"></i> Returns
              <?php if (isset($pending_returns) && $pending_returns > 0): ?>
                <span class="pull-right-container"><small class="badge pull-right bg-yellow"><?php echo $pending_returns; ?></small></span>
              <?php endif; ?>
            </a>
          </li>
        </ul>
      </li>

      <!-- ── Marketing ────────────────────────────────────────── -->
      <li class="treeview <?php echo $in_marketing ? 'active' : ''; ?>">
        <a href="#">
          <i class="fa fa-bullhorn"></i>
          <span>Marketing</span>
          <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
        </a>
        <ul class="treeview-menu">
          <li <?php echo $page === 'coupons' ? 'class="active"' : ''; ?>>
            <a href="<?php echo site_url('admin-coupons') ?>">
              <i class="fa fa-ticket"></i> Coupons &amp; Vouchers
            </a>
          </li>
          <li <?php echo $page === 'banners' ? 'class="active"' : ''; ?>>
            <a href="<?php echo site_url('admin-banners') ?>">
              <i class="fa fa-image"></i> Banners
            </a>
          </li>
          <li <?php echo $page === 'loyalty' ? 'class="active"' : ''; ?>>
            <a href="<?php echo site_url('admin-loyalty') ?>">
              <i class="fa fa-star"></i> Loyalty &amp; CRM
            </a>
          </li>
        </ul>
      </li>

      <!-- ── Content ───────────────────────────────────────────── -->
      <li class="treeview <?php echo $in_content ? 'active' : ''; ?>">
        <a href="#">
          <i class="fa fa-file-text"></i>
          <span>Content</span>
          <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
        </a>
        <ul class="treeview-menu">
          <li <?php echo $page === 'cms' ? 'class="active"' : ''; ?>>
            <a href="<?php echo site_url('admin-cms') ?>">
              <i class="fa fa-file-text-o"></i> CMS Pages
            </a>
          </li>
          <li <?php echo $page === 'why_choose_us' ? 'class="active"' : ''; ?>>
            <a href="<?php echo site_url('admin-why-choose-us') ?>">
              <i class="fa fa-check-circle"></i> Why Choose Us
            </a>
          </li>
          <li <?php echo $page === 'testimonials' ? 'class="active"' : ''; ?>>
            <a href="<?php echo site_url('admin-testimonials') ?>">
              <i class="fa fa-comments"></i> Testimonials
            </a>
          </li>
        </ul>
      </li>

      <!-- ── Settings ──────────────────────────────────────────── -->
      <li class="treeview <?php echo $in_settings ? 'active' : ''; ?>">
        <a href="#">
          <i class="fa fa-cog"></i>
          <span>Settings</span>
          <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
        </a>
        <ul class="treeview-menu">
          <li <?php echo $page === 'shipping' ? 'class="active"' : ''; ?>>
            <a href="<?php echo site_url('admin-shipping') ?>">
              <i class="fa fa-truck"></i> Shipping
            </a>
          </li>
          <li <?php echo $page === 'payments' ? 'class="active"' : ''; ?>>
            <a href="<?php echo site_url('admin-payments') ?>">
              <i class="fa fa-credit-card"></i> Payments
            </a>
          </li>
        </ul>
      </li>

      <!-- Contacts (standalone) -->
      <li <?php echo $page === 'contacts' ? 'class="active"' : ''; ?>>
        <a href="<?php echo site_url('admin-contacts') ?>">
          <i class="fa fa-envelope"></i>
          <span>Contacts</span>
          <?php if (isset($unread_contacts) && $unread_contacts > 0): ?>
            <span class="pull-right-container"><small class="badge pull-right bg-red"><?php echo $unread_contacts; ?></small></span>
          <?php endif; ?>
        </a>
      </li>

      <!-- Reports (standalone) -->
      <li <?php echo $page === 'reports' ? 'class="active"' : ''; ?>>
        <a href="<?php echo site_url('admin-reports') ?>">
          <i class="fa fa-bar-chart"></i> <span>Reports</span>
        </a>
      </li>

      <!-- POS Integration (standalone) -->
      <li <?php echo $page === 'pos' ? 'class="active"' : ''; ?>>
        <a href="<?php echo site_url('admin-pos') ?>">
          <i class="fa fa-exchange"></i> <span>POS Integration</span>
        </a>
      </li>

      <!-- ── Administration (admin-role only) ─────────────────── -->
      <?php if ($is_admin): ?>
      <li class="treeview <?php echo $in_admin_grp ? 'active' : ''; ?>">
        <a href="#">
          <i class="fa fa-shield"></i>
          <span>Administration</span>
          <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
        </a>
        <ul class="treeview-menu">
          <li <?php echo $page === 'roles' ? 'class="active"' : ''; ?>>
            <a href="<?php echo site_url('admin-roles') ?>">
              <i class="fa fa-user-secret"></i> Admin Roles
            </a>
          </li>
          <li <?php echo $page === 'settings' ? 'class="active"' : ''; ?>>
            <a href="<?php echo site_url('admin-settings') ?>">
              <i class="fa fa-sliders"></i> App Settings
            </a>
          </li>
        </ul>
      </li>
      <?php endif; ?>

      <li class="divider"></li>

      <li>
        <a href="<?php echo site_url('home') ?>" target="_blank">
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
