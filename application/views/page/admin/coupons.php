<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title">Coupon Management</h3>
    <div class="box-tools pull-right">
      <button class="btn btn-sm btn-saffron" data-toggle="modal" data-target="#couponModal" onclick="resetCouponModal()">
        <i class="fa fa-plus"></i> Add Coupon
      </button>
    </div>
  </div>
  <div class="box-body">

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><ul><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table table-bordered table-hover admin-table">
        <thead>
          <tr><th>Code</th><th>Type</th><th>Value</th><th>Min Order</th><th>Max Disc.</th><th>Usage</th><th>Expires</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($coupons as $c): ?>
          <tr>
            <td><strong style="font-family:monospace;font-size:1.05rem"><?php echo htmlspecialchars($c['code']); ?></strong></td>
            <td>
              <span class="label <?php echo $c['type']==='percent' ? 'label-info' : 'label-warning'; ?>">
                <?php echo $c['type'] === 'percent' ? 'Percent %' : 'Flat ₹'; ?>
              </span>
            </td>
            <td>
              <?php echo $c['type']==='percent'
                ? htmlspecialchars($c['value']).'%'
                : '₹'.number_format((float)$c['value'],2); ?>
            </td>
            <td><?php echo $c['min_order'] > 0 ? '₹'.number_format((float)$c['min_order'],2) : '—'; ?></td>
            <td><?php echo $c['max_discount'] ? '₹'.number_format((float)$c['max_discount'],2) : '—'; ?></td>
            <td><?php echo $c['uses_count']; ?><?php echo $c['uses_limit'] ? ' / '.$c['uses_limit'] : ' / ∞'; ?></td>
            <td><?php echo $c['expires_at'] ? date('d M Y',strtotime($c['expires_at'])) : 'Never'; ?></td>
            <td>
              <span class="label <?php echo $c['status'] ? 'label-success' : 'label-default'; ?>">
                <?php echo $c['status'] ? 'Active' : 'Inactive'; ?>
              </span>
            </td>
            <td>
              <button class="btn btn-xs btn-primary" onclick='openEditCoupon(<?php echo json_encode($c); ?>)'>
                <i class="fa fa-pencil"></i>
              </button>
              <a href="<?php echo site_url('admin-coupons'); ?>?action=delete&edit=<?php echo $c['id']; ?>"
                 class="btn btn-xs btn-danger" onclick="return confirm('Delete this coupon?')">
                <i class="fa fa-trash"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($coupons)): ?>
            <tr><td colspan="9" class="text-center text-muted">No coupons yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Coupon Modal -->
<div class="modal fade" id="couponModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" action="<?php echo site_url('admin-coupons'); ?>">
        <input type="hidden" name="coupon_id" id="couponId" value="0">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="couponModalTitle">Add Coupon</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Coupon Code *</label>
                <input type="text" class="form-control" name="code" id="couponCode"
                       placeholder="e.g. SAVE20" style="text-transform:uppercase" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Discount Type *</label>
                <select class="form-control" name="type" id="couponType">
                  <option value="percent">Percent (%)</option>
                  <option value="flat">Flat Amount (₹)</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Value *</label>
                <input type="number" class="form-control" name="value" id="couponValue"
                       step="0.01" min="0.01" required placeholder="e.g. 10 or 50">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Min Order Amount (₹)</label>
                <input type="number" class="form-control" name="min_order" id="couponMinOrder"
                       step="0.01" min="0" value="0" placeholder="0 = no minimum">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Max Discount (₹) <small class="text-muted">for % type</small></label>
                <input type="number" class="form-control" name="max_discount" id="couponMaxDisc"
                       step="0.01" min="0" placeholder="Leave blank = no cap">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Usage Limit</label>
                <input type="number" class="form-control" name="uses_limit" id="couponUsesLimit"
                       min="0" placeholder="Leave blank = unlimited">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Expiry Date</label>
                <input type="date" class="form-control" name="expires_at" id="couponExpiry">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Status</label>
                <select class="form-control" name="status" id="couponStatus">
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-saffron">Save Coupon</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function resetCouponModal() {
  document.getElementById('couponModalTitle').textContent = 'Add Coupon';
  document.getElementById('couponId').value         = '0';
  document.getElementById('couponCode').value        = '';
  document.getElementById('couponType').value        = 'percent';
  document.getElementById('couponValue').value       = '';
  document.getElementById('couponMinOrder').value    = '0';
  document.getElementById('couponMaxDisc').value     = '';
  document.getElementById('couponUsesLimit').value   = '';
  document.getElementById('couponExpiry').value      = '';
  document.getElementById('couponStatus').value      = '1';
}
function openEditCoupon(c) {
  document.getElementById('couponModalTitle').textContent = 'Edit Coupon';
  document.getElementById('couponId').value         = c.id;
  document.getElementById('couponCode').value        = c.code;
  document.getElementById('couponType').value        = c.type;
  document.getElementById('couponValue').value       = c.value;
  document.getElementById('couponMinOrder').value    = c.min_order || '0';
  document.getElementById('couponMaxDisc').value     = c.max_discount || '';
  document.getElementById('couponUsesLimit').value   = c.uses_limit || '';
  document.getElementById('couponExpiry').value      = c.expires_at ? c.expires_at.substring(0,10) : '';
  document.getElementById('couponStatus').value      = c.status;
  $('#couponModal').modal('show');
}
</script>
