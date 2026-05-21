<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php
$s = $stats;
$total   = (int)($s['total']   ?? 0);
$pending = (int)($s['pending'] ?? 0);
$approved= (int)($s['approved']?? 0);
$rejected= (int)($s['rejected']?? 0);
$resolved= (int)($s['resolved']?? 0);
$t_returns = (int)($s['type_returns']  ?? 0);
$t_cancels  = (int)($s['type_cancels'] ?? 0);
$tot_amt    = (float)($s['total_amount']    ?? 0);
$app_amt    = (float)($s['approved_amount'] ?? 0);
$today_cnt  = (int)($s['today_count'] ?? 0);
?>

<!-- ── Stats Row ──────────────────────────────────────────── -->
<div class="row" style="margin-bottom:16px">
  <div class="col-sm-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-yellow"><i class="fa fa-clock-o"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Pending</span>
        <span class="info-box-number"><?php echo $pending; ?></span>
        <div class="progress"><div class="progress-bar" style="width:<?php echo $total ? round($pending/$total*100) : 0; ?>%"></div></div>
        <span class="progress-description"><?php echo $today_cnt; ?> new today</span>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Approved</span>
        <span class="info-box-number"><?php echo $approved; ?></span>
        <div class="progress"><div class="progress-bar bg-green" style="width:<?php echo $total ? round($approved/$total*100) : 0; ?>%"></div></div>
        <span class="progress-description">&#8377;<?php echo number_format($app_amt, 0); ?> refund value</span>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-red"><i class="fa fa-times"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Rejected</span>
        <span class="info-box-number"><?php echo $rejected; ?></span>
        <div class="progress"><div class="progress-bar bg-red" style="width:<?php echo $total ? round($rejected/$total*100) : 0; ?>%"></div></div>
        <span class="progress-description"><?php echo $resolved; ?> resolved</span>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-aqua"><i class="fa fa-undo"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total Requests</span>
        <span class="info-box-number"><?php echo $total; ?></span>
        <div class="progress"><div class="progress-bar bg-aqua" style="width:100%"></div></div>
        <span class="progress-description"><?php echo $t_returns; ?> returns &middot; <?php echo $t_cancels; ?> cancels</span>
      </div>
    </div>
  </div>
</div>

