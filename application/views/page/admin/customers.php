<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
  <!-- Customer Table -->
  <div class="<?php echo $view_customer ? 'col-md-7' : 'col-md-12'; ?>">
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">Customers
          <span class="label label-default"><?php echo count($customers); ?> total</span>
        </h3>
      </div>
      <div class="box-body">
        <!-- Filter bar -->
        <div class="row" style="margin-bottom:10px">
          <div class="col-sm-5">
            <div class="input-group input-group-sm">
              <span class="input-group-addon"><i class="fa fa-search"></i></span>
              <input type="text" class="form-control" id="cust_search" placeholder="Search name, email, phone…">
              <span class="input-group-btn">
                <button type="button" class="btn btn-default" id="cust_clear" title="Clear"><i class="fa fa-times"></i></button>
              </span>
            </div>
          </div>
          <div class="col-sm-4" style="line-height:30px">
            <small class="text-muted" id="cust_count"></small>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-bordered table-hover admin-table" id="cust_table">
            <thead>
              <tr><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Total Spent</th><th>Joined</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php foreach ($customers as $c): ?>
              <tr <?php echo $view_id === (int)$c['id'] ? 'class="warning"' : ''; ?>>
                <td>
                  <div class="clearfix">
                    <div style="width:34px;height:34px;border-radius:50%;background:#7B4228;
                                display:flex;align-items:center;justify-content:center;
                                color:#fff;font-weight:600;font-size:.85rem;float:left;margin-right:8px">
                      <?php echo strtoupper(substr($c['name'],0,1)); ?>
                    </div>
                    <strong><?php echo htmlspecialchars($c['name']); ?></strong>
                  </div>
                </td>
                <td><?php echo htmlspecialchars($c['email']); ?></td>
                <td><?php echo htmlspecialchars($c['phone'] ?: '—'); ?></td>
                <td><span class="label label-default"><?php echo $c['order_count']; ?></span></td>
                <td class="text-saffron"><strong><?php echo $this->spice_model->rupees((float)$c['total_spent']); ?></strong></td>
                <td><?php echo date('d M Y', strtotime($c['created_at'])); ?></td>
                <td>
                  <a href="<?php echo site_url('admin-customers'); ?>?view=<?php echo $c['id']; ?>"
                     class="btn btn-xs btn-default"><i class="fa fa-eye"></i> View</a>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($customers)): ?>
                <tr><td colspan="7" class="text-center text-muted">No customers yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script>
  (function () {
    var rows   = Array.from(document.querySelectorAll('#cust_table tbody tr'));
    var search = document.getElementById('cust_search');
    var count  = document.getElementById('cust_count');
    function run() {
      var q = search.value.trim().toLowerCase();
      var n = 0;
      rows.forEach(function (r) {
        if (r.cells.length < 2) { r.style.display = ''; return; }
        var ok = !q || r.textContent.toLowerCase().indexOf(q) >= 0;
        r.style.display = ok ? '' : 'none';
        if (ok) n++;
      });
      count.textContent = n + ' / ' + rows.length + ' customers';
    }
    search.addEventListener('input', run);
    document.getElementById('cust_clear').addEventListener('click', function () { search.value = ''; run(); });
  })();
  </script>

  <!-- Customer Detail Panel -->
  <?php if ($view_customer): ?>
  <div class="col-md-5">
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title">Customer Profile</h3>
        <div class="box-tools pull-right">
          <a href="<?php echo site_url('admin-customers'); ?>" class="btn btn-xs btn-default"><i class="fa fa-times"></i></a>
        </div>
      </div>
      <div class="box-body text-center">
        <div style="width:65px;height:65px;border-radius:50%;background:#7B4228;
                    display:flex;align-items:center;justify-content:center;
                    margin:0 auto 10px;font-size:1.8rem;color:#fff;font-weight:700">
          <?php echo strtoupper(substr($view_customer['name'],0,1)); ?>
        </div>
        <h4><?php echo htmlspecialchars($view_customer['name']); ?></h4>
        <p class="text-muted"><?php echo htmlspecialchars($view_customer['email']); ?></p>
        <?php if ($view_customer['phone']): ?>
          <p class="text-muted"><?php echo htmlspecialchars($view_customer['phone']); ?></p>
        <?php endif; ?>
        <small class="text-muted">Member since <?php echo date('d M Y', strtotime($view_customer['created_at'])); ?></small>

        <?php
        $total_spent = 0;
        foreach ($cust_orders as $co) {
          if ($co['status'] !== 'cancelled') $total_spent += $co['total_amount'];
        }
        ?>
        <div class="row margin-t-15">
          <div class="col-xs-6">
            <div class="description-block border-right">
              <span class="description-percentage text-saffron"><?php echo count($cust_orders); ?></span>
              <span class="description-text">TOTAL ORDERS</span>
            </div>
          </div>
          <div class="col-xs-6">
            <div class="description-block">
              <span class="description-percentage text-saffron"><?php echo $this->spice_model->rupees((float)$total_spent); ?></span>
              <span class="description-text">TOTAL SPENT</span>
            </div>
          </div>
        </div>

        <?php if ($view_customer['address']): ?>
        <div class="text-left margin-t-15">
          <strong>Default Address</strong>
          <p class="text-muted small" style="white-space:pre-line"><?php echo htmlspecialchars($view_customer['address']); ?></p>
        </div>
        <?php endif; ?>

        <div class="text-left margin-t-10">
          <strong>Order History</strong>
        </div>
        <?php if (empty($cust_orders)): ?>
          <p class="text-muted small">No orders yet.</p>
        <?php else: ?>
          <div style="max-height:250px;overflow-y:auto;margin-top:8px">
            <?php foreach ($cust_orders as $co): ?>
            <div class="clearfix border-bottom padding-b-10 margin-b-10">
              <div class="pull-left">
                <strong>#<?php echo str_pad($co['id'],5,'0',STR_PAD_LEFT); ?></strong><br>
                <small class="text-muted"><?php echo date('d M Y', strtotime($co['created_at'])); ?> · <?php echo $co['item_count']; ?> item<?php echo $co['item_count']!=1?'s':''; ?></small>
              </div>
              <div class="pull-right text-right">
                <strong><?php echo $this->spice_model->rupees((float)$co['total_amount']); ?></strong><br>
                <?php echo $this->spice_model->order_status_badge($co['status']); ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
