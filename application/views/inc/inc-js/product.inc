<script>
(function () {
  var addBtn       = document.getElementById('addToCartBtn');
  var qtyInput     = document.getElementById('qtyInput');
  var priceEl      = document.getElementById('productPrice');
  var skuEl        = document.getElementById('variantSku');
  var basePrice    = priceEl ? parseFloat(priceEl.dataset.basePrice || '0') : 0;

  // { "Size": { id, value, type, priceMod, sku }, ... }
  var selected     = {};
  var variantGroups = {};   // { "Size": [btn, btn, ...], ... }

  document.querySelectorAll('.variant-btn').forEach(function (btn) {
    var type = btn.dataset.variantType;
    if (!variantGroups[type]) variantGroups[type] = [];
    variantGroups[type].push(btn);

    btn.addEventListener('click', function () {
      var isColor = !!btn.dataset.colorHex;

      // Deselect all siblings in this group
      variantGroups[type].forEach(function (b) {
        if (b.dataset.colorHex) {
          b.classList.remove('swatch-selected');
        } else {
          b.classList.remove('btn-saffron', 'active');
          b.classList.add('btn-outline-secondary');
        }
      });

      // Select this button
      if (isColor) {
        btn.classList.add('swatch-selected');
        // Show color name label
        var labelEl = document.getElementById('colorNameLabel_' + type);
        if (labelEl) labelEl.textContent = btn.dataset.colorName || '';
      } else {
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-saffron', 'active');
      }

      selected[type] = {
        id:       btn.dataset.variantId,
        value:    btn.dataset.variantValue,
        type:     type,
        priceMod: parseFloat(btn.dataset.priceMod || '0'),
        sku:      btn.dataset.sku || '',
      };

      // Hide "required" hint for this group
      var reqEl = document.getElementById('req_' + type);
      if (reqEl) reqEl.style.display = 'none';

      updatePrice();
      updateSku();
      syncAddBtn();
    });
  });

  function updatePrice() {
    if (!priceEl) return;
    var mod = 0;
    Object.keys(selected).forEach(function (t) { mod += selected[t].priceMod; });
    var final = basePrice + mod;
    priceEl.textContent = '₹' + final.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function updateSku() {
    if (!skuEl) return;
    var types = Object.keys(selected);
    if (types.length === 1 && selected[types[0]].sku) {
      skuEl.textContent = 'SKU: ' + selected[types[0]].sku;
    } else {
      skuEl.textContent = '';
    }
  }

  function syncAddBtn() {
    if (!addBtn) return;
    var types   = Object.keys(variantGroups);
    var allDone = types.every(function (t) { return !!selected[t]; });
    if (allDone) {
      var primaryId = selected[types[0]].id;
      var label     = types.map(function (t) { return t + ': ' + selected[t].value; }).join(' | ');
      addBtn.dataset.variantId    = primaryId;
      addBtn.dataset.variantLabel = label;
    } else {
      delete addBtn.dataset.variantId;
      delete addBtn.dataset.variantLabel;
    }
  }

  // Qty +/- sync
  if (qtyInput && addBtn) {
    qtyInput.addEventListener('change', function () {
      var max = parseInt(qtyInput.max || '99', 10);
      var val = Math.max(1, Math.min(max, parseInt(qtyInput.value || '1', 10)));
      qtyInput.value    = val;
      addBtn.dataset.qty = val;
    });
  }

  // Intercept Add to Cart click — validate before main.js fires
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
      // Qty sync
      if (qtyInput) addBtn.dataset.qty = qtyInput.value || '1';
    });
  }
})();
</script>
