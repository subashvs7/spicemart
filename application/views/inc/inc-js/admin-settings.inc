<script>
$(function () {

  /* Persist active tab in URL hash */
  var hash = window.location.hash;
  if (hash) $('#settingsTabs a[href="' + hash + '"]').tab('show');
  $('#settingsTabs a').on('shown.bs.tab', function (e) {
    window.location.hash = $(e.target).attr('href');
  });

  /* Logo file preview */
  $('#logoFileInput').on('change', function () {
    var file = this.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (e) {
      var prev = document.getElementById('logoPreview');
      if (prev.tagName === 'IMG') {
        prev.src = e.target.result;
      } else {
        prev.outerHTML = '<img id="logoPreview" src="' + e.target.result +
          '" style="max-height:60px;max-width:100%;border-radius:6px;border:1px solid #ddd">';
      }
    };
    reader.readAsDataURL(file);
  });

  /* SEO character counters */
  var metaTitleInput = $('input[name="meta_title"]');
  var metaDescInput  = $('#metaDescField');

  metaTitleInput.on('input', function () {
    var len = this.value.length;
    $('#metaTitleCount').text(len).css('color', len > 60 ? '#c0392b' : '#3c763d');
  });

  metaDescInput.on('input', function () {
    var len = this.value.length;
    $('#metaDescCount').text(len).css('color', len > 155 ? '#c0392b' : '#3c763d');
  });

});
</script>
