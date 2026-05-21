<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title">Why Choose Us</h3>
    <div class="box-tools pull-right">
      <button class="btn btn-sm btn-saffron" data-toggle="modal" data-target="#whyModal" onclick="resetWhyModal()">
        <i class="fa fa-plus"></i> Add Item
      </button>
    </div>
  </div>
  <div class="box-body">

    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Filter bar -->
    <div class="row" style="margin-bottom:10px">
      <div class="col-sm-5">
        <div class="input-group input-group-sm">
          <span class="input-group-addon"><i class="fa fa-search"></i></span>
          <input type="text" class="form-control" id="why_search" placeholder="Search title, description…">
          <span class="input-group-btn">
            <button type="button" class="btn btn-default" id="why_clear" title="Clear"><i class="fa fa-times"></i></button>
          </span>
        </div>
      </div>
      <div class="col-sm-2 col-xs-6">
        <select class="form-control input-sm" id="why_fStatus">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="hidden">Hidden</option>
        </select>
      </div>
      <div class="col-sm-5" style="line-height:30px">
        <small class="text-muted" id="why_count"></small>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover admin-table" id="why_table">
        <thead>
          <tr><th style="width:60px">Icon</th><th>Title</th><th>Description</th><th style="width:70px">Order</th><th style="width:80px">Status</th><th style="width:80px">Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
          <tr>
            <td style="font-size:1.4rem;text-align:center"><?php echo htmlspecialchars($item['icon']); ?></td>
            <td><strong><?php echo htmlspecialchars($item['title']); ?></strong></td>
            <td><?php echo htmlspecialchars($item['description']); ?></td>
            <td><?php echo (int)$item['sort_order']; ?></td>
            <td>
              <span class="label <?php echo $item['status'] ? 'label-success' : 'label-default'; ?>">
                <?php echo $item['status'] ? 'Active' : 'Hidden'; ?>
              </span>
            </td>
            <td>
              <button class="btn btn-xs btn-primary" onclick='openEditWhy(<?php echo json_encode($item); ?>)'>
                <i class="fa fa-pencil"></i>
              </button>
              <a href="<?php echo site_url('admin-why-choose-us'); ?>?action=delete&edit=<?php echo $item['id']; ?>"
                 class="btn btn-xs btn-danger" onclick="return confirm('Delete this item?')">
                <i class="fa fa-trash"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($items)): ?>
            <tr><td colspan="6" class="text-center text-muted">No items yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
(function () {
  var rows   = Array.from(document.querySelectorAll('#why_table tbody tr'));
  var search = document.getElementById('why_search');
  var count  = document.getElementById('why_count');
  function run() {
    var q  = search.value.trim().toLowerCase();
    var fS = document.getElementById('why_fStatus').value.toLowerCase();
    var n = 0;
    rows.forEach(function (r) {
      if (r.cells.length < 2) { r.style.display = ''; return; }
      var stat = r.cells[4] ? r.cells[4].textContent.trim().toLowerCase() : '';
      var ok = (!q || r.textContent.toLowerCase().indexOf(q) >= 0) && (!fS || stat.indexOf(fS) >= 0);
      r.style.display = ok ? '' : 'none';
      if (ok) n++;
    });
    count.textContent = n + ' / ' + rows.length + ' items';
  }
  search.addEventListener('input', run);
  document.getElementById('why_clear').addEventListener('click', function () { search.value = ''; run(); });
  document.getElementById('why_fStatus').addEventListener('change', run);
})();
</script>

<!-- Modal -->
<div class="modal fade" id="whyModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="<?php echo site_url('admin-why-choose-us'); ?>">
        <input type="hidden" name="item_id" id="whyItemId" value="<?php echo isset($form_data['id']) ? (int)$form_data['id'] : 0; ?>">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="whyModalTitle">
            <?php echo (!empty($form_data) && $form_data['id']) ? 'Edit Item' : 'Add Why Choose Us Item'; ?>
          </h4>
        </div>
        <div class="modal-body">

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" style="margin-bottom:15px">
              <ul class="mb-0" style="padding-left:18px">
                <?php foreach($errors as $e): ?>
                  <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label>Icon (emoji)</label>
                <input type="text" class="form-control" name="icon" id="whyIcon"
                       value="<?php echo isset($form_data['icon']) ? htmlspecialchars($form_data['icon']) : '🌿'; ?>"
                       maxlength="10" style="font-size:1.3rem">
              </div>
            </div>
            <div class="col-md-9">
              <div class="form-group <?php echo (!empty($errors) && empty($form_data['title'])) ? 'has-error' : ''; ?>">
                <label>Title *</label>
                <input type="text" class="form-control" name="title" id="whyTitle"
                       value="<?php echo isset($form_data['title']) ? htmlspecialchars($form_data['title']) : ''; ?>"
                       required>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group <?php echo (!empty($errors) && empty($form_data['description'])) ? 'has-error' : ''; ?>">
                <label>Description *</label>
                <textarea class="form-control" name="description" id="whyDescription" rows="3" required><?php echo isset($form_data['description']) ? htmlspecialchars($form_data['description']) : ''; ?></textarea>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Sort Order</label>
                <input type="number" class="form-control" name="sort_order" id="whySortOrder"
                       value="<?php echo isset($form_data['sort_order']) ? (int)$form_data['sort_order'] : 0; ?>"
                       min="0">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Status</label>
                <select class="form-control" name="status" id="whyStatus">
                  <option value="1" <?php echo (!isset($form_data['status']) || $form_data['status'] == 1) ? 'selected' : ''; ?>>Active</option>
                  <option value="0" <?php echo (isset($form_data['status']) && $form_data['status'] == 0) ? 'selected' : ''; ?>>Hidden</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-saffron">Save Item</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
<?php if (!empty($errors)): ?>
$(document).ready(function() { $('#whyModal').modal('show'); });
<?php endif; ?>

function resetWhyModal() {
  document.getElementById('whyModalTitle').textContent = 'Add Why Choose Us Item';
  document.getElementById('whyItemId').value      = '0';
  document.getElementById('whyIcon').value        = '🌿';
  document.getElementById('whyTitle').value       = '';
  document.getElementById('whyDescription').value = '';
  document.getElementById('whySortOrder').value   = '0';
  document.getElementById('whyStatus').value      = '1';
}
function openEditWhy(item) {
  document.getElementById('whyModalTitle').textContent = 'Edit Item';
  document.getElementById('whyItemId').value      = item.id;
  document.getElementById('whyIcon').value        = item.icon;
  document.getElementById('whyTitle').value       = item.title;
  document.getElementById('whyDescription').value = item.description;
  document.getElementById('whySortOrder').value   = item.sort_order;
  document.getElementById('whyStatus').value      = item.status;
  $('#whyModal').modal('show');
}
</script>
