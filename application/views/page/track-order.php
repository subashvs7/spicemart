<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container py-4">
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?php echo site_url('account'); ?>?tab=orders">My Orders</a></li>
      <li class="breadcrumb-item active">Track Order</li>
    </ol>
  </nav>

  <h2 class="mb-4" style="font-family:'Playfair Display',serif">
    Track Order #<?php echo str_pad($order['id'],5,'0',STR_PAD_LEFT); ?>
  </h2>

  <div class="row g-4">
    <div class="col-md-7">
      <!-- Status Timeline -->
      <div class="bg-white rounded-xl shadow-soft p-4 mb-4">
        <h5 class="mb-4" style="font-family:'Playfair Display',serif">Order Status</h5>
        <?php
        $steps = array(
          'pending'    => array('icon'=>'bi-clock','label'=>'Order Placed',    'desc'=>'Your order has been received.'),
          'processing' => array('icon'=>'bi-gear', 'label'=>'Processing',      'desc'=>'Your order is being prepared.'),
          'shipped'    => array('icon'=>'bi-truck','label'=>'Shipped',          'desc'=>'Your order is on the way.'),
          'delivered'  => array('icon'=>'bi-check-circle','label'=>'Delivered','desc'=>'Order delivered successfully.'),
        );
        $statusOrder = array_keys($steps);
        $curIdx      = array_search($order['status'], $statusOrder);
        if ($curIdx === false) $curIdx = -1;
        foreach ($steps as $skey => $sinfo):
          $idx   = array_search($skey, $statusOrder);
          $done  = $idx <= $curIdx;
          $active= $idx === $curIdx;
        ?>
        <div class="d-flex gap-3 mb-4 <?php echo $done?'':'opacity-50'; ?>">
          <div style="width:42px;height:42px;border-radius:50%;flex-shrink:0;
                      background:<?php echo $done?'var(--saffron)':'#e9ecef'; ?>;
                      display:flex;align-items:center;justify-content:center">
            <i class="bi <?php echo $sinfo['icon']; ?> <?php echo $done?'text-white':'text-muted'; ?>"></i>
          </div>
          <div>
            <div class="fw-600 <?php echo $active?'text-saffron':''; ?>"><?php echo $sinfo['label']; ?></div>
            <div class="text-muted small"><?php echo $sinfo['desc']; ?></div>
            <?php if ($active && !empty($order['tracking_no'])): ?>
              <div class="mt-1 small">
                <strong>Tracking:</strong>
                <?php echo htmlspecialchars($order['tracking_no']); ?>
                <?php if ($order['courier_name']): ?>
                  via <?php echo htmlspecialchars($order['courier_name']); ?>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>

        <?php if ($order['status'] === 'cancelled'): ?>
          <div class="alert alert-danger mb-0">
            <i class="bi bi-x-circle me-2"></i>This order was cancelled.
          </div>
        <?php endif; ?>
      </div>

      <!-- Items -->
      <div class="bg-white rounded-xl shadow-soft p-4">
        <h5 class="mb-3" style="font-family:'Playfair Display',serif">Items Ordered</h5>
        <?php foreach ($items as $it): ?>
        <div class="d-flex align-items-center gap-3 mb-3">
          <img src="<?php echo $this->spice_model->product_image($it['image']); ?>"
               width="52" height="52" style="object-fit:cover;border-radius:8px">
          <div class="flex-grow-1">
            <div class="fw-600"><?php echo htmlspecialchars($it['product_name']); ?></div>
            <small class="text-muted">Qty: <?php echo $it['quantity']; ?> × <?php echo $this->spice_model->rupees((float)$it['unit_price']); ?></small>
          </div>
          <div class="fw-600"><?php echo $this->spice_model->rupees((float)($it['unit_price']*$it['quantity'])); ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="col-md-5">
      <div class="bg-white rounded-xl shadow-soft p-4 mb-4">
        <h5 class="mb-3" style="font-family:'Playfair Display',serif">Order Summary</h5>
        <div class="summary-row"><span class="text-muted">Order Date</span><span><?php echo date('d M Y',strtotime($order['created_at'])); ?></span></div>
        <div class="summary-row"><span class="text-muted">Payment</span><span><?php echo strtoupper($order['payment_method']); ?></span></div>
        <div class="summary-row"><span class="text-muted">Payment Status</span>
          <span><?php echo $this->spice_model->payment_status_badge($order['payment_status']); ?></span>
        </div>
        <?php if ($order['coupon_code']): ?>
          <div class="summary-row"><span class="text-muted">Coupon (<?php echo htmlspecialchars($order['coupon_code']); ?>)</span>
            <span class="text-success">-<?php echo $this->spice_model->rupees((float)$order['coupon_discount']); ?></span>
          </div>
        <?php endif; ?>
        <div class="summary-row"><span class="text-muted">Shipping</span>
          <span><?php echo $order['shipping_charge']>0 ? $this->spice_model->rupees((float)$order['shipping_charge']) : '<span class="text-success">FREE</span>'; ?></span>
        </div>
        <hr>
        <div class="summary-row summary-total">
          <span>Total</span>
          <span style="color:var(--saffron)"><?php echo $this->spice_model->rupees((float)$order['total_amount']); ?></span>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-soft p-4">
        <h5 class="mb-3" style="font-family:'Playfair Display',serif">Delivered To</h5>
        <p class="text-muted small" style="white-space:pre-line"><?php echo htmlspecialchars($order['shipping_address']); ?></p>
      </div>

      <div class="d-flex gap-2 mt-3">
        <a href="<?php echo site_url('invoice/'.$order['id']); ?>" target="_blank"
           class="btn btn-outline-saffron flex-fill btn-sm">
          <i class="bi bi-receipt me-1"></i> Invoice
        </a>
        <?php if (in_array($order['status'], array('pending','processing'))): ?>
        <a href="<?php echo site_url('cancel-order/'.$order['id']); ?>"
           class="btn btn-outline-danger flex-fill btn-sm">
          <i class="bi bi-x-circle me-1"></i> Cancel
        </a>
        <?php elseif ($order['status'] === 'delivered'): ?>
        <a href="<?php echo site_url('return-order/'.$order['id']); ?>"
           class="btn btn-outline-warning flex-fill btn-sm">
          <i class="bi bi-arrow-return-left me-1"></i> Return
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
