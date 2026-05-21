<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title">Category Management</h3>
    <div class="box-tools pull-right">
      <button class="btn btn-sm btn-saffron" data-toggle="modal" data-target="#catModal" onclick="resetCatModal()">
        <i class="fa fa-plus"></i> Add Category
      </button>
    </div>
  </div>
  <div class="box-body">

    <?php $flash_s = $this->session->flashdata('success'); if ($flash_s): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($flash_s); ?></div>
    <?php endif; ?>
    <?php $flash_d = $this->session->flashdata('danger'); if ($flash_d): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($flash_d); ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><ul><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Filter bar -->
    <div class="row" style="margin-bottom:10px">
      <div class="col-sm-4">
        <div class="input-group input-group-sm">
          <span class="input-group-addon"><i class="fa fa-search"></i></span>
          <input type="text" class="form-control" id="cat_search" placeholder="Search name, slug…">
          <span class="input-group-btn">
            <button type="button" class="btn btn-default" id="cat_clear" title="Clear"><i class="fa fa-times"></i></button>
          </span>
        </div>
      </div>
      <div class="col-sm-2 col-xs-6">
        <select class="form-control input-sm" id="cat_fStatus">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
      <div class="col-sm-2 col-xs-6">
        <select class="form-control input-sm" id="cat_fLevel">
          <option value="">All Levels</option>
          <option value="top level">Top Level</option>
          <option value="↳">Sub-category</option>
        </select>
      </div>
      <div class="col-sm-4" style="line-height:30px">
        <small class="text-muted" id="cat_count"></small>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover admin-table" id="cat_table">
        <thead>
          <tr><th>Image</th><th>Name</th><th>Parent</th><th>Slug</th><th>Products</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($cats as $cat): ?>
          <tr>
            <td>
              <?php if (!empty($cat['image']) && file_exists(FCPATH.'uploads/products/'.$cat['image'])): ?>
                <img src="<?php echo base_url('uploads/products/'.$cat['image']); ?>" class="admin-thumb" alt="">
              <?php else: ?>
                <div class="admin-thumb text-center" style="background:#f4f4f4;border-radius:8px;font-size:1.4rem">🌿</div>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($cat['parent_id']): ?>
                <span style="color:#aaa;margin-right:6px">↳</span>
              <?php endif; ?>
              <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
            </td>
            <td>
              <?php echo $cat['parent_name'] ? '<span class="label label-default">'.htmlspecialchars($cat['parent_name']).'</span>' : '<span class="text-muted small">— (top level)</span>'; ?>
            </td>
            <td><code><?php echo htmlspecialchars($cat['slug']); ?></code></td>
            <td>
              <a href="<?php echo site_url('admin-products'); ?>"><?php echo $cat['product_count']; ?> product<?php echo $cat['product_count']!=1?'s':''; ?></a>
            </td>
            <td>
              <span class="label <?php echo $cat['status'] ? 'label-success' : 'label-default'; ?>">
                <?php echo $cat['status'] ? 'Active' : 'Inactive'; ?>
              </span>
            </td>
            <td>
              <button class="btn btn-xs btn-primary" onclick='openEditCat(<?php echo json_encode($cat); ?>)'>
                <i class="fa fa-pencil"></i>
              </button>
              <a href="<?php echo site_url('admin-categories'); ?>?action=delete&edit=<?php echo $cat['id']; ?>"
                 class="btn btn-xs btn-danger"
                 onclick="return confirm('Delete this category? Only possible if no products are linked.')">
                <i class="fa fa-trash"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($cats)): ?>
            <tr><td colspan="7" class="text-center text-muted">No categories yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
(function () {
  var rows   = Array.from(document.querySelectorAll('#cat_table tbody tr'));
  var search = document.getElementById('cat_search');
  var count  = document.getElementById('cat_count');
  function run() {
    var q  = search.value.trim().toLowerCase();
    var fS = document.getElementById('cat_fStatus').value.toLowerCase();
    var fL = document.getElementById('cat_fLevel').value.toLowerCase();
    var n = 0;
    rows.forEach(function (r) {
      if (r.cells.length < 2) { r.style.display = ''; return; }
      var txt  = r.textContent.toLowerCase();
      var stat = r.cells[5] ? r.cells[5].textContent.trim().toLowerCase() : '';
      var par  = r.cells[2] ? r.cells[2].textContent.trim().toLowerCase() : '';
      var ok = (!q || txt.indexOf(q) >= 0)
            && (!fS || stat.indexOf(fS) >= 0)
            && (!fL || par.indexOf(fL) >= 0);
      r.style.display = ok ? '' : 'none';
      if (ok) n++;
    });
    count.textContent = n + ' / ' + rows.length + ' categories';
  }
  search.addEventListener('input', run);
  document.getElementById('cat_clear').addEventListener('click', function () { search.value = ''; run(); });
  ['cat_fStatus','cat_fLevel'].forEach(function (id) { document.getElementById(id).addEventListener('change', run); });
})();
</script>

<!-- Category Modal -->
<div class="modal fade" id="catModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="<?php echo site_url('admin-categories'); ?>" enctype="multipart/form-data">
        <input type="hidden" name="cat_id" id="catId" value="0">
        <input type="hidden" name="existing_image" id="catExistingImage" value="">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="catModalTitle">Add Category</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Category Name *</label>
            <input type="text" class="form-control" name="name" id="catName" required>
          </div>
          <div class="form-group">
            <label>Parent Category <small class="text-muted">(leave blank for top-level)</small></label>
            <select class="form-control" name="parent_id" id="catParentId">
              <option value="">— Top Level —</option>
              <?php foreach ($cats as $cat): ?>
                <?php if (!$cat['parent_id']): ?>
                  <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Image (optional)</label>
            <input type="file" class="form-control" name="image" accept="image/*">
            <div id="catCurrentImg" class="margin-t-10"></div>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select class="form-control" name="status" id="catStatus">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-saffron">Save Category</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function resetCatModal() {
  document.getElementById('catModalTitle').textContent = 'Add Category';
  document.getElementById('catId').value      = '0';
  document.getElementById('catName').value    = '';
  document.getElementById('catParentId').value= '';
  document.getElementById('catStatus').value  = '1';
  document.getElementById('catExistingImage').value = '';
  document.getElementById('catCurrentImg').innerHTML = '';
}
function openEditCat(cat) {
  document.getElementById('catModalTitle').textContent = 'Edit Category';
  document.getElementById('catId').value      = cat.id;
  document.getElementById('catName').value    = cat.name;
  document.getElementById('catParentId').value= cat.parent_id || '';
  document.getElementById('catStatus').value  = cat.status;
  document.getElementById('catExistingImage').value = cat.image || '';
  document.getElementById('catCurrentImg').innerHTML = cat.image
    ? '<img src="<?php echo base_url("uploads/products/"); ?>' + cat.image + '" height="55" style="border-radius:8px" alt="">'
    : '';
  $('#catModal').modal('show');
}
</script>