<div class="row">

  <!-- ── Main Table ────────────────────────────────────────── -->
  <div class="col-md-9">
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-undo"></i> Return &amp; Cancel Requests</h3>
        <div class="box-tools pull-right">
          <a href="<?php echo site_url('admin-returns'); ?>" class="btn btn-xs btn-default" title="Reset filters">
            <i class="fa fa-refresh"></i> Reset
          </a>
        </div>
      </div>
      <div class="box-body">

        <!-- Filter bar -->
        <form method="get" action="<?php echo site_url('admin-returns'); ?>" class="margin-b-10">
          <div class="row">
            <div class="col-sm-3">
              <div class="input-group input-group-sm">
                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                <input type="text" class="form-control" id="ret_search" placeholder="Order #, customer…">
              </div>
            </div>
            <div class="col-sm-2">
              <select class="form-control input-sm" name="type">
                <option value="">All Types</option>
                <option value="cancel" <?php echo $filter_type==='cancel'?'selected':''; ?>>Cancel</option>
                <option value="return" <?php echo $filter_type==='return'?'selected':''; ?>>Return</option>
              </select>
            </div>
            <div class="col-sm-2">
              <select class="form-control input-sm" name="status">
                <option value="">All Status</option>
                <?php foreach (array('pending','approved','rejected','resolved') as $st): ?>
                  <option value="<?php echo $st; ?>" <?php echo $filter_status===$st?'selected':''; ?>><?php echo ucfirst($st); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-sm-2">
              <input type="date" class="form-control input-sm" name="date_from"
                     value="<?php echo htmlspecialchars($date_from); ?>" placeholder="From">
            </div>
            <div class="col-sm-2">
              <input type="date" class="form-control input-sm" name="date_to"
                     value="<?php echo htmlspecialchars($date_to); ?>" placeholder="To">
            </div>
            <div class="col-sm-1">
              <button type="submit" class="btn btn-sm btn-saffron btn-block"><i class="fa fa-filter"></i></button>
            </div>
          </div>
        </form>

        <!-- Bulk action bar -->
        <form method="post" action="<?php echo site_url('admin-returns'); ?>" id="bulkForm">
          <input type="hidden" name="bulk_action" value="1">
          <div class="margin-b-10" id="bulkBar" style="display:none">
            <span class="text-muted small" id="bulkCount"></span>
            <select class="form-control input-sm" name="bulk_status" style="display:inline-block;width:120px;margin:0 6px">
              <?php foreach (array('pending','approved','rejected','resolved') as $st): ?>
                <option value="<?php echo $st; ?>"><?php echo ucfirst($st); ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Apply to Selected</button>
            <button type="button" class="btn btn-sm btn-default" id="bulkClear">Clear</button>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered table-hover admin-table" id="ret_table">
              <thead>
                <tr>
                  <th style="width:30px"><input type="checkbox" id="selectAll"></th>
                  <th>ID</th>
                  <th>Order</th>
                  <th>Customer</th>
                  <th>Type</th>
                  <th>Reason</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($returns)): ?>
                  <tr><td colspan="10" class="text-center text-muted">No requests match the current filters.</td></tr>
                <?php endif; ?>
                <?php foreach ($returns as $r):
                  $smap = array('pending'=>'warning','approved'=>'success','rejected'=>'danger','resolved'=>'primary');
                  $sc   = $smap[$r['status']] ?? 'default';
                  $items_json = json_encode($order_items_map[$r['order_id']] ?? array());
                ?>
                <tr data-ret-id="<?php echo $r['id']; ?>">
                  <td><input type="checkbox" name="bulk_ids[]" value="<?php echo $r['id']; ?>" class="bulk-cb"></td>
                  <td>#<?php echo $r['id']; ?></td>
                  <td>
                    <strong>#<?php echo str_pad($r['order_id'],5,'0',STR_PAD_LEFT); ?></strong><br>
                    <small class="text-muted"><?php echo strtoupper($r['payment_method'] ?? ''); ?></small>
                  </td>
                  <td>
                    <?php echo htmlspecialchars($r['customer_name']); ?><br>
                    <small class="text-muted"><?php echo htmlspecialchars($r['email']); ?></small>
                  </td>
                  <td>
                    <span class="label <?php echo $r['type']==='return' ? 'label-warning' : 'label-danger'; ?>">
                      <?php echo $r['type']==='return' ? '↩ Return' : '✕ Cancel'; ?>
                    </span>
                  </td>
                  <td><small><?php echo htmlspecialchars($this->spice_model->truncate_text($r['reason'], 55)); ?></small></td>
                  <td class="text-saffron"><strong><?php echo $this->spice_model->rupees((float)$r['total_amount']); ?></strong></td>
                  <td><span class="label label-<?php echo $sc; ?>"><?php echo ucfirst($r['status']); ?></span></td>
                  <td><?php echo date('d M Y', strtotime($r['created_at'])); ?><br>
                      <small class="text-muted"><?php echo date('h:i A', strtotime($r['created_at'])); ?></small></td>
                  <td>
                    <button type="button" class="btn btn-xs btn-primary"
                      onclick='openReturnModal(<?php echo json_encode($r); ?>, <?php echo $items_json; ?>)'>
                      <i class="fa fa-eye"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <small class="text-muted" id="ret_count"></small>
          </div>
        </form>

      </div>
    </div>
  </div>

  <!-- ── Sidebar: Analytics ────────────────────────────────── -->
  <div class="col-md-3">

    <!-- Type breakdown -->
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-pie-chart"></i> Breakdown</h3>
      </div>
      <div class="box-body" style="padding-bottom:8px">
        <div class="clearfix margin-b-5">
          <span class="pull-left">Returns</span>
          <span class="pull-right text-warning"><strong><?php echo $t_returns; ?></strong></span>
        </div>
        <div class="progress progress-xs">
          <div class="progress-bar bg-yellow" style="width:<?php echo $total ? round($t_returns/$total*100) : 0; ?>%"></div>
        </div>
        <div class="clearfix margin-b-5">
          <span class="pull-left">Cancellations</span>
          <span class="pull-right text-danger"><strong><?php echo $t_cancels; ?></strong></span>
        </div>
        <div class="progress progress-xs">
          <div class="progress-bar bg-red" style="width:<?php echo $total ? round($t_cancels/$total*100) : 0; ?>%"></div>
        </div>
        <hr style="margin:8px 0">
        <div class="clearfix">
          <span class="pull-left text-muted small">Total order value</span>
          <span class="pull-right"><strong><?php echo $this->spice_model->rupees($tot_amt); ?></strong></span>
        </div>
        <div class="clearfix">
          <span class="pull-left text-muted small">Approved refund value</span>
          <span class="pull-right text-success"><strong><?php echo $this->spice_model->rupees($app_amt); ?></strong></span>
        </div>
        <?php if ($total > 0): ?>
        <div class="clearfix">
          <span class="pull-left text-muted small">Approval rate</span>
          <span class="pull-right"><strong><?php echo round($approved/$total*100); ?>%</strong></span>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Top reasons -->
    <div class="box box-warning">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-comment"></i> Top Reasons</h3>
      </div>
      <div class="box-body" style="padding-bottom:8px">
        <?php if (empty($top_reasons)): ?>
          <p class="text-muted text-center">No data</p>
        <?php else: ?>
          <?php foreach ($top_reasons as $i => $tr): ?>
          <div class="clearfix margin-b-5">
            <span class="pull-left" style="max-width:75%;word-break:break-word">
              <small><?php echo htmlspecialchars($this->spice_model->truncate_text($tr['reason_text'], 50)); ?></small>
            </span>
            <span class="pull-right">
              <span class="badge bg-yellow"><?php echo $tr['cnt']; ?></span>
            </span>
          </div>
          <?php if ($i < count($top_reasons)-1): ?><hr style="margin:4px 0"><?php endif; ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Quick filter shortcuts -->
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-filter"></i> Quick Filters</h3>
      </div>
      <div class="box-body" style="padding:8px">
        <?php
        $qfilters = array(
          array('label'=>'All Pending',  'url'=>'admin-returns?status=pending',  'class'=>'warning'),
          array('label'=>'All Returns',  'url'=>'admin-returns?type=return',     'class'=>'default'),
          array('label'=>'All Cancels',  'url'=>'admin-returns?type=cancel',     'class'=>'default'),
          array('label'=>'Approved',     'url'=>'admin-returns?status=approved', 'class'=>'success'),
          array('label'=>'Rejected',     'url'=>'admin-returns?status=rejected', 'class'=>'danger'),
          array('label'=>'Today',        'url'=>'admin-returns?date_from='.date('Y-m-d').'&date_to='.date('Y-m-d'), 'class'=>'info'),
        );
        foreach ($qfilters as $qf):
        ?>
          <a href="<?php echo site_url($qf['url']); ?>"
             class="btn btn-xs btn-<?php echo $qf['class']; ?>"
             style="margin:2px"><?php echo $qf['label']; ?></a>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>

