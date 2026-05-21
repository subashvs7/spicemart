<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-ticket"></i> Coupon Management</h3>
    <div class="box-tools pull-right">
      <button class="btn btn-sm btn-saffron" data-toggle="modal" data-target="#couponModal" onclick="resetCouponModal()">
        <i class="fa fa-plus"></i> Add Coupon
      </button>
    </div>
  </div>
  <div class="box-body">

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Filter bar -->
    <div class="row" style="margin-bottom:10px">
      <div class="col-sm-4">
        <div class="input-group input-group-sm">
          <span class="input-group-addon"><i class="fa fa-search"></i></span>
          <input type="text" class="form-control" id="coupon_search" placeholder="Search coupon code…">
          <span class="input-group-btn">
            <button type="button" class="btn btn-default" id="coupon_clear" title="Clear"><i class="fa fa-times"></i></button>
          </span>
        </div>
      </div>
      <div class="col-sm-2 col-xs-6">
        <select class="form-control input-sm" id="coupon_fType">
          <option value="">All Types</option>
          <option value="percent">Percent %</option>
          <option value="flat">Flat ₹</option>
        </select>
      </div>
      <div class="col-sm-2 col-xs-6">
        <select class="form-control input-sm" id="coupon_fStatus">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
      <div class="col-sm-4" style="line-height:30px">
        <small class="text-muted" id="coupon_count"></small>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover admin-table" id="coupon_table">
        <thead>
          <tr>
            <th>Code</th><th>Type</th><th>Value</th>
            <th>Min Order</th><th>Max Disc.</th>
            <th>Total Usage</th><th>Per User</th>
            <th>Restriction</th><th>Expires</th>
            <th>Status</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($coupons as $c): ?>
          <tr>
            <td><strong style="font-family:monospace;font-size:1.05rem"><?php echo htmlspecialchars($c['code']); ?></strong></td>
            <td>
              <span class="label <?php echo $c['type']==='percent' ? 'label-info' : 'label-warning'; ?>">
                <?php echo $c['type']==='percent' ? 'Percent %' : 'Flat ₹'; ?>
              </span>
            </td>
            <td>
              <?php echo $c['type']==='percent'
                ? htmlspecialchars($c['value']).'%'
                : '₹'.number_format((float)$c['value'],2); ?>
            </td>
            <td><?php echo $c['min_order'] > 0 ? '₹'.number_format((float)$c['min_order'],2) : '<span class="text-muted">—</span>'; ?></td>
            <td><?php echo $c['max_discount'] ? '₹'.number_format((float)$c['max_discount'],2) : '<span class="text-muted">—</span>'; ?></td>
            <td>
              <?php echo $c['uses_count']; ?>
              <?php echo $c['uses_limit'] ? '<span class="text-muted"> / '.$c['uses_limit'].'</span>' : '<span class="text-muted"> / ∞</span>'; ?>
            </td>
            <td>
              <?php echo $c['uses_per_user']
                ? '<span class="label label-default">'.$c['uses_per_user'].'x</span>'
                : '<span class="text-muted">∞</span>'; ?>
            </td>
            <td>
              <?php
              $rt_labels = array('all'=>array('default','All Users'),'staff'=>array('warning','Staff / Alumni'),'specific'=>array('primary','Specific'));
              $rt = $rt_labels[$c['restrict_to']] ?? array('default','All');
              ?>
              <span class="label label-<?php echo $rt[0]; ?>"><?php echo $rt[1]; ?></span>
            </td>
            <td>
              <?php if ($c['expires_at']): ?>
                <?php
                $exp = strtotime($c['expires_at']);
                $cls = $exp < time() ? 'text-red' : '';
                echo '<span class="'.$cls.'">'.date('d M Y',$exp).'</span>';
                ?>
              <?php else: ?>
                <span class="text-muted">Never</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="label <?php echo $c['status'] ? 'label-success' : 'label-default'; ?>">
                <?php echo $c['status'] ? 'Active' : 'Inactive'; ?>
              </span>
            </td>
            <td style="white-space:nowrap">
              <button class="btn btn-xs btn-primary" onclick='openEditCoupon(<?php echo htmlspecialchars(json_encode($c), ENT_QUOTES); ?>)'>
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
            <tr><td colspan="11" class="text-center text-muted">No coupons yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── Coupon Modal ──────────────────────────────────────────── -->
