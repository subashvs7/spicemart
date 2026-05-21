<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Stats row -->
<div class="row">
  <div class="col-sm-3">
    <div class="info-box">
      <span class="info-box-icon bg-yellow"><i class="fa fa-star"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Avg Rating</span>
        <span class="info-box-number"><?php echo number_format((float)$stats['avg_rating'],1); ?> / 5</span>
      </div>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="info-box">
      <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Approved</span>
        <span class="info-box-number"><?php echo number_format($stats['approved']); ?></span>
      </div>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="info-box">
      <span class="info-box-icon bg-orange"><i class="fa fa-clock-o"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Pending</span>
        <span class="info-box-number"><?php echo number_format($stats['pending']); ?></span>
      </div>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="info-box">
      <span class="info-box-icon bg-red"><i class="fa fa-ban"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Rejected</span>
        <span class="info-box-number"><?php echo number_format($stats['rejected']); ?></span>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- Main reviews table -->
  <div class="col-md-9">
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">Reviews <small class="text-muted"><?php echo count($reviews); ?> shown</small></h3>
        <div class="box-tools pull-right">
          <!-- Quick status filters -->
          <?php
          $statuses = array('' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected');
          foreach ($statuses as $val => $lbl):
            $active = $filter_status === $val;
            $cls = $active ? 'btn-saffron' : 'btn-default';
          ?>
            <a href="<?php echo site_url('admin-reviews'); ?>?status=<?php echo $val; ?><?php echo $filter_rating ? '&rating='.$filter_rating : ''; ?>"
               class="btn btn-xs <?php echo $cls; ?>"><?php echo $lbl; ?></a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Filters -->
      <div class="box-body border-bottom" style="padding-bottom:10px">
        <form method="get" action="<?php echo site_url('admin-reviews'); ?>" class="form-inline">
          <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status); ?>">
          <div class="form-group margin-r-5">
            <input type="text" class="form-control input-sm" name="q"
                   value="<?php echo htmlspecialchars($filter_search); ?>"
                   placeholder="Search customer, product, comment…" style="width:220px">
          </div>
          <div class="form-group margin-r-5">
            <select class="form-control input-sm" name="rating">
              <option value="">All Ratings</option>
              <?php for ($i=5;$i>=1;$i--): ?>
                <option value="<?php echo $i; ?>" <?php echo $filter_rating===$i?'selected':''; ?>>
                  <?php echo $i; ?> Star<?php echo $i!==1?'s':''; ?>
                </option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group margin-r-5">
            <input type="date" class="form-control input-sm" name="date_from"
                   value="<?php echo htmlspecialchars($filter_date); ?>" placeholder="From date">
          </div>
          <button type="submit" class="btn btn-sm btn-saffron"><i class="fa fa-filter"></i> Filter</button>
          <a href="<?php echo site_url('admin-reviews'); ?>" class="btn btn-sm btn-default">Reset</a>
        </form>
      </div>

      <!-- Bulk action bar -->
      <div class="box-body border-bottom" style="padding:8px 15px" id="bulkBar" style="display:none">
        <span class="text-muted small" id="bulkCount">0 selected</span>
        <div class="pull-right">
          <button class="btn btn-xs btn-success" onclick="bulkAction('approve')"><i class="fa fa-check"></i> Approve</button>
          <button class="btn btn-xs btn-warning" onclick="bulkAction('reject')"><i class="fa fa-ban"></i> Reject</button>
          <button class="btn btn-xs btn-danger"  onclick="bulkAction('delete')"><i class="fa fa-trash"></i> Delete</button>
        </div>
      </div>

      <div class="box-body" style="padding:0">
        <table class="table table-bordered table-hover admin-table" style="margin:0">
          <thead>
            <tr>
              <th style="width:30px"><input type="checkbox" id="checkAll"></th>
              <th>Product</th>
              <th>Customer</th>
              <th style="width:90px">Rating</th>
              <th>Comment</th>
              <th style="width:80px">Status</th>
              <th style="width:80px">Date</th>
              <th style="width:110px">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($reviews as $r): ?>
            <tr data-review-id="<?php echo $r['id']; ?>" id="rev-row-<?php echo $r['id']; ?>">
              <td><input type="checkbox" class="rev-check" value="<?php echo $r['id']; ?>"></td>
              <td>
                <a href="<?php echo site_url('product/'.$r['pid']); ?>" target="_blank" class="text-dark">
                  <?php echo htmlspecialchars($r['product_name']); ?>
                </a>
              </td>
              <td><?php echo htmlspecialchars($r['customer_name']); ?></td>
              <td>
                <span class="text-warning">
                  <?php for($i=1;$i<=5;$i++) echo $i<=(int)$r['rating'] ? '★' : '☆'; ?>
                </span>
                <small class="text-muted">(<?php echo $r['rating']; ?>)</small>
                <?php if ($r['is_featured']): ?>
                  <span class="label label-warning label-xs" title="Featured"><i class="fa fa-bookmark"></i></span>
                <?php endif; ?>
              </td>
              <td>
                <span style="display:block;max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                      title="<?php echo htmlspecialchars($r['comment']); ?>">
                  <?php echo $r['comment'] ? htmlspecialchars($r['comment']) : '<em class="text-muted">No comment</em>'; ?>
                </span>
              </td>
              <td>
                <?php
                $smap = array('approved'=>'label-success','pending'=>'label-warning','rejected'=>'label-danger');
                $scls = $smap[$r['status']] ?? 'label-default';
                ?>
                <span class="label <?php echo $scls; ?> rev-status-badge" id="rev-status-<?php echo $r['id']; ?>">
                  <?php echo ucfirst($r['status']); ?>
                </span>
              </td>
              <td class="text-muted small"><?php echo date('d M Y', strtotime($r['created_at'])); ?></td>
              <td>
                <?php if ($r['status'] !== 'approved'): ?>
                  <button class="btn btn-xs btn-success rev-action-btn" title="Approve"
                          data-id="<?php echo $r['id']; ?>" data-action="approve">
                    <i class="fa fa-check"></i>
                  </button>
                <?php endif; ?>
                <?php if ($r['status'] !== 'rejected'): ?>
                  <button class="btn btn-xs btn-warning rev-action-btn" title="Reject"
                          data-id="<?php echo $r['id']; ?>" data-action="reject">
                    <i class="fa fa-ban"></i>
                  </button>
                <?php endif; ?>
                <?php if (!$r['is_featured']): ?>
                  <button class="btn btn-xs btn-default rev-action-btn" title="Feature"
                          data-id="<?php echo $r['id']; ?>" data-action="feature">
                    <i class="fa fa-bookmark-o"></i>
                  </button>
                <?php else: ?>
                  <button class="btn btn-xs btn-default rev-action-btn" title="Unfeature"
                          data-id="<?php echo $r['id']; ?>" data-action="unfeature">
                    <i class="fa fa-bookmark"></i>
                  </button>
                <?php endif; ?>
                <button class="btn btn-xs btn-danger rev-action-btn" title="Delete"
                        data-id="<?php echo $r['id']; ?>" data-action="delete">
                  <i class="fa fa-trash"></i>
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($reviews)): ?>
              <tr><td colspan="8" class="text-center text-muted" style="padding:30px">No reviews found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="col-md-3">

    <!-- Rating distribution -->
    <div class="box box-default">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-bar-chart"></i> Rating Breakdown</h3>
      </div>
      <div class="box-body">
        <?php
        $dist_map = array();
        foreach ($rating_dist as $d) $dist_map[$d['rating']] = (int)$d['cnt'];
        $total_approved = max(1, (int)$stats['approved']);
        for ($i=5;$i>=1;$i--):
          $cnt = $dist_map[$i] ?? 0;
          $pct = round($cnt / $total_approved * 100);
        ?>
        <div class="clearfix margin-b-5">
          <span class="pull-left" style="width:50px">
            <span class="text-warning"><?php echo str_repeat('★',$i); ?></span>
          </span>
          <div class="pull-left" style="flex:1;min-width:0;width:calc(100% - 90px)">
            <div class="progress progress-xs" style="margin:5px 0 0;width:100%">
              <div class="progress-bar progress-bar-warning" style="width:<?php echo $pct; ?>%"></div>
            </div>
          </div>
          <span class="pull-right text-muted small" style="width:35px;text-align:right"><?php echo $cnt; ?></span>
        </div>
        <?php endfor; ?>
        <hr style="margin:10px 0">
        <div class="text-center">
          <span style="font-size:2rem;font-weight:700;color:var(--saffron)"><?php echo number_format((float)$stats['avg_rating'],1); ?></span>
          <div class="text-warning" style="font-size:1.1rem">
            <?php
            $avg = (float)$stats['avg_rating'];
            for ($i=1;$i<=5;$i++) echo $i<=$avg ? '★' : ($i-0.5<=$avg ? '½' : '☆');
            ?>
          </div>
          <small class="text-muted"><?php echo number_format($stats['total']); ?> total reviews</small>
        </div>
      </div>
    </div>

    <!-- Quick tips -->
    <div class="box box-info">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-info-circle"></i> Tips</h3>
      </div>
      <div class="box-body">
        <ul class="text-muted small" style="padding-left:16px;margin:0">
          <li class="margin-b-5"><strong>Approve</strong> — review becomes visible on the product page</li>
          <li class="margin-b-5"><strong>Reject</strong> — hidden from customers but kept for records</li>
          <li class="margin-b-5"><strong>Feature</strong> <i class="fa fa-bookmark-o"></i> — pinned to the top of the review list</li>
          <li class="margin-b-5"><strong>Delete</strong> — permanently removed</li>
          <li>New reviews default to <span class="label label-success label-xs">Approved</span> and go live immediately</li>
        </ul>
      </div>
    </div>

  </div>
</div>
