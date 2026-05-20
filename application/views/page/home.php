<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- ══════════════════════════════════════════════════
     HERO
══════════════════════════════════════════════════ -->
<!-- <section class="hero-v2">
  <div class="hero-v2-particles" aria-hidden="true">
    <span class="hv2-p p1">🌶️</span>
    <span class="hv2-p p2">🌿</span>
    <span class="hv2-p p3">🧄</span>
    <span class="hv2-p p4">🫚</span>
    <span class="hv2-p p5">🌰</span>
    <span class="hv2-p p6">⭐</span>
  </div>
  <div class="container position-relative" style="z-index:2">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <div class="hero-v2-pill mb-3">
          <span class="hero-v2-dot"></span>
          Farm-to-Table · Lab Tested · 100% Pure
        </div>
        <h1 class="hero-v2-title mb-3">
          Taste the<br>
          <em class="hero-gradient-text">Authenticity</em><br>
          of Pure Spices
        </h1>
        <p class="hero-v2-desc mb-4">
          Hand-picked from the finest farms across India. No additives,
          no preservatives — just pure, bold, natural flavour in every pack.
        </p>
        <div class="d-flex flex-wrap gap-3 mb-4">
          <a href="<?php echo site_url('shop'); ?>" class="btn-hv2-primary">
            <i class="bi bi-bag-heart me-2"></i>Shop Now
          </a>
          <a href="<?php echo site_url('shop'); ?>?category=blended-masala" class="btn-hv2-ghost">
            Explore Masalas <i class="bi bi-arrow-right ms-1"></i>
          </a>
        </div>
        <div class="hero-v2-trust">
          <div class="hv2-trust-item"><i class="bi bi-patch-check-fill"></i><span>100% Pure</span></div>
          <div class="hv2-trust-item"><i class="bi bi-truck"></i><span>Free Ship ₹499+</span></div>
          <div class="hv2-trust-item"><i class="bi bi-arrow-counterclockwise"></i><span>7-Day Returns</span></div>
          <div class="hv2-trust-item"><i class="bi bi-shield-check"></i><span>Secure Pay</span></div>
        </div>
      </div>
      <div class="col-lg-5 d-none d-lg-flex justify-content-center">
        <div class="hero-v2-visual">
          <div class="hv2-bowl">🍛</div>
          <div class="hv2-float-card c1"><i class="bi bi-star-fill text-warning"></i> 4.9 / 5 Rating</div>
          <div class="hv2-float-card c2"><i class="bi bi-people-fill"></i> 5K+ Customers</div>
          <div class="hv2-float-card c3"><i class="bi bi-award-fill text-warning"></i> Lab Certified</div>
        </div>
      </div>
    </div>
  </div>
  <div class="hv2-scroll-hint" aria-hidden="true"><i class="bi bi-chevron-double-down"></i></div>
</section> -->

<!-- ══════════════════════════════════════════════════
     BANNER CAROUSEL
══════════════════════════════════════════════════ -->
<?php if (!empty($banners)): ?>
<section class="py-4" style="background:var(--cream)">
  <div class="container">
    <div id="bannerCarousel" class="carousel slide banner-v2" data-bs-ride="carousel" data-bs-interval="5000">
      <div class="carousel-inner rounded-4 overflow-hidden shadow-soft">
        <?php foreach ($banners as $bi => $b): ?>
        <div class="carousel-item <?php echo $bi === 0 ? 'active' : ''; ?>">
          <?php if (!empty($b['image'])): ?>
            <a href="<?php echo htmlspecialchars(!empty($b['link']) ? $b['link'] : '#'); ?>">
              <img src="<?php echo base_url('uploads/banners/'.$b['image']); ?>"
                   class="d-block w-100" style="max-height:420px;object-fit:cover"
                   alt="<?php echo htmlspecialchars($b['title'] ?? ''); ?>">
            </a>
          <?php else: ?>
            <div class="banner-fallback-v2 d-flex align-items-center justify-content-center">
              <div class="text-center text-white px-4">
                <?php if (!empty($b['title'])): ?>
                  <h2 style="font-family:'Playfair Display',serif;font-size:2.4rem"><?php echo htmlspecialchars($b['title']); ?></h2>
                <?php endif; ?>
                <?php if (!empty($b['subtitle'])): ?>
                  <p class="mb-4 fs-5" style="opacity:.75"><?php echo htmlspecialchars($b['subtitle']); ?></p>
                <?php endif; ?>
                <?php if (!empty($b['link']) && !empty($b['btn_text'])): ?>
                  <a href="<?php echo htmlspecialchars($b['link']); ?>" class="btn btn-saffron btn-lg px-5"><?php echo htmlspecialchars($b['btn_text']); ?></a>
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

