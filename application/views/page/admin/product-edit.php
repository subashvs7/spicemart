<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$pid        = $product['id'];
$base_upload= base_url('uploads/products/');
$save_url   = site_url('admin-variant-save');
$del_var_url= site_url('admin-variant-delete/');
$upload_url = site_url('admin-image-upload');
$del_img_url= site_url('admin-image-delete/');
$pri_img_url= site_url('admin-image-primary/');
?>

<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title">
      <a href="<?php echo site_url('admin-products'); ?>" class="text-muted">
        <i class="fa fa-arrow-left"></i> Products
      </a>
      &nbsp;&rsaquo;&nbsp;
      <strong><?php echo htmlspecialchars($product['name']); ?></strong>
    </h3>
    <div class="box-tools pull-right">
      <?php if (!empty($product['product_code'])): ?>
        <span class="label label-default" style="font-size:13px;letter-spacing:1px;padding:5px 10px;margin-right:8px">
          <i class="fa fa-barcode"></i> <?php echo htmlspecialchars($product['product_code']); ?>
        </span>
      <?php endif; ?>
      <a href="<?php echo site_url('product/'.$pid); ?>" target="_blank" class="btn btn-sm btn-default">
        <i class="fa fa-eye"></i> View on Site
      </a>
    </div>
  </div>

  <div class="box-body">

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Tabs -->
    <ul class="nav nav-tabs" id="productTabs">
      <li class="active"><a href="#tab-basic" data-toggle="tab"><i class="fa fa-info-circle"></i> Basic Info</a></li>
      <li><a href="#tab-gallery" data-toggle="tab"><i class="fa fa-picture-o"></i> Gallery
        <span class="badge" id="galleryBadge"><?php echo count($gallery); ?></span>
      </a></li>
      <li><a href="#tab-variants" data-toggle="tab"><i class="fa fa-tags"></i> Variants
        <span class="badge" id="variantBadge"><?php echo count($variants); ?></span>
      </a></li>
    </ul>

    <div class="tab-content" style="padding-top:20px">

      <!-- ── Tab 1: Basic Info ─────────────────────────────────── -->
      <div class="tab-pane active" id="tab-basic">
        <form method="post" action="<?php echo site_url('admin-product/'.$pid); ?>" enctype="multipart/form-data">
          <input type="hidden" name="update_basic" value="1">
          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label>Product Name *</label>
                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Category *</label>
                <select class="form-control" name="category_id" required>
                  <option value="">Select…</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id']==$product['category_id']?'selected':''; ?>>
                      <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Brand</label>
                <select class="form-control" name="brand_id">
                  <option value="">No Brand</option>
                  <?php foreach ($brands as $br): ?>
                    <option value="<?php echo $br['id']; ?>" <?php echo $br['id']==$product['brand_id']?'selected':''; ?>>
                      <?php echo htmlspecialchars($br['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Weight (e.g. 100g)</label>
                <input type="text" class="form-control" name="weight" value="<?php echo htmlspecialchars($product['weight'] ?? ''); ?>">
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label>Description</label>
                <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Price (₹) *</label>
                <input type="number" class="form-control" name="price" step="0.01" min="0.01" value="<?php echo $product['price']; ?>" required>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Offer Price (₹)</label>
                <input type="number" class="form-control" name="offer_price" step="0.01" min="0" value="<?php echo $product['offer_price'] ?? ''; ?>" placeholder="0 = no offer">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>GST (%)</label>
                <input type="number" class="form-control" name="gst" step="0.5" min="0" value="<?php echo $product['gst'] ?? '0'; ?>">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Stock Qty</label>
                <input type="number" class="form-control" name="stock_qty" min="0" value="<?php echo $product['stock_qty']; ?>">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Tags <small class="text-muted">(comma separated)</small></label>
                <input type="text" class="form-control" name="tags" value="<?php echo htmlspecialchars($product['tags'] ?? ''); ?>">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Featured?</label>
                <select class="form-control" name="is_featured">
                  <option value="0" <?php echo !$product['is_featured']?'selected':''; ?>>No</option>
                  <option value="1" <?php echo $product['is_featured']?'selected':''; ?>>Yes ⭐</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Status</label>
                <select class="form-control" name="status">
                  <option value="1" <?php echo $product['status']?'selected':''; ?>>Active</option>
                  <option value="0" <?php echo !$product['status']?'selected':''; ?>>Inactive</option>
                </select>
              </div>
            </div>

            <!-- Primary Image -->
            <div class="col-md-12">
              <div class="form-group">
                <label>Primary / Cover Image</label>
                <div class="row">
                  <div class="col-md-3">
                    <img id="primaryImgPreview"
                         src="<?php echo $this->spice_model->product_image($product['image']); ?>"
                         style="width:100%;border-radius:8px;border:1px solid #ddd">
                  </div>
                  <div class="col-md-9">
                    <input type="file" class="form-control" name="image" accept="image/*"
                           onchange="previewPrimary(this)">
                    <small class="text-muted">JPEG/PNG/WebP, max 2MB. Leave empty to keep current.</small>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>Meta Title <small class="text-muted">(SEO)</small></label>
                <input type="text" class="form-control" name="meta_title" value="<?php echo htmlspecialchars($product['meta_title'] ?? ''); ?>">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Meta Description <small class="text-muted">(SEO)</small></label>
                <input type="text" class="form-control" name="meta_desc" value="<?php echo htmlspecialchars($product['meta_desc'] ?? ''); ?>">
              </div>
            </div>
          </div>

          <div class="form-group">
            <button type="submit" class="btn btn-saffron"><i class="fa fa-save"></i> Save Basic Info</button>
          </div>
        </form>
      </div><!-- /tab-basic -->

      <!-- ── Tab 2: Gallery ────────────────────────────────────── -->
      <div class="tab-pane" id="tab-gallery">

        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label><i class="fa fa-upload"></i> Upload Images (multiple allowed)</label>
              <input type="file" id="galleryFileInput" multiple accept="image/*" class="form-control">
              <small class="text-muted">JPEG/PNG/WebP, max 2MB each. You can select multiple files at once.</small>
            </div>
            <button class="btn btn-saffron btn-sm" id="uploadImagesBtn">
              <i class="fa fa-cloud-upload"></i> Upload Selected
            </button>
            <span id="uploadStatus" class="text-muted small margin-l-10"></span>
          </div>
        </div>

        <hr>

        <div class="row" id="galleryGrid">
          <?php foreach ($gallery as $img): ?>
          <div class="col-xs-6 col-md-3 gallery-item" id="gi_<?php echo $img['id']; ?>" style="margin-bottom:15px">
            <div class="thumbnail" style="position:relative;padding-bottom:36px">
              <img src="<?php echo $base_upload.$img['image']; ?>"
                   style="width:100%;height:150px;object-fit:cover;border-radius:4px">
              <?php if ($img['is_primary']): ?>
                <span class="label label-success" style="position:absolute;top:5px;left:5px">Primary</span>
              <?php endif; ?>
              <div style="position:absolute;bottom:5px;left:0;right:0;text-align:center">
                <?php if (!$img['is_primary']): ?>
                  <button class="btn btn-xs btn-primary set-primary-btn" data-id="<?php echo $img['id']; ?>" title="Set as Primary">
                    <i class="fa fa-star"></i> Set Primary
                  </button>
                <?php endif; ?>
                <button class="btn btn-xs btn-danger delete-img-btn" data-id="<?php echo $img['id']; ?>" title="Delete">
                  <i class="fa fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php if (empty($gallery)): ?>
            <div class="col-md-12" id="galleryEmpty">
              <p class="text-muted"><i class="fa fa-picture-o"></i> No gallery images yet. Upload some above.</p>
            </div>
          <?php endif; ?>
        </div>
      </div><!-- /tab-gallery -->

      <!-- ── Tab 3: Variants ───────────────────────────────────── -->
      <div class="tab-pane" id="tab-variants">

        <div class="row">
          <div class="col-md-12">
            <p class="text-muted small">
              Variants let customers choose options like <strong>size</strong>, <strong>color</strong>, or any custom attribute.
              Each variant can have its own price modifier and stock level.
            </p>
          </div>
        </div>

        <!-- Variant Table -->
        <div class="table-responsive">
          <table class="table table-bordered table-hover" id="variantTable">
            <thead>
              <tr>
                <th>Type</th><th>Value</th><th>Color</th>
                <th>Price Modifier (₹)</th><th>Stock</th><th>SKU</th>
                <th style="width:110px">Actions</th>
              </tr>
            </thead>
            <tbody id="variantTbody">
              <?php foreach ($variants as $v): ?>
              <tr id="vrow_<?php echo $v['id']; ?>" data-id="<?php echo $v['id']; ?>">
                <td><span class="label label-info"><?php echo htmlspecialchars(ucfirst($v['variant_type'])); ?></span></td>
                <td><strong><?php echo htmlspecialchars($v['variant_value']); ?></strong></td>
                <td>
                  <?php if ($v['variant_type']==='color' && !empty($v['color_hex'])): ?>
                    <span style="display:inline-flex;align-items:center;gap:4px">
                      <span style="width:18px;height:18px;border-radius:50%;background:<?php echo htmlspecialchars($v['color_hex']); ?>;border:1px solid #ccc;display:inline-block"></span>
                      <code style="font-size:.78rem"><?php echo htmlspecialchars($v['color_hex']); ?></code>
                    </span>
                  <?php else: ?>—<?php endif; ?>
                </td>
                <td><?php echo $v['price_modifier'] >= 0 ? '+' : ''; ?>₹<?php echo number_format((float)$v['price_modifier'],2); ?></td>
                <td><?php echo (int)$v['stock_qty']; ?></td>
                <td><?php echo htmlspecialchars($v['sku'] ?? '—'); ?></td>
                <td>
                  <button class="btn btn-xs btn-primary edit-variant-btn"
                          data-id="<?php echo $v['id']; ?>"
                          data-type="<?php echo htmlspecialchars($v['variant_type']); ?>"
                          data-value="<?php echo htmlspecialchars($v['variant_value']); ?>"
                          data-price="<?php echo $v['price_modifier']; ?>"
                          data-stock="<?php echo $v['stock_qty']; ?>"
                          data-sku="<?php echo htmlspecialchars($v['sku'] ?? ''); ?>"
                          data-color="<?php echo htmlspecialchars($v['color_hex'] ?? ''); ?>">
                    <i class="fa fa-pencil"></i>
                  </button>
                  <button class="btn btn-xs btn-danger delete-variant-btn" data-id="<?php echo $v['id']; ?>">
                    <i class="fa fa-trash"></i>
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($variants)): ?>
              <tr id="noVariantsRow">
                <td colspan="6" class="text-center text-muted">No variants yet.</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Add / Edit Variant Form -->
        <div class="form-card" style="background:#f9f9f9;border:1px solid #e0e0e0;border-radius:8px;padding:20px;margin-top:10px">
          <h4 class="margin-bottom-15" id="variantFormTitle"><i class="fa fa-plus-circle text-saffron"></i> Add Variant</h4>
          <input type="hidden" id="editVariantId" value="0">
          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label>Type *</label>
                <div class="input-group">
                  <select class="form-control" id="variantTypeSelect" onchange="syncVariantType(this)">
                    <option value="size">Size</option>
                    <option value="color">Color</option>
                    <option value="pack">Pack</option>
                    <option value="custom">Custom…</option>
                  </select>
                </div>
                <input type="text" class="form-control margin-t-5" id="variantTypeCustom"
                       placeholder="e.g. flavor, material…" style="display:none">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Value *</label>
                <input type="text" class="form-control" id="variantValue" placeholder="e.g. 100g, Red, Large">
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label>Price Modifier (₹)</label>
                <input type="number" class="form-control" id="variantPrice" step="0.01" value="0"
                       placeholder="+50 or -20">
                <small class="text-muted">Added to base price</small>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label>Stock Qty</label>
                <input type="number" class="form-control" id="variantStock" min="0" value="0">
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label>SKU <small class="text-muted">(auto)</small></label>
                <div class="input-group">
                  <input type="text" class="form-control" id="variantSku"
                         data-auto="1" placeholder="Auto-generated">
                  <span class="input-group-btn">
                    <button type="button" class="btn btn-default" id="varSkuGenBtn"
                            title="Re-generate SKU"><i class="fa fa-magic"></i></button>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Color picker — shown only when type = color -->
          <div class="row" id="editColorRow" style="display:none">
            <div class="col-md-4">
              <div class="form-group">
                <label><i class="fa fa-paint-brush"></i> Color Code</label>
                <div class="input-group">
                  <input type="color" id="editColorPicker" value="#7B4228"
                         style="width:40px;height:34px;padding:2px;border:1px solid #ccc;border-radius:4px 0 0 4px;cursor:pointer">
                  <input type="text" class="form-control" id="editColorHex"
                         value="#7B4228" maxlength="7" style="font-family:monospace"
                         placeholder="#RRGGBB">
                  <span class="input-group-addon" id="editColorSwatch"
                        style="width:36px;background:#7B4228;border-radius:0 4px 4px 0"></span>
                </div>
                <small class="text-muted">Shown as color swatch on the product page</small>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <button class="btn btn-saffron" id="saveVariantBtn">
                <i class="fa fa-save"></i> <span id="saveVariantLabel">Save Variant</span>
              </button>
              <button class="btn btn-default margin-l-5" id="cancelEditBtn" style="display:none">
                <i class="fa fa-times"></i> Cancel Edit
              </button>
            </div>
          </div>
        </div>

        <!-- Color Swatches Preview (visible when type=color) -->
        <div id="colorSwatchPreview" style="display:none;margin-top:10px">
          <label class="small text-muted">Color Preview:</label>
          <span id="colorSwatch" style="display:inline-block;width:30px;height:30px;border-radius:50%;border:1px solid #ccc;vertical-align:middle;margin-left:5px"></span>
        </div>

      </div><!-- /tab-variants -->

    </div><!-- /tab-content -->
  </div><!-- /box-body -->
</div>

<script>
var PRODUCT_ID  = <?php echo (int)$pid; ?>;
var SAVE_VAR    = '<?php echo $save_url; ?>';
var DEL_VAR     = '<?php echo $del_var_url; ?>';
var UPLOAD_IMG  = '<?php echo $upload_url; ?>';
var DEL_IMG     = '<?php echo $del_img_url; ?>';
var PRI_IMG     = '<?php echo $pri_img_url; ?>';

function previewPrimary(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('primaryImgPreview').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function syncVariantType(sel) {
  var custom = document.getElementById('variantTypeCustom');
  if (sel.value === 'custom') {
    custom.style.display = 'block';
    custom.focus();
  } else {
    custom.style.display = 'none';
  }
  toggleColorPreview();
}

function toggleColorPreview() {
  var sel   = document.getElementById('variantTypeSelect');
  var wrap  = document.getElementById('colorSwatchPreview');
  var input = document.getElementById('variantValue');
  wrap.style.display = (sel.value === 'color') ? 'block' : 'none';
  updateSwatch(input.value);
}

function updateSwatch(val) {
  document.getElementById('colorSwatch').style.background = val || '#ccc';
}
</script>
