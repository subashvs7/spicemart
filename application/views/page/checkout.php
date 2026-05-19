<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container py-4">
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?php echo site_url('home'); ?>">Home</a></li>
      <li class="breadcrumb-item"><a href="<?php echo site_url('cart'); ?>">Cart</a></li>
      <li class="breadcrumb-item active">Checkout</li>
    </ol>
  </nav>

  <h2 class="mb-4" style="font-family:'Playfair Display',serif">Checkout</h2>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" action="<?php echo site_url('checkout'); ?>">
    <div class="row g-4">

      <!-- Left: Address + Payment -->
      <div class="col-lg-7">

        <!-- Saved Addresses -->
        <?php if (!empty($addresses)): ?>
        <div class="bg-white rounded-xl shadow-soft p-4 mb-4">
          <h5 class="mb-3" style="font-family:'Playfair Display',serif">
            <i class="bi bi-geo-alt me-2 text-saffron"></i>Saved Addresses
          </h5>
          <div class="d-flex flex-column gap-2 mb-3">
            <?php foreach ($addresses as $addr): ?>
            <label class="d-flex align-items-start gap-3 p-3 rounded-3 border" style="cursor:pointer">
              <input type="radio" name="use_saved_address" value="<?php echo $addr['id']; ?>"
                     <?php echo $addr['is_default'] ? 'checked' : ''; ?> style="margin-top:3px">
              <div>
                <div class="fw-600"><?php echo htmlspecialchars($addr['name']); ?>
                  <span class="badge bg-light text-dark border ms-2 small"><?php echo htmlspecialchars($addr['label'] ?: 'Home'); ?></span>
                  <?php if ($addr['is_default']): ?><span class="badge ms-1" style="background:var(--saffron);color:#fff;font-size:.7rem">Default</span><?php endif; ?>
                </div>
                <div class="text-muted small"><?php echo htmlspecialchars($addr['phone']); ?></div>
                <div class="text-muted small"><?php echo htmlspecialchars($addr['address_line'].', '.$addr['city'].', '.$addr['state'].' - '.$addr['pincode']); ?></div>
              </div>
            </label>
            <?php endforeach; ?>
            <label class="d-flex align-items-center gap-3 p-3 rounded-3 border" style="cursor:pointer">
              <input type="radio" name="use_saved_address" value="0" id="useNewAddress">
              <span>Use a new address</span>
            </label>
          </div>
        </div>
        <?php endif; ?>

        <!-- New Address Form -->
        <div class="bg-white rounded-xl shadow-soft p-4 mb-4" id="newAddressForm"
             style="<?php echo !empty($addresses) ? 'display:none' : ''; ?>">
          <h5 class="mb-4" style="font-family:'Playfair Display',serif">
            <i class="bi bi-plus-circle me-2 text-saffron"></i>Shipping Address
          </h5>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-600">Full Name *</label>
              <input type="text" class="form-control" name="full_name"
                     value="<?php echo htmlspecialchars($this->input->post('full_name') ?: ''); ?>"
                     placeholder="Your full name">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-600">Phone *</label>
              <input type="tel" class="form-control" name="phone"
                     value="<?php echo htmlspecialchars($this->input->post('phone') ?: ''); ?>"
                     placeholder="10-digit mobile number">
            </div>
            <div class="col-12">
              <label class="form-label small fw-600">Address *</label>
              <textarea class="form-control" name="address" rows="2"
                        placeholder="Flat no, building, street, area"><?php echo htmlspecialchars($this->input->post('address') ?: ''); ?></textarea>
            </div>
            <div class="col-md-5">
              <label class="form-label small fw-600">City *</label>
              <input type="text" class="form-control" name="city"
                     value="<?php echo htmlspecialchars($this->input->post('city') ?: ''); ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-600">State</label>
              <select class="form-select" name="state">
                <?php
                $states = array('Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh',
                  'Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala',
                  'Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland',
                  'Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura',
                  'Uttar Pradesh','Uttarakhand','West Bengal','Delhi','Jammu & Kashmir','Ladakh','Puducherry');
                $posted_state = $this->input->post('state') ?: '';
                foreach ($states as $s):
                ?>
                  <option value="<?php echo $s; ?>" <?php echo $posted_state===$s?'selected':''; ?>><?php echo $s; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-600">PIN Code *</label>
              <input type="text" class="form-control" name="pincode"
                     value="<?php echo htmlspecialchars($this->input->post('pincode') ?: ''); ?>"
                     maxlength="6" pattern="\d{6}" placeholder="6 digits">
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="save_address" id="saveAddr" value="1">
                <label class="form-check-label small" for="saveAddr">Save this address for future orders</label>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Method -->
        <div class="bg-white rounded-xl shadow-soft p-4">
          <h5 class="mb-4" style="font-family:'Playfair Display',serif">
            <i class="bi bi-credit-card me-2 text-saffron"></i>Payment Method
          </h5>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="d-block">
                <input type="radio" name="payment_method" value="cod" class="d-none" checked>
                <div class="p-3 rounded-3 payment-option selected-payment" style="cursor:pointer;border:2px solid var(--saffron);background:rgba(255,107,53,.05)">
                  <div class="fs-3 mb-2">💵</div>
                  <div class="fw-600">Cash on Delivery</div>
                  <small class="text-muted">Pay when your order arrives</small>
                </div>
              </label>
            </div>
            <?php if (!empty($razorpay_key)): ?>
            <div class="col-md-6">
              <label class="d-block">
                <input type="radio" name="payment_method" value="razorpay" class="d-none">
                <div class="p-3 rounded-3 border payment-option" style="cursor:pointer">
                  <div class="fs-3 mb-2">💳</div>
                  <div class="fw-600">Online Payment</div>
                  <small class="text-muted">UPI / Card / Net Banking via Razorpay</small>
                </div>
              </label>
            </div>
            <?php else: ?>
            <div class="col-md-6">
              <div class="p-3 rounded-3 border" style="opacity:.5">
                <div class="fs-3 mb-2">💳</div>
                <div class="fw-600">Online Payment</div>
                <small class="text-muted">Coming soon</small>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="col-lg-5">
        <div class="summary-card">
          <h5 class="mb-4" style="font-family:'Playfair Display',serif">Order Summary</h5>

          <?php foreach ($items as $item): ?>
          <div class="d-flex align-items-center gap-2 mb-3">
            <img src="<?php echo $this->spice_model->product_image($item['image']); ?>" class="rounded" width="44" height="44" style="object-fit:cover">
            <div style="flex:1;min-width:0">
              <div class="small fw-600 text-truncate"><?php echo htmlspecialchars($item['name']); ?></div>
              <?php if (!empty($item['variant_label'])): ?>
                <div class="small text-saffron"><?php echo htmlspecialchars($item['variant_label']); ?></div>
              <?php endif; ?>
              <div class="small text-muted">Qty: <?php echo $item['quantity']; ?></div>
            </div>
            <div class="small fw-600">
              <?php $price = $item['offer_price'] ?: $item['price']; ?>
              <?php echo $this->spice_model->rupees((float)($price*$item['quantity'])); ?>
            </div>
          </div>
          <?php endforeach; ?>
          <hr>

          <!-- Coupon -->
          <div class="mb-3" id="couponSection">
            <?php if (!empty($coupon['code'])): ?>
              <div class="d-flex align-items-center justify-content-between p-2 rounded-3"
                   style="background:rgba(40,167,69,.08);border:1px solid rgba(40,167,69,.3)">
                <span class="small">
                  <i class="bi bi-ticket-perforated text-success me-1"></i>
                  <strong><?php echo htmlspecialchars($coupon['code']); ?></strong> applied!
                </span>
                <a href="<?php echo site_url('cart/remove-coupon'); ?>" class="text-danger small">Remove</a>
              </div>
            <?php else: ?>
              <div class="input-group input-group-sm">
                <input type="text" class="form-control" id="couponInput" placeholder="Coupon code">
                <button class="btn btn-outline-saffron" type="button" id="applyCouponBtn">Apply</button>
              </div>
              <div id="couponMsg" class="small mt-1"></div>
            <?php endif; ?>
          </div>

          <div class="summary-row"><span class="text-muted">Subtotal</span><span><?php echo $this->spice_model->rupees($subtotal); ?></span></div>
          <?php if ($discount > 0): ?>
          <div class="summary-row"><span class="text-muted">Discount</span><span class="text-success">-<?php echo $this->spice_model->rupees($discount); ?></span></div>
          <?php endif; ?>
          <div class="summary-row">
            <span class="text-muted">Shipping</span>
            <?php if ($shipping === 0): ?>
              <span class="text-success fw-600">FREE</span>
            <?php else: ?>
              <span><?php echo $this->spice_model->rupees($shipping); ?></span>
            <?php endif; ?>
          </div>
          <div class="summary-row summary-total">
            <span>Grand Total</span>
            <span style="color:var(--saffron)"><?php echo $this->spice_model->rupees($total); ?></span>
          </div>
          <button type="submit" class="btn btn-saffron w-100 btn-lg mt-4">
            <i class="bi bi-check-circle me-2"></i>Place Order
          </button>
          <p class="text-center text-muted small mt-3 mb-0">
            <i class="bi bi-shield-lock me-1"></i>Your info is safe &amp; secure.
          </p>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