<!-- ══════════════════════════════════════════════════
     STATS BAR
══════════════════════════════════════════════════ -->
<section class="stats-v2">
  <div class="container">
    <div class="stats-v2-grid">
      <div class="stat-v2-item">
        <div class="stat-v2-icon" style="background:rgba(255,107,53,.12);color:#FF6B35"><i class="bi bi-box-seam-fill"></i></div>
        <div class="stat-v2-body">
          <div class="stat-v2-number"><span class="count-up" data-target="15">15</span>+</div>
          <div class="stat-v2-label">Product Varieties</div>
        </div>
      </div>
      <div class="stat-v2-sep"></div>
      <div class="stat-v2-item">
        <div class="stat-v2-icon" style="background:rgba(39,174,96,.12);color:#27ae60"><i class="bi bi-people-fill"></i></div>
        <div class="stat-v2-body">
          <div class="stat-v2-number"><span class="count-up" data-target="5000">5000</span>+</div>
          <div class="stat-v2-label">Happy Customers</div>
        </div>
      </div>
      <div class="stat-v2-sep"></div>
      <div class="stat-v2-item">
        <div class="stat-v2-icon" style="background:rgba(155,89,182,.12);color:#9b59b6"><i class="bi bi-geo-alt-fill"></i></div>
        <div class="stat-v2-body">
          <div class="stat-v2-number"><span class="count-up" data-target="12">12</span>+</div>
          <div class="stat-v2-label">States Delivered</div>
        </div>
      </div>
      <div class="stat-v2-sep"></div>
      <div class="stat-v2-item">
        <div class="stat-v2-icon" style="background:rgba(231,76,60,.12);color:#e74c3c"><i class="bi bi-patch-check-fill"></i></div>
        <div class="stat-v2-body">
          <div class="stat-v2-number"><span class="count-up" data-target="100">100</span>%</div>
          <div class="stat-v2-label">Natural & Pure</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════
     OFFER STRIP
══════════════════════════════════════════════════ -->
<div class="offer-strip-v2">
  <div class="container">
    <div class="offer-v2-inner">
      <span class="offer-v2-fire">🔥</span>
      <span class="offer-v2-text"><strong>LIMITED OFFER —</strong> Use code <span class="offer-code-v2">STYLE10</span> for 10% off your first order!</span>
      <a href="<?php echo site_url('shop'); ?>" class="offer-v2-btn">Grab Deal <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════
     CATEGORIES
══════════════════════════════════════════════════ -->
<section class="py-5">
  <div class="container">
    <div class="section-header mb-4">
      <div>
        <div class="section-eyebrow">Browse By</div>
        <h2 class="section-title mb-0">Shop Categories</h2>
      </div>
      <a href="<?php echo site_url('shop'); ?>" class="btn btn-outline-saffron btn-sm">
        All Products <i class="bi bi-arrow-right ms-1"></i>
      </a>
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
    <div class="cat-scroll-wrap">
      <?php foreach ($categories as $cat): ?>
      <a href="<?php echo site_url('shop'); ?>?category=<?php echo htmlspecialchars($cat['slug']); ?>" class="text-decoration-none cat-scroll-item">
        <div class="category-card-v2">
          <div class="cat-v2-img-wrap">
            <?php if (!empty($cat['image']) && file_exists(FCPATH.'uploads/products/'.$cat['image'])): ?>
              <img src="<?php echo base_url('uploads/products/'.$cat['image']); ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>">
            <?php else: ?>
              <span class="cat-v2-emoji"><?php echo $catEmojis[$cat['slug']] ?? '🌿'; ?></span>
            <?php endif; ?>
            <div class="cat-v2-overlay"></div>
          </div>
          <p class="cat-v2-name"><?php echo htmlspecialchars($cat['name']); ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════
     PRODUCTS — TABS (Featured / Best Sellers)
