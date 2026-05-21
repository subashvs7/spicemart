<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title">Customer Testimonials</h3>
    <div class="box-tools pull-right">
      <button class="btn btn-sm btn-saffron" data-toggle="modal" data-target="#testimonialModal" onclick="resetTestimonialModal()">
        <i class="fa fa-plus"></i> Add Testimonial
      </button>
    </div>
  </div>
  <div class="box-body">

    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Filter bar -->
    <div class="row" style="margin-bottom:10px">
      <div class="col-sm-4">
        <div class="input-group input-group-sm">
          <span class="input-group-addon"><i class="fa fa-search"></i></span>
          <input type="text" class="form-control" id="test_search" placeholder="Search customer, quote…">
          <span class="input-group-btn">
            <button type="button" class="btn btn-default" id="test_clear" title="Clear"><i class="fa fa-times"></i></button>
          </span>
        </div>
      </div>
      <div class="col-sm-2 col-xs-6">
        <select class="form-control input-sm" id="test_fRating">
          <option value="">All Ratings</option>
          <option value="5">⭐⭐⭐⭐⭐ 5</option>
          <option value="4">⭐⭐⭐⭐ 4</option>
          <option value="3">⭐⭐⭐ 3</option>
          <option value="2">⭐⭐ 2</option>
          <option value="1">⭐ 1</option>
        </select>
      </div>
      <div class="col-sm-2 col-xs-6">
        <select class="form-control input-sm" id="test_fStatus">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="hidden">Hidden</option>
        </select>
      </div>
      <div class="col-sm-4" style="line-height:30px">
        <small class="text-muted" id="test_count"></small>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover admin-table" id="test_table">
        <thead>
          <tr><th>Customer</th><th style="width:100px">Rating</th><th>Quote</th><th style="width:70px">Order</th><th style="width:80px">Status</th><th style="width:80px">Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($testimonials as $t): ?>
          <tr>
            <td><strong><?php echo htmlspecialchars($t['customer_name']); ?></strong></td>
            <td><?php echo str_repeat('⭐', (int)$t['rating']); ?></td>
            <td><em>"<?php echo htmlspecialchars($t['quote']); ?>"</em></td>
            <td><?php echo (int)$t['sort_order']; ?></td>
            <td>
              <span class="label <?php echo $t['status'] ? 'label-success' : 'label-default'; ?>">
                <?php echo $t['status'] ? 'Active' : 'Hidden'; ?>
              </span>
            </td>
            <td>
              <button class="btn btn-xs btn-primary" onclick='openEditTestimonial(<?php echo json_encode($t); ?>)'>
                <i class="fa fa-pencil"></i>
              </button>
              <a href="<?php echo site_url('admin-testimonials'); ?>?action=delete&edit=<?php echo $t['id']; ?>"
                 class="btn btn-xs btn-danger" onclick="return confirm('Delete this testimonial?')">
                <i class="fa fa-trash"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($testimonials)): ?>
            <tr><td colspan="6" class="text-center text-muted">No testimonials yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
(function () {
  var rows   = Array.from(document.querySelectorAll('#test_table tbody tr'));
  var search = document.getElementById('test_search');
  var count  = document.getElementById('test_count');
  function run() {
    var q  = search.value.trim().toLowerCase();
    var fR = document.getElementById('test_fRating').value;
    var fS = document.getElementById('test_fStatus').value.toLowerCase();
    var n = 0;
    rows.forEach(function (r) {
      if (r.cells.length < 2) { r.style.display = ''; return; }
      var rating = r.cells[1] ? (r.cells[1].textContent.match(/⭐/g) || []).length : 0;
      var stat   = r.cells[4] ? r.cells[4].textContent.trim().toLowerCase() : '';
      var ok = (!q  || r.textContent.toLowerCase().indexOf(q) >= 0)
            && (!fR || rating == parseInt(fR, 10))
            && (!fS || stat.indexOf(fS) >= 0);
      r.style.display = ok ? '' : 'none';
      if (ok) n++;
    });
    count.textContent = n + ' / ' + rows.length + ' testimonials';
  }
  search.addEventListener('input', run);
  document.getElementById('test_clear').addEventListener('click', function () { search.value = ''; run(); });
  ['test_fRating','test_fStatus'].forEach(function (id) { document.getElementById(id).addEventListener('change', run); });
})();
</script>

