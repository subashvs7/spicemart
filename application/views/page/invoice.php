<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Invoice #<?php echo str_pad($order['id'],5,'0',STR_PAD_LEFT); ?> | SpiceMart</title>
  <style>
    body { font-family: Arial, sans-serif; font-size: 13px; color: #333; margin: 0; padding: 20px; }
    .invoice-wrap { max-width: 750px; margin: 0 auto; padding: 30px; border: 1px solid #ddd; }
    .invoice-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 2px solid #FF6B35; padding-bottom: 20px; }
    .brand-name { font-size: 22px; font-weight: 700; color: #FF6B35; }
    .invoice-no { text-align: right; }
    .invoice-no h2 { margin: 0; font-size: 18px; color: #555; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    table th { background: #FF6B35; color: #fff; padding: 8px 10px; text-align: left; }
    table td { padding: 8px 10px; border-bottom: 1px solid #f0f0f0; }
    .totals { width: 260px; margin-left: auto; }
    .totals td { padding: 6px 10px; }
    .totals .grand { font-weight: 700; font-size: 15px; color: #FF6B35; border-top: 2px solid #FF6B35; }
    .footer-note { margin-top: 30px; padding-top: 15px; border-top: 1px solid #eee; font-size: 11px; color: #888; text-align: center; }
    @media print { .no-print { display: none; } body { padding: 0; } .invoice-wrap { border: none; } }
  </style>
</head>
<body>

<div class="no-print" style="margin-bottom:20px;max-width:750px;margin-left:auto;margin-right:auto">
  <button onclick="window.print()" style="background:#FF6B35;color:#fff;border:none;padding:8px 20px;border-radius:6px;cursor:pointer;font-size:14px">
    🖨️ Print Invoice
  </button>
  <a href="javascript:history.back()" style="margin-left:10px;color:#555;font-size:13px">← Go Back</a>
</div>

<div class="invoice-wrap">
  <div class="invoice-header">
    <div>
      <div class="brand-name">🌶️ SpiceMart</div>
      <div style="color:#888;font-size:12px;margin-top:4px">Pure &amp; Natural Spices</div>
      <div style="margin-top:10px;font-size:12px;color:#555">
        123 Spice Bazaar, T. Nagar<br>
        Chennai – 600 017, Tamil Nadu<br>
        GSTIN: 33AABCS1234A1Z5
      </div>
    </div>
    <div class="invoice-no">
      <h2>TAX INVOICE</h2>
      <div style="font-size:14px;margin-top:6px"><strong>#<?php echo str_pad($order['id'],5,'0',STR_PAD_LEFT); ?></strong></div>
      <div style="color:#888;font-size:12px;margin-top:4px">
        Date: <?php echo date('d M Y', strtotime($order['created_at'])); ?>
      </div>
      <div style="margin-top:6px">
        <?php echo '<span style="padding:3px 8px;border-radius:4px;font-size:11px;font-weight:700;background:'.
          ($order['payment_status']==='paid'?'#28a745':'#ffc107').';color:'.
          ($order['payment_status']==='paid'?'#fff':'#333').'">'.
          strtoupper($order['payment_status']).'</span>'; ?>
      </div>
    </div>
  </div>

  <div style="display:flex;justify-content:space-between;margin-bottom:20px">
    <div>
      <strong>Bill To:</strong><br>
      <div style="margin-top:6px">
        <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
        <?php echo htmlspecialchars($order['email']); ?><br>
        <?php if ($order['customer_phone']): ?>
          <?php echo htmlspecialchars($order['customer_phone']); ?><br>
        <?php endif; ?>
        <div style="white-space:pre-line;margin-top:4px;color:#555;font-size:12px">
          <?php echo htmlspecialchars($order['shipping_address']); ?>
        </div>
      </div>
    </div>
    <div style="text-align:right">
      <strong>Payment Method:</strong><br>
      <div style="margin-top:6px"><?php echo strtoupper($order['payment_method']); ?></div>
      <?php if ($order['transaction_id']): ?>
        <div style="font-size:12px;color:#888">Txn: <?php echo htmlspecialchars($order['transaction_id']); ?></div>
      <?php endif; ?>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Product</th>
        <th style="text-align:right">Unit Price</th>
        <th style="text-align:center">Qty</th>
        <th style="text-align:right">Amount</th>
      </tr>
    </thead>
    <tbody>
      <?php $subtotal = 0; foreach ($items as $i => $it):
        $amt = (float)$it['unit_price'] * (int)$it['quantity'];
        $subtotal += $amt;
      ?>
      <tr>
        <td><?php echo $i+1; ?></td>
        <td><?php echo htmlspecialchars($it['product_name']); ?></td>
        <td style="text-align:right">₹<?php echo number_format((float)$it['unit_price'],2); ?></td>
        <td style="text-align:center"><?php echo $it['quantity']; ?></td>
        <td style="text-align:right">₹<?php echo number_format($amt,2); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <table class="totals">
    <tr>
      <td style="color:#555">Subtotal</td>
      <td style="text-align:right">₹<?php echo number_format($subtotal,2); ?></td>
    </tr>
    <?php if (!empty($order['coupon_code'])): ?>
    <tr>
      <td style="color:#555">Coupon (<?php echo htmlspecialchars($order['coupon_code']); ?>)</td>
      <td style="text-align:right;color:green">-₹<?php echo number_format((float)$order['coupon_discount'],2); ?></td>
    </tr>
    <?php endif; ?>
    <tr>
      <td style="color:#555">Shipping</td>
      <td style="text-align:right">
        <?php echo $order['shipping_charge'] > 0 ? '₹'.number_format((float)$order['shipping_charge'],2) : 'FREE'; ?>
      </td>
    </tr>
    <tr class="grand">
      <td>Grand Total</td>
      <td style="text-align:right">₹<?php echo number_format((float)$order['total_amount'],2); ?></td>
    </tr>
  </table>

  <div class="footer-note">
    This is a computer-generated invoice and does not require a signature.<br>
    Thank you for shopping with SpiceMart! For queries: support@spicemart.in
  </div>
</div>

</body>
</html>
