<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if (empty($products)): ?>
  <div class="empty-state">
    <div class="empty-icon">🔍</div>
    <h5 class="mt-3">No products found</h5>
    <p class="text-muted">Try adjusting your filters or <a href="<?php echo site_url('shop'); ?>">browse all products</a>.</p>
  </div>
<?php else: ?>
  <div class="row g-4">
    <?php foreach ($products as $p): ?>
    <div class="col-6 col-md-4 col-xl-3">
      <div class="product-card">
        <div class="product-img-wrap">
          <a href="<?php echo site_url('product/'.$p['id']); ?>">
            <img src="<?php echo $this->spice_model->product_image($p['image']); ?>"
                 alt="<?php echo htmlspecialchars($p['name']); ?>" loading="lazy">
          </a>
          <?php if (!empty($p['offer_price']) && $p['offer_price'] > 0): ?>
            <?php $saving = round((($p['price']-$p['offer_price'])/$p['price'])*100); ?>
            <span class="badge bg-danger stock-badge">-<?php echo $saving; ?>%</span>
          <?php elseif ($p['stock_qty'] < 1): ?>
            <span class="badge bg-secondary stock-badge">Out of Stock</span>
          <?php elseif ($p['stock_qty'] < 20): ?>
            <span class="badge bg-warning text-dark stock-badge">Low Stock</span>
          <?php endif; ?>
          <button class="btn-wishlist" data-wishlist="<?php echo $p['id']; ?>" title="Add to Wishlist">
            <i class="bi bi-heart"></i>
          </button>
        </div>
        <div class="card-body">
          <p class="product-weight mb-1"><?php echo htmlspecialchars($p['cat_name']); ?></p>
          <h6 class="product-title mb-1">
            <a href="<?php echo site_url('product/'.$p['id']); ?>" class="text-dark">
              <?php echo htmlspecialchars($p['name']); ?>
            </a>
          </h6>
          <?php if ($p['avg_rating'] > 0): ?>
            <div class="mb-1">
              <?php echo $this->spice_model->star_rating((float)$p['avg_rating']); ?>
              <small class="text-muted">(<?php echo $p['review_count']; ?>)</small>
            </div>
          <?php endif; ?>
          <p class="product-weight mb-2"><?php echo htmlspecialchars($p['weight'] ?? ''); ?></p>
          <div class="d-flex align-items-center gap-2 mb-2">
            <?php if (!empty($p['offer_price']) && $p['offer_price'] > 0): ?>
              <span class="product-price"><?php echo $this->spice_model->rupees((float)$p['offer_price']); ?></span>
              <span class="text-muted text-decoration-line-through small"><?php echo $this->spice_model->rupees((float)$p['price']); ?></span>
            <?php else: ?>
              <span class="product-price"><?php echo $this->spice_model->rupees((float)$p['price']); ?></span>
            <?php endif; ?>
          </div>
          <?php if ($p['stock_qty'] > 0): ?>
            <button class="btn-add-cart" data-add-cart="<?php echo $p['id']; ?>">
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
