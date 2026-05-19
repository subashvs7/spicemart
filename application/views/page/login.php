<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Login | <?php echo APP_NAME; ?></title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="<?php echo base_url(); ?>asset/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>asset/bower_components/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>asset/dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700">
  <style>
    .login-page { background: linear-gradient(135deg, #2C1810, #5C2E1A); }
    .login-box-body { border-radius: 16px; }
    .login-logo h3 { color: #FF6B35; font-weight: 700; }
  </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-box-body">
    <div class="login-logo">
      <h3>🌶️ <?php echo APP_NAME; ?></h3>
      <p class="text-muted small">Admin & Customer Login</p>
    </div>

    <?php if (!$login && !empty($error)): ?>
      <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <form action="<?php echo site_url('login'); ?>" method="post">
      <input type="hidden" name="mode" value="Login">
      <div class="form-group has-feedback">
        <input type="email" name="email" class="form-control" placeholder="Email address" required
               value="<?php echo set_value('email'); ?>">
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="row">
        <div class="col-xs-12">
          <button type="submit" class="btn btn-block btn-flat"
                  style="background:#FF6B35;color:#fff">Sign In</button>
        </div>
      </div>
    </form>

    <div class="text-center mt-3">
      <a href="<?php echo site_url('register'); ?>" class="text-muted small">
        Don't have an account? <strong>Register</strong>
      </a>
    </div>
    <div class="text-center mt-2">
      <a href="<?php echo site_url('home'); ?>" class="text-muted small">
        ← Back to Store
      </a>
    </div>
  </div>
</div>

<script src="<?php echo base_url(); ?>asset/bower_components/jquery/dist/jquery.min.js"></script>
<script src="<?php echo base_url(); ?>asset/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html>
