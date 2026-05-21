<style>
/* ════════════════════════════════════════════════════════════
   Razorpay-style Test Modal
   Reference: Razorpay Standard Checkout UI
════════════════════════════════════════════════════════════ */

/* Overlay */
#rzptOverlay {
  display: none; position: fixed; inset: 0; z-index: 99999;
  background: rgba(0,0,0,.6);
  align-items: center; justify-content: center;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Rubik', sans-serif;
  animation: rzpt-fadein .2s ease;
}
#rzptOverlay.rzpt-show { display: flex; }
@keyframes rzpt-fadein { from { opacity: 0; } to { opacity: 1; } }

/* Modal shell */
#rzptModal {
  display: flex; flex-direction: row;
  width: 620px; max-width: 96vw; height: 520px; max-height: 92vh;
  border-radius: 10px; overflow: hidden;
  box-shadow: 0 30px 90px rgba(0,0,0,.5);
  position: relative;
  animation: rzpt-slidein .22s ease;
}
@keyframes rzpt-slidein {
  from { transform: translateY(18px) scale(.97); opacity: 0; }
  to   { transform: translateY(0)    scale(1);   opacity: 1; }
}

/* ── LEFT PANEL ─────────────────────────────────────────── */
#rzptLeft {
  width: 220px; flex-shrink: 0;
  background: #072654;
  display: flex; flex-direction: column;
  overflow: hidden;
}

/* Merchant card */
#rzptMerchant {
  padding: 22px 18px 16px;
  border-bottom: 1px solid rgba(255,255,255,.1);
}
#rzptAvatar {
  width: 46px; height: 46px; border-radius: 50%;
  background: #fff; color: #072654;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px; font-weight: 800;
  margin-bottom: 10px; flex-shrink: 0;
}
#rzptMerchantName {
  font-size: 13px; font-weight: 600;
  color: rgba(255,255,255,.9); margin-bottom: 2px;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
#rzptAmountLabel {
  font-size: 10px; color: rgba(255,255,255,.45);
  text-transform: uppercase; letter-spacing: .8px; margin-bottom: 3px;
}
#rzptAmountLeft {
  font-size: 22px; font-weight: 800; color: #fff; letter-spacing: -.3px;
}

/* Test badge */
#rzptTestBadge {
  display: inline-block; margin-top: 8px;
  background: #f6a623; color: #072654;
  font-size: 9px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase;
  padding: 2px 6px; border-radius: 3px;
}

/* Method list */
#rzptMethods {
  flex: 1; overflow-y: auto; padding: 8px 0;
}
#rzptMethods::-webkit-scrollbar { width: 3px; }
#rzptMethods::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 2px; }

.rzpt-method {
  display: flex; align-items: center; gap: 10px;
  padding: 11px 18px; cursor: pointer;
  border-left: 3px solid transparent;
  color: rgba(255,255,255,.65);
  transition: background .15s, color .15s, border-color .15s;
  font-size: 12.5px; font-weight: 500;
}
.rzpt-method:hover {
  background: rgba(255,255,255,.08);
  color: rgba(255,255,255,.9);
}
.rzpt-method.rzpt-active {
  background: rgba(51,149,255,.18);
  border-left-color: #3395ff;
  color: #fff;
  font-weight: 700;
}
.rzpt-method-icon { font-size: 1.05rem; width: 22px; text-align: center; flex-shrink: 0; }

/* Branding */
#rzptBranding {
  padding: 12px 18px;
  border-top: 1px solid rgba(255,255,255,.1);
  display: flex; align-items: center; gap: 5px;
}
#rzptBranding svg { flex-shrink: 0; }
#rzptBrandingText {
  font-size: 10px; color: rgba(255,255,255,.35);
  line-height: 1.3;
}
#rzptBrandingText strong { color: rgba(255,255,255,.6); font-weight: 700; }

/* ── RIGHT PANEL ────────────────────────────────────────── */
#rzptRight {
  flex: 1; background: #fff;
  display: flex; flex-direction: column; overflow: hidden;
  position: relative;
}

