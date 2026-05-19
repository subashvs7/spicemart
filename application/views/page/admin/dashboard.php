<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Stat Cards -->
<div class="row">
  <div class="col-sm-6 col-xl-3">
    <div class="info-box">
      <span class="info-box-icon bg-aqua"><i class="fa fa-shopping-cart"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total Orders</span>
        <span class="info-box-number"><?php echo number_format($total_orders); ?></span>
        <a href="<?php echo site_url('admin-orders'); ?>" class="small text-aqua">View all →</a>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="info-box">
      <span class="info-box-icon bg-green"><i class="fa fa-inr"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Revenue Today</span>
        <span class="info-box-number"><?php echo $this->spice_model->rupees($revenue_today); ?></span>
        <a href="<?php echo site_url('admin-reports'); ?>" class="small text-green">View reports →</a>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="info-box">
      <span class="info-box-icon bg-red"><i class="fa fa-warning"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Low Stock Alerts</span>
        <span class="info-box-number"><?php echo $low_stock; ?></span>
        <a href="<?php echo site_url('admin-products'); ?>?filter=low_stock" class="small text-red">View products →</a>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="info-box">
      <span class="info-box-icon bg-blue"><i class="fa fa-users"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">New Customers (Month)</span>
        <span class="info-box-number"><?php echo $new_customers; ?></span>
        <a href="<?php echo site_url('admin-customers'); ?>" class="small">View customers →</a>
      </div>
    </div>
  </div>
</div>

<!-- Chart + Quick Stats -->
<div class="row">
  <div class="col-md-7">
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title">Monthly Revenue (Last 6 Months)</h3>
      </div>
      <div class="box-body">
        <?php if (empty($monthly)): ?>
          <p class="text-muted">No sales data yet.</p>
        <?php else: ?>
          <canvas id="revenueChart" height="120"></canvas>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-md-5">
    <div class="box box-info">
      <div class="box-header with-border">
        <h3 class="box-title">Quick Overview</h3>
      </div>
      <div class="box-body">
        <div class="row">
          <div class="col-xs-6">
            <div class="description-block border-right">
              <span class="description-percentage text-green"><?php echo $this->spice_model->rupees($total_revenue); ?></span>
              <h5 class="description-header"></h5>
              <span class="description-text">TOTAL REVENUE</span>
            </div>
          </div>
          <div class="col-xs-6">
            <div class="description-block">
              <span class="description-percentage text-yellow"><?php echo $pending_orders; ?></span>
              <h5 class="description-header"></h5>
              <span class="description-text">PENDING ORDERS</span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-6">
            <div class="description-block border-right">
              <span class="description-percentage" style="color:#FF6B35"><?php echo $active_products; ?></span>
              <h5 class="description-header"></h5>
              <span class="description-text">ACTIVE PRODUCTS</span>
            </div>
          </div>
          <div class="col-xs-6">
            <div class="description-block">
              <span class="description-percentage text-aqua"><?php echo $total_customers; ?></span>
              <h5 class="description-header"></h5>
              <span class="description-text">TOTAL CUSTOMERS</span>
            </div>
          </div>
        </div>

        <h5 class="text-muted" style="margin-top:20px;font-size:.78rem;text-transform:uppercase;letter-spacing:.5px">Order Status</h5>
        <?php
        $all_statuses = array('pending','processing','shipped','delivered','cancelled');
        $bar_colors   = array('pending'=>'#95a5a6','processing'=>'#3498db','shipped'=>'#1abc9c','delivered'=>'#27ae60','cancelled'=>'#e74c3c');
        foreach ($all_statuses as $st):
          $cnt = $status_counts[$st] ?? 0;
          $pct = $total_orders > 0 ? round($cnt / $total_orders * 100) : 0;
        ?>
        <div style="margin-bottom:8px">
          <div class="clearfix">
            <span class="pull-left"><?php echo ucfirst($st); ?></span>
            <span class="pull-right"><?php echo $cnt; ?> (<?php echo $pct; ?>%)</span>
          </div>
          <div class="progress progress-xs">
            <div class="progress-bar" style="width:<?php echo $pct; ?>%;background:<?php echo $bar_colors[$st]; ?>"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- Recent Orders -->
<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title">Recent Orders</h3>
    <div class="box-tools pull-right">
      <a href="<?php echo site_url('admin-orders'); ?>" class="btn btn-sm btn-saffron">View All</a>
    </div>
  </div>
  <div class="box-body">
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>Order ID</th><th>Customer</th><th>Amount</th><th>Payment</th><th>Status</th><th>Date</th><th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent_orders as $ord): ?>
          <tr>
            <td><strong>#<?php echo str_pad($ord['id'],5,'0',STR_PAD_LEFT); ?></strong></td>
            <td><?php echo htmlspecialchars($ord['customer_name']); ?></td>
            <td class="text-saffron"><strong><?php echo $this->spice_model->rupees((float)$ord['total_amount']); ?></strong></td>
            <td><span class="label label-default"><?php echo strtoupper($ord['payment_method']); ?></span></td>
            <td><?php echo $this->spice_model->order_status_badge($ord['status']); ?></td>
            <td><?php echo date('d M Y', strtotime($ord['created_at'])); ?></td>
            <td>
              <a href="<?php echo site_url('admin-orders'); ?>?view=<?php echo $ord['id']; ?>" class="btn btn-xs btn-default">View</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($recent_orders)): ?>
            <tr><td colspan="7" class="text-center text-muted">No orders yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if (!empty($monthly)): ?>
<script>
var ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?php echo $chart_labels; ?>,
    datasets: [{
      label: 'Revenue (₹)',
      data: <?php echo $chart_revenue; ?>,
      backgroundColor: 'rgba(255,107,53,.75)',
      borderColor: '#FF6B35',
      borderWidth: 2,
      borderRadius: 4
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, ticks: { callback: function(v){ return '₹' + v.toLocaleString('en-IN'); } } },
      x: { grid: { display: false } }
    }
  }
});
</script>
<?php endif; ?>
