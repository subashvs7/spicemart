<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php
$s          = $stats;
$total      = (int)($s['total_syncs']         ?? 0);
$succ       = (int)($s['total_success']        ?? 0);
$fail       = (int)($s['total_failed']         ?? 0);
$part       = (int)($s['total_partial']        ?? 0);
$tot_upd    = (int)($s['total_records_updated']?? 0);
$today      = (int)($s['today_syncs']          ?? 0);
$last_sync  = $s['last_sync_at'] ?? null;
$succ_rate  = $total > 0 ? round($succ / $total * 100) : 0;
?>

<!-- ── Tab navigation ─────────────────────────────────────────── -->
<div class="nav-tabs-custom">
  <ul class="nav nav-tabs">
    <li <?php echo $tab==='overview' ?'class="active"':''; ?>>
      <a href="<?php echo site_url('admin-pos'); ?>?tab=overview"><i class="fa fa-dashboard"></i> Overview</a>
    </li>
    <li <?php echo $tab==='keys' ?'class="active"':''; ?>>
      <a href="<?php echo site_url('admin-pos'); ?>?tab=keys"><i class="fa fa-key"></i> API Keys</a>
    </li>
    <li <?php echo $tab==='logs' ?'class="active"':''; ?>>
      <a href="<?php echo site_url('admin-pos'); ?>?tab=logs"><i class="fa fa-list"></i> Sync Logs</a>
    </li>
    <li <?php echo $tab==='manual' ?'class="active"':''; ?>>
      <a href="<?php echo site_url('admin-pos'); ?>?tab=manual"><i class="fa fa-pencil"></i> Manual Sync</a>
    </li>
    <li <?php echo $tab==='docs' ?'class="active"':''; ?>>
      <a href="<?php echo site_url('admin-pos'); ?>?tab=docs"><i class="fa fa-book"></i> API Docs</a>
    </li>
  </ul>

  <div class="tab-content">

    <!-- ══════════════════════════════════════════════════════════
         TAB: OVERVIEW
    ═══════════════════════════════════════════════════════════ -->
    <?php if ($tab === 'overview'): ?>
    <div class="tab-pane active">

      <!-- Stat cards -->
      <div class="row">
        <div class="col-sm-6 col-md-3">
          <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-refresh"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Syncs</span>
              <span class="info-box-number"><?php echo number_format($total); ?></span>
              <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
              <span class="progress-description"><?php echo $today; ?> today</span>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-md-3">
          <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Success Rate</span>
              <span class="info-box-number"><?php echo $succ_rate; ?>%</span>
              <div class="progress"><div class="progress-bar bg-green" style="width:<?php echo $succ_rate; ?>%"></div></div>
              <span class="progress-description"><?php echo $succ; ?> successful</span>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-md-3">
          <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="fa fa-database"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Records Updated</span>
              <span class="info-box-number"><?php echo number_format($tot_upd); ?></span>
              <div class="progress"><div class="progress-bar bg-yellow" style="width:100%"></div></div>
              <span class="progress-description">across all syncs</span>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-md-3">
          <div class="info-box">
            <span class="info-box-icon <?php echo $fail > 0 ? 'bg-red' : 'bg-green'; ?>">
              <i class="fa fa-<?php echo $fail > 0 ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text">Failed Syncs</span>
              <span class="info-box-number"><?php echo $fail; ?></span>
              <div class="progress"><div class="progress-bar bg-red" style="width:<?php echo $total ? round($fail/$total*100) : 0; ?>%"></div></div>
              <span class="progress-description"><?php echo $part; ?> partial</span>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Webhook URL -->
        <div class="col-md-6">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-plug"></i> Webhook Endpoint</h3>
            </div>
            <div class="box-body">
              <p class="text-muted small">Configure your POS system to POST sync data to this URL:</p>
              <div class="input-group">
                <input type="text" class="form-control" id="webhookUrl"
                       value="<?php echo htmlspecialchars($webhook_url); ?>" readonly>
                <span class="input-group-btn">
                  <button class="btn btn-default" type="button" onclick="copyWebhook()">
                    <i class="fa fa-copy"></i> Copy
                  </button>
                </span>
              </div>
              <div class="margin-t-10">
                <span class="label label-default">POST</span>
                <span class="label label-info">JSON</span>
                <span class="label label-warning">Header: X-POS-Key</span>
              </div>
              <?php if ($last_sync): ?>
              <p class="margin-t-10 text-muted small">
                <i class="fa fa-clock-o"></i> Last sync: <strong><?php echo date('d M Y, h:i A', strtotime($last_sync)); ?></strong>
              </p>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Sync type breakdown -->
        <div class="col-md-6">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-pie-chart"></i> Sync Type Breakdown</h3>
            </div>
            <div class="box-body" style="padding-bottom:8px">
              <?php
              $type_data = array(
                array('label'=>'Stock Updates',        'count'=>(int)($s['stock_syncs']??0), 'color'=>'bg-aqua',  'icon'=>'fa-cubes'),
                array('label'=>'Price Updates',        'count'=>(int)($s['price_syncs']??0), 'color'=>'bg-green', 'icon'=>'fa-tag'),
                array('label'=>'Coupon/Discount Sync', 'count'=>(int)($s['coupon_syncs']??0),'color'=>'bg-yellow','icon'=>'fa-ticket'),
                array('label'=>'Availability Updates', 'count'=>(int)($s['avail_syncs']??0), 'color'=>'bg-red',   'icon'=>'fa-toggle-on'),
              );
              foreach ($type_data as $td):
                $pct = $total > 0 ? round($td['count']/$total*100) : 0;
              ?>
              <div class="clearfix margin-b-5">
                <span class="pull-left"><i class="fa <?php echo $td['icon']; ?> text-muted"></i> <?php echo $td['label']; ?></span>
                <span class="pull-right"><strong><?php echo $td['count']; ?></strong></span>
              </div>
              <div class="progress progress-xs margin-b-10">
                <div class="progress-bar <?php echo $td['color']; ?>" style="width:<?php echo $pct; ?>%"></div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Active API keys summary -->
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-key"></i> Active API Keys</h3>
          <div class="box-tools pull-right">
            <a href="<?php echo site_url('admin-pos?tab=keys'); ?>" class="btn btn-xs btn-default">Manage Keys</a>
          </div>
        </div>
        <div class="box-body no-padding">
          <table class="table table-condensed">
            <thead><tr><th>Label</th><th>POS System</th><th>Permissions</th><th>Last Sync</th><th>Status</th></tr></thead>
            <tbody>
              <?php if (empty($api_keys)): ?>
                <tr><td colspan="5" class="text-center text-muted">No API keys yet. <a href="<?php echo site_url('admin-pos?tab=keys'); ?>">Create one</a>.</td></tr>
              <?php endif; ?>
              <?php foreach ($api_keys as $k): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($k['label']); ?></strong></td>
                <td><?php echo htmlspecialchars($k['pos_system'] ?: '—'); ?></td>
                <td>
                  <?php if ($k['sync_stock']): ?><span class="label label-info">Stock</span> <?php endif; ?>
                  <?php if ($k['sync_price']): ?><span class="label label-success">Price</span> <?php endif; ?>
                  <?php if ($k['sync_coupon']): ?><span class="label label-warning">Coupon</span> <?php endif; ?>
                  <?php if ($k['sync_avail']): ?><span class="label label-default">Avail</span> <?php endif; ?>
                </td>
                <td><?php echo $k['last_sync_at'] ? date('d M, h:i A', strtotime($k['last_sync_at'])) : '<span class="text-muted">Never</span>'; ?></td>
                <td><span class="label label-<?php echo $k['status'] ? 'success' : 'danger'; ?>"><?php echo $k['status'] ? 'Active' : 'Inactive'; ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Recent sync log -->
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-history"></i> Recent Sync Activity</h3>
          <div class="box-tools pull-right">
            <a href="<?php echo site_url('admin-pos?tab=logs'); ?>" class="btn btn-xs btn-default">Full Log</a>
          </div>
        </div>
        <div class="box-body no-padding">
          <table class="table table-condensed">
            <thead><tr><th>#</th><th>Type</th><th>Source</th><th>Sent</th><th>Updated</th><th>Failed</th><th>Status</th><th>Time</th></tr></thead>
            <tbody>
              <?php if (empty($recent_logs)): ?>
                <tr><td colspan="8" class="text-center text-muted">No sync activity yet.</td></tr>
              <?php endif; ?>
              <?php foreach ($recent_logs as $l):
                $sc = array('success'=>'success','failed'=>'danger','partial'=>'warning','running'=>'info');
              ?>
              <tr>
                <td><?php echo $l['id']; ?></td>
                <td><span class="label label-info"><?php echo ucfirst($l['sync_type']); ?></span></td>
                <td><?php echo ucfirst($l['source']); ?><?php if ($l['key_label']): ?><br><small class="text-muted"><?php echo htmlspecialchars($l['key_label']); ?></small><?php endif; ?></td>
                <td><?php echo $l['records_sent']; ?></td>
                <td class="text-success"><strong><?php echo $l['records_updated']; ?></strong></td>
                <td class="<?php echo $l['records_failed'] > 0 ? 'text-danger' : ''; ?>"><?php echo $l['records_failed']; ?></td>
                <td><span class="label label-<?php echo $sc[$l['status']] ?? 'default'; ?>"><?php echo ucfirst($l['status']); ?></span></td>
                <td><small><?php echo date('d M, h:i A', strtotime($l['started_at'])); ?></small></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div><!-- /overview -->


    <!-- ══════════════════════════════════════════════════════════
         TAB: API KEYS
    ═══════════════════════════════════════════════════════════ -->
    <?php elseif ($tab === 'keys'): ?>
    <div class="tab-pane active">
      <div class="row">
        <!-- Key list -->
        <div class="col-md-7">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">API Keys</h3>
            </div>
            <div class="box-body no-padding">
              <table class="table table-bordered table-hover">
                <thead><tr><th>Label</th><th>API Key</th><th>POS System</th><th>Perms</th><th>Syncs</th><th>Last Sync</th><th>Status</th><th></th></tr></thead>
                <tbody>
                  <?php if (empty($api_keys)): ?>
                    <tr><td colspan="8" class="text-center text-muted">No API keys yet.</td></tr>
                  <?php endif; ?>
                  <?php foreach ($api_keys as $k): ?>
                  <tr class="<?php echo !$k['status'] ? 'text-muted' : ''; ?>">
                    <td><strong><?php echo htmlspecialchars($k['label']); ?></strong></td>
                    <td>
                      <code class="small" id="key_<?php echo $k['id']; ?>" style="word-break:break-all">
                        <?php echo substr($k['api_key'],0,8); ?>••••••••••••••••••••
                      </code>
                      <button class="btn btn-xs btn-default" onclick="revealKey('<?php echo htmlspecialchars($k['api_key']); ?>','<?php echo $k['id']; ?>')" title="Show">
                        <i class="fa fa-eye"></i>
                      </button>
                    </td>
                    <td><?php echo htmlspecialchars($k['pos_system'] ?: '—'); ?></td>
                    <td>
                      <?php if ($k['sync_stock']): ?><span class="label label-info" title="Stock">S</span> <?php endif; ?>
                      <?php if ($k['sync_price']): ?><span class="label label-success" title="Price">P</span> <?php endif; ?>
                      <?php if ($k['sync_coupon']): ?><span class="label label-warning" title="Coupon">C</span> <?php endif; ?>
                      <?php if ($k['sync_avail']): ?><span class="label label-default" title="Availability">A</span> <?php endif; ?>
                    </td>
                    <td><?php echo number_format($k['sync_count']); ?></td>
                    <td><small><?php echo $k['last_sync_at'] ? date('d M, h:i A', strtotime($k['last_sync_at'])) : 'Never'; ?></small></td>
                    <td><span class="label label-<?php echo $k['status'] ? 'success' : 'danger'; ?>"><?php echo $k['status'] ? 'Active' : 'Inactive'; ?></span></td>
                    <td>
                      <a href="<?php echo site_url('admin-pos?tab=keys&toggle_key='.$k['id']); ?>"
                         class="btn btn-xs btn-<?php echo $k['status'] ? 'warning' : 'success'; ?>"
                         title="<?php echo $k['status'] ? 'Deactivate' : 'Activate'; ?>">
                        <i class="fa fa-<?php echo $k['status'] ? 'pause' : 'play'; ?>"></i>
                      </a>
                      <a href="<?php echo site_url('admin-pos?tab=keys&delete_key='.$k['id']); ?>"
                         class="btn btn-xs btn-danger"
                         onclick="return confirm('Delete this API key? The POS system using it will stop syncing.')"
                         title="Delete">
                        <i class="fa fa-trash"></i>
                      </a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Create key form -->
        <div class="col-md-5">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-plus"></i> Generate New API Key</h3>
            </div>
            <div class="box-body">
              <form method="post" action="<?php echo site_url('admin-pos?tab=keys'); ?>">
                <input type="hidden" name="create_api_key" value="1">
                <div class="form-group">
                  <label>Label <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="label" placeholder="e.g. Main Counter POS" required>
                </div>
                <div class="form-group">
                  <label>POS System / Software</label>
                  <input type="text" class="form-control" name="pos_system" placeholder="e.g. Marg ERP, Tally, custom">
                </div>
                <div class="form-group">
                  <label>Allowed Sync Types</label>
                  <div>
                    <label class="checkbox-inline"><input type="checkbox" name="sync_stock"  value="1" checked> Stock</label>
                    <label class="checkbox-inline"><input type="checkbox" name="sync_price"  value="1" checked> Price</label>
                    <label class="checkbox-inline"><input type="checkbox" name="sync_coupon" value="1" checked> Coupons</label>
                    <label class="checkbox-inline"><input type="checkbox" name="sync_avail"  value="1" checked> Availability</label>
                  </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                  <i class="fa fa-key"></i> Generate Key
                </button>
              </form>
              <div class="callout callout-info margin-t-15" style="padding:8px 12px">
                <small><i class="fa fa-info-circle"></i>
                  The generated key is shown once in a success alert — copy it immediately.
                  Keys are stored hashed and cannot be recovered.
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div><!-- /keys -->


    <!-- ══════════════════════════════════════════════════════════
         TAB: SYNC LOGS
    ═══════════════════════════════════════════════════════════ -->
    <?php elseif ($tab === 'logs'): ?>
    <div class="tab-pane active">
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-list"></i> Sync Log Report</h3>
          <div class="box-tools pull-right">
            <small class="text-muted"><?php echo count($sync_logs); ?> records</small>
          </div>
        </div>
        <div class="box-body">
          <!-- Filters -->
          <form method="get" action="<?php echo site_url('admin-pos'); ?>" class="margin-b-15">
            <input type="hidden" name="tab" value="logs">
            <div class="row">
              <div class="col-sm-2">
                <select class="form-control input-sm" name="log_type">
                  <option value="">All Types</option>
                  <?php foreach (array('stock','price','coupon','availability') as $lt): ?>
                    <option value="<?php echo $lt; ?>" <?php echo $log_type===$lt?'selected':''; ?>><?php echo ucfirst($lt); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-sm-2">
                <select class="form-control input-sm" name="log_status">
                  <option value="">All Status</option>
                  <?php foreach (array('success','failed','partial','running') as $ls): ?>
                    <option value="<?php echo $ls; ?>" <?php echo $log_status===$ls?'selected':''; ?>><?php echo ucfirst($ls); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-sm-2">
                <input type="date" class="form-control input-sm" name="log_from"
                       value="<?php echo htmlspecialchars($log_from); ?>" placeholder="From">
              </div>
              <div class="col-sm-2">
                <input type="date" class="form-control input-sm" name="log_to"
                       value="<?php echo htmlspecialchars($log_to); ?>" placeholder="To">
              </div>
              <div class="col-sm-2">
                <button type="submit" class="btn btn-sm btn-saffron"><i class="fa fa-filter"></i> Filter</button>
                <a href="<?php echo site_url('admin-pos?tab=logs'); ?>" class="btn btn-sm btn-default"><i class="fa fa-times"></i></a>
              </div>
              <div class="col-sm-2">
                <input type="text" class="form-control input-sm" id="logSearch" placeholder="Search…">
              </div>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table table-bordered table-hover table-condensed" id="logTable">
              <thead>
                <tr>
                  <th>Log #</th>
                  <th>Type</th>
                  <th>Source</th>
                  <th>API Key</th>
                  <th>Sent</th>
                  <th>Updated</th>
                  <th>Failed</th>
                  <th>Status</th>
                  <th>IP</th>
                  <th>Started</th>
                  <th>Duration</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($sync_logs)): ?>
                  <tr><td colspan="12" class="text-center text-muted">No sync logs found.</td></tr>
                <?php endif; ?>
                <?php foreach ($sync_logs as $l):
                  $sc = array('success'=>'success','failed'=>'danger','partial'=>'warning','running'=>'info');
                  $dur = '';
                  if ($l['completed_at'] && $l['started_at']) {
                    $secs = strtotime($l['completed_at']) - strtotime($l['started_at']);
                    $dur  = $secs < 60 ? $secs.'s' : round($secs/60,1).'m';
                  }
                ?>
                <tr>
                  <td>#<?php echo $l['id']; ?></td>
                  <td><span class="label label-info"><?php echo ucfirst($l['sync_type']); ?></span></td>
                  <td><?php echo ucfirst($l['source']); ?></td>
                  <td><small><?php echo htmlspecialchars($l['key_label'] ?: ($l['api_key_id'] ? '#'.$l['api_key_id'] : 'Manual')); ?></small></td>
                  <td><?php echo $l['records_sent']; ?></td>
                  <td class="text-success"><strong><?php echo $l['records_updated']; ?></strong></td>
                  <td class="<?php echo $l['records_failed'] > 0 ? 'text-danger' : ''; ?>">
                    <?php echo $l['records_failed']; ?>
                  </td>
                  <td><span class="label label-<?php echo $sc[$l['status']] ?? 'default'; ?>"><?php echo ucfirst($l['status']); ?></span></td>
                  <td><small class="text-muted"><?php echo htmlspecialchars($l['request_ip'] ?? '—'); ?></small></td>
                  <td><small><?php echo date('d M, h:i A', strtotime($l['started_at'])); ?></small></td>
                  <td><small><?php echo $dur ?: '—'; ?></small></td>
                  <td>
                    <?php if ($l['error_message'] || $l['payload_summary']): ?>
                    <button class="btn btn-xs btn-default"
                      onclick='showLogDetail(<?php echo json_encode($l); ?>)'>
                      <i class="fa fa-info-circle"></i>
                    </button>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div><!-- /logs -->


    <!-- ══════════════════════════════════════════════════════════
         TAB: MANUAL SYNC
    ═══════════════════════════════════════════════════════════ -->
    <?php elseif ($tab === 'manual'): ?>
    <div class="tab-pane active">
      <div class="row">
        <div class="col-md-7">
          <div class="box box-warning">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-pencil"></i> Manual Sync</h3>
            </div>
            <div class="box-body">
              <div class="callout callout-info" style="padding:8px 12px;margin-bottom:15px">
                <small><i class="fa fa-info-circle"></i>
                  Use this for one-off updates. Enter the <strong>Product Code</strong> (from Products → product_code field) and the new values.
                </small>
              </div>
              <div id="manualSyncResult"></div>
              <form id="manualSyncForm" method="post" action="<?php echo site_url('ajax/pos-manual-sync'); ?>">

                <div class="form-group">
                  <label>Sync Type</label>
                  <select class="form-control" name="manual_type" id="manualType" onchange="toggleManualFields(this.value)">
                    <option value="stock">Stock Update</option>
                    <option value="price">Price Update</option>
                    <option value="availability">Availability</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Product Code <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="manual_product_code"
                         id="manualProductCode" list="productCodeList"
                         placeholder="e.g. SKU001" required>
                  <datalist id="productCodeList">
                    <?php foreach ($products_with_code as $pc): ?>
                      <option value="<?php echo htmlspecialchars($pc['product_code']); ?>"
                              label="<?php echo htmlspecialchars($pc['name']); ?>">
                    <?php endforeach; ?>
                  </datalist>
                  <small class="text-muted" id="productPreview"></small>
                </div>

                <!-- Stock fields -->
                <div id="fieldsStock">
                  <div class="form-group">
                    <label>New Stock Quantity</label>
                    <input type="number" class="form-control" name="manual_stock_qty" min="0" value="0">
                  </div>
                </div>

                <!-- Price fields -->
                <div id="fieldsPrice" style="display:none">
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="form-group">
                        <label>Selling Price (₹)</label>
                        <input type="number" step="0.01" class="form-control" name="manual_price" min="0">
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="form-group">
                        <label>Offer Price (₹) <small class="text-muted">optional</small></label>
                        <input type="number" step="0.01" class="form-control" name="manual_offer_price" min="0" placeholder="Leave blank to keep">
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Availability fields -->
                <div id="fieldsAvail" style="display:none">
                  <div class="form-group">
                    <label>Availability</label>
                    <select class="form-control" name="manual_available">
                      <option value="1">Available (Active)</option>
                      <option value="0">Unavailable (Inactive)</option>
                    </select>
                  </div>
                </div>

                <button type="submit" id="manualSyncBtn" class="btn btn-warning btn-block">
                  <i class="fa fa-refresh"></i> Apply Manual Sync
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- Products with codes reference -->
        <div class="col-md-5">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-list"></i> Products with Product Code</h3>
            </div>
            <div class="box-body no-padding" style="max-height:420px;overflow-y:auto">
              <?php if (empty($products_with_code)): ?>
                <div class="text-center text-muted" style="padding:20px">
                  No products have a product code assigned.<br>
                  <a href="<?php echo site_url('admin-products'); ?>">Go to Products</a> and add codes.
                </div>
              <?php else: ?>
              <table class="table table-condensed table-hover" id="prodCodeTable">
                <thead><tr><th>Code</th><th>Name</th><th>Price</th><th>Stock</th><th>Status</th></tr></thead>
                <tbody>
                  <?php foreach ($products_with_code as $pc): ?>
                  <tr style="cursor:pointer" onclick="fillProductCode('<?php echo htmlspecialchars($pc['product_code']); ?>')">
                    <td><code><?php echo htmlspecialchars($pc['product_code']); ?></code></td>
                    <td><small><?php echo htmlspecialchars($this->spice_model->truncate_text($pc['name'], 30)); ?></small></td>
                    <td>
                      <small>
                        <?php echo $this->spice_model->rupees((float)$pc['price']); ?>
                        <?php if (!empty($pc['offer_price'])): ?>
                          <span class="text-success">(<?php echo $this->spice_model->rupees((float)$pc['offer_price']); ?>)</span>
                        <?php endif; ?>
                      </small>
                    </td>
                    <td><small><?php echo $pc['stock_qty']; ?></small></td>
                    <td><span class="label label-<?php echo $pc['status'] ? 'success' : 'danger'; ?> label-xs"><?php echo $pc['status'] ? 'On' : 'Off'; ?></span></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div><!-- /manual -->


    <!-- ══════════════════════════════════════════════════════════
         TAB: API DOCS
    ═══════════════════════════════════════════════════════════ -->
    <?php elseif ($tab === 'docs'): ?>
    <div class="tab-pane active">
      <div class="row">
        <div class="col-md-8">

          <div class="box box-default">
            <div class="box-header with-border"><h3 class="box-title">Webhook Endpoint</h3></div>
            <div class="box-body">
              <table class="table table-condensed">
                <tr><td><strong>URL</strong></td><td><code><?php echo htmlspecialchars($webhook_url); ?></code></td></tr>
                <tr><td><strong>Method</strong></td><td><code>POST</code></td></tr>
                <tr><td><strong>Content-Type</strong></td><td><code>application/json</code></td></tr>
                <tr><td><strong>Auth Header</strong></td><td><code>X-POS-Key: &lt;your_api_key&gt;</code></td></tr>
              </table>
            </div>
          </div>

          <!-- Stock example -->
          <div class="box box-info">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-cubes"></i> 1. Stock Sync</h3></div>
            <div class="box-body">
              <pre style="background:#2d2d2d;color:#f8f8f2;padding:12px;border-radius:4px;font-size:12px">{
  "type": "stock",
  "items": [
    { "product_code": "SKU001", "stock_qty": 50 },
    { "product_code": "SKU002", "stock_qty": 0  },
    { "product_code": "SKU003", "sku": "VAR-L",  "stock_qty": 12 }
  ]
}</pre>
              <p class="text-muted small">Use <code>product_code</code> to update the main product stock. Add <code>sku</code> to also update a specific variant.</p>
            </div>
          </div>

          <!-- Price example -->
          <div class="box box-success">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-tag"></i> 2. Price Sync</h3></div>
            <div class="box-body">
              <pre style="background:#2d2d2d;color:#f8f8f2;padding:12px;border-radius:4px;font-size:12px">{
  "type": "price",
  "items": [
    { "product_code": "SKU001", "price": 299.00, "offer_price": 249.00 },
    { "product_code": "SKU002", "price": 599.00 },
    { "product_code": "SKU003", "price": 199.00, "gst": 18 }
  ]
}</pre>
              <p class="text-muted small">Send only the fields you want to update. Omit <code>offer_price</code> to leave it unchanged.</p>
            </div>
          </div>

          <!-- Coupon example -->
          <div class="box box-warning">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-ticket"></i> 3. Coupon / Discount Sync</h3></div>
            <div class="box-body">
              <pre style="background:#2d2d2d;color:#f8f8f2;padding:12px;border-radius:4px;font-size:12px">{
  "type": "coupon",
  "items": [
    { "code": "SALE20",  "type": "percent", "value": 20, "min_order": 500, "expires_at": "2026-12-31" },
    { "code": "FLAT100", "type": "flat",    "value": 100, "min_order": 999 },
    { "code": "OLDCODE", "status": 0 }
  ]
}</pre>
              <p class="text-muted small">Creates coupon if it doesn't exist; updates if it does. Set <code>"status": 0</code> to deactivate.</p>
            </div>
          </div>

          <!-- Availability example -->
          <div class="box box-danger">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-toggle-on"></i> 4. Availability Sync</h3></div>
            <div class="box-body">
              <pre style="background:#2d2d2d;color:#f8f8f2;padding:12px;border-radius:4px;font-size:12px">{
  "type": "availability",
  "items": [
    { "product_code": "SKU001", "available": true  },
    { "product_code": "SKU002", "available": false },
    { "product_code": "SKU003", "status": 1 }
  ]
}</pre>
              <p class="text-muted small">Use <code>available: true/false</code> or <code>status: 1/0</code>. Sets the product as Active or Inactive in the store.</p>
            </div>
          </div>

          <!-- Response format -->
          <div class="box">
            <div class="box-header with-border"><h3 class="box-title">Response Format</h3></div>
            <div class="box-body">
              <p><strong>Success (HTTP 200):</strong></p>
              <pre style="background:#2d2d2d;color:#f8f8f2;padding:12px;border-radius:4px;font-size:12px">{ "success": true, "status": "success", "log_id": 42, "updated": 3, "failed": 0, "errors": [] }</pre>
              <p><strong>Partial (HTTP 200):</strong></p>
              <pre style="background:#2d2d2d;color:#f8f8f2;padding:12px;border-radius:4px;font-size:12px">{ "success": true, "status": "partial", "log_id": 43, "updated": 2, "failed": 1, "errors": ["Row 2: product_code 'BAD' not found"] }</pre>
              <p><strong>Auth Failure (HTTP 401):</strong></p>
              <pre style="background:#2d2d2d;color:#f8f8f2;padding:12px;border-radius:4px;font-size:12px">{ "success": false, "error": "Invalid or inactive API key" }</pre>
            </div>
          </div>

        </div>

        <!-- Quick reference sidebar -->
        <div class="col-md-4">
          <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">Quick Reference</h3></div>
            <div class="box-body">
              <p><strong>Sync Types</strong></p>
              <ul class="list-unstyled">
                <li><span class="label label-info">stock</span> — update <code>stock_qty</code></li>
                <li><span class="label label-success">price</span> — update <code>price</code>, <code>offer_price</code>, <code>gst</code></li>
                <li><span class="label label-warning">coupon</span> — upsert discount codes</li>
                <li><span class="label label-danger">availability</span> — set product active/inactive</li>
              </ul>
              <hr>
              <p><strong>Match field</strong></p>
              <p class="text-muted small">Products are matched by <code>product_code</code> (set in Products page). Variants are matched by <code>sku</code>.</p>
              <hr>
              <p><strong>HTTP Status Codes</strong></p>
              <ul class="list-unstyled small">
                <li><span class="label label-success">200</span> success / partial</li>
                <li><span class="label label-warning">400</span> bad request (invalid type)</li>
                <li><span class="label label-danger">401</span> invalid API key</li>
                <li><span class="label label-danger">403</span> permission denied</li>
                <li><span class="label label-danger">422</span> all items failed</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div><!-- /docs -->
    <?php endif; ?>

  </div><!-- /.tab-content -->
</div><!-- /.nav-tabs-custom -->

<!-- Log detail modal -->
<div class="modal fade" id="logDetailModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Log Detail</h4>
      </div>
      <div class="modal-body" id="logDetailBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
