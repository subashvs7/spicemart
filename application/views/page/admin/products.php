<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<style>
/* Sticky tab nav inside modal */
#productModal .modal-tabs-wrap {
  position: sticky;
  top: 0;
  z-index: 10;
  background: #fff;
  border-bottom: 2px solid #ddd;
  padding: 0 15px;
}
#productModal .nav-tabs { border-bottom: none; }
#productModal .nav-tabs > li > a { padding: 10px 14px; font-size: .88rem; }
#productModal .modal-body  { max-height: 62vh; overflow-y: auto; padding-top: 12px; }
#productModal .tab-pane    { padding-top: 4px; }
/* Gallery grid */
.pm-gallery-item { position: relative; margin-bottom: 14px; }
.pm-gallery-item img { width: 100%; height: 110px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; }
.pm-gallery-item .gi-actions { position: absolute; bottom: 4px; left: 0; right: 0; text-align: center; }
.pm-gallery-item .gi-primary { position: absolute; top: 4px; left: 4px; }
/* Variant form card */
.pm-var-form { background: #f9f9f9; border: 1px solid #ddd; border-radius: 6px; padding: 14px; margin-top: 10px; }
</style>

<!-- ═══ PRODUCT TABLE ═══════════════════════════════════════════ -->
<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title">
      Product Management
      <?php if ($filter_low): ?>
        <span class="label label-warning">Low Stock Only &mdash;
          <a href="<?php echo site_url('admin-products'); ?>">Clear</a>
        </span>
      <?php endif; ?>
    </h3>
    <div class="box-tools pull-right">
      <a href="<?php echo site_url('admin-products'); ?>?filter=low_stock"
         class="btn btn-sm btn-warning margin-r-5">
        <i class="fa fa-exclamation-triangle"></i> Low Stock
      </a>
      <button class="btn btn-sm btn-saffron" id="btnAddProduct">
        <i class="fa fa-plus"></i> Add Product
      </button>
    </div>
  </div>

  <div class="box-body">
    <?php if ($flash_s = $this->session->flashdata('success')): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($flash_s); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if (!empty($errors) && !empty($form_data)): ?>
    <script>
    $(function () {
      var fd = <?php echo json_encode($form_data); ?>;
      var errs = <?php echo json_encode($errors); ?>;

      /* Repopulate basic fields */
      $('#pmProductId').val(fd.product_id || '0');
      $('#pmExistingImage').val(fd.image || '');
      $('#pmName').val(fd.name || '');
      $('#pmDesc').val(fd.description || '');
      $('#pmPrice').val(fd.price || '');
      $('#pmOfferPrice').val(fd.offer_price || '');
      $('#pmGst').val(fd.gst || '0');
      $('#pmStock').val(fd.stock_qty || '0');
      $('#pmWeight').val(fd.weight || '');
      $('#pmTags').val(fd.tags || '');
      $('#pmMetaTitle').val(fd.meta_title || '');
      $('#pmMetaDesc').val(fd.meta_desc || '');
      $('#pmFeatured').val(fd.is_featured || '0');
      $('#pmStatus').val(fd.status || '1');
      $('#pmCatId').val(fd.category_id || '');
      $('#pmBrandId').val(fd.brand_id || '');

      if (fd.product_id && fd.product_id != '0') {
        $('#pmTitleIcon').removeClass('fa-plus-circle').addClass('fa-pencil');
        $('#pmTitleText').text('Edit Product');
        $('#pmSaveLabel').text('Update Product');
        $('#pmTabGallery, #pmTabVariants').show();
        if (fd.image) {
          $('#pmCoverThumb').attr('src', PM.uploadBase + fd.image);
          $('#pmCoverThumbWrap').show();
        }
      }

      /* Show errors inside modal */
      var html = '<strong><i class="fa fa-exclamation-triangle"></i> Please fix the following:</strong><ul style="margin:6px 0 0 0">';
      $.each(errs, function (i, e) { html += '<li>' + e + '</li>'; });
      html += '</ul>';
      $('#pmModalErrors').html(html).show();

      $('#productModal').modal('show');
    });
    </script>
    <?php endif; ?>

    <!-- Filter bar -->
    <div class="row" style="margin-bottom:10px">
      <div class="col-sm-4">
        <div class="input-group input-group-sm">
          <span class="input-group-addon"><i class="fa fa-search"></i></span>
          <input type="text" class="form-control" id="prod_search" placeholder="Search name, code, brand…">
          <span class="input-group-btn">
            <button type="button" class="btn btn-default" id="prod_clear" title="Clear"><i class="fa fa-times"></i></button>
          </span>
        </div>
      </div>
      <div class="col-sm-2 col-xs-6">
        <select class="form-control input-sm" id="prod_fStatus">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
      <div class="col-sm-3 col-xs-6">
        <select class="form-control input-sm" id="prod_fCat">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo htmlspecialchars(strtolower($cat['name'])); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-3" style="line-height:30px">
        <small class="text-muted" id="prod_count"></small>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover admin-table" id="prod_table">
        <thead>
          <tr>
            <th>Image</th><th>Name</th><th>Category</th>
            <th>Price</th><th>Offer</th><th>Stock</th>
            <th>Sizes</th><th>Featured</th><th>Status</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
          <tr>
            <td>
              <img src="<?php echo $this->spice_model->product_image($p['image']); ?>"
                   class="admin-thumb" alt="">
            </td>
            <td>
              <strong><?php echo htmlspecialchars($p['name']); ?></strong>
              <?php if (!empty($p['product_code'])): ?>
                <span class="label label-default" style="font-size:.7rem;letter-spacing:.8px;margin-left:4px">
                  <i class="fa fa-barcode"></i> <?php echo htmlspecialchars($p['product_code']); ?>
                </span>
              <?php endif; ?><br>
              <small class="text-muted">
                <?php echo htmlspecialchars($this->spice_model->truncate_text($p['description'] ?? '', 50)); ?>
              </small>
              <?php if (!empty($p['brand_name'])): ?>
                <br><span class="label label-default" style="font-size:.7rem">
                  <?php echo htmlspecialchars($p['brand_name']); ?>
                </span>
              <?php endif; ?>
            </td>
            <td><span class="label label-default"><?php echo htmlspecialchars($p['cat_name']); ?></span></td>
            <td class="text-saffron"><strong><?php echo $this->spice_model->rupees((float)$p['price']); ?></strong></td>
            <td>
              <?php if (!empty($p['offer_price']) && $p['offer_price'] > 0): ?>
                <span class="label label-success"><?php echo $this->spice_model->rupees((float)$p['offer_price']); ?></span>
              <?php else: ?><span class="text-muted">—</span><?php endif; ?>
            </td>
            <td class="<?php echo $p['stock_qty'] < 20 ? 'text-red' : ''; ?>">
              <strong><?php echo $p['stock_qty']; ?></strong>
            </td>
            <td>
              <button class="btn btn-xs btn-info open-size-modal"
                      data-id="<?php echo $p['id']; ?>"
                      data-name="<?php echo htmlspecialchars($p['name'], ENT_QUOTES); ?>"
                      data-price="<?php echo $p['price']; ?>">
                <i class="fa fa-tags"></i> Sizes
              </button>
            </td>
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
            <td style="white-space:nowrap">
              <!-- Edit → opens modal -->
              <button class="btn btn-xs btn-saffron open-edit-modal"
                      data-product='<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES); ?>'
                      title="Edit product">
                <i class="fa fa-pencil"></i> Edit
              </button>
              <!-- Toggle active (1) ↔ inactive (0) -->
              <a href="<?php echo site_url('admin-products'); ?>?action=toggle&edit=<?php echo $p['id']; ?>"
                 class="btn btn-xs btn-warning"
                 title="<?php echo $p['status'] ? 'Deactivate' : 'Activate'; ?>">
                <i class="fa fa-<?php echo $p['status'] ? 'eye-slash' : 'eye'; ?>"></i>
              </a>
              <!-- Delete — hides from admin and shop (status = -1) -->
              <a href="<?php echo site_url('admin-products'); ?>?action=delete&edit=<?php echo $p['id']; ?>"
                 class="btn btn-xs btn-danger"
                 onclick="return confirm('Delete this product? It will be permanently hidden.')">
                <i class="fa fa-trash"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($products)): ?>
            <tr><td colspan="10" class="text-center text-muted">No products found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>


