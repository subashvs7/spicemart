<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
  <div class="<?php echo $view_msg ? 'col-md-7' : 'col-md-12'; ?>">
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-envelope"></i> Contact Messages</h3>
      </div>
      <div class="box-body">
        <!-- Filter bar -->
        <div class="row" style="margin-bottom:10px">
          <div class="col-sm-5">
            <div class="input-group input-group-sm">
              <span class="input-group-addon"><i class="fa fa-search"></i></span>
              <input type="text" class="form-control" id="msg_search" placeholder="Search name, email, subject…">
              <span class="input-group-btn">
                <button type="button" class="btn btn-default" id="msg_clear" title="Clear"><i class="fa fa-times"></i></button>
              </span>
            </div>
          </div>
          <div class="col-sm-2 col-xs-6">
            <select class="form-control input-sm" id="msg_fRead">
              <option value="">All Messages</option>
              <option value="unread">Unread</option>
              <option value="read">Read</option>
            </select>
          </div>
          <div class="col-sm-5" style="line-height:30px">
            <small class="text-muted" id="msg_count"></small>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-bordered table-hover admin-table" id="msg_table">
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

  <script>
  (function () {
    var rows   = Array.from(document.querySelectorAll('#msg_table tbody tr'));
    var search = document.getElementById('msg_search');
    var count  = document.getElementById('msg_count');
    function run() {
      var q  = search.value.trim().toLowerCase();
      var fR = document.getElementById('msg_fRead').value;
      var n = 0;
      rows.forEach(function (r) {
        if (r.cells.length < 2) { r.style.display = ''; return; }
        var isUnread = r.classList.contains('warning');
        var readOk = !fR
          || (fR === 'unread' && isUnread)
          || (fR === 'read'   && !isUnread);
        var ok = (!q || r.textContent.toLowerCase().indexOf(q) >= 0) && readOk;
        r.style.display = ok ? '' : 'none';
        if (ok) n++;
      });
      count.textContent = n + ' / ' + rows.length + ' messages';
    }
    search.addEventListener('input', run);
    document.getElementById('msg_clear').addEventListener('click', function () { search.value = ''; run(); });
    document.getElementById('msg_fRead').addEventListener('change', run);
  })();
  </script>

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
