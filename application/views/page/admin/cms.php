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

    <div class="table-responsive">
      <table class="table table-bordered table-hover admin-table">
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
                <textarea class="form-control" name="content" id="pageContent" rows="10" required></textarea>
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
