<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title">
      Product Management
      <?php if ($filter_low): ?>
        <span class="label label-warning">Low Stock Only &mdash; <a href="<?php echo site_url('admin-products'); ?>">Clear</a></span>
      <?php endif; ?>
    </h3>
    <div class="box-tools pull-right">
      <a href="<?php echo site_url('admin-products'); ?>?filter=low_stock" class="btn btn-sm btn-warning margin-r-5">
        <i class="fa fa-exclamation-triangle"></i> Low Stock
      </a>
      <button class="btn btn-sm btn-saffron" data-toggle="modal" data-target="#productModal">
        <i class="fa fa-plus"></i> Add Product
      </button>
    </div>
  </div>
  <div class="box-body">

    <?php $flash_s = $this->session->flashdata('success'); if ($flash_s): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($flash_s); ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table table-bordered table-hover admin-table">
        <thead>
          <tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Offer</th><th>Stock</th><th>Featured</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
          <tr>
            <td><img src="<?php echo $this->spice_model->product_image($p['image']); ?>" class="admin-thumb" alt=""></td>
            <td>
              <strong><?php echo htmlspecialchars($p['name']); ?></strong><br>
              <small class="text-muted"><?php echo htmlspecialchars($this->spice_model->truncate_text($p['description'] ?? '', 50)); ?></small>
              <?php if (!empty($p['brand_name'])): ?>
                <br><span class="label label-default" style="font-size:.7rem"><?php echo htmlspecialchars($p['brand_name']); ?></span>
              <?php endif; ?>
            </td>
            <td><span class="label label-default"><?php echo htmlspecialchars($p['cat_name']); ?></span></td>
            <td class="text-saffron"><strong><?php echo $this->spice_model->rupees((float)$p['price']); ?></strong></td>
            <td>
              <?php if (!empty($p['offer_price']) && $p['offer_price'] > 0): ?>
                <span class="label label-success"><?php echo $this->spice_model->rupees((float)$p['offer_price']); ?></span>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
            <td class="<?php echo $p['stock_qty'] < 20 ? 'text-red' : ''; ?>"><strong><?php echo $p['stock_qty']; ?></strong></td>
            <td>
              <span class="label <?php echo $p['is_featured'] ? 'label-warning' : 'label-default'; ?>">
                <?php echo $p['is_featured'] ? '⭐' : '—'; ?>
              </span>
            </td>
            <td>
              <span class="label <?php echo $p['status'] ? 'label-success' : 'label-default'; ?>">
                <?php echo $p['status'] ? 'Active' : 'Inactive'; ?>
              </span>
            </td>
            <td>
              <a href="<?php echo site_url('admin-product/'.$p['id']); ?>"
                 class="btn btn-xs btn-saffron" title="Edit Details / Gallery / Variants">
                <i class="fa fa-edit"></i> Edit
              </a>
              <a href="<?php echo site_url('admin-products'); ?>?action=toggle&edit=<?php echo $p['id']; ?>"
                 class="btn btn-xs btn-warning" title="<?php echo $p['status'] ? 'Deactivate' : 'Activate'; ?>">
                <i class="fa fa-<?php echo $p['status'] ? 'eye-slash' : 'eye'; ?>"></i>
              </a>
              <a href="<?php echo site_url('admin-products'); ?>?action=delete&edit=<?php echo $p['id']; ?>"
                 class="btn btn-xs btn-danger"
                 onclick="return confirm('Deactivate this product?')">
                <i class="fa fa-trash"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($products)): ?>
            <tr><td colspan="9" class="text-center text-muted">No products found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" action="<?php echo site_url('admin-products'); ?>" enctype="multipart/form-data">
        <input type="hidden" name="product_id" id="modalProductId" value="0">
        <input type="hidden" name="existing_image" id="modalExistingImage" value="">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="modalTitle">Add New Product</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label>Product Name *</label>
                <input type="text" class="form-control" name="name" id="modalName" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Category *</label>
                <select class="form-control" name="category_id" id="modalCatId" required>
                  <option value="">Select…</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Brand</label>
                <select class="form-control" name="brand_id" id="modalBrandId">
                  <option value="">No Brand</option>
                  <?php foreach ($brands as $br): ?>
                    <option value="<?php echo $br['id']; ?>"><?php echo htmlspecialchars($br['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Weight (e.g. 100g)</label>
                <input type="text" class="form-control" name="weight" id="modalWeight" placeholder="100g">
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label>Description</label>
                <textarea class="form-control" name="description" id="modalDesc" rows="3"></textarea>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Price (₹) *</label>
                <input type="number" class="form-control" name="price" id="modalPrice" step="0.01" min="0.01" required>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Offer Price (₹)</label>
                <input type="number" class="form-control" name="offer_price" id="modalOfferPrice" step="0.01" min="0" placeholder="0 = no offer">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>GST (%)</label>
                <input type="number" class="form-control" name="gst" id="modalGst" step="0.5" min="0" value="0" placeholder="e.g. 5 or 12">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Stock Qty</label>
                <input type="number" class="form-control" name="stock_qty" id="modalStock" min="0" value="0">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Tags <small class="text-muted">(comma separated)</small></label>
                <input type="text" class="form-control" name="tags" id="modalTags" placeholder="spicy, fresh, organic">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Featured?</label>
                <select class="form-control" name="is_featured" id="modalFeatured">
                  <option value="0">No</option>
                  <option value="1">Yes ⭐</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Status</label>
                <select class="form-control" name="status" id="modalStatus">
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label>Product Image</label>
                <input type="file" class="form-control" name="image" accept="image/*">
                <small class="text-muted">JPEG/PNG/WebP, max 2MB.</small>
                <div id="modalCurrentImg" class="margin-t-10"></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Meta Title <small class="text-muted">(SEO)</small></label>
                <input type="text" class="form-control" name="meta_title" id="modalMetaTitle">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Meta Description <small class="text-muted">(SEO)</small></label>
                <input type="text" class="form-control" name="meta_desc" id="modalMetaDesc">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-saffron"><i class="fa fa-save"></i> Save Product</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function editProduct(p) {
  document.getElementById('modalTitle').textContent       = 'Edit Product';
  document.getElementById('modalProductId').value         = p.id;
  document.getElementById('modalName').value              = p.name;
  document.getElementById('modalDesc').value              = p.description || '';
  document.getElementById('modalPrice').value             = p.price;
  document.getElementById('modalOfferPrice').value        = p.offer_price || '';
  document.getElementById('modalGst').value               = p.gst || '0';
  document.getElementById('modalStock').value             = p.stock_qty;
  document.getElementById('modalWeight').value            = p.weight || '';
  document.getElementById('modalTags').value              = p.tags || '';
  document.getElementById('modalFeatured').value          = p.is_featured || '0';
  document.getElementById('modalStatus').value            = p.status;
  document.getElementById('modalExistingImage').value     = p.image || '';
  document.getElementById('modalCatId').value             = p.category_id;
  document.getElementById('modalBrandId').value           = p.brand_id || '';
  document.getElementById('modalMetaTitle').value         = p.meta_title || '';
  document.getElementById('modalMetaDesc').value          = p.meta_desc || '';
  var imgDiv = document.getElementById('modalCurrentImg');
  imgDiv.innerHTML = p.image
    ? '<img src="<?php echo base_url("uploads/products/"); ?>' + p.image + '" height="55" style="border-radius:8px" alt="">'
    : '';
  $('#productModal').modal('show');
}
</script>
