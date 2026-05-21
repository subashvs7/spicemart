<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Stats row -->
<div class="row">
  <div class="col-sm-3">
    <div class="info-box">
      <span class="info-box-icon bg-aqua"><i class="fa fa-id-card-o"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total Uses</span>
        <span class="info-box-number"><?php echo number_format($stats['total_uses']); ?></span>
      </div>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="info-box">
      <span class="info-box-icon bg-green"><i class="fa fa-tag"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total Discount Given</span>
        <span class="info-box-number"><?php echo $this->spice_model->rupees((float)$stats['total_discount']); ?></span>
      </div>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="info-box">
      <span class="info-box-icon bg-yellow"><i class="fa fa-users"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Unique Members</span>
        <span class="info-box-number"><?php echo number_format($stats['unique_members']); ?></span>
      </div>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="info-box">
      <span class="info-box-icon bg-saffron"><i class="fa fa-shopping-cart"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total GMV</span>
        <span class="info-box-number"><?php echo $this->spice_model->rupees((float)$stats['total_gmv']); ?></span>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- Main table -->
  <div class="col-md-9">
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">Usage Log</h3>
        <div class="box-tools pull-right">
          <a href="<?php echo site_url('admin-fazaa-report'); ?>?export=csv&program=<?php echo urlencode($filter_program); ?>&date_from=<?php echo urlencode($filter_date_from); ?>&date_to=<?php echo urlencode($filter_date_to); ?>"
             class="btn btn-xs btn-default"><i class="fa fa-download"></i> Export CSV</a>
          <a href="<?php echo site_url('admin-fazaa'); ?>" class="btn btn-xs btn-saffron">
            <i class="fa fa-cog"></i> Settings
          </a>
        </div>
      </div>
      <!-- Filters -->
      <div class="box-body border-bottom" style="padding-bottom:10px">
        <form method="get" action="<?php echo site_url('admin-fazaa-report'); ?>" class="form-inline">
          <div class="form-group margin-r-5">
            <select class="form-control input-sm" name="program">
              <option value="">All Programs</option>
              <option value="fazaa" <?php echo $filter_program==='fazaa'?'selected':''; ?>>Fazaa</option>
              <option value="isaad" <?php echo $filter_program==='isaad'?'selected':''; ?>>Isaad</option>
            </select>
          </div>
          <div class="form-group margin-r-5">
            <input type="date" class="form-control input-sm" name="date_from"
                   value="<?php echo htmlspecialchars($filter_date_from); ?>" placeholder="From">
          </div>
          <div class="form-group margin-r-5">
            <input type="date" class="form-control input-sm" name="date_to"
                   value="<?php echo htmlspecialchars($filter_date_to); ?>" placeholder="To">
          </div>
          <button type="submit" class="btn btn-sm btn-saffron"><i class="fa fa-filter"></i> Filter</button>
          <a href="<?php echo site_url('admin-fazaa-report'); ?>" class="btn btn-sm btn-default">Reset</a>
        </form>
      </div>
      <div class="box-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover admin-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Program</th>
                <th>Member No</th>
                <th>Customer</th>
                <th>Order</th>
                <th>Disc%</th>
                <th>Discount</th>
                <th>Order Total</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($usages as $u): ?>
              <tr>
                <td><?php echo $u['id']; ?></td>
                <td>
                  <span class="label <?php echo $u['program']==='fazaa' ? 'label-info' : 'label-primary'; ?>">
                    <?php echo strtoupper($u['program']); ?>
                  </span>
                </td>
                <td><code><?php echo htmlspecialchars($u['member_no']); ?></code></td>
                <td><?php echo htmlspecialchars($u['customer_name'] ?: '—'); ?></td>
                <td>
                  <a href="<?php echo site_url('admin-orders'); ?>?view=<?php echo $u['order_id']; ?>" target="_blank">
                    #<?php echo str_pad($u['order_id'],5,'0',STR_PAD_LEFT); ?>
                  </a>
                </td>
                <td><?php echo number_format($u['discount_pct'],1); ?>%</td>
                <td class="text-success"><strong><?php echo $this->spice_model->rupees((float)$u['discount_amt']); ?></strong></td>
                <td><?php echo $this->spice_model->rupees((float)$u['order_total']); ?></td>
                <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($usages)): ?>
                <tr><td colspan="9" class="text-center text-muted">No usage records found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Sidebar: by program -->
  <div class="col-md-3">
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title">By Program</h3>
      </div>
      <div class="box-body">
        <?php if (!empty($by_program)): ?>
          <?php foreach ($by_program as $bp): ?>
          <div class="clearfix margin-b-10">
            <span class="pull-left">
              <span class="label <?php echo $bp['program']==='fazaa' ? 'label-info' : 'label-primary'; ?>">
                <?php echo strtoupper($bp['program']); ?>
              </span>
            </span>
            <span class="pull-right">
              <strong><?php echo number_format($bp['uses']); ?></strong>
              <span class="text-muted small"> uses</span>
            </span>
          </div>
          <div class="clearfix margin-b-15">
            <span class="pull-left text-muted small">Total Discount</span>
            <span class="pull-right text-success small">
              <strong><?php echo $this->spice_model->rupees((float)$bp['disc_total']); ?></strong>
            </span>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-muted text-center small">No data yet.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="box box-default">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-info-circle"></i> Note</h3>
      </div>
      <div class="box-body">
        <p class="text-muted small">Each row represents one order where a Fazaa or Isaad discount was successfully applied at checkout.</p>
        <p class="text-muted small">Discount amount is calculated as the configured % of (subtotal − coupon discount), capped at the maximum allowed discount.</p>
      </div>
    </div>
  </div>
</div>
