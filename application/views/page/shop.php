<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Breadcrumb -->
<div class="container mt-3">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?php echo site_url('home'); ?>">Home</a></li>
      <?php if ($category): ?>
        <li class="breadcrumb-item"><a href="<?php echo site_url('shop'); ?>">Shop</a></li>
        <li class="breadcrumb-item active"><?php echo htmlspecialchars(ucwords(str_replace('-', ' ', $category))); ?></li>
      <?php else: ?>
        <li class="breadcrumb-item active">Shop</li>
      <?php endif; ?>
    </ol>
  </nav>
</div>

<div class="container pb-5">
  <div class="row g-4">

    <!-- Sidebar Filters -->
    <div class="col-lg-3">
      <div class="filter-card">
        <h5 class="mb-3" style="font-family:'Poppins',sans-serif;font-weight:700">Filters</h5>

        <div class="mb-4">
          <h6>Search</h6>
          <input type="text" id="filterQ" class="form-control form-control-sm"
                 placeholder="Type a spice name…" value="<?php echo htmlspecialchars($q); ?>">
        </div>

        <div class="mb-4">
          <h6>Category</h6>
          <div class="d-flex flex-column gap-1">
            <label class="form-check mb-0">
              <input class="form-check-input" type="radio" name="category" value=""
                     <?php echo !$category ? 'checked' : ''; ?>>
              <span class="form-check-label small">All Categories</span>
            </label>
            <?php foreach ($all_categories as $cat): ?>
            <label class="form-check mb-0 <?php echo $cat['parent_id'] ? 'ms-3' : ''; ?>">
              <input class="form-check-input" type="radio" name="category"
                     value="<?php echo htmlspecialchars($cat['slug']); ?>"
                     <?php echo $category === $cat['slug'] ? 'checked' : ''; ?>>
              <span class="form-check-label small"><?php echo htmlspecialchars($cat['name']); ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <?php if (!empty($brands)): ?>
        <div class="mb-4">
          <h6>Brand</h6>
          <div class="d-flex flex-column gap-1">
            <label class="form-check mb-0">
              <input class="form-check-input" type="radio" name="brand" value=""
                     <?php echo !$brand_id ? 'checked' : ''; ?>>
              <span class="form-check-label small">All Brands</span>
            </label>
            <?php foreach ($brands as $br): ?>
            <label class="form-check mb-0">
              <input class="form-check-input" type="radio" name="brand"
                     value="<?php echo $br['id']; ?>"
                     <?php echo (int)$brand_id === (int)$br['id'] ? 'checked' : ''; ?>>
              <span class="form-check-label small"><?php echo htmlspecialchars($br['name']); ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="mb-4">
          <h6>Max Price: <span id="priceLabel">₹<?php echo $max_price; ?></span></h6>
          <input type="range" class="form-range" id="priceRange" name="max_price"
                 min="50" max="2000" step="50" value="<?php echo $max_price; ?>">
          <div class="d-flex justify-content-between">
            <small class="text-muted">₹50</small>
            <small class="text-muted">₹2000</small>
          </div>
        </div>

        <div class="mb-4">
          <h6>Sort By</h6>
          <select class="form-select form-select-sm" id="shopSort">
            <option value="newest"     <?php echo $sort==='newest'     ?'selected':''; ?>>Latest</option>
            <option value="price_asc"  <?php echo $sort==='price_asc'  ?'selected':''; ?>>Price: Low to High</option>
            <option value="price_desc" <?php echo $sort==='price_desc' ?'selected':''; ?>>Price: High to Low</option>
            <option value="popular"    <?php echo $sort==='popular'    ?'selected':''; ?>>Top Rated</option>
            <option value="name_asc"   <?php echo $sort==='name_asc'   ?'selected':''; ?>>Name A–Z</option>
          </select>
        </div>

        <button id="clearFilters" class="btn btn-outline-secondary w-100 btn-sm">Clear All</button>
      </div>
    </div>

    <!-- Product Grid -->
    <div class="col-lg-9">
      <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
          <h4 class="mb-0" style="font-family:'Playfair Display',serif">
            <?php echo $category ? htmlspecialchars(ucwords(str_replace('-', ' ', $category))) : 'All Products'; ?>
          </h4>
          <small class="text-muted"><span id="productTotal"><?php echo $total; ?> product<?php echo $total !== 1 ? 's' : ''; ?> found</span></small>
        </div>
        <div id="filterSpinner" class="spinner-border spinner-border-sm text-warning d-none" role="status">
          <span class="visually-hidden">Loading…</span>
        </div>
      </div>

      <div id="productResults">
        <?php $this->load->view('inc/shop-products-partial', get_defined_vars()); ?>
        <?php $this->load->view('inc/shop-pagination-partial', get_defined_vars()); ?>
      </div>
    </div>

  </div>
</div>
