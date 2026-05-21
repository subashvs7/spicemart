<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<!-- Status filter pills -->
<div class="margin-b-10">
  <?php
  $statuses = array('' => 'All', 'pending' => 'Pending', 'processing' => 'Processing',
                    'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled');
  foreach ($statuses as $val => $label):
  ?>
    <a href="<?php echo site_url('admin-orders'); ?>?status=<?php echo $val; ?>"
       class="btn btn-sm <?php echo $filter_status === $val ? 'btn-saffron' : 'btn-default'; ?>"
       style="margin-right:4px;margin-bottom:6px">
      <?php echo $label; ?>
    </a>
  <?php endforeach; ?>
</div>

<div class="row">
  <!-- Orders List -->
  <div class="<?php echo $view_order ? 'col-md-7' : 'col-md-12'; ?>">
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">Orders</h3>
      </div>
      <div class="box-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover admin-table">
            <thead>
              <tr><th>Order</th><th>Customer</th><th>Items</th><th>Total</th><th>Payment</th><th>Pay Status</th><th>Status</th><th>Date</th><th></th></tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $o): ?>
              <tr data-order-id="<?php echo $o['id']; ?>" <?php echo ($view_order && $view_order['id'] === (int)$o['id']) ? 'class="warning"' : ''; ?>>
                <td><strong>#<?php echo str_pad($o['id'],5,'0',STR_PAD_LEFT); ?></strong></td>
                <td><?php echo htmlspecialchars($o['customer_name']); ?></td>
                <td><?php echo $o['item_count']; ?></td>
                <td class="text-saffron"><strong><?php echo $this->spice_model->rupees((float)$o['total_amount']); ?></strong></td>
                <td><span class="label label-default"><?php echo strtoupper($o['payment_method']); ?></span></td>
                <td><?php echo $this->spice_model->payment_status_badge($o['payment_status']); ?></td>
                <td><?php echo $this->spice_model->order_status_badge($o['status']); ?></td>
                <td><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
                <td>
                  <a href="<?php echo site_url('admin-orders'); ?>?view=<?php echo $o['id']; ?><?php echo $filter_status ? '&status='.$filter_status : ''; ?>"
                     class="btn btn-xs btn-default"><i class="fa fa-eye"></i></a>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($orders)): ?>
                <tr><td colspan="9" class="text-center text-muted">No orders found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Order Detail Panel -->
  <?php if ($view_order): ?>
  <div class="col-md-5">
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title">Order #<?php echo str_pad($view_order['id'],5,'0',STR_PAD_LEFT); ?></h3>
        <div class="box-tools pull-right">
          <a href="<?php echo site_url('admin-orders'); ?><?php echo $filter_status ? '?status='.$filter_status : ''; ?>"
             class="btn btn-xs btn-default"><i class="fa fa-times"></i></a>
        </div>
      </div>
      <div class="box-body">

        <!-- Status Update Form -->
        <form id="orderUpdateForm" method="post" action="<?php echo site_url('ajax/orders-update'); ?>" class="margin-b-15">
          <input type="hidden" name="order_id" value="<?php echo $view_order['id']; ?>">
          <div class="form-group">
            <label class="text-muted small">Order Status</label>
            <select class="form-control" name="status">
              <?php foreach (array('pending','processing','shipped','delivered','cancelled') as $st): ?>
                <option value="<?php echo $st; ?>" <?php echo $view_order['status']===$st?'selected':''; ?>><?php echo ucfirst($st); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="text-muted small">Tracking Number</label>
                <input type="text" class="form-control input-sm" name="tracking_no"
                       value="<?php echo htmlspecialchars($view_order['tracking_no'] ?? ''); ?>"
                       placeholder="AWB/Tracking No.">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="text-muted small">Courier Name</label>
                <input type="text" class="form-control input-sm" name="courier_name"
                       value="<?php echo htmlspecialchars($view_order['courier_name'] ?? ''); ?>"
                       placeholder="e.g. DTDC, Delhivery">
              </div>
            </div>
          </div>
          <button type="submit" name="update_status" value="1" class="btn btn-saffron btn-sm btn-block">
            <i class="fa fa-save"></i> Update Order
          </button>
        </form>

        <!-- Customer -->
        <div class="callout callout-info">
          <h4><?php echo htmlspecialchars($view_order['customer_name']); ?></h4>
          <p class="text-muted margin-b-0"><?php echo htmlspecialchars($view_order['email']); ?></p>
          <?php if ($view_order['phone']): ?>
            <p class="text-muted margin-b-0"><?php echo htmlspecialchars($view_order['phone']); ?></p>
          <?php endif; ?>
        </div>

        <!-- Payment Info -->
        <div class="clearfix margin-b-10">
          <span class="pull-left">
            <span class="label label-default"><?php echo strtoupper($view_order['payment_method']); ?></span>
            &nbsp;<?php echo $this->spice_model->payment_status_badge($view_order['payment_status']); ?>
          </span>
          <?php if ($view_order['coupon_code']): ?>
            <span class="pull-right text-success small">
              Coupon: <?php echo htmlspecialchars($view_order['coupon_code']); ?> (-<?php echo $this->spice_model->rupees((float)$view_order['coupon_discount']); ?>)
            </span>
          <?php endif; ?>
        </div>

        <!-- Items -->
        <h5>Items Ordered</h5>
        <?php foreach ($order_items as $oi): ?>
        <div class="clearfix margin-b-10">
          <img src="<?php echo $this->spice_model->product_image($oi['image']); ?>" width="40" height="40"
               style="object-fit:cover;border-radius:7px;float:left;margin-right:10px">
          <div>
            <strong><?php echo htmlspecialchars($oi['product_name']); ?></strong><br>
            <small class="text-muted">
              ID: <strong>#<?php echo (int)$oi['product_id']; ?></strong>
              <?php if (!empty($oi['sku'])): ?>
                &nbsp;| SKU: <strong><?php echo htmlspecialchars($oi['sku']); ?></strong>
              <?php endif; ?>
              <?php if (!empty($oi['variant_label'])): ?>
                &nbsp;| <?php echo htmlspecialchars($oi['variant_label']); ?>
              <?php endif; ?>
            </small><br>
            <small class="text-muted">Qty: <?php echo $oi['quantity']; ?> × <?php echo $this->spice_model->rupees((float)$oi['unit_price']); ?></small>
          </div>
        </div>
        <?php endforeach; ?>

        <div class="clearfix border-top margin-t-10 padding-t-10">
          <?php if ($view_order['shipping_charge'] > 0): ?>
            <div class="clearfix">
              <span class="pull-left text-muted small">Shipping</span>
              <span class="pull-right text-muted small"><?php echo $this->spice_model->rupees((float)$view_order['shipping_charge']); ?></span>
            </div>
          <?php endif; ?>
          <div class="clearfix">
            <span class="pull-left"><strong>Total</strong></span>
            <span class="pull-right text-saffron"><strong><?php echo $this->spice_model->rupees((float)$view_order['total_amount']); ?></strong></span>
          </div>
        </div>

        <!-- Shipping Address -->
        <h5 class="margin-t-15">Ship To</h5>
        <p class="text-muted small" style="white-space:pre-line"><?php echo htmlspecialchars($view_order['shipping_address']); ?></p>

        <!-- Meta -->
        <div class="text-muted small">
          Placed: <?php echo date('d M Y, h:i A', strtotime($view_order['created_at'])); ?>
        </div>

        <?php
        $order_pts = $this->db->query(
            "SELECT SUM(points) AS pts FROM loyalty_ledger WHERE ref_type='order' AND ref_id=? AND type='earned'",
            array($view_order['id'])
        )->row()->pts;
        $earn_per  = (int)($this->spice_model->get_setting('loyalty_earn_per')  ?: 10);
        $earn_rate = (int)($this->spice_model->get_setting('loyalty_earn_rate') ?: 1);
        $will_earn = ($earn_per > 0) ? (int)floor(((float)$view_order['total_amount'] / $earn_per) * $earn_rate) : 0;
        ?>
        <div class="callout callout-warning margin-t-10" style="padding:8px 12px">
          <i class="fa fa-star text-warning"></i>
          <?php if ($order_pts > 0): ?>
            <strong><?php echo (int)$order_pts; ?> loyalty points</strong> awarded to customer.
          <?php elseif ($view_order['status'] === 'delivered'): ?>
            <span class="text-muted">Points check: none recorded for this order.</span>
          <?php else: ?>
            Customer will earn <strong><?php echo $will_earn; ?> points</strong> on delivery.
          <?php endif; ?>
        </div>

        <a href="<?php echo site_url('invoice/'.$view_order['id']); ?>" target="_blank"
           class="btn btn-default btn-sm btn-block margin-t-10">
          <i class="fa fa-file-text"></i> View Invoice
        </a>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