<div class="modal fade" id="couponModal" tabindex="-1" data-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" action="<?php echo site_url('admin-coupons'); ?>">
        <input type="hidden" name="coupon_id" id="couponId" value="0">

        <div class="modal-header" style="background:#2C1810;color:#fff;border-radius:4px 4px 0 0">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1">&times;</button>
          <h4 class="modal-title" id="couponModalTitle">Add Coupon</h4>
        </div>

        <div class="modal-body">
          <div class="nav-tabs-custom" style="margin-bottom:0">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#ctab-basic"    data-toggle="tab"><i class="fa fa-tag"></i> Basic</a></li>
              <li>              <a href="#ctab-rules"    data-toggle="tab"><i class="fa fa-filter"></i> Rules</a></li>
              <li>              <a href="#ctab-restrict" data-toggle="tab"><i class="fa fa-users"></i> Restrictions</a></li>
            </ul>

            <div class="tab-content" style="padding:18px 0 0">

              <!-- ── Tab 1: Basic ─────────────────────────────── -->
              <div class="tab-pane active" id="ctab-basic">
                <div class="row">
                  <div class="col-md-5">
                    <div class="form-group">
                      <label>Coupon Code <span class="text-danger">*</span></label>
                      <div class="input-group">
                        <input type="text" class="form-control" name="code" id="couponCode"
                               placeholder="e.g. SAVE20" style="text-transform:uppercase;font-family:monospace;font-size:1.1rem;font-weight:700"
                               required>
                        <span class="input-group-btn">
                          <button type="button" class="btn btn-default" id="genCodeBtn" title="Auto-generate code">
                            <i class="fa fa-magic"></i>
                          </button>
                        </span>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Discount Type <span class="text-danger">*</span></label>
                      <select class="form-control" name="type" id="couponType">
                        <option value="percent">Percent (%)</option>
                        <option value="flat">Flat Amount (₹)</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="form-group">
                      <label>Value <span class="text-danger">*</span></label>
                      <input type="number" class="form-control" name="value" id="couponValue"
                             step="0.01" min="0.01" required placeholder="e.g. 20">
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="form-group">
                      <label>Status</label>
                      <select class="form-control" name="status" id="couponStatus">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Max Discount (₹) <small class="text-muted">for % type</small></label>
                      <input type="number" class="form-control" name="max_discount" id="couponMaxDisc"
                             step="0.01" min="0" placeholder="Leave blank = no cap">
                      <small class="text-muted">e.g. 10% off but max ₹200 discount</small>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Expiry Date</label>
                      <input type="date" class="form-control" name="expires_at" id="couponExpiry">
                      <small class="text-muted">Leave blank = never expires</small>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Description <small class="text-muted">(internal)</small></label>
                      <input type="text" class="form-control" id="couponDesc"
                             placeholder="e.g. Summer sale 20% off">
                    </div>
                  </div>
                </div>
              </div>

              <!-- ── Tab 2: Rules ─────────────────────────────── -->
              <div class="tab-pane" id="ctab-rules">
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label><i class="fa fa-shopping-cart"></i> Min Cart Value (₹)</label>
                      <input type="number" class="form-control" name="min_order" id="couponMinOrder"
                             step="0.01" min="0" value="0" placeholder="0 = no minimum">
                      <small class="text-muted">Coupon only applies if cart total ≥ this amount.</small>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label><i class="fa fa-globe"></i> Total Usage Limit</label>
                      <input type="number" class="form-control" name="uses_limit" id="couponUsesLimit"
                             min="1" placeholder="Leave blank = unlimited">
                      <small class="text-muted">Max times the coupon can be used across all users.</small>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label><i class="fa fa-user"></i> Uses Per User</label>
                      <input type="number" class="form-control" name="uses_per_user" id="couponUsesPerUser"
                             min="1" placeholder="Leave blank = unlimited per user">
                      <small class="text-muted">Set to <strong>1</strong> for one-time use per customer.</small>
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div class="callout callout-info" style="margin-top:10px;font-size:13px">
                      <i class="fa fa-info-circle"></i>
                      <strong>Usage Rule Examples:</strong><br>
                      • <strong>Total Limit = 1, Per User = blank</strong> — One coupon for the first customer only (flash deal)<br>
                      • <strong>Total Limit = blank, Per User = 1</strong> — Each customer can use once, any number of customers<br>
                      • <strong>Total Limit = 100, Per User = 1</strong> — First 100 unique customers, each once
                    </div>
                  </div>
                </div>
              </div>

              <!-- ── Tab 3: Restrictions ──────────────────────── -->
              <div class="tab-pane" id="ctab-restrict">
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label><i class="fa fa-users"></i> Who can use this coupon?</label>
                      <select class="form-control" name="restrict_to" id="couponRestrictTo">
                        <option value="all">All Users — anyone with the code</option>
                        <option value="staff">Staff / Alumni only — users with Staff or Admin role</option>
                        <option value="specific">Specific Users — email allow-list below</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-12" id="allowedEmailsWrap" style="display:none">
                    <div class="form-group">
                      <label><i class="fa fa-envelope"></i> Allowed Email Addresses</label>
                      <textarea class="form-control" name="allowed_emails" id="allowedEmails"
                                rows="6"
                                placeholder="One email per line, e.g.:&#10;john@example.com&#10;jane@example.com&#10;alumni@college.edu"></textarea>
                      <small class="text-muted">
                        <i class="fa fa-info-circle"></i>
                        One email per line. Only these users will be able to apply the coupon.
                        Used for alumni vouchers, staff perks, or personal discount codes.
                      </small>
                    </div>
                    <div class="alert alert-info" style="font-size:12px;padding:8px 12px">
                      <i class="fa fa-lightbulb-o"></i>
                      <strong>Tip:</strong> Users must be registered with exactly these email addresses.
                      The coupon will be rejected for any account not in this list.
                    </div>
                  </div>
                </div>
              </div>

            </div><!-- /tab-content -->
          </div><!-- /nav-tabs-custom -->
        </div><!-- /modal-body -->

        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">
            <i class="fa fa-times"></i> Cancel
          </button>
          <button type="submit" class="btn btn-saffron">
            <i class="fa fa-save"></i> <span id="couponSaveLabel">Save Coupon</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
