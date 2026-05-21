<script>
$(function () {

  /* ── Segment filter (Customers tab) ── */
  $(document).on('click', '.seg-filter-btn', function () {
    $('.seg-filter-btn').removeClass('active');
    $(this).addClass('active');
    var seg = $(this).data('seg');
    $('#custLoyTable tbody tr').each(function () {
      var ok = seg === 'all' || $(this).data('seg') === seg;
      $(this).toggle(ok);
    });
  });

  /* ── Customer loyalty search ── */
  $('#custLoySearch').on('input', function () {
    var q = this.value.trim().toLowerCase();
    $('#custLoyTable tbody tr').each(function () {
      $(this).toggle(!q || $(this).text().toLowerCase().indexOf(q) >= 0);
    });
  });

  /* ── Adjust Points modal ── */
  $(document).on('click', '.adj-btn', function () {
    $('#adjUserId').val($(this).data('id'));
    $('#adjUserName').text($(this).data('name'));
    $('#adjBalance').text($(this).data('balance'));
    $('#adjPoints').val('');
    $('#adjModal').modal('show');
  });

  /* ── Set Birthday modal ── */
  $(document).on('click', '.bday-btn', function () {
    $('#bdayUserId').val($(this).data('id'));
    $('#bdayUserName').text($(this).data('name'));
    $('#bdayDate').val($(this).data('bday') || '');
    $('#bdayModal').modal('show');
  });

  /* ── Campaign search ── */
  (function () {
    var rows  = Array.from(document.querySelectorAll('#campTable tbody tr'));
    var count = document.getElementById('campCount');
    function run() {
      var q = document.getElementById('campSearch').value.trim().toLowerCase();
      var n = 0;
      rows.forEach(function (r) {
        var ok = !q || r.textContent.toLowerCase().indexOf(q) >= 0;
        r.style.display = ok ? '' : 'none';
        if (ok) n++;
      });
    }
    var el = document.getElementById('campSearch');
    if (el) el.addEventListener('input', run);
    var cl = document.getElementById('campClear');
    if (cl) cl.addEventListener('click', function () { el.value = ''; run(); });
  })();

  /* ── Add Campaign button ── */
  $('#addCampaignBtn').on('click', function () {
    resetCampModal();
    $('#campModal').modal('show');
  });

  /* ── Edit Campaign button ── */
  $(document).on('click', '.edit-camp-btn', function () {
    var c = $(this).data('camp');
    resetCampModal();
    $('#campModalTitle').html('<i class="fa fa-pencil"></i> Edit Campaign — ' + c.name);
    $('#campSaveLabel').text('Update Campaign');
    $('#campId').val(c.id);
    $('#campName').val(c.name);
    $('#campType').val(c.type);
    $('#campStatus').val(c.status);
    $('#campOfferType').val(c.offer_type);
    $('#campOfferValue').val(c.offer_value);
    $('#campTarget').val(c.target);
    $('#campCoupon').val(c.coupon_code || '');
    $('#campStart').val(c.start_date ? c.start_date.substring(0,10) : '');
    $('#campEnd').val(c.end_date ? c.end_date.substring(0,10) : '');
    $('#campFestDate').val(c.festival_date ? c.festival_date.substring(0,10) : '');
    $('#campDesc').val(c.description || '');
    $('#campMessage').val(c.message || '');
    toggleFestDate(c.type);
    $('#campModal').modal('show');
  });

  function resetCampModal() {
    $('#campModalTitle').html('<i class="fa fa-bullhorn"></i> Add Campaign');
    $('#campSaveLabel').text('Save Campaign');
    $('#campId').val('0');
    $('#campName, #campOfferValue, #campCoupon, #campDesc, #campMessage').val('');
    $('#campType').val('general');
    $('#campStatus').val('draft');
    $('#campOfferType').val('points_bonus');
    $('#campTarget').val('all');
    $('#campStart, #campEnd, #campFestDate').val('');
    $('#festDateWrap').hide();
  }

  /* ── Show festival date only for festival type ── */
  function toggleFestDate(type) {
    $('#festDateWrap').toggle(type === 'festival');
  }
  $('#campType').on('change', function () { toggleFestDate($(this).val()); });

  /* ── Activity log filter ── */
  (function () {
    var rows   = Array.from(document.querySelectorAll('#ledgerTable tbody tr'));
    var search = document.getElementById('ledgerSearch');
    var filter = document.getElementById('ledgerFilter');
    var count  = document.getElementById('ledgerCount');
    function run() {
      var q = search ? search.value.trim().toLowerCase() : '';
      var f = filter ? filter.value.toLowerCase() : '';
      var n = 0;
      rows.forEach(function (r) {
        if (r.cells.length < 2) { r.style.display = ''; return; }
        var typeCell = r.cells[2] ? r.cells[2].textContent.trim().toLowerCase() : '';
        var ok = (!q || r.textContent.toLowerCase().indexOf(q) >= 0)
              && (!f || typeCell.indexOf(f) >= 0);
        r.style.display = ok ? '' : 'none';
        if (ok) n++;
      });
      if (count) count.textContent = n + ' / ' + rows.length + ' entries';
    }
    if (search) search.addEventListener('input', run);
    if (filter) filter.addEventListener('change', run);
  })();

});
</script>
