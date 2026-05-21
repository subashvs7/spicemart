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
          <a href="<?php echo site_url('account'); ?>?tab=loyalty" class="nav-link <?php echo $tab==='loyalty'?'active':''; ?>">
            <i class="bi bi-star me-2"></i>Loyalty Points
            <?php if (!empty($loyalty['points_balance'])): ?>
              <span class="badge ms-auto" style="background:var(--saffron);color:#fff;font-size:.7rem"><?php echo number_format($loyalty['points_balance']); ?></span>
            <?php endif; ?>
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

      <!-- TAB: Loyalty Points -->
      <?php elseif ($tab === 'loyalty'):
        $tier        = $loyalty['tier'] ?? 'bronze';
        $bal         = (int)($loyalty['points_balance'] ?? 0);
        $earned      = (int)($loyalty['points_earned']  ?? 0);
        $redeemed    = (int)($loyalty['points_redeemed']?? 0);
        $tier_config = array(
          'bronze'   => array('label'=>'Bronze',   'color'=>'#cd7f32','min'=>0,    'next'=>500,  'next_label'=>'Silver'),
          'silver'   => array('label'=>'Silver',   'color'=>'#888',   'min'=>500,  'next'=>2000, 'next_label'=>'Gold'),
          'gold'     => array('label'=>'Gold',     'color'=>'#f5a623','min'=>2000, 'next'=>5000, 'next_label'=>'Platinum'),
          'platinum' => array('label'=>'Platinum', 'color'=>'#7c3aed','min'=>5000, 'next'=>null, 'next_label'=>''),
        );
        $tc   = $tier_config[$tier] ?? $tier_config['bronze'];
        $prog = 0;
        if ($tc['next']) {
          $prog = min(100, (int)round(max(0, $earned - $tc['min']) / ($tc['next'] - $tc['min']) * 100));
        } else {
          $prog = 100;
        }
      ?>
      <!-- Hero card -->
      <div class="bg-white rounded-xl shadow-soft p-4 mb-4"
           style="background:linear-gradient(135deg,<?php echo $tc['color']; ?>22,#fff)!important">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div style="width:56px;height:56px;border-radius:50%;background:<?php echo $tc['color']; ?>;
                      display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.6rem">
            <i class="bi bi-star-fill"></i>
          </div>
          <div>
            <h5 class="mb-0" style="font-family:'Playfair Display',serif">Loyalty Points</h5>
            <span class="badge" style="background:<?php echo $tc['color']; ?>;color:#fff;font-size:.8rem">
              <?php echo $tc['label']; ?> Member
            </span>
          </div>
          <div class="ms-auto text-end">
            <div class="fs-2 fw-600" style="color:<?php echo $tc['color']; ?>"><?php echo number_format($bal); ?></div>
            <small class="text-muted">Available Points</small>
          </div>
        </div>

        <!-- Stats row -->
        <div class="row g-2 mb-3">
          <div class="col-4">
            <div class="p-3 rounded-3 text-center" style="background:#f8f8f8">
              <div class="fw-600 text-saffron"><?php echo number_format($earned); ?></div>
              <small class="text-muted">Total Earned</small>
            </div>
          </div>
          <div class="col-4">
            <div class="p-3 rounded-3 text-center" style="background:#f8f8f8">
              <div class="fw-600 text-danger"><?php echo number_format($redeemed); ?></div>
              <small class="text-muted">Redeemed</small>
            </div>
          </div>
          <div class="col-4">
            <div class="p-3 rounded-3 text-center" style="background:#f8f8f8">
              <div class="fw-600" style="color:<?php echo $tc['color']; ?>"><?php echo number_format($bal); ?></div>
              <small class="text-muted">Balance</small>
            </div>
          </div>
        </div>

        <!-- Tier progress -->
        <?php if ($tc['next']): ?>
        <div>
          <div class="d-flex justify-content-between mb-1">
            <small class="text-muted"><?php echo $tc['label']; ?></small>
            <small class="text-muted"><?php echo $tc['next_label']; ?> at <?php echo number_format($tc['next']); ?> pts</small>
          </div>
          <div class="progress" style="height:8px;border-radius:4px">
            <div class="progress-bar" role="progressbar"
                 style="width:<?php echo $prog; ?>%;background:<?php echo $tc['color']; ?>;border-radius:4px"
                 aria-valuenow="<?php echo $prog; ?>" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
          <small class="text-muted d-block mt-1">
            <?php $pts_needed = $tc['next'] - $earned; ?>
            <?php if ($pts_needed > 0): ?>
              Earn <?php echo number_format($pts_needed); ?> more points to reach <?php echo $tc['next_label']; ?>
            <?php else: ?>
              You've reached <?php echo $tc['next_label']; ?>!
            <?php endif; ?>
          </small>
        </div>
        <?php else: ?>
        <div class="text-center py-2">
          <span class="badge" style="background:#7c3aed;color:#fff;padding:.5em 1.2em;font-size:.9rem">
            <i class="bi bi-trophy me-1"></i> Highest Tier — Platinum Member
          </span>
        </div>
        <?php endif; ?>
      </div>

      <!-- Tier benefits -->
      <div class="bg-white rounded-xl shadow-soft p-4 mb-4">
        <h6 class="mb-3 fw-600">Tier Benefits</h6>
        <div class="row g-2">
          <?php foreach ($tier_config as $tk => $tv): ?>
          <div class="col-6 col-md-3">
            <div class="p-3 rounded-3 text-center border <?php echo $tier===$tk ? 'border-2' : ''; ?>"
                 style="<?php echo $tier===$tk ? 'border-color:'.$tv['color'].'!important;background:'.$tv['color'].'11' : ''; ?>">
              <div class="fw-600" style="color:<?php echo $tv['color']; ?>"><?php echo $tv['label']; ?></div>
              <small class="text-muted"><?php echo number_format($tv['min']); ?>+ pts</small>
              <?php if ($tier === $tk): ?>
                <div><small class="badge" style="background:<?php echo $tv['color']; ?>;color:#fff">Current</small></div>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Points history -->
      <div class="bg-white rounded-xl shadow-soft p-4">
        <h6 class="mb-3 fw-600">Points History</h6>
        <?php if (empty($loyalty_history)): ?>
          <div class="empty-state py-3">
            <div class="empty-icon">⭐</div>
            <h6 class="mt-2 text-muted">No transactions yet</h6>
            <p class="text-muted small">Start shopping to earn loyalty points!</p>
            <a href="<?php echo site_url('shop'); ?>" class="btn btn-saffron btn-sm mt-1">Shop Now</a>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Date</th>
                  <th>Description</th>
                  <th>Type</th>
                  <th class="text-end">Points</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($loyalty_history as $lh):
                  $is_positive = (int)$lh['points'] >= 0;
                  $type_labels = array(
                    'earned'   => array('label'=>'Earned',   'class'=>'bg-success'),
                    'redeemed' => array('label'=>'Redeemed', 'class'=>'bg-warning text-dark'),
                    'bonus'    => array('label'=>'Bonus',    'class'=>'bg-info'),
                    'adjusted' => array('label'=>'Adjusted', 'class'=>'bg-secondary'),
                    'expired'  => array('label'=>'Expired',  'class'=>'bg-danger'),
                  );
                  $tl = $type_labels[$lh['type']] ?? array('label'=>ucfirst($lh['type']),'class'=>'bg-secondary');
                ?>
                <tr>
                  <td class="text-muted small"><?php echo date('d M Y', strtotime($lh['created_at'])); ?></td>
                  <td><?php echo htmlspecialchars($lh['note'] ?: ucfirst($lh['type']).' transaction'); ?></td>
                  <td><span class="badge <?php echo $tl['class']; ?>"><?php echo $tl['label']; ?></span></td>
                  <td class="text-end fw-600 <?php echo $is_positive ? 'text-success' : 'text-danger'; ?>">
                    <?php echo $is_positive ? '+' : ''; ?><?php echo number_format((int)$lh['points']); ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
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