<script>
(function () {
  var rows   = Array.from(document.querySelectorAll('#prod_table tbody tr'));
  var search = document.getElementById('prod_search');
  var count  = document.getElementById('prod_count');
  function run() {
    var q   = search.value.trim().toLowerCase();
    var fS  = document.getElementById('prod_fStatus').value.toLowerCase();
    var fC  = document.getElementById('prod_fCat').value.toLowerCase();
    var n = 0;
    rows.forEach(function (r) {
      if (r.cells.length < 2) { r.style.display = ''; return; }
      var txt  = r.textContent.toLowerCase();
      var stat = r.cells[8] ? r.cells[8].textContent.trim().toLowerCase() : '';
      var cat  = r.cells[2] ? r.cells[2].textContent.trim().toLowerCase() : '';
      var ok = (!q || txt.indexOf(q) >= 0)
            && (!fS || stat.indexOf(fS) >= 0)
            && (!fC || cat.indexOf(fC) >= 0);
      r.style.display = ok ? '' : 'none';
      if (ok) n++;
    });
    count.textContent = n + ' / ' + rows.length + ' products';
  }
  search.addEventListener('input', run);
  document.getElementById('prod_clear').addEventListener('click', function () { search.value = ''; run(); });
  ['prod_fStatus', 'prod_fCat'].forEach(function (id) {
    document.getElementById(id).addEventListener('change', run);
  });
})();
</script>

