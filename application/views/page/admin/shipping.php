<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-truck"></i> Shipping &amp; Payment Settings</h3>
  </div>
  <div class="box-body">

    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo site_url('admin-shipping'); ?>">
      <div class="row">

        <div class="col-md-6">
          <div class="box box-default">
            <div class="box-header with-border">
              <h4 class="box-title"><i class="fa fa-truck"></i> Shipping Rules</h4>
            </div>
            <div class="box-body">
              <div class="form-group">
                <label>Free Shipping Above (₹)</label>
                <input type="number" class="form-control" name="free_shipping_above"
                       value="<?php echo htmlspecialchars($settings['free_shipping_above'] ?? '499'); ?>"
                       step="1" min="0" placeholder="499">
                <small class="text-muted">Orders above this amount get free shipping.</small>
              </div>
              <div class="form-group">
                <label>Standard Shipping Charge (₹)</label>
                <input type="number" class="form-control" name="standard_charge"
                       value="<?php echo htmlspecialchars($settings['standard_charge'] ?? '60'); ?>"
                       step="1" min="0" placeholder="60">
              </div>
              <div class="form-group">
                <label>Express Shipping Charge (₹)</label>
                <input type="number" class="form-control" name="express_charge"
                       value="<?php echo htmlspecialchars($settings['express_charge'] ?? '120'); ?>"
                       step="1" min="0" placeholder="120">
              </div>
              <div class="form-group">
                <label>Estimated Delivery Days</label>
                <input type="text" class="form-control" name="estimated_days"
                       value="<?php echo htmlspecialchars($settings['estimated_days'] ?? '3-5'); ?>"
                       placeholder="e.g. 3-5">
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="box box-default">
            <div class="box-header with-border">
              <h4 class="box-title"><i class="fa fa-credit-card"></i> Razorpay Payment Gateway</h4>
            </div>
            <div class="box-body">
              <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                Enter your Razorpay API keys to enable online payments. Get keys from
                <a href="https://dashboard.razorpay.com" target="_blank">dashboard.razorpay.com</a>.
              </div>
              <div class="form-group">
                <label>Razorpay Key ID</label>
                <input type="text" class="form-control" name="razorpay_key_id"
                       value="<?php echo htmlspecialchars($settings['razorpay_key_id'] ?? ''); ?>"
                       placeholder="rzp_test_XXXXXXXXXXXXXXXX">
              </div>
              <div class="form-group">
                <label>Razorpay Key Secret</label>
                <input type="password" class="form-control" name="razorpay_key_secret"
                       value="<?php echo htmlspecialchars($settings['razorpay_key_secret'] ?? ''); ?>"
                       placeholder="••••••••••••••••••••">
                <small class="text-muted">This key is used server-side only and never exposed to customers.</small>
              </div>
            </div>
          </div>
        </div>

      </div>

      <div class="box-footer">
        <button type="submit" class="btn btn-saffron btn-lg">
          <i class="fa fa-save"></i> Save Settings
        </button>
      </div>
    </form>

  </div>
</div>