/* Close button */
#rzptClose {
  position: absolute; top: 10px; right: 12px; z-index: 1;
  background: none; border: none; width: 28px; height: 28px;
  border-radius: 50%; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  color: #aaa; font-size: 20px; line-height: 1;
  transition: background .15s, color .15s;
}
#rzptClose:hover { background: #f0f0f0; color: #444; }

/* Right header */
#rzptRightHeader {
  padding: 18px 20px 0; border-bottom: 1px solid #f0f0f0;
  flex-shrink: 0;
}
#rzptMethodTitle {
  font-size: 15px; font-weight: 700; color: #1a1a1a;
  margin-bottom: 12px; padding-right: 28px;
}

/* Form area */
#rzptFormArea {
  flex: 1; overflow-y: auto; padding: 18px 20px;
}
#rzptFormArea::-webkit-scrollbar { width: 3px; }
#rzptFormArea::-webkit-scrollbar-thumb { background: #e0e0e0; border-radius: 2px; }

.rzpt-content { display: none; }
.rzpt-content.rzpt-active { display: block; }

/* Form elements */
.rzpt-group { margin-bottom: 14px; }
.rzpt-label {
  display: block; font-size: 11px; font-weight: 600;
  color: #818181; margin-bottom: 5px; text-transform: uppercase; letter-spacing: .5px;
}
.rzpt-input, .rzpt-select {
  width: 100%; box-sizing: border-box;
  border: 1px solid #c8c8c8; border-radius: 5px;
  padding: 10px 12px; font-size: 14px; color: #1a1a1a;
  outline: none; background: #fff;
  transition: border-color .15s, box-shadow .15s;
  font-family: inherit;
}
.rzpt-input:focus, .rzpt-select:focus {
  border-color: #3395ff;
  box-shadow: 0 0 0 3px rgba(51,149,255,.12);
}
.rzpt-row { display: flex; gap: 12px; }
.rzpt-row .rzpt-group { flex: 1; margin-bottom: 0; }

/* Card icons row */
.rzpt-card-icons {
  display: flex; gap: 6px; margin-bottom: 14px;
}
.rzpt-card-icon {
  height: 22px; border-radius: 3px; border: 1px solid #e8e8e8;
  background: #f8f8f8; display: flex; align-items: center; justify-content: center;
  padding: 2px 6px; font-size: 10px; font-weight: 700; color: #555;
}

/* UPI apps row */
.rzpt-upi-apps {
  display: flex; gap: 10px; margin-bottom: 14px; flex-wrap: wrap;
}
.rzpt-upi-app {
  display: flex; flex-direction: column; align-items: center; gap: 4px;
  font-size: 10px; color: #666; cursor: default;
}
.rzpt-upi-app-icon { font-size: 1.6rem; }

/* Test info */
.rzpt-testinfo {
  background: #fffbf0; border: 1px solid #f6c94c; border-radius: 5px;
  padding: 8px 12px; font-size: 11px; color: #8a6500;
  display: flex; gap: 6px; align-items: flex-start; margin-top: 14px;
}
.rzpt-testinfo-icon { flex-shrink: 0; font-size: 12px; margin-top: 1px; }

/* Bank grid */
.rzpt-bank-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 14px;
}
.rzpt-bank-item {
  border: 1px solid #e8e8e8; border-radius: 6px;
  padding: 8px 10px; font-size: 12px; color: #333; cursor: pointer;
  display: flex; align-items: center; gap: 6px;
  transition: border-color .15s, background .15s;
}
.rzpt-bank-item:hover, .rzpt-bank-item.rzpt-bank-sel {
  border-color: #3395ff; background: rgba(51,149,255,.05); color: #3395ff;
}
.rzpt-bank-item-icon { font-size: 1rem; }

/* Wallet grid */
.rzpt-wallet-grid {
  display: grid; grid-template-columns: repeat(3,1fr); gap: 8px; margin-bottom: 14px;
}
.rzpt-wallet-item {
  border: 1px solid #e8e8e8; border-radius: 6px; padding: 10px 6px;
  text-align: center; font-size: 11px; color: #444; cursor: pointer;
  transition: border-color .15s, background .15s;
}
.rzpt-wallet-item:hover, .rzpt-wallet-item.rzpt-wal-sel {
  border-color: #3395ff; background: rgba(51,149,255,.05);
}
.rzpt-wallet-icon { font-size: 1.4rem; display: block; margin-bottom: 4px; }