<!-- ═══════════════════════════════════════════════════════════════
     UNIFIED ADD / EDIT PRODUCT MODAL  (3 tabs, tabs 2-3 for edit)
═══════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="productModal" tabindex="-1" data-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <!-- Header -->
      <div class="modal-header" style="background:#2C1810;color:#fff;padding:12px 18px;border-radius:4px 4px 0 0">
        <button type="button" class="close" data-dismiss="modal"
                style="color:#fff;opacity:1;font-size:1.4rem">&times;</button>
        <h4 class="modal-title" id="pmTitle" style="margin:0">
          <i class="fa fa-plus-circle" id="pmTitleIcon"></i>
          <span id="pmTitleText">Add New Product</span>
          <span id="pmProductCode" style="display:none;font-size:12px;font-weight:normal;
                background:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.35);
                border-radius:4px;padding:2px 9px;margin-left:10px;letter-spacing:1px;
                vertical-align:middle"></span>
        </h4>
      </div>

      <!-- Sticky tab nav -->
      <div class="modal-tabs-wrap">
        <ul class="nav nav-tabs" id="pmTabs">
          <li class="active">
            <a href="#pm-basic" data-toggle="tab">
              <i class="fa fa-info-circle"></i> Basic Info
            </a>
          </li>
          <li id="pmTabGallery" style="display:none">
            <a href="#pm-gallery" data-toggle="tab">
              <i class="fa fa-picture-o"></i> Gallery
              <span class="badge" id="pmGalleryBadge" style="background:#777">0</span>
            </a>
          </li>
          <li id="pmTabVariants" style="display:none">
            <a href="#pm-variants" data-toggle="tab">
              <i class="fa fa-tags"></i> Variants
              <span class="badge" id="pmVariantBadge" style="background:#777">0</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- Scrollable body -->
      <div class="modal-body">
        <div class="tab-content">

          <!-- ── TAB 1 : BASIC INFO ──────────────────────────── -->
          <div class="tab-pane active" id="pm-basic">
            <form id="pmForm" method="post"
                  action="<?php echo site_url('admin-products'); ?>"
                  enctype="multipart/form-data">
              <input type="hidden" name="product_id"      id="pmProductId"    value="0">
              <input type="hidden" name="existing_image"  id="pmExistingImage" value="">

              <div id="pmModalErrors" class="alert alert-danger" style="display:none;margin-bottom:12px"></div>

              <div class="row">
                <div class="col-md-8">
                  <div class="form-group">
                    <label>Product Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" id="pmName" required>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Category <span class="text-danger">*</span></label>
                    <select class="form-control" name="category_id" id="pmCatId" required>
                      <option value="">Select…</option>
                      <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>">
                          <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Brand</label>
                    <select class="form-control" name="brand_id" id="pmBrandId">
                      <option value="">No Brand</option>
                      <?php foreach ($brands as $br): ?>
                        <option value="<?php echo $br['id']; ?>">
                          <?php echo htmlspecialchars($br['name']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Weight <small class="text-muted">(e.g. 100g)</small></label>
                    <input type="text" class="form-control" name="weight" id="pmWeight">
                  </div>
                </div>

                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <textarea class="form-control" name="description" id="pmDesc" rows="3"></textarea>
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Price (₹) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="price" id="pmPrice"
                           step="0.01" min="0.01" required>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Offer Price (₹)</label>
                    <input type="number" class="form-control" name="offer_price" id="pmOfferPrice"
                           step="0.01" min="0" placeholder="0 = no offer">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>GST (%)</label>
                    <input type="number" class="form-control" name="gst" id="pmGst"
                           step="0.5" min="0" value="0">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Stock Qty</label>
                    <input type="number" class="form-control" name="stock_qty" id="pmStock"
                           min="0" value="0">
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Tags <small class="text-muted">(comma separated)</small></label>
                    <input type="text" class="form-control" name="tags" id="pmTags"
                           placeholder="spicy, fresh, organic">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Featured?</label>
                    <select class="form-control" name="is_featured" id="pmFeatured">
                      <option value="0">No</option>
                      <option value="1">Yes ⭐</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" name="status" id="pmStatus">
                      <option value="1">Active</option>
                      <option value="0">Inactive</option>
                    </select>
                  </div>
                </div>

                <!-- Cover image -->
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Cover Image</label>
                    <div class="row">
                      <div class="col-md-2" id="pmCoverThumbWrap" style="display:none">
                        <img id="pmCoverThumb" src="" alt=""
                             style="width:100%;border-radius:6px;border:1px solid #ddd">
                      </div>
                      <div class="col-md-10">
                        <input type="file" class="form-control" name="image" id="pmImageFile"
                               accept="image/*" onchange="previewCover(this)">
                        <small class="text-muted">JPEG/PNG/WebP · max 2 MB. Leave blank to keep current.</small>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Meta Title <small class="text-muted">(SEO)</small></label>
                    <input type="text" class="form-control" name="meta_title" id="pmMetaTitle">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Meta Description <small class="text-muted">(SEO)</small></label>
                    <input type="text" class="form-control" name="meta_desc" id="pmMetaDesc">
                  </div>
                </div>
              </div><!-- /row -->

              <!-- Save button inside the form/tab -->
              <div class="text-right" style="border-top:1px solid #eee;padding-top:12px;margin-top:4px">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                  <i class="fa fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn btn-saffron margin-l-5">
                  <i class="fa fa-save"></i> <span id="pmSaveLabel">Save Product</span>
                </button>
              </div>
            </form>
          </div><!-- /pm-basic -->

          <!-- ── TAB 2 : GALLERY ─────────────────────────────── -->
          <div class="tab-pane" id="pm-gallery">

            <!-- Upload strip -->
            <div class="well well-sm" style="margin-bottom:14px">
              <div class="row">
                <div class="col-md-8">
                  <input type="file" id="pmGalleryFileInput" multiple accept="image/*" class="form-control">
                </div>
                <div class="col-md-4">
                  <button class="btn btn-primary btn-block" id="pmUploadImagesBtn">
                    <i class="fa fa-cloud-upload"></i> Upload
                  </button>
                </div>
              </div>
              <small class="text-muted">Select multiple files · JPEG/PNG/WebP · max 2 MB each.</small>
              <span id="pmUploadStatus" class="small margin-l-10"></span>
            </div>

            <!-- Loading -->
            <div id="pmGallerySpinner" class="text-center" style="padding:20px;display:none">
              <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
            </div>

            <!-- Image grid -->
            <div class="row" id="pmGalleryGrid">
              <!-- filled by JS -->
            </div>

            <p id="pmGalleryEmpty" class="text-muted text-center" style="display:none">
              <i class="fa fa-picture-o fa-2x"></i><br>No gallery images yet. Upload some above.
            </p>
          </div><!-- /pm-gallery -->

          <!-- ── TAB 3 : VARIANTS ────────────────────────────── -->
          <div class="tab-pane" id="pm-variants">

            <!-- Loading -->
            <div id="pmVariantsSpinner" class="text-center" style="padding:20px;display:none">
              <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
            </div>

            <!-- Base price info -->
            <div class="alert alert-info" style="font-size:.88rem;padding:7px 12px;margin-bottom:10px">
              <i class="fa fa-info-circle"></i>
              Base price: <strong id="pmBasePrice">—</strong> &nbsp;|&nbsp;
              Price modifier is added to / subtracted from the base price per variant.
            </div>

            <!-- Variant table -->
            <div class="table-responsive">
              <table class="table table-bordered table-condensed" style="font-size:.88rem">
                <thead style="background:#f4f4f4">
                  <tr>
                    <th>#</th><th>Type</th><th>Value</th>
                    <th>Modifier</th><th>Final ₹</th><th>Stock</th><th>SKU</th><th>Action</th>
                  </tr>
                </thead>
                <tbody id="pmVariantTbody">
                  <!-- filled by JS -->
                </tbody>
              </table>
            </div>

            <!-- Add / Edit variant form -->
            <div class="pm-var-form">
              <h5 style="margin-top:0">
                <i class="fa fa-plus-circle text-saffron" id="pmVarFormIcon"></i>
                <span id="pmVarFormTitle">Add Variant</span>
              </h5>
              <input type="hidden" id="pmVarEditId" value="0">

              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="small">Type *</label>
                    <select class="form-control input-sm" id="pmVarType">
                      <option value="size">Size</option>
                      <option value="weight">Weight</option>
                      <option value="color">Color</option>
                      <option value="pack">Pack</option>
                      <option value="custom">Custom…</option>
                    </select>
                    <input type="text" class="form-control input-sm margin-t-5" id="pmVarCustomType"
                           placeholder="e.g. flavor" style="display:none">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="small">Value *</label>
                    <input type="text" class="form-control input-sm" id="pmVarValue"
                           placeholder="100g, Red, Large">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label class="small">Price Mod (₹)</label>
                    <input type="number" class="form-control input-sm" id="pmVarPrice"
                           step="0.01" value="0">
                    <small class="text-muted">Final: <strong id="pmVarFinal">—</strong></small>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label class="small">Stock</label>
                    <input type="number" class="form-control input-sm" id="pmVarStock"
                           min="0" value="0">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label class="small">SKU <span class="text-muted">(auto)</span></label>
                    <div class="input-group input-group-sm">
                      <input type="text" class="form-control" id="pmVarSku"
                             data-auto="1" placeholder="Auto-generated">
                      <span class="input-group-btn">
                        <button type="button" class="btn btn-default" id="pmVarSkuGenBtn"
                                title="Re-generate SKU"><i class="fa fa-magic"></i></button>
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Color picker row — shown only when type = color -->
              <div class="row" id="pmVarColorRow" style="display:none">
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="small"><i class="fa fa-paint-brush"></i> Color Code</label>
                    <div class="input-group input-group-sm">
                      <input type="color" id="pmVarColorPicker" value="#7B4228"
                             style="width:40px;height:32px;padding:2px;border:1px solid #ccc;border-radius:4px 0 0 4px;cursor:pointer">
                      <input type="text" class="form-control" id="pmVarColorHex"
                             value="#7B4228" maxlength="7"
                             style="font-family:monospace;font-size:.82rem"
                             placeholder="#RRGGBB">
                      <span class="input-group-addon" id="pmVarColorSwatch"
                            style="width:32px;background:#7B4228;border-radius:0 4px 4px 0"></span>
                    </div>
                    <small class="text-muted">Pick or type hex code</small>
                  </div>
                </div>
              </div>

              <button class="btn btn-sm btn-primary" id="pmVarSaveBtn">
                <i class="fa fa-save"></i> <span id="pmVarSaveLabel">Add Variant</span>
              </button>
              <button class="btn btn-sm btn-default margin-l-5" id="pmVarCancelBtn" style="display:none">
                <i class="fa fa-times"></i> Cancel
              </button>
              <span id="pmVarMsg" class="small margin-l-10"></span>
            </div><!-- /pm-var-form -->
          </div><!-- /pm-variants -->

        </div><!-- /tab-content -->
      </div><!-- /modal-body -->

      <!-- Footer (close only — save is inside the Basic Info form) -->
      <div class="modal-footer" style="padding:10px 15px">
        <small class="text-muted pull-left" id="pmFooterNote"></small>
        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
          <i class="fa fa-times"></i> Close
        </button>
      </div>

    </div><!-- /modal-content -->
  </div>
</div><!-- /#productModal -->


<!-- ═══════════════════════════════════════════════════════════════
     QUICK SIZE MODAL (unchanged — from Sizes button in table)
═══════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="sizeModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header" style="background:#1a6496;color:#fff;border-radius:4px 4px 0 0">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1">&times;</button>
        <h4 class="modal-title">
          <i class="fa fa-tags"></i>
          Variants &mdash; <span id="sizeModalProductName" style="font-style:italic"></span>
        </h4>
      </div>

      <div class="modal-body">
        <input type="hidden" id="sizeModalProductId" value="">
        <div class="alert alert-info" style="font-size:.9rem;padding:8px 12px;margin-bottom:15px">
          <i class="fa fa-info-circle"></i>
          Base price: <strong id="sizeModalBasePrice"></strong> &nbsp;|&nbsp;
          Price Modifier is added to / subtracted from the base price.
        </div>

        <div id="sizeLoadingSpinner" class="text-center" style="padding:20px 0">
          <i class="fa fa-spinner fa-spin fa-2x text-info"></i>
          <p class="text-muted margin-t-5">Loading variants…</p>
        </div>

        <div id="sizeVariantWrap" style="display:none">
          <table class="table table-bordered table-hover" style="font-size:.9rem">
            <thead style="background:#f4f4f4">
              <tr>
                <th>#</th><th>Type</th><th>Value</th>
                <th>Price Mod</th><th>Final Price</th><th>Stock</th><th>SKU</th><th>Action</th>
              </tr>
            </thead>
            <tbody id="sizeVariantTbody"></tbody>
          </table>
        </div>

        <hr>

        <div style="background:#f9f9f9;border:1px solid #ddd;border-radius:6px;padding:16px">
          <h5 style="margin-top:0">
            <i class="fa fa-plus-circle" id="sizeFormIcon"></i>
            <span id="sizeFormHeading">Add New Variant</span>
          </h5>
          <input type="hidden" id="sizeEditId" value="0">

          <div class="row">
            <div class="col-md-2">
              <div class="form-group">
                <label class="control-label">Type *</label>
                <select class="form-control" id="sizeType">
                  <option value="size">Size</option>
                  <option value="weight">Weight</option>
                  <option value="pack">Pack</option>
                  <option value="color">Color</option>
                  <option value="custom">Custom…</option>
                </select>
              </div>
            </div>
            <div class="col-md-2" id="sizeCustomTypeWrap" style="display:none">
              <div class="form-group">
                <label class="control-label">Custom Type</label>
                <input type="text" class="form-control" id="sizeCustomType" placeholder="e.g. flavor">
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label class="control-label">Value *</label>
                <input type="text" class="form-control" id="sizeValue" placeholder="100g, Large, Red">
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label class="control-label">Price Modifier (₹)</label>
                <input type="number" class="form-control" id="sizePriceMod" step="0.01" value="0">
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label class="control-label">Stock</label>
                <input type="number" class="form-control" id="sizeStock" min="0" value="0">
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label class="control-label">SKU</label>
                <input type="text" class="form-control" id="sizeSku" placeholder="optional">
              </div>
            </div>
          </div>

          <!-- Color picker — visible only when type = color -->
          <div class="row" id="sizeColorRow" style="display:none">
            <div class="col-md-4">
              <div class="form-group">
                <label class="control-label"><i class="fa fa-paint-brush"></i> Color Code</label>
                <div class="input-group">
                  <input type="color" id="sizeColorPicker" value="#7B4228"
                         style="width:40px;height:34px;padding:2px;border:1px solid #ccc;border-radius:4px 0 0 4px;cursor:pointer">
                  <input type="text" class="form-control" id="sizeColorHex"
                         value="#7B4228" maxlength="7"
                         style="font-family:monospace"
                         placeholder="#RRGGBB">
                  <span class="input-group-addon" id="sizeColorSwatch"
                        style="width:36px;background:#7B4228;border-radius:0 4px 4px 0"></span>
                </div>
                <small class="text-muted">Pick or type a hex color</small>
              </div>
            </div>
          </div>

          <p class="text-muted small margin-b-5">
            Final price: <strong id="sizeFinalPreview" class="text-success">—</strong>
          </p>

          <button class="btn btn-primary" id="sizeSubmitBtn">
            <i class="fa fa-save"></i> <span id="sizeSubmitLabel">Add Variant</span>
          </button>
          <button class="btn btn-default margin-l-5" id="sizeCancelEditBtn" style="display:none">
            <i class="fa fa-times"></i> Cancel
          </button>
          <span id="sizeSaveMsg" class="margin-l-10 small"></span>
        </div>
      </div>

      <div class="modal-footer">
        <a id="sizeModalFullEditLink" href="#" class="btn btn-saffron btn-sm">
          <i class="fa fa-external-link"></i> Full Edit Page
        </a>
        <button type="button" class="btn btn-default" data-dismiss="modal">
          <i class="fa fa-check"></i> Done
        </button>
      </div>
    </div>
  </div>
</div>

<?php /* Expose PHP constants to JS */ ?>
<script>
var PM = {
  urlSave      : '<?php echo site_url("admin-products"); ?>',
  urlVarSave   : '<?php echo site_url("admin-variant-save"); ?>',
  urlVarDel    : '<?php echo site_url("admin-variant-delete/"); ?>',
  urlVars      : '<?php echo site_url("admin-get-variants/"); ?>',
  urlGallery   : '<?php echo site_url("admin-get-gallery/"); ?>',
  urlImgUpload : '<?php echo site_url("admin-image-upload"); ?>',
  urlImgDel    : '<?php echo site_url("admin-image-delete/"); ?>',
  urlImgPrimary: '<?php echo site_url("admin-image-primary/"); ?>',
  uploadBase   : '<?php echo base_url("uploads/products/"); ?>'
};

function previewCover(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      $('#pmCoverThumb').attr('src', e.target.result);
      $('#pmCoverThumbWrap').show();
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>
