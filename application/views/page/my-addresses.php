<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container py-4">
  <div class="d-flex align-items-center gap-2 mb-4">
    <span class="fs-2">📍</span>
    <div>
      <h2 class="mb-0" style="font-family:'Playfair Display',serif">My Addresses</h2>
      <small class="text-muted">Manage your saved delivery addresses</small>
    </div>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <div class="row g-4">
    <!-- Saved Addresses -->
    <div class="col-md-7">
      <div class="bg-white rounded-xl shadow-soft p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0" style="font-family:'Playfair Display',serif">Saved Addresses</h5>
          <button class="btn btn-sm btn-saffron" data-bs-toggle="modal" data-bs-target="#addrModal" onclick="resetAddrModal()">
            <i class="bi bi-plus-lg me-1"></i> Add New
          </button>
        </div>

        <?php if (empty($addresses)): ?>
          <div class="empty-state py-4">
            <div class="empty-icon">📭</div>
            <p class="text-muted mt-2">No saved addresses yet.</p>
          </div>
        <?php else: ?>
          <div class="d-flex flex-column gap-3">
            <?php foreach ($addresses as $addr): ?>
            <div class="p-3 rounded-3 border <?php echo $addr['is_default'] ? 'border-saffron' : ''; ?>"
                 style="<?php echo $addr['is_default'] ? 'border-color:var(--saffron)!important;background:rgba(255,107,53,.03)' : ''; ?>">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($addr['label'] ?: 'Home'); ?></span>
                    <?php if ($addr['is_default']): ?>
                      <span class="badge bg-saffron text-white" style="background:var(--saffron)">Default</span>
                    <?php endif; ?>
                  </div>
                  <div class="fw-600"><?php echo htmlspecialchars($addr['name']); ?></div>
                  <div class="text-muted small"><?php echo htmlspecialchars($addr['phone']); ?></div>
                  <div class="text-muted small">
                    <?php echo htmlspecialchars($addr['address_line'].', '.$addr['city'].', '.$addr['state'].' - '.$addr['pincode']); ?>
                  </div>
                </div>
                <div class="d-flex gap-2 flex-shrink-0">
                  <button class="btn btn-sm btn-outline-saffron"
                          onclick='openEditAddr(<?php echo json_encode($addr); ?>)'>
                    Edit
                  </button>
                  <a href="<?php echo site_url('my-addresses'); ?>?delete=<?php echo $addr['id']; ?>"
                     class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('Delete this address?')">
                    <i class="bi bi-trash"></i>
                  </a>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Quick Add -->
    <div class="col-md-5">
      <div class="bg-white rounded-xl shadow-soft p-4">
        <h5 class="mb-3" style="font-family:'Playfair Display',serif">Quick Links</h5>
        <div class="d-flex flex-column gap-2">
          <a href="<?php echo site_url('account'); ?>" class="btn btn-outline-secondary btn-sm text-start">
            <i class="bi bi-person me-2"></i>My Account
          </a>
          <a href="<?php echo site_url('account'); ?>?tab=orders" class="btn btn-outline-secondary btn-sm text-start">
            <i class="bi bi-box me-2"></i>My Orders
          </a>
          <a href="<?php echo site_url('wishlist'); ?>" class="btn btn-outline-secondary btn-sm text-start">
            <i class="bi bi-heart me-2"></i>My Wishlist
          </a>
          <a href="<?php echo site_url('checkout'); ?>" class="btn btn-saffron btn-sm">
            <i class="bi bi-cart-check me-2"></i>Go to Checkout
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Address Modal -->
<div class="modal fade" id="addrModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" action="<?php echo site_url('my-addresses'); ?>">
        <input type="hidden" name="addr_id" id="addrId" value="0">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title" id="addrModalTitle">Add New Address</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-600">Label</label>
              <select class="form-select" name="label" id="addrLabel">
                <option value="Home">Home</option>
                <option value="Work">Work</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-600">Full Name *</label>
              <input type="text" class="form-control" name="name" id="addrName" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-600">Phone *</label>
              <input type="tel" class="form-control" name="phone" id="addrPhone" required>
            </div>
            <div class="col-12">
              <label class="form-label small fw-600">Address Line *</label>
              <input type="text" class="form-control" name="address_line" id="addrLine"
                     placeholder="Flat/Building, Street, Area" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-600">City *</label>
              <input type="text" class="form-control" name="city" id="addrCity" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-600">State</label>
              <input type="text" class="form-control" name="state" id="addrState">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-600">PIN Code *</label>
              <input type="text" class="form-control" name="pincode" id="addrPin" maxlength="6" required>
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_default" id="addrDefault" value="1">
                <label class="form-check-label small" for="addrDefault">Set as default address</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-saffron">Save Address</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function resetAddrModal() {
  document.getElementById('addrModalTitle').textContent = 'Add New Address';
  document.getElementById('addrId').value    = '0';
  document.getElementById('addrLabel').value = 'Home';
  document.getElementById('addrName').value  = '';
  document.getElementById('addrPhone').value = '';
  document.getElementById('addrLine').value  = '';
  document.getElementById('addrCity').value  = '';
  document.getElementById('addrState').value = '';
  document.getElementById('addrPin').value   = '';
  document.getElementById('addrDefault').checked = false;
}
function openEditAddr(a) {
  document.getElementById('addrModalTitle').textContent = 'Edit Address';
  document.getElementById('addrId').value    = a.id;
  document.getElementById('addrLabel').value = a.label || 'Home';
  document.getElementById('addrName').value  = a.name;
  document.getElementById('addrPhone').value = a.phone;
  document.getElementById('addrLine').value  = a.address_line;
  document.getElementById('addrCity').value  = a.city;
  document.getElementById('addrState').value = a.state || '';
  document.getElementById('addrPin').value   = a.pincode;
  document.getElementById('addrDefault').checked = a.is_default == 1;
  var modal = new bootstrap.Modal(document.getElementById('addrModal'));
  modal.show();
}
</script>