// Toggle new address form based on saved address selection
document.querySelectorAll('[name="use_saved_address"]').forEach(function(radio) {
  radio.addEventListener('change', function() {
    document.getElementById('newAddressForm').style.display =
      this.value === '0' ? 'block' : 'none';
  });
});

// Payment option highlight
document.querySelectorAll('[name="payment_method"]').forEach(function(radio) {
  radio.addEventListener('change', function() {
    document.querySelectorAll('.payment-option').forEach(function(el) {
      el.style.border = '2px solid #dee2e6';
      el.style.background = '';
    });
    this.closest('label').querySelector('.payment-option').style.border = '2px solid var(--saffron)';
    this.closest('label').querySelector('.payment-option').style.background = 'rgba(255,107,53,.05)';
  });
});

// Coupon apply
<?php if (empty($coupon['code'])): ?>
document.getElementById('applyCouponBtn') && document.getElementById('applyCouponBtn').addEventListener('click', function() {
  var code = document.getElementById('couponInput').value.trim();
  var msg  = document.getElementById('couponMsg');
  if (!code) { msg.innerHTML = '<span class="text-danger">Enter a coupon code.</span>'; return; }
  fetch('<?php echo site_url("cart/apply-coupon"); ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ code: code, subtotal: <?php echo $subtotal; ?> })
  }).then(function(r) { return r.json(); }).then(function(data) {
    if (data.success) {
      msg.innerHTML = '<span class="text-success">' + data.message + '</span>';
      setTimeout(function(){ location.reload(); }, 800);
    } else {
      msg.innerHTML = '<span class="text-danger">' + data.message + '</span>';
    }
  });
});
<?php endif; ?>
</script>
