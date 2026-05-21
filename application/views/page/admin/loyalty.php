<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
  <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<!-- ══ STAT CARDS ═══════════════════════════════════════════════ -->
<div class="row">
  <div class="col-md-3 col-sm-6">
    <div class="info-box" style="border-radius:10px">
      <span class="info-box-icon" style="background:#7B4228;border-radius:10px 0 0 10px"><i class="fa fa-star"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total Points Issued</span>
        <span class="info-box-number"><?php echo number_format($total_pts_issued); ?></span>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="info-box" style="border-radius:10px">
      <span class="info-box-icon bg-green" style="border-radius:10px 0 0 10px"><i class="fa fa-exchange"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Points Redeemed</span>
        <span class="info-box-number"><?php echo number_format($total_pts_redeemed); ?></span>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="info-box" style="border-radius:10px">
      <span class="info-box-icon bg-aqua" style="border-radius:10px 0 0 10px"><i class="fa fa-users"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Enrolled Members</span>
        <span class="info-box-number"><?php echo number_format($total_enrolled); ?></span>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="info-box" style="border-radius:10px">
      <span class="info-box-icon bg-yellow" style="border-radius:10px 0 0 10px"><i class="fa fa-bullhorn"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Active Campaigns</span>
        <span class="info-box-number"><?php echo $active_campaigns; ?></span>
        <div style="margin-top:4px">
          <a href="<?php echo site_url('admin-loyalty'); ?>?run_auto=1"
             class="btn btn-xs btn-warning"
             onclick="return confirm('Run today\'s birthday & anniversary automations?')">
            <i class="fa fa-magic"></i> Run Today's Automations
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══ MAIN TABS ════════════════════════════════════════════════ -->
<div class="nav-tabs-custom">
  <ul class="nav nav-tabs">
    <li class="active"><a href="#tab-settings"   data-toggle="tab"><i class="fa fa-cog"></i> Points Settings</a></li>
    <li>              <a href="#tab-customers"  data-toggle="tab"><i class="fa fa-users"></i> Customers
      <span class="badge" style="background:#777"><?php echo count($customers); ?></span></a></li>
    <li>              <a href="#tab-campaigns"  data-toggle="tab"><i class="fa fa-bullhorn"></i> Campaigns
      <span class="badge" style="background:#777"><?php echo count($campaigns); ?></span></a></li>
    <li>              <a href="#tab-activity"   data-toggle="tab"><i class="fa fa-history"></i> Activity Log</a></li>
  </ul>

  <div class="tab-content" style="padding-top:20px">

    <!-- ══ TAB 1: SETTINGS ══════════════════════════════════════ -->
    <div class="tab-pane active" id="tab-settings">
      <form method="post" action="<?php echo site_url('admin-loyalty'); ?>">
        <input type="hidden" name="save_settings" value="1">
        <div class="row">

          <div class="col-md-6">
            <div class="box box-primary">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-plus-circle"></i> Points Earning</h3>
              </div>
              <div class="box-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Points Earned</label>
                      <input type="number" class="form-control" name="loyalty_earn_rate"
                             value="<?php echo (int)($ly['loyalty_earn_rate'] ?? 1); ?>" min="1">
                      <small class="text-muted">points per unit spent</small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Per ₹ Amount</label>
                      <input type="number" class="form-control" name="loyalty_earn_per"
                             value="<?php echo (int)($ly['loyalty_earn_per'] ?? 10); ?>" min="1">
                      <small class="text-muted">e.g. 1 point per ₹10</small>
                    </div>
                  </div>
                </div>
                <div class="callout callout-info" style="font-size:13px;margin-top:5px">
                  <i class="fa fa-lightbulb-o"></i>
                  Current: Customer earns <strong><?php echo $ly['loyalty_earn_rate'] ?? 1; ?> point(s)</strong>
                  for every <strong>₹<?php echo $ly['loyalty_earn_per'] ?? 10; ?></strong> spent.
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="box box-success">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-minus-circle"></i> Points Redemption</h3>
              </div>
              <div class="box-body">
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Points Required</label>
                      <input type="number" class="form-control" name="loyalty_redeem_rate"
                             value="<?php echo (int)($ly['loyalty_redeem_rate'] ?? 100); ?>" min="1">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>= ₹ Discount</label>
                      <input type="number" class="form-control" name="loyalty_redeem_value"
                             value="<?php echo (int)($ly['loyalty_redeem_value'] ?? 10); ?>" min="1">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Min to Redeem</label>
                      <input type="number" class="form-control" name="loyalty_min_redeem"
                             value="<?php echo (int)($ly['loyalty_min_redeem'] ?? 100); ?>" min="1">
                      <small class="text-muted">min points</small>
                    </div>
                  </div>
                </div>
                <div class="callout callout-success" style="font-size:13px;margin-top:5px">
                  <i class="fa fa-lightbulb-o"></i>
                  Current: <strong><?php echo $ly['loyalty_redeem_rate'] ?? 100; ?> points = ₹<?php echo $ly['loyalty_redeem_value'] ?? 10; ?></strong> discount.
                  Min balance: <strong><?php echo $ly['loyalty_min_redeem'] ?? 100; ?> pts</strong>.
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="box">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-clock-o"></i> Tier Thresholds</h3>
                <small class="text-muted" style="margin-left:8px">Based on lifetime points earned</small>
              </div>
              <div class="box-body">
                <table class="table table-condensed">
                  <thead><tr><th>Tier</th><th>Required Points</th><th>Benefits</th></tr></thead>
                  <tbody>
                    <tr><td><span class="label" style="background:#cd7f32">🥉 Bronze</span></td><td>0</td><td>Base earn rate</td></tr>
                    <tr><td><span class="label label-default">🥈 Silver</span></td><td>500</td><td>1.2× bonus points</td></tr>
                    <tr><td><span class="label label-warning">🥇 Gold</span></td><td>2,000</td><td>1.5× bonus + early access</td></tr>
                    <tr><td><span class="label label-primary">💎 Platinum</span></td><td>5,000</td><td>2× bonus + exclusive offers</td></tr>
                  </tbody>
                </table>
                <small class="text-muted"><i class="fa fa-info-circle"></i> Tiers are automatically updated when points are awarded.</small>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="box">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-calendar-times-o"></i> Expiry</h3>
              </div>
              <div class="box-body">
                <div class="form-group">
                  <label>Points expire after (days)</label>
                  <input type="number" class="form-control" name="loyalty_expiry_days"
                         value="<?php echo (int)($ly['loyalty_expiry_days'] ?? 365); ?>" min="0">
                  <small class="text-muted">Set to 0 for no expiry.</small>
                </div>
              </div>
            </div>
          </div>

        </div>
        <button type="submit" class="btn btn-saffron btn-lg">
          <i class="fa fa-save"></i> Save Settings
        </button>
      </form>
    </div>

    <!-- ══ TAB 2: CUSTOMERS ══════════════════════════════════════ -->
    <div class="tab-pane" id="tab-customers">

      <!-- Segment pills -->
      <?php
      $seg_counts = array('all'=>0,'new'=>0,'frequent'=>0,'highvalue'=>0,'inactive'=>0,'regular'=>0);
      foreach ($customers as $c) {
          $seg_counts['all']++;
          $seg_counts[$c['segment']] = ($seg_counts[$c['segment']] ?? 0) + 1;
      }
      $seg_labels = array('all'=>'All','new'=>'New','frequent'=>'Frequent','highvalue'=>'High Value','inactive'=>'Inactive','regular'=>'Regular');
      $seg_colors = array('all'=>'btn-default','new'=>'btn-info','frequent'=>'btn-primary','highvalue'=>'btn-warning','inactive'=>'btn-danger','regular'=>'btn-default');
      ?>
      <div style="margin-bottom:12px">
        <?php foreach ($seg_labels as $key => $label): ?>
          <button class="btn btn-sm <?php echo $seg_colors[$key]; ?> seg-filter-btn <?php echo $key==='all'?'active':''; ?>"
                  data-seg="<?php echo $key; ?>" style="margin-right:4px;margin-bottom:4px">
            <?php echo $label; ?> <span class="badge"><?php echo $seg_counts[$key]; ?></span>
          </button>
        <?php endforeach; ?>

        <div class="pull-right">
          <div class="input-group input-group-sm" style="width:220px">
            <span class="input-group-addon"><i class="fa fa-search"></i></span>
            <input type="text" class="form-control" id="custLoySearch" placeholder="Search name or email…">
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover admin-table" id="custLoyTable">
          <thead>
            <tr>
              <th>Customer</th><th>Segment</th><th>Tier</th>
              <th>Points Balance</th><th>Lifetime Earned</th>
              <th>Orders</th><th>Spent</th><th>Last Order</th>
              <th>Birthday</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($customers as $c): ?>
            <?php
            $seg = $c['segment'];
            $seg_badge = array(
              'new'       => '<span class="label label-info">New</span>',
              'frequent'  => '<span class="label label-primary">⭐ Frequent</span>',
              'highvalue' => '<span class="label label-warning">💎 High Value</span>',
              'inactive'  => '<span class="label label-danger">Inactive</span>',
              'regular'   => '<span class="label label-default">Regular</span>',
            );
            $tier_badge = array(
              'bronze'   => '<span class="label" style="background:#cd7f32;color:#fff">🥉 Bronze</span>',
              'silver'   => '<span class="label label-default">🥈 Silver</span>',
              'gold'     => '<span class="label label-warning">🥇 Gold</span>',
              'platinum' => '<span class="label label-primary">💎 Platinum</span>',
            );
            ?>
            <tr data-seg="<?php echo $seg; ?>">
              <td>
                <strong><?php echo htmlspecialchars($c['name']); ?></strong><br>
                <small class="text-muted"><?php echo htmlspecialchars($c['email']); ?></small>
              </td>
              <td><?php echo $seg_badge[$seg] ?? '<span class="label label-default">'.ucfirst($seg).'</span>'; ?></td>
              <td><?php echo $tier_badge[$c['tier']] ?? ''; ?></td>
              <td>
                <strong class="text-saffron"><?php echo number_format($c['points_balance']); ?></strong>
                <small class="text-muted">pts</small>
              </td>
              <td><?php echo number_format($c['points_earned']); ?> pts</td>
              <td><?php echo $c['order_count']; ?></td>
              <td><?php echo $this->spice_model->rupees((float)$c['total_spent']); ?></td>
              <td>
                <?php if ($c['last_order_at']): ?>
                  <small><?php echo date('d M Y', strtotime($c['last_order_at'])); ?></small>
                <?php else: ?>
                  <span class="text-muted">Never</span>
                <?php endif; ?>
              </td>
              <td>
                <?php echo $c['birthday'] ? date('d M', strtotime($c['birthday'])) : '<span class="text-muted">—</span>'; ?>
              </td>
              <td style="white-space:nowrap">
                <button class="btn btn-xs btn-saffron adj-btn"
                        data-id="<?php echo $c['id']; ?>"
                        data-name="<?php echo htmlspecialchars($c['name'], ENT_QUOTES); ?>"
                        data-balance="<?php echo $c['points_balance']; ?>"
                        title="Adjust Points">
                  <i class="fa fa-star"></i>
                </button>
                <button class="btn btn-xs btn-info bday-btn"
                        data-id="<?php echo $c['id']; ?>"
                        data-name="<?php echo htmlspecialchars($c['name'], ENT_QUOTES); ?>"
                        data-bday="<?php echo htmlspecialchars($c['birthday'] ?? '', ENT_QUOTES); ?>"
                        title="Set Birthday">
                  <i class="fa fa-birthday-cake"></i>
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($customers)): ?>
              <tr><td colspan="10" class="text-center text-muted">No customers yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ══ TAB 3: CAMPAIGNS ══════════════════════════════════════ -->
    <div class="tab-pane" id="tab-campaigns">
      <div class="row" style="margin-bottom:12px">
        <div class="col-sm-8">
          <div class="input-group input-group-sm">
            <span class="input-group-addon"><i class="fa fa-search"></i></span>
            <input type="text" class="form-control" id="campSearch" placeholder="Search campaigns…">
            <span class="input-group-btn">
              <button class="btn btn-default" id="campClear"><i class="fa fa-times"></i></button>
            </span>
          </div>
        </div>
        <div class="col-sm-4 text-right">
          <button class="btn btn-sm btn-saffron" id="addCampaignBtn">
            <i class="fa fa-plus"></i> Add Campaign
          </button>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover admin-table" id="campTable">
          <thead>
            <tr>
              <th>Name</th><th>Type</th><th>Offer</th><th>Target</th>
              <th>Dates</th><th>Status</th><th>Sent</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($campaigns as $cp): ?>
            <?php
            $type_icons = array('general'=>'📢','birthday'=>'🎂','anniversary'=>'🎉','festival'=>'🎊');
            $type_colors= array('general'=>'label-default','birthday'=>'label-danger','anniversary'=>'label-warning','festival'=>'label-success');
            $offer_desc = array(
              'points_bonus'  => '⭐ +'.(int)$cp['offer_value'].' pts',
              'percent_off'   => '🏷 '.(float)$cp['offer_value'].'% off',
              'flat_off'      => '🏷 ₹'.(float)$cp['offer_value'].' off',
              'free_shipping' => '🚚 Free Shipping',
            );
            $target_labels = array('all'=>'All','new'=>'New','frequent'=>'Frequent','highvalue'=>'High Value','inactive'=>'Inactive');
            $status_badge  = array('draft'=>'label-default','active'=>'label-success','paused'=>'label-warning','completed'=>'label-primary');
            ?>
            <tr>
              <td>
                <strong><?php echo htmlspecialchars($cp['name']); ?></strong>
                <?php if ($cp['coupon_code']): ?>
                  <br><code style="font-size:.8rem"><?php echo htmlspecialchars($cp['coupon_code']); ?></code>
                <?php endif; ?>
              </td>
              <td>
                <span class="label <?php echo $type_colors[$cp['type']] ?? 'label-default'; ?>">
                  <?php echo ($type_icons[$cp['type']] ?? '').' '.ucfirst($cp['type']); ?>
                </span>
              </td>
              <td><?php echo $offer_desc[$cp['offer_type']] ?? $cp['offer_type']; ?></td>
              <td><?php echo $target_labels[$cp['target']] ?? ucfirst($cp['target']); ?></td>
              <td>
                <small>
                  <?php if ($cp['start_date']): ?><?php echo date('d M Y', strtotime($cp['start_date'])); ?><?php endif; ?>
                  <?php if ($cp['end_date']): ?> – <?php echo date('d M Y', strtotime($cp['end_date'])); ?><?php endif; ?>
                  <?php if ($cp['festival_date']): ?><br>📅 <?php echo date('d M Y', strtotime($cp['festival_date'])); ?><?php endif; ?>
                </small>
              </td>
              <td>
                <span class="label <?php echo $status_badge[$cp['status']] ?? 'label-default'; ?>">
                  <?php echo ucfirst($cp['status']); ?>
                </span>
              </td>
              <td>
                <strong><?php echo $cp['actual_sent']; ?></strong>
                <?php if ($cp['status'] === 'active'): ?>
                  <br><a href="<?php echo site_url('admin-loyalty'); ?>?trigger=<?php echo $cp['id']; ?>"
                         class="btn btn-xs btn-primary margin-t-5"
                         onclick="return confirm('Send campaign to target customers now?')">
                    <i class="fa fa-paper-plane"></i> Send
                  </a>
                <?php endif; ?>
              </td>
              <td style="white-space:nowrap">
                <button class="btn btn-xs btn-primary edit-camp-btn"
                        data-camp='<?php echo htmlspecialchars(json_encode($cp), ENT_QUOTES); ?>'>
                  <i class="fa fa-pencil"></i>
                </button>
                <a href="<?php echo site_url('admin-loyalty'); ?>?del_campaign=<?php echo $cp['id']; ?>"
                   class="btn btn-xs btn-danger"
                   onclick="return confirm('Delete this campaign?')">
                  <i class="fa fa-trash"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($campaigns)): ?>
              <tr><td colspan="8" class="text-center text-muted">No campaigns yet. Create your first one!</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Automation info box -->
      <div class="callout callout-info" style="margin-top:16px;font-size:13px">
        <h5><i class="fa fa-magic"></i> Automated Campaigns</h5>
        <p class="margin-b-0">
          <strong>Birthday</strong> — Auto-triggers for customers whose birthday matches today's date.<br>
          <strong>Anniversary</strong> — Auto-triggers on the customer's account creation anniversary.<br>
          <strong>Festival</strong> — Set a specific date and send to your chosen segment.<br>
          Click <strong>Run Today's Automations</strong> (top stat card) daily to process birthday &amp; anniversary campaigns.
        </p>
      </div>
    </div>

    <!-- ══ TAB 4: ACTIVITY LOG ═══════════════════════════════════ -->
    <div class="tab-pane" id="tab-activity">
      <div class="row" style="margin-bottom:10px">
        <div class="col-sm-5">
          <div class="input-group input-group-sm">
            <span class="input-group-addon"><i class="fa fa-search"></i></span>
            <input type="text" class="form-control" id="ledgerSearch" placeholder="Search customer, note…">
          </div>
        </div>
        <div class="col-sm-3">
          <select class="form-control input-sm" id="ledgerFilter">
            <option value="">All Types</option>
            <option value="earned">Earned</option>
            <option value="redeemed">Redeemed</option>
            <option value="bonus">Bonus</option>
            <option value="adjusted">Adjusted</option>
            <option value="expired">Expired</option>
          </select>
        </div>
        <div class="col-sm-4" style="line-height:30px">
          <small class="text-muted" id="ledgerCount"></small>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover admin-table" id="ledgerTable">
          <thead>
            <tr><th>Customer</th><th>Points</th><th>Type</th><th>Reference</th><th>Note</th><th>Date</th></tr>
          </thead>
          <tbody>
            <?php foreach ($ledger as $l): ?>
            <tr>
              <td><?php echo htmlspecialchars($l['customer_name']); ?></td>
              <td>
                <strong class="<?php echo $l['points'] >= 0 ? 'text-green' : 'text-red'; ?>">
                  <?php echo ($l['points'] >= 0 ? '+' : '').$l['points']; ?>
                </strong>
              </td>
              <td>
                <?php
                $t_colors = array('earned'=>'label-success','redeemed'=>'label-danger','bonus'=>'label-warning','adjusted'=>'label-primary','expired'=>'label-default');
                $t_icons  = array('earned'=>'⬆','redeemed'=>'⬇','bonus'=>'⭐','adjusted'=>'⚙','expired'=>'⌛');
                ?>
                <span class="label <?php echo $t_colors[$l['type']] ?? 'label-default'; ?>">
                  <?php echo ($t_icons[$l['type']] ?? '').' '.ucfirst($l['type']); ?>
                </span>
              </td>
              <td>
                <small class="text-muted"><?php echo ucfirst($l['ref_type']); ?>
                <?php if ($l['ref_id']): ?>#<?php echo $l['ref_id']; ?><?php endif; ?>
                </small>
              </td>
              <td><small><?php echo htmlspecialchars($l['note'] ?? '—'); ?></small></td>
              <td><small><?php echo date('d M Y, h:i A', strtotime($l['created_at'])); ?></small></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($ledger)): ?>
              <tr><td colspan="6" class="text-center text-muted">No activity yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- /tab-content -->
