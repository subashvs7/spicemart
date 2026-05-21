    </section>
  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
    <div class="pull-right hidden-xs">
      <strong><?php echo APP_NAME; ?></strong> Admin Panel
    </div>
    <strong>Copyright &copy; <?php echo date('Y'); ?> myeoncasuals.</strong> All rights reserved.
  </footer>
</div>
<!-- ./wrapper -->

<!-- jQuery 3 -->
<script src="<?php echo base_url() ?>asset/bower_components/jquery/dist/jquery.min.js"></script>
<!-- jQuery UI -->
<script src="<?php echo base_url() ?>asset/bower_components/jquery-ui/jquery-ui.min.js"></script>
<script>$.widget.bridge('uibutton', $.ui.button);</script>
<!-- Bootstrap 3.3.7 -->
<script src="<?php echo base_url() ?>asset/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Slimscroll -->
<script src="<?php echo base_url() ?>asset/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="<?php echo base_url() ?>asset/bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="<?php echo base_url() ?>asset/dist/js/adminlte.min.js"></script>

<script>
/* ── Global: auto-dismiss .alert elements after 3 seconds ── */
$(function () {
  function smDismiss(el) {
    setTimeout(function () {
      $(el).fadeTo(400, 0).slideUp(300, function () { $(this).remove(); });
    }, 3000);
  }
  $('.alert:not(.alert-permanent)').each(function () { smDismiss(this); });

  /* Watch for dynamically injected alerts */
  new MutationObserver(function (muts) {
    muts.forEach(function (m) {
      m.addedNodes.forEach(function (n) {
        if (n.nodeType !== 1) return;
        if ($(n).hasClass('alert') && !$(n).hasClass('alert-permanent')) smDismiss(n);
        $(n).find('.alert:not(.alert-permanent)').each(function () { smDismiss(this); });
      });
    });
  }).observe(document.body, { childList: true, subtree: true });
});

/* ── Global: inject a dismissible alert at the top of .content ── */
function smAlert(msg, type) {
  type = type || 'success';
  var icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
  var el = $('<div class="alert alert-' + type + '" style="margin:0 0 12px">' +
    '<i class="fa fa-' + icon + '"></i> ' + msg + '</div>');
  var target = $('.content-wrapper .content').first();
  if (!target.length) target = $('.content-wrapper').first();
  target.prepend(el);
}
</script>
<?php
if (isset($js) && !empty($js)) {
    include_once(APPPATH . 'views/inc/inc-js/' . $js);
}
?>
</body>
</html>
