<script>
/* ── Orders status update — AJAX (zero refresh) ── */
$(function () {
  var $form = $('#orderUpdateForm');
  if (!$form.length) return;

  $form.on('submit', function (e) {
    e.preventDefault();
    var $btn = $form.find('button[type=submit]');
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving…');

    $.post($form.attr('action'), $form.serialize())
      .done(function (data) {
        smAlert(data.message, data.success ? 'success' : 'danger');

        if (data.success) {
          var badgeMap = {
            pending    : 'label-warning',
            processing : 'label-info',
            shipped    : 'label-primary',
            delivered  : 'label-success',
            cancelled  : 'label-danger'
          };
          var newCls   = badgeMap[data.new_status] || 'label-default';
          var newLabel = data.new_status.charAt(0).toUpperCase() + data.new_status.slice(1);

          /* Update the row in the left-hand orders table */
          var $row = $('tr[data-order-id="' + data.order_id + '"]');
          $row.find('.label').filter(function () {
            return /label-(warning|info|primary|success|danger|default)/.test(this.className);
          }).first().attr('class', 'label ' + newCls).text(newLabel);

          /* Update the detail panel header badge */
          $('.box-primary .label').filter(function () {
            return /label-(warning|info|primary|success|danger|default)/.test(this.className);
          }).first().attr('class', 'label ' + newCls).text(newLabel);

          /* If now delivered, update payment badge to Paid for COD */
          if (data.new_status === 'delivered' && data.payment_status) {
            var payMap = { paid:'label-success', pending:'label-warning', failed:'label-danger' };
            var payCls = payMap[data.payment_status] || 'label-default';
            var payLabel = data.payment_status.charAt(0).toUpperCase() + data.payment_status.slice(1);
            /* Update payment badge in detail panel */
            $('.callout-info .label-default, .callout-info [class*="label-"]').last()
              .attr('class', 'label ' + payCls).text(payLabel);
          }
        }
      })
      .fail(function () { smAlert('Server error. Please try again.', 'danger'); })
      .always(function () {
        $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Update Order');
      });
  });
});
</script>