══════════════════════════════════════════════════ -->
<?php if (!empty($featured) || !empty($products)): ?>
<section class="py-5" style="background:#fff">
  <div class="container">
    <div class="section-header mb-0">
      <div>
        <div class="section-eyebrow">Our Collection</div>
        <h2 class="section-title mb-0">Products</h2>
      </div>
      <a href="<?php echo site_url('shop'); ?>" class="btn btn-outline-saffron btn-sm d-none d-md-inline-flex">
        View All <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>
    <ul class="product-tab-pills nav nav-pills my-4" id="productTabs" role="tablist">
      <?php if (!empty($featured)): ?>
      <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tabFeatured" type="button" role="tab">
          <i class="bi bi-star me-1"></i>Featured
        </button>
      </li>
      <?php endif; ?>
      <?php if (!empty($products)): ?>
      <li class="nav-item" role="presentation">
        <button class="nav-link <?php echo empty($featured) ? 'active' : ''; ?>" data-bs-toggle="pill" data-bs-target="#tabBestSellers" type="button" role="tab">
          <i class="bi bi-fire me-1"></i>Best Sellers
        </button>
      </li>
      <?php endif; ?>
    </ul>

    <div class="tab-content">
      <?php if (!empty($featured)): ?>
      <div class="tab-pane fade show active" id="tabFeatured" role="tabpanel">
        <div class="row g-4">
          <?php foreach ($featured as $p): ?>
          <div class="col-6 col-md-4 col-lg-3">
            <div class="product-card-v2">
              <div class="pcv2-img-wrap">
                <a href="<?php echo site_url('product/'.$p['id']); ?>">
                  <img src="<?php echo $this->spice_model->product_image($p['image']); ?>"
                       alt="<?php echo htmlspecialchars($p['name']); ?>" loading="lazy">
                </a>
                <?php if ($p['stock_qty'] < 1): ?>
                  <span class="pcv2-badge pcv2-badge-oos">Out of Stock</span>
                <?php elseif (!empty($p['offer_price']) && $p['offer_price'] > 0): ?>
                  <?php $disc = round((($p['price']-$p['offer_price'])/$p['price'])*100); ?>
                  <span class="pcv2-badge pcv2-badge-sale">-<?php echo $disc; ?>%</span>
                <?php else: ?>
                  <span class="pcv2-badge pcv2-badge-feat">⭐ Featured</span>
                <?php endif; ?>
                <button class="btn-wishlist" data-wishlist="<?php echo $p['id']; ?>" title="Add to Wishlist">
                  <i class="bi bi-heart"></i>
                </button>
                <div class="pcv2-overlay">
                  <a href="<?php echo site_url('product/'.$p['id']); ?>" class="pcv2-quick-view">
                    <i class="bi bi-eye me-1"></i>Quick View
                  </a>
                </div>
              </div>
              <div class="pcv2-body">
                <span class="pcv2-category"><?php echo htmlspecialchars($p['cat_name']); ?></span>
                <h6 class="pcv2-title">
                  <a href="<?php echo site_url('product/'.$p['id']); ?>"><?php echo htmlspecialchars($p['name']); ?></a>
                </h6>
                <?php $avg = $this->spice_model->avg_rating($p['id']); ?>
                <?php if ($avg > 0): ?>
                <div class="pcv2-rating">
                  <?php echo $this->spice_model->star_rating($avg); ?>
                  <small>(<?php echo $this->spice_model->review_count($p['id']); ?>)</small>
                </div>
                <?php endif; ?>
                <?php if (!empty($p['weight'])): ?>
                  <span class="pcv2-weight"><?php echo htmlspecialchars($p['weight']); ?></span>
                <?php endif; ?>
                <div class="pcv2-footer">
                  <div class="pcv2-price-wrap">
                    <?php if (!empty($p['offer_price']) && $p['offer_price'] > 0): ?>
                      <span class="pcv2-price"><?php echo $this->spice_model->rupees((float)$p['offer_price']); ?></span>
                      <span class="pcv2-mrp"><?php echo $this->spice_model->rupees((float)$p['price']); ?></span>
                    <?php else: ?>
                      <span class="pcv2-price"><?php echo $this->spice_model->rupees((float)$p['price']); ?></span>
                    <?php endif; ?>
                  </div>
                  <?php if ($p['stock_qty'] > 0): ?>
                    <button class="pcv2-cart-btn" data-add-cart="<?php echo $p['id']; ?>" data-cart-icon="1" title="Add to Cart">
                      <i class="bi bi-bag-plus"></i>
                    </button>
                  <?php else: ?>
                    <button class="pcv2-cart-btn" disabled title="Out of Stock">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($products)): ?>
      <div class="tab-pane fade <?php echo empty($featured) ? 'show active' : ''; ?>" id="tabBestSellers" role="tabpanel">
        <div class="row g-4">
          <?php foreach ($products as $p): ?>
          <div class="col-6 col-md-4 col-lg-3">
            <div class="product-card-v2">
              <div class="pcv2-img-wrap">
                <a href="<?php echo site_url('product/'.$p['id']); ?>">
                  <img src="<?php echo $this->spice_model->product_image($p['image']); ?>"
                       alt="<?php echo htmlspecialchars($p['name']); ?>" loading="lazy">
                </a>
                <?php if ($p['stock_qty'] < 1): ?>
                  <span class="pcv2-badge pcv2-badge-oos">Out of Stock</span>
                <?php elseif (!empty($p['offer_price']) && $p['offer_price'] > 0): ?>
                  <?php $disc = round((($p['price']-$p['offer_price'])/$p['price'])*100); ?>
                  <span class="pcv2-badge pcv2-badge-sale">-<?php echo $disc; ?>%</span>
                <?php elseif ($p['stock_qty'] < 20): ?>
                  <span class="pcv2-badge pcv2-badge-low">Low Stock</span>
                <?php endif; ?>
                <button class="btn-wishlist" data-wishlist="<?php echo $p['id']; ?>" title="Add to Wishlist">
                  <i class="bi bi-heart"></i>
                </button>
                <div class="pcv2-overlay">
                  <a href="<?php echo site_url('product/'.$p['id']); ?>" class="pcv2-quick-view">
                    <i class="bi bi-eye me-1"></i>Quick View
                  </a>
                </div>
              </div>
              <div class="pcv2-body">
                <span class="pcv2-category"><?php echo htmlspecialchars($p['cat_name']); ?></span>
                <h6 class="pcv2-title">
                  <a href="<?php echo site_url('product/'.$p['id']); ?>"><?php echo htmlspecialchars($p['name']); ?></a>
                </h6>
                <?php $avg = $this->spice_model->avg_rating($p['id']); ?>
                <?php if ($avg > 0): ?>
                <div class="pcv2-rating">
                  <?php echo $this->spice_model->star_rating($avg); ?>
                  <small>(<?php echo $this->spice_model->review_count($p['id']); ?>)</small>
                </div>
                <?php endif; ?>
                <?php if (!empty($p['weight'])): ?>
                  <span class="pcv2-weight"><?php echo htmlspecialchars($p['weight']); ?></span>
                <?php endif; ?>
                <div class="pcv2-footer">
                  <div class="pcv2-price-wrap">
                    <?php if (!empty($p['offer_price']) && $p['offer_price'] > 0): ?>
                      <span class="pcv2-price"><?php echo $this->spice_model->rupees((float)$p['offer_price']); ?></span>
                      <span class="pcv2-mrp"><?php echo $this->spice_model->rupees((float)$p['price']); ?></span>
                    <?php else: ?>
                      <span class="pcv2-price"><?php echo $this->spice_model->rupees((float)$p['price']); ?></span>
                    <?php endif; ?>
                  </div>
                  <?php if ($p['stock_qty'] > 0): ?>
                    <button class="pcv2-cart-btn" data-add-cart="<?php echo $p['id']; ?>" data-cart-icon="1" title="Add to Cart">
                      <i class="bi bi-bag-plus"></i>
                    </button>
                  <?php else: ?>
                    <button class="pcv2-cart-btn" disabled title="Out of Stock">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <div class="text-center mt-5">
      <a href="<?php echo site_url('shop'); ?>" class="btn btn-outline-saffron px-5 py-2">
        Browse All Products <i class="bi bi-arrow-right ms-2"></i>
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════
     WHY CHOOSE US
