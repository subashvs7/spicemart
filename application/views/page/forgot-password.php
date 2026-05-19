<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Forgot Password | <?php echo APP_NAME; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?php echo base_url('public/css/custom.css'); ?>" rel="stylesheet">
</head>
<body style="background:linear-gradient(135deg,#2C1810,#5C2E1A);min-height:100vh;display:flex;align-items:center">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">

      <div class="text-center mb-4">
        <a href="<?php echo site_url('home'); ?>" class="text-decoration-none">
          <div style="font-size:3rem">🌶️</div>
          <div style="font-family:'Playfair Display',serif;font-size:1.6rem;color:#fff;font-weight:700">SpiceMart</div>
        </a>
      </div>

      <div class="bg-white rounded-xl shadow-soft p-4">
        <h4 class="mb-1" style="font-family:'Playfair Display',serif">Reset Password</h4>

        <?php
        $stepLabels = array('email'=>'Step 1: Enter Email', 'otp'=>'Step 2: Verify OTP', 'reset'=>'Step 3: New Password');
        $stepNum    = array('email'=>1,'otp'=>2,'reset'=>3);
        $cur = $step ?? 'email';
        ?>
        <div class="d-flex gap-1 mb-4">
          <?php for ($s = 1; $s <= 3; $s++): ?>
            <div class="flex-fill py-1 rounded text-center small"
                 style="background:<?php echo $stepNum[$cur] >= $s ? 'var(--saffron)' : '#e9ecef'; ?>;
                        color:<?php echo $stepNum[$cur] >= $s ? '#fff' : '#6c757d'; ?>;font-weight:600">
              <?php echo $s; ?>
            </div>
          <?php endfor; ?>
        </div>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger py-2"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
          <div class="alert alert-success py-2"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($step === 'email'): ?>
        <!-- Step 1: Email -->
        <form method="post" action="<?php echo site_url('forgot-password'); ?>">
          <input type="hidden" name="step" value="email">
          <div class="mb-3">
            <label class="form-label small fw-600">Registered Email *</label>
            <input type="email" class="form-control" name="email" required
                   placeholder="you@example.com" autofocus>
          </div>
          <button type="submit" class="btn btn-saffron w-100">
            Send OTP <i class="bi bi-arrow-right ms-1"></i>
          </button>
        </form>

        <?php elseif ($step === 'otp'): ?>
        <!-- Step 2: OTP -->
        <?php if (!empty($demo_otp)): ?>
          <div class="alert alert-warning py-2">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Demo OTP:</strong> <span style="font-size:1.4rem;font-weight:700;letter-spacing:4px"><?php echo htmlspecialchars($demo_otp); ?></span><br>
            <small class="text-muted">(In production this would be emailed to you.)</small>
          </div>
        <?php endif; ?>
        <form method="post" action="<?php echo site_url('forgot-password'); ?>">
          <input type="hidden" name="step" value="otp">
          <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">
          <div class="mb-3">
            <label class="form-label small fw-600">Enter 6-digit OTP *</label>
            <input type="text" class="form-control text-center" name="otp" required
                   maxlength="6" placeholder="000000" autofocus
                   style="font-size:1.5rem;letter-spacing:6px;font-weight:700">
            <small class="text-muted">OTP expires in 15 minutes.</small>
          </div>
          <button type="submit" class="btn btn-saffron w-100">
            Verify OTP <i class="bi bi-check-circle ms-1"></i>
          </button>
        </form>

        <?php elseif ($step === 'reset'): ?>
        <!-- Step 3: New Password -->
        <form method="post" action="<?php echo site_url('forgot-password'); ?>">
          <input type="hidden" name="step" value="reset">
          <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">
          <div class="mb-3">
            <label class="form-label small fw-600">New Password *</label>
            <input type="password" class="form-control" name="password" required minlength="6" autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-600">Confirm Password *</label>
            <input type="password" class="form-control" name="confirm_password" required>
          </div>
          <button type="submit" class="btn btn-saffron w-100">
            <i class="bi bi-shield-check me-1"></i> Reset Password
          </button>
        </form>

        <?php else: ?>
        <!-- Success / complete -->
        <div class="text-center py-3">
          <div style="font-size:3rem">✅</div>
          <h5 class="mt-3">Password Reset!</h5>
          <a href="<?php echo site_url('login'); ?>" class="btn btn-saffron mt-2">Go to Login</a>
        </div>
        <?php endif; ?>

        <div class="text-center mt-3">
          <a href="<?php echo site_url('login'); ?>" class="text-muted small">← Back to Login</a>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
