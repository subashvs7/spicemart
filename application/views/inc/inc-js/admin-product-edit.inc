<script>
$(function () {

  // ── Gallery Upload ─────────────────────────────────────────

  $('#uploadImagesBtn').on('click', function () {
    var files = document.getElementById('galleryFileInput').files;
    if (!files.length) {
      alert('Please select at least one image file.');
      return;
    }

    var formData = new FormData();
    formData.append('product_id', PRODUCT_ID);
    $.each(files, function (i, f) { formData.append('images[]', f); });

    $('#uploadStatus').text('Uploading…');
    $(this).prop('disabled', true);
    var btn = this;

    $.ajax({
      url        : UPLOAD_IMG,
      type       : 'POST',
      data       : formData,
      processData: false,
      contentType: false,
      success    : function (res) {
        if (res.success) {
          $('#galleryEmpty').remove();
          $.each(res.uploaded, function (i, img) {
            appendGalleryItem(img);
          });
          if (res.errors && res.errors.length) {
            $('#uploadStatus').html('<span class="text-danger">' + res.errors.join(', ') + '</span>');
          } else {
            $('#uploadStatus').text(res.uploaded.length + ' image(s) uploaded.');
          }
          updateGalleryBadge();
          document.getElementById('galleryFileInput').value = '';
        } else {
          $('#uploadStatus').html('<span class="text-danger">' + res.message + '</span>');
        }
      },
      error: function () {
        $('#uploadStatus').html('<span class="text-danger">Upload failed. Try again.</span>');
      },
      complete: function () { $(btn).prop('disabled', false); }
    });
  });

  function appendGalleryItem(img) {
    var primary = img.is_primary
      ? '<span class="label label-success" style="position:absolute;top:5px;left:5px">Primary</span>'
      : '';
    var setPrimaryBtn = img.is_primary
      ? ''
      : '<button class="btn btn-xs btn-primary set-primary-btn" data-id="' + img.id + '" title="Set as Primary"><i class="fa fa-star"></i> Set Primary</button>';

    var html = '<div class="col-xs-6 col-md-3 gallery-item" id="gi_' + img.id + '" style="margin-bottom:15px">'
      + '<div class="thumbnail" style="position:relative;padding-bottom:36px">'
      + '<img src="' + img.url + '" style="width:100%;height:150px;object-fit:cover;border-radius:4px">'
      + primary
      + '<div style="position:absolute;bottom:5px;left:0;right:0;text-align:center">'
      + setPrimaryBtn
      + ' <button class="btn btn-xs btn-danger delete-img-btn" data-id="' + img.id + '" title="Delete"><i class="fa fa-trash"></i></button>'
      + '</div></div></div>';

    $('#galleryGrid').append(html);
  }

  // ── Set Primary Image ──────────────────────────────────────

  $(document).on('click', '.set-primary-btn', function () {
    var id  = $(this).data('id');
    var btn = this;
    $(btn).prop('disabled', true);

    $.get(PRI_IMG + id, function (res) {
      if (res.success) {
        // Remove all existing Primary labels and set-primary buttons, restore delete-only for all
        $('.gallery-item').each(function () {
          var itemId = this.id.replace('gi_', '');
          var thumb  = $(this).find('.thumbnail');
          thumb.find('.label-success').remove();
          thumb.find('.set-primary-btn').remove();
          // Ensure delete button exists
          if (!thumb.find('.delete-img-btn').length) {
            thumb.find('div[style*="bottom"]').prepend(
              '<button class="btn btn-xs btn-primary set-primary-btn" data-id="' + itemId + '" title="Set as Primary"><i class="fa fa-star"></i> Set Primary</button> '
            );
          }
        });
        // Mark this one as primary
        var $item = $('#gi_' + id);
        $item.find('.thumbnail').prepend('<span class="label label-success" style="position:absolute;top:5px;left:5px">Primary</span>');
        $item.find('.set-primary-btn').remove();
      }
    }).always(function () { $(btn).prop('disabled', false); });
  });

  // ── Delete Image ───────────────────────────────────────────

  $(document).on('click', '.delete-img-btn', function () {
    if (!confirm('Delete this image?')) return;
    var id  = $(this).data('id');
    var btn = this;
    $(btn).prop('disabled', true);

    $.get(DEL_IMG + id, function (res) {
      if (res.success) {
        $('#gi_' + id).remove();
        updateGalleryBadge();
        if ($('.gallery-item').length === 0) {
          $('#galleryGrid').append('<div class="col-md-12" id="galleryEmpty"><p class="text-muted"><i class="fa fa-picture-o"></i> No gallery images yet.</p></div>');
        }
      }
    }).fail(function () { $(btn).prop('disabled', false); });
  });

  function updateGalleryBadge() {
    $('#galleryBadge').text($('.gallery-item').length);
  }

  // ── Variant Type Dropdown ──────────────────────────────────

  $('#variantValue').on('input', function () {
    var sel = document.getElementById('variantTypeSelect');
    if (sel.value === 'color') updateSwatch(this.value);
  });

  // ── Save Variant ───────────────────────────────────────────

  $('#saveVariantBtn').on('click', function () {
    var typeSelect = $('#variantTypeSelect').val();
    var type       = typeSelect === 'custom' ? $.trim($('#variantTypeCustom').val()) : typeSelect;
    var value      = $.trim($('#variantValue').val());
    var price      = $('#variantPrice').val();
    var stock      = $('#variantStock').val();
    var sku        = $.trim($('#variantSku').val());
    var editId     = $('#editVariantId').val();

    if (!type)  { alert('Please select or enter a variant type.'); return; }
    if (!value) { alert('Please enter a variant value.'); return; }

    var btn = this;
    $(btn).prop('disabled', true);

    $.post(SAVE_VAR, {
      product_id    : PRODUCT_ID,
      variant_id    : editId,
      variant_type  : type,
      variant_value : value,
      price_modifier: price,
      stock_qty     : stock,
      sku           : sku
    }, function (res) {
      if (res.success) {
        var modifier = parseFloat(price);
        var modStr   = (modifier >= 0 ? '+' : '') + '₹' + Math.abs(modifier).toFixed(2);
        var varId    = res.variant_id;

        if (editId != '0') {
          // Update existing row
          var $row = $('#vrow_' + editId);
          $row.find('td:eq(0)').html('<span class="label label-info">' + capFirst(type) + '</span>');
          $row.find('td:eq(1)').html('<strong>' + escHtml(value) + '</strong>');
          $row.find('td:eq(2)').text(modStr);
          $row.find('td:eq(3)').text(parseInt(stock));
          $row.find('td:eq(4)').text(sku || '—');
          $row.find('.edit-variant-btn')
            .data('type', type).data('value', value)
            .data('price', price).data('stock', stock).data('sku', sku);
          resetVariantForm();
        } else {
          // Append new row
          $('#noVariantsRow').remove();
          var rowHtml = '<tr id="vrow_' + varId + '" data-id="' + varId + '">'
            + '<td><span class="label label-info">' + capFirst(type) + '</span></td>'
            + '<td><strong>' + escHtml(value) + '</strong></td>'
            + '<td>' + modStr + '</td>'
            + '<td>' + parseInt(stock) + '</td>'
            + '<td>' + escHtml(sku || '—') + '</td>'
            + '<td>'
            + '<button class="btn btn-xs btn-primary edit-variant-btn" data-id="' + varId + '" data-type="' + escHtml(type) + '" data-value="' + escHtml(value) + '" data-price="' + price + '" data-stock="' + stock + '" data-sku="' + escHtml(sku) + '"><i class="fa fa-pencil"></i></button> '
            + '<button class="btn btn-xs btn-danger delete-variant-btn" data-id="' + varId + '"><i class="fa fa-trash"></i></button>'
            + '</td></tr>';
          $('#variantTbody').append(rowHtml);
          $('#variantBadge').text(parseInt($('#variantBadge').text()) + 1);
          resetVariantForm();
        }
      } else {
        alert(res.message || 'Save failed.');
      }
    }).fail(function () {
      alert('Request failed. Please try again.');
    }).always(function () { $(btn).prop('disabled', false); });
  });

  // ── Edit Variant ───────────────────────────────────────────

  $(document).on('click', '.edit-variant-btn', function () {
    var id    = $(this).data('id');
    var type  = $(this).data('type');
    var value = $(this).data('value');
    var price = $(this).data('price');
    var stock = $(this).data('stock');
    var sku   = $(this).data('sku');

    var knownTypes = ['size','color','pack'];
    var sel        = document.getElementById('variantTypeSelect');

    if (knownTypes.indexOf(type) >= 0) {
      sel.value = type;
      $('#variantTypeCustom').hide();
    } else {
      sel.value = 'custom';
      $('#variantTypeCustom').show().val(type);
    }

    $('#editVariantId').val(id);
    $('#variantValue').val(value);
    $('#variantPrice').val(price);
    $('#variantStock').val(stock);
    $('#variantSku').val(sku);
    $('#variantFormTitle').html('<i class="fa fa-pencil text-saffron"></i> Edit Variant');
    $('#saveVariantLabel').text('Update Variant');
    $('#cancelEditBtn').show();
    toggleColorPreview();
    $('html, body').animate({ scrollTop: $('#tab-variants').offset().top - 100 }, 300);
  });

  // ── Delete Variant ─────────────────────────────────────────

  $(document).on('click', '.delete-variant-btn', function () {
    if (!confirm('Delete this variant?')) return;
    var id  = $(this).data('id');
    var btn = this;
    $(btn).prop('disabled', true);

    $.get(DEL_VAR + id, function (res) {
      if (res.success) {
        $('#vrow_' + id).remove();
        var cnt = parseInt($('#variantBadge').text()) - 1;
        $('#variantBadge').text(cnt);
        if ($('#variantTbody tr').length === 0) {
          $('#variantTbody').append('<tr id="noVariantsRow"><td colspan="6" class="text-center text-muted">No variants yet.</td></tr>');
        }
        if ($('#editVariantId').val() == id) resetVariantForm();
      }
    }).fail(function () { $(btn).prop('disabled', false); });
  });

  // ── Cancel Edit ────────────────────────────────────────────

  $('#cancelEditBtn').on('click', resetVariantForm);

  function resetVariantForm() {
    $('#editVariantId').val('0');
    $('#variantTypeSelect').val('size');
    $('#variantTypeCustom').hide().val('');
    $('#variantValue').val('');
    $('#variantPrice').val('0');
    $('#variantStock').val('0');
    $('#variantSku').val('');
    $('#variantFormTitle').html('<i class="fa fa-plus-circle text-saffron"></i> Add Variant');
    $('#saveVariantLabel').text('Save Variant');
    $('#cancelEditBtn').hide();
    $('#colorSwatchPreview').hide();
  }

  // ── Helpers ────────────────────────────────────────────────

  function capFirst(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

  function escHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  // Tab persistence via hash
  var hash = window.location.hash;
  if (hash) { $('#productTabs a[href="' + hash + '"]').tab('show'); }
  $('#productTabs a').on('shown.bs.tab', function (e) {
    window.location.hash = $(e.target).attr('href');
  });

});
</script>