</div><!-- /nav-tabs-custom -->


<!-- ══ ADJUST POINTS MODAL ══════════════════════════════════════ -->
<div class="modal fade" id="adjModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="<?php echo site_url('admin-loyalty'); ?>">
        <input type="hidden" name="adjust_points" value="1">
        <input type="hidden" name="adj_user_id" id="adjUserId">
        <div class="modal-header" style="background:#2C1810;color:#fff">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff">&times;</button>
          <h4 class="modal-title"><i class="fa fa-star"></i> Adjust Points — <span id="adjUserName"></span></h4>
        </div>
        <div class="modal-body">
          <div class="callout callout-info">
            Current balance: <strong id="adjBalance"></strong> points
          </div>
          <div class="form-group">
            <label>Points to Add / Deduct</label>
            <input type="number" class="form-control" name="adj_points" id="adjPoints"
                   placeholder="e.g. +100 or -50" required>
            <small class="text-muted">Use positive to add, negative to deduct.</small>
          </div>
          <div class="form-group">
            <label>Reason / Note</label>
            <input type="text" class="form-control" name="adj_note" placeholder="e.g. Compensation, Error correction…">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-saffron"><i class="fa fa-check"></i> Apply</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ══ SET BIRTHDAY MODAL ════════════════════════════════════════ -->
