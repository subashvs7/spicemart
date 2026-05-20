<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Register | <?php echo APP_NAME; ?></title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="<?php echo base_url(); ?>asset/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>asset/bower_components/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>asset/dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700">
  <style>
    .register-page { background: linear-gradient(135deg, #2C1810, #5C2E1A); }
    .login-box { width: 420px; }
    .login-box-body { border-radius: 16px; }
    .brand-h { color: #7B4228; font-weight: 700; }
  </style>
</head>
<body class="hold-transition register-page" style="background:linear-gradient(135deg,#2C1810,#5C2E1A)">
<div class="login-box" style="margin:5vh auto">
  <div class="login-box-body">
    <div class="login-logo">
      <h3 class="brand-h">🌶️ <?php echo APP_NAME; ?></h3>
      <p class="text-muted small">Create your account</p>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0" style="padding-left:18px">
          <?php foreach ($errors as $e): ?>
            <li><?php echo $e; ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form action="<?php echo site_url('register'); ?>" method="post">
      <input type="hidden" name="mode" value="Register">

      <div class="form-group has-feedback">
        <input type="text" name="name" class="form-control" placeholder="Full Name" required
               value="<?php echo htmlspecialchars($form['name'] ?? ''); ?>">
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>

      <div class="form-group has-feedback">
        <input type="email" name="email" class="form-control" placeholder="Email address" required
               value="<?php echo htmlspecialchars($form['email'] ?? ''); ?>">
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>

      <div class="form-group has-feedback">
        <input type="tel" name="phone" class="form-control" placeholder="Phone number"
               value="<?php echo htmlspecialchars($form['phone'] ?? ''); ?>">
        <span class="glyphicon glyphicon-phone form-control-feedback"></span>
      </div>

      <div class="form-group has-feedback">
        <input type="password" name="password" class="form-control" placeholder="Password (min 8 chars)" required minlength="8">
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>

      <div class="form-group has-feedback">
        <input type="password" name="confirm" class="form-control" placeholder="Confirm password" required>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>

      <div class="row">
        <div class="col-xs-12">
          <button type="submit" class="btn btn-block btn-flat" style="background:#7B4228;color:#fff">
            Create Account
          </button>
        </div>
      </div>
    </form>

    <div class="text-center mt-3">
      <a href="<?php echo site_url('login'); ?>" class="text-muted small">
        Already have an account? <strong>Sign In</strong>
      </a>
    </div>
    <div class="text-center mt-2">
      <a href="<?php echo site_url('home'); ?>" class="text-muted small">← Back to Store</a>
    </div>
  </div>
</div>

<script src="<?php echo base_url(); ?>asset/bower_components/jquery/dist/jquery.min.js"></script>
<script src="<?php echo base_url(); ?>asset/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html>