/* ── Table filter ── */
(function () {
  var rows   = Array.from(document.querySelectorAll('#coupon_table tbody tr'));
  var search = document.getElementById('coupon_search');
  var count  = document.getElementById('coupon_count');
  function run() {
    var q  = search.value.trim().toLowerCase();
    var fT = document.getElementById('coupon_fType').value.toLowerCase();
    var fS = document.getElementById('coupon_fStatus').value.toLowerCase();
    var n = 0;
    rows.forEach(function (r) {
      if (r.cells.length < 2) { r.style.display = ''; return; }
      var type = r.cells[1] ? r.cells[1].textContent.trim().toLowerCase() : '';
      var stat = r.cells[9] ? r.cells[9].textContent.trim().toLowerCase() : '';
      var ok = (!q || r.textContent.toLowerCase().indexOf(q) >= 0)
            && (!fT || type.indexOf(fT) >= 0)
            && (!fS || stat.indexOf(fS) >= 0);
      r.style.display = ok ? '' : 'none';
      if (ok) n++;
    });
    count.textContent = n + ' / ' + rows.length + ' coupons';
  }
  search.addEventListener('input', run);
  document.getElementById('coupon_clear').addEventListener('click', function () { search.value = ''; run(); });
  ['coupon_fType','coupon_fStatus'].forEach(function (id) { document.getElementById(id).addEventListener('change', run); });
})();

/* ── Restriction toggle ── */
document.getElementById('couponRestrictTo').addEventListener('change', function () {
  document.getElementById('allowedEmailsWrap').style.display = this.value === 'specific' ? 'block' : 'none';
});

/* ── Auto-generate code ── */
document.getElementById('genCodeBtn').addEventListener('click', function () {
  var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  var code  = '';
  for (var i = 0; i < 8; i++) code += chars.charAt(Math.floor(Math.random() * chars.length));
  document.getElementById('couponCode').value = code;
});

/* ── Reset modal to Add mode ── */
function resetCouponModal() {
  document.getElementById('couponModalTitle').textContent = 'Add Coupon';
  document.getElementById('couponSaveLabel').textContent  = 'Save Coupon';
  document.getElementById('couponId').value          = '0';
  document.getElementById('couponCode').value         = '';
  document.getElementById('couponType').value         = 'percent';
  document.getElementById('couponValue').value        = '';
  document.getElementById('couponStatus').value       = '1';
  document.getElementById('couponMaxDisc').value      = '';
  document.getElementById('couponExpiry').value       = '';
  document.getElementById('couponDesc').value         = '';
  document.getElementById('couponMinOrder').value     = '0';
  document.getElementById('couponUsesLimit').value    = '';
  document.getElementById('couponUsesPerUser').value  = '';
  document.getElementById('couponRestrictTo').value   = 'all';
  document.getElementById('allowedEmails').value      = '';
  document.getElementById('allowedEmailsWrap').style.display = 'none';
  /* Reset to first tab */
  document.querySelector('#couponModal .nav-tabs a[href="#ctab-basic"]').click();
}

/* ── Open edit modal ── */
function openEditCoupon(c) {
  document.getElementById('couponModalTitle').textContent = 'Edit Coupon — ' + c.code;
  document.getElementById('couponSaveLabel').textContent  = 'Update Coupon';
  document.getElementById('couponId').value          = c.id;
  document.getElementById('couponCode').value         = c.code;
  document.getElementById('couponType').value         = c.type;
  document.getElementById('couponValue').value        = c.value;
  document.getElementById('couponStatus').value       = c.status;
  document.getElementById('couponMaxDisc').value      = c.max_discount || '';
  document.getElementById('couponExpiry').value       = c.expires_at ? c.expires_at.substring(0,10) : '';
  document.getElementById('couponMinOrder').value     = c.min_order || '0';
  document.getElementById('couponUsesLimit').value    = c.uses_limit || '';
  document.getElementById('couponUsesPerUser').value  = c.uses_per_user || '';
  document.getElementById('couponRestrictTo').value   = c.restrict_to || 'all';
  document.getElementById('allowedEmails').value      = c.allowed_emails || '';
  document.getElementById('allowedEmailsWrap').style.display = (c.restrict_to === 'specific') ? 'block' : 'none';
  document.querySelector('#couponModal .nav-tabs a[href="#ctab-basic"]').click();
  $('#couponModal').modal('show');
}
</script>
