<script>
/* ── Review Management AJAX ── */
var REVIEW_ACTION_URL = '<?php echo site_url("ajax/review-action"); ?>';

var statusClsMap = {
  approved : 'label-success',
  pending  : 'label-warning',
  rejected : 'label-danger'
};

/* Single action buttons */
$(document).on('click', '.rev-action-btn', function () {
  var $btn    = $(this);
  var id      = $btn.data('id');
  var action  = $btn.data('action');
  if (action === 'delete' && !confirm('Delete this review permanently?')) return;

  $btn.prop('disabled', true);
  $.post(REVIEW_ACTION_URL, { review_id: id, action: action })
    .done(function (data) {
      smAlert(data.message, data.success ? 'success' : 'danger');
      if (!data.success) return;

      var $row = $('#rev-row-' + id);

      if (data.deleted) {
        $row.fadeOut(300, function () { $(this).remove(); });
        return;
      }

      /* Update status badge */
      var $badge = $('#rev-status-' + id);
      if (action === 'approve' || action === 'reject' || action === 'pending') {
        var newLabel = action.charAt(0).toUpperCase() + action.slice(1);
        $badge.attr('class', 'label ' + (statusClsMap[action] || 'label-default') + ' rev-status-badge').text(newLabel);
      }

      /* Toggle feature icon */
      if (action === 'feature') {
        $btn.data('action', 'unfeature').attr('title', 'Unfeature')
            .find('i').removeClass('fa-bookmark-o').addClass('fa-bookmark');
        $row.find('.text-warning').append(' <span class="label label-warning label-xs"><i class="fa fa-bookmark"></i></span>');
      }
      if (action === 'unfeature') {
        $btn.data('action', 'feature').attr('title', 'Feature')
            .find('i').removeClass('fa-bookmark').addClass('fa-bookmark-o');
        $row.find('.label-warning.label-xs').remove();
      }

      /* Show/hide approve & reject buttons based on new status */
      if (action === 'approve') {
        $row.find('[data-action="approve"]').hide();
        $row.find('[data-action="reject"]').show();
      }
      if (action === 'reject') {
        $row.find('[data-action="reject"]').hide();
        $row.find('[data-action="approve"]').show();
      }
    })
    .fail(function () { smAlert('Server error. Please try again.', 'danger'); })
    .always(function () { $btn.prop('disabled', false); });
});

/* Select all checkbox */
$('#checkAll').on('change', function () {
  $('.rev-check').prop('checked', this.checked);
  updateBulkBar();
});
$(document).on('change', '.rev-check', updateBulkBar);

function updateBulkBar() {
  var cnt = $('.rev-check:checked').length;
  $('#bulkCount').text(cnt + ' selected');
  cnt > 0 ? $('#bulkBar').show() : $('#bulkBar').hide();
}

function bulkAction(action) {
  var ids = $('.rev-check:checked').map(function () { return $(this).val(); }).get();
  if (!ids.length) { smAlert('Select at least one review.', 'danger'); return; }
  if (action === 'delete' && !confirm('Delete ' + ids.length + ' review(s) permanently?')) return;

  $.post(REVIEW_ACTION_URL, { bulk_ids: ids, bulk_action: action })
    .done(function (data) {
      smAlert(data.message, data.success ? 'success' : 'danger');
      if (!data.success) return;
      if (data.deleted) {
        $.each(data.ids, function (_, id) { $('#rev-row-' + id).fadeOut(200, function () { $(this).remove(); }); });
      } else {
        $.each(data.ids, function (_, id) {
          var $badge = $('#rev-status-' + id);
          var lbl = data.new_status.charAt(0).toUpperCase() + data.new_status.slice(1);
          $badge.attr('class', 'label ' + (statusClsMap[data.new_status] || 'label-default') + ' rev-status-badge').text(lbl);
        });
      }
      $('#checkAll').prop('checked', false);
      updateBulkBar();
    })
    .fail(function () { smAlert('Server error. Please try again.', 'danger'); });
}
</script>
