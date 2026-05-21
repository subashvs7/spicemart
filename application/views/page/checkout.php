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

  <form method="post" action="<?php echo site_url('checkout'); ?>" id="checkoutForm">
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
          <h5 class="mb-3" style="font-family:'Playfair Display',serif">
            <i class="bi bi-credit-card me-2 text-saffron"></i>Payment Method
          </h5>

          <?php
          $rzp_on  = !empty($razorpay_key) && ($pay_settings['payment_razorpay_enabled'] ?? '1') == '1';
          $cod_on  = ($pay_settings['payment_cod_enabled']        ?? '1') == '1';
          $card_on = $rzp_on && ($pay_settings['payment_cards_enabled']      ?? '1') == '1';
          $nb_on   = $rzp_on && ($pay_settings['payment_netbanking_enabled'] ?? '1') == '1';
          $upi_on  = $rzp_on && ($pay_settings['payment_upi_enabled']        ?? '1') == '1';
          $wal_on  = $rzp_on && ($pay_settings['payment_wallets_enabled']    ?? '1') == '1';
          $apl_on  = $rzp_on && ($pay_settings['payment_applepay_enabled']   ?? '0') == '1';

          $online_chips = array();
          if ($card_on) $online_chips[] = array('icon'=>'💳','label'=>'Cards');
          if ($upi_on)  $online_chips[] = array('icon'=>'📱','label'=>'UPI / GPay');
          if ($nb_on)   $online_chips[] = array('icon'=>'🏦','label'=>'Net Banking');
          if ($wal_on)  $online_chips[] = array('icon'=>'👜','label'=>'Wallets');
          if ($apl_on)  $online_chips[] = array('icon'=>'🍎','label'=>'Apple Pay');

          $default = $cod_on ? 'cod' : ($rzp_on ? 'razorpay' : 'cod');
          ?>

          <div class="pm-checkout-grid">

            <?php if ($cod_on): ?>
            <label class="pm-checkout-label">
              <input type="radio" name="payment_method" value="cod"
                     class="d-none pm-radio" <?php echo $default==='cod'?'checked':''; ?>>
              <div class="pm-checkout-card <?php echo $default==='cod'?'pm-selected':''; ?>">
                <div class="pm-checkout-icon">💵</div>
                <div class="pm-checkout-title">Cash on Delivery</div>
                <div class="pm-checkout-desc">Pay when your order arrives</div>
              </div>
            </label>
            <?php endif; ?>

            <?php if ($rzp_on): ?>
            <label class="pm-checkout-label">
              <input type="radio" name="payment_method" value="razorpay"
                     class="d-none pm-radio" <?php echo $default==='razorpay'?'checked':''; ?>>
              <div class="pm-checkout-card <?php echo $default==='razorpay'?'pm-selected':''; ?>">
                <div class="pm-checkout-icon">💳</div>
                <div class="pm-checkout-title">Online Payment</div>
                <div class="pm-checkout-desc">Secure checkout via Razorpay</div>
                <?php if ($online_chips): ?>
                <div class="pm-chips-row">
                  <?php foreach ($online_chips as $ch): ?>
                    <span class="pm-chip"><?php echo $ch['icon']; ?> <?php echo $ch['label']; ?></span>
                  <?php endforeach; ?>
                </div>
                <?php endif; ?>
              </div>
            </label>
            <?php elseif (!$cod_on): ?>
            <div class="pm-checkout-card" style="opacity:.45;cursor:default">
              <div class="pm-checkout-icon">💳</div>
              <div class="pm-checkout-title">Online Payment</div>
              <div class="pm-checkout-desc">Not configured</div>
            </div>
            <?php endif; ?>

          </div>

          <!-- Hidden field populated by Razorpay JS on success -->
          <input type="hidden" name="rzp_payment_id" id="rzpPaymentId" value="">

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

          <!-- Fazaa / Isaad Discount -->
          <?php if (!empty($fazaa_programs)): ?>
          <div class="mb-3" id="fazaaSection">
            <?php if (!empty($fazaa_sess['program'])): ?>
              <!-- Already verified -->
              <div class="d-flex align-items-center justify-content-between p-2 rounded-3"
                   style="background:rgba(0,123,255,.07);border:1px solid rgba(0,123,255,.3)">
                <span class="small">
                  <i class="bi bi-patch-check-fill text-primary me-1"></i>
                  <strong><?php echo htmlspecialchars($fazaa_sess['label']); ?></strong>
                  (<?php echo (int)$fazaa_sess['discount_pct']; ?>% off) applied!
                </span>
                <a href="#" id="removeFazaaBtn" class="text-danger small">Remove</a>
              </div>
            <?php else: ?>
              <!-- Step 1: program select + member number -->
              <div id="fazaaStep1">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <?php foreach ($fazaa_programs as $fp): ?>
                    <div class="form-check form-check-inline mb-0">
                      <input class="form-check-input fazaa-prog-radio" type="radio"
                             name="_fazaa_prog" id="fp_<?php echo $fp['program']; ?>"
                             value="<?php echo $fp['program']; ?>"
                             data-label="<?php echo htmlspecialchars($fp['label']); ?>"
                             data-pct="<?php echo $fp['discount_pct']; ?>"
                             <?php echo $fp === reset($fazaa_programs) ? 'checked' : ''; ?>>
                      <label class="form-check-label small fw-600" for="fp_<?php echo $fp['program']; ?>">
                        <?php echo htmlspecialchars($fp['label']); ?>
                      </label>
                    </div>
                  <?php endforeach; ?>
                  <span class="text-muted small ms-auto">Gov. employee discount</span>
                </div>
                <div class="input-group input-group-sm">
                  <input type="text" class="form-control" id="fazaaMemberInput"
                         placeholder="Enter membership number">
                  <button class="btn btn-outline-primary" type="button" id="fazaaVerifyBtn">Verify</button>
                </div>
                <div id="fazaaMsg" class="small mt-1"></div>
              </div>
              <!-- Step 2: OTP (shown only when otp_required) -->
              <div id="fazaaStep2" style="display:none">
                <div class="input-group input-group-sm">
                  <input type="text" class="form-control" id="fazaaOtpInput"
                         placeholder="Enter 6-digit OTP" maxlength="6">
                  <button class="btn btn-outline-primary" type="button" id="fazaaOtpBtn">Confirm OTP</button>
                </div>
                <div id="fazaaOtpMsg" class="small mt-1"></div>
                <a href="#" class="small text-muted" id="fazaaBackBtn">← Change membership number</a>
              </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>

          <div class="summary-row"><span class="text-muted">Subtotal</span><span><?php echo $this->spice_model->rupees($subtotal); ?></span></div>
          <?php if ($discount > 0): ?>
          <div class="summary-row"><span class="text-muted">Coupon Discount</span><span class="text-success">-<?php echo $this->spice_model->rupees($discount); ?></span></div>
          <?php endif; ?>
          <?php if ($fazaa_discount > 0): ?>
          <div class="summary-row" id="fazaaDiscountRow">
            <span class="text-muted"><?php echo htmlspecialchars($fazaa_sess['label'] ?? 'Gov. Discount'); ?></span>
            <span class="text-primary">-<?php echo $this->spice_model->rupees($fazaa_discount); ?></span>
          </div>
          <?php else: ?>
          <div class="summary-row" id="fazaaDiscountRow" style="display:none">
            <span class="text-muted" id="fazaaDiscountLabel">Gov. Discount</span>
            <span class="text-primary" id="fazaaDiscountAmt"></span>
          </div>
          <?php endif; ?>
          <div class="summary-row">
            <span class="text-muted">Shipping</span>
            <?php if ($shipping === 0): ?>
              <span class="text-success fw-600">FREE</span>
            <?php else: ?>
              <span><?php echo $this->spice_model->rupees($shipping); ?></span>
            <?php endif; ?>
          </div>
          <div class="summary-row summary-total" id="grandTotalRow">
            <span>Grand Total</span>
            <span style="color:var(--saffron)" id="grandTotalAmt"><?php echo $this->spice_model->rupees($total); ?></span>
          </div>
          <button type="submit" class="btn btn-saffron w-100 btn-lg mt-4" id="placeOrderBtn">
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