<div class="modal fade" id="bdayModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="<?php echo site_url('admin-loyalty'); ?>">
        <input type="hidden" name="save_birthday" value="1">
        <input type="hidden" name="bday_user_id" id="bdayUserId">
        <div class="modal-header" style="background:#2C1810;color:#fff">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff">&times;</button>
          <h4 class="modal-title">🎂 Set Birthday — <span id="bdayUserName"></span></h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Birthday</label>
            <input type="date" class="form-control" name="bday_date" id="bdayDate">
            <small class="text-muted">Used for automated birthday campaign rewards.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-saffron"><i class="fa fa-save"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ══ ADD / EDIT CAMPAIGN MODAL ════════════════════════════════ -->
<div class="modal fade" id="campModal" tabindex="-1" data-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" action="<?php echo site_url('admin-loyalty'); ?>">
        <input type="hidden" name="save_campaign" value="1">
        <input type="hidden" name="campaign_id" id="campId" value="0">
        <div class="modal-header" style="background:#2C1810;color:#fff;border-radius:4px 4px 0 0">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1">&times;</button>
          <h4 class="modal-title" id="campModalTitle"><i class="fa fa-bullhorn"></i> Add Campaign</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label>Campaign Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" id="campName" required
                       placeholder="e.g. Summer Festival Bonus">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Status</label>
                <select class="form-control" name="status" id="campStatus">
                  <option value="draft">Draft</option>
                  <option value="active">Active</option>
                  <option value="paused">Paused</option>
                  <option value="completed">Completed</option>
                </select>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label>Campaign Type</label>
                <select class="form-control" name="type" id="campType">
                  <option value="general">📢 General</option>
                  <option value="birthday">🎂 Birthday</option>
                  <option value="anniversary">🎉 Anniversary</option>
                  <option value="festival">🎊 Festival</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Offer Type</label>
                <select class="form-control" name="offer_type" id="campOfferType">
                  <option value="points_bonus">⭐ Bonus Points</option>
                  <option value="percent_off">🏷 Percent Discount</option>
                  <option value="flat_off">🏷 Flat Discount</option>
                  <option value="free_shipping">🚚 Free Shipping</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Offer Value</label>
                <input type="number" class="form-control" name="offer_value" id="campOfferValue"
                       step="0.01" min="0" placeholder="e.g. 100 (pts) or 20 (%)">
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label>Target Segment</label>
                <select class="form-control" name="target" id="campTarget">
                  <option value="all">All Customers</option>
                  <option value="new">New Customers</option>
                  <option value="frequent">Frequent Buyers</option>
                  <option value="highvalue">High-Value Customers</option>
                  <option value="inactive">Inactive Customers</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Coupon Code <small class="text-muted">(optional)</small></label>
                <input type="text" class="form-control" name="coupon_code" id="campCoupon"
                       placeholder="e.g. BDAY2025" style="text-transform:uppercase">
              </div>
            </div>
            <div class="col-md-4" id="festDateWrap" style="display:none">
              <div class="form-group">
                <label>Festival Date</label>
                <input type="date" class="form-control" name="festival_date" id="campFestDate">
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label>Start Date</label>
                <input type="date" class="form-control" name="start_date" id="campStart">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>End Date</label>
                <input type="date" class="form-control" name="end_date" id="campEnd">
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label>Description <small class="text-muted">(internal notes)</small></label>
                <input type="text" class="form-control" name="description" id="campDesc"
                       placeholder="e.g. Diwali 2025 loyalty bonus campaign">
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label>Customer Message <small class="text-muted">(shown in notification or email)</small></label>
                <textarea class="form-control" name="message" id="campMessage" rows="3"
                          placeholder="e.g. 🎂 Happy Birthday! Here are 200 bonus points as a gift from us!"></textarea>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-saffron"><i class="fa fa-save"></i> <span id="campSaveLabel">Save Campaign</span></button>
        </div>
      </form>
    </div>
  </div>
</div>
