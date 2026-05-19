<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Banners Carousel -->
<?php if (!empty($banners)): ?>
<section class="py-4" style="background:#fdf6f0">
  <div class="container">
    <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner rounded-3 overflow-hidden shadow-soft">
        <?php foreach ($banners as $bi => $b): ?>
        <div class="carousel-item <?php echo $bi === 0 ? 'active' : ''; ?>">
          <?php if (!empty($b['image'])): ?>
            <?php $blink = !empty($b['link']) ? $b['link'] : '#'; ?>
            <a href="<?php echo htmlspecialchars($blink); ?>">
              <img src="<?php echo base_url('uploads/banners/'.$b['image']); ?>"
                   class="d-block w-100" style="max-height:400px;object-fit:cover"
                   alt="<?php echo htmlspecialchars($b['title'] ?? ''); ?>">
            </a>
          <?php else: ?>
            <div class="d-flex align-items-center justify-content-center"
                 style="height:280px;background:linear-gradient(135deg,#2C1810,var(--saffron))">
              <div class="text-center text-white px-4">
                <?php if (!empty($b['title'])): ?>
                  <h2 style="font-family:'Playfair Display',serif"><?php echo htmlspecialchars($b['title']); ?></h2>
                <?php endif; ?>
                <?php if (!empty($b['subtitle'])): ?>
                  <p class="mb-3 text-white-75"><?php echo htmlspecialchars($b['subtitle']); ?></p>
                <?php endif; ?>
                <?php if (!empty($b['link']) && !empty($b['btn_text'])): ?>
                  <a href="<?php echo htmlspecialchars($b['link']); ?>" class="btn btn-saffron"><?php echo htmlspecialchars($b['btn_text']); ?></a>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if (count($banners) > 1): ?>
      <div class="carousel-indicators">
        <?php foreach ($banners as $bi => $b): ?>
          <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="<?php echo $bi; ?>"
                  <?php echo $bi === 0 ? 'class="active"' : ''; ?>></button>
        <?php endforeach; ?>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
      </button>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Hero -->
<section class="hero-section">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <div class="hero-badge mb-3">🌿 Farm-to-Table Spices</div>
        <h1 class="hero-title mb-3">
          Taste the<br>
          <span style="color:var(--saffron)">Authenticity</span><br>
          of Pure Spices
        </h1>
        <p class="text-white-50 mb-4" style="font-size:1.05rem;max-width:480px">
          Hand-picked from the finest farms across India.
          No additives. No preservatives. Just pure, natural flavour.
        </p>
        <div class="d-flex flex-wrap gap-3">
          <a href="<?php echo site_url('shop'); ?>" class="btn btn-saffron btn-lg px-4">
            Shop Fresh Spices <i class="bi bi-arrow-right ms-2"></i>
          </a>
          <a href="<?php echo site_url('shop'); ?>?category=blended-masala" class="btn btn-outline-light btn-lg px-4">
            Explore Masalas
          </a>
        </div>
        <div class="hero-emoji-row mt-4">🌶️ 🫚 🧅 🫛 🥜 🌿</div>
        <div class="d-flex flex-wrap gap-4 mt-4">
          <div class="text-white-50 d-flex align-items-center gap-2 small"><span class="fs-5">✅</span> 100% Pure</div>
          <div class="text-white-50 d-flex align-items-center gap-2 small"><span class="fs-5">🚚</span> Free Shipping ₹499+</div>
          <div class="text-white-50 d-flex align-items-center gap-2 small"><span class="fs-5">↩️</span> Easy Returns</div>
        </div>
      </div>
      <div class="col-lg-5 d-none d-lg-flex justify-content-center">
        <div style="font-size:10rem;line-height:1;filter:drop-shadow(0 20px 40px rgba(0,0,0,.4))">🍛</div>
      </div>
    </div>
  </div>
</section>

