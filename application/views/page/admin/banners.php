<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title">Banner Management</h3>
    <div class="box-tools pull-right">
      <button class="btn btn-sm btn-saffron" data-toggle="modal" data-target="#bannerModal" onclick="resetBannerModal()">
        <i class="fa fa-plus"></i> Add Banner
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
          <tr><th>Image</th><th>Title</th><th>Type</th><th>Sort</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($banners as $b): ?>
          <tr>
            <td>
              <?php if (!empty($b['image']) && file_exists(FCPATH.'uploads/banners/'.$b['image'])): ?>
                <img src="<?php echo base_url('uploads/banners/'.$b['image']); ?>"
                     style="height:50px;width:100px;object-fit:cover;border-radius:6px" alt="">
              <?php else: ?>
                <div style="height:50px;width:100px;background:#f4f4f4;border-radius:6px;display:flex;align-items:center;justify-content:center">🖼️</div>
              <?php endif; ?>
            </td>
            <td>
              <strong><?php echo htmlspecialchars($b['title'] ?: '(no title)'); ?></strong><br>
              <small class="text-muted"><?php echo htmlspecialchars($this->spice_model->truncate_text($b['subtitle'] ?? '', 50)); ?></small>
            </td>
            <td><span class="label label-info"><?php echo ucfirst($b['type']); ?></span></td>
            <td><?php echo $b['sort_order']; ?></td>
            <td>
              <span class="label <?php echo $b['status'] ? 'label-success' : 'label-default'; ?>">
                <?php echo $b['status'] ? 'Active' : 'Inactive'; ?>
              </span>
            </td>
            <td>
              <button class="btn btn-xs btn-primary" onclick='openEditBanner(<?php echo json_encode($b); ?>)'>
                <i class="fa fa-pencil"></i>
              </button>
              <a href="<?php echo site_url('admin-banners'); ?>?action=delete&edit=<?php echo $b['id']; ?>"
                 class="btn btn-xs btn-danger" onclick="return confirm('Delete this banner?')">
                <i class="fa fa-trash"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($banners)): ?>
            <tr><td colspan="6" class="text-center text-muted">No banners yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Banner Modal -->
<div class="modal fade" id="bannerModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" action="<?php echo site_url('admin-banners'); ?>" enctype="multipart/form-data">
        <input type="hidden" name="banner_id" id="bannerId" value="0">
        <input type="hidden" name="existing_image" id="bannerExistingImage" value="">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="bannerModalTitle">Add Banner</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Title</label>
                <input type="text" class="form-control" name="title" id="bannerTitle" placeholder="e.g. Fresh Spices Sale">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Subtitle</label>
                <input type="text" class="form-control" name="subtitle" id="bannerSubtitle" placeholder="e.g. Up to 30% off">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Link URL</label>
                <input type="text" class="form-control" name="link_url" id="bannerLink" placeholder="e.g. shop or shop?category=masala">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Button Text</label>
                <input type="text" class="form-control" name="btn_text" id="bannerBtnText" value="Shop Now">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Type</label>
                <select class="form-control" name="type" id="bannerType">
                  <option value="slider">Slider</option>
                  <option value="promo">Promo</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Sort Order</label>
                <input type="number" class="form-control" name="sort_order" id="bannerSort" value="0" min="0">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Status</label>
                <select class="form-control" name="status" id="bannerStatus">
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label>Banner Image *</label>
                <input type="file" class="form-control" name="image" accept="image/*">
                <small class="text-muted">Recommended: 1400×500px, JPEG/PNG/WebP, max 3MB.</small>
                <div id="bannerCurrentImg" class="margin-t-10"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-saffron">Save Banner</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function resetBannerModal() {
  document.getElementById('bannerModalTitle').textContent = 'Add Banner';
  document.getElementById('bannerId').value      = '0';
  document.getElementById('bannerTitle').value   = '';
  document.getElementById('bannerSubtitle').value= '';
  document.getElementById('bannerLink').value    = '';
  document.getElementById('bannerBtnText').value = 'Shop Now';
  document.getElementById('bannerType').value    = 'slider';
  document.getElementById('bannerSort').value    = '0';
  document.getElementById('bannerStatus').value  = '1';
  document.getElementById('bannerExistingImage').value = '';
  document.getElementById('bannerCurrentImg').innerHTML = '';
}
function openEditBanner(b) {
  document.getElementById('bannerModalTitle').textContent = 'Edit Banner';
  document.getElementById('bannerId').value      = b.id;
  document.getElementById('bannerTitle').value   = b.title || '';
  document.getElementById('bannerSubtitle').value= b.subtitle || '';
  document.getElementById('bannerLink').value    = b.link_url || '';
  document.getElementById('bannerBtnText').value = b.btn_text || 'Shop Now';
  document.getElementById('bannerType').value    = b.type || 'slider';
  document.getElementById('bannerSort').value    = b.sort_order || '0';
  document.getElementById('bannerStatus').value  = b.status;
  document.getElementById('bannerExistingImage').value = b.image || '';
  document.getElementById('bannerCurrentImg').innerHTML = b.image
    ? '<img src="<?php echo base_url("uploads/banners/"); ?>' + b.image + '" style="height:55px;border-radius:6px" alt="">'
    : '';
  $('#bannerModal').modal('show');
}
</script>
