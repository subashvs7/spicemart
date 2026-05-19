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
        <div class="table-responsive">
          <table class="table table-bordered table-hover admin-table">
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