/* ── RIGHT FOOTER (Pay button) ─────────────────────────── */
#rzptRightFooter {
  padding: 14px 20px; border-top: 1px solid #f0f0f0;
  flex-shrink: 0;
}
#rzptPayBtn {
  width: 100%; background: #3395ff; color: #fff; border: none;
  border-radius: 6px; padding: 13px;
  font-size: 15px; font-weight: 700; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: background .15s, opacity .15s;
  font-family: inherit;
}
#rzptPayBtn:hover    { background: #1a85ff; }
#rzptPayBtn:disabled { opacity: .65; cursor: default; }

#rzptPaySecure {
  text-align: center; margin-top: 7px;
  font-size: 10px; color: #aaa;
  display: flex; align-items: center; justify-content: center; gap: 4px;
}

/* Spinner */
@keyframes rzpt-spin { to { transform: rotate(360deg); } }
.rzpt-spinner {
  width: 16px; height: 16px; flex-shrink: 0;
  border: 2px solid rgba(255,255,255,.35); border-top-color: #fff;
  border-radius: 50%; animation: rzpt-spin .7s linear infinite;
}

/* Button spinner for checkout button */
@keyframes btn-spin { to { transform: rotate(360deg); } }
.btn-spinner {
  display: inline-block; width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,.35); border-top-color: #fff;
  border-radius: 50%; animation: btn-spin .7s linear infinite;
  vertical-align: middle; margin-right: 8px;
}

/* Mobile: stack vertically */
@media (max-width: 520px) {
  #rzptModal { flex-direction: column; width: 96vw; height: auto; max-height: 92vh; }
  #rzptLeft { width: 100%; flex-direction: row; align-items: center;
              padding: 14px 16px; gap: 14px; border-radius: 0; }
  #rzptMerchant { border-bottom: none; padding: 0; flex: 1; display: flex; align-items: center; gap: 12px; }
  #rzptAmountLabel { display: none; }
  #rzptAmountLeft { font-size: 16px; }
  #rzptTestBadge { display: none; }
  #rzptMethods { display: flex; flex-direction: row; overflow-x: auto; padding: 0; white-space: nowrap; }
  .rzpt-method { flex-direction: column; gap: 2px; padding: 8px 12px;
                 border-left: none; border-bottom: 3px solid transparent; font-size: 10px; }
  .rzpt-method.rzpt-active { border-left-color: transparent; border-bottom-color: #3395ff; }
  #rzptBranding { display: none; }
  #rzptRight { min-height: 320px; }
}
</style>

