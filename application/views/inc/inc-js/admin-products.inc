<script>
$(function () {

  var pmSkuAuto = true; // tracks whether SKU field is in auto-generate mode

  /* ════════════════════════════════════════════════════════════
     PRODUCT MODAL — ADD mode
  ════════════════════════════════════════════════════════════ */
  $('#btnAddProduct').on('click', function () {
    resetProductModal();
    $('#productModal').modal('show');
  });

  /* ════════════════════════════════════════════════════════════
     PRODUCT MODAL — EDIT mode
  ════════════════════════════════════════════════════════════ */
  $(document).on('click', '.open-edit-modal', function () {
    var p = $(this).data('product');
    resetProductModal();

    $('#pmTitleIcon').removeClass('fa-plus-circle').addClass('fa-pencil');
    $('#pmTitleText').text('Edit Product');
    $('#pmSaveLabel').text('Update Product');
    $('#pmFooterNote').text('Gallery & Variant changes save instantly via AJAX.');

    if (p.product_code) {
      $('#pmProductCode').text(p.product_code).show();
    }

    $('#pmProductId').val(p.id);
    $('#pmExistingImage').val(p.image || '');
    $('#pmName').val(p.name);
    $('#pmDesc').val(p.description || '');
    $('#pmPrice').val(p.price);
    $('#pmOfferPrice').val(p.offer_price || '');
    $('#pmGst').val(p.gst || '0');
    $('#pmStock').val(p.stock_qty);
    $('#pmWeight').val(p.weight || '');
    $('#pmTags').val(p.tags || '');
    $('#pmFeatured').val(p.is_featured || '0');
    $('#pmStatus').val(p.status);
    $('#pmCatId').val(p.category_id);
    $('#pmBrandId').val(p.brand_id || '');
    $('#pmMetaTitle').val(p.meta_title || '');
    $('#pmMetaDesc').val(p.meta_desc || '');

    if (p.image) {
      $('#pmCoverThumb').attr('src', PM.uploadBase + p.image);
      $('#pmCoverThumbWrap').show();
    }

    $('#pmTabGallery, #pmTabVariants').show();
    $('#pmBasePrice').text('₹' + parseFloat(p.price).toFixed(2));
    $('#productModal').modal('show');

    loadPmGallery(p.id);
    loadPmVariants(p.id, parseFloat(p.price) || 0);
  });

  /* ════════════════════════════════════════════════════════════
     RESET modal to add-mode defaults
  ════════════════════════════════════════════════════════════ */
  function resetProductModal() {
    $('#pmTabs a[href="#pm-basic"]').tab('show');
    $('#pmTitleIcon').removeClass('fa-pencil').addClass('fa-plus-circle');
    $('#pmTitleText').text('Add New Product');
    $('#pmProductCode').text('').hide();
    $('#pmSaveLabel').text('Save Product');
    $('#pmFooterNote').text('');
    document.getElementById('pmForm').reset();
    $('#pmProductId').val('0');
    $('#pmExistingImage').val('');
    $('#pmCoverThumbWrap').hide();
    $('#pmCoverThumb').attr('src', '');
    $('#pmTabGallery, #pmTabVariants').hide();
    $('#pmGalleryBadge').text('0');
    $('#pmVariantBadge').text('0');
    $('#pmGalleryGrid').empty();
    $('#pmGalleryEmpty').hide();
    $('#pmUploadStatus').text('');
    $('#pmVariantTbody').empty();
    resetPmVarForm();
  }

  $('#productModal').on('hidden.bs.modal', resetProductModal);

  /* ════════════════════════════════════════════════════════════
     TAB 2 : GALLERY
  ════════════════════════════════════════════════════════════ */
  function loadPmGallery(pid) {
    $('#pmGallerySpinner').show();
    $('#pmGalleryGrid').empty();
    $('#pmGalleryEmpty').hide();
    $.getJSON(PM.urlGallery + pid, function (res) {
      $('#pmGallerySpinner').hide();
      if (res.success) {
        renderGalleryGrid(res.images);
        $('#pmGalleryBadge').text(res.images.length);
      }
    }).fail(function () {
      $('#pmGallerySpinner').hide();
      $('#pmGalleryGrid').html('<div class="col-md-12"><p class="text-danger text-center">Failed to load images.</p></div>');
    });
  }

  function renderGalleryGrid(images) {
    var grid = $('#pmGalleryGrid');
    grid.empty();
    if (!images || images.length === 0) { $('#pmGalleryEmpty').show(); return; }
    $('#pmGalleryEmpty').hide();
    $.each(images, function (i, img) { grid.append(buildGalleryItem(img)); });
  }

  function buildGalleryItem(img) {
    var primaryBadge  = img.is_primary == 1 ? '<span class="label label-success gi-primary">Primary</span>' : '';
    var setPrimaryBtn = img.is_primary == 1 ? '' :
      '<button class="btn btn-xs btn-primary pm-set-primary" data-id="'+img.id+'"><i class="fa fa-star"></i></button> ';
    return '<div class="col-xs-6 col-md-3 pm-gallery-item" id="pmgi_'+img.id+'">' +
      '<img src="'+img.url+'" alt="">' + primaryBadge +
      '<div class="gi-actions">' + setPrimaryBtn +
      '<button class="btn btn-xs btn-danger pm-del-img" data-id="'+img.id+'"><i class="fa fa-trash"></i></button>' +
      '</div></div>';
  }

  $('#pmUploadImagesBtn').on('click', function () {
    var files = document.getElementById('pmGalleryFileInput').files;
    var pid   = $('#pmProductId').val();
    if (!files.length) { alert('Select at least one image.'); return; }
    if (!pid || pid == '0') { alert('Save the product first before uploading gallery images.'); return; }
    var fd = new FormData();
    fd.append('product_id', pid);
    $.each(files, function (i, f) { fd.append('images[]', f); });
    var btn = this; $(btn).prop('disabled', true);
    $('#pmUploadStatus').text('Uploading…');
    $.ajax({ url: PM.urlImgUpload, type: 'POST', data: fd, processData: false, contentType: false,
      success: function (res) {
        if (res.success) {
          $('#pmGalleryEmpty').hide();
          $.each(res.uploaded, function (i, img) { $('#pmGalleryGrid').append(buildGalleryItem(img)); });
          $('#pmGalleryBadge').text(parseInt($('#pmGalleryBadge').text()) + res.uploaded.length);
          $('#pmUploadStatus').css('color', res.errors && res.errors.length ? '#a94442' : '#3c763d')
            .text(res.errors && res.errors.length ? res.errors.join('; ') : res.uploaded.length + ' uploaded.');
          document.getElementById('pmGalleryFileInput').value = '';
        } else { $('#pmUploadStatus').css('color','#a94442').text(res.message); }
      },
      error: function () { $('#pmUploadStatus').css('color','#a94442').text('Upload failed.'); },
      complete: function () { $(btn).prop('disabled', false); }
    });
  });

  $(document).on('click', '.pm-set-primary', function () {
    var id = $(this).data('id');
    $.getJSON(PM.urlImgPrimary + id, function (res) {
      if (!res.success) return;
      $('.pm-gallery-item').each(function () {
        var iid = this.id.replace('pmgi_', '');
        $(this).find('.label-success.gi-primary').remove();
        if (!$(this).find('.pm-set-primary').length) {
          $(this).find('.gi-actions').prepend('<button class="btn btn-xs btn-primary pm-set-primary" data-id="'+iid+'"><i class="fa fa-star"></i></button> ');
        }
      });
      var $item = $('#pmgi_' + id);
      $item.prepend('<span class="label label-success gi-primary">Primary</span>');
      $item.find('.pm-set-primary').remove();
    });
  });

  $(document).on('click', '.pm-del-img', function () {
    if (!confirm('Delete this image?')) return;
    var id = $(this).data('id'), btn = this;
    $(btn).prop('disabled', true);
    $.getJSON(PM.urlImgDel + id, function (res) {
      if (res.success) {
        $('#pmgi_' + id).fadeOut(150, function () {
          $(this).remove();
          $('#pmGalleryBadge').text(Math.max(0, parseInt($('#pmGalleryBadge').text()) - 1));
          if ($('.pm-gallery-item').length === 0) $('#pmGalleryEmpty').show();
        });
      } else { $(btn).prop('disabled', false); }
    }).fail(function () { $(btn).prop('disabled', false); });
  });

  /* ════════════════════════════════════════════════════════════
     TAB 3 : VARIANTS
  ════════════════════════════════════════════════════════════ */
  function loadPmVariants(pid, basePrice) {
    $('#pmVariantsSpinner').show();
    $('#pmVariantTbody').html('<tr><td colspan="9" class="text-center"><i class="fa fa-spinner fa-spin"></i></td></tr>');
    $.getJSON(PM.urlVars + pid, function (res) {
      $('#pmVariantsSpinner').hide();
      if (res.success) { renderPmVariants(res.variants, basePrice); $('#pmVariantBadge').text(res.variants.length); }
    }).fail(function () {
      $('#pmVariantsSpinner').hide();
      $('#pmVariantTbody').html('<tr><td colspan="9" class="text-center text-danger">Failed to load.</td></tr>');
    });
  }

  function renderPmVariants(variants, basePrice) {
    var tbody = $('#pmVariantTbody');
    tbody.empty();
    if (!variants || variants.length === 0) {
      tbody.html('<tr><td colspan="9" class="text-center text-muted">No variants yet — add one below.</td></tr>');
      return;
    }
    $.each(variants, function (i, v) {
      var mod      = parseFloat(v.price_modifier) || 0;
      var final    = basePrice + mod;
      var modStr   = (mod >= 0 ? '+' : '') + '₹' + Math.abs(mod).toFixed(2);
      var modCls   = mod > 0 ? 'text-success' : (mod < 0 ? 'text-danger' : 'text-muted');
      var colorCell = (v.variant_type === 'color' && v.color_hex)
        ? '<span style="display:inline-block;width:18px;height:18px;border-radius:50%;background:'+escHtml(v.color_hex)+';border:1px solid #ccc;vertical-align:middle;margin-right:4px"></span>'
        : '';
      tbody.append(
        '<tr id="pmvr_'+v.id+'">' +
        '<td class="text-muted">'+(i+1)+'</td>' +
        '<td>'+typeBadge(v.variant_type)+'</td>' +
        '<td>'+colorCell+'<strong>'+escHtml(v.variant_value)+'</strong></td>' +
        '<td class="'+modCls+'">'+modStr+'</td>' +
        '<td class="text-saffron"><strong>₹'+final.toFixed(2)+'</strong></td>' +
        '<td>'+parseInt(v.stock_qty)+'</td>' +
        '<td><small>'+escHtml(v.sku||'—')+'</small></td>' +
        '<td>'+(v.color_hex ? '<span class="label" style="background:'+escHtml(v.color_hex)+'">'+escHtml(v.color_hex)+'</span>' : '—')+'</td>' +
        '<td style="white-space:nowrap">' +
          '<button class="btn btn-xs btn-primary pm-var-edit" '+
            'data-id="'+v.id+'" data-type="'+escAttr(v.variant_type)+'" data-value="'+escAttr(v.variant_value)+'" '+
            'data-price="'+v.price_modifier+'" data-stock="'+v.stock_qty+'" data-sku="'+escAttr(v.sku||'')+'" '+
            'data-color="'+escAttr(v.color_hex||'')+'"><i class="fa fa-pencil"></i></button> '+
          '<button class="btn btn-xs btn-danger pm-var-del" data-id="'+v.id+'"><i class="fa fa-trash"></i></button>' +
        '</td></tr>'
      );
    });
  }

  /* ── SKU auto-generation ── */
  function generateSku(type, value) {
    var abbr = { size: 'SZ', weight: 'WT', color: 'CL', pack: 'PK' };
    var t = abbr[type] || String(type).substring(0, 3).toUpperCase().replace(/[^A-Z0-9]/g, '');
    var v = String(value).toUpperCase().replace(/[^A-Z0-9]+/g, '-').replace(/^-+|-+$/g, '').substring(0, 12);
    return t + (v ? '-' + v : '');
  }

  function pmAutoSku() {
    if (!pmSkuAuto) return;
    var type  = $('#pmVarType').val() === 'custom' ? $.trim($('#pmVarCustomType').val()) : $('#pmVarType').val();
    var value = $.trim($('#pmVarValue').val());
    if (type && value) $('#pmVarSku').val(generateSku(type, value));
  }

  $('#pmVarType').on('change', function () {
    var val = $(this).val();
    if (val === 'custom') { $('#pmVarCustomType').show().focus(); }
    else                  { $('#pmVarCustomType').hide().val(''); }
    togglePmColorPicker(val);
    pmAutoSku();
  });

  $('#pmVarCustomType').on('input', pmAutoSku);
  $('#pmVarValue').on('input',      pmAutoSku);

  $('#pmVarSku').on('input', function () {
    pmSkuAuto = false;
  });

  $('#pmVarSkuGenBtn').on('click', function () {
    pmSkuAuto = true;
    pmAutoSku();
  });

  function togglePmColorPicker(type) {
    if (type === 'color') { $('#pmVarColorRow').show(); } else { $('#pmVarColorRow').hide(); }
  }

  /* Sync color picker ↔ hex text ↔ swatch */
  $('#pmVarColorPicker').on('input', function () {
    $('#pmVarColorHex').val(this.value);
    $('#pmVarColorSwatch').css('background', this.value);
  });
  $('#pmVarColorHex').on('input', function () {
    var hex = this.value;
    if (/^#[0-9a-fA-F]{6}$/.test(hex)) {
      $('#pmVarColorPicker').val(hex);
      $('#pmVarColorSwatch').css('background', hex);
    }
  });

  /* ── Live final price preview ── */
  $('#pmVarPrice').on('input', function () {
    var base = parseFloat($('#pmBasePrice').text().replace('₹','')) || 0;
    $('#pmVarFinal').text('₹' + (base + (parseFloat($(this).val()) || 0)).toFixed(2));
  });

  /* ── Save / Update variant ── */
  $('#pmVarSaveBtn').on('click', function () {
    var pid    = $('#pmProductId').val();
    var editId = $('#pmVarEditId').val();
    var typeRaw= $('#pmVarType').val();
    var type   = typeRaw === 'custom' ? $.trim($('#pmVarCustomType').val()) : typeRaw;
    var value  = $.trim($('#pmVarValue').val());
    var hex    = type === 'color' ? $.trim($('#pmVarColorHex').val()) : '';
    if (!type)  { pmVarMsg('Type is required.','danger'); return; }
    if (!value) { pmVarMsg('Value is required.','danger'); return; }
    var btn = this; $(btn).prop('disabled', true); pmVarMsg('Saving…','info');
    $.post(PM.urlVarSave, {
      product_id: pid, variant_id: editId,
      variant_type: type, variant_value: value,
      price_modifier: $('#pmVarPrice').val(),
      stock_qty: $('#pmVarStock').val(),
      sku: $.trim($('#pmVarSku').val()),
      color_hex: hex
    }, function (res) {
      if (res.success) {
        var base = parseFloat($('#pmBasePrice').text().replace('₹','')) || 0;
        $.getJSON(PM.urlVars + pid, function (data) {
          if (data.success) { renderPmVariants(data.variants, base); $('#pmVariantBadge').text(data.variants.length); }
        });
        pmVarMsg(editId!='0' ? 'Updated!' : 'Added!', 'success'); resetPmVarForm();
      } else { pmVarMsg(res.message||'Save failed.','danger'); }
    }).fail(function () { pmVarMsg('Request failed.','danger'); })
      .always(function () { $(btn).prop('disabled', false); });
  });

  /* ── Edit variant → fill form ── */
  $(document).on('click', '.pm-var-edit', function () {
    var id    = $(this).data('id');
    var type  = $(this).data('type');
    var known = ['size','weight','color','pack'];
    if (known.indexOf(type) >= 0) { $('#pmVarType').val(type); $('#pmVarCustomType').hide().val(''); }
    else { $('#pmVarType').val('custom'); $('#pmVarCustomType').show().val(type); }
    togglePmColorPicker(type);
    if (type === 'color') {
      var hex = $(this).data('color') || '#7B4228';
      $('#pmVarColorPicker').val(hex);
      $('#pmVarColorHex').val(hex);
      $('#pmVarColorSwatch').css('background', hex);
    }
    var existingSku = $(this).data('sku') || '';
    $('#pmVarEditId').val(id);
    $('#pmVarValue').val($(this).data('value'));
    $('#pmVarPrice').val($(this).data('price'));
    $('#pmVarStock').val($(this).data('stock'));
    $('#pmVarSku').val(existingSku); pmSkuAuto = !existingSku;
    $('#pmVarFormTitle').text('Edit Variant');
    $('#pmVarFormIcon').removeClass('fa-plus-circle').addClass('fa-pencil');
    $('#pmVarSaveLabel').text('Update Variant');
    $('#pmVarCancelBtn').show();
    $('#pmVarPrice').trigger('input');
    $('#pm-variants').closest('.modal-body').animate({ scrollTop: $('.pm-var-form').position().top - 20 }, 200);
  });

  /* ── Delete variant ── */
  $(document).on('click', '.pm-var-del', function () {
    if (!confirm('Delete this variant?')) return;
    var id  = $(this).data('id'); var btn = this; $(btn).prop('disabled', true);
    $.getJSON(PM.urlVarDel + id, function (res) {
      if (res.success) {
        $('#pmvr_'+id).fadeOut(150, function () {
          $(this).remove();
          $('#pmVariantTbody tr').each(function(i){ $(this).find('td:first').text(i+1); });
          $('#pmVariantBadge').text(Math.max(0, parseInt($('#pmVariantBadge').text())-1));
          if ($('#pmVariantTbody tr').length === 0)
            $('#pmVariantTbody').html('<tr><td colspan="9" class="text-center text-muted">No variants yet.</td></tr>');
        });
        if ($('#pmVarEditId').val() == id) resetPmVarForm();
      } else { $(btn).prop('disabled', false); }
    }).fail(function () { $(btn).prop('disabled', false); });
  });

  $('#pmVarCancelBtn').on('click', resetPmVarForm);

  function resetPmVarForm() {
    $('#pmVarEditId').val('0'); $('#pmVarType').val('size');
    $('#pmVarCustomType').hide().val('');
    $('#pmVarColorRow').hide();
    $('#pmVarColorPicker').val('#7B4228'); $('#pmVarColorHex').val('#7B4228');
    $('#pmVarColorSwatch').css('background','#7B4228');
    $('#pmVarValue').val(''); $('#pmVarPrice').val('0');
    $('#pmVarStock').val('0');
    $('#pmVarSku').val(''); pmSkuAuto = true;
    $('#pmVarFormTitle').text('Add Variant');
    $('#pmVarFormIcon').removeClass('fa-pencil').addClass('fa-plus-circle');
    $('#pmVarSaveLabel').text('Add Variant');
    $('#pmVarCancelBtn').hide(); $('#pmVarMsg').text('');
    $('#pmVarFinal').text('—');
  }

  function pmVarMsg(msg, type) {
    var c = { success:'#3c763d', danger:'#a94442', info:'#31708f' };
    $('#pmVarMsg').css('color', c[type]||'#333').text(msg);
    if (type==='success') setTimeout(function(){ $('#pmVarMsg').text(''); }, 3000);
  }

  /* ════════════════════════════════════════════════════════════
     QUICK SIZE MODAL (Sizes button in table)
  ════════════════════════════════════════════════════════════ */
  $(document).on('click', '.open-size-modal', function () {
    var pid    = $(this).data('id');
    var pname  = $(this).data('name');
    var pprice = parseFloat($(this).data('price')) || 0;
    $('#sizeModalProductId').val(pid);
    $('#sizeModalProductName').text(pname);
    $('#sizeModalBasePrice').text('₹' + pprice.toFixed(2));
    $('#sizeModalFullEditLink').attr('href', PM.urlVarDel.replace('admin-variant-delete/', 'admin-product/') + pid);
    resetSizeForm();
    $('#sizeLoadingSpinner').show(); $('#sizeVariantWrap').hide();
    $('#sizeModal').modal('show');
    $.getJSON(PM.urlVars + pid, function (res) {
      $('#sizeLoadingSpinner').hide();
      if (res.success) { renderSizeTable(res.variants, pprice); $('#sizeVariantWrap').show(); }
    }).fail(function () {
      $('#sizeLoadingSpinner').hide(); $('#sizeVariantWrap').show();
      $('#sizeVariantTbody').html('<tr><td colspan="9" class="text-center text-danger">Failed to load.</td></tr>');
    });
  });

  function renderSizeTable(variants, basePrice) {
    var tbody = $('#sizeVariantTbody');
    tbody.empty();
    if (!variants || variants.length === 0) {
      tbody.html('<tr><td colspan="9" class="text-center text-muted">No variants yet.</td></tr>'); return;
    }
    $.each(variants, function (i, v) {
      var mod    = parseFloat(v.price_modifier) || 0;
      var final  = basePrice + mod;
      var modStr = (mod >= 0 ? '+' : '') + '₹' + Math.abs(mod).toFixed(2);
      var modCls = mod > 0 ? 'text-success' : (mod < 0 ? 'text-danger' : 'text-muted');
      var swatch = (v.variant_type === 'color' && v.color_hex)
        ? '<span style="display:inline-block;width:16px;height:16px;border-radius:50%;background:'+escHtml(v.color_hex)+';border:1px solid #ccc;vertical-align:middle;margin-right:3px"></span>'
        : '';
      tbody.append(
        '<tr id="svrow_'+v.id+'">' +
        '<td class="text-muted">'+(i+1)+'</td>' +
        '<td>'+typeBadge(v.variant_type)+'</td>' +
        '<td>'+swatch+'<strong>'+escHtml(v.variant_value)+'</strong></td>' +
        '<td class="'+modCls+'">'+modStr+'</td>' +
        '<td class="text-saffron"><strong>₹'+final.toFixed(2)+'</strong></td>' +
        '<td>'+parseInt(v.stock_qty)+'</td>' +
        '<td><small>'+escHtml(v.sku||'—')+'</small></td>' +
        '<td>'+(v.color_hex ? '<span style="display:inline-block;width:20px;height:20px;background:'+escHtml(v.color_hex)+';border-radius:4px;border:1px solid #ccc"></span> '+escHtml(v.color_hex) : '—')+'</td>' +
        '<td style="white-space:nowrap">' +
          '<button class="btn btn-xs btn-primary sv-edit-btn" '+
            'data-id="'+v.id+'" data-type="'+escAttr(v.variant_type)+'" data-value="'+escAttr(v.variant_value)+'" '+
            'data-price="'+v.price_modifier+'" data-stock="'+v.stock_qty+'" data-sku="'+escAttr(v.sku||'')+'" '+
            'data-color="'+escAttr(v.color_hex||'')+'"><i class="fa fa-pencil"></i></button> '+
          '<button class="btn btn-xs btn-danger sv-del-btn" data-id="'+v.id+'"><i class="fa fa-trash"></i></button>' +
        '</td></tr>'
      );
    });
  }

  /* ── Size modal type → show/hide color picker ── */
  $('#sizeType').on('change', function () {
    var val = $(this).val();
    if (val === 'custom') { $('#sizeCustomTypeWrap').show(); $('#sizeCustomType').focus(); }
    else                  { $('#sizeCustomTypeWrap').hide(); $('#sizeCustomType').val(''); }
    toggleSizeColorPicker(val);
  });

  function toggleSizeColorPicker(type) {
    $('#sizeColorRow').toggle(type === 'color');
  }

  $('#sizeColorPicker').on('input', function () {
    $('#sizeColorHex').val(this.value);
    $('#sizeColorSwatch').css('background', this.value);
  });
  $('#sizeColorHex').on('input', function () {
    var hex = this.value;
    if (/^#[0-9a-fA-F]{6}$/.test(hex)) {
      $('#sizeColorPicker').val(hex); $('#sizeColorSwatch').css('background', hex);
    }
  });

  $('#sizePriceMod').on('input', function () {
    var base = parseFloat($('#sizeModalBasePrice').text().replace('₹','')) || 0;
    $('#sizeFinalPreview').text('₹' + (base + (parseFloat($(this).val())||0)).toFixed(2));
  });

  /* ── Size modal save ── */
  $('#sizeSubmitBtn').on('click', function () {
    var pid    = $('#sizeModalProductId').val();
    var editId = $('#sizeEditId').val();
    var typeRaw= $('#sizeType').val();
    var type   = typeRaw === 'custom' ? $.trim($('#sizeCustomType').val()) : typeRaw;
    var value  = $.trim($('#sizeValue').val());
    var hex    = type === 'color' ? $.trim($('#sizeColorHex').val()) : '';
    if (!type)  { flashMsg('#sizeSaveMsg','Type required.','danger'); return; }
    if (!value) { flashMsg('#sizeSaveMsg','Value required.','danger'); return; }
    var btn = this; $(btn).prop('disabled', true); flashMsg('#sizeSaveMsg','Saving…','info');
    $.post(PM.urlVarSave, {
      product_id: pid, variant_id: editId,
      variant_type: type, variant_value: value,
      price_modifier: $('#sizePriceMod').val(),
      stock_qty: $('#sizeStock').val(), sku: $.trim($('#sizeSku').val()),
      color_hex: hex
    }, function (res) {
      if (res.success) {
        var base = parseFloat($('#sizeModalBasePrice').text().replace('₹','')) || 0;
        $.getJSON(PM.urlVars + pid, function(data){ if(data.success) renderSizeTable(data.variants, base); });
        flashMsg('#sizeSaveMsg', editId!='0'?'Updated!':'Added!','success');
        resetSizeForm();
      } else { flashMsg('#sizeSaveMsg', res.message||'Failed.','danger'); }
    }).fail(function(){ flashMsg('#sizeSaveMsg','Request failed.','danger'); })
      .always(function(){ $(btn).prop('disabled',false); });
  });

  /* ── Size modal edit row ── */
  $(document).on('click', '.sv-edit-btn', function () {
    var id   = $(this).data('id');
    var type = $(this).data('type');
    var known= ['size','weight','pack','color'];
    if (known.indexOf(type)>=0) { $('#sizeType').val(type); $('#sizeCustomTypeWrap').hide(); }
    else { $('#sizeType').val('custom'); $('#sizeCustomTypeWrap').show(); $('#sizeCustomType').val(type); }
    toggleSizeColorPicker(type);
    if (type === 'color') {
      var hex = $(this).data('color') || '#7B4228';
      $('#sizeColorPicker').val(hex); $('#sizeColorHex').val(hex); $('#sizeColorSwatch').css('background', hex);
    }
    $('#sizeEditId').val(id);
    $('#sizeValue').val($(this).data('value'));
    $('#sizePriceMod').val($(this).data('price'));
    $('#sizeStock').val($(this).data('stock'));
    $('#sizeSku').val($(this).data('sku'));
    $('#sizeFormHeading').text('Edit Variant');
    $('#sizeFormIcon').removeClass('fa-plus-circle').addClass('fa-pencil');
    $('#sizeSubmitLabel').text('Update Variant');
    $('#sizeCancelEditBtn').show();
    $('#sizePriceMod').trigger('input');
  });

  $(document).on('click', '.sv-del-btn', function () {
    if (!confirm('Delete this variant?')) return;
    var id = $(this).data('id'); var btn = this; $(btn).prop('disabled',true);
    $.getJSON(PM.urlVarDel + id, function(res){
      if (res.success) {
        $('#svrow_'+id).fadeOut(150, function(){
          $(this).remove();
          $('#sizeVariantTbody tr').each(function(i){ $(this).find('td:first').text(i+1); });
          if($('#sizeVariantTbody tr').length===0)
            $('#sizeVariantTbody').html('<tr><td colspan="9" class="text-center text-muted">No variants yet.</td></tr>');
        });
        if ($('#sizeEditId').val()==id) resetSizeForm();
      } else { $(btn).prop('disabled',false); }
    }).fail(function(){ $(btn).prop('disabled',false); });
  });

  $('#sizeCancelEditBtn').on('click', resetSizeForm);

  function resetSizeForm() {
    $('#sizeEditId').val('0'); $('#sizeType').val('size');
    $('#sizeCustomTypeWrap').hide(); $('#sizeCustomType').val('');
    $('#sizeColorRow').hide();
    $('#sizeColorPicker').val('#7B4228'); $('#sizeColorHex').val('#7B4228'); $('#sizeColorSwatch').css('background','#7B4228');
    $('#sizeValue').val(''); $('#sizePriceMod').val('0');
    $('#sizeStock').val('0'); $('#sizeSku').val('');
    $('#sizeFormHeading').text('Add New Variant');
    $('#sizeFormIcon').removeClass('fa-pencil').addClass('fa-plus-circle');
    $('#sizeSubmitLabel').text('Add Variant');
    $('#sizeCancelEditBtn').hide(); $('#sizeSaveMsg').text('');
    $('#sizeFinalPreview').text('—');
  }

  /* ════════════════════════════════════════════════════════════
     SHARED HELPERS
  ════════════════════════════════════════════════════════════ */
  function typeBadge(t) {
    var map = { size:'label-info', weight:'label-primary', pack:'label-default', color:'label-success' };
    return '<span class="label '+(map[t]||'label-warning')+'">'+capFirst(t)+'</span>';
  }
  function flashMsg(sel,msg,type) {
    var c={success:'#3c763d',danger:'#a94442',info:'#31708f'};
    $(sel).css('color',c[type]||'#333').text(msg);
    if(type==='success') setTimeout(function(){$(sel).text('');},3000);
  }
  function capFirst(s)  { return String(s).charAt(0).toUpperCase()+String(s).slice(1); }
  function escHtml(s)   { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
  function escAttr(s)   { return String(s).replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }

});
</script>
