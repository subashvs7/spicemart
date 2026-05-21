<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('spice_model');
        header('Content-Type: application/json');
    }

    // ── POS Sync Webhook ──────────────────────────────────────────
    // POST  /pos-sync
    // Headers: X-POS-Key: <api_key>   OR body param api_key=<key>
    // Body (JSON):
    //   { "type": "stock|price|coupon|availability",
    //     "items": [ { "product_code": "SKU01", "stock_qty": 50 }, … ] }
    //
    public function pos_sync()
    {
        // ── Authenticate ──────────────────────────────────────────
        $api_key = $this->input->get_request_header('X-POS-Key', TRUE);
        if (!$api_key) {
            $raw_body = json_decode(file_get_contents('php://input'), true);
            $api_key  = $raw_body['api_key'] ?? $this->input->post('api_key');
        }

        if (!$api_key) {
            http_response_code(401);
            echo json_encode(array('success'=>false,'error'=>'Missing API key'));
            return;
        }

        $key_row = $this->db->query(
            'SELECT * FROM pos_api_keys WHERE api_key=? AND status=1',
            array($api_key)
        )->row_array();

        if (!$key_row) {
            http_response_code(401);
            echo json_encode(array('success'=>false,'error'=>'Invalid or inactive API key'));
            return;
        }

        // ── Parse payload ─────────────────────────────────────────
        $raw  = file_get_contents('php://input');
        $body = json_decode($raw, true);
        if (!is_array($body)) $body = array();

        $sync_type = trim($body['type'] ?? $this->input->post('type') ?? '');
        $items     = $body['items'] ?? array();

        $valid_types = array('stock','price','coupon','availability');
        if (!in_array($sync_type, $valid_types)) {
            http_response_code(400);
            echo json_encode(array('success'=>false,'error'=>'Invalid type. Use: '.implode(', ',$valid_types)));
            return;
        }

        // Check permission on key
        $perm_map = array(
            'stock'        => 'sync_stock',
            'price'        => 'sync_price',
            'coupon'       => 'sync_coupon',
            'availability' => 'sync_avail',
        );
        if (empty($key_row[$perm_map[$sync_type]])) {
            http_response_code(403);
            echo json_encode(array('success'=>false,'error'=>'This API key does not have permission for '.$sync_type.' sync'));
            return;
        }

        // ── Create log entry ──────────────────────────────────────
        $this->db->query(
            'INSERT INTO pos_sync_logs (api_key_id,sync_type,source,records_sent,request_ip,payload_summary,status)
             VALUES (?,?,?,?,?,?,?)',
            array(
                $key_row['id'], $sync_type, 'webhook', count($items),
                $this->input->ip_address(),
                json_encode(array_slice($items, 0, 3)),
                'running'
            )
        );
        $log_id = $this->db->insert_id();

        // ── Process items ─────────────────────────────────────────
        $updated = 0; $failed = 0; $errors = array();

        switch ($sync_type) {

            // ── Stock sync ────────────────────────────────────────
            case 'stock':
                foreach ($items as $idx => $item) {
                    $code = trim($item['product_code'] ?? $item['sku'] ?? '');
                    if (!$code) { $failed++; $errors[] = "Row $idx: missing product_code"; continue; }

                    $qty = (int)($item['stock_qty'] ?? $item['qty'] ?? 0);

                    // Try product_code first, then SKU in variants
                    $this->db->query(
                        'UPDATE products SET stock_qty=? WHERE product_code=?', array($qty, $code)
                    );
                    $prod_hit = $this->db->affected_rows();

                    // Also update variant-level stock if variant sku provided
                    if (!empty($item['sku'])) {
                        $this->db->query(
                            'UPDATE product_variants SET stock_qty=? WHERE sku=?',
                            array($qty, $item['sku'])
                        );
                    }

                    if ($prod_hit > 0) $updated++;
                    else { $failed++; $errors[] = "Row $idx: product_code '$code' not found"; }
                }
                break;

            // ── Price sync ────────────────────────────────────────
            case 'price':
                foreach ($items as $idx => $item) {
                    $code = trim($item['product_code'] ?? '');
                    if (!$code) { $failed++; $errors[] = "Row $idx: missing product_code"; continue; }

                    $set = array(); $params = array();
                    if (isset($item['price']))       { $set[] = 'price=?';       $params[] = (float)$item['price']; }
                    if (isset($item['offer_price'])) { $set[] = 'offer_price=?'; $params[] = (float)$item['offer_price'] ?: null; }
                    if (isset($item['gst']))         { $set[] = 'gst=?';         $params[] = (float)$item['gst']; }

                    if (empty($set)) { $failed++; $errors[] = "Row $idx: no price fields provided"; continue; }

                    $params[] = $code;
                    $this->db->query(
                        'UPDATE products SET '.implode(',',$set).' WHERE product_code=?', $params
                    );
                    if ($this->db->affected_rows() > 0) $updated++;
                    else { $failed++; $errors[] = "Row $idx: product_code '$code' not found"; }
                }
                break;

            // ── Coupon / discount sync ────────────────────────────
            case 'coupon':
                foreach ($items as $idx => $item) {
                    $code = strtoupper(trim($item['code'] ?? ''));
                    if (!$code) { $failed++; $errors[] = "Row $idx: missing coupon code"; continue; }

                    $type    = in_array($item['type'] ?? '', array('percent','flat')) ? $item['type'] : 'percent';
                    $value   = (float)($item['value'] ?? 0);
                    $min_ord = (float)($item['min_order'] ?? 0);
                    $max_dis = !empty($item['max_discount']) ? (float)$item['max_discount'] : null;
                    $expires = !empty($item['expires_at']) ? $item['expires_at'] : null;
                    $status  = isset($item['status']) ? (int)$item['status'] : 1;

                    $this->db->query(
                        'INSERT INTO coupons (code,type,value,min_order,max_discount,expires_at,status)
                         VALUES (?,?,?,?,?,?,?)
                         ON DUPLICATE KEY UPDATE type=?,value=?,min_order=?,max_discount=?,expires_at=?,status=?',
                        array($code,$type,$value,$min_ord,$max_dis,$expires,$status,
                              $type,$value,$min_ord,$max_dis,$expires,$status)
                    );
                    $updated++;
                }
                break;

            // ── Availability sync ─────────────────────────────────
            case 'availability':
                foreach ($items as $idx => $item) {
                    $code = trim($item['product_code'] ?? '');
                    if (!$code) { $failed++; $errors[] = "Row $idx: missing product_code"; continue; }

                    $avail = isset($item['available'])
                           ? (int)(bool)$item['available']
                           : (isset($item['status']) ? (int)$item['status'] : 1);

                    $this->db->query(
                        'UPDATE products SET status=? WHERE product_code=?', array($avail, $code)
                    );
                    if ($this->db->affected_rows() > 0) $updated++;
                    else { $failed++; $errors[] = "Row $idx: product_code '$code' not found"; }
                }
                break;
        }

        // ── Finalise log ──────────────────────────────────────────
        $final = $failed === 0 ? 'success' : ($updated > 0 ? 'partial' : 'failed');
        $this->db->query(
            'UPDATE pos_sync_logs SET status=?,records_updated=?,records_failed=?,
             error_message=?,completed_at=NOW() WHERE id=?',
            array($final, $updated, $failed, $errors ? implode('; ', array_slice($errors,0,5)) : null, $log_id)
        );
        $this->db->query(
            'UPDATE pos_api_keys SET last_sync_at=NOW() WHERE id=?', array($key_row['id'])
        );

        // ── Respond ───────────────────────────────────────────────
        http_response_code($final === 'failed' ? 422 : 200);
        echo json_encode(array(
            'success'  => $final !== 'failed',
            'status'   => $final,
            'log_id'   => $log_id,
            'updated'  => $updated,
            'failed'   => $failed,
            'errors'   => array_slice($errors, 0, 10),
        ));
    }

    // ── Fazaa / Isaad Membership Verification ────────────────────
    // POST  /ajax/fazaa-verify
    // Body: program=fazaa|isaad  &  member_no=XXXX
    //
    public function fazaa_verify()
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            http_response_code(405); echo json_encode(array('success'=>false,'message'=>'Method not allowed')); return;
        }

        $program   = trim($this->input->post('program'));
        $member_no = trim($this->input->post('member_no'));

        if (!in_array($program, array('fazaa','isaad'), true) || $member_no === '') {
            echo json_encode(array('success'=>false,'message'=>'Invalid program or member number.')); return;
        }

        $cfg = $this->db->get_where('fazaa_settings', array('program'=>$program, 'enabled'=>1))->row_array();
        if (!$cfg) {
            echo json_encode(array('success'=>false,'message'=>ucfirst($program).' discount is not available.')); return;
        }

        // ── External API verification (if configured) ─────────────
        $verified = false;
        if (!empty($cfg['api_url']) && !empty($cfg['api_key'])) {
            $ch = curl_init($cfg['api_url']);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => array('Content-Type: application/json', 'Authorization: Bearer '.$cfg['api_key']),
                CURLOPT_POSTFIELDS     => json_encode(array('member_no'=>$member_no, 'program'=>$program)),
            ));
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code === 200) {
                $body = json_decode($resp, true);
                $verified = !empty($body['valid']);
            }
        } else {
            // No external API configured — accept any non-empty member number (demo / internal use)
            $verified = (strlen($member_no) >= 4);
        }

        if (!$verified) {
            echo json_encode(array('success'=>false,'message'=>'Membership number could not be verified.')); return;
        }

        // ── OTP flow ──────────────────────────────────────────────
        if (!empty($cfg['otp_enabled'])) {
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $this->session->set_userdata(SESS_HEAD.'_fazaa_otp',     $otp);
            $this->session->set_userdata(SESS_HEAD.'_fazaa_otp_prog',$program);
            $this->session->set_userdata(SESS_HEAD.'_fazaa_otp_mem', $member_no);
            $this->session->set_userdata(SESS_HEAD.'_fazaa_otp_exp', time() + 300);
            // In production, send OTP via SMS/email — here we just return otp_required
            echo json_encode(array('success'=>true,'otp_required'=>true,'message'=>'OTP sent to your registered mobile number.')); return;
        }

        // ── No OTP required — store verified discount in session ───
        $discount_pct = (float)$cfg['discount_pct'];
        $max_discount = (float)$cfg['max_discount'];
        $this->session->set_userdata(SESS_HEAD.'_fazaa', array(
            'program'      => $program,
            'member_no'    => $member_no,
            'discount_pct' => $discount_pct,
            'max_discount' => $max_discount,
            'label'        => $cfg['label'],
        ));

        echo json_encode(array(
            'success'      => true,
            'otp_required' => false,
            'program'      => $program,
            'label'        => $cfg['label'],
            'discount_pct' => $discount_pct,
            'max_discount' => $max_discount,
            'message'      => $cfg['label'].' membership verified! '.number_format($discount_pct,0).'% discount applied.',
        ));
    }

    // ── Fazaa / Isaad OTP Confirmation ───────────────────────────
    // POST  /ajax/fazaa-otp-confirm
    // Body: otp=XXXXXX
    //
    public function fazaa_otp_confirm()
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            http_response_code(405); echo json_encode(array('success'=>false,'message'=>'Method not allowed')); return;
        }

        $otp_input = trim($this->input->post('otp'));
        $otp_saved = $this->session->userdata(SESS_HEAD.'_fazaa_otp');
        $otp_exp   = (int)$this->session->userdata(SESS_HEAD.'_fazaa_otp_exp');
        $program   = $this->session->userdata(SESS_HEAD.'_fazaa_otp_prog');
        $member_no = $this->session->userdata(SESS_HEAD.'_fazaa_otp_mem');

        if (!$otp_saved || !$program || time() > $otp_exp) {
            echo json_encode(array('success'=>false,'message'=>'OTP expired. Please verify your membership again.')); return;
        }
        if ($otp_input !== $otp_saved) {
            echo json_encode(array('success'=>false,'message'=>'Invalid OTP. Please try again.')); return;
        }

        // Clear OTP session keys
        $this->session->unset_userdata(SESS_HEAD.'_fazaa_otp');
        $this->session->unset_userdata(SESS_HEAD.'_fazaa_otp_prog');
        $this->session->unset_userdata(SESS_HEAD.'_fazaa_otp_mem');
        $this->session->unset_userdata(SESS_HEAD.'_fazaa_otp_exp');

        $cfg = $this->db->get_where('fazaa_settings', array('program'=>$program, 'enabled'=>1))->row_array();
        if (!$cfg) {
            echo json_encode(array('success'=>false,'message'=>'Program configuration error.')); return;
        }

        $discount_pct = (float)$cfg['discount_pct'];
        $max_discount = (float)$cfg['max_discount'];
        $this->session->set_userdata(SESS_HEAD.'_fazaa', array(
            'program'      => $program,
            'member_no'    => $member_no,
            'discount_pct' => $discount_pct,
            'max_discount' => $max_discount,
            'label'        => $cfg['label'],
        ));

        echo json_encode(array(
            'success'      => true,
            'program'      => $program,
            'label'        => $cfg['label'],
            'discount_pct' => $discount_pct,
            'max_discount' => $max_discount,
            'message'      => $cfg['label'].' membership verified! '.number_format($discount_pct,0).'% discount applied.',
        ));
    }

    // ── Remove Fazaa discount from session ───────────────────────
    public function fazaa_remove()
    {
        $this->session->unset_userdata(SESS_HEAD.'_fazaa');
        echo json_encode(array('success'=>true));
    }
}
