<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
  <div class="<?php echo $view_msg ? 'col-md-7' : 'col-md-12'; ?>">
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-envelope"></i> Contact Messages</h3>
      </div>
      <div class="box-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover admin-table">
            <thead>
              <tr><th>Name</th><th>Email</th><th>Subject</th><th>Date</th><th></th></tr>
            </thead>
            <tbody>
              <?php foreach ($contacts as $msg): ?>
              <tr <?php echo !$msg['is_read'] ? 'class="warning"' : ''; ?>>
                <td>
                  <?php if (!$msg['is_read']): ?><i class="fa fa-circle text-yellow" title="Unread"></i>&nbsp;<?php endif; ?>
                  <strong><?php echo htmlspecialchars($msg['name']); ?></strong>
                </td>
                <td><?php echo htmlspecialchars($msg['email']); ?></td>
                <td><?php echo htmlspecialchars($this->spice_model->truncate_text($msg['subject'] ?? '(no subject)', 40)); ?></td>
                <td><?php echo date('d M Y', strtotime($msg['created_at'])); ?></td>
                <td>
                  <a href="<?php echo site_url('admin-contacts'); ?>?view=<?php echo $msg['id']; ?>"
                     class="btn btn-xs btn-default"><i class="fa fa-eye"></i></a>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($contacts)): ?>
                <tr><td colspan="5" class="text-center text-muted">No messages yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <?php if ($view_msg): ?>
  <div class="col-md-5">
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title">Message from <?php echo htmlspecialchars($view_msg['name']); ?></h3>
        <div class="box-tools pull-right">
          <a href="<?php echo site_url('admin-contacts'); ?>" class="btn btn-xs btn-default">
            <i class="fa fa-times"></i>
          </a>
        </div>
      </div>
      <div class="box-body">
        <div class="callout callout-info">
          <strong><?php echo htmlspecialchars($view_msg['name']); ?></strong><br>
          <a href="mailto:<?php echo htmlspecialchars($view_msg['email']); ?>"><?php echo htmlspecialchars($view_msg['email']); ?></a>
          <?php if ($view_msg['phone']): ?>
            &nbsp;|&nbsp; <?php echo htmlspecialchars($view_msg['phone']); ?>
          <?php endif; ?>
        </div>

        <?php if ($view_msg['subject']): ?>
          <p><strong>Subject:</strong> <?php echo htmlspecialchars($view_msg['subject']); ?></p>
        <?php endif; ?>

        <div class="well well-sm" style="white-space:pre-line;word-break:break-word">
          <?php echo htmlspecialchars($view_msg['message']); ?>
        </div>

        <p class="text-muted small">
          Received: <?php echo date('d M Y, h:i A', strtotime($view_msg['created_at'])); ?>
        </p>

        <a href="mailto:<?php echo htmlspecialchars($view_msg['email']); ?>"
           class="btn btn-primary btn-sm">
          <i class="fa fa-reply"></i> Reply via Email
        </a>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