<!-- Stats Strip -->
<div style="background:var(--saffron)">
  <div class="container">
    <div class="row text-white text-center py-3">
      <div class="col-6 col-md-3 py-2">
        <div class="fw-700 fs-4" style="font-family:'Playfair Display',serif">15+</div>
        <div class="small opacity-75">Product Varieties</div>
      </div>
      <div class="col-6 col-md-3 py-2">
        <div class="fw-700 fs-4" style="font-family:'Playfair Display',serif">5000+</div>
        <div class="small opacity-75">Happy Customers</div>
      </div>
      <div class="col-6 col-md-3 py-2">
        <div class="fw-700 fs-4" style="font-family:'Playfair Display',serif">12+</div>
        <div class="small opacity-75">States We Deliver</div>
      </div>
      <div class="col-6 col-md-3 py-2">
        <div class="fw-700 fs-4" style="font-family:'Playfair Display',serif">100%</div>
        <div class="small opacity-75">Natural & Pure</div>
      </div>
    </div>
  </div>
</div>

<!-- Categories -->
<section class="py-5">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-4">
      <div>
        <p class="text-saffron fw-500 mb-1 small text-uppercase ls-wide">Browse By</p>
        <h2 class="section-title mb-0">Categories</h2>
      </div>
      <a href="<?php echo site_url('shop'); ?>" class="btn btn-outline-saffron btn-sm">View All</a>
    </div>
    <?php
    $catEmojis = array(
      'whole-spices'   => '🌶️',
      'ground-masala'  => '🟡',
      'blended-masala' => '🍛',
      'seeds'          => '🌱',
      'dry-fruits'     => '🥜',
    );
    ?>
    <div class="row g-3">
      <?php foreach ($categories as $cat): ?>
      <div class="col-6 col-md-4 col-lg-2">
        <a href="<?php echo site_url('shop'); ?>?category=<?php echo htmlspecialchars($cat['slug']); ?>" class="text-decoration-none">
          <div class="category-card text-center">
            <div class="category-img-wrap">
              <?php if (!empty($cat['image']) && file_exists(FCPATH.'uploads/products/'.$cat['image'])): ?>
                <img src="<?php echo base_url('uploads/products/'.$cat['image']); ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>">
              <?php else: ?>
                <span class="cat-emoji"><?php echo $catEmojis[$cat['slug']] ?? '🌿'; ?></span>
              <?php endif; ?>
            </div>
            <div class="card-body">
              <p class="card-title"><?php echo htmlspecialchars($cat['name']); ?></p>
            </div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Featured Products -->
<?php if (!empty($featured)): ?>
<section class="py-5" style="background:#fff">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-4">
      <div>
        <p class="text-saffron fw-500 mb-1 small text-uppercase">Hand Picked</p>
        <h2 class="section-title mb-0">Featured Products</h2>
      </div>
      <a href="<?php echo site_url('shop'); ?>" class="btn btn-outline-saffron btn-sm">View All</a>
    </div>
    <div class="row g-4">
      <?php foreach ($featured as $p): ?>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="product-card">
          <div class="product-img-wrap">
            <img src="<?php echo $this->spice_model->product_image($p['image']); ?>"
                 alt="<?php echo htmlspecialchars($p['name']); ?>" loading="lazy">
            <span class="badge stock-badge" style="background:var(--saffron)">⭐ Featured</span>
            <?php if ($p['stock_qty'] < 1): ?>
              <span class="badge bg-secondary stock-badge" style="top:32px">Out of Stock</span>
            <?php endif; ?>
          </div>
          <div class="card-body">
            <p class="product-weight mb-1"><?php echo htmlspecialchars($p['cat_name']); ?></p>
            <h6 class="product-title mb-1">
              <a href="<?php echo site_url('product/'.$p['id']); ?>" class="text-dark">
                <?php echo htmlspecialchars($p['name']); ?>
              </a>
            </h6>
            <?php $avg = $this->spice_model->avg_rating($p['id']); ?>
            <?php if ($avg > 0): ?>
              <div class="mb-2">
                <?php echo $this->spice_model->star_rating($avg); ?>
                <small class="text-muted">(<?php echo $this->spice_model->review_count($p['id']); ?>)</small>
              </div>
            <?php endif; ?>
            <?php if (!empty($p['weight'])): ?>
              <p class="product-weight mb-2"><?php echo htmlspecialchars($p['weight']); ?></p>
            <?php endif; ?>
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
  </div>
</section>
<?php endif; ?>

