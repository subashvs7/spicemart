<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<style>
/* Toggle switch */
.pm-toggle { position:relative; display:inline-block; width:52px; height:28px; margin:0; cursor:pointer; }
.pm-toggle input { opacity:0; width:0; height:0; }
.pm-slider {
  position:absolute; inset:0; background:#ccc; border-radius:28px;
  transition:.3s; cursor:pointer;
}
.pm-slider:before {
  content:''; position:absolute; width:22px; height:22px; left:3px; bottom:3px;
  background:#fff; border-radius:50%; transition:.3s;
}
.pm-toggle input:checked + .pm-slider { background:#7B4228; }
.pm-toggle input:checked + .pm-slider:before { transform:translateX(24px); }

.pm-toggle-sm { width:40px; height:22px; }
.pm-toggle-sm .pm-slider:before { width:16px; height:16px; }
.pm-toggle-sm input:checked + .pm-slider:before { transform:translateX(18px); }

/* Payment method card */
.pm-card {
  border:1px solid #e0e0e0; border-radius:10px; padding:18px;
  margin-bottom:14px; transition:border-color .2s;
}
.pm-card:hover { border-color:#7B4228; }
.pm-card-header { display:flex; align-items:center; justify-content:space-between; }
.pm-card-icon { font-size:2rem; margin-right:14px; }
.pm-card-info strong { font-size:15px; }

/* Sub-method row */
.pm-sub-row {
  display:flex; align-items:center; gap:12px;
  padding:10px 0; border-bottom:1px solid #f4f4f4;
}
.pm-sub-row:last-child { border-bottom:none; }
.pm-sub-icon { font-size:1.4rem; width:30px; text-align:center; flex-shrink:0; }
.pm-sub-info { flex:1; }
.pm-sub-info strong { font-size:13px; }
.pm-sub-info small { display:block; color:#999; font-size:11px; }

/* Stat card */
.pay-stat-card {
  border-radius:10px; padding:16px 18px;
  margin-bottom:14px; color:#fff;
}

/* Gateway status badge */
.gw-status-row { display:flex; align-items:center; justify-content:space-between;
  padding:10px 15px; border-bottom:1px solid #f4f4f4; }
.gw-status-row:last-child { border-bottom:none; }
.gw-dot { width:10px; height:10px; border-radius:50%; display:inline-block; margin-right:8px; flex-shrink:0; }
.gw-dot-on  { background:#00a65a; }
.gw-dot-off { background:#ccc; }
</style>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="post" action="<?php echo site_url('admin-payments'); ?>">
<div class="row">

  <!-- ── LEFT: Settings ─────────────────────────────────────── -->
  <div class="col-md-8">

    <!-- Payment Methods -->
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-credit-card"></i> Payment Methods</h3>
        <small class="text-muted" style="margin-left:10px">Enable or disable payment options shown at checkout</small>
      </div>
      <div class="box-body">

        <!-- COD -->
        <div class="pm-card">
          <div class="pm-card-header">
            <div style="display:flex;align-items:center">
              <span class="pm-card-icon">💵</span>
              <div class="pm-card-info">
                <strong>Cash on Delivery (COD)</strong>
                <p class="text-muted small margin-b-0">Customer pays at the door. No transaction fees.</p>
              </div>
            </div>
            <label class="pm-toggle">
              <input type="checkbox" name="payment_cod_enabled" value="1"
                     <?php echo ($settings['payment_cod_enabled'] ?? '1') == '1' ? 'checked' : ''; ?>>
              <span class="pm-slider"></span>
            </label>
          </div>
        </div>

        <!-- Online / Razorpay -->
        <div class="pm-card">
          <div class="pm-card-header">
            <div style="display:flex;align-items:center">
              <span class="pm-card-icon">💳</span>
              <div class="pm-card-info">
                <strong>Online Payments via Razorpay</strong>
                <p class="text-muted small margin-b-0">Razorpay gateway — cards, UPI, wallets, net banking &amp; more.</p>
              </div>
            </div>
            <label class="pm-toggle">
              <input type="checkbox" name="payment_razorpay_enabled" value="1"
                     id="rzpMasterToggle"
                     <?php echo ($settings['payment_razorpay_enabled'] ?? '1') == '1' ? 'checked' : ''; ?>>
              <span class="pm-slider"></span>
            </label>
          </div>

          <!-- Sub-methods -->
          <div id="rzpSubMethods" style="margin-top:16px;padding-top:16px;border-top:1px dashed #e0e0e0;
               <?php echo ($settings['payment_razorpay_enabled'] ?? '1') == '0' ? 'opacity:.45;pointer-events:none' : ''; ?>">
            <p class="text-muted small margin-b-10">
              <i class="fa fa-info-circle"></i>
              Select which methods are displayed inside the Razorpay checkout modal:
            </p>

            <div class="row">
              <div class="col-md-6">

                <div class="pm-sub-row">
                  <label class="pm-toggle pm-toggle-sm" style="flex-shrink:0">
                    <input type="checkbox" name="payment_cards_enabled" value="1"
                           <?php echo ($settings['payment_cards_enabled'] ?? '1') == '1' ? 'checked' : ''; ?>>
                    <span class="pm-slider"></span>
                  </label>
                  <span class="pm-sub-icon">💳</span>
                  <div class="pm-sub-info">
                    <strong>Credit / Debit Cards</strong>
                    <small>Visa, Mastercard, RuPay, Amex</small>
                  </div>
                </div>

                <div class="pm-sub-row">
                  <label class="pm-toggle pm-toggle-sm" style="flex-shrink:0">
                    <input type="checkbox" name="payment_netbanking_enabled" value="1"
                           <?php echo ($settings['payment_netbanking_enabled'] ?? '1') == '1' ? 'checked' : ''; ?>>
                    <span class="pm-slider"></span>
                  </label>
                  <span class="pm-sub-icon">🏦</span>
                  <div class="pm-sub-info">
                    <strong>Net Banking</strong>
                    <small>All major Indian banks</small>
                  </div>
                </div>

                <div class="pm-sub-row">
                  <label class="pm-toggle pm-toggle-sm" style="flex-shrink:0">
                    <input type="checkbox" name="payment_upi_enabled" value="1"
                           <?php echo ($settings['payment_upi_enabled'] ?? '1') == '1' ? 'checked' : ''; ?>>
                    <span class="pm-slider"></span>
                  </label>
                  <span class="pm-sub-icon">📱</span>
                  <div class="pm-sub-info">
                    <strong>UPI / Google Pay</strong>
                    <small>GPay, PhonePe, BHIM, Paytm UPI</small>
                  </div>
                </div>

              </div>
              <div class="col-md-6">

                <div class="pm-sub-row">
                  <label class="pm-toggle pm-toggle-sm" style="flex-shrink:0">
                    <input type="checkbox" name="payment_wallets_enabled" value="1"
                           <?php echo ($settings['payment_wallets_enabled'] ?? '1') == '1' ? 'checked' : ''; ?>>
                    <span class="pm-slider"></span>
                  </label>
                  <span class="pm-sub-icon">👜</span>
                  <div class="pm-sub-info">
                    <strong>Wallets</strong>
                    <small>Paytm, Amazon Pay, Mobikwik, Freecharge</small>
                  </div>
                </div>

                <div class="pm-sub-row">
                  <label class="pm-toggle pm-toggle-sm" style="flex-shrink:0">
                    <input type="checkbox" name="payment_applepay_enabled" value="1"
                           <?php echo ($settings['payment_applepay_enabled'] ?? '0') == '1' ? 'checked' : ''; ?>>
                    <span class="pm-slider"></span>
                  </label>
                  <span class="pm-sub-icon" style="font-size:1.2rem;font-weight:700;color:#555"></span>
                  <div class="pm-sub-info">
                    <strong>Apple Pay</strong>
                    <small>Safari on iOS / macOS only — limited India support</small>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Razorpay Config -->
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-key"></i> Razorpay API Configuration</h3>
      </div>
      <div class="box-body">
        <div class="alert alert-info" style="font-size:13px">
          <i class="fa fa-info-circle"></i>
          Get your API keys from
          <a href="https://dashboard.razorpay.com/app/keys" target="_blank">Razorpay Dashboard → Settings → API Keys</a>.
          Use <strong>Test keys</strong> (rzp_test_…) for staging and <strong>Live keys</strong> (rzp_live_…) for production.
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label><i class="fa fa-id-badge"></i> Key ID</label>
              <input type="text" class="form-control" name="razorpay_key_id"
                     value="<?php echo htmlspecialchars($settings['razorpay_key_id'] ?? ''); ?>"
                     placeholder="rzp_test_XXXXXXXXXXXXXXXX"
                     autocomplete="off">
              <small class="text-muted">Starts with <code>rzp_test_</code> or <code>rzp_live_</code></small>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label><i class="fa fa-lock"></i> Key Secret</label>
              <div class="input-group">
                <input type="password" class="form-control" name="razorpay_key_secret"
                       id="rzpSecretInput"
                       value="<?php echo htmlspecialchars($settings['razorpay_key_secret'] ?? ''); ?>"
                       placeholder="••••••••••••••••••••"
                       autocomplete="off">
                <span class="input-group-btn">
                  <button type="button" class="btn btn-default" id="rzpSecretToggleBtn">
                    <i class="fa fa-eye" id="rzpSecretIcon"></i>
                  </button>
                </span>
              </div>
              <small class="text-muted">Server-side only — never exposed to customers.</small>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label><i class="fa fa-link"></i> Webhook Callback URL
                <small class="text-muted">(add this in your Razorpay Dashboard → Webhooks)</small>
              </label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-globe"></i></span>
                <input type="text" class="form-control" readonly
                       id="webhookUrlField"
                       value="<?php echo site_url('razorpay-callback'); ?>">
                <span class="input-group-btn">
                  <button type="button" class="btn btn-default" id="copyWebhookBtn" title="Copy URL">
                    <i class="fa fa-copy"></i> Copy
                  </button>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Save -->
    <div style="margin-bottom:20px">
      <button type="submit" class="btn btn-saffron btn-lg">
        <i class="fa fa-save"></i> Save Payment Settings
      </button>
      <a href="<?php echo site_url('admin-shipping'); ?>" class="btn btn-default btn-lg margin-l-10">
        <i class="fa fa-truck"></i> Shipping Settings
      </a>
    </div>

  </div>

  <!-- ── RIGHT: Status + Stats ─────────────────────────────── -->
  <div class="col-md-4">

    <?php
    $rzp_key  = $settings['razorpay_key_id'] ?? '';
    $rzp_mode = strpos($rzp_key, 'rzp_live_') === 0
                ? 'live'
                : ($rzp_key ? 'test' : 'unconfigured');

    $bool = function($key, $default = '1') use ($settings) {
        return ($settings[$key] ?? $default) == '1';
    };
    ?>

    <!-- Gateway Status -->
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-signal"></i> Live Status</h3>
      </div>
      <div class="box-body" style="padding:0">
        <div class="gw-status-row">
          <span>
            <span class="gw-dot <?php echo $rzp_key ? 'gw-dot-on' : 'gw-dot-off'; ?>"></span>
            Razorpay Gateway
          </span>
          <?php if ($rzp_mode === 'live'): ?>
            <span class="label label-success" style="font-size:11px">LIVE</span>
          <?php elseif ($rzp_mode === 'test'): ?>
            <span class="label label-warning" style="font-size:11px">TEST MODE</span>
          <?php else: ?>
            <span class="label label-danger" style="font-size:11px">NOT SET</span>
          <?php endif; ?>
        </div>
        <div class="gw-status-row">
          <span>
            <span class="gw-dot <?php echo $bool('payment_cod_enabled') ? 'gw-dot-on' : 'gw-dot-off'; ?>"></span>
            Cash on Delivery
          </span>
          <span class="label <?php echo $bool('payment_cod_enabled') ? 'label-success' : 'label-default'; ?>" style="font-size:11px">
            <?php echo $bool('payment_cod_enabled') ? 'ON' : 'OFF'; ?>
          </span>
        </div>
        <div class="gw-status-row">
          <span>
            <span class="gw-dot <?php echo $bool('payment_cards_enabled') ? 'gw-dot-on' : 'gw-dot-off'; ?>"></span>
            Credit / Debit Cards
          </span>
          <span class="label <?php echo $bool('payment_cards_enabled') ? 'label-success' : 'label-default'; ?>" style="font-size:11px">
            <?php echo $bool('payment_cards_enabled') ? 'ON' : 'OFF'; ?>
          </span>
        </div>
        <div class="gw-status-row">
          <span>
            <span class="gw-dot <?php echo $bool('payment_netbanking_enabled') ? 'gw-dot-on' : 'gw-dot-off'; ?>"></span>
            Net Banking
          </span>
          <span class="label <?php echo $bool('payment_netbanking_enabled') ? 'label-success' : 'label-default'; ?>" style="font-size:11px">
            <?php echo $bool('payment_netbanking_enabled') ? 'ON' : 'OFF'; ?>
          </span>
        </div>
        <div class="gw-status-row">
          <span>
            <span class="gw-dot <?php echo $bool('payment_upi_enabled') ? 'gw-dot-on' : 'gw-dot-off'; ?>"></span>
            UPI / Google Pay
          </span>
          <span class="label <?php echo $bool('payment_upi_enabled') ? 'label-success' : 'label-default'; ?>" style="font-size:11px">
            <?php echo $bool('payment_upi_enabled') ? 'ON' : 'OFF'; ?>
          </span>
        </div>
        <div class="gw-status-row">
          <span>
            <span class="gw-dot <?php echo $bool('payment_wallets_enabled') ? 'gw-dot-on' : 'gw-dot-off'; ?>"></span>
            Wallets
          </span>
          <span class="label <?php echo $bool('payment_wallets_enabled') ? 'label-success' : 'label-default'; ?>" style="font-size:11px">
            <?php echo $bool('payment_wallets_enabled') ? 'ON' : 'OFF'; ?>
          </span>
        </div>
        <div class="gw-status-row">
          <span>
            <span class="gw-dot <?php echo $bool('payment_applepay_enabled', '0') ? 'gw-dot-on' : 'gw-dot-off'; ?>"></span>
            Apple Pay
          </span>
          <span class="label <?php echo $bool('payment_applepay_enabled', '0') ? 'label-success' : 'label-default'; ?>" style="font-size:11px">
            <?php echo $bool('payment_applepay_enabled', '0') ? 'ON' : 'OFF'; ?>
          </span>
        </div>
      </div>
    </div>

    <!-- Payment Stats -->
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-bar-chart"></i> Payment Stats</h3>
        <small class="pull-right text-muted">All time</small>
      </div>
      <div class="box-body" style="padding:0">
        <table class="table table-condensed" style="margin-bottom:0;font-size:13px">
          <thead style="background:#f9f9f9">
            <tr>
              <th>Method</th>
              <th class="text-center">Orders</th>
              <th class="text-center">Paid</th>
              <th class="text-right">Revenue</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $method_icons = array('cod'=>'💵','razorpay'=>'💳','payu'=>'🔷','wallet'=>'👜');
            foreach ($payment_stats as $ps):
              $icon = $method_icons[$ps['payment_method']] ?? '💰';
            ?>
            <tr>
              <td><?php echo $icon; ?> <strong><?php echo strtoupper($ps['payment_method']); ?></strong></td>
              <td class="text-center"><?php echo $ps['cnt']; ?></td>
              <td class="text-center">
                <span class="label label-success"><?php echo $ps['paid_cnt']; ?></span>
                <?php if ($ps['failed_cnt'] > 0): ?>
                  <span class="label label-danger"><?php echo $ps['failed_cnt']; ?></span>
                <?php endif; ?>
              </td>
              <td class="text-right text-saffron"><strong><?php echo $this->spice_model->rupees((float)$ps['total']); ?></strong></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($payment_stats)): ?>
              <tr><td colspan="4" class="text-center text-muted">No payment data yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
</form>

<!-- ── Transactions Log ─────────────────────────────────────── -->
<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-list-alt"></i> Transaction Log
      <span class="label label-default" style="font-size:12px;margin-left:6px"><?php echo count($transactions); ?></span>
    </h3>
  </div>
  <div class="box-body">

    <!-- Filter bar -->
    <div class="row" style="margin-bottom:12px">
      <div class="col-sm-4">
        <div class="input-group input-group-sm">
          <span class="input-group-addon"><i class="fa fa-search"></i></span>
          <input type="text" class="form-control" id="txn_search" placeholder="Search order #, customer, txn ID…">
          <span class="input-group-btn">
            <button type="button" class="btn btn-default" id="txn_clear" title="Clear"><i class="fa fa-times"></i></button>
          </span>
        </div>
      </div>
      <div class="col-sm-2 col-xs-6">
        <select class="form-control input-sm" id="txn_fMethod">
          <option value="">All Methods</option>
          <option value="cod">COD</option>
          <option value="razorpay">Razorpay</option>
          <option value="payu">PayU</option>
          <option value="wallet">Wallet</option>
        </select>
      </div>
      <div class="col-sm-2 col-xs-6">
        <select class="form-control input-sm" id="txn_fPayStatus">
          <option value="">All Pay Status</option>
          <option value="paid">Paid</option>
          <option value="pending">Pending</option>
          <option value="failed">Failed</option>
          <option value="refunded">Refunded</option>
        </select>
      </div>
      <div class="col-sm-4" style="line-height:30px">
        <small class="text-muted" id="txn_count"></small>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover admin-table" id="txn_table">
        <thead>
          <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Method</th>
            <th>Transaction ID</th>
            <th>Amount</th>
            <th>Pay Status</th>
            <th>Order Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($transactions as $t): ?>
          <tr>
            <td>
              <a href="<?php echo site_url('admin-orders'); ?>?view=<?php echo $t['id']; ?>" target="_blank">
                <strong>#<?php echo str_pad($t['id'],5,'0',STR_PAD_LEFT); ?></strong>
              </a>
            </td>
            <td>
              <strong><?php echo htmlspecialchars($t['customer_name']); ?></strong><br>
              <small class="text-muted"><?php echo htmlspecialchars($t['email']); ?></small>
            </td>
            <td>
              <?php
              $micons = array('cod'=>'💵','razorpay'=>'💳','payu'=>'🔷','wallet'=>'👜');
              echo ($micons[$t['payment_method']] ?? '💰');
              ?>
              <span class="label label-default" style="font-size:11px"><?php echo strtoupper($t['payment_method']); ?></span>
            </td>
            <td>
              <?php if ($t['transaction_id']): ?>
                <code style="font-size:11px"><?php echo htmlspecialchars($t['transaction_id']); ?></code>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
            <td class="text-saffron"><strong><?php echo $this->spice_model->rupees((float)$t['total_amount']); ?></strong></td>
            <td><?php echo $this->spice_model->payment_status_badge($t['payment_status']); ?></td>
            <td><?php echo $this->spice_model->order_status_badge($t['order_status']); ?></td>
            <td><?php echo date('d M Y', strtotime($t['created_at'])); ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($transactions)): ?>
            <tr><td colspan="8" class="text-center text-muted">No transactions yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
/* ── Razorpay master toggle dims sub-methods ── */
document.getElementById('rzpMasterToggle').addEventListener('change', function () {
  var sub = document.getElementById('rzpSubMethods');
  sub.style.opacity       = this.checked ? '1'    : '0.45';
  sub.style.pointerEvents = this.checked ? 'auto' : 'none';
});

/* ── Show/hide secret key ── */
document.getElementById('rzpSecretToggleBtn').addEventListener('click', function () {
  var inp  = document.getElementById('rzpSecretInput');
  var icon = document.getElementById('rzpSecretIcon');
  if (inp.type === 'password') {
    inp.type = 'text';
    icon.className = 'fa fa-eye-slash';
  } else {
    inp.type = 'password';
    icon.className = 'fa fa-eye';
  }
});

/* ── Copy webhook URL ── */
document.getElementById('copyWebhookBtn').addEventListener('click', function () {
  var field = document.getElementById('webhookUrlField');
  field.select();
  document.execCommand('copy');
  this.innerHTML = '<i class="fa fa-check"></i> Copied!';
  var btn = this;
  setTimeout(function () { btn.innerHTML = '<i class="fa fa-copy"></i> Copy'; }, 2000);
});

/* ── Transaction table filter ── */
(function () {
  var rows   = Array.from(document.querySelectorAll('#txn_table tbody tr'));
  var search = document.getElementById('txn_search');
  var count  = document.getElementById('txn_count');
  function run() {
    var q  = search.value.trim().toLowerCase();
    var fM = document.getElementById('txn_fMethod').value.toLowerCase();
    var fP = document.getElementById('txn_fPayStatus').value.toLowerCase();
    var n = 0;
    rows.forEach(function (r) {
      if (r.cells.length < 2) { r.style.display = ''; return; }
      var method  = r.cells[2] ? r.cells[2].textContent.trim().toLowerCase() : '';
      var paystat = r.cells[5] ? r.cells[5].textContent.trim().toLowerCase() : '';
      var ok = (!q  || r.textContent.toLowerCase().indexOf(q) >= 0)
            && (!fM || method.indexOf(fM) >= 0)
            && (!fP || paystat.indexOf(fP) >= 0);
      r.style.display = ok ? '' : 'none';
      if (ok) n++;
    });
    count.textContent = n + ' / ' + rows.length + ' transactions';
  }
  search.addEventListener('input', run);
  document.getElementById('txn_clear').addEventListener('click', function () { search.value = ''; run(); });
  ['txn_fMethod','txn_fPayStatus'].forEach(function (id) {
    document.getElementById(id).addEventListener('change', run);
  });
})();
</script>
