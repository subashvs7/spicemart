<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php if ($success): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
  <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row">
  <div class="col-md-8">
    <form method="post" action="<?php echo site_url('admin-fazaa'); ?>">
      <input type="hidden" name="save_fazaa" value="1">

      <?php
      $program_defs = array(
        'fazaa' => array('label' => 'Fazaa', 'desc' => 'UAE Ministry of Human Resources & Emiratisation discount card'),
        'isaad' => array('label' => 'Isaad', 'desc' => 'UAE Armed Forces & government employee discount program'),
      );
      foreach ($program_defs as $prog => $def):
        $p = $prog_map[$prog] ?? array();
        $enabled      = isset($p['enabled'])      ? (int)$p['enabled']            : 1;
        $discount_pct = isset($p['discount_pct']) ? (float)$p['discount_pct']     : 10;
        $max_discount = isset($p['max_discount']) ? (float)$p['max_discount']     : 100;
        $min_order    = isset($p['min_order'])    ? (float)$p['min_order']        : 0;
        $api_url      = $p['api_url']  ?? '';
        $api_key      = $p['api_key']  ?? '';
        $otp_enabled  = isset($p['otp_enabled'])  ? (int)$p['otp_enabled']        : 0;
        $logo_url     = $p['logo_url'] ?? '';
      ?>
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">
            <i class="fa fa-id-card-o text-saffron"></i>
            &nbsp;<?php echo $def['label']; ?>
            <small class="text-muted"> — <?php echo $def['desc']; ?></small>
          </h3>
          <div class="box-tools pull-right">
            <div class="toggle-switch" style="margin-top:2px">
              <input type="checkbox" id="en_<?php echo $prog; ?>" name="enabled_<?php echo $prog; ?>"
                     value="1" <?php echo $enabled ? 'checked' : ''; ?>>
              <label for="en_<?php echo $prog; ?>" style="cursor:pointer">
                <span class="label <?php echo $enabled ? 'label-success' : 'label-default'; ?>">
                  <?php echo $enabled ? 'Enabled' : 'Disabled'; ?>
                </span>
              </label>
            </div>
          </div>
        </div>
        <div class="box-body">
          <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                <label class="text-muted small">Discount %</label>
                <div class="input-group input-group-sm">
                  <input type="number" class="form-control" name="discount_pct_<?php echo $prog; ?>"
                         value="<?php echo $discount_pct; ?>" min="0" max="100" step="0.01">
                  <span class="input-group-addon">%</span>
                </div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label class="text-muted small">Max Discount (₹)</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-addon">₹</span>
                  <input type="number" class="form-control" name="max_discount_<?php echo $prog; ?>"
                         value="<?php echo $max_discount; ?>" min="0" step="0.01">
                </div>
                <p class="help-block">0 = no cap</p>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label class="text-muted small">Minimum Order (₹)</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-addon">₹</span>
                  <input type="number" class="form-control" name="min_order_<?php echo $prog; ?>"
                         value="<?php echo $min_order; ?>" min="0" step="0.01">
                </div>
                <p class="help-block">0 = no minimum</p>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label class="text-muted small">Verification API URL <span class="text-muted">(optional)</span></label>
                <input type="url" class="form-control input-sm" name="api_url_<?php echo $prog; ?>"
                       value="<?php echo htmlspecialchars($api_url); ?>"
                       placeholder="https://api.example.com/verify">
                <p class="help-block">Leave blank to accept any member number (demo/internal use)</p>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label class="text-muted small">API Key / Token</label>
                <input type="text" class="form-control input-sm" name="api_key_<?php echo $prog; ?>"
                       value="<?php echo htmlspecialchars($api_key); ?>"
                       placeholder="Bearer token or API key">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label class="text-muted small">Logo URL <span class="text-muted">(optional)</span></label>
                <input type="text" class="form-control input-sm" name="logo_url_<?php echo $prog; ?>"
                       value="<?php echo htmlspecialchars($logo_url); ?>"
                       placeholder="https://…/logo.png">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label class="text-muted small">OTP Verification</label><br>
                <div class="radio">
                  <label>
                    <input type="radio" name="otp_enabled_<?php echo $prog; ?>" value="0"
                           <?php echo !$otp_enabled ? 'checked' : ''; ?>>
                    Disabled — verify membership number only
                  </label>
                </div>
                <div class="radio">
                  <label>
                    <input type="radio" name="otp_enabled_<?php echo $prog; ?>" value="1"
                           <?php echo $otp_enabled ? 'checked' : ''; ?>>
                    Enabled — send OTP to registered mobile
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

      <div class="box-footer">
        <button type="submit" class="btn btn-saffron btn-sm">
          <i class="fa fa-save"></i> Save Settings
        </button>
        <a href="<?php echo site_url('admin-fazaa-report'); ?>" class="btn btn-default btn-sm pull-right">
          <i class="fa fa-bar-chart"></i> View Usage Report
        </a>
      </div>
    </form>
  </div>

  <div class="col-md-4">
    <div class="box box-info">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-info-circle"></i> How It Works</h3>
      </div>
      <div class="box-body">
        <p class="text-muted small">Customers with a Fazaa or Isaad membership card can apply their government employee discount at checkout.</p>
        <ol class="text-muted small" style="padding-left:16px">
          <li>Customer selects Fazaa or Isaad on the checkout page</li>
          <li>Enters their membership number</li>
          <li>System verifies via your API (or accepts all if no API set)</li>
          <li>If OTP is enabled, an OTP is sent to the registered mobile</li>
          <li>Discount is applied automatically to the order total</li>
        </ol>
        <div class="callout callout-warning" style="padding:8px 12px">
          <i class="fa fa-exclamation-triangle text-warning"></i>
          <small>If no API URL is configured, any membership number with 4+ characters will be accepted. Configure an external verification API for production use.</small>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Toggle label on checkbox change
document.querySelectorAll('[id^="en_"]').forEach(function(cb) {
  cb.addEventListener('change', function() {
    var lbl = this.nextElementSibling.querySelector('.label');
    if (this.checked) {
      lbl.className = 'label label-success'; lbl.textContent = 'Enabled';
    } else {
      lbl.className = 'label label-default'; lbl.textContent = 'Disabled';
    }
  });
});
</script>
