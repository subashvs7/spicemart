<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart extends CI_Controller {

    private function _front_base(&$data)
    {
        $logged = $this->session->userdata(SESS_HEAD.'_logged_in');
        $data['is_logged_in']   = (bool)$logged;
        $data['user_name']      = $this->session->userdata(SESS_HEAD.'_user_name') ?: '';
        $data['user_role']      = $this->session->userdata(SESS_HEAD.'_user_role') ?: '';
        $data['cart_count']     = $this->spice_model->get_cart_count();
        $data['wishlist_count'] = $this->spice_model->get_wishlist_count();
        $data['all_categories'] = $this->db->query(
            'SELECT * FROM categories WHERE status=1 AND deleted_at IS NULL ORDER BY parent_id, name'
        )->result_array();
        $data['app_settings'] = $this->spice_model->get_all_settings();
    }

    public function index()
    {
        $this->_front_base($data);

        $items    = $this->spice_model->get_cart_items();
        $subtotal = 0;
        foreach ($items as $it) {
            $price     = $it['offer_price'] ?: $it['price'];
            $subtotal += $price * $it['quantity'];
        }

        $coupon   = $this->session->userdata(SESS_HEAD.'_coupon') ?: array();
        $discount = isset($coupon['discount']) ? (float)$coupon['discount'] : 0;
        $shipping = $this->spice_model->get_shipping_charge($subtotal - $discount);
        $total    = $subtotal - $discount + $shipping;

        $shipping_free = (float)$this->spice_model->get_setting('free_shipping_above') ?: 499;

        $data['js']                  = 'cart.inc';
        $data['cart_items']          = $items;
        $data['subtotal']            = $subtotal;
        $data['discount']            = $discount;
        $data['coupon']              = $coupon;
        $data['shipping']            = $shipping;
        $data['grand_total']         = $total;
        $data['shipping_free']       = $shipping_free;
        $data['shipping_charge_raw'] = (float)$this->spice_model->get_setting('standard_charge') ?: 60;

        $this->load->view('inc/front-header', $data);
        $this->load->view('page/cart', $data);
        $this->load->view('inc/front-footer', $data);
    }

    public function ajax_cart()
    {
        header('Content-Type: application/json');
        $this->output->set_content_type('application/json');

        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            echo json_encode(array('success'=>false,'message'=>'Invalid request'));
            return;
        }

        $input  = json_decode(file_get_contents('php://input'), true) ?: array();
        $action = $input['action'] ?? '';

        if (!$this->session->userdata(SESS_HEAD.'_logged_in')) {
            echo json_encode(array('success'=>false,'message'=>'Please login to manage your cart.'));
            return;
        }

        if ($action === 'add') {
            $product_id    = (int)($input['product_id'] ?? 0);
            $qty           = max(1, (int)($input['qty'] ?? 1));
            $variant_id    = !empty($input['variant_id'])    ? (int)$input['variant_id']           : null;
            $variant_label = !empty($input['variant_label']) ? trim($input['variant_label'])        : '';
            $product       = $this->db->query(
                'SELECT id,stock_qty FROM products WHERE id=? AND status=1', array($product_id)
            )->row();
            if (!$product) {
                echo json_encode(array('success'=>false,'message'=>'Product not found.'));
                return;
            }
            if ($product->stock_qty < 1) {
                echo json_encode(array('success'=>false,'message'=>'Product is out of stock.'));
                return;
            }
            $this->spice_model->add_to_cart($product_id, $qty, $variant_id, $variant_label);
            echo json_encode(array(
                'success'    => true,
                'message'    => 'Added to cart!',
                'cart_count' => $this->spice_model->get_cart_count(),
            ));
        } elseif ($action === 'update') {
            $cart_id = (int)($input['cart_id'] ?? 0);
            $qty     = max(1, (int)($input['qty'] ?? 1));
            $this->spice_model->update_cart_item($cart_id, $qty);
            echo json_encode(array('success'=>true,'cart_count'=>$this->spice_model->get_cart_count()));
        } elseif ($action === 'remove') {
            $cart_id = (int)($input['cart_id'] ?? 0);
            $this->spice_model->remove_cart_item($cart_id);
            echo json_encode(array('success'=>true,'cart_count'=>$this->spice_model->get_cart_count()));
        } else {
            echo json_encode(array('success'=>false,'message'=>'Unknown action.'));
        }
    }

    public function apply_coupon()
    {
        header('Content-Type: application/json');
        if (!$this->session->userdata(SESS_HEAD.'_logged_in')) {
            echo json_encode(array('success'=>false,'message'=>'Please login first.'));
            return;
        }

        $input    = json_decode(file_get_contents('php://input'), true) ?: array();
        $code     = strtoupper(trim($input['code'] ?? ''));
        $subtotal = (float)($input['subtotal'] ?? 0);

        $user_id = (int)$this->session->userdata(SESS_HEAD.'_user_id');
        $result  = $this->spice_model->validate_coupon($code, $subtotal, $user_id);
        if ($result['valid']) {
            $this->session->set_userdata(SESS_HEAD.'_coupon', array(
                'code'      => $code,
                'coupon_id' => $result['coupon_id'],
                'type'      => $result['type'],
                'value'     => $result['value'],
                'discount'  => $result['discount'],
            ));
            echo json_encode(array('success'=>true,'discount'=>$result['discount'],'message'=>'Coupon applied! You saved ₹'.number_format($result['discount'],2)));
        } else {
            echo json_encode(array('success'=>false,'message'=>$result['message']));
        }
    }

    public function remove_coupon()
    {
        $this->session->unset_userdata(SESS_HEAD.'_coupon');
        echo json_encode(array('success'=>true));
    }

    public function checkout()
    {
        if (!$this->session->userdata(SESS_HEAD.'_logged_in')) redirect('login');
        $this->_front_base($data);

        $user_id  = (int)$this->session->userdata(SESS_HEAD.'_user_id');
        $items    = $this->spice_model->get_cart_items();

        if (empty($items)) redirect('cart');

        $subtotal = 0;
        foreach ($items as $it) {
            $price     = $it['offer_price'] ?: $it['price'];
            $subtotal += $price * $it['quantity'];
        }

        $coupon   = $this->session->userdata(SESS_HEAD.'_coupon') ?: array();
        $discount = isset($coupon['discount']) ? (float)$coupon['discount'] : 0;

        $fazaa_sess     = $this->session->userdata(SESS_HEAD.'_fazaa') ?: array();
        $fazaa_discount = 0;
        if (!empty($fazaa_sess['discount_pct'])) {
            $raw = round(($subtotal - $discount) * ((float)$fazaa_sess['discount_pct'] / 100), 2);
            $fazaa_discount = min($raw, (float)($fazaa_sess['max_discount'] ?? PHP_INT_MAX));
        }

        $shipping = $this->spice_model->get_shipping_charge($subtotal - $discount - $fazaa_discount);
        $total    = $subtotal - $discount - $fazaa_discount + $shipping;

        $addresses = $this->db->query(
            'SELECT * FROM addresses WHERE user_id=? AND deleted_at IS NULL ORDER BY is_default DESC', array($user_id)
        )->result_array();

        $razorpay_key = $this->spice_model->get_setting('razorpay_key_id');

        // Load payment method toggles from shipping_settings
        $ps_raw = $this->db->query(
            "SELECT key_name, key_value FROM shipping_settings WHERE key_name LIKE 'payment_%'"
        )->result_array();
        $pay_settings = array(
            'payment_cod_enabled'        => '1',
            'payment_razorpay_enabled'   => '1',
            'payment_cards_enabled'      => '1',
            'payment_netbanking_enabled' => '1',
            'payment_wallets_enabled'    => '1',
            'payment_upi_enabled'        => '1',
            'payment_applepay_enabled'   => '0',
        );
        foreach ($ps_raw as $r) $pay_settings[$r['key_name']] = $r['key_value'];

        $errors = array();

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            // collect address
            $use_saved = (int)$this->input->post('use_saved_address');
            if ($use_saved) {
                $addr_row = $this->db->query(
                    'SELECT * FROM addresses WHERE id=? AND user_id=? AND deleted_at IS NULL', array($use_saved,$user_id)
                )->row_array();
                $shipping_address = $addr_row
                    ? $addr_row['name']."\n".$addr_row['phone']."\n".$addr_row['address_line']."\n".$addr_row['city'].', '.$addr_row['state'].' - '.$addr_row['pincode']
                    : '';
                if (!$shipping_address) $errors[] = 'Please select a valid address.';
            } else {
                $f_name  = trim($this->input->post('full_name') ?: '');
                $f_phone = trim($this->input->post('phone') ?: '');
                $f_addr  = trim($this->input->post('address') ?: '');
                $f_city  = trim($this->input->post('city') ?: '');
                $f_state = trim($this->input->post('state') ?: '');
                $f_pin   = trim($this->input->post('pincode') ?: '');
                if (!$f_name || !$f_phone || !$f_addr || !$f_city || !$f_pin) {
                    $errors[] = 'Please fill in all shipping address fields.';
                }
                $shipping_address = "$f_name\n$f_phone\n$f_addr\n$f_city, $f_state - $f_pin";

                if ($this->input->post('save_address')) {
                    $this->db->query(
                        'INSERT INTO addresses (user_id,name,phone,address_line,city,state,pincode) VALUES (?,?,?,?,?,?,?)',
                        array($user_id,$f_name,$f_phone,$f_addr,$f_city,$f_state,$f_pin)
                    );
                }
            }

            $payment_method_raw = $this->input->post('payment_method') ?: 'cod';
            $payment_method     = (strpos($payment_method_raw, 'razorpay') === 0) ? 'razorpay' : $payment_method_raw;
            $rzp_payment_id     = trim($this->input->post('rzp_payment_id') ?: '');

            // Razorpay requires a completed payment before we create the order
            if ($payment_method === 'razorpay' && !$rzp_payment_id) {
                $errors[] = 'Please complete the online payment to proceed.';
            }

            // Stock check
            foreach ($items as $it) {
                $stock = (int)$this->db->query(
                    'SELECT stock_qty FROM products WHERE id=?', array($it['product_id'])
                )->row()->stock_qty;
                if ($stock < $it['quantity']) {
                    $errors[] = htmlspecialchars($it['name']).' has insufficient stock.';
                }
            }

            if (empty($errors)) {
                $this->db->trans_start();

                $pay_status = ($payment_method === 'razorpay' && $rzp_payment_id) ? 'paid' : 'pending';
                $txn_id     = ($payment_method === 'razorpay') ? $rzp_payment_id : '';

                $this->db->query(
                    'INSERT INTO orders (user_id,total_amount,shipping_charge,coupon_code,coupon_discount,fazaa_program,fazaa_member_no,fazaa_discount,shipping_address,payment_method,payment_status,transaction_id)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
                    array(
                        $user_id, $total, $shipping,
                        $coupon['code'] ?? '', $discount,
                        $fazaa_sess['program'] ?? null, $fazaa_sess['member_no'] ?? null, $fazaa_discount,
                        $shipping_address, $payment_method,
                        $pay_status, $txn_id,
                    )
                );
                $order_id = $this->db->insert_id();

                foreach ($items as $it) {
                    $price = $it['offer_price'] ?: $it['price'];
                    $this->db->query(
                        'INSERT INTO order_items (order_id,product_id,product_name,variant_label,sku,quantity,unit_price) VALUES (?,?,?,?,?,?,?)',
                        array($order_id, $it['product_id'], $it['name'], $it['variant_label'] ?? '', $it['sku'] ?? '', $it['quantity'], $price)
                    );
                    $this->db->query(
                        'UPDATE products SET stock_qty=stock_qty-? WHERE id=?',
                        array($it['quantity'], $it['product_id'])
                    );
                }

                // Increment global coupon usage + record per-user usage
                if (!empty($coupon['code'])) {
                    $this->db->query(
                        'UPDATE coupons SET uses_count=uses_count+1 WHERE code=?', array($coupon['code'])
                    );
                    if (!empty($coupon['coupon_id'])) {
                        $this->db->query(
                            'INSERT INTO coupon_usage (coupon_id,user_id,order_id) VALUES (?,?,?)',
                            array($coupon['coupon_id'], $user_id, $order_id)
                        );
                    }
                }

                // Award loyalty points for this order
                $earn_per  = (float)$this->spice_model->get_setting('loyalty_earn_per')  ?: 10;
                $earn_rate = (float)$this->spice_model->get_setting('loyalty_earn_rate') ?: 1;
                $pts = (int)floor(($total / $earn_per) * $earn_rate);
                if ($pts > 0) {
                    $this->spice_model->add_loyalty_points(
                        $user_id, $pts, 'earned', 'order', $order_id,
                        'Order #'.str_pad($order_id, 5, '0', STR_PAD_LEFT)
                    );
                }

                // Track Fazaa/Isaad usage
                if ($fazaa_discount > 0 && !empty($fazaa_sess['program'])) {
                    $this->db->query(
                        'INSERT INTO fazaa_usages (program,member_no,user_id,order_id,discount_pct,discount_amt,order_total,verified_at) VALUES (?,?,?,?,?,?,?,NOW())',
                        array($fazaa_sess['program'], $fazaa_sess['member_no'], $user_id, $order_id, $fazaa_sess['discount_pct'], $fazaa_discount, $total)
                    );
                }

                $this->db->trans_complete();

                if ($this->db->trans_status()) {
                    $this->spice_model->clear_cart();
                    $this->session->unset_userdata(SESS_HEAD.'_coupon');
                    $this->session->unset_userdata(SESS_HEAD.'_fazaa');
                    redirect('order-success/'.$order_id);
                } else {
                    $errors[] = 'Order failed. Please try again.';
                }
            }
        }

        $fazaa_programs = $this->db->query(
            'SELECT program, label, discount_pct, max_discount, min_order FROM fazaa_settings WHERE enabled=1 ORDER BY id'
        )->result_array();

        $data['js']              = 'checkout.inc';
        $data['items']           = $items;
        $data['subtotal']        = $subtotal;
        $data['discount']        = $discount;
        $data['coupon']          = $coupon;
        $data['fazaa_programs']  = $fazaa_programs;
        $data['fazaa_sess']      = $fazaa_sess;
        $data['fazaa_discount']  = $fazaa_discount;
        $data['shipping']        = $shipping;
        $data['total']           = $total;
        $data['addresses']       = $addresses;
        $data['razorpay_key']    = $razorpay_key;
        $data['pay_settings']    = $pay_settings;
        $data['errors']          = $errors;

        $this->load->view('inc/front-header', $data);
        $this->load->view('page/checkout', $data);
        $this->load->view('inc/front-footer', $data);
    }

    public function order_success($order_id = 0)
    {
        if (!$this->session->userdata(SESS_HEAD.'_logged_in')) redirect('login');
        $this->_front_base($data);

        $user_id  = (int)$this->session->userdata(SESS_HEAD.'_user_id');
        $order_id = (int)$order_id;

        $order = $this->db->query(
            'SELECT o.*, u.name AS customer_name
             FROM orders o JOIN users u ON u.id = o.user_id
             WHERE o.id=? AND o.user_id=?', array($order_id, $user_id)
        )->row_array();

        if (!$order) redirect('home');

        $items = $this->db->query(
            'SELECT oi.*, p.image FROM order_items oi
             JOIN products p ON p.id=oi.product_id
             WHERE oi.order_id=?', array($order_id)
        )->result_array();

        $data['js']       = 'order-success.inc';
        $data['order']    = $order;
        $data['items']    = $items;
        $data['order_id'] = $order_id;

        $this->load->view('inc/front-header', $data);
        $this->load->view('page/order-success', $data);
        $this->load->view('inc/front-footer', $data);
    }
}
