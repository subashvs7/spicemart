<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="post" action="<?php echo site_url('admin-shipping'); ?>">
<div class="row">

  <!-- Left column -->
  <div class="col-md-7">

    <!-- Shipping Rules -->
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-truck"></i> Shipping Rules</h3>
      </div>
      <div class="box-body">
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label>Free Shipping Above (₹)</label>
              <div class="input-group">
                <span class="input-group-addon">₹</span>
                <input type="number" class="form-control" name="free_shipping_above"
                       value="<?php echo htmlspecialchars($settings['free_shipping_above'] ?? '499'); ?>"
                       step="1" min="0" placeholder="499">
              </div>
              <small class="text-muted">Set 0 to disable free shipping.</small>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label>Estimated Delivery Days</label>
              <input type="text" class="form-control" name="estimated_days"
                     value="<?php echo htmlspecialchars($settings['estimated_days'] ?? '3-5'); ?>"
                     placeholder="e.g. 3-5">
              <small class="text-muted">Shown on product and order pages.</small>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label>Standard Shipping Charge (₹)</label>
              <div class="input-group">
                <span class="input-group-addon">₹</span>
                <input type="number" class="form-control" name="standard_charge"
                       value="<?php echo htmlspecialchars($settings['standard_charge'] ?? '60'); ?>"
                       step="1" min="0" placeholder="60">
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label>Express Shipping Charge (₹)</label>
              <div class="input-group">
                <span class="input-group-addon">₹</span>
                <input type="number" class="form-control" name="express_charge"
                       value="<?php echo htmlspecialchars($settings['express_charge'] ?? '120'); ?>"
                       step="1" min="0" placeholder="120">
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label>Packaging Charge (₹) <span class="text-muted small">per order</span></label>
              <div class="input-group">
                <span class="input-group-addon">₹</span>
                <input type="number" class="form-control" name="packaging_charge"
                       value="<?php echo htmlspecialchars($settings['packaging_charge'] ?? '0'); ?>"
                       step="1" min="0" placeholder="0">
              </div>
              <small class="text-muted">Set 0 to disable.</small>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label>Handling Fee (₹) <span class="text-muted small">per order</span></label>
              <div class="input-group">
                <span class="input-group-addon">₹</span>
                <input type="number" class="form-control" name="handling_fee"
                       value="<?php echo htmlspecialchars($settings['handling_fee'] ?? '0'); ?>"
                       step="1" min="0" placeholder="0">
              </div>
              <small class="text-muted">Set 0 to disable.</small>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>Order Dispatch Cut-off Time</label>
          <input type="text" class="form-control" name="dispatch_cutoff"
                 value="<?php echo htmlspecialchars($settings['dispatch_cutoff'] ?? '2:00 PM'); ?>"
                 placeholder="e.g. 2:00 PM">
          <small class="text-muted">Orders placed before this time are dispatched same day.</small>
        </div>
        <div class="form-group">
          <label>Checkout Shipping Message <span class="text-muted small">(shown on cart &amp; checkout)</span></label>
          <input type="text" class="form-control" name="shipping_message"
                 value="<?php echo htmlspecialchars($settings['shipping_message'] ?? ''); ?>"
                 placeholder="e.g. 🚚 Free shipping on orders above ₹499">
          <small class="text-muted">Leave blank to hide.</small>
        </div>
      </div>
    </div>

    <!-- COD Settings -->
    <div class="box box-warning">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-money"></i> Cash on Delivery (COD)</h3>
        <div class="box-tools pull-right">
          <?php $cod_on = ($settings['cod_enabled'] ?? '1') == '1'; ?>
          <span class="label <?php echo $cod_on ? 'label-success' : 'label-default'; ?>" id="codStatusLabel">
            <?php echo $cod_on ? 'Enabled' : 'Disabled'; ?>
          </span>
        </div>
      </div>
      <div class="box-body">
        <div class="row">
          <div class="col-sm-4">
            <div class="form-group">
              <label>COD Status</label>
              <select class="form-control" name="cod_enabled" id="codEnabledSelect">
                <option value="1" <?php echo $cod_on ? 'selected' : ''; ?>>Enabled</option>
                <option value="0" <?php echo !$cod_on ? 'selected' : ''; ?>>Disabled</option>
              </select>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              <label>COD Surcharge (₹)</label>
              <div class="input-group">
                <span class="input-group-addon">₹</span>
                <input type="number" class="form-control" name="cod_surcharge"
                       value="<?php echo htmlspecialchars($settings['cod_surcharge'] ?? '0'); ?>"
                       step="1" min="0" placeholder="0">
              </div>
              <small class="text-muted">Extra charge for COD. Set 0 to disable.</small>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              <label>COD Minimum Order (₹)</label>
              <div class="input-group">
                <span class="input-group-addon">₹</span>
                <input type="number" class="form-control" name="cod_min_order"
                       value="<?php echo htmlspecialchars($settings['cod_min_order'] ?? '0'); ?>"
                       step="1" min="0" placeholder="0">
              </div>
              <small class="text-muted">Minimum cart value to allow COD. Set 0 for no minimum.</small>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Right column -->
  <div class="col-md-5">

    <!-- Courier Partners -->
    <div class="box box-default">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-plane"></i> Courier Partners</h3>
      </div>
      <div class="box-body">
        <div class="form-group">
          <label>Available Couriers <span class="text-muted small">(comma-separated)</span></label>
          <textarea class="form-control" name="courier_partners" rows="3"
                    placeholder="DTDC, Delhivery, BlueDart, Ekart, Xpressbees"><?php echo htmlspecialchars($settings['courier_partners'] ?? 'DTDC, Delhivery, BlueDart, Ekart'); ?></textarea>
          <small class="text-muted">These appear as autocomplete suggestions when updating order courier in the admin.</small>
        </div>
      </div>
    </div>

    <!-- Return Policy -->
    <div class="box box-default">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-undo"></i> Return Policy</h3>
      </div>
      <div class="box-body">
        <div class="form-group">
          <label>Return Window (days)</label>
          <input type="number" class="form-control" name="return_window_days"
                 value="<?php echo htmlspecialchars($settings['return_window_days'] ?? '7'); ?>"
                 step="1" min="0" placeholder="7">
          <small class="text-muted">Customers can request a return within this many days of delivery. Set 0 to disable returns.</small>
        </div>
        <div class="form-group">
          <label>Free Returns</label>
          <?php $free_ret = ($settings['free_returns'] ?? '1') == '1'; ?>
          <select class="form-control" name="free_returns">
            <option value="1" <?php echo $free_ret ? 'selected' : ''; ?>>Yes — seller bears return shipping</option>
            <option value="0" <?php echo !$free_ret ? 'selected' : ''; ?>>No — customer bears return shipping</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Summary Card -->
    <div class="box box-info">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-eye"></i> Current Summary</h3>
      </div>
      <div class="box-body" style="font-size:13px">
        <table class="table table-condensed table-borderless" style="margin:0">
          <tr>
            <td class="text-muted">Free shipping above</td>
            <td><strong>₹<?php echo htmlspecialchars($settings['free_shipping_above'] ?? '499'); ?></strong></td>
          </tr>
          <tr>
            <td class="text-muted">Standard charge</td>
            <td><strong>₹<?php echo htmlspecialchars($settings['standard_charge'] ?? '60'); ?></strong></td>
          </tr>
          <tr>
            <td class="text-muted">Express charge</td>
            <td><strong>₹<?php echo htmlspecialchars($settings['express_charge'] ?? '120'); ?></strong></td>
          </tr>
          <tr>
            <td class="text-muted">Estimated delivery</td>
            <td><strong><?php echo htmlspecialchars($settings['estimated_days'] ?? '3-5'); ?> days</strong></td>
          </tr>
          <tr>
            <td class="text-muted">COD</td>
            <td>
              <?php if (($settings['cod_enabled'] ?? '1') == '1'): ?>
                <span class="label label-success">Enabled</span>
                <?php if (!empty($settings['cod_surcharge']) && $settings['cod_surcharge'] > 0): ?>
                  <small class="text-muted">+₹<?php echo $settings['cod_surcharge']; ?> surcharge</small>
                <?php endif; ?>
              <?php else: ?>
                <span class="label label-danger">Disabled</span>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Return window</td>
            <td><strong><?php echo (int)($settings['return_window_days'] ?? 7); ?> days</strong></td>
          </tr>
        </table>
      </div>
    </div>

  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <button type="submit" class="btn btn-saffron btn-lg">
      <i class="fa fa-save"></i> Save All Settings
    </button>
  </div>
</div>
</form>

<script>
document.getElementById('codEnabledSelect') && document.getElementById('codEnabledSelect').addEventListener('change', function() {
  var lbl = document.getElementById('codStatusLabel');
  if (this.value === '1') {
    lbl.className = 'label label-success'; lbl.textContent = 'Enabled';
  } else {
    lbl.className = 'label label-default'; lbl.textContent = 'Disabled';
  }
});
</script>
