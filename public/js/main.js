/**
 * public/js/main.js  —  SpiceMart Frontend JavaScript
 */

// Cart AJAX endpoint URL (set via inline script from front-footer)
var CART_AJAX_URL = window.CART_AJAX_URL || '/spicemart/cart-ajax';

function showToast(message, type) {
  type = type || 'success';
  var container = document.getElementById('toastContainer');
  if (!container) return;
  var icons = { success: '✅', danger: '❌', warning: '⚠️', info: 'ℹ️' };
  var id = 'toast_' + Date.now();
  var html = '<div id="' + id + '" class="toast align-items-center text-bg-' + type + ' border-0 mb-2" role="alert">' +
    '<div class="d-flex"><div class="toast-body fw-500">' + (icons[type] || '') + ' ' + message + '</div>' +
    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
    '</div></div>';
  container.insertAdjacentHTML('beforeend', html);
  var el = document.getElementById(id);
  var t = new bootstrap.Toast(el, { delay: 3000 });
  t.show();
  el.addEventListener('hidden.bs.toast', function() { el.remove(); });
}

function updateCartBadge(count) {
  document.querySelectorAll('#cartBadge, .cart-badge').forEach(function(b) {
    b.textContent = count;
    b.style.display = count > 0 ? 'flex' : 'none';
  });
}

// Add to Cart
document.addEventListener('click', function(e) {
  var btn = e.target.closest('[data-add-cart]');
  if (!btn) return;
  e.preventDefault();
  var productId    = btn.dataset.addCart;
  var qty          = parseInt(btn.dataset.qty || '1', 10);
  var variantId    = btn.dataset.variantId    || '';
  var variantLabel = btn.dataset.variantLabel || '';

  // If product has variants but none selected, product.inc handles the toast/block.
  // Double-check here as a safety net.
  if (btn.dataset.hasVariants === '1' && !variantId) return;

  var isIconBtn = btn.dataset.cartIcon === '1';
  btn.disabled = true;
  btn.innerHTML = isIconBtn
    ? '<span class="spinner-border spinner-border-sm" style="width:.85rem;height:.85rem"></span>'
    : '<span class="spinner-border spinner-border-sm me-1"></span> Adding…';
  fetch(CART_AJAX_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'add', product_id: productId, qty: qty, variant_id: variantId, variant_label: variantLabel })
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.success) {
      showToast(data.message || 'Added to cart!', 'success');
      updateCartBadge(data.cart_count);
    } else if (data.message && data.message.toLowerCase().indexOf('login') !== -1) {
      window.location.href = window.LOGIN_URL || '/spicemart/login';
    } else {
      showToast(data.message || 'Something went wrong.', 'danger');
    }
  })
  .catch(function() { showToast('Something went wrong.', 'danger'); })
  .finally(function() {
    btn.disabled = false;
    btn.innerHTML = isIconBtn
      ? '<i class="bi bi-bag-plus"></i>'
      : '<i class="bi bi-bag-plus me-1"></i> Add to Cart';
  });
});

// Qty +/- buttons
document.addEventListener('click', function(e) {
  var btn = e.target.closest('[data-qty-change]');
  if (!btn) return;
  var cartId = btn.dataset.cartId;
  var delta  = parseInt(btn.dataset.qtyChange, 10);
  var input  = document.querySelector('.qty-input[data-cart-id="' + cartId + '"]');
  if (!input) return;
  var newQty = Math.max(1, parseInt(input.value, 10) + delta);
  input.value = newQty;
  updateCartItemAjax(cartId, newQty);
});

document.addEventListener('change', function(e) {
  if (!e.target.classList.contains('qty-input')) return;
  var cartId = e.target.dataset.cartId;
  var newQty = Math.max(1, parseInt(e.target.value, 10) || 1);
  e.target.value = newQty;
  updateCartItemAjax(cartId, newQty);
});

function updateCartItemAjax(cartId, qty) {
  fetch(CART_AJAX_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'update', cart_id: cartId, qty: qty })
  })
  .then(function(r) { return r.json(); })
  .then(function(data) { if (data.success) location.reload(); });
}

// Remove item — confirmation is handled by the modal in cart.inc (no confirm dialog)
document.addEventListener('click', function(e) {
  var btn = e.target.closest('[data-remove-cart]');
  if (!btn) return;
  /* Cart page uses its own modal confirmation (cart.inc), so skip here */
  if (document.getElementById('removeCartModal')) return;
  var cartId = btn.dataset.removeCart;
  fetch(CART_AJAX_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'remove', cart_id: cartId })
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.success) {
      var row = document.getElementById('cart-row-' + cartId);
      if (row) row.remove();
      updateCartBadge(data.cart_count);
      showToast('Item removed.', 'info');
      if (data.cart_count === 0) location.reload();
    }
  });
});

