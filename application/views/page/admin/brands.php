<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title">Brand Management</h3>
    <div class="box-tools pull-right">
      <button class="btn btn-sm btn-saffron" data-toggle="modal" data-target="#brandModal" onclick="resetBrandModal()">
        <i class="fa fa-plus"></i> Add Brand
      </button>
    </div>
  </div>
  <div class="box-body">

    <?php $flash_s = $this->session->flashdata('success'); if ($flash_s): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($flash_s); ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><ul><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table table-bordered table-hover admin-table">
        <thead>
          <tr><th>Logo</th><th>Name</th><th>Slug</th><th>Products</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($brands as $b): ?>
          <tr>
            <td>
              <?php if (!empty($b['image']) && file_exists(FCPATH.'uploads/products/'.$b['image'])): ?>
                <img src="<?php echo base_url('uploads/products/'.$b['image']); ?>" class="admin-thumb" alt="">
              <?php else: ?>
                <div class="admin-thumb text-center" style="background:#f4f4f4;border-radius:8px;font-size:1.4rem">🏷️</div>
              <?php endif; ?>
            </td>
            <td><strong><?php echo htmlspecialchars($b['name']); ?></strong></td>
            <td><code><?php echo htmlspecialchars($b['slug']); ?></code></td>
            <td><?php echo $b['product_count']; ?> product<?php echo $b['product_count']!=1?'s':''; ?></td>
            <td>
              <span class="label <?php echo $b['status'] ? 'label-success' : 'label-default'; ?>">
                <?php echo $b['status'] ? 'Active' : 'Inactive'; ?>
              </span>
            </td>
            <td>
              <button class="btn btn-xs btn-primary" onclick='openEditBrand(<?php echo json_encode($b); ?>)'>
                <i class="fa fa-pencil"></i>
              </button>
              <a href="<?php echo site_url('admin-brands'); ?>?action=delete&edit=<?php echo $b['id']; ?>"
                 class="btn btn-xs btn-danger"
                 onclick="return confirm('Delete this brand?')">
                <i class="fa fa-trash"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($brands)): ?>
            <tr><td colspan="6" class="text-center text-muted">No brands yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Brand Modal -->
<div class="modal fade" id="brandModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="<?php echo site_url('admin-brands'); ?>" enctype="multipart/form-data">
        <input type="hidden" name="brand_id" id="brandId" value="0">
        <input type="hidden" name="existing_image" id="brandExistingImage" value="">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="brandModalTitle">Add Brand</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Brand Name *</label>
            <input type="text" class="form-control" name="name" id="brandName" required>
          </div>
          <div class="form-group">
            <label>Logo Image (optional)</label>
            <input type="file" class="form-control" name="image" accept="image/*">
            <div id="brandCurrentImg" class="margin-t-10"></div>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select class="form-control" name="status" id="brandStatus">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-saffron">Save Brand</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function resetBrandModal() {
  document.getElementById('brandModalTitle').textContent = 'Add Brand';
  document.getElementById('brandId').value = '0';
  document.getElementById('brandName').value = '';
  document.getElementById('brandStatus').value = '1';
  document.getElementById('brandExistingImage').value = '';
  document.getElementById('brandCurrentImg').innerHTML = '';
}
function openEditBrand(b) {
  document.getElementById('brandModalTitle').textContent = 'Edit Brand';
  document.getElementById('brandId').value     = b.id;
  document.getElementById('brandName').value   = b.name;
  document.getElementById('brandStatus').value = b.status;
  document.getElementById('brandExistingImage').value = b.image || '';
  document.getElementById('brandCurrentImg').innerHTML = b.image
    ? '<img src="<?php echo base_url("uploads/products/"); ?>' + b.image + '" height="55" style="border-radius:8px" alt="">'
    : '';
  $('#brandModal').modal('show');
}
</script>
