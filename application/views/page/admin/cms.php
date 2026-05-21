<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title">CMS Pages</h3>
    <div class="box-tools pull-right">
      <button class="btn btn-sm btn-saffron" data-toggle="modal" data-target="#pageModal" onclick="resetPageModal()">
        <i class="fa fa-plus"></i> Add Page
      </button>
    </div>
  </div>
  <div class="box-body">

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><ul><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Filter bar -->
    <div class="row" style="margin-bottom:10px">
      <div class="col-sm-5">
        <div class="input-group input-group-sm">
          <span class="input-group-addon"><i class="fa fa-search"></i></span>
          <input type="text" class="form-control" id="cms_search" placeholder="Search title, slug…">
          <span class="input-group-btn">
            <button type="button" class="btn btn-default" id="cms_clear" title="Clear"><i class="fa fa-times"></i></button>
          </span>
        </div>
      </div>
      <div class="col-sm-2 col-xs-6">
        <select class="form-control input-sm" id="cms_fStatus">
          <option value="">All Status</option>
          <option value="published">Published</option>
          <option value="draft">Draft</option>
        </select>
      </div>
      <div class="col-sm-5" style="line-height:30px">
        <small class="text-muted" id="cms_count"></small>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover admin-table" id="cms_table">
        <thead>
          <tr><th>Title</th><th>Slug</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($pages as $pg): ?>
          <tr>
            <td><strong><?php echo htmlspecialchars($pg['title']); ?></strong></td>
            <td>
              <code><?php echo htmlspecialchars($pg['slug']); ?></code>
              <a href="<?php echo site_url('page/'.$pg['slug']); ?>" target="_blank" class="text-muted" style="margin-left:6px">
                <i class="fa fa-external-link"></i>
              </a>
            </td>
            <td>
              <span class="label <?php echo $pg['status'] ? 'label-success' : 'label-default'; ?>">
                <?php echo $pg['status'] ? 'Published' : 'Draft'; ?>
              </span>
            </td>
            <td>
              <button class="btn btn-xs btn-primary" onclick='openEditPage(<?php echo json_encode($pg); ?>)'>
                <i class="fa fa-pencil"></i>
              </button>
              <a href="<?php echo site_url('admin-cms'); ?>?action=delete&edit=<?php echo $pg['id']; ?>"
                 class="btn btn-xs btn-danger" onclick="return confirm('Delete this page?')">
                <i class="fa fa-trash"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($pages)): ?>
            <tr><td colspan="4" class="text-center text-muted">No CMS pages yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
(function () {
  var rows   = Array.from(document.querySelectorAll('#cms_table tbody tr'));
  var search = document.getElementById('cms_search');
  var count  = document.getElementById('cms_count');
  function run() {
    var q  = search.value.trim().toLowerCase();
    var fS = document.getElementById('cms_fStatus').value.toLowerCase();
    var n = 0;
    rows.forEach(function (r) {
      if (r.cells.length < 2) { r.style.display = ''; return; }
      var stat = r.cells[2] ? r.cells[2].textContent.trim().toLowerCase() : '';
      var ok = (!q || r.textContent.toLowerCase().indexOf(q) >= 0) && (!fS || stat.indexOf(fS) >= 0);
      r.style.display = ok ? '' : 'none';
      if (ok) n++;
    });
    count.textContent = n + ' / ' + rows.length + ' pages';
  }
  search.addEventListener('input', run);
  document.getElementById('cms_clear').addEventListener('click', function () { search.value = ''; run(); });
  document.getElementById('cms_fStatus').addEventListener('change', run);
})();
</script>

<!-- Page Modal -->
<div class="modal fade" id="pageModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" action="<?php echo site_url('admin-cms'); ?>">
        <input type="hidden" name="page_id" id="pageId" value="0">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="pageModalTitle">Add CMS Page</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label>Title *</label>
                <input type="text" class="form-control" name="title" id="pageTitle" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Slug (auto-generated if blank)</label>
                <input type="text" class="form-control" name="slug" id="pageSlug" placeholder="e.g. about-us">
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label>Content *</label>
                <!-- CKEditor replaces this textarea — do NOT use required attribute -->
                <textarea name="content" id="pageContent" rows="10"></textarea>
                <div id="pageContentError" class="text-danger small" style="display:none">
                  <i class="fa fa-exclamation-circle"></i> Content is required.
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Meta Title</label>
                <input type="text" class="form-control" name="meta_title" id="pageMetaTitle">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Status</label>
                <select class="form-control" name="status" id="pageStatus">
                  <option value="1">Published</option>
                  <option value="0">Draft</option>
                </select>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label>Meta Description</label>
                <textarea class="form-control" name="meta_desc" id="pageMetaDesc" rows="2"></textarea>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-saffron">Save Page</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function resetPageModal() {
  document.getElementById('pageModalTitle').textContent = 'Add CMS Page';
  document.getElementById('pageId').value        = '0';
  document.getElementById('pageTitle').value     = '';
  document.getElementById('pageSlug').value      = '';
  document.getElementById('pageContent').value   = '';
  document.getElementById('pageMetaTitle').value = '';
  document.getElementById('pageMetaDesc').value  = '';
  document.getElementById('pageStatus').value    = '1';
}
function openEditPage(pg) {
  document.getElementById('pageModalTitle').textContent = 'Edit Page';
  document.getElementById('pageId').value        = pg.id;
  document.getElementById('pageTitle').value     = pg.title;
  document.getElementById('pageSlug').value      = pg.slug;
  document.getElementById('pageContent').value   = pg.content;
  document.getElementById('pageMetaTitle').value = pg.meta_title || '';
  document.getElementById('pageMetaDesc').value  = pg.meta_desc || '';
  document.getElementById('pageStatus').value    = pg.status;
  $('#pageModal').modal('show');
}
</script>
