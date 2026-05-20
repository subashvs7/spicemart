<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container py-4">
  <div class="d-flex align-items-center gap-2 mb-4">
    <span class="fs-2">👤</span>
    <div>
      <h2 class="mb-0" style="font-family:'Playfair Display',serif">My Account</h2>
      <small class="text-muted">Welcome back, <?php echo htmlspecialchars($user['name']); ?></small>
    </div>
  </div>

  <div class="row g-4">
    <!-- Sidebar -->
    <div class="col-md-3">
      <div class="account-sidebar">
        <div class="text-center mb-4">
          <div style="width:70px;height:70px;border-radius:50%;background:var(--saffron);
                      display:flex;align-items:center;justify-content:center;
                      margin:0 auto 10px;font-size:2rem;color:#fff">
            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
          </div>
          <div class="fw-600"><?php echo htmlspecialchars($user['name']); ?></div>
          <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
        </div>
        <nav class="account-nav d-flex flex-column gap-1">
          <a href="<?php echo site_url('account'); ?>?tab=profile" class="nav-link <?php echo $tab==='profile'?'active':''; ?>">
            <i class="bi bi-person me-2"></i>Profile
          </a>
          <a href="<?php echo site_url('account'); ?>?tab=orders" class="nav-link <?php echo $tab==='orders'?'active':''; ?>">
            <i class="bi bi-box me-2"></i>My Orders
          </a>
          <a href="<?php echo site_url('account'); ?>?tab=wishlist" class="nav-link <?php echo $tab==='wishlist'?'active':''; ?>">
            <i class="bi bi-heart me-2"></i>Wishlist
          </a>
          <a href="<?php echo site_url('account'); ?>?tab=addresses" class="nav-link <?php echo $tab==='addresses'?'active':''; ?>">
            <i class="bi bi-geo-alt me-2"></i>Addresses
          </a>
          <a href="<?php echo site_url('account'); ?>?tab=password" class="nav-link <?php echo $tab==='password'?'active':''; ?>">
            <i class="bi bi-shield-lock me-2"></i>Change Password
          </a>
          <hr class="my-2">
          <a href="<?php echo site_url('logout'); ?>" class="nav-link text-danger">
            <i class="bi bi-box-arrow-right me-2"></i>Logout
          </a>
        </nav>
      </div>
    </div>

    <!-- Main Content -->
    <div class="col-md-9">

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul></div>
      <?php endif; ?>
      <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>

      <!-- TAB: Profile -->
      <?php if ($tab === 'profile'): ?>
      <div class="bg-white rounded-xl shadow-soft p-4">
        <h5 class="mb-4" style="font-family:'Playfair Display',serif">Edit Profile</h5>
        <form method="post" action="<?php echo site_url('account'); ?>?tab=profile">
          <input type="hidden" name="update_profile" value="1">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-600">Full Name *</label>
              <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-600">Email</label>
              <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
              <small class="text-muted">Email cannot be changed.</small>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-600">Phone</label>
              <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>
            <div class="col-12">
              <label class="form-label small fw-600">Default Address</label>
              <textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>
          </div>
          <button type="submit" class="btn btn-saffron mt-4">Save Changes</button>
        </form>
      </div>

      <!-- TAB: Orders -->
      <?php elseif ($tab === 'orders'): ?>

        <?php if ($orderDetail): ?>
        <div class="bg-white rounded-xl shadow-soft p-4 mb-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0" style="font-family:'Playfair Display',serif">
              Order #<?php echo str_pad($orderDetail['id'],5,'0',STR_PAD_LEFT); ?>
            </h5>
            <a href="<?php echo site_url('account'); ?>?tab=orders" class="btn btn-sm btn-outline-secondary">← Back</a>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
              <small class="text-muted d-block">Date</small>
              <strong><?php echo date('d M Y', strtotime($orderDetail['created_at'])); ?></strong>
            </div>
            <div class="col-6 col-md-3">
              <small class="text-muted d-block">Status</small>
              <?php echo $this->spice_model->order_status_badge($orderDetail['status']); ?>
            </div>
            <div class="col-6 col-md-3">
              <small class="text-muted d-block">Payment</small>
              <strong><?php echo strtoupper($orderDetail['payment_method']); ?></strong>
            </div>
            <div class="col-6 col-md-3">
              <small class="text-muted d-block">Total</small>
              <strong class="text-saffron"><?php echo $this->spice_model->rupees((float)$orderDetail['total_amount']); ?></strong>
            </div>
          </div>
          <?php if ($orderDetail['tracking_no']): ?>
          <div class="alert alert-info py-2">
            <i class="bi bi-truck me-2"></i>
            Tracking: <strong><?php echo htmlspecialchars($orderDetail['tracking_no']); ?></strong>
            <?php if ($orderDetail['courier_name']): ?> via <?php echo htmlspecialchars($orderDetail['courier_name']); ?><?php endif; ?>
          </div>
          <?php endif; ?>
          <hr>
          <?php foreach ($orderItems as $oi): ?>
          <div class="d-flex align-items-center gap-3 mb-3">
            <img src="<?php echo $this->spice_model->product_image($oi['image']); ?>"
                 width="52" height="52" style="object-fit:cover;border-radius:8px">
            <div style="flex:1">
              <div class="fw-600"><?php echo htmlspecialchars($oi['product_name']); ?></div>
              <small class="text-muted">Qty: <?php echo $oi['quantity']; ?> × <?php echo $this->spice_model->rupees((float)$oi['unit_price']); ?></small>
            </div>
            <div class="fw-600"><?php echo $this->spice_model->rupees((float)($oi['unit_price']*$oi['quantity'])); ?></div>
          </div>
          <?php endforeach; ?>
          <hr>
          <div class="small text-muted" style="white-space:pre-line">
            <strong>Delivered to:</strong><br><?php echo htmlspecialchars($orderDetail['shipping_address']); ?>
          </div>
          <div class="d-flex gap-2 mt-3">
            <a href="<?php echo site_url('track-order/'.$orderDetail['id']); ?>" class="btn btn-sm btn-outline-saffron">
              <i class="bi bi-truck me-1"></i> Track
            </a>
            <a href="<?php echo site_url('invoice/'.$orderDetail['id']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-receipt me-1"></i> Invoice
            </a>
            <?php if (in_array($orderDetail['status'], array('pending','processing'))): ?>
            <a href="<?php echo site_url('cancel-order/'.$orderDetail['id']); ?>" class="btn btn-sm btn-outline-danger">
              <i class="bi bi-x-circle me-1"></i> Cancel
            </a>
            <?php elseif ($orderDetail['status'] === 'delivered'): ?>
            <a href="<?php echo site_url('return-order/'.$orderDetail['id']); ?>" class="btn btn-sm btn-outline-warning">
              <i class="bi bi-arrow-return-left me-1"></i> Return
            </a>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-soft p-4">
          <h5 class="mb-4" style="font-family:'Playfair Display',serif">Order History</h5>
          <?php if (empty($orders)): ?>
            <div class="empty-state">
              <div class="empty-icon">📦</div>
              <h5 class="mt-3">No orders yet</h5>
              <a href="<?php echo site_url('shop'); ?>" class="btn btn-saffron mt-2">Start Shopping</a>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr><th>Order ID</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                  <?php foreach ($orders as $ord): ?>
                  <tr>
                    <td class="fw-600">#<?php echo str_pad($ord['id'],5,'0',STR_PAD_LEFT); ?></td>
                    <td><?php echo date('d M Y', strtotime($ord['created_at'])); ?></td>
                    <td><?php echo $ord['item_count']; ?> item<?php echo $ord['item_count']!=1?'s':''; ?></td>
                    <td class="fw-600 text-saffron"><?php echo $this->spice_model->rupees((float)$ord['total_amount']); ?></td>
                    <td><?php echo $this->spice_model->order_status_badge($ord['status']); ?></td>
                    <td>
                      <a href="<?php echo site_url('account'); ?>?tab=orders&order=<?php echo $ord['id']; ?>" class="btn btn-sm btn-outline-saffron">View</a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

      <!-- TAB: Wishlist -->
      <?php elseif ($tab === 'wishlist'): ?>
      <div class="bg-white rounded-xl shadow-soft p-4">
        <h5 class="mb-4" style="font-family:'Playfair Display',serif">My Wishlist</h5>
        <?php if (empty($wishlist)): ?>
          <div class="empty-state py-4">
            <div class="empty-icon">❤️</div>
            <h5 class="mt-3">Your wishlist is empty</h5>
            <a href="<?php echo site_url('shop'); ?>" class="btn btn-saffron mt-2">Browse Products</a>
          </div>
        <?php else: ?>
          <div class="row g-3">
            <?php foreach ($wishlist as $w): ?>
            <div class="col-6 col-md-4">
              <div class="product-card">
                <div class="product-img-wrap">
                  <a href="<?php echo site_url('product/'.$w['product_id']); ?>">
                    <img src="<?php echo $this->spice_model->product_image($w['image']); ?>"
                         alt="<?php echo htmlspecialchars($w['name']); ?>" loading="lazy">
                  </a>
                </div>
                <div class="card-body">
                  <h6 class="product-title mb-1">
                    <a href="<?php echo site_url('product/'.$w['product_id']); ?>" class="text-dark">
                      <?php echo htmlspecialchars($w['name']); ?>
                    </a>
                  </h6>
                  <div class="mb-2">
                    <?php if (!empty($w['offer_price']) && $w['offer_price'] > 0): ?>
                      <span class="product-price"><?php echo $this->spice_model->rupees((float)$w['offer_price']); ?></span>
                    <?php else: ?>
                      <span class="product-price"><?php echo $this->spice_model->rupees((float)$w['price']); ?></span>
                    <?php endif; ?>
                  </div>
                  <button class="btn-add-cart" data-add-cart="<?php echo $w['product_id']; ?>">
                    <i class="bi bi-bag-plus me-1"></i> Add to Cart
                  </button>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- TAB: Addresses -->
      <?php elseif ($tab === 'addresses'): ?>
      <div class="bg-white rounded-xl shadow-soft p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h5 class="mb-0" style="font-family:'Playfair Display',serif">My Addresses</h5>
          <a href="<?php echo site_url('my-addresses'); ?>" class="btn btn-sm btn-saffron">
            <i class="bi bi-plus-lg me-1"></i> Manage Addresses
          </a>
        </div>
        <?php if (empty($addresses)): ?>
          <div class="empty-state py-4">
            <div class="empty-icon">📍</div>
            <h5 class="mt-3">No saved addresses</h5>
            <a href="<?php echo site_url('my-addresses'); ?>" class="btn btn-saffron mt-2">Add Address</a>
          </div>
        <?php else: ?>
          <div class="d-flex flex-column gap-3">
            <?php foreach ($addresses as $addr): ?>
            <div class="p-3 rounded-3 border <?php echo $addr['is_default'] ? 'border-saffron' : ''; ?>"
                 style="<?php echo $addr['is_default'] ? 'border-color:var(--saffron)!important;background:rgba(123,66,40,.03)' : ''; ?>">
              <div class="d-flex justify-content-between">
                <div>
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($addr['label'] ?: 'Home'); ?></span>
                    <?php if ($addr['is_default']): ?>
                      <span class="badge" style="background:var(--saffron);color:#fff">Default</span>
                    <?php endif; ?>
                  </div>
                  <div class="fw-600"><?php echo htmlspecialchars($addr['name']); ?></div>
                  <div class="text-muted small"><?php echo htmlspecialchars($addr['phone']); ?></div>
                  <div class="text-muted small"><?php echo htmlspecialchars($addr['address_line'].', '.$addr['city'].', '.$addr['state'].' - '.$addr['pincode']); ?></div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- TAB: Password -->
      <?php elseif ($tab === 'password'): ?>
      <div class="bg-white rounded-xl shadow-soft p-4">
        <h5 class="mb-4" style="font-family:'Playfair Display',serif">Change Password</h5>
        <form method="post" action="<?php echo site_url('account'); ?>?tab=password" style="max-width:400px">
          <input type="hidden" name="change_password" value="1">
          <div class="mb-3">
            <label class="form-label small fw-600">Current Password *</label>
            <input type="password" class="form-control" name="current_password" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-600">New Password *</label>
            <input type="password" class="form-control" name="new_password" minlength="6" required>
            <small class="text-muted">Minimum 6 characters.</small>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-600">Confirm New Password *</label>
            <input type="password" class="form-control" name="confirm_password" required>
          </div>
          <button type="submit" class="btn btn-saffron">Update Password</button>
        </form>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>
