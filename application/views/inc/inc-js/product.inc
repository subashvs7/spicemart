<script>
(function () {
  var VARIANT_URL  = '<?php echo site_url("shop/variant_info"); ?>/';
  var addBtn       = document.getElementById('addToCartBtn');
  var qtyInput     = document.getElementById('qtyInput');
  var priceEl      = document.getElementById('productPrice');
  var skuEl        = document.getElementById('variantSku');
  var stockWrap    = document.getElementById('stockBadgeWrap');
  var basePrice    = priceEl ? parseFloat(priceEl.dataset.basePrice || '0') : 0;

  // { type: {id, value, priceMod, stock, sku} }
  var selected      = {};
  // { type: [btn, btn, ...] }
  var variantGroups = {};
  // { type: true } — request in flight
  var pending       = {};

  document.querySelectorAll('.variant-btn').forEach(function (btn) {
    var type = btn.dataset.variantType;
    if (!variantGroups[type]) variantGroups[type] = [];
    variantGroups[type].push(btn);

    btn.addEventListener('click', function () {
      if (pending[type]) return;

      var isColor = !!btn.dataset.colorHex;
      var vid     = btn.dataset.variantId;

      // Deselect all siblings
      variantGroups[type].forEach(function (b) {
        if (b.dataset.colorHex) {
          b.classList.remove('swatch-selected');
        } else {
          b.classList.remove('variant-text-selected');
        }
      });

      // Visually select this button immediately
      if (isColor) {
        btn.classList.add('swatch-selected');
        var labelEl = document.getElementById('colorNameLabel_' + type);
        if (labelEl) labelEl.textContent = btn.dataset.colorName || '';
      } else {
        btn.classList.add('variant-text-selected');
      }

      var reqEl = document.getElementById('req_' + type);
      if (reqEl) reqEl.style.display = 'none';

      // Dim price while fetching
      if (priceEl) priceEl.style.opacity = '0.4';

      pending[type] = true;

      fetch(VARIANT_URL + vid)
        .then(function (r) { return r.json(); })
        .then(function (data) {
          pending[type] = false;
          if (priceEl) priceEl.style.opacity = '';

          if (data.ok) {
            selected[type] = {
              id:       vid,
              value:    data.variant_value,
              priceMod: data.price_modifier,
              stock:    data.stock_qty,
              sku:      data.sku,
            };
            updateStockWrap(data.stock_qty);
          } else {
            // Fallback to data attributes
            selected[type] = fallback(btn);
          }

          updatePrice();
          updateSku();
          syncAddBtn();
          updateCartBtnStock();
        })
        .catch(function () {
          pending[type] = false;
          if (priceEl) priceEl.style.opacity = '';
          selected[type] = fallback(btn);
          updatePrice();
          updateSku();
          syncAddBtn();
          updateCartBtnStock();
        });
    });
  });

  function fallback(btn) {
    return {
      id:       btn.dataset.variantId,
      value:    btn.dataset.variantValue,
      priceMod: parseFloat(btn.dataset.priceMod || '0'),
      stock:    parseInt(btn.dataset.stock || '0', 10),
      sku:      btn.dataset.sku || '',
    };
  }

  function updatePrice() {
    if (!priceEl) return;
    var mod = 0;
    Object.keys(selected).forEach(function (t) { mod += selected[t].priceMod; });
    priceEl.textContent = '₹' + (basePrice + mod).toLocaleString('en-IN', {
      minimumFractionDigits: 2, maximumFractionDigits: 2
    });
  }

  function updateSku() {
    if (!skuEl) return;
    var skus = Object.keys(selected).map(function (t) { return selected[t].sku; }).filter(Boolean);
    skuEl.textContent = skus.length ? 'SKU: ' + skus[0] : '';
  }

  function updateStockWrap(qty) {
    if (!stockWrap) return;
    if (qty <= 0) {
      stockWrap.innerHTML = '<span class="badge bg-secondary">Out of Stock</span>';
    } else if (qty < 10) {
      stockWrap.innerHTML =
        '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>In Stock</span>' +
        '<span class="badge bg-warning text-dark ms-2">Only ' + qty + ' left!</span>';
    } else {
      stockWrap.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>In Stock</span>';
    }
  }

  function syncAddBtn() {
    if (!addBtn) return;
    var types   = Object.keys(variantGroups);
    var allDone = types.every(function (t) { return !!selected[t]; });
    if (allDone) {
      addBtn.dataset.variantId    = selected[types[0]].id;
      addBtn.dataset.variantLabel = types.map(function (t) {
        return t + ': ' + selected[t].value;
      }).join(' | ');
    } else {
      delete addBtn.dataset.variantId;
      delete addBtn.dataset.variantLabel;
    }
  }

  function updateCartBtnStock() {
    if (!addBtn) return;
    var anyOos = Object.keys(selected).some(function (t) { return selected[t].stock <= 0; });
    if (anyOos) {
      addBtn.disabled = true;
      addBtn.innerHTML = '<i class="bi bi-bag-x me-2"></i>Out of Stock';
      addBtn.style.opacity = '0.6';
    } else {
      addBtn.disabled = false;
      addBtn.innerHTML = '<i class="bi bi-bag-plus me-2"></i>Add to Cart';
      addBtn.style.opacity = '';
    }
  }

  // Qty input sync
  if (qtyInput && addBtn) {
    qtyInput.addEventListener('change', function () {
      var max = parseInt(qtyInput.max || '99', 10);
      var val = Math.max(1, Math.min(max, parseInt(qtyInput.value || '1', 10)));
      qtyInput.value     = val;
      addBtn.dataset.qty = val;
    });
  }

  // Validate variant selection before add-to-cart fires
  if (addBtn && addBtn.dataset.hasVariants) {
    addBtn.addEventListener('click', function (e) {
      var types   = Object.keys(variantGroups);
      var missing = types.filter(function (t) { return !selected[t]; });
      if (missing.length > 0) {
        e.stopImmediatePropagation();
        missing.forEach(function (t) {
          var reqEl = document.getElementById('req_' + t);
          if (reqEl) reqEl.style.display = 'inline';
        });
        if (typeof showToast === 'function') {
          showToast('Please select ' + missing.join(', ') + ' before adding to cart.', 'warning');
        }
        return;
      }
      if (qtyInput) addBtn.dataset.qty = qtyInput.value || '1';
    });
  }
})();
</script>
