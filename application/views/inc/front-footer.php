<?php defined('BASEPATH') OR exit('No direct script access allowed');

/* Settings shorthand */
$cfg = isset($app_settings) ? $app_settings : array();
function fs($cfg, $key, $default = '') {
    return htmlspecialchars(isset($cfg[$key]) ? (string)$cfg[$key] : $default);
}

/* Collect parent categories for the footer column */
$footer_cats = array();
if (!empty($all_categories)) {
    foreach ($all_categories as $cat) {
        if (empty($cat['parent_id'])) {
            $footer_cats[] = $cat;
        }
    }
}

/* Social links — only render if not empty / not '#' */
$socials = array(
    'social_facebook'  => array('bi-facebook',  'Facebook'),
    'social_instagram' => array('bi-instagram', 'Instagram'),
    'social_youtube'   => array('bi-youtube',   'YouTube'),
    'social_whatsapp'  => array('bi-whatsapp',  'WhatsApp'),
    'social_twitter'   => array('bi-twitter-x', 'Twitter/X'),
);
?>

<footer class="site-footer mt-5">
  <div class="container">
    <div class="row gy-4">

      <!-- Brand blurb -->
      <div class="col-lg-4">
        <h5 class="footer-brand">🛍️ <?php echo fs($cfg,'site_name','myeoncasuals'); ?></h5>
        <p class="small mt-2" style="color:rgba(255,255,255,.75)">
          <?php echo nl2br(fs($cfg,'footer_about','Bringing you the trendiest casual wear at affordable prices. Quality fabrics, stylish designs, delivered to your doorstep.')); ?>
        </p>
        <div class="social-links mt-3">
          <?php foreach ($socials as $key => $meta):
            $url = isset($cfg[$key]) ? trim($cfg[$key]) : '';
            if (!$url || $url === '#') continue;
          ?>
            <a href="<?php echo htmlspecialchars($url); ?>"
               aria-label="<?php echo $meta[1]; ?>"
               target="_blank" rel="noopener">
              <i class="bi <?php echo $meta[0]; ?>"></i>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="col-6 col-lg-2">
        <h6 class="footer-heading">Quick Links</h6>
        <ul class="footer-list">
          <li><a href="<?php echo site_url('home'); ?>">Home</a></li>
          <li><a href="<?php echo site_url('shop'); ?>">Shop All</a></li>
          <li><a href="<?php echo site_url('contact'); ?>">Contact Us</a></li>
          <li><a href="<?php echo site_url('account'); ?>">My Account</a></li>
          <li><a href="<?php echo site_url('cart'); ?>">Cart</a></li>
          <li><a href="<?php echo site_url('page/about'); ?>">About Us</a></li>
        </ul>
      </div>

      <!-- Dynamic Categories -->
      <div class="col-6 col-lg-2">
        <h6 class="footer-heading">Categories</h6>
        <ul class="footer-list">
          <?php if (!empty($footer_cats)): ?>
            <?php foreach (array_slice($footer_cats, 0, 6) as $fc): ?>
              <li>
                <a href="<?php echo site_url('shop'); ?>?category=<?php echo htmlspecialchars($fc['slug']); ?>">
                  <?php echo htmlspecialchars($fc['name']); ?>
                </a>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li><a href="<?php echo site_url('shop'); ?>">All Products</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- Policies + Contact -->
      <div class="col-lg-4">
        <h6 class="footer-heading">Policies</h6>
        <ul class="footer-list mb-3">
          <li><a href="<?php echo site_url('page/return-policy'); ?>">Return Policy</a></li>
          <li><a href="<?php echo site_url('page/privacy'); ?>">Privacy Policy</a></li>
          <li><a href="<?php echo site_url('page/terms'); ?>">Terms &amp; Conditions</a></li>
        </ul>

        <h6 class="footer-heading">Get In Touch</h6>
        <ul class="footer-list">
          <?php if (!empty($cfg['contact_phone'])): ?>
          <li>
            <i class="bi bi-telephone me-2 text-saffron"></i>
            <a href="tel:<?php echo preg_replace('/\s+/','',$cfg['contact_phone']); ?>"
               style="color:#bbb">
              <?php echo fs($cfg,'contact_phone'); ?>
            </a>
          </li>
          <?php endif; ?>
          <?php if (!empty($cfg['contact_email'])): ?>
          <li>
            <i class="bi bi-envelope me-2 text-saffron"></i>
            <a href="mailto:<?php echo fs($cfg,'contact_email'); ?>" style="color:#bbb">
              <?php echo fs($cfg,'contact_email'); ?>
            </a>
          </li>
          <?php endif; ?>
          <?php if (!empty($cfg['contact_address'])): ?>
          <li>
            <i class="bi bi-geo-alt me-2 text-saffron"></i>
            <?php echo nl2br(fs($cfg,'contact_address')); ?>
          </li>
          <?php endif; ?>
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
        <small class="text-muted">© <?php echo date('Y'); ?> <?php echo fs($cfg,'footer_copyright','myeoncasuals. All rights reserved.'); ?></small>
      </div>
      <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
        <small class="text-muted">
          <i class="bi bi-shield-check text-saffron me-1"></i>Secure Payments
          &nbsp;|&nbsp; Made with ❤️ in India
        </small>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  window.CART_AJAX_URL       = '<?php echo site_url("cart-ajax"); ?>';
  window.LOGIN_URL           = '<?php echo site_url("login"); ?>';
  window.WISHLIST_TOGGLE_URL = '<?php echo site_url("wishlist/toggle/"); ?>';
</script>
<script src="<?php echo base_url('public/js/main.js'); ?>?v=<?php echo filemtime(FCPATH.'public/js/main.js'); ?>"></script>
<?php if (isset($js) && !empty($js)) {
    include_once APPPATH . 'views/inc/inc-js/' . $js;
} ?>
</body>
</html>
