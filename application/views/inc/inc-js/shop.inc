<script>
(function () {
  var AJAX_URL = '<?php echo site_url("shop-ajax"); ?>';
  var searchTimer, priceTimer;

  function getFilters(page) {
    var cat    = document.querySelector('input[name="category"]:checked');
    var brand  = document.querySelector('input[name="brand"]:checked');
    return {
      q:         document.getElementById('filterQ').value.trim(),
      category:  cat   ? cat.value   : '',
      brand:     brand ? brand.value : '',
      max_price: document.getElementById('priceRange').value,
      sort:      document.getElementById('shopSort').value,
      page:      page || 1,
    };
  }

  function bindPagination() {
    document.querySelectorAll('.ajax-page').forEach(function (a) {
      a.addEventListener('click', function (e) {
        e.preventDefault();
        if (!this.closest('.page-item').classList.contains('disabled')) {
          fetchProducts(parseInt(this.dataset.page, 10));
        }
      });
    });
  }

  function fetchProducts(page) {
    var filters = getFilters(page);
    var params  = new URLSearchParams(filters);

    document.getElementById('filterSpinner').classList.remove('d-none');
    document.getElementById('productResults').style.opacity = '0.4';

    fetch(AJAX_URL + '?' + params.toString())
      .then(function (r) { return r.json(); })
      .then(function (data) {
        document.getElementById('productResults').innerHTML = data.html + data.pagination;
        document.getElementById('productResults').style.opacity = '1';
        document.getElementById('filterSpinner').classList.add('d-none');

        var count = data.total;
        document.getElementById('productTotal').textContent =
          count + ' product' + (count !== 1 ? 's' : '') + ' found';

        history.pushState(null, '', window.location.pathname + '?' + params.toString());
        bindPagination();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      })
      .catch(function () {
        document.getElementById('filterSpinner').classList.add('d-none');
        document.getElementById('productResults').style.opacity = '1';
      });
  }

  // Search — debounced 400 ms
  document.getElementById('filterQ').addEventListener('input', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function () { fetchProducts(1); }, 400);
  });

  // Category radios — immediate
  document.querySelectorAll('input[name="category"]').forEach(function (r) {
    r.addEventListener('change', function () { fetchProducts(1); });
  });

  // Brand radios — immediate
  document.querySelectorAll('input[name="brand"]').forEach(function (r) {
    r.addEventListener('change', function () { fetchProducts(1); });
  });

  // Price range — debounced 500 ms, update label instantly
  document.getElementById('priceRange').addEventListener('input', function () {
    document.getElementById('priceLabel').textContent = '₹' + this.value;
    clearTimeout(priceTimer);
    var val = this.value;
    priceTimer = setTimeout(function () { fetchProducts(1); }, 500);
  });

  // Sort — immediate
  document.getElementById('shopSort').addEventListener('change', function () {
    fetchProducts(1);
  });

  // Clear All
  document.getElementById('clearFilters').addEventListener('click', function () {
    document.getElementById('filterQ').value = '';
    var allCat = document.querySelector('input[name="category"][value=""]');
    if (allCat) allCat.checked = true;
    var allBrand = document.querySelector('input[name="brand"][value=""]');
    if (allBrand) allBrand.checked = true;
    document.getElementById('priceRange').value = 2000;
    document.getElementById('priceLabel').textContent = '₹2000';
    document.getElementById('shopSort').value = 'newest';
    fetchProducts(1);
  });

  // Initial pagination binding (server-rendered first load)
  bindPagination();
})();
</script>
