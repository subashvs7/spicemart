<script>
(function () {
  'use strict';

  /* Tell main.js to skip its [data-qty-change] and .qty-input handlers — we own those here */
  window._cartIncActive = true;

  /* ── Override main.js updateCartItemAjax so it never calls location.reload() ── */
  window.updateCartItemAjax = function (cartId, qty) {
    fetch(CART_DATA.cartAjaxUrl, {
      method : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body   : JSON.stringify({ action: 'update', cart_id: cartId, qty: qty })
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.success) {
        updateCartBadge(data.cart_count);
        recalcTotals();
      }
    });
  };

  /* ════════════════════════════════════════════════════════════
     QTY CONTROLS
     Each cart row has TWO qty inputs (mobile + desktop).
     We read from the clicked button's own .qty-ctrl sibling
     and write to ALL inputs that share the same data-cart-id.
  ════════════════════════════════════════════════════════════ */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-qty-change]');
    if (!btn) return;

    var cartId = btn.dataset.cartId;
    var delta  = parseInt(btn.dataset.qtyChange, 10);
    var row    = document.getElementById('cart-row-' + cartId);
    if (!row) return;

    /* Read from the button's own qty-ctrl so we always get the right current value */
    var ctrl      = btn.closest('.qty-ctrl');
    var ctrlInput = ctrl ? ctrl.querySelector('.qty-input') : row.querySelector('.qty-input');
    if (!ctrlInput) return;

    var stock  = parseInt(row.dataset.stock, 10) || 99;
    var newQty = Math.max(1, Math.min(stock, parseInt(ctrlInput.value, 10) + delta));

    /* Sync both mobile and desktop inputs */
    syncAllInputs(cartId, newQty);
    syncQtyButtons(row, newQty, stock);
    updateRowTotal(row, newQty);
    recalcTotals();
    updateCartItemAjax(cartId, newQty);
  });

  document.addEventListener('change', function (e) {
    if (!e.target.classList.contains('qty-input')) return;
    var cartId = e.target.dataset.cartId;
    var row    = document.getElementById('cart-row-' + cartId);
    if (!row) return;
    var stock  = parseInt(row.dataset.stock, 10) || 99;
    var newQty = Math.max(1, Math.min(stock, parseInt(e.target.value, 10) || 1));

    /* Sync both mobile and desktop inputs */
    syncAllInputs(cartId, newQty);
    syncQtyButtons(row, newQty, stock);
    updateRowTotal(row, newQty);
    recalcTotals();
    updateCartItemAjax(cartId, newQty);
  });

  /* Update every qty input that belongs to this cart item */
  function syncAllInputs(cartId, qty) {
    document.querySelectorAll('.qty-input[data-cart-id="' + cartId + '"]').forEach(function (inp) {
      inp.value = qty;
    });
  }

  /* Disable/enable +/- buttons in EVERY qty-ctrl within the row */
  function syncQtyButtons(row, qty, stock) {
    row.querySelectorAll('[data-qty-change="-1"]').forEach(function (b) { b.disabled = (qty <= 1); });
    row.querySelectorAll('[data-qty-change="1"]').forEach(function (b)  { b.disabled = (qty >= stock); });
  }

  /* ════════════════════════════════════════════════════════════
     REMOVE ITEM — confirmation modal then remove on confirm
  ════════════════════════════════════════════════════════════ */
  var pendingRemoveId = null;
  var removeModal     = new bootstrap.Modal(document.getElementById('removeCartModal'));

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-remove-cart]');
    if (!btn) return;

    pendingRemoveId = btn.dataset.removeCart;

    var row      = document.getElementById('cart-row-' + pendingRemoveId);
    var nameEl   = row ? row.querySelector('.cart-item-name') : null;
    var nameText = nameEl ? nameEl.textContent.trim() : 'this item';
    var modalName = document.getElementById('removeModalItemName');
    if (modalName) modalName.textContent = '"' + nameText + '"';

    removeModal.show();
  });

  document.getElementById('confirmRemoveBtn').addEventListener('click', function () {
    if (!pendingRemoveId) return;
    removeModal.hide();

    var cartId = pendingRemoveId;
    pendingRemoveId = null;

    fetch(CART_DATA.cartAjaxUrl, {
      method : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body   : JSON.stringify({ action: 'remove', cart_id: cartId })
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (!data.success) return;

      var row = document.getElementById('cart-row-' + cartId);
      if (row) {
        row.classList.add('removing');
        setTimeout(function () {
          row.remove();
          updateCartBadge(data.cart_count);
          recalcTotals();
          showToast('Item removed from cart.', 'info');
          if (data.cart_count === 0) {
            setTimeout(function () { location.reload(); }, 600);
          }
        }, 350);
      }
    });
  });

  /* ════════════════════════════════════════════════════════════
     LIVE TOTALS RECALCULATION
  ════════════════════════════════════════════════════════════ */
  function updateRowTotal(row, qty) {
    var price    = parseFloat(row.dataset.price) || 0;
    var rowTotal = price * qty;
    var cartId   = row.dataset.cartId;
    /* Update mobile span (id="row-total-X") and desktop span (id="row-total-X-desktop") */
    var mobileEl  = document.getElementById('row-total-' + cartId);
    var desktopEl = document.getElementById('row-total-' + cartId + '-desktop');
    if (mobileEl)  mobileEl.textContent  = formatRupee(rowTotal);
    if (desktopEl) desktopEl.textContent = formatRupee(rowTotal);
  }

  function recalcTotals() {
    var subtotal = 0;
    document.querySelectorAll('.cart-item-card').forEach(function (row) {
      var price = parseFloat(row.dataset.price) || 0;
      /* Use first input value as the qty source (both are synced at this point) */
      var inp = row.querySelector('.qty-input');
      var qty = inp ? (parseInt(inp.value, 10) || 1) : 1;
      subtotal += price * qty;
    });

    var discount  = CART_DATA.discount;
    var afterDis  = subtotal - discount;
    var shipping  = (afterDis >= CART_DATA.freeThresh) ? 0 : CART_DATA.shipCharge;
    var grand     = afterDis + shipping;

    setEl('summarySubtotal', formatRupee(subtotal));

    var shipEl = document.getElementById('summaryShipping');
    if (shipEl) {
      shipEl.textContent = shipping === 0 ? 'FREE 🎉' : formatRupee(shipping);
      shipEl.className   = shipping === 0 ? 'text-success fw-600' : '';
    }

    setEl('summaryTotal', formatRupee(grand));

    /* Free shipping progress bar */
    var pct     = CART_DATA.freeThresh > 0 ? Math.min(100, Math.round(subtotal / CART_DATA.freeThresh * 100)) : 100;
    var fillEl  = document.getElementById('shippingBarFill');
    var msgEl   = document.getElementById('freeShipMsg');
    var remainEl = document.getElementById('freeShipRemain');
    if (fillEl) fillEl.style.width = pct + '%';
    if (shipping === 0) {
      if (msgEl) msgEl.innerHTML = '<span class="text-success fw-600">🎉 You\'ve unlocked free shipping!</span>';
    } else {
      var remain = Math.max(0, CART_DATA.freeThresh - subtotal);
      if (remainEl) remainEl.textContent = formatRupee(remain);
    }

    CART_DATA.subtotal   = subtotal;
    CART_DATA.shipping   = shipping;
    CART_DATA.grandTotal = grand;
  }

  /* ════════════════════════════════════════════════════════════
     COUPON — apply
  ════════════════════════════════════════════════════════════ */
  var applyBtn = document.getElementById('applyCouponBtn');
  if (applyBtn) {
    applyBtn.addEventListener('click', function () {
      var code = (document.getElementById('couponInput').value || '').trim().toUpperCase();
      if (!code) { setCouponMsg('Enter a coupon code.', 'danger'); return; }

      applyBtn.disabled = true;
      applyBtn.textContent = '…';

      fetch(CART_DATA.applyCouponUrl, {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ code: code, subtotal: CART_DATA.subtotal })
      })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) {
          CART_DATA.discount = data.discount;

          var discLine = document.getElementById('discountLine');
          var discVal  = document.getElementById('summaryDiscount');
          if (discLine) discLine.style.display = '';
          if (discVal)  discVal.textContent = '−' + formatRupee(data.discount);

          var appliedEl = document.getElementById('couponApplied');
          var wrapEl    = document.getElementById('couponInputWrap');
          if (appliedEl) {
            appliedEl.style.display = '';
            appliedEl.innerHTML =
              '<i class="bi bi-tag-fill text-success"></i>' +
              '<div class="flex-grow-1">' +
                '<span class="code">' + escHtml(code) + '</span>' +
                '<span class="text-muted ms-1 small">applied</span>' +
                '<div class="text-success small">You saved ' + formatRupee(data.discount) + '!</div>' +
              '</div>' +
              '<button class="btn btn-sm btn-outline-danger border-0 px-2 py-0" id="removeCouponBtn" style="font-size:.8rem">' +
                '<i class="bi bi-x-circle"></i> Remove' +
              '</button>';
            bindRemoveCoupon();
          }
          if (wrapEl) wrapEl.style.display = 'none';

          recalcTotals();
          showToast(data.message || 'Coupon applied!', 'success');
        } else {
          setCouponMsg(data.message || 'Invalid coupon.', 'danger');
        }
      })
      .catch(function () { setCouponMsg('Request failed. Try again.', 'danger'); })
      .finally(function () { applyBtn.disabled = false; applyBtn.textContent = 'Apply'; });
    });
  }

  var couponInput = document.getElementById('couponInput');
  if (couponInput) {
    couponInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); if (applyBtn) applyBtn.click(); }
    });
  }

  function bindRemoveCoupon() {
    var btn = document.getElementById('removeCouponBtn');
    if (!btn) return;
    btn.addEventListener('click', function () {
      fetch(CART_DATA.removeCouponUrl, { method: 'POST' })
        .then(function (r) { return r.json(); })
        .then(function () {
          CART_DATA.discount = 0;

          var discLine = document.getElementById('discountLine');
          if (discLine) discLine.style.display = 'none';

          var appliedEl = document.getElementById('couponApplied');
          var wrapEl    = document.getElementById('couponInputWrap');
          if (appliedEl) appliedEl.style.display = 'none';
          if (wrapEl)    wrapEl.style.display = '';

          var ci = document.getElementById('couponInput');
          if (ci) ci.value = '';
          setCouponMsg('', '');

          recalcTotals();
          showToast('Coupon removed.', 'info');
        });
    });
  }
  bindRemoveCoupon();

  /* ════════════════════════════════════════════════════════════
     HELPERS
  ════════════════════════════════════════════════════════════ */
  function formatRupee(n) {
    n = parseFloat(n) || 0;
    return '₹' + n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function setEl(id, val) {
    var el = document.getElementById(id);
    if (el) el.textContent = val;
  }

  function setCouponMsg(msg, type) {
    var el = document.getElementById('couponMsg');
    if (!el) return;
    var colors = { danger: '#c0392b', success: '#27ae60', '': '#666' };
    el.style.color = colors[type] || '#666';
    el.textContent = msg;
  }

  function escHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  /* ── Initial button state sync on page load ── */
  document.querySelectorAll('.cart-item-card').forEach(function (row) {
    var cartId = row.dataset.cartId;
    var stock  = parseInt(row.dataset.stock, 10) || 99;
    var inp    = row.querySelector('.qty-input');
    if (inp) syncQtyButtons(row, parseInt(inp.value, 10), stock);
  });

})();
</script>
