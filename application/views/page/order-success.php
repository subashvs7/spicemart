<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container py-5">
  <div class="text-center mb-5">
    <div style="font-size:5rem">🎉</div>
    <h1 class="mt-3" style="font-family:'Playfair Display',serif;color:var(--saffron)">
      Order Placed Successfully!
    </h1>
    <p class="text-muted fs-5 mt-2">
      Thank you, <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>!
      Your order has been received and is being processed.
    </p>
    <span class="badge bg-success fs-6 px-4 py-2">
      <i class="bi bi-check-circle me-2"></i>Order Confirmed
    </span>
  </div>

  <div class="row g-4 justify-content-center">
    <div class="col-lg-8">

      <!-- Order Info -->
      <div class="bg-white rounded-xl shadow-soft p-4 mb-4">
        <div class="row g-3 text-center">
          <div class="col-6 col-md-3">
            <div class="small text-muted mb-1">Order ID</div>
            <div class="fw-700 text-saffron">#<?php echo str_pad($order_id, 5, '0', STR_PAD_LEFT); ?></div>
          </div>
          <div class="col-6 col-md-3">
            <div class="small text-muted mb-1">Date</div>
            <div class="fw-600"><?php echo date('d M Y', strtotime($order['created_at'])); ?></div>
          </div>
          <div class="col-6 col-md-3">
            <div class="small text-muted mb-1">Payment</div>
            <div class="fw-600"><?php echo $order['payment_method'] === 'cod' ? '💵 Cash on Delivery' : '💳 Online'; ?></div>
          </div>
          <div class="col-6 col-md-3">
            <div class="small text-muted mb-1">Expected By</div>
            <div class="fw-600 text-success"><?php echo date('D, d M Y', strtotime('+5 weekdays')); ?></div>
          </div>
        </div>
      </div>

      <!-- Items -->
      <div class="bg-white rounded-xl shadow-soft p-4 mb-4">
        <h5 class="mb-4" style="font-family:'Playfair Display',serif">Your Items</h5>
        <?php foreach ($items as $item): ?>
        <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
          <img src="<?php echo $this->spice_model->product_image($item['image']); ?>"
               width="60" height="60" style="object-fit:cover;border-radius:10px"
               alt="<?php echo htmlspecialchars($item['product_name']); ?>">
          <div class="flex-grow-1">
            <div class="fw-600"><?php echo htmlspecialchars($item['product_name']); ?></div>
            <div class="small text-muted">Qty: <?php echo $item['quantity']; ?> × <?php echo $this->spice_model->rupees((float)$item['unit_price']); ?></div>
          </div>
          <div class="fw-700 text-saffron">
            <?php echo $this->spice_model->rupees((float)($item['unit_price'] * $item['quantity'])); ?>
          </div>
        </div>
        <?php endforeach; ?>
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Total Amount</span>
          <span class="fw-700 fs-5 text-saffron"><?php echo $this->spice_model->rupees((float)$order['total_amount']); ?></span>
        </div>
      </div>

      <!-- Shipping Address -->
      <div class="bg-white rounded-xl shadow-soft p-4 mb-4">
        <h5 class="mb-3" style="font-family:'Playfair Display',serif">
          <i class="bi bi-geo-alt text-saffron me-2"></i>Delivering To
        </h5>
        <p class="mb-0" style="white-space:pre-line"><?php echo htmlspecialchars($order['shipping_address']); ?></p>
      </div>

      <!-- Order Tracking -->
      <div class="bg-white rounded-xl shadow-soft p-4 mb-4">
        <h5 class="mb-4" style="font-family:'Playfair Display',serif">Order Status</h5>
        <?php
        $steps   = array('pending','processing','shipped','delivered');
        $labels  = array('Order Placed','Being Processed','Shipped','Delivered');
        $icons   = array('📋','⚙️','🚚','✅');
        $current = array_search($order['status'], $steps) ?: 0;
        ?>
        <div class="d-flex justify-content-between position-relative">
          <div style="position:absolute;top:20px;left:10%;right:10%;height:3px;
                      background:linear-gradient(to right,var(--saffron) <?php echo ($current/3*100); ?>%,#dee2e6 <?php echo ($current/3*100); ?>%)">
          </div>
          <?php foreach ($steps as $i => $step): ?>
          <div class="text-center" style="flex:1;position:relative">
            <div class="mx-auto mb-2 d-flex align-items-center justify-content-center"
                 style="width:42px;height:42px;border-radius:50%;z-index:1;position:relative;
                        background:<?php echo $i <= $current ? 'var(--saffron)' : '#dee2e6'; ?>;
                        color:<?php echo $i <= $current ? '#fff' : '#999'; ?>;font-size:1.1rem">
              <?php echo $icons[$i]; ?>
            </div>
            <div class="small <?php echo $i <= $current ? 'fw-600 text-saffron' : 'text-muted'; ?>">
              <?php echo $labels[$i]; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="d-flex flex-wrap gap-3 justify-content-center mt-2">
        <a href="<?php echo site_url('account'); ?>?tab=orders" class="btn btn-outline-saffron">
          <i class="bi bi-box me-2"></i>View All Orders
        </a>
        <a href="<?php echo site_url('shop'); ?>" class="btn btn-saffron">
          <i class="bi bi-shop me-2"></i>Continue Shopping
        </a>
      </div>

    </div>
  </div>
</div>