══════════════════════════════════════════════════ -->
<?php if (!empty($why_choose_us)): ?>
<section class="why-v2-section">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-eyebrow">Why myeoncasuals</div>
      <h2 class="section-title d-inline-block">Why Choose Us?</h2>
    </div>
    <div class="row g-4">
      <?php foreach ($why_choose_us as $i => $card): ?>
      <div class="col-6 col-md-4 col-lg-2">
        <div class="why-v2-card h-100">
          <div class="why-v2-icon-wrap">
            <div class="why-v2-icon"><?php echo htmlspecialchars($card['icon']); ?></div>
            <div class="why-v2-num"><?php echo str_pad($i + 1, 2, '0', STR_PAD_LEFT); ?></div>
          </div>
          <h6 class="why-v2-title"><?php echo htmlspecialchars($card['title']); ?></h6>
          <p class="why-v2-desc"><?php echo htmlspecialchars($card['description']); ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════
     NEWSLETTER CTA
══════════════════════════════════════════════════ -->
<section class="newsletter-v2-section">
  <div class="container">
    <div class="newsletter-v2-card">
      <div class="nv2-spice-art" aria-hidden="true">🌶️🫚🌿🧄🌰</div>
      <div class="row align-items-center gy-4">
        <div class="col-lg-6">
          <div class="nv2-eyebrow">Exclusive Member Offer</div>
          <h3 class="nv2-title">Get ₹100 Off Your First Order!</h3>
          <p class="nv2-desc">Subscribe and receive exclusive deals, new arrival alerts, and spice recipe tips straight to your inbox.</p>
        </div>
        <div class="col-lg-6">
          <form class="nv2-form" onsubmit="return false;">
            <div class="nv2-input-wrap">
              <i class="bi bi-envelope nv2-input-icon"></i>
              <input type="email" class="nv2-input" placeholder="Enter your email address">
              <button type="submit" class="nv2-submit">Subscribe</button>
            </div>
            <p class="nv2-note"><i class="bi bi-shield-check me-1"></i>No spam. Unsubscribe any time.</p>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════
     TESTIMONIALS