// Wishlist toggle
function updateWishlistBadge(count) {
  var badge = document.getElementById('wishlistBadge');
  if (!badge) return;
  badge.textContent = count;
  badge.style.display = count > 0 ? 'flex' : 'none';
}

document.addEventListener('click', function(e) {
  var btn = e.target.closest('[data-wishlist]');
  if (!btn) return;
  e.preventDefault();
  e.stopPropagation();
  var productId = btn.dataset.wishlist;
  var url = (window.WISHLIST_TOGGLE_URL || '/wishlist/toggle/') + productId;
  btn.disabled = true;
  fetch(url)
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (!data.success) {
        window.location.href = window.LOGIN_URL || '/login';
        return;
      }
      var icon = btn.querySelector('i');
      if (icon) {
        icon.classList.toggle('bi-heart',      data.action === 'removed');
        icon.classList.toggle('bi-heart-fill', data.action === 'added');
      }
      updateWishlistBadge(data.wishlist_count);
      showToast(data.action === 'added' ? 'Added to wishlist!' : 'Removed from wishlist.', data.action === 'added' ? 'success' : 'info');
    })
    .catch(function() { showToast('Something went wrong.', 'danger'); })
    .finally(function() { btn.disabled = false; });
});

// Sticky navbar
window.addEventListener('scroll', function() {
  var nav = document.getElementById('mainNav');
  if (nav) nav.classList.toggle('scrolled', window.scrollY > 20);
});

// ── Hover-open nav dropdowns (desktop ≥ 992px) ─────────────
(function () {
  function initHoverNav() {
    if (window.innerWidth < 992) return;

    /* 1. Top-level "Categories" dropdown */
    document.querySelectorAll('.navbar-nav .nav-item.dropdown').forEach(function (item) {
      var menu = item.querySelector(':scope > .dropdown-menu');
      if (!menu) return;
      item.addEventListener('mouseenter', function () {
        menu.style.display = 'block';
      });
      item.addEventListener('mouseleave', function () {
        menu.style.display = '';
      });
    });

    /* 2. Nested dropend submenus */
    document.querySelectorAll('.navbar-nav .dropdown-menu .dropend').forEach(function (item) {
      var sub = item.querySelector(':scope > .dropdown-menu');
      if (!sub) return;

      item.addEventListener('mouseenter', function () {
        sub.style.display    = 'block';
        sub.style.position   = 'absolute';
        sub.style.top        = '0';
        sub.style.left       = item.offsetWidth + 'px';
        sub.style.marginLeft = '0';
        sub.style.zIndex     = '1050';
      });
      item.addEventListener('mouseleave', function () {
        sub.style.display = '';
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHoverNav);
  } else {
    initHoverNav();
  }

  /* Re-init if window resized across the 992px breakpoint */
  var lastWidth = window.innerWidth;
  window.addEventListener('resize', function () {
    if (window.innerWidth !== lastWidth) {
      lastWidth = window.innerWidth;
      initHoverNav();
    }
  });
})();

// Price range label
var priceRange = document.getElementById('priceRange');
var priceLabel = document.getElementById('priceLabel');
if (priceRange && priceLabel) {
  priceRange.addEventListener('input', function() {
    priceLabel.textContent = '₹' + priceRange.value;
  });
}

// Product image gallery
document.addEventListener('click', function(e) {
  var thumb = e.target.closest('.product-thumb');
  if (!thumb) return;
  var main = document.getElementById('mainProductImg');
  if (main) {
    main.src = thumb.src;
    document.querySelectorAll('.product-thumb').forEach(function(t) { t.classList.remove('active'); });
    thumb.classList.add('active');
  }
});

// Review star picker
var starPicker = document.getElementById('starPicker');
if (starPicker) {
  var stars = starPicker.querySelectorAll('[data-star]');
  var ratingInput = document.getElementById('ratingInput');
  function highlightStars(n) {
    stars.forEach(function(s) {
      if (parseInt(s.dataset.star) <= parseInt(n)) {
        s.className = 'bi bi-star-fill text-warning fs-4 me-1';
      } else {
        s.className = 'bi bi-star text-warning fs-4 me-1';
      }
      s.style.cursor = 'pointer';
    });
  }
  stars.forEach(function(star) {
    star.addEventListener('mouseenter', function() { highlightStars(star.dataset.star); });
    star.addEventListener('mouseleave', function() { highlightStars(ratingInput ? ratingInput.value : 0); });
    star.addEventListener('click', function() {
      if (ratingInput) ratingInput.value = star.dataset.star;
      highlightStars(star.dataset.star);
    });
  });
}
