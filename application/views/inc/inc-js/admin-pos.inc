<script>
/* ── POS Manual Sync — AJAX ── */
$(function () {
  var $form = $('#manualSyncForm');
  if (!$form.length) return;

  $form.on('submit', function (e) {
    e.preventDefault();
    var $btn = $('#manualSyncBtn');
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Syncing…');
    $('#manualSyncResult').empty();

    $.post($form.attr('action'), $form.serialize())
      .done(function (data) {
        var type = data.success ? 'success' : 'danger';
        var icon = data.success ? 'check-circle' : 'exclamation-triangle';
        var html = '<div class="alert alert-' + type + '">' +
          '<i class="fa fa-' + icon + '"></i> ' + data.message + '</div>';
        $('#manualSyncResult').html(html);

        if (data.success && data.product) {
          var p = data.product;
          /* Update the reference table row for instant feedback */
          $('#prodCodeTable tbody tr').each(function () {
            var code = $(this).find('code').text().trim();
            if (code === p.product_code) {
              $(this).find('td').eq(2).html(
                '<small>₹' + parseFloat(p.price).toFixed(2) +
                (p.offer_price ? ' <span class="text-success">(₹' + parseFloat(p.offer_price).toFixed(2) + ')</span>' : '') + '</small>'
              );
              $(this).find('td').eq(3).html('<small>' + p.stock_qty + '</small>');
              $(this).find('td').eq(4).html(
                '<span class="label label-' + (p.status ? 'success' : 'danger') + ' label-xs">' + (p.status ? 'On' : 'Off') + '</span>'
              );
            }
          });
        }
      })
      .fail(function () {
        $('#manualSyncResult').html(
          '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Server error. Please try again.</div>'
        );
      })
      .always(function () {
        $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Apply Manual Sync');
      });
  });
});

/* ── Copy webhook URL ── */
function copyWebhook() {
  var el = document.getElementById('webhookUrl');
  if (!el) return;
  el.select();
  document.execCommand('copy');
  var btn = el.nextElementSibling.querySelector('button');
  if (btn) { btn.innerHTML = '<i class="fa fa-check"></i> Copied'; setTimeout(function(){ btn.innerHTML = '<i class="fa fa-copy"></i> Copy'; }, 2000); }
}

/* ── Reveal API key ── */
function revealKey(key, id) {
  var el = document.getElementById('key_' + id);
  if (el) {
    el.textContent = key;
    el.style.color = '#c7254e';
  }
}

/* ── Manual sync field toggle ── */
function toggleManualFields(type) {
  var sections = { stock: 'fieldsStock', price: 'fieldsPrice', availability: 'fieldsAvail' };
  Object.keys(sections).forEach(function (k) {
    var el = document.getElementById(sections[k]);
    if (el) el.style.display = k === type ? '' : 'none';
  });
}

/* ── Fill product code from table click ── */
function fillProductCode(code) {
  var el = document.getElementById('manualProductCode');
  if (el) { el.value = code; el.focus(); }
}

/* ── Sync log search ── */
(function () {
  var input = document.getElementById('logSearch');
  if (!input) return;
  var rows = Array.from(document.querySelectorAll('#logTable tbody tr'));
  input.addEventListener('input', function () {
    var q = input.value.trim().toLowerCase();
    rows.forEach(function (r) {
      r.style.display = (!q || r.textContent.toLowerCase().indexOf(q) >= 0) ? '' : 'none';
    });
  });
})();

/* ── Log detail modal ── */
function showLogDetail(log) {
  var sc = { success: 'success', failed: 'danger', partial: 'warning', running: 'info' };
  var html = '<table class="table table-condensed"><tbody>';
  html += '<tr><td><strong>Log #</strong></td><td>' + log.id + '</td></tr>';
  html += '<tr><td><strong>Type</strong></td><td><span class="label label-info">' + log.sync_type + '</span></td></tr>';
  html += '<tr><td><strong>Source</strong></td><td>' + log.source + '</td></tr>';
  html += '<tr><td><strong>Status</strong></td><td><span class="label label-' + (sc[log.status]||'default') + '">' + log.status + '</span></td></tr>';
  html += '<tr><td><strong>Sent</strong></td><td>' + log.records_sent + '</td></tr>';
  html += '<tr><td><strong>Updated</strong></td><td class="text-success"><strong>' + log.records_updated + '</strong></td></tr>';
  html += '<tr><td><strong>Failed</strong></td><td class="text-danger">' + log.records_failed + '</td></tr>';
  html += '<tr><td><strong>IP</strong></td><td>' + (log.request_ip || '—') + '</td></tr>';
  html += '<tr><td><strong>Started</strong></td><td>' + (log.started_at || '—') + '</td></tr>';
  html += '<tr><td><strong>Completed</strong></td><td>' + (log.completed_at || '—') + '</td></tr>';
  if (log.error_message) {
    html += '<tr><td><strong>Errors</strong></td><td class="text-danger small">' + log.error_message + '</td></tr>';
  }
  if (log.payload_summary) {
    html += '<tr><td><strong>Payload Sample</strong></td><td><pre style="font-size:11px;max-height:150px;overflow-y:auto">' + log.payload_summary + '</pre></td></tr>';
  }
  html += '</tbody></table>';
  document.getElementById('logDetailBody').innerHTML = html;
  $('#logDetailModal').modal('show');
}
</script>