<style>
.pm-checkout-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 14px;
}
.pm-checkout-label { cursor: pointer; margin: 0; }
.pm-checkout-card {
  border: 2px solid #e0e0e0;
  border-radius: 14px;
  padding: 20px 16px;
  text-align: center;
  transition: border-color .18s, background .18s, box-shadow .18s;
  user-select: none;
  height: 100%;
}
.pm-checkout-label:hover .pm-checkout-card { border-color: var(--saffron); }
.pm-checkout-card.pm-selected {
  border-color: var(--saffron);
  background: rgba(123,66,40,.05);
  box-shadow: 0 3px 14px rgba(123,66,40,.13);
}
.pm-checkout-icon  { font-size: 2.2rem; margin-bottom: 8px; }
.pm-checkout-title { font-weight: 700; font-size: .95rem; margin-bottom: 4px; }
.pm-checkout-desc  { font-size: .75rem; color: #999; line-height: 1.4; }
.pm-chips-row { display:flex; flex-wrap:wrap; justify-content:center; gap:4px; margin-top:8px; }
.pm-chip {
  background: #f0f0f0; border-radius: 20px;
  padding: 2px 8px; font-size: .68rem; color: #555;
  white-space: nowrap;
}
</style>

<?php
/* ── Razorpay JS config ──────────────────────────────────────── */
$rzp_disabled = array();
if (!$card_on) $rzp_disabled[] = 'card';
if (!$nb_on)   $rzp_disabled[] = 'netbanking';
if (!$upi_on)  $rzp_disabled[] = 'upi';
if (!$wal_on)  $rzp_disabled[] = 'wallet';
$site_name = $app_settings['site_name'] ?? 'SpiceMart';
?>
<script>
var RZP = {
  enabled:  <?php echo $rzp_on ? 'true' : 'false'; ?>,
  key:      '<?php echo addslashes($razorpay_key ?? ''); ?>',
  amount:   <?php echo (int)($total * 100); ?>,
  currency: 'INR',
  name:     '<?php echo addslashes($site_name); ?>',
  disabled: <?php echo json_encode($rzp_disabled); ?>,
  prefill:  { name: '<?php echo addslashes($user_name ?? ''); ?>' }
};
</script>

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
    this.closest('label').querySelector('.payment-option').style.background = 'rgba(123,66,40,.05)';
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

// Fazaa / Isaad verification
(function () {
  var subtotal    = <?php echo (float)$subtotal; ?>;
  var couponDisc  = <?php echo (float)$discount; ?>;
  var shippingAmt = <?php echo (float)$shipping; ?>;

  function getSelectedProg() {
    var el = document.querySelector('.fazaa-prog-radio:checked');
    return el ? { program: el.value, label: el.dataset.label, pct: parseFloat(el.dataset.pct) } : null;
  }

  function calcFazaaDisc(pct, maxDisc) {
    var base = subtotal - couponDisc;
    var raw  = Math.round((base * pct / 100) * 100) / 100;
    return Math.min(raw, maxDisc || 9999);
  }

  function showFazaaApplied(data) {
    var disc = calcFazaaDisc(data.discount_pct, data.max_discount);
    var row  = document.getElementById('fazaaDiscountRow');
    if (row) {
      row.style.display = '';
      var lbl = document.getElementById('fazaaDiscountLabel');
      var amt = document.getElementById('fazaaDiscountAmt');
      if (lbl) lbl.textContent = data.label + ' Discount';
      if (amt) amt.textContent = '-₹' + disc.toFixed(2);
    }
    var tot = document.getElementById('grandTotalAmt');
    if (tot) tot.textContent = '₹' + (subtotal - couponDisc - disc + shippingAmt).toFixed(2);
  }

  // Remove
  var removeBtn = document.getElementById('removeFazaaBtn');
  if (removeBtn) {
    removeBtn.addEventListener('click', function(e) {
      e.preventDefault();
      fetch('<?php echo site_url("ajax/fazaa-remove"); ?>', { method: 'POST' })
        .finally(function() { location.reload(); });
    });
  }

  var verifyBtn = document.getElementById('fazaaVerifyBtn');
  if (!verifyBtn) return;

  verifyBtn.addEventListener('click', function() {
    var prog = getSelectedProg();
    var mem  = (document.getElementById('fazaaMemberInput').value || '').trim();
    var msg  = document.getElementById('fazaaMsg');
    if (!prog) { msg.innerHTML = '<span class="text-danger">Select a program.</span>'; return; }
    if (!mem)  { msg.innerHTML = '<span class="text-danger">Enter membership number.</span>'; return; }
    verifyBtn.disabled = true;
    verifyBtn.textContent = 'Verifying…';
    msg.innerHTML = '';
    var fd = new FormData();
    fd.append('program', prog.program);
    fd.append('member_no', mem);
    fetch('<?php echo site_url("ajax/fazaa-verify"); ?>', { method: 'POST', body: fd })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (!data.success) {
          msg.innerHTML = '<span class="text-danger">' + data.message + '</span>';
          return;
        }
        if (data.otp_required) {
          document.getElementById('fazaaStep1').style.display = 'none';
          document.getElementById('fazaaStep2').style.display = '';
          document.getElementById('fazaaOtpMsg').innerHTML = '<span class="text-success">' + data.message + '</span>';
        } else {
          msg.innerHTML = '<span class="text-success">' + data.message + '</span>';
          showFazaaApplied(data);
          setTimeout(function(){ location.reload(); }, 1000);
        }
      })
      .catch(function() { msg.innerHTML = '<span class="text-danger">Server error. Try again.</span>'; })
      .finally(function() { verifyBtn.disabled = false; verifyBtn.textContent = 'Verify'; });
  });

  var otpBtn = document.getElementById('fazaaOtpBtn');
  if (otpBtn) {
    otpBtn.addEventListener('click', function() {
      var otp = (document.getElementById('fazaaOtpInput').value || '').trim();
      var msg = document.getElementById('fazaaOtpMsg');
      if (!otp) { msg.innerHTML = '<span class="text-danger">Enter OTP.</span>'; return; }
      otpBtn.disabled = true; otpBtn.textContent = 'Confirming…';
      var fd = new FormData(); fd.append('otp', otp);
      fetch('<?php echo site_url("ajax/fazaa-otp-confirm"); ?>', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
          if (!data.success) {
            msg.innerHTML = '<span class="text-danger">' + data.message + '</span>';
            return;
          }
          msg.innerHTML = '<span class="text-success">' + data.message + '</span>';
          showFazaaApplied(data);
          setTimeout(function(){ location.reload(); }, 1000);
        })
        .catch(function() { msg.innerHTML = '<span class="text-danger">Server error. Try again.</span>'; })
        .finally(function() { otpBtn.disabled = false; otpBtn.textContent = 'Confirm OTP'; });
    });
  }

  var backBtn = document.getElementById('fazaaBackBtn');
  if (backBtn) {
    backBtn.addEventListener('click', function(e) {
      e.preventDefault();
      document.getElementById('fazaaStep2').style.display = 'none';
      document.getElementById('fazaaStep1').style.display = '';
    });
  }
})();
</script>
