<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
  <div class="col-md-8">
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-user-secret"></i> Admin &amp; Staff Users</h3>
        <div class="box-tools pull-right">
          <button class="btn btn-sm btn-saffron" data-toggle="modal" data-target="#roleModal" onclick="resetRoleModal()">
            <i class="fa fa-plus"></i> Add User
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
              <tr><th>Name</th><th>Email</th><th>Role</th><th>Permissions</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php foreach ($admins as $a): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($a['name']); ?></strong></td>
                <td><?php echo htmlspecialchars($a['email']); ?></td>
                <td>
                  <span class="label <?php echo $a['role']==='admin' ? 'label-danger' : 'label-warning'; ?>">
                    <?php echo ucfirst($a['role']); ?>
                  </span>
                </td>
                <td>
                  <small class="text-muted">
                    <?php echo $a['permissions'] ? htmlspecialchars($a['permissions']) : 'All (admin)'; ?>
                  </small>
                </td>
                <td>
                  <button class="btn btn-xs btn-primary" onclick='openEditRole(<?php echo json_encode($a); ?>)'>
                    <i class="fa fa-pencil"></i>
                  </button>
                  <?php if ($a['id'] != $this->session->userdata(SESS_HEAD.'_user_id')): ?>
                  <a href="<?php echo site_url('admin-roles'); ?>?delete=<?php echo $a['id']; ?>"
                     class="btn btn-xs btn-danger"
                     onclick="return confirm('Delete this admin/staff user?')">
                    <i class="fa fa-trash"></i>
                  </a>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($admins)): ?>
                <tr><td colspan="5" class="text-center text-muted">No admin users found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="box box-info">
      <div class="box-header with-border">
        <h3 class="box-title">About Roles</h3>
      </div>
      <div class="box-body">
        <dl>
          <dt><span class="label label-danger">Admin</span></dt>
          <dd class="text-muted margin-b-10">Full access to all admin features including managing other admin/staff users.</dd>
          <dt><span class="label label-warning">Staff</span></dt>
          <dd class="text-muted">Limited access. Can manage products, orders, and other day-to-day tasks. Cannot access Admin Roles.</dd>
        </dl>
        <div class="alert alert-warning margin-t-10">
          <i class="fa fa-exclamation-triangle"></i>
          Only Admin-role users can access this page.
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Role Modal -->
<div class="modal fade" id="roleModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="<?php echo site_url('admin-roles'); ?>">
        <input type="hidden" name="user_id" id="roleUserId" value="0">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="roleModalTitle">Add Admin/Staff User</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Full Name *</label>
            <input type="text" class="form-control" name="name" id="roleName" required>
          </div>
          <div class="form-group">
            <label>Email *</label>
            <input type="email" class="form-control" name="email" id="roleEmail" required>
          </div>
          <div class="form-group">
            <label>Password <small class="text-muted">(leave blank to keep existing)</small></label>
            <input type="text" class="form-control" name="password" id="rolePassword"
                   placeholder="Enter password">
          </div>
          <div class="form-group">
            <label>Role</label>
            <select class="form-control" name="role" id="roleType">
              <option value="staff">Staff</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="form-group">
            <label>Permissions <small class="text-muted">(for Staff role)</small></label>
            <select class="form-control" name="permissions[]" id="rolePerms" multiple size="5">
              <option value="products">Products</option>
              <option value="categories">Categories</option>
              <option value="orders">Orders</option>
              <option value="customers">Customers</option>
              <option value="reports">Reports</option>
              <option value="coupons">Coupons</option>
              <option value="banners">Banners</option>
              <option value="returns">Returns</option>
              <option value="contacts">Contacts</option>
            </select>
            <small class="text-muted">Hold Ctrl/Cmd to select multiple. Leave blank = all.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-saffron">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function resetRoleModal() {
  document.getElementById('roleModalTitle').textContent = 'Add Admin/Staff User';
  document.getElementById('roleUserId').value   = '0';
  document.getElementById('roleName').value     = '';
  document.getElementById('roleEmail').value    = '';
  document.getElementById('rolePassword').value = '';
  document.getElementById('roleType').value     = 'staff';
  Array.from(document.getElementById('rolePerms').options).forEach(function(o){ o.selected=false; });
}
function openEditRole(a) {
  document.getElementById('roleModalTitle').textContent = 'Edit User';
  document.getElementById('roleUserId').value   = a.id;
  document.getElementById('roleName').value     = a.name;
  document.getElementById('roleEmail').value    = a.email;
  document.getElementById('rolePassword').value = '';
  document.getElementById('roleType').value     = a.role;
  var perms = (a.permissions || '').split(',');
  Array.from(document.getElementById('rolePerms').options).forEach(function(o){
    o.selected = perms.indexOf(o.value) !== -1;
  });
  $('#roleModal').modal('show');
}
</script>
