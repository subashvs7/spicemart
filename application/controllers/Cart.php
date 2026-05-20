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
            'SELECT * FROM categories WHERE status=1 ORDER BY parent_id, name'
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

        $result = $this->spice_model->validate_coupon($code, $subtotal);
        if ($result['valid']) {
            $this->session->set_userdata(SESS_HEAD.'_coupon', array(
                'code'     => $code,
                'type'     => $result['type'],
                'value'    => $result['value'],
                'discount' => $result['discount'],
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
        $shipping = $this->spice_model->get_shipping_charge($subtotal - $discount);
        $total    = $subtotal - $discount + $shipping;

        $addresses = $this->db->query(
            'SELECT * FROM addresses WHERE user_id=? ORDER BY is_default DESC', array($user_id)
        )->result_array();

        $razorpay_key = $this->spice_model->get_setting('razorpay_key_id');
        $errors       = array();

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            // collect address
            $use_saved = (int)$this->input->post('use_saved_address');
            if ($use_saved) {
                $addr_row = $this->db->query(
                    'SELECT * FROM addresses WHERE id=? AND user_id=?', array($use_saved,$user_id)
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

            $payment_method = $this->input->post('payment_method') ?: 'cod';

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

                $this->db->query(
                    'INSERT INTO orders (user_id,total_amount,shipping_charge,coupon_code,coupon_discount,shipping_address,payment_method,payment_status)
                     VALUES (?,?,?,?,?,?,?,?)',
                    array(
                        $user_id, $total, $shipping,
                        $coupon['code'] ?? '', $discount,
                        $shipping_address, $payment_method,
                        $payment_method === 'cod' ? 'pending' : 'pending',
                    )
                );
                $order_id = $this->db->insert_id();

                foreach ($items as $it) {
                    $price = $it['offer_price'] ?: $it['price'];
                    $this->db->query(
                        'INSERT INTO order_items (order_id,product_id,product_name,variant_label,quantity,unit_price) VALUES (?,?,?,?,?,?)',
                        array($order_id, $it['product_id'], $it['name'], $it['variant_label'] ?? '', $it['quantity'], $price)
                    );
                    $this->db->query(
                        'UPDATE products SET stock_qty=stock_qty-? WHERE id=?',
                        array($it['quantity'], $it['product_id'])
                    );
                }

                // Increment coupon usage
                if (!empty($coupon['code'])) {
                    $this->db->query(
                        'UPDATE coupons SET uses_count=uses_count+1 WHERE code=?', array($coupon['code'])
                    );
                }

                $this->db->trans_complete();

                if ($this->db->trans_status()) {
                    $this->spice_model->clear_cart();
                    $this->session->unset_userdata(SESS_HEAD.'_coupon');
                    redirect('order-success/'.$order_id);
                } else {
                    $errors[] = 'Order failed. Please try again.';
                }
            }
        }

        $data['js']          = 'checkout.inc';
        $data['items']       = $items;
        $data['subtotal']    = $subtotal;
        $data['discount']    = $discount;
        $data['coupon']      = $coupon;
        $data['shipping']    = $shipping;
        $data['total']       = $total;
        $data['addresses']   = $addresses;
        $data['razorpay_key']= $razorpay_key;
        $data['errors']      = $errors;

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
