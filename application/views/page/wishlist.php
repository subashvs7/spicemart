<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container py-4">
  <div class="d-flex align-items-center gap-2 mb-4">
    <span class="fs-2">❤️</span>
    <div>
      <h2 class="mb-0" style="font-family:'Playfair Display',serif">My Wishlist</h2>
      <small class="text-muted"><?php echo count($items); ?> item<?php echo count($items)!==1?'s':''; ?></small>
    </div>
  </div>

  <?php if (empty($items)): ?>
    <div class="empty-state">
      <div class="empty-icon">🛒</div>
      <h5 class="mt-3">Your wishlist is empty</h5>
      <p class="text-muted">Save items you love by clicking the heart icon.</p>
      <a href="<?php echo site_url('shop'); ?>" class="btn btn-saffron mt-2">Browse Products</a>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($items as $item): ?>
      <div class="col-6 col-md-4 col-lg-3" id="wl-item-<?php echo $item['product_id']; ?>">
        <div class="product-card">
          <div class="product-img-wrap">
            <a href="<?php echo site_url('product/'.$item['product_id']); ?>">
              <img src="<?php echo $this->spice_model->product_image($item['image']); ?>"
                   alt="<?php echo htmlspecialchars($item['name']); ?>" loading="lazy">
            </a>
            <?php if ($item['stock_qty'] < 1): ?>
              <span class="badge bg-secondary stock-badge">Out of Stock</span>
            <?php endif; ?>
            <button class="btn btn-sm btn-wishlist-rm position-absolute"
                    style="top:8px;right:8px;background:rgba(255,255,255,.9);border:none;border-radius:50%;width:34px;height:34px;padding:0;line-height:1"
                    data-wishlist="<?php echo $item['product_id']; ?>"
                    title="Remove from Wishlist">
              <i class="bi bi-heart-fill text-danger"></i>
            </button>
          </div>
          <div class="card-body">
            <p class="product-weight mb-1"><?php echo htmlspecialchars($item['cat_name']); ?></p>
            <h6 class="product-title mb-1">
              <a href="<?php echo site_url('product/'.$item['product_id']); ?>" class="text-dark">
                <?php echo htmlspecialchars($item['name']); ?>
              </a>
            </h6>
            <div class="d-flex align-items-center gap-2 mb-2">
              <?php if (!empty($item['offer_price']) && $item['offer_price'] > 0): ?>
                <span class="product-price"><?php echo $this->spice_model->rupees((float)$item['offer_price']); ?></span>
                <span class="text-muted text-decoration-line-through small"><?php echo $this->spice_model->rupees((float)$item['price']); ?></span>
              <?php else: ?>
                <span class="product-price"><?php echo $this->spice_model->rupees((float)$item['price']); ?></span>
              <?php endif; ?>
            </div>
            <?php if ($item['stock_qty'] > 0): ?>
              <button class="btn-add-cart" data-add-cart="<?php echo $item['product_id']; ?>">
                <i class="bi bi-bag-plus me-1"></i> Add to Cart
              </button>
            <?php else: ?>
              <button class="btn-add-cart" disabled style="opacity:.5">Out of Stock</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
