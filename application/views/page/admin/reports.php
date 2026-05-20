<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Date Filter -->
<div class="box">
  <div class="box-body">
    <form method="get" action="<?php echo site_url('admin-reports'); ?>" class="form-inline">
      <div class="form-group">
        <label>From: &nbsp;</label>
        <input type="date" class="form-control" name="from" value="<?php echo htmlspecialchars($date_from); ?>">
      </div>
      &nbsp;
      <div class="form-group">
        <label>To: &nbsp;</label>
        <input type="date" class="form-control" name="to" value="<?php echo htmlspecialchars($date_to); ?>">
      </div>
      &nbsp;
      <button type="submit" class="btn btn-saffron"><i class="fa fa-filter"></i> Apply</button>
      &nbsp;
      <a href="<?php echo site_url('admin-reports'); ?>?from=<?php echo $date_from; ?>&to=<?php echo $date_to; ?>&export=csv"
         class="btn btn-default"><i class="fa fa-download"></i> Export CSV</a>
    </form>
    <div class="margin-t-10">
      <?php
      $quick = array(
        'Today'      => array(date('Y-m-d'), date('Y-m-d')),
        'This Week'  => array(date('Y-m-d', strtotime('monday this week')), date('Y-m-d')),
        'This Month' => array(date('Y-m-01'), date('Y-m-d')),
        'Last Month' => array(date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month'))),
        'This Year'  => array(date('Y-01-01'), date('Y-m-d')),
      );
      foreach ($quick as $lbl => list($f,$t)):
      ?>
        <a href="<?php echo site_url('admin-reports'); ?>?from=<?php echo $f; ?>&to=<?php echo $t; ?>"
           class="btn btn-xs <?php echo ($date_from===$f && $date_to===$t)?'btn-saffron':'btn-default'; ?>"
           style="margin-right:4px"><?php echo $lbl; ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Summary Cards -->
<div class="row">
  <div class="col-sm-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-aqua"><i class="fa fa-shopping-cart"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total Orders</span>
        <span class="info-box-number"><?php echo number_format($summary['total_orders']); ?></span>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-green"><i class="fa fa-inr"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total Revenue</span>
        <span class="info-box-number"><?php echo $this->spice_model->rupees((float)$summary['total_revenue']); ?></span>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-yellow"><i class="fa fa-bar-chart"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Avg Order Value</span>
        <span class="info-box-number"><?php echo $this->spice_model->rupees((float)$summary['avg_order']); ?></span>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-red"><i class="fa fa-shopping-bag"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Units Sold</span>
        <span class="info-box-number"><?php echo number_format($summary['units_sold']); ?></span>
      </div>
    </div>
  </div>
</div>

<!-- Daily Revenue Chart -->
<?php if (!empty($daily)): ?>
<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title">Daily Revenue — <?php echo htmlspecialchars($date_from); ?> to <?php echo htmlspecialchars($date_to); ?></h3>
  </div>
  <div class="box-body">
    <canvas id="dailyChart" height="100"></canvas>
  </div>
</div>
<?php endif; ?>

<div class="row">
  <!-- Top Products -->
  <div class="col-md-5">
    <div class="box">
      <div class="box-header with-border"><h3 class="box-title">Top Products</h3></div>
      <div class="box-body">
        <?php if (empty($top_products)): ?>
          <p class="text-muted">No sales in this period.</p>
        <?php else: ?>
          <?php foreach ($top_products as $i => $tp): ?>
          <div class="clearfix margin-b-10">
            <span class="pull-left text-muted" style="width:24px;font-weight:700"><?php echo $i+1; ?>.</span>
            <div style="overflow:hidden">
              <strong><?php echo htmlspecialchars($tp['name']); ?></strong><br>
              <small class="text-muted"><?php echo $tp['qty_sold']; ?> units sold</small>
            </div>
            <span class="pull-right text-saffron"><strong><?php echo $this->spice_model->rupees((float)$tp['revenue']); ?></strong></span>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Daily Breakdown Table -->
  <div class="col-md-7">
    <div class="box">
      <div class="box-header with-border"><h3 class="box-title">Daily Breakdown</h3></div>
      <div class="box-body table-responsive" style="max-height:400px;overflow-y:auto">
        <table class="table table-sm table-bordered">
          <thead class="thead-light">
            <tr><th>Date</th><th class="text-center">Orders</th><th class="text-right">Revenue</th></tr>
          </thead>
          <tbody>
            <?php foreach ($daily as $d): ?>
            <tr>
              <td><?php echo date('D, d M Y', strtotime($d['sale_date'])); ?></td>
              <td class="text-center"><?php echo $d['orders']; ?></td>
              <td class="text-right text-saffron"><strong><?php echo $this->spice_model->rupees((float)$d['revenue']); ?></strong></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($daily)): ?>
              <tr><td colspan="3" class="text-center text-muted">No sales in this period.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($daily)): ?>
<script>
var labels  = <?php echo $chart_labels;  ?>.reverse();
var revenue = <?php echo $chart_revenue; ?>.reverse();
var ctx = document.getElementById('dailyChart').getContext('2d');
new Chart(ctx, {
  type: 'line',
  data: {
    labels: labels,
    datasets: [{
      label: 'Revenue (₹)',
      data: revenue,
      borderColor: '#7B4228',
      backgroundColor: 'rgba(123,66,40,.1)',
      borderWidth: 2.5,
      pointRadius: 4,
      pointBackgroundColor: '#7B4228',
      fill: true,
      tension: 0.35
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, ticks: { callback: function(v){ return '₹'+v.toLocaleString('en-IN'); } } },
      x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } }
    }
  }
});
</script>
<?php endif; ?>
