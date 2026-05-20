<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Breadcrumb -->
<div class="container mt-3">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?php echo site_url('home'); ?>">Home</a></li>
      <li class="breadcrumb-item"><a href="<?php echo site_url('shop'); ?>">Shop</a></li>
      <li class="breadcrumb-item">
        <a href="<?php echo site_url('shop'); ?>?category=<?php echo htmlspecialchars($product['cat_slug']); ?>">
          <?php echo htmlspecialchars($product['cat_name']); ?>
        </a>
      </li>
      <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
    </ol>
  </nav>
</div>

<div class="container pb-5">
  <!-- Product Main -->
  <div class="row g-5 mb-5">
    <div class="col-lg-5">
      <img id="mainProductImg"
           src="<?php echo $this->spice_model->product_image($product['image']); ?>"
           alt="<?php echo htmlspecialchars($product['name']); ?>"
           class="product-main-img mb-3">
      <div class="d-flex gap-2 flex-wrap">
        <img src="<?php echo $this->spice_model->product_image($product['image']); ?>"
             class="product-thumb active" alt=""
             onclick="document.getElementById('mainProductImg').src=this.src">
        <?php foreach ($extra_images as $ei): ?>
          <img src="<?php echo base_url('uploads/products/'.$ei['image']); ?>"
               class="product-thumb" alt=""
               onclick="document.getElementById('mainProductImg').src=this.src">
        <?php endforeach; ?>
      </div>
    </div>

    <div class="col-lg-7">
      <p class="text-saffron fw-500 mb-1 small">
        <a href="<?php echo site_url('shop'); ?>?category=<?php echo htmlspecialchars($product['cat_slug']); ?>" class="text-saffron">
          <?php echo htmlspecialchars($product['cat_name']); ?>
        </a>
        <?php if (!empty($product['brand_name'])): ?>
          <span class="text-muted"> · <?php echo htmlspecialchars($product['brand_name']); ?></span>
        <?php endif; ?>
      </p>
      <h1 style="font-family:'Playfair Display',serif;font-size:2rem"><?php echo htmlspecialchars($product['name']); ?></h1>

      <?php if ($review_count > 0): ?>
      <div class="d-flex align-items-center gap-2 mb-3">
        <?php echo $this->spice_model->star_rating($avg_rating); ?>
        <span class="text-muted small"><?php echo round($avg_rating,1); ?> / 5 (<?php echo $review_count; ?> review<?php echo $review_count!==1?'s':''; ?>)</span>
      </div>
      <?php endif; ?>

      <?php $base_price = (!empty($product['offer_price']) && $product['offer_price'] > 0) ? (float)$product['offer_price'] : (float)$product['price']; ?>
      <div class="mb-3 d-flex align-items-baseline gap-3">
        <?php if (!empty($product['offer_price']) && $product['offer_price'] > 0): ?>
          <span id="productPrice" data-base-price="<?php echo $base_price; ?>" style="font-size:2rem;font-weight:700;color:var(--saffron)"><?php echo $this->spice_model->rupees($base_price); ?></span>
          <span class="text-muted text-decoration-line-through fs-5"><?php echo $this->spice_model->rupees((float)$product['price']); ?></span>
          <?php $saving = round((($product['price']-$product['offer_price'])/$product['price'])*100); ?>
          <span class="badge bg-danger" id="savingBadge"><?php echo $saving; ?>% OFF</span>
        <?php else: ?>
          <span id="productPrice" data-base-price="<?php echo $base_price; ?>" style="font-size:2rem;font-weight:700;color:var(--saffron)"><?php echo $this->spice_model->rupees($base_price); ?></span>
        <?php endif; ?>
        <?php if (!empty($product['weight'])): ?>
          <span class="text-muted small">per <?php echo htmlspecialchars($product['weight']); ?></span>
        <?php endif; ?>
      </div>

      <?php if ($product['stock_qty'] > 0): ?>
        <div class="mb-3">
          <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>In Stock</span>
          <?php if ($product['stock_qty'] < 20): ?>
            <span class="badge bg-warning text-dark ms-2">Only <?php echo $product['stock_qty']; ?> left!</span>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="mb-3"><span class="badge bg-secondary">Out of Stock</span></div>
      <?php endif; ?>

      <!-- Variants -->
      <?php if (!empty($variants)): ?>
      <div class="mb-3" id="variantSection">
        <?php
        $variantGroups = array();
        foreach ($variants as $v) {
          $variantGroups[$v['variant_type']][] = $v;
        }
        foreach ($variantGroups as $vtype => $vopts):
          $isColor = ($vtype === 'color');
        ?>
        <div class="mb-3">
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="fw-600 small">
              <?php echo htmlspecialchars(ucfirst($vtype)); ?>:
            </span>
            <span class="text-danger small fw-500" id="req_<?php echo htmlspecialchars($vtype); ?>"
                  style="display:none">⚠ Please select</span>
            <?php if ($isColor): ?>
              <span class="text-muted small" id="colorNameLabel_<?php echo htmlspecialchars($vtype); ?>"></span>
            <?php endif; ?>
          </div>

          <div class="d-flex flex-wrap gap-2">
          <?php foreach ($vopts as $vo):
            $hasHex   = ($isColor && !empty($vo['color_hex']));
            $modLabel = '';
            if ($vo['price_modifier'] != 0) {
              $modLabel = ' (' . ($vo['price_modifier'] > 0 ? '+' : '') . '₹' . abs($vo['price_modifier']) . ')';
            }
          ?>

            <?php if ($hasHex): ?>
              <!-- Color swatch button -->
              <button type="button"
                      class="color-swatch-btn variant-btn"
                      data-variant-id="<?php echo $vo['id']; ?>"
                      data-variant-type="<?php echo htmlspecialchars($vtype); ?>"
                      data-variant-value="<?php echo htmlspecialchars($vo['variant_value']); ?>"
                      data-price-mod="<?php echo $vo['price_modifier']; ?>"
                      data-stock="<?php echo (int)$vo['stock_qty']; ?>"
                      data-sku="<?php echo htmlspecialchars($vo['sku'] ?? ''); ?>"
                      data-color-hex="<?php echo htmlspecialchars($vo['color_hex']); ?>"
                      data-color-name="<?php echo htmlspecialchars($vo['variant_value']); ?>"
                      style="background:<?php echo htmlspecialchars($vo['color_hex']); ?>"
                      title="<?php echo htmlspecialchars($vo['variant_value'].$modLabel); ?>"
                      aria-label="<?php echo htmlspecialchars($vo['variant_value']); ?>">
                <?php if ((int)$vo['stock_qty'] === 0): ?>
                  <span class="swatch-oos"></span>
                <?php endif; ?>
              </button>

            <?php else: ?>
              <!-- Text / size button — always clickable; OOS is handled in JS -->
              <button type="button"
                      class="btn btn-sm btn-outline-secondary variant-btn<?php echo (int)$vo['stock_qty'] === 0 ? ' variant-oos' : ''; ?>"
                      data-variant-id="<?php echo $vo['id']; ?>"
                      data-variant-type="<?php echo htmlspecialchars($vtype); ?>"
                      data-variant-value="<?php echo htmlspecialchars($vo['variant_value']); ?>"
                      data-price-mod="<?php echo $vo['price_modifier']; ?>"
                      data-stock="<?php echo (int)$vo['stock_qty']; ?>"
                      data-sku="<?php echo htmlspecialchars($vo['sku'] ?? ''); ?>"
                      title="<?php echo (int)$vo['stock_qty'] === 0 ? 'Out of stock' : htmlspecialchars($vo['variant_value'].$modLabel); ?>">
                <?php echo htmlspecialchars($vo['variant_value']); ?>
                <?php if ($modLabel): ?>
                  <small class="opacity-75"><?php echo $modLabel; ?></small>
                <?php endif; ?>
                <?php if ((int)$vo['stock_qty'] === 0): ?>
                  <small style="font-size:.65rem;display:block;line-height:1;opacity:.7">OOS</small>
                <?php endif; ?>
              </button>
            <?php endif; ?>

          <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
        <div class="small text-muted mt-1" id="variantSku"></div>
      </div>
      <?php endif; ?>

      <?php if (!empty($product['weight'])): ?>
      <div class="mb-3">
        <span class="badge rounded-pill" style="background:rgba(123,66,40,.15);color:var(--saffron);font-size:.85rem;padding:.4rem .9rem">
          📦 <?php echo htmlspecialchars($product['weight']); ?>
        </span>
      </div>
      <?php endif; ?>

      <p class="text-muted mb-4" style="line-height:1.75"><?php echo nl2br(htmlspecialchars($product['description'] ?? '')); ?></p>

      <div class="d-flex gap-3 align-items-center mb-4 flex-wrap">
        <?php if ($product['stock_qty'] > 0): ?>
          <div class="d-flex align-items-center border rounded-3 overflow-hidden" style="height:44px">
            <button class="btn btn-light px-3 h-100" onclick="const i=document.getElementById('qtyInput');i.value=Math.max(1,parseInt(i.value)-1)">−</button>
            <input type="number" id="qtyInput" value="1" min="1"
                   max="<?php echo $product['stock_qty']; ?>"
                   class="form-control border-0 text-center fw-600"
                   style="width:55px;height:100%">
            <button class="btn btn-light px-3 h-100" onclick="const i=document.getElementById('qtyInput');i.value=Math.min(<?php echo $product['stock_qty']; ?>,parseInt(i.value)+1)">+</button>
          </div>
          <button class="btn btn-saffron btn-lg flex-grow-1"
                  data-add-cart="<?php echo $product['id']; ?>"
                  data-qty="1"
                  <?php if (!empty($variants)): ?>data-has-variants="1"<?php endif; ?>
                  id="addToCartBtn">
            <i class="bi bi-bag-plus me-2"></i>Add to Cart
          </button>
          <button class="btn btn-outline-danger"
                  data-wishlist="<?php echo $product['id']; ?>"
                  id="wishlistBtn"
                  title="<?php echo $in_wishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>">
            <i class="bi bi-heart<?php echo $in_wishlist ? '-fill' : ''; ?>" id="wishlistIcon"></i>
          </button>
        <?php else: ?>
          <button class="btn btn-secondary btn-lg flex-grow-1" disabled>Out of Stock</button>
        <?php endif; ?>
      </div>

      <div class="d-flex flex-wrap gap-2 mb-3">
        <span class="badge rounded-pill bg-light text-dark border">🌿 100% Natural</span>
        <span class="badge rounded-pill bg-light text-dark border">🚚 Free Shipping ₹499+</span>
        <span class="badge rounded-pill bg-light text-dark border">↩️ 7-Day Returns</span>
      </div>
    </div>
  </div>

  <!-- Reviews -->
  <div id="reviews" class="row g-4">
    <div class="col-lg-8">
      <h3 class="section-title mb-4">Customer Reviews</h3>

      <?php if (!empty($review_success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($review_success); ?></div>
      <?php endif; ?>

      <?php if (empty($reviews)): ?>
        <p class="text-muted">No reviews yet. Be the first to review!</p>
      <?php else: ?>
        <?php foreach ($reviews as $rev): ?>
        <div class="mb-4 pb-4 border-bottom">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <strong><?php echo htmlspecialchars($rev['user_name']); ?></strong>
            <small class="text-muted"><?php echo date('d M Y', strtotime($rev['created_at'])); ?></small>
          </div>
          <?php echo $this->spice_model->star_rating((float)$rev['rating']); ?>
          <?php if ($rev['comment']): ?>
            <p class="mt-2 mb-0 text-muted"><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></p>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="col-lg-4">
      <div class="filter-card">
        <h5 class="mb-3">Write a Review</h5>
        <?php if (!$is_logged_in): ?>
          <p class="text-muted small">Please <a href="<?php echo site_url('login'); ?>">login</a> to leave a review.</p>
        <?php else: ?>
          <?php if (!empty($error)): ?>
            <div class="alert alert-danger small"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>
          <form method="post" action="<?php echo site_url('product/'.$product['id']); ?>#reviews">
            <input type="hidden" name="submit_review" value="1">
            <div class="mb-3">
              <label class="form-label small fw-600">Your Rating *</label>
              <div id="starPicker">
                <?php for ($s = 1; $s <= 5; $s++): ?>
                  <i class="bi bi-star text-warning fs-4 me-1" style="cursor:pointer" data-star="<?php echo $s; ?>"></i>
                <?php endfor; ?>
              </div>
              <input type="hidden" id="ratingInput" name="rating" value="0">
            </div>
            <div class="mb-3">
              <label class="form-label small fw-600">Comment (optional)</label>
              <textarea class="form-control form-control-sm" name="comment" rows="4"
                        placeholder="Share your experience…"></textarea>
            </div>
            <button type="submit" class="btn btn-saffron w-100 btn-sm">Submit Review</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Related Products -->
  <?php if (!empty($related)): ?>
  <div class="mt-5">
    <h3 class="section-title mb-4">You May Also Like</h3>
    <div class="row g-4">
      <?php foreach ($related as $rp): ?>
      <div class="col-6 col-md-3">
        <div class="product-card">
          <div class="product-img-wrap">
            <a href="<?php echo site_url('product/'.$rp['id']); ?>">
              <img src="<?php echo $this->spice_model->product_image($rp['image']); ?>"
                   alt="<?php echo htmlspecialchars($rp['name']); ?>" loading="lazy">
            </a>
          </div>
          <div class="card-body">
            <h6 class="product-title mb-1">
              <a href="<?php echo site_url('product/'.$rp['id']); ?>" class="text-dark"><?php echo htmlspecialchars($rp['name']); ?></a>
            </h6>
            <p class="product-weight mb-2"><?php echo htmlspecialchars($rp['weight'] ?? ''); ?></p>
            <?php if (!empty($rp['offer_price']) && $rp['offer_price'] > 0): ?>
              <span class="product-price"><?php echo $this->spice_model->rupees((float)$rp['offer_price']); ?></span>
              <span class="text-muted text-decoration-line-through small ms-1"><?php echo $this->spice_model->rupees((float)$rp['price']); ?></span>
            <?php else: ?>
              <span class="product-price"><?php echo $this->spice_model->rupees((float)$rp['price']); ?></span>
            <?php endif; ?>
            <?php if ($rp['stock_qty'] > 0): ?>
            <button class="btn-add-cart mt-2" data-add-cart="<?php echo $rp['id']; ?>">
              <i class="bi bi-bag-plus me-1"></i> Add to Cart
            </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
