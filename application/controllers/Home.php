<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

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

        $banners = $this->db->query(
            'SELECT * FROM banners WHERE status=1 ORDER BY sort_order'
        )->result_array();

        $categories = $this->db->query(
            'SELECT * FROM categories WHERE status=1 AND parent_id IS NULL ORDER BY id LIMIT 8'
        )->result_array();

        $featured = $this->db->query(
            'SELECT p.*, c.name AS cat_name,
                    COALESCE(AVG(r.rating),0) AS avg_rating
             FROM products p
             JOIN categories c ON c.id=p.category_id
             LEFT JOIN reviews r ON r.product_id=p.id
             WHERE p.status=1 AND p.is_featured=1
             GROUP BY p.id ORDER BY p.id DESC LIMIT 8'
        )->result_array();

        $products = $this->db->query(
            'SELECT p.*, c.name AS cat_name,
                    COALESCE(AVG(r.rating),0) AS avg_rating
             FROM products p
             JOIN categories c ON c.id=p.category_id
             LEFT JOIN reviews r ON r.product_id=p.id
             WHERE p.status=1
             GROUP BY p.id ORDER BY p.id DESC LIMIT 8'
        )->result_array();

        $why_choose_us = $this->db->query(
            'SELECT * FROM why_choose_us WHERE status=1 ORDER BY sort_order, id'
        )->result_array();

        $testimonials = $this->db->query(
            'SELECT * FROM testimonials WHERE status=1 ORDER BY sort_order, id'
        )->result_array();

        $data['js']            = 'home.inc';
        $data['banners']       = $banners;
        $data['categories']    = $categories;
        $data['featured']      = $featured;
        $data['products']      = $products;
        $data['why_choose_us'] = $why_choose_us;
        $data['testimonials']  = $testimonials;

        $this->load->view('inc/front-header', $data);
        $this->load->view('page/home', $data);
        $this->load->view('inc/front-footer', $data);
    }

    public function account()
    {
        if (!$this->session->userdata(SESS_HEAD.'_logged_in')) redirect('login');
        $this->_front_base($data);

        $user_id = (int)$this->session->userdata(SESS_HEAD.'_user_id');
        $tab     = $this->input->get('tab') ?: 'profile';
        $user    = $this->db->query('SELECT * FROM users WHERE id=?', array($user_id))->row_array();

        $errors = array(); $success = '';

        if ($this->input->post('update_profile')) {
            $name    = trim($this->input->post('name'));
            $phone   = trim($this->input->post('phone'));
            $address = trim($this->input->post('address'));
            if (empty($name)) $errors[] = 'Name cannot be empty.';
            if (empty($errors)) {
                $this->db->query(
                    'UPDATE users SET name=?,phone=?,address=? WHERE id=?',
                    array($name, $phone, $address, $user_id)
                );
                $this->session->set_userdata(SESS_HEAD.'_user_name', $name);
                $data['user_name'] = $name;
                $success = 'Profile updated successfully.';
                $user['name'] = $name; $user['phone'] = $phone; $user['address'] = $address;
            }
        }

        if ($this->input->post('change_password')) {
            $current = $this->input->post('current_password');
            $newPass = $this->input->post('new_password');
            $confirm = $this->input->post('confirm_password');
            if ($user['password'] !== $current) {
                $errors[] = 'Current password is incorrect.';
            } elseif (strlen($newPass) < 6) {
                $errors[] = 'New password must be at least 6 characters.';
            } elseif ($newPass !== $confirm) {
                $errors[] = 'New passwords do not match.';
            } else {
                $this->db->query('UPDATE users SET password=? WHERE id=?', array($newPass, $user_id));
                $success = 'Password changed successfully.';
            }
        }

        $orders = array(); $orderDetail = null; $orderItems = array();
        $viewOrderId = (int)$this->input->get('order');

        if ($viewOrderId) {
            $tab = 'orders';
            $orderDetail = $this->db->query(
                'SELECT * FROM orders WHERE id=? AND user_id=?', array($viewOrderId, $user_id)
            )->row_array();
            if ($orderDetail) {
                $orderItems = $this->db->query(
                    'SELECT oi.*, p.image FROM order_items oi
                     JOIN products p ON p.id=oi.product_id
                     WHERE oi.order_id=?', array($viewOrderId)
                )->result_array();
            }
        }

        if ($tab === 'orders') {
            $orders = $this->db->query(
                'SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id=o.id) AS item_count
                 FROM orders o WHERE o.user_id=? ORDER BY o.created_at DESC',
                array($user_id)
            )->result_array();
        }

        $addresses = $this->db->query(
            'SELECT * FROM addresses WHERE user_id=? ORDER BY is_default DESC', array($user_id)
        )->result_array();

        $wishlist = array();
        if ($tab === 'wishlist') {
            $wishlist = $this->db->query(
                'SELECT w.*, p.name, p.price, p.offer_price, p.image, p.slug
                 FROM wishlist w JOIN products p ON p.id=w.product_id
                 WHERE w.user_id=? ORDER BY w.created_at DESC', array($user_id)
            )->result_array();
        }

        $data['js']          = 'account.inc';
        $data['tab']         = $tab;
        $data['user']        = $user;
        $data['errors']      = $errors;
        $data['success']     = $success;
        $data['orders']      = $orders;
        $data['orderDetail'] = $orderDetail;
        $data['orderItems']  = $orderItems;
        $data['addresses']   = $addresses;
        $data['wishlist']    = $wishlist;

        $this->load->view('inc/front-header', $data);
        $this->load->view('page/account', $data);
        $this->load->view('inc/front-footer', $data);
    }

    public function wishlist()
    {
        if (!$this->session->userdata(SESS_HEAD.'_logged_in')) redirect('login');
        $this->_front_base($data);

        $user_id = (int)$this->session->userdata(SESS_HEAD.'_user_id');
        $items   = $this->db->query(
            'SELECT w.id, w.created_at, p.id AS product_id, p.name, p.price, p.offer_price,
                    p.image, p.slug, p.stock_qty, c.name AS cat_name
             FROM wishlist w
             JOIN products p ON p.id=w.product_id
             JOIN categories c ON c.id=p.category_id
             WHERE w.user_id=? AND p.status=1
             ORDER BY w.created_at DESC', array($user_id)
        )->result_array();

        $data['js']    = 'wishlist.inc';
        $data['items'] = $items;

        $this->load->view('inc/front-header', $data);
        $this->load->view('page/wishlist', $data);
        $this->load->view('inc/front-footer', $data);
    }

    public function wishlist_toggle($product_id = 0)
    {
        header('Content-Type: application/json');
        if (!$this->session->userdata(SESS_HEAD.'_logged_in')) {
            echo json_encode(array('success'=>false,'message'=>'Please login first.'));
            return;
        }
        $user_id    = (int)$this->session->userdata(SESS_HEAD.'_user_id');
        $product_id = (int)$product_id;
        $exists = $this->db->query(
            'SELECT id FROM wishlist WHERE user_id=? AND product_id=?', array($user_id, $product_id)
        )->row();
        if ($exists) {
            $this->db->query('DELETE FROM wishlist WHERE user_id=? AND product_id=?', array($user_id, $product_id));
            $action = 'removed';
        } else {
            $this->db->query('INSERT IGNORE INTO wishlist (user_id,product_id) VALUES (?,?)', array($user_id, $product_id));
            $action = 'added';
        }
        $count = (int)$this->db->query('SELECT COUNT(*) AS cnt FROM wishlist WHERE user_id=?', array($user_id))->row()->cnt;
        echo json_encode(array('success'=>true,'action'=>$action,'wishlist_count'=>$count));
    }

    public function my_addresses()
    {
        if (!$this->session->userdata(SESS_HEAD.'_logged_in')) redirect('login');
        $this->_front_base($data);

        $user_id = (int)$this->session->userdata(SESS_HEAD.'_user_id');
        $errors  = array(); $success = '';

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $addr_id = (int)$this->input->post('addr_id');
            $label   = trim($this->input->post('label') ?: 'Home');
            $name    = trim($this->input->post('name'));
            $phone   = trim($this->input->post('phone'));
            $line    = trim($this->input->post('address_line'));
            $city    = trim($this->input->post('city'));
            $state   = trim($this->input->post('state'));
            $pin     = trim($this->input->post('pincode'));
            $default = (int)$this->input->post('is_default');

            if (!$name)  $errors[] = 'Full name required.';
            if (!$phone) $errors[] = 'Phone required.';
            if (!$line)  $errors[] = 'Address required.';
            if (!$city)  $errors[] = 'City required.';
            if (!$pin)   $errors[] = 'Pincode required.';

            if (empty($errors)) {
                if ($default) {
                    $this->db->query('UPDATE addresses SET is_default=0 WHERE user_id=?', array($user_id));
                }
                if ($addr_id) {
                    $this->db->query(
                        'UPDATE addresses SET label=?,name=?,phone=?,address_line=?,city=?,state=?,pincode=?,is_default=? WHERE id=? AND user_id=?',
                        array($label,$name,$phone,$line,$city,$state,$pin,$default,$addr_id,$user_id)
                    );
                } else {
                    $this->db->query(
                        'INSERT INTO addresses (user_id,label,name,phone,address_line,city,state,pincode,is_default) VALUES (?,?,?,?,?,?,?,?,?)',
                        array($user_id,$label,$name,$phone,$line,$city,$state,$pin,$default)
                    );
                }
                $success = 'Address saved successfully.';
            }
        }

        if ($this->input->get('delete')) {
            $aid = (int)$this->input->get('delete');
            $this->db->query('DELETE FROM addresses WHERE id=? AND user_id=?', array($aid,$user_id));
            redirect('my-addresses');
        }

        $data['js']        = 'account.inc';
        $data['addresses'] = $this->db->query(
            'SELECT * FROM addresses WHERE user_id=? ORDER BY is_default DESC', array($user_id)
        )->result_array();
        $data['errors']  = $errors;
        $data['success'] = $success;

        $this->load->view('inc/front-header', $data);
        $this->load->view('page/my-addresses', $data);
        $this->load->view('inc/front-footer', $data);
    }

    public function cancel_order($order_id = 0)
    {
        if (!$this->session->userdata(SESS_HEAD.'_logged_in')) redirect('login');
        $this->_front_base($data);

        $user_id  = (int)$this->session->userdata(SESS_HEAD.'_user_id');
        $order_id = (int)$order_id;
        $order    = $this->db->query(
            'SELECT * FROM orders WHERE id=? AND user_id=?', array($order_id, $user_id)
        )->row_array();

        if (!$order || !in_array($order['status'], array('pending','processing'))) {
            $this->session->set_flashdata('error', 'This order cannot be cancelled.');
            redirect('account?tab=orders');
        }

        $error = '';

        if ($this->input->post('submit_cancel')) {
            $reason = trim($this->input->post('reason'));
            if (!$reason) { $error = 'Please provide a reason.'; }
            else {
                $this->db->query(
                    'INSERT INTO returns (order_id,user_id,type,reason) VALUES (?,?,?,?)',
                    array($order_id, $user_id, 'cancel', $reason)
                );
                $this->db->query("UPDATE orders SET status='cancelled' WHERE id=?", array($order_id));
                // Restore stock
                $items = $this->db->query('SELECT * FROM order_items WHERE order_id=?', array($order_id))->result_array();
                foreach ($items as $it) {
                    $this->db->query('UPDATE products SET stock_qty=stock_qty+? WHERE id=?', array($it['quantity'],$it['product_id']));
                }
                $this->session->set_flashdata('success', 'Order #'.str_pad($order_id,5,'0',STR_PAD_LEFT).' has been cancelled.');
                redirect('account?tab=orders');
            }
        }

        $data['js']    = 'account.inc';
        $data['order'] = $order;
        $data['error'] = $error;

        $this->load->view('inc/front-header', $data);
        $this->load->view('page/cancel-order', $data);
        $this->load->view('inc/front-footer', $data);
    }

    public function return_order($order_id = 0)
    {
        if (!$this->session->userdata(SESS_HEAD.'_logged_in')) redirect('login');
        $this->_front_base($data);

        $user_id  = (int)$this->session->userdata(SESS_HEAD.'_user_id');
        $order_id = (int)$order_id;
        $order    = $this->db->query(
            'SELECT * FROM orders WHERE id=? AND user_id=?', array($order_id, $user_id)
        )->row_array();

        if (!$order || $order['status'] !== 'delivered') {
            $this->session->set_flashdata('error', 'Return is only allowed for delivered orders.');
            redirect('account?tab=orders');
        }

        $error = '';

        if ($this->input->post('submit_return')) {
            $reason = trim($this->input->post('reason'));
            if (!$reason) { $error = 'Please provide a reason for return.'; }
            else {
                $existing = $this->db->query(
                    'SELECT id FROM returns WHERE order_id=? AND user_id=? AND type="return"',
                    array($order_id, $user_id)
                )->row();
                if ($existing) { $error = 'A return request already exists for this order.'; }
                else {
                    $this->db->query(
                        'INSERT INTO returns (order_id,user_id,type,reason) VALUES (?,?,?,?)',
                        array($order_id, $user_id, 'return', $reason)
                    );
                    $this->session->set_flashdata('success', 'Return request submitted. We will contact you within 24 hours.');
                    redirect('account?tab=orders');
                }
            }
        }

        $data['js']    = 'account.inc';
        $data['order'] = $order;
        $data['error'] = $error;

        $this->load->view('inc/front-header', $data);
        $this->load->view('page/return-order', $data);
        $this->load->view('inc/front-footer', $data);
    }

    public function track_order($order_id = 0)
    {
        if (!$this->session->userdata(SESS_HEAD.'_logged_in')) redirect('login');
        $this->_front_base($data);

        $user_id  = (int)$this->session->userdata(SESS_HEAD.'_user_id');
        $order_id = (int)$order_id;
        $order    = $this->db->query(
            'SELECT * FROM orders WHERE id=? AND user_id=?', array($order_id, $user_id)
        )->row_array();

        if (!$order) { show_404(); }

        $items = $this->db->query(
            'SELECT oi.*, p.image FROM order_items oi
             JOIN products p ON p.id=oi.product_id
             WHERE oi.order_id=?', array($order_id)
        )->result_array();

        $data['js']    = 'home.inc';
        $data['order'] = $order;
        $data['items'] = $items;

        $this->load->view('inc/front-header', $data);
        $this->load->view('page/track-order', $data);
        $this->load->view('inc/front-footer', $data);
    }

    public function invoice($order_id = 0)
    {
        if (!$this->session->userdata(SESS_HEAD.'_logged_in')) redirect('login');

        $user_id  = (int)$this->session->userdata(SESS_HEAD.'_user_id');
        $order_id = (int)$order_id;
        $order    = $this->db->query(
            'SELECT o.*, u.name AS customer_name, u.email, u.phone AS customer_phone
             FROM orders o JOIN users u ON u.id=o.user_id
             WHERE o.id=? AND o.user_id=?', array($order_id, $user_id)
        )->row_array();

        if (!$order) { show_404(); }

        $items = $this->db->query(
            'SELECT * FROM order_items WHERE order_id=?', array($order_id)
        )->result_array();

        $data['order'] = $order;
        $data['items'] = $items;

        $this->load->view('page/invoice', $data);
    }

    public function contact()
    {
        $this->_front_base($data);

        $error = ''; $success = '';

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $name    = trim($this->input->post('name'));
            $email   = trim($this->input->post('email'));
            $phone   = trim($this->input->post('phone'));
            $subject = trim($this->input->post('subject'));
            $message = trim($this->input->post('message'));

            if (!$name || !$email || !$message) {
                $error = 'Please fill in all required fields.';
            } else {
                $this->db->query(
                    'INSERT INTO contacts (name,email,phone,subject,message) VALUES (?,?,?,?,?)',
                    array($name, $email, $phone, $subject, $message)
                );
                $success = 'Thank you! Your message has been sent. We will get back to you within 24 hours.';
            }
        }

        $data['js']      = 'contact.inc';
        $data['error']   = $error;
        $data['success'] = $success;

        $this->load->view('inc/front-header', $data);
        $this->load->view('page/contact', $data);
        $this->load->view('inc/front-footer', $data);
    }

    public function cms_page($slug = '')
    {
        $this->_front_base($data);

        $page = $this->db->query(
            'SELECT * FROM cms_pages WHERE slug=? AND status=1', array($slug)
        )->row_array();

        if (!$page) { show_404(); }

        $data['js']   = 'home.inc';
        $data['page'] = $page;

        $this->load->view('inc/front-header', $data);
        $this->load->view('page/cms-page', $data);
        $this->load->view('inc/front-footer', $data);
    }
}
