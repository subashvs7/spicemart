<!-- CKEditor 4 — works natively with AdminLTE / Bootstrap 3 / jQuery -->
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>

<style>
/* ── CKEditor 4 + AdminLTE / Bootstrap 3 modal ────────────── */

/* Allow editor dropdowns / dialogs to overflow the modal bounds */
#pageModal .modal-body   { overflow: visible !important; }
#pageModal .modal-dialog { overflow: visible !important; }

/* Make the editable area a good height */
.cke_editable { min-height: 280px; }

/* Give the whole editor a slight AdminLTE-style shadow */
.cke { border-radius: 4px; border: 1px solid #d2d6de !important; }
.cke_top   { border-radius: 4px 4px 0 0; background: #f4f4f4 !important; border-bottom: 1px solid #d2d6de !important; }
.cke_bottom{ border-radius: 0 0 4px 4px; background: #f4f4f4 !important; border-top : 1px solid #d2d6de !important; }

/* Saffron focus ring */
.cke_focus { box-shadow: 0 0 0 3px rgba(255,107,53,.2) !important; }
</style>

<script>
$(function () {

  /* ─── Fix Bootstrap 3 modal stealing CKEditor dialog focus ─── */
  $.fn.modal.Constructor.prototype.enforceFocus = function () {};

  /* ─── CKEditor 4 toolbar config ──────────────────────────────── */
  var CK_CONFIG = {
    customConfig : '',          /* skip external config file */
    height       : 360,
    resize_enabled : true,
    allowedContent : true,      /* preserve all HTML as-is  */
    entities       : false,
    forcePasteAsPlainText : false,
    format_tags    : 'p;h2;h3;h4;pre',

    toolbar : [
      /* Row 1 */
      { name: 'document',  items: ['Source', '-', 'Preview', 'Maximize'] },
      { name: 'clipboard', items: ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo'] },
      '/',
      /* Row 2 */
      { name: 'styles',    items: ['Format'] },
      { name: 'basicstyles', items: ['Bold','Italic','Underline','Strike','-','RemoveFormat'] },
      { name: 'colors',    items: ['TextColor','BGColor'] },
      '/',
      /* Row 3 */
      { name: 'paragraph', items: ['NumberedList','BulletedList','-','Outdent','Indent','-',
                                    'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-',
                                    'Blockquote','CreateDiv'] },
      '/',
      /* Row 4 */
      { name: 'links',     items: ['Link','Unlink','Anchor'] },
      { name: 'insert',    items: ['Image','Table','HorizontalRule','SpecialChar'] },
    ],

    /* Match AdminLTE body font */
    contentsCss : [
      'https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600&display=swap',
      'body { font-family:"Source Sans Pro",sans-serif; font-size:14px; color:#333; line-height:1.7; padding:10px 18px; }'
    ],

    /* Dialog z-index above Bootstrap modal (1040) */
    dialog_backgroundCoverOpacity : 0.5,
  };

  /* ─── Shared instance reference ──────────────────────────────── */
  var editorId = 'pageContent';

  function getEditor () {
    return CKEDITOR.instances[editorId] || null;
  }

  function destroyEditor () {
    var ed = getEditor();
    if (ed) ed.destroy(true);
  }

  function createEditor (initialContent) {
    destroyEditor();                          /* always clean slate    */
    var ed = CKEDITOR.replace(editorId, CK_CONFIG);
    ed.on('instanceReady', function () {
      ed.setData(initialContent || '');
    });
    return ed;
  }

  /* ─── Bootstrap 3 modal lifecycle ────────────────────────────── */

  /* SHOWN  →  create editor, populate from hidden textarea */
  $('#pageModal').on('shown.bs.modal', function () {
    var content = document.getElementById(editorId).value || '';
    createEditor(content);
  });

  /* HIDDEN  →  destroy editor to avoid stale DOM elements */
  $('#pageModal').on('hidden.bs.modal', function () {
    destroyEditor();
  });

  /* SUBMIT  →  push editor HTML back into textarea, validate */
  $('#pageModal form').on('submit', function (e) {
    var ed   = getEditor();
    var data = ed ? ed.getData() : document.getElementById(editorId).value;

    if (!$.trim(data)) {
      e.preventDefault();
      $('#pageContentError').show();
      if (ed) ed.focus();
      return false;
    }

    $('#pageContentError').hide();
    document.getElementById(editorId).value = data;
  });

  /* Hide content-required hint as soon as user types */
  CKEDITOR.on('instanceCreated', function (evt) {
    evt.editor.on('change', function () {
      $('#pageContentError').hide();
    });
  });

  /* ─── Extend inline functions from cms.php ─────────────────────
     resetPageModal()  just clears the textarea (defined inline).
     openEditPage(pg)  sets textarea.value = pg.content (defined inline).
     Both are fine — the 'shown.bs.modal' handler above reads the
     textarea value and passes it to CKEditor each time.              */

});
</script>