<!-- ── Review Modal ─────────────────────────────────────────── -->
<div class="modal fade" id="returnModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="returnUpdateForm" method="post" action="<?php echo site_url('ajax/returns-update'); ?>">
        <input type="hidden" name="return_id" id="returnId" value="">
        <div class="modal-header" style="border-bottom:2px solid #f39c12">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"><i class="fa fa-undo text-warning"></i> Review Request</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <!-- Left: request details -->
            <div class="col-md-7">
              <div class="callout callout-info" id="returnDetails" style="margin-bottom:12px"></div>

              <!-- Order items -->
              <h5 style="margin-top:0">Order Items</h5>
              <div id="returnItems" class="table-responsive">
                <p class="text-muted">Loading…</p>
              </div>
            </div>

            <!-- Right: action panel -->
            <div class="col-md-5">
              <div class="box box-solid" style="margin-bottom:0">
                <div class="box-header with-border">
                  <h3 class="box-title">Update Status</h3>
                </div>
                <div class="box-body">
                  <!-- Quick action buttons -->
                  <div class="margin-b-10">
                    <button type="button" class="btn btn-success btn-sm" onclick="setReturnStatus('approved')">
                      <i class="fa fa-check"></i> Approve
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="setReturnStatus('rejected')">
                      <i class="fa fa-times"></i> Reject
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="setReturnStatus('resolved')">
                      <i class="fa fa-check-circle"></i> Resolve
                    </button>
                  </div>
                  <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" name="ret_status" id="returnStatus">
                      <option value="pending">Pending</option>
                      <option value="approved">Approved</option>
                      <option value="rejected">Rejected</option>
                      <option value="resolved">Resolved</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Admin Note <small class="text-muted">(visible to you)</small></label>
                    <textarea class="form-control" name="admin_note" id="returnNote" rows="4"
                              placeholder="Reason for decision, refund instructions…"></textarea>
                  </div>
                  <div class="callout callout-warning" id="approveWarning" style="display:none;padding:6px 10px">
                    <small><i class="fa fa-info-circle"></i>
                      Approving a <strong>return</strong> will restore stock and mark the order as returned.
                    </small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <a id="modalOrderLink" href="#" target="_blank" class="btn btn-default btn-sm">
            <i class="fa fa-external-link"></i> View Order
          </a>
          <a id="modalInvoiceLink" href="#" target="_blank" class="btn btn-default btn-sm">
            <i class="fa fa-file-text"></i> Invoice
          </a>
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-saffron"><i class="fa fa-save"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
/* ── Client-side search filter ── */
(function () {
  var rows   = Array.from(document.querySelectorAll('#ret_table tbody tr'));
  var search = document.getElementById('ret_search');
  var count  = document.getElementById('ret_count');
  function run() {
    var q  = (search.value || '').trim().toLowerCase();
    var n = 0;
    rows.forEach(function (r) {
      if (r.cells.length < 4) { r.style.display=''; return; }
      var ok = !q || r.textContent.toLowerCase().indexOf(q) >= 0;
      r.style.display = ok ? '' : 'none';
      if (ok) n++;
    });
    if (count) count.textContent = n + ' of ' + rows.length + ' shown';
  }
  if (search) { search.addEventListener('input', run); run(); }
})();