<!-- Latest Products -->
<section class="py-5" style="background:#fdf6f0">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-4">
      <div>
        <p class="text-saffron fw-500 mb-1 small text-uppercase">Trending Now</p>
        <h2 class="section-title mb-0">Best Sellers</h2>
      </div>
      <a href="<?php echo site_url('shop'); ?>" class="btn btn-outline-saffron btn-sm">View All Products</a>
    </div>
    <div class="row g-4">
      <?php foreach ($products as $p): ?>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="product-card">
          <div class="product-img-wrap">
            <img src="<?php echo $this->spice_model->product_image($p['image']); ?>"
                 alt="<?php echo htmlspecialchars($p['name']); ?>" loading="lazy">
            <?php if ($p['stock_qty'] < 1): ?>
              <span class="badge bg-secondary stock-badge">Out of Stock</span>
            <?php elseif ($p['stock_qty'] < 20): ?>
              <span class="badge bg-warning text-dark stock-badge">Low Stock</span>
            <?php endif; ?>
          </div>
          <div class="card-body">
            <p class="product-weight mb-1"><?php echo htmlspecialchars($p['cat_name']); ?></p>
            <h6 class="product-title mb-1">
              <a href="<?php echo site_url('product/'.$p['id']); ?>" class="text-dark">
                <?php echo htmlspecialchars($p['name']); ?>
              </a>
            </h6>
            <?php $avg = $this->spice_model->avg_rating($p['id']); ?>
            <?php if ($avg > 0): ?>
              <div class="mb-2">
                <?php echo $this->spice_model->star_rating($avg); ?>
                <small class="text-muted">(<?php echo $this->spice_model->review_count($p['id']); ?>)</small>
              </div>
            <?php endif; ?>
            <?php if (!empty($p['weight'])): ?>
              <p class="product-weight mb-2"><?php echo htmlspecialchars($p['weight']); ?></p>
            <?php endif; ?>
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
  </div>
</section>

<!-- Why Choose Us -->
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <p class="text-saffron fw-500 small text-uppercase">Why SpiceMart</p>
      <h2 class="section-title d-inline-block">Why Choose Us?</h2>
    </div>
    <div class="row g-4">
      <?php
      $whyCards = array(
        array('🌿', 'Farm-to-Table',     'Sourced directly from trusted farms. Fresh stock every month.'),
        array('✅', '100% Pure',          'Zero adulterants, artificial colours, or preservatives. Ever.'),
        array('🧪', 'Lab Tested',         'Every batch is tested for purity, potency, and heavy metals.'),
        array('📦', 'Hygienic Packaging', 'Food-grade, airtight, moisture-proof packaging.'),
        array('🚚', 'Fast Delivery',      'Pan-India delivery in 3–5 business days. Free above ₹499.'),
        array('🔄', 'Easy Returns',       '7-day hassle-free return policy on all products.'),
      );
      foreach ($whyCards as $card):
      ?>
      <div class="col-6 col-md-4 col-lg-2">
        <div class="why-card h-100">
          <div class="why-icon"><?php echo $card[0]; ?></div>
          <h6 class="fw-600 mb-2"><?php echo $card[1]; ?></h6>
          <p class="text-muted small mb-0"><?php echo $card[2]; ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Testimonials -->
<section class="py-5" style="background:linear-gradient(135deg,#2C1810,#5C2E1A);color:#fff">
  <div class="container text-center">
    <h2 class="mb-4" style="font-family:'Playfair Display',serif">What Our Customers Say</h2>
    <div class="row g-4 justify-content-center">
      <?php
      $testimonials = array(
        array('⭐⭐⭐⭐⭐', 'The garam masala is out of this world! I can smell the freshness the moment I open the pack.', 'Lakshmi R., Coimbatore'),
        array('⭐⭐⭐⭐⭐', 'Finally found a brand I can trust for pure spices. Fast delivery and great packaging.', 'Rahul M., Bangalore'),
        array('⭐⭐⭐⭐⭐', 'The kashmiri chilli gives such beautiful colour to my dishes. My family loves it!', 'Ananya S., Chennai'),
      );
      foreach ($testimonials as $t):
      ?>
      <div class="col-md-4">
        <div class="p-4 h-100" style="background:rgba(255,255,255,.07);border-radius:16px;border:1px solid rgba(255,255,255,.1)">
          <div class="mb-2"><?php echo $t[0]; ?></div>
          <p class="text-white-75 mb-3" style="font-style:italic">"<?php echo $t[1]; ?>"</p>
          <p class="small text-white-50 mb-0">— <?php echo $t[2]; ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
