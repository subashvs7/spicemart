<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Footer -->
<footer class="site-footer mt-5">
  <div class="container">
    <div class="row gy-4">

      <div class="col-lg-4">
        <h5 class="footer-brand">🌶️ SpiceMart</h5>
        <p class="text-muted small mt-2">
          Bringing the finest farm-fresh spices and masalas directly to your kitchen.
          100% pure, no additives, no compromises.
        </p>
        <div class="social-links mt-3">
          <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
          <a href="#" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
        </div>
      </div>

      <div class="col-6 col-lg-2">
        <h6 class="footer-heading">Quick Links</h6>
        <ul class="footer-list">
          <li><a href="<?php echo site_url('home'); ?>">Home</a></li>
          <li><a href="<?php echo site_url('shop'); ?>">Shop</a></li>
          <li><a href="<?php echo site_url('account'); ?>">My Account</a></li>
          <li><a href="<?php echo site_url('cart'); ?>">Cart</a></li>
        </ul>
      </div>

      <div class="col-6 col-lg-2">
        <h6 class="footer-heading">Categories</h6>
        <ul class="footer-list">
          <li><a href="<?php echo site_url('shop'); ?>?category=whole-spices">Whole Spices</a></li>
          <li><a href="<?php echo site_url('shop'); ?>?category=ground-masala">Ground Masala</a></li>
          <li><a href="<?php echo site_url('shop'); ?>?category=blended-masala">Blended Masala</a></li>
          <li><a href="<?php echo site_url('shop'); ?>?category=seeds">Seeds</a></li>
          <li><a href="<?php echo site_url('shop'); ?>?category=dry-fruits">Dry Fruits</a></li>
        </ul>
      </div>

      <div class="col-lg-4">
        <h6 class="footer-heading">Get In Touch</h6>
        <ul class="footer-list">
          <li><i class="bi bi-telephone me-2 text-saffron"></i>+91 98765 43210</li>
          <li><i class="bi bi-envelope me-2 text-saffron"></i>hello@spicemart.in</li>
          <li><i class="bi bi-geo-alt me-2 text-saffron"></i>Koyambedu Market, Chennai – 600092</li>
        </ul>
        <div class="trust-badges mt-3 d-flex flex-wrap gap-2">
          <span class="badge-pill">✅ 100% Pure</span>
          <span class="badge-pill">🌿 No Preservatives</span>
          <span class="badge-pill">🚚 Fast Delivery</span>
        </div>
      </div>

    </div>
    <hr class="footer-hr mt-4">
    <div class="row align-items-center py-3">
      <div class="col-md-6 text-center text-md-start">
        <small class="text-muted">© <?php echo date('Y'); ?> SpiceMart. All rights reserved.</small>
      </div>
      <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
        <small class="text-muted">Secure Payments</small>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  window.CART_AJAX_URL = '<?php echo site_url("cart-ajax"); ?>';
  window.LOGIN_URL     = '<?php echo site_url("login"); ?>';
</script>
<!-- Custom JS -->
<script src="<?php echo base_url('public/js/main.js'); ?>"></script>
<?php
if (isset($js) && !empty($js)) {
    include_once(APPPATH . 'views/inc/inc-js/' . $js);
}
?>
</body>
</html>
