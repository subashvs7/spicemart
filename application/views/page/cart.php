<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container py-4">
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?php echo site_url('home'); ?>">Home</a></li>
      <li class="breadcrumb-item active">Cart</li>
    </ol>
  </nav>

  <h2 class="mb-4" style="font-family:'Playfair Display',serif">
    🛒 My Cart
    <?php if (!empty($cart_items)): ?>
      <span class="fs-6 text-muted fw-normal ms-2">
        (<?php echo count($cart_items); ?> item<?php echo count($cart_items) !== 1 ? 's' : ''; ?>)
      </span>
    <?php endif; ?>
  </h2>

  <?php if (empty($cart_items)): ?>
    <div class="empty-state py-5">
      <div class="empty-icon">🛒</div>
      <h4 class="mt-3">Your cart is empty</h4>
      <p class="text-muted">Looks like you haven't added any spices yet.</p>
      <a href="<?php echo site_url('shop'); ?>" class="btn btn-saffron mt-2">
        <i class="bi bi-shop me-2"></i>Continue Shopping
      </a>
    </div>
  <?php else: ?>

    <?php if (!$is_logged_in): ?>
      <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        Please <a href="<?php echo site_url('login'); ?>">login</a> to manage your cart and checkout.
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <!-- Cart Items -->
      <div class="col-lg-8">
        <div class="bg-white rounded-xl shadow-soft p-0 overflow-hidden">
          <table class="table cart-table mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4 py-3">Product</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Price</th>
                <th class="text-end">Total</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cart_items as $item): ?>
              <tr id="cart-row-<?php echo $item['cart_id']; ?>" class="align-middle">
                <td class="ps-4 py-3">
                  <div class="d-flex align-items-center gap-3">
                    <img src="<?php echo $this->spice_model->product_image($item['image']); ?>"
                         class="cart-img" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <div>
                      <a href="<?php echo site_url('product/'.$item['product_id']); ?>" class="fw-600 text-dark d-block">
                        <?php echo htmlspecialchars($item['name']); ?>
                      </a>
                      <?php if (!empty($item['variant_label'])): ?>
                        <small class="text-muted"><?php echo htmlspecialchars($item['variant_label']); ?></small>
                      <?php endif; ?>
                      <?php if ($item['stock_qty'] < $item['quantity']): ?>
                        <small class="text-danger d-block">Only <?php echo $item['stock_qty']; ?> in stock</small>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
                <td class="text-center">
                  <div class="d-flex align-items-center justify-content-center gap-1">
                    <button class="btn btn-sm btn-light px-2 py-1"
                            data-qty-change="-1" data-cart-id="<?php echo $item['cart_id']; ?>">−</button>
                    <input type="number" class="qty-input" value="<?php echo $item['quantity']; ?>"
                           min="1" max="<?php echo $item['stock_qty']; ?>"
                           data-cart-id="<?php echo $item['cart_id']; ?>">
                    <button class="btn btn-sm btn-light px-2 py-1"
                            data-qty-change="1" data-cart-id="<?php echo $item['cart_id']; ?>">+</button>
                  </div>
                </td>
                <td class="text-end"><?php echo $this->spice_model->rupees((float)$item['price']); ?></td>
                <td class="text-end fw-600 text-saffron">
                  <?php echo $this->spice_model->rupees((float)($item['price'] * $item['quantity'])); ?>
                </td>
                <td class="pe-3 text-end">
                  <button class="btn btn-sm btn-outline-danger"
                          data-remove-cart="<?php echo $item['cart_id']; ?>" title="Remove">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="mt-3">
          <a href="<?php echo site_url('shop'); ?>" class="btn btn-outline-saffron btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Continue Shopping
          </a>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="col-lg-4">
        <div class="summary-card">
          <h5 class="mb-4" style="font-family:'Playfair Display',serif">Order Summary</h5>
          <div class="summary-row">
            <span class="text-muted">Subtotal</span>
            <span><?php echo $this->spice_model->rupees($subtotal); ?></span>
          </div>
          <div class="summary-row">
            <span class="text-muted">Shipping</span>
            <?php if ($shipping === 0): ?>
              <span class="text-success fw-600">FREE 🎉</span>
            <?php else: ?>
              <span><?php echo $this->spice_model->rupees($shipping); ?></span>
            <?php endif; ?>
          </div>
          <?php if ($subtotal < $shipping_free): ?>
          <div class="alert alert-info p-2 small mt-2 mb-2">
            Add <strong><?php echo $this->spice_model->rupees($shipping_free - $subtotal); ?></strong> more for free shipping!
          </div>
          <?php endif; ?>
          <div class="summary-row summary-total mt-3">
            <span class="fw-700">Grand Total</span>
            <span style="color:var(--saffron)"><?php echo $this->spice_model->rupees($grand_total); ?></span>
          </div>

          <?php if ($is_logged_in): ?>
          <a href="<?php echo site_url('checkout'); ?>" class="btn btn-saffron w-100 mt-4 btn-lg">
            Proceed to Checkout <i class="bi bi-arrow-right ms-2"></i>
          </a>
          <?php else: ?>
          <a href="<?php echo site_url('login'); ?>" class="btn btn-saffron w-100 mt-4 btn-lg">
            Login to Checkout <i class="bi bi-arrow-right ms-2"></i>
          </a>
          <?php endif; ?>

          <div class="text-center mt-3">
            <small class="text-muted d-block mb-2">We accept</small>
            <div class="d-flex justify-content-center gap-2">
              <span class="badge bg-light text-dark border">💵 Cash on Delivery</span>
              <span class="badge bg-light text-dark border">💳 Online Payment</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>
