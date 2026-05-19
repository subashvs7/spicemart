<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
/* Pre-compute effective price per item */
$cart_rows = array();
foreach ($cart_items as $item) {
    $eff = (!empty($item['offer_price']) && (float)$item['offer_price'] > 0)
           ? (float)$item['offer_price']
           : (float)$item['price'];
    $saving_pct = ($eff < (float)$item['price'])
                  ? round(((float)$item['price'] - $eff) / (float)$item['price'] * 100)
                  : 0;
    $item['eff_price']   = $eff;
    $item['saving_pct']  = $saving_pct;
    $item['row_total']   = $eff * $item['quantity'];
    $cart_rows[]         = $item;
}
$item_count   = count($cart_rows);
$free_thresh  = (float)$shipping_free;
$prog_pct     = $free_thresh > 0 ? min(100, round($subtotal / $free_thresh * 100)) : 100;
$remaining    = max(0, $free_thresh - $subtotal);
?>

<style>
/* ── Cart page overrides ──────────────────────────────── */
.cart-wrap       { max-width:1200px; margin:0 auto; }
.cart-item-card  {
  display:flex; align-items:flex-start; gap:16px;
  padding:18px 20px; border-bottom:1px solid #f0ede9;
  transition:background .2s;
  position:relative;
}
.cart-item-card:last-child { border-bottom:none; }
.cart-item-card:hover      { background:#fffaf6; }
.cart-item-card.removing   { opacity:0; transform:translateX(30px); transition:all .35s ease; }

.cart-img-wrap { flex-shrink:0; }
.cart-img-wrap img {
  width:88px; height:88px; object-fit:cover;
  border-radius:12px; border:1px solid #f0ede9;
}

.cart-item-info  { flex:1; min-width:0; }
.cart-item-name  {
  font-weight:600; font-size:.97rem; color:#2C1810;
  display:block; margin-bottom:4px; text-decoration:none;
}
.cart-item-name:hover { color:var(--saffron); }

/* Variant chips */
.variant-chip {
  display:inline-flex; align-items:center; gap:5px;
  background:#f5f0eb; border:1px solid #e8ddd4;
  border-radius:20px; padding:2px 10px 2px 6px;
  font-size:.75rem; font-weight:500; color:#5c4033;
  margin-right:4px; margin-bottom:4px;
}
.variant-chip .swatch {
  width:14px; height:14px; border-radius:50%;
  border:1px solid rgba(0,0,0,.2); flex-shrink:0;
  display:inline-block;
}

/* Savings badge */
.saving-badge {
  display:inline-block; background:#e8f5e9; color:#2e7d32;
  border-radius:4px; padding:1px 7px; font-size:.72rem; font-weight:600;
  border:1px solid #c8e6c9;
}

/* Qty control */
.qty-ctrl {
  display:inline-flex; align-items:center;
  border:1.5px solid #e0d8d0; border-radius:8px; overflow:hidden;
  background:#fff; height:36px;
}
.qty-ctrl button {
  width:32px; height:100%; border:none; background:transparent;
  font-size:1.1rem; color:#5c4033; cursor:pointer; transition:background .15s;
  display:flex; align-items:center; justify-content:center;
}
.qty-ctrl button:hover { background:#fff3ee; color:var(--saffron); }
.qty-ctrl button:disabled { opacity:.4; cursor:default; }
.qty-ctrl input {
  width:42px; height:100%; border:none; border-left:1.5px solid #e0d8d0;
  border-right:1.5px solid #e0d8d0; text-align:center;
  font-size:.9rem; font-weight:600; color:#2C1810;
  -moz-appearance:textfield; background:transparent;
}
.qty-ctrl input::-webkit-outer-spin-button,
.qty-ctrl input::-webkit-inner-spin-button { -webkit-appearance:none; }

/* Price columns */
.cart-price-col  { min-width:80px; text-align:right; flex-shrink:0; }
.cart-total-col  { min-width:88px; text-align:right; flex-shrink:0; }
.cart-action-col { flex-shrink:0; }

.unit-price-main { font-weight:600; font-size:.95rem; color:#2C1810; }
.unit-price-orig { font-size:.78rem; color:#aaa; text-decoration:line-through; }
.row-total-val   { font-weight:700; font-size:1.05rem; color:var(--saffron); }

/* Remove button */
.btn-remove {
  width:30px; height:30px; border-radius:50%; border:1.5px solid #f0ede9;
  background:#fff; color:#bbb; cursor:pointer; display:flex;
  align-items:center; justify-content:center; font-size:.85rem;
  transition:all .2s; padding:0;
}
.btn-remove:hover { background:#fff0f0; border-color:#ffcccc; color:#c0392b; }

/* Free shipping bar */
.shipping-bar-wrap { background:#fff8f2; border-radius:10px; padding:12px 16px; }
.shipping-bar-track {
  height:6px; background:#f0ede9; border-radius:10px; overflow:hidden; margin:6px 0;
}
.shipping-bar-fill  {
  height:100%; border-radius:10px;
  background:linear-gradient(90deg,var(--saffron),#e55a20);
  transition:width .5s ease;
}

/* Coupon */
.coupon-wrap { border-top:1px dashed #e0d8d0; padding-top:14px; margin-top:14px; }
.coupon-applied {
  background:#f0fff4; border:1.5px solid #86efac; border-radius:8px;
  padding:8px 12px; display:flex; align-items:center; gap:8px;
  font-size:.88rem;
}
.coupon-applied .code { font-weight:700; color:#166534; font-size:.92rem; }

/* Summary card */
.summary-card { background:#fff; border-radius:16px; padding:24px; box-shadow:0 4px 24px rgba(44,24,16,.09); position:sticky; top:90px; }
.summary-line { display:flex; justify-content:space-between; align-items:center; font-size:.92rem; margin-bottom:8px; }
.summary-divider { border:none; border-top:1.5px dashed #f0ede9; margin:14px 0; }
.summary-total  { display:flex; justify-content:space-between; align-items:center; }
.trust-strip { display:flex; flex-direction:column; gap:6px; margin-top:16px; }
.trust-item { display:flex; align-items:center; gap:8px; font-size:.8rem; color:#888; }

/* Stock warning */
.stock-warn { font-size:.75rem; color:#c0392b; font-weight:500; display:flex; align-items:center; gap:3px; }

/* Column headers (desktop) */
.cart-header {
  display:none;
  padding:10px 20px 10px;
  border-bottom:2px solid #f0ede9;
  font-size:.75rem; font-weight:600; color:#999;
  text-transform:uppercase; letter-spacing:.6px;
}
@media (min-width:768px) {
  .cart-header { display:flex; align-items:center; gap:16px; }
  .cart-header .ch-product { flex:1; }
  .cart-header .ch-price, .cart-header .ch-qty, .cart-header .ch-total { min-width:88px; text-align:right; }
  .cart-header .ch-action  { width:40px; }
}
@media (max-width:575px) {
  .cart-item-card { gap:10px; padding:14px 14px; }
  .cart-img-wrap img { width:68px; height:68px; }
  .cart-price-col, .cart-total-col { min-width:64px; }
  .qty-ctrl input { width:32px; }
}
</style>

<div class="container py-4 cart-wrap">

  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?php echo site_url('home'); ?>">Home</a></li>
      <li class="breadcrumb-item active">Cart</li>
    </ol>
  </nav>

  <h2 class="mb-4" style="font-family:'Playfair Display',serif">
    🛒 My Cart
    <?php if ($item_count > 0): ?>
      <span class="fs-6 text-muted fw-normal ms-2">
        (<?php echo $item_count; ?> item<?php echo $item_count !== 1 ? 's' : ''; ?>)
      </span>
    <?php endif; ?>
  </h2>

  <?php if (empty($cart_rows)): ?>
  <!-- ── EMPTY STATE ── -->
  <div class="text-center py-5">
    <div style="font-size:5rem;opacity:.3">🛒</div>
    <h4 class="mt-3 fw-600">Your cart is empty</h4>
    <p class="text-muted">Looks like you haven't added any spices yet.</p>
    <a href="<?php echo site_url('shop'); ?>" class="btn btn-saffron mt-2 px-4">
      <i class="bi bi-shop me-2"></i>Browse Products
    </a>
  </div>

  <?php else: ?>

  <?php if (!$is_logged_in): ?>
    <div class="alert alert-info border-0 mb-4">
      <i class="bi bi-info-circle me-2"></i>
      Please <a href="<?php echo site_url('login'); ?>" class="fw-600">login</a>
      to manage your cart and checkout.
    </div>
  <?php endif; ?>

  <div class="row g-4 align-items-start">

    <!-- ══════════════ LEFT: CART ITEMS ══════════════ -->
    <div class="col-lg-8">
      <div class="bg-white rounded-xl shadow-soft overflow-hidden"
           id="cartItemsBox"
           data-free-shipping="<?php echo $free_thresh; ?>"
           data-shipping-charge="<?php echo $shipping_charge_raw; ?>"
           data-discount="<?php echo $discount; ?>">

        <!-- Column headers (desktop) -->
        <div class="cart-header">
          <div class="ch-product">Product</div>
          <div class="ch-price">Price</div>
          <div class="ch-qty text-center" style="min-width:120px">Quantity</div>
          <div class="ch-total">Total</div>
          <div class="ch-action"></div>
        </div>

        <!-- Cart rows -->
        <?php foreach ($cart_rows as $item):
          $eff         = $item['eff_price'];
          $orig        = (float)$item['price'];
          $save_pct    = $item['saving_pct'];
          $row_total   = $item['row_total'];
          $isColor     = (!empty($item['variant_type']) && $item['variant_type'] === 'color');
          $hasHex      = ($isColor && !empty($item['color_hex']));
          $variantParts = array();
          if (!empty($item['variant_label'])) {
            $variantParts = array_map('trim', explode('|', $item['variant_label']));
          }
        ?>
        <div class="cart-item-card"
             id="cart-row-<?php echo $item['cart_id']; ?>"
             data-cart-id="<?php echo $item['cart_id']; ?>"
             data-price="<?php echo $eff; ?>"
             data-stock="<?php echo (int)$item['stock_qty']; ?>">

          <!-- Image -->
          <div class="cart-img-wrap">
            <a href="<?php echo site_url('product/'.$item['product_id']); ?>">
              <img src="<?php echo $this->spice_model->product_image($item['image']); ?>"
                   alt="<?php echo htmlspecialchars($item['name']); ?>">
            </a>
          </div>

          <!-- Info -->
          <div class="cart-item-info">
            <a href="<?php echo site_url('product/'.$item['product_id']); ?>"
               class="cart-item-name">
              <?php echo htmlspecialchars($item['name']); ?>
            </a>

            <!-- Variant chips -->
            <?php if (!empty($variantParts)): ?>
            <div class="mb-1">
              <?php foreach ($variantParts as $part):
                $isThisColor = (stripos($part, 'color') !== false);
                $chipHex     = ($isThisColor && !empty($item['color_hex'])) ? $item['color_hex'] : '';
              ?>
                <span class="variant-chip">
                  <?php if ($chipHex): ?>
                    <span class="swatch" style="background:<?php echo htmlspecialchars($chipHex); ?>"></span>
                  <?php else: ?>
                    <i class="bi bi-tag" style="font-size:.7rem"></i>
                  <?php endif; ?>
                  <?php echo htmlspecialchars($part); ?>
                </span>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Savings badge -->
            <?php if ($save_pct > 0): ?>
              <span class="saving-badge mb-1">
                <?php echo $save_pct; ?>% OFF
              </span>
            <?php endif; ?>

            <!-- Stock warning -->
            <?php if ((int)$item['stock_qty'] < (int)$item['quantity']): ?>
              <div class="stock-warn mt-1">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Only <?php echo (int)$item['stock_qty']; ?> left in stock
              </div>
            <?php elseif ((int)$item['stock_qty'] <= 5): ?>
              <div class="stock-warn mt-1" style="color:#e67e22">
                <i class="bi bi-exclamation-circle"></i>
                Only <?php echo (int)$item['stock_qty']; ?> left
              </div>
            <?php endif; ?>

            <!-- Mobile: price + qty + total in one row -->
            <div class="d-flex align-items-center gap-3 mt-2 d-md-none flex-wrap">
              <div>
                <div class="unit-price-main">
                  <?php echo $this->spice_model->rupees($eff); ?>
                </div>
                <?php if ($save_pct > 0): ?>
                  <div class="unit-price-orig"><?php echo $this->spice_model->rupees($orig); ?></div>
                <?php endif; ?>
              </div>
              <div class="qty-ctrl">
                <button type="button" data-qty-change="-1"
                        data-cart-id="<?php echo $item['cart_id']; ?>"
                        <?php if ((int)$item['quantity'] <= 1) echo 'disabled'; ?>>−</button>
                <input type="number" class="qty-input"
                       value="<?php echo (int)$item['quantity']; ?>"
                       min="1" max="<?php echo (int)$item['stock_qty']; ?>"
                       data-cart-id="<?php echo $item['cart_id']; ?>">
                <button type="button" data-qty-change="1"
                        data-cart-id="<?php echo $item['cart_id']; ?>"
                        <?php if ((int)$item['quantity'] >= (int)$item['stock_qty']) echo 'disabled'; ?>>+</button>
              </div>
              <div class="row-total-val"
                   id="row-total-<?php echo $item['cart_id']; ?>">
                <?php echo $this->spice_model->rupees($row_total); ?>
              </div>
            </div>
          </div>

          <!-- Desktop: Price -->
          <div class="cart-price-col d-none d-md-block">
            <div class="unit-price-main">
              <?php echo $this->spice_model->rupees($eff); ?>
            </div>
            <?php if ($save_pct > 0): ?>
              <div class="unit-price-orig"><?php echo $this->spice_model->rupees($orig); ?></div>
            <?php endif; ?>
          </div>

          <!-- Desktop: Qty -->
          <div class="d-none d-md-flex align-items-center justify-content-end"
               style="min-width:120px">
            <div class="qty-ctrl">
              <button type="button" data-qty-change="-1"
                      data-cart-id="<?php echo $item['cart_id']; ?>"
                      <?php if ((int)$item['quantity'] <= 1) echo 'disabled'; ?>>−</button>
              <input type="number" class="qty-input"
                     value="<?php echo (int)$item['quantity']; ?>"
                     min="1" max="<?php echo (int)$item['stock_qty']; ?>"
                     data-cart-id="<?php echo $item['cart_id']; ?>">
              <button type="button" data-qty-change="1"
                      data-cart-id="<?php echo $item['cart_id']; ?>"
                      <?php if ((int)$item['quantity'] >= (int)$item['stock_qty']) echo 'disabled'; ?>>+</button>
            </div>
          </div>

          <!-- Desktop: Row total -->
          <div class="cart-total-col d-none d-md-block">
            <span class="row-total-val"
                  id="row-total-<?php echo $item['cart_id']; ?>">
              <?php echo $this->spice_model->rupees($row_total); ?>
            </span>
          </div>

          <!-- Remove button -->
          <div class="cart-action-col">
            <button class="btn-remove" data-remove-cart="<?php echo $item['cart_id']; ?>"
                    title="Remove item">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

        </div>
        <?php endforeach; ?>

        <!-- Continue shopping link -->
        <div class="px-4 py-3 border-top" style="background:#fafaf8">
          <a href="<?php echo site_url('shop'); ?>"
             class="text-muted small text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Continue Shopping
          </a>
        </div>
      </div><!-- /cart items box -->
    </div>

    <!-- ══════════════ RIGHT: ORDER SUMMARY ══════════════ -->
    <div class="col-lg-4">
      <div class="summary-card">
        <h5 class="mb-4 fw-700" style="font-family:'Playfair Display',serif">
          Order Summary
        </h5>

        <!-- Free shipping progress -->
        <div class="shipping-bar-wrap mb-4" id="freeShipWrap">
          <?php if ($shipping === 0): ?>
            <div class="text-success fw-600 small mb-1">
              🎉 You've unlocked free shipping!
            </div>
            <div class="shipping-bar-track">
              <div class="shipping-bar-fill" id="shippingBarFill" style="width:100%"></div>
            </div>
          <?php else: ?>
            <div class="small mb-1" id="freeShipMsg">
              Add <strong id="freeShipRemain"><?php echo $this->spice_model->rupees($remaining); ?></strong>
              more for <span class="text-success fw-600">FREE shipping!</span>
            </div>
            <div class="shipping-bar-track">
              <div class="shipping-bar-fill" id="shippingBarFill"
                   style="width:<?php echo $prog_pct; ?>%"></div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Coupon section -->
        <div class="coupon-wrap mb-3">
          <?php if (!empty($coupon) && !empty($coupon['code'])): ?>
            <!-- Coupon applied -->
            <div class="coupon-applied" id="couponApplied">
              <i class="bi bi-tag-fill text-success"></i>
              <div class="flex-grow-1">
                <span class="code"><?php echo htmlspecialchars($coupon['code']); ?></span>
                <span class="text-muted ms-1 small">applied</span>
                <div class="text-success small">
                  You saved <?php echo $this->spice_model->rupees($discount); ?>!
                </div>
              </div>
              <button class="btn btn-sm btn-outline-danger border-0 px-2 py-0"
                      id="removeCouponBtn" style="font-size:.8rem">
                <i class="bi bi-x-circle"></i> Remove
              </button>
            </div>
            <div id="couponInputWrap" style="display:none">
          <?php else: ?>
            <div id="couponApplied" style="display:none" class="coupon-applied"></div>
            <div id="couponInputWrap">
          <?php endif; ?>
              <div class="input-group input-group-sm">
                <input type="text" class="form-control" id="couponInput"
                       placeholder="Have a coupon code?"
                       style="border-radius:8px 0 0 8px;font-size:.88rem">
                <button class="btn btn-outline-saffron" id="applyCouponBtn"
                        style="border-radius:0 8px 8px 0;font-size:.88rem">
                  Apply
                </button>
              </div>
              <div id="couponMsg" class="small mt-1"></div>
            </div>
        </div>

        <!-- Line items -->
        <div class="summary-line">
          <span class="text-muted">
            Subtotal
            <span class="text-dark fw-500">(<?php echo $item_count; ?> item<?php echo $item_count!==1?'s':''; ?>)</span>
          </span>
          <span id="summarySubtotal">
            <?php echo $this->spice_model->rupees($subtotal); ?>
          </span>
        </div>

        <?php if ($discount > 0): ?>
        <div class="summary-line text-success">
          <span><i class="bi bi-tag me-1"></i>Coupon Discount</span>
          <span id="summaryDiscount">−<?php echo $this->spice_model->rupees($discount); ?></span>
        </div>
        <?php else: ?>
        <div class="summary-line text-success" id="discountLine" style="display:none">
          <span><i class="bi bi-tag me-1"></i>Coupon Discount</span>
          <span id="summaryDiscount">−₹0.00</span>
        </div>
        <?php endif; ?>

        <div class="summary-line">
          <span class="text-muted">Shipping</span>
          <?php if ($shipping === 0): ?>
            <span class="text-success fw-600" id="summaryShipping">FREE 🎉</span>
          <?php else: ?>
            <span id="summaryShipping"><?php echo $this->spice_model->rupees($shipping); ?></span>
          <?php endif; ?>
        </div>

        <hr class="summary-divider">

        <div class="summary-total mb-4">
          <span class="fw-700 fs-5">Grand Total</span>
          <span class="fw-700 fs-4" style="color:var(--saffron)" id="summaryTotal">
            <?php echo $this->spice_model->rupees($grand_total); ?>
          </span>
        </div>

        <?php if ($discount > 0): ?>
          <div class="alert alert-success py-2 px-3 mb-3 small border-0"
               style="background:#f0fff4;border-radius:8px">
            <i class="bi bi-piggy-bank me-1"></i>
            You're saving <strong><?php echo $this->spice_model->rupees($discount); ?></strong> on this order!
          </div>
        <?php endif; ?>

        <?php if ($is_logged_in): ?>
          <a href="<?php echo site_url('checkout'); ?>"
             class="btn btn-saffron w-100 py-3 fw-600 fs-6 rounded-xl">
            Proceed to Checkout &nbsp;<i class="bi bi-arrow-right"></i>
          </a>
        <?php else: ?>
          <a href="<?php echo site_url('login'); ?>"
             class="btn btn-saffron w-100 py-3 fw-600 fs-6 rounded-xl">
            Login to Checkout &nbsp;<i class="bi bi-arrow-right"></i>
          </a>
        <?php endif; ?>

        <!-- Payment methods -->
        <div class="text-center mt-3 mb-2">
          <small class="text-muted d-block mb-2">We accept</small>
          <div class="d-flex justify-content-center gap-2 flex-wrap">
            <span class="badge bg-light text-dark border px-3 py-2" style="font-size:.78rem">
              💵 Cash on Delivery
            </span>
            <span class="badge bg-light text-dark border px-3 py-2" style="font-size:.78rem">
              💳 Online Payment
            </span>
          </div>
        </div>

        <!-- Trust strip -->
        <div class="trust-strip pt-3 border-top mt-3">
          <div class="trust-item">
            <i class="bi bi-shield-lock text-success fs-5"></i>
            <span>100% Secure Checkout</span>
          </div>
          <div class="trust-item">
            <i class="bi bi-arrow-return-left text-saffron fs-5" style="color:var(--saffron)!important"></i>
            <span>7-Day Easy Returns</span>
          </div>
          <div class="trust-item">
            <i class="bi bi-truck text-primary fs-5"></i>
            <span>Fast Delivery Across India</span>
          </div>
        </div>

      </div><!-- /summary-card -->
    </div>

  </div><!-- /row -->
  <?php endif; ?>
</div>

<!-- ── Remove Item Confirmation Modal ─────────────────────── -->
<div class="modal fade" id="removeCartModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:380px">
    <div class="modal-content border-0 shadow-lg" style="border-radius:18px;overflow:hidden">

      <div class="modal-body text-center p-4 pb-2">
        <div style="font-size:3.2rem;line-height:1;margin-bottom:12px">🗑️</div>
        <h5 class="fw-700 mb-1" style="font-family:'Playfair Display',serif">
          Remove Item?
        </h5>
        <p class="text-muted mb-1" style="font-size:.92rem">
          Are you sure you want to remove
        </p>
        <p class="fw-600 text-dark mb-3" id="removeModalItemName" style="font-size:.95rem">
          this item
        </p>
        <p class="text-muted small">This item will be removed from your cart.</p>
      </div>

      <div class="modal-footer border-0 justify-content-center gap-2 px-4 pb-4">
        <button type="button" class="btn btn-light px-4 rounded-pill fw-500"
                data-bs-dismiss="modal" style="min-width:110px">
          <i class="bi bi-arrow-left me-1"></i> Keep It
        </button>
        <button type="button" class="btn btn-danger px-4 rounded-pill fw-600"
                id="confirmRemoveBtn" style="min-width:110px">
          <i class="bi bi-trash me-1"></i> Remove
        </button>
      </div>

    </div>
  </div>
</div>

<?php
/* Pass server-side values to cart.inc JS */
$ci_discount     = $discount;
$ci_subtotal     = $subtotal;
$ci_shipping     = $shipping;
$ci_grand        = $grand_total;
$ci_free_thresh  = $free_thresh;
$ci_ship_charge  = $shipping_charge_raw;
$ci_coupon_code  = !empty($coupon['code']) ? $coupon['code'] : '';
$ci_apply_url    = site_url('cart/apply-coupon');
$ci_remove_url   = site_url('cart/remove-coupon');
$ci_cart_ajax    = site_url('cart-ajax');
?>
<script>
var CART_DATA = {
  subtotal      : <?php echo (float)$ci_subtotal; ?>,
  discount      : <?php echo (float)$ci_discount; ?>,
  shipping      : <?php echo (float)$ci_shipping; ?>,
  grandTotal    : <?php echo (float)$ci_grand; ?>,
  freeThresh    : <?php echo (float)$ci_free_thresh; ?>,
  shipCharge    : <?php echo (float)$ci_ship_charge; ?>,
  couponCode    : '<?php echo addslashes($ci_coupon_code); ?>',
  applyCouponUrl: '<?php echo $ci_apply_url; ?>',
  removeCouponUrl:'<?php echo $ci_remove_url; ?>',
  cartAjaxUrl   : '<?php echo $ci_cart_ajax; ?>'
};
</script>