/* ── Bulk select ── */
(function () {
  var all = document.getElementById('selectAll');
  var bar = document.getElementById('bulkBar');
  var cnt = document.getElementById('bulkCount');
  function updateBar() {
    var checked = document.querySelectorAll('.bulk-cb:checked');
    if (checked.length > 0) {
      bar.style.display = '';
      cnt.textContent = checked.length + ' selected — ';
    } else {
      bar.style.display = 'none';
    }
  }
  if (all) {
    all.addEventListener('change', function () {
      document.querySelectorAll('.bulk-cb').forEach(function (cb) { cb.checked = all.checked; });
      updateBar();
    });
  }
  document.addEventListener('change', function (e) {
    if (e.target && e.target.classList.contains('bulk-cb')) updateBar();
  });
  var clear = document.getElementById('bulkClear');
  if (clear) clear.addEventListener('click', function () {
    document.querySelectorAll('.bulk-cb').forEach(function (cb) { cb.checked = false; });
    if (all) all.checked = false;
    updateBar();
  });
})();

/* ── Return modal — AJAX submit ── */
$(function () {
  $('#returnUpdateForm').on('submit', function (e) {
    e.preventDefault();
    var $btn = $(this).find('button[type=submit]');
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving…');

    $.post($(this).attr('action'), $(this).serialize())
      .done(function (data) {
        $('#returnModal').modal('hide');
        smAlert(data.message, data.success ? 'success' : 'danger');

        if (data.success) {
          /* Update the status badge in the table row */
          var sMap = { pending:'warning', approved:'success', rejected:'danger', resolved:'primary' };
          var cls  = sMap[data.new_status] || 'default';
          var label = data.new_status.charAt(0).toUpperCase() + data.new_status.slice(1);
          $('tr[data-ret-id="' + data.ret_id + '"]').find('.label').filter(function () {
            return /label-(warning|success|danger|primary|default)/.test(this.className);
          }).first().attr('class', 'label label-' + cls).text(label);
        }
      })
      .fail(function () { smAlert('Server error. Please try again.', 'danger'); })
      .always(function () { $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save'); });
  });
});

function setReturnStatus(val) {
  document.getElementById('returnStatus').value = val;
  var w = document.getElementById('approveWarning');
  if (w) w.style.display = (val === 'approved') ? '' : 'none';
}

function openReturnModal(r, items) {
  document.getElementById('returnId').value     = r.id;
  document.getElementById('returnStatus').value = r.status;
  document.getElementById('returnNote').value   = r.admin_note || '';

  var typeLabel  = r.type === 'return' ? '↩ Return Request' : '✕ Cancellation Request';
  var statusMap  = {pending:'warning', approved:'success', rejected:'danger', resolved:'primary'};
  var statusBadge = '<span class="label label-' + (statusMap[r.status] || 'default') + '">' + r.status.charAt(0).toUpperCase() + r.status.slice(1) + '</span>';

  document.getElementById('returnDetails').innerHTML =
    '<strong>' + typeLabel + '</strong> &nbsp;' + statusBadge + '<br>' +
    '<small class="text-muted">Order <strong>#' + String(r.order_id).padStart(5,'0') + '</strong>' +
    (r.payment_method ? ' &middot; ' + r.payment_method.toUpperCase() : '') +
    ' &middot; <strong>&#8377;' + parseFloat(r.total_amount).toLocaleString('en-IN',{minimumFractionDigits:2}) + '</strong>' +
    '<br>' + r.customer_name + (r.email ? ' &middot; ' + r.email : '') +
    (r.phone ? ' &middot; ' + r.phone : '') + '</small>' +
    '<p class="margin-t-5 margin-b-0">' + r.reason + '</p>';

  // Order items table
  var itemHtml = '';
  if (items && items.length > 0) {
    itemHtml = '<table class="table table-condensed table-bordered" style="margin:0">' +
      '<thead><tr><th>Product</th><th>Variant</th><th style="text-align:right">Qty</th><th style="text-align:right">Price</th></tr></thead><tbody>';
    items.forEach(function (it) {
      itemHtml += '<tr>' +
        '<td><small>' + it.product_name + '</small></td>' +
        '<td><small class="text-muted">' + (it.variant_label || '—') + '</small></td>' +
        '<td style="text-align:right"><small>' + it.quantity + '</small></td>' +
        '<td style="text-align:right"><small>&#8377;' + parseFloat(it.unit_price).toLocaleString('en-IN',{minimumFractionDigits:2}) + '</small></td>' +
        '</tr>';
    });
    itemHtml += '</tbody></table>';
  } else {
    itemHtml = '<p class="text-muted">No items found.</p>';
  }
  document.getElementById('returnItems').innerHTML = itemHtml;

  // Approve warning for returns
  var w = document.getElementById('approveWarning');
  if (w) w.style.display = (r.type === 'return' && r.status !== 'approved') ? '' : 'none';

  // Order/invoice links
  var ol = document.getElementById('modalOrderLink');
  var il = document.getElementById('modalInvoiceLink');
  if (ol) ol.href = '<?php echo site_url('admin-orders'); ?>?view=' + r.order_id;
  if (il) il.href = '<?php echo site_url('invoice'); ?>/' + r.order_id;

  $('#returnModal').modal('show');
}
</script>
