<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container py-4">
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?php echo site_url('account'); ?>?tab=orders">My Orders</a></li>
      <li class="breadcrumb-item active">Return Order</li>
    </ol>
  </nav>

  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="bg-white rounded-xl shadow-soft p-4">
        <h4 class="mb-1" style="font-family:'Playfair Display',serif">Return Request</h4>
        <p class="text-muted mb-4">
          Order <strong>#<?php echo str_pad($order['id'],5,'0',STR_PAD_LEFT); ?></strong>
          &middot; ₹<?php echo number_format((float)$order['total_amount'],2); ?>
        </p>

        <div class="alert alert-info">
          <i class="bi bi-info-circle me-2"></i>
          Returns are accepted within 7 days of delivery. Our team will contact you within 24 hours.
        </div>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="<?php echo site_url('return-order/'.$order['id']); ?>">
          <div class="mb-3">
            <label class="form-label fw-600">Reason for Return *</label>
            <select class="form-select mb-2" name="reason_type" id="reasonType">
              <option value="">Select a reason…</option>
              <option value="Wrong item received">Wrong item received</option>
              <option value="Damaged/defective product">Damaged/defective product</option>
              <option value="Product not as described">Product not as described</option>
              <option value="Quality not satisfactory">Quality not satisfactory</option>
              <option value="Other">Other</option>
            </select>
            <textarea class="form-control" name="reason" rows="3" required
                      placeholder="Describe the issue in detail…"></textarea>
          </div>
          <div class="d-flex gap-3">
            <a href="<?php echo site_url('account'); ?>?tab=orders" class="btn btn-outline-secondary flex-fill">
              Go Back
            </a>
            <button type="submit" name="submit_return" value="1" class="btn btn-warning flex-fill">
              <i class="bi bi-arrow-return-left me-1"></i> Submit Return
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('reasonType').addEventListener('change', function() {
  var ta = this.closest('form').querySelector('textarea');
  if (this.value && this.value !== 'Other') ta.value = this.value;
});
</script>