<script>
(function () {

  /* ════════════════════════════════════════════════════════
     1. Payment card highlight (checkout page)
  ════════════════════════════════════════════════════════ */
  function selectCard(label) {
    document.querySelectorAll('.pm-checkout-card').forEach(function (c) { c.classList.remove('pm-selected'); });
    var card  = label.querySelector('.pm-checkout-card');
    var radio = label.querySelector('.pm-radio');
    if (card)  card.classList.add('pm-selected');
    if (radio) radio.checked = true;
  }
  document.querySelectorAll('.pm-checkout-label').forEach(function (lbl) {
    lbl.addEventListener('click', function () { selectCard(lbl); });
  });
  document.querySelectorAll('.pm-radio').forEach(function (r) {
    if (r.checked) {
      var lbl = r.closest('.pm-checkout-label');
      if (lbl && lbl.querySelector('.pm-checkout-card'))
        lbl.querySelector('.pm-checkout-card').classList.add('pm-selected');
    }
  });


  /* ════════════════════════════════════════════════════════
     2. Test Razorpay Modal
  ════════════════════════════════════════════════════════ */
  if (!window.RZP) return;

  var form            = document.getElementById('checkoutForm');
  var placeBtn        = document.getElementById('placeOrderBtn');
  var originalBtnHtml = placeBtn ? placeBtn.innerHTML : '';

  function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  function fmtAmt(paise) {
    var n = (paise || 0) / 100;
    return '₹' + n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  function randId() {
    var c = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', s = 'pay_test_';
    for (var i = 0; i < 14; i++) s += c[Math.floor(Math.random() * c.length)];
    return s;
  }

  /* ── method definitions ── */
  var methodDefs = [
    {
      id: 'card', icon: '💳', label: 'Credit / Debit Card',
      tab: 'Cards',
      html: function(amt) {
        return '<div class="rzpt-card-icons">' +
                 '<div class="rzpt-card-icon">VISA</div>' +
                 '<div class="rzpt-card-icon">MC</div>' +
                 '<div class="rzpt-card-icon">AMEX</div>' +
                 '<div class="rzpt-card-icon">RuPay</div>' +
               '</div>' +
               '<div class="rzpt-group">' +
                 '<label class="rzpt-label">Card Number</label>' +
                 '<input class="rzpt-input" placeholder="•••• •••• •••• ••••" maxlength="19">' +
               '</div>' +
               '<div class="rzpt-row">' +
                 '<div class="rzpt-group"><label class="rzpt-label">Expiry</label><input class="rzpt-input" placeholder="MM / YY" maxlength="7"></div>' +
                 '<div class="rzpt-group"><label class="rzpt-label">CVV</label><input class="rzpt-input" placeholder="•••" maxlength="4" type="password"></div>' +
               '</div>' +
               '<div class="rzpt-group" style="margin-top:12px"><label class="rzpt-label">Name on Card</label><input class="rzpt-input" placeholder="As printed on card"></div>' +
               rzptTestNote('Any test values work — no real transaction.');
      }
    },
    {
      id: 'upi', icon: '📱', label: 'UPI',
      tab: 'UPI',
      html: function(amt) {
        return '<div class="rzpt-upi-apps">' +
                 '<div class="rzpt-upi-app"><span class="rzpt-upi-app-icon">G</span><span>Google Pay</span></div>' +
                 '<div class="rzpt-upi-app"><span class="rzpt-upi-app-icon">📲</span><span>PhonePe</span></div>' +
                 '<div class="rzpt-upi-app"><span class="rzpt-upi-app-icon">₿</span><span>BHIM</span></div>' +
                 '<div class="rzpt-upi-app"><span class="rzpt-upi-app-icon">P</span><span>Paytm</span></div>' +
               '</div>' +
               '<div class="rzpt-group">' +
                 '<label class="rzpt-label">Enter UPI ID</label>' +
                 '<input class="rzpt-input" placeholder="yourname@upi" value="test@upi">' +
                 '<div style="font-size:11px;color:#aaa;margin-top:5px">e.g. mobileno@upi · name@okicici · name@ybl</div>' +
               '</div>' +
               rzptTestNote('Any UPI ID accepted in test mode.');
      }
    },
    {
      id: 'netbanking', icon: '🏦', label: 'Net Banking',
      tab: 'Net Banking',
      html: function(amt) {
        var banks = [
          ['🏛','SBI'], ['🔵','HDFC'], ['🔷','ICICI'],
          ['🟠','Axis'], ['🟡','Kotak'], ['🟣','PNB'],
          ['🟢','BOB'], ['🔴','Canara']
        ];
        var grid = banks.map(function(b) {
          return '<div class="rzpt-bank-item" onclick="rzptSelectBank(this)">' +
                 '<span class="rzpt-bank-item-icon">' + b[0] + '</span>' + b[1] + '</div>';
        }).join('');
        return '<div class="rzpt-bank-grid">' + grid + '</div>' +
               '<div class="rzpt-group">' +
                 '<label class="rzpt-label">All Banks</label>' +
                 '<select class="rzpt-select"><option value="">— or search all banks —</option>' +
                 '<option>State Bank of India</option><option>HDFC Bank</option>' +
                 '<option>ICICI Bank</option><option>Axis Bank</option>' +
                 '<option>Kotak Mahindra</option><option>Punjab National Bank</option>' +
                 '<option>Bank of Baroda</option><option>Canara Bank</option>' +
                 '<option>Union Bank</option><option>IndusInd Bank</option>' +
                 '</select></div>' +
               rzptTestNote('No real login required in test mode.');
      }
    },
    {
      id: 'wallet', icon: '👜', label: 'Wallets',
      tab: 'Wallets',
      html: function(amt) {
        var wallets = [
          ['💙','Paytm'], ['🟠','Amazon Pay'], ['🔵','Mobikwik'],
          ['🟣','Freecharge'], ['🔴','JioMoney'], ['🟢','Airtel']
        ];
        var grid = wallets.map(function(w) {
          return '<div class="rzpt-wallet-item" onclick="rzptSelectWallet(this)">' +
                 '<span class="rzpt-wallet-icon">' + w[0] + '</span>' + w[1] + '</div>';
        }).join('');
        return '<div class="rzpt-wallet-grid">' + grid + '</div>' +
               '<div class="rzpt-group"><label class="rzpt-label">Mobile Number</label>' +
               '<input class="rzpt-input" placeholder="10-digit registered mobile"></div>' +
               rzptTestNote('Any values accepted in test mode.');
      }
    }
  ];

  function rzptTestNote(msg) {
    return '<div class="rzpt-testinfo"><span class="rzpt-testinfo-icon">⚠️</span><span>TEST MODE — ' + msg + '</span></div>';
  }

  /* ── filter to enabled methods ── */
  var disabled      = RZP.disabled || [];
  var activeMethods = methodDefs.filter(function (m) { return disabled.indexOf(m.id) === -1; });
  if (!activeMethods.length) return;

  var amtStr    = fmtAmt(RZP.amount);
  var shortName = (RZP.name || 'Store').substring(0, 1).toUpperCase();

  /* ── build LEFT: method list ── */
  var methodListHtml = activeMethods.map(function (m, i) {
    return '<div class="rzpt-method' + (i === 0 ? ' rzpt-active' : '') + '" data-m="' + m.id + '">' +
           '<span class="rzpt-method-icon">' + m.icon + '</span>' +
           '<span>' + m.tab + '</span>' +
           '</div>';
  }).join('');

  /* ── build RIGHT: content panels ── */
  var contentPanels = activeMethods.map(function (m, i) {
    return '<div class="rzpt-content' + (i === 0 ? ' rzpt-active' : '') + '" data-m="' + m.id + '">' +
           m.html(amtStr) + '</div>';
  }).join('');

  var firstTitle = activeMethods[0].label;

  /* ── Razorpay SVG wordmark (simplified inline) ── */
  var rzpWordmark =
    '<svg width="62" height="14" viewBox="0 0 62 14" fill="none" xmlns="http://www.w3.org/2000/svg">' +
    '<text x="0" y="11" font-size="11" fill="rgba(255,255,255,0.55)" font-family="sans-serif" font-weight="700">razorpay</text>' +
    '</svg>';

  /* ── inject modal HTML ── */
  var html =
    '<div id="rzptOverlay">' +
    '<div id="rzptModal">' +

      /* LEFT */
      '<div id="rzptLeft">' +
        '<div id="rzptMerchant">' +
          '<div id="rzptAvatar">' + shortName + '</div>' +
          '<div id="rzptMerchantName">' + esc(RZP.name || 'Store') + '</div>' +
          '<div id="rzptAmountLabel">Order Amount</div>' +
          '<div id="rzptAmountLeft">' + amtStr + '</div>' +
          '<div id="rzptTestBadge">Test Mode</div>' +
        '</div>' +
        '<div id="rzptMethods">' + methodListHtml + '</div>' +
        '<div id="rzptBranding">' +
          '<div id="rzptBrandingText">Secured by <strong>Razorpay</strong></div>' +
        '</div>' +
      '</div>' +

      /* RIGHT */
      '<div id="rzptRight">' +
        '<button id="rzptClose" title="Close">&#215;</button>' +
        '<div id="rzptRightHeader">' +
          '<div id="rzptMethodTitle">' + esc(firstTitle) + '</div>' +
        '</div>' +
        '<div id="rzptFormArea">' + contentPanels + '</div>' +
        '<div id="rzptRightFooter">' +
          '<button id="rzptPayBtn">Pay ' + amtStr + '</button>' +
          '<div id="rzptPaySecure">🔒 Secured by Razorpay</div>' +
        '</div>' +
      '</div>' +

    '</div>' +
    '</div>';

  var tmp = document.createElement('div');
  tmp.innerHTML = html;
  document.body.appendChild(tmp.firstElementChild);

  /* ── method switching ── */
  function switchMethod(id) {
    document.querySelectorAll('.rzpt-method').forEach(function (m) { m.classList.remove('rzpt-active'); });
    document.querySelectorAll('.rzpt-content').forEach(function (c) { c.classList.remove('rzpt-active'); });
    var methodEl  = document.querySelector('.rzpt-method[data-m="' + id + '"]');
    var contentEl = document.querySelector('.rzpt-content[data-m="' + id + '"]');
    var def       = activeMethods.filter(function (m) { return m.id === id; })[0];
    if (methodEl)  methodEl.classList.add('rzpt-active');
    if (contentEl) contentEl.classList.add('rzpt-active');
    if (def) document.getElementById('rzptMethodTitle').textContent = def.label;
  }

  document.querySelectorAll('.rzpt-method').forEach(function (el) {
    el.addEventListener('click', function () { switchMethod(el.getAttribute('data-m')); });
  });

  /* ── bank / wallet selection helpers (called from inline onclick) ── */
  window.rzptSelectBank = function (el) {
    document.querySelectorAll('.rzpt-bank-item').forEach(function (b) { b.classList.remove('rzpt-bank-sel'); });
    el.classList.add('rzpt-bank-sel');
  };
  window.rzptSelectWallet = function (el) {
    document.querySelectorAll('.rzpt-wallet-item').forEach(function (w) { w.classList.remove('rzpt-wal-sel'); });
    el.classList.add('rzpt-wal-sel');
  };

  /* ── card number auto-space ── */
  document.addEventListener('input', function (e) {
    if (e.target.classList.contains('rzpt-input') && e.target.placeholder.includes('••••')) {
      var v = e.target.value.replace(/\D/g,'').substring(0,16);
      e.target.value = v.replace(/(.{4})/g,'$1 ').trim();
    }
  });

  /* ── open / close ── */
  function openModal() {
    document.getElementById('rzptOverlay').classList.add('rzpt-show');
    document.body.style.overflow = 'hidden';
  }
  function closeModal() {
    document.getElementById('rzptOverlay').classList.remove('rzpt-show');
    document.body.style.overflow = '';
    if (placeBtn) { placeBtn.disabled = false; placeBtn.innerHTML = originalBtnHtml; }
  }
  document.getElementById('rzptClose').addEventListener('click', closeModal);
  document.getElementById('rzptOverlay').addEventListener('click', function (e) {
    if (e.target.id === 'rzptOverlay') closeModal();
  });

  /* ── Pay button ── */
  document.getElementById('rzptPayBtn').addEventListener('click', function () {
    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="rzpt-spinner"></span> Processing…';
    setTimeout(function () {
      document.getElementById('rzpPaymentId').value = randId();
      closeModal();
      if (placeBtn) { placeBtn.disabled = true; placeBtn.innerHTML = '<span class="btn-spinner"></span>Placing order…'; }
      if (form) form.submit();
    }, 1100);
  });


  /* ════════════════════════════════════════════════════════
     3. Intercept checkout form submit
  ════════════════════════════════════════════════════════ */
  if (!form || !placeBtn) return;

  form.addEventListener('submit', function (e) {
    var sel = '';
    document.querySelectorAll('[name="payment_method"]').forEach(function (r) { if (r.checked) sel = r.value; });
    if (sel !== 'razorpay') return;  /* COD → normal submit */

    e.preventDefault();
    if (typeof form.checkValidity === 'function' && !form.checkValidity()) { form.reportValidity(); return; }

    placeBtn.disabled = true;
    placeBtn.innerHTML = '<span class="btn-spinner"></span>Opening payment…';
    setTimeout(openModal, 300);
  });

})();
</script>