<!-- Modal -->
<div class="modal fade" id="testimonialModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="<?php echo site_url('admin-testimonials'); ?>">
        <input type="hidden" name="testimonial_id" id="testimonialId" value="<?php echo isset($form_data['id']) ? (int)$form_data['id'] : 0; ?>">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="testimonialModalTitle">
            <?php echo (!empty($form_data) && $form_data['id']) ? 'Edit Testimonial' : 'Add Testimonial'; ?>
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
            <div class="col-md-8">
              <div class="form-group <?php echo (!empty($errors) && empty($form_data['customer_name'])) ? 'has-error' : ''; ?>">
                <label>Customer Name *</label>
                <input type="text" class="form-control" name="customer_name" id="testimonialName"
                       placeholder="e.g. Lakshmi R., Coimbatore"
                       value="<?php echo isset($form_data['customer_name']) ? htmlspecialchars($form_data['customer_name']) : ''; ?>"
                       required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Rating (1–5)</label>
                <select class="form-control" name="rating" id="testimonialRating">
                  <?php
                  $sel_rating = isset($form_data['rating']) ? (int)$form_data['rating'] : 5;
                  for ($r = 5; $r >= 1; $r--):
                  ?>
                  <option value="<?php echo $r; ?>" <?php echo $sel_rating === $r ? 'selected' : ''; ?>>
                    <?php echo str_repeat('⭐', $r); ?> (<?php echo $r; ?>)
                  </option>
                  <?php endfor; ?>
                </select>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group <?php echo (!empty($errors) && empty($form_data['quote'])) ? 'has-error' : ''; ?>">
                <label>Quote *</label>
                <textarea class="form-control" name="quote" id="testimonialQuote" rows="3" required><?php echo isset($form_data['quote']) ? htmlspecialchars($form_data['quote']) : ''; ?></textarea>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Sort Order</label>
                <input type="number" class="form-control" name="sort_order" id="testimonialSortOrder"
                       value="<?php echo isset($form_data['sort_order']) ? (int)$form_data['sort_order'] : 0; ?>"
                       min="0">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Status</label>
                <select class="form-control" name="status" id="testimonialStatus">
                  <option value="1" <?php echo (!isset($form_data['status']) || $form_data['status'] == 1) ? 'selected' : ''; ?>>Active</option>
                  <option value="0" <?php echo (isset($form_data['status']) && $form_data['status'] == 0) ? 'selected' : ''; ?>>Hidden</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-saffron">Save Testimonial</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
<?php if (!empty($errors)): ?>
$(document).ready(function() { $('#testimonialModal').modal('show'); });
<?php endif; ?>

function resetTestimonialModal() {
  document.getElementById('testimonialModalTitle').textContent = 'Add Testimonial';
  document.getElementById('testimonialId').value         = '0';
  document.getElementById('testimonialName').value       = '';
  document.getElementById('testimonialRating').value     = '5';
  document.getElementById('testimonialQuote').value      = '';
  document.getElementById('testimonialSortOrder').value  = '0';
  document.getElementById('testimonialStatus').value     = '1';
}
function openEditTestimonial(t) {
  document.getElementById('testimonialModalTitle').textContent = 'Edit Testimonial';
  document.getElementById('testimonialId').value         = t.id;
  document.getElementById('testimonialName').value       = t.customer_name;
  document.getElementById('testimonialRating').value     = t.rating;
  document.getElementById('testimonialQuote').value      = t.quote;
  document.getElementById('testimonialSortOrder').value  = t.sort_order;
  document.getElementById('testimonialStatus').value     = t.status;
  $('#testimonialModal').modal('show');
}
</script>
