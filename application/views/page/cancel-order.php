<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container py-4">
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?php echo site_url('account'); ?>?tab=orders">My Orders</a></li>
      <li class="breadcrumb-item active">Cancel Order</li>
    </ol>
  </nav>

  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="bg-white rounded-xl shadow-soft p-4">
        <h4 class="mb-1" style="font-family:'Playfair Display',serif">Cancel Order</h4>
        <p class="text-muted mb-4">
          Order <strong>#<?php echo str_pad($order['id'],5,'0',STR_PAD_LEFT); ?></strong>
          &middot; ₹<?php echo number_format((float)$order['total_amount'],2); ?>
        </p>

        <div class="alert alert-warning">
          <i class="bi bi-exclamation-triangle me-2"></i>
          This action is permanent. Once cancelled, the order cannot be restored.
        </div>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="<?php echo site_url('cancel-order/'.$order['id']); ?>">
          <div class="mb-3">
            <label class="form-label fw-600">Reason for Cancellation *</label>
            <textarea class="form-control" name="reason" rows="4" required
                      placeholder="Please tell us why you want to cancel this order…"></textarea>
          </div>
          <div class="d-flex gap-3">
            <a href="<?php echo site_url('account'); ?>?tab=orders" class="btn btn-outline-secondary flex-fill">
              Go Back
            </a>
            <button type="submit" name="submit_cancel" value="1" class="btn btn-danger flex-fill">
              <i class="bi bi-x-circle me-1"></i> Confirm Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
