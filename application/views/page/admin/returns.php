<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-undo"></i> Return &amp; Cancel Requests</h3>
      </div>
      <div class="box-body">
        <!-- Filter bar -->
        <div class="row" style="margin-bottom:10px">
          <div class="col-sm-4">
            <div class="input-group input-group-sm">
              <span class="input-group-addon"><i class="fa fa-search"></i></span>
              <input type="text" class="form-control" id="ret_search" placeholder="Search order #, customer…">
              <span class="input-group-btn">
                <button type="button" class="btn btn-default" id="ret_clear" title="Clear"><i class="fa fa-times"></i></button>
              </span>
            </div>
          </div>
          <div class="col-sm-2 col-xs-6">
            <select class="form-control input-sm" id="ret_fType">
              <option value="">All Types</option>
              <option value="cancel">Cancel</option>
              <option value="return">Return</option>
            </select>
          </div>
          <div class="col-sm-2 col-xs-6">
            <select class="form-control input-sm" id="ret_fStatus">
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
              <option value="resolved">Resolved</option>
            </select>
          </div>
          <div class="col-sm-4" style="line-height:30px">
            <small class="text-muted" id="ret_count"></small>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-hover admin-table" id="ret_table">
            <thead>
              <tr><th>ID</th><th>Order</th><th>Customer</th><th>Type</th><th>Reason</th><th>Status</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php foreach ($returns as $r): ?>
              <tr>
                <td>#<?php echo $r['id']; ?></td>
                <td><strong>#<?php echo str_pad($r['order_id'],5,'0',STR_PAD_LEFT); ?></strong></td>
                <td>
                  <?php echo htmlspecialchars($r['customer_name']); ?><br>
                  <small class="text-muted"><?php echo htmlspecialchars($r['email']); ?></small>
                </td>
                <td>
                  <span class="label <?php echo $r['type']==='return' ? 'label-warning' : 'label-danger'; ?>">
                    <?php echo ucfirst($r['type']); ?>
                  </span>
                </td>
                <td><small><?php echo htmlspecialchars($this->spice_model->truncate_text($r['reason'], 60)); ?></small></td>
                <td>
                  <?php
                  $smap = array('pending'=>'warning','approved'=>'success','rejected'=>'danger','resolved'=>'primary');
                  $sc   = isset($smap[$r['status']]) ? $smap[$r['status']] : 'default';
                  ?>
                  <span class="label label-<?php echo $sc; ?>"><?php echo ucfirst($r['status']); ?></span>
                </td>
                <td><?php echo date('d M Y',strtotime($r['created_at'])); ?></td>
                <td>
                  <button class="btn btn-xs btn-primary" onclick='openReturnModal(<?php echo json_encode($r); ?>)'>
                    <i class="fa fa-eye"></i> Review
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($returns)): ?>
                <tr><td colspan="8" class="text-center text-muted">No return/cancel requests.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var rows   = Array.from(document.querySelectorAll('#ret_table tbody tr'));
  var search = document.getElementById('ret_search');
  var count  = document.getElementById('ret_count');
  function run() {
    var q  = search.value.trim().toLowerCase();
    var fT = document.getElementById('ret_fType').value.toLowerCase();
    var fS = document.getElementById('ret_fStatus').value.toLowerCase();
    var n = 0;
    rows.forEach(function (r) {
      if (r.cells.length < 2) { r.style.display = ''; return; }
      var type = r.cells[3] ? r.cells[3].textContent.trim().toLowerCase() : '';
      var stat = r.cells[5] ? r.cells[5].textContent.trim().toLowerCase() : '';
      var ok = (!q  || r.textContent.toLowerCase().indexOf(q) >= 0)
            && (!fT || type.indexOf(fT) >= 0)
            && (!fS || stat.indexOf(fS) >= 0);
      r.style.display = ok ? '' : 'none';
      if (ok) n++;
    });
    count.textContent = n + ' / ' + rows.length + ' requests';
  }
  search.addEventListener('input', run);
  document.getElementById('ret_clear').addEventListener('click', function () { search.value = ''; run(); });
  ['ret_fType','ret_fStatus'].forEach(function (id) { document.getElementById(id).addEventListener('change', run); });
})();
</script>

<!-- Return Review Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="<?php echo site_url('admin-returns'); ?>">
        <input type="hidden" name="update_return" value="1">
        <input type="hidden" name="return_id" id="returnId" value="">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Review Request</h4>
        </div>
        <div class="modal-body">
          <div class="callout callout-info" id="returnDetails"></div>
          <div class="form-group">
            <label>Update Status</label>
            <select class="form-control" name="ret_status" id="returnStatus">
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
              <option value="resolved">Resolved</option>
            </select>
          </div>
          <div class="form-group">
            <label>Admin Note</label>
            <textarea class="form-control" name="admin_note" id="returnNote" rows="3"
                      placeholder="Internal note or message to customer…"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-saffron">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openReturnModal(r) {
  document.getElementById('returnId').value     = r.id;
  document.getElementById('returnStatus').value = r.status;
  document.getElementById('returnNote').value   = r.admin_note || '';
  document.getElementById('returnDetails').innerHTML =
    '<strong>' + (r.type === 'return' ? '↩️ Return' : '❌ Cancellation') + ' Request</strong><br>' +
    '<small>Order #' + String(r.order_id).padStart(5,'0') +
    ' &middot; ' + r.customer_name + '</small><br>' +
    '<p class="margin-t-5 margin-b-0">' + r.reason + '</p>';
  $('#returnModal').modal('show');
}
</script>