══════════════════════════════════════════════════ -->
<?php if (!empty($testimonials)): ?>
<section class="testi-v2-section">
  <div class="testi-v2-bg"></div>
  <div class="container position-relative" style="z-index:2">
    <div class="text-center mb-5">
      <div class="section-eyebrow" style="color:rgba(255,179,107,.8)">Customer Stories</div>
      <h2 class="testi-v2-heading">What Our Customers Say</h2>
    </div>
    <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
      <div class="carousel-inner">
        <?php foreach ($testimonials as $ti => $t): ?>
        <div class="carousel-item <?php echo $ti === 0 ? 'active' : ''; ?>">
          <div class="testi-v2-card mx-auto">
            <div class="testi-v2-quote-mark">&ldquo;</div>
            <p class="testi-v2-text"><?php echo htmlspecialchars($t['quote']); ?></p>
            <div class="testi-v2-footer">
              <div class="testi-v2-avatar">
                <?php echo mb_strtoupper(mb_substr($t['customer_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
              </div>
              <div>
                <div class="testi-v2-name"><?php echo htmlspecialchars($t['customer_name']); ?></div>
                <div class="testi-v2-stars">
                  <?php for ($s = 1; $s <= 5; $s++): ?>
                    <i class="bi bi-star-fill<?php echo $s > (int)$t['rating'] ? ' testi-star-dim' : ''; ?>"></i>
                  <?php endfor; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if (count($testimonials) > 1): ?>
      <div class="testi-v2-nav">
        <?php foreach ($testimonials as $ti => $t): ?>
          <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="<?php echo $ti; ?>"
                  class="testi-v2-dot <?php echo $ti === 0 ? 'active' : ''; ?>" aria-label="Slide <?php echo $ti+1; ?>"></button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>
