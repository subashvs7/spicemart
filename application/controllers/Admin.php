<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

    private function _require_admin()
    {
        if (!$this->session->userdata(SESS_HEAD.'_logged_in') ||
            !in_array($this->session->userdata(SESS_HEAD.'_user_role'), array('admin','staff'))) {
            redirect('login');
        }
    }

    private function _admin_base(&$data)
    {
        $data['admin_name'] = $this->session->userdata(SESS_HEAD.'_user_name');
        $data['admin_role'] = $this->session->userdata(SESS_HEAD.'_user_role');
        $data['unread_contacts'] = (int)$this->db->query(
            'SELECT COUNT(*) AS cnt FROM contacts WHERE is_read=0'
        )->row()->cnt;
        $data['pending_returns'] = (int)$this->db->query(
            "SELECT COUNT(*) AS cnt FROM returns WHERE status='pending'"
        )->row()->cnt;
    }

    // ── Dashboard ─────────────────────────────────────────────────

    public function index()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        $total_orders  = (int)$this->db->query('SELECT COUNT(*) AS cnt FROM orders')->row()->cnt;
        $revenue_today = (float)$this->db->query(
            "SELECT COALESCE(SUM(total_amount),0) AS rev FROM orders WHERE DATE(created_at)=CURDATE() AND status!='cancelled'"
        )->row()->rev;
        $low_stock    = (int)$this->db->query('SELECT COUNT(*) AS cnt FROM products WHERE stock_qty<20 AND status=1')->row()->cnt;
        $new_customers= (int)$this->db->query(
            "SELECT COUNT(*) AS cnt FROM users WHERE role='customer' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())"
        )->row()->cnt;

        $recent_orders= $this->db->query(
            'SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.created_at DESC LIMIT 10'
        )->result_array();

        $monthly = $this->db->query(
            "SELECT DATE_FORMAT(created_at,'%b %Y') AS month_label, MONTH(created_at) AS m, YEAR(created_at) AS y,
                    SUM(total_amount) AS revenue, COUNT(*) AS orders
             FROM orders WHERE status!='cancelled' AND created_at>=DATE_SUB(NOW(),INTERVAL 6 MONTH)
             GROUP BY y,m,month_label ORDER BY y,m"
        )->result_array();

        $total_revenue   = (float)$this->db->query("SELECT COALESCE(SUM(total_amount),0) AS rev FROM orders WHERE status!='cancelled'")->row()->rev;
        $pending_orders  = (int)$this->db->query("SELECT COUNT(*) AS cnt FROM orders WHERE status='pending'")->row()->cnt;
        $active_products = (int)$this->db->query('SELECT COUNT(*) AS cnt FROM products WHERE status=1')->row()->cnt;
        $total_customers = (int)$this->db->query("SELECT COUNT(*) AS cnt FROM users WHERE role='customer'")->row()->cnt;

        $status_counts = array();
        foreach ($this->db->query('SELECT status,COUNT(*) AS cnt FROM orders GROUP BY status')->result_array() as $r) {
            $status_counts[$r['status']] = $r['cnt'];
        }

        $data['js']              = 'admin-dashboard.inc';
        $data['page']            = 'dashboard';
        $data['total_orders']    = $total_orders;
        $data['revenue_today']   = $revenue_today;
        $data['low_stock']       = $low_stock;
        $data['new_customers']   = $new_customers;
        $data['recent_orders']   = $recent_orders;
        $data['monthly']         = $monthly;
        $data['chart_labels']    = json_encode(array_column($monthly,'month_label'));
        $data['chart_revenue']   = json_encode(array_map(function($r){ return round((float)$r['revenue'],2); }, $monthly));
        $data['total_revenue']   = $total_revenue;
        $data['pending_orders']  = $pending_orders;
        $data['active_products'] = $active_products;
        $data['total_customers'] = $total_customers;
        $data['status_counts']   = $status_counts;

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/dashboard', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── Products ──────────────────────────────────────────────────

    public function products()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        $errors = array(); $success = '';
        $categories = $this->db->query('SELECT id,name FROM categories WHERE status=1 AND deleted_at IS NULL ORDER BY name')->result_array();
        $brands     = $this->db->query('SELECT id,name FROM brands WHERE status=1 AND deleted_at IS NULL ORDER BY name')->result_array();

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $post_id     = (int)$this->input->post('product_id');
            $cat_id      = (int)$this->input->post('category_id');
            $brand_id    = (int)$this->input->post('brand_id') ?: null;
            $name        = trim($this->input->post('name') ?: '');
            $description = trim($this->input->post('description') ?: '');
            $price       = (float)$this->input->post('price');
            $offer_price = (float)$this->input->post('offer_price') ?: null;
            $gst         = (float)$this->input->post('gst') ?: 0;
            $stock_qty   = (int)$this->input->post('stock_qty');
            $weight      = trim($this->input->post('weight') ?: '');
            $tags        = trim($this->input->post('tags') ?: '');
            $meta_title  = trim($this->input->post('meta_title') ?: '');
            $meta_desc   = trim($this->input->post('meta_desc') ?: '');
            $is_featured = (int)$this->input->post('is_featured');
            $status      = (int)$this->input->post('status');

            if (!$name)      $errors[] = 'Product name is required.';
            if (!$cat_id)    $errors[] = 'Please select a category.';
            if ($price <= 0) $errors[] = 'Price must be greater than 0.';

            $image_name = trim($this->input->post('existing_image') ?: '');
            if (!empty($_FILES['image']['name'])) {
                $this->load->library('upload', array(
                    'upload_path'   => FCPATH.'uploads/products/',
                    'allowed_types' => 'jpg|jpeg|png|webp|gif|svg',
                    'max_size'      => 2048,
                    'encrypt_name'  => TRUE,
                ));
                if ($this->upload->do_upload('image')) {
                    $image_name = $this->upload->data('file_name');
                } else {
                    $errors[] = $this->upload->display_errors('','');
                }
            }

            if (empty($errors)) {
                $slug = $this->spice_model->make_slug($name);
                $slug_conflict = $this->db->query(
                    'SELECT id FROM products WHERE slug=? AND id!=?',
                    array($slug, $post_id ?: 0)
                )->row();
                if ($slug_conflict) {
                    $errors[] = 'A product with the name "'.htmlspecialchars($name).'" already exists. Please use a different name.';
                }
            }

            if (empty($errors)) {
                if ($post_id) {
                    $this->db->query(
                        'UPDATE products SET category_id=?,brand_id=?,name=?,slug=?,description=?,price=?,offer_price=?,gst=?,stock_qty=?,weight=?,image=?,tags=?,meta_title=?,meta_desc=?,is_featured=?,status=? WHERE id=?',
                        array($cat_id,$brand_id,$name,$slug,$description,$price,$offer_price,$gst,$stock_qty,$weight,$image_name,$tags,$meta_title,$meta_desc,$is_featured,$status,$post_id)
                    );
                    $success = 'Product updated.';
                } else {
                    $this->db->query(
                        'INSERT INTO products (category_id,brand_id,name,slug,description,price,offer_price,gst,stock_qty,weight,image,tags,meta_title,meta_desc,is_featured,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                        array($cat_id,$brand_id,$name,$slug,$description,$price,$offer_price,$gst,$stock_qty,$weight,$image_name,$tags,$meta_title,$meta_desc,$is_featured,$status)
                    );
                    $new_id = $this->db->insert_id();
                    $this->db->query(
                        'UPDATE products SET product_code=? WHERE id=?',
                        array('PRD-'.str_pad($new_id, 5, '0', STR_PAD_LEFT), $new_id)
                    );
                    $success = 'Product added.';
                }
            }

            if (!empty($errors)) {
                $data['form_data'] = array(
                    'product_id'  => $post_id,
                    'name'        => $name,
                    'category_id' => $cat_id,
                    'brand_id'    => $brand_id ?: '',
                    'description' => $description,
                    'price'       => $price,
                    'offer_price' => $offer_price ?: '',
                    'gst'         => $gst,
                    'stock_qty'   => $stock_qty,
                    'weight'      => $weight,
                    'tags'        => $tags,
                    'meta_title'  => $meta_title,
                    'meta_desc'   => $meta_desc,
                    'is_featured' => $is_featured,
                    'status'      => $status,
                    'image'       => $image_name,
                );
            }
        }

        $action  = $this->input->get('action') ?: '';
        $edit_id = (int)$this->input->get('edit');
        if ($action === 'delete' && $edit_id) {
            $this->db->query('UPDATE products SET status=-1 WHERE id=?', array($edit_id));
            $this->session->set_flashdata('success', 'Product deleted.');
            redirect('admin-products');
        }
        if ($action === 'toggle' && $edit_id) {
            // Toggles only between active (1) and inactive (0); deleted (-1) is not affected
            $this->db->query('UPDATE products SET status=1-status WHERE id=? AND status >= 0', array($edit_id));
            redirect('admin-products');
        }

        $filter_low = $this->input->get('filter') === 'low_stock';
        if ($filter_low) {
            $products = $this->db->query(
                'SELECT p.*,c.name AS cat_name,b.name AS brand_name FROM products p
                 JOIN categories c ON c.id=p.category_id
                 LEFT JOIN brands b ON b.id=p.brand_id
                 WHERE p.stock_qty<20 AND p.status=1 ORDER BY p.stock_qty'
            )->result_array();
        } else {
            $products = $this->db->query(
                'SELECT p.*,c.name AS cat_name,b.name AS brand_name FROM products p
                 JOIN categories c ON c.id=p.category_id
                 LEFT JOIN brands b ON b.id=p.brand_id
                 WHERE p.status >= 0 ORDER BY p.id DESC'
            )->result_array();
        }

        $data['js']         = 'admin-products.inc';
        $data['page']       = 'products';
        $data['products']   = $products;
        $data['categories'] = $categories;
        $data['brands']     = $brands;
        $data['errors']     = $errors;
        $data['success']    = $success;
        $data['filter_low'] = $filter_low;
        if (!isset($data['form_data'])) $data['form_data'] = array();

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/products', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── Categories ────────────────────────────────────────────────

    public function categories()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        $errors = array(); $success = '';

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $cat_id    = (int)$this->input->post('cat_id');
            $parent_id = (int)$this->input->post('parent_id') ?: null;
            $name      = trim($this->input->post('name') ?: '');
            $status    = (int)$this->input->post('status');

            if (!$name) $errors[] = 'Category name is required.';

            $image_name = trim($this->input->post('existing_image') ?: '');
            if (!empty($_FILES['image']['name'])) {
                $this->load->library('upload', array(
                    'upload_path'   => FCPATH.'uploads/products/',
                    'allowed_types' => 'jpg|jpeg|png|webp|gif|svg',
                    'max_size'      => 1024,
                    'encrypt_name'  => TRUE,
                ));
                if ($this->upload->do_upload('image')) {
                    $image_name = $this->upload->data('file_name');
                } else {
                    $errors[] = $this->upload->display_errors('','');
                }
            }

            if (empty($errors)) {
                $slug = $this->spice_model->make_slug($name);
                if ($cat_id) {
                    $this->db->query(
                        'UPDATE categories SET parent_id=?,name=?,slug=?,image=?,status=? WHERE id=?',
                        array($parent_id,$name,$slug,$image_name,$status,$cat_id)
                    );
                    $success = 'Category updated.';
                } else {
                    $this->db->query(
                        'INSERT INTO categories (parent_id,name,slug,image,status) VALUES (?,?,?,?,?)',
                        array($parent_id,$name,$slug,$image_name,$status)
                    );
                    $success = 'Category added.';
                }
            }
        }

        $action  = $this->input->get('action') ?: '';
        $edit_id = (int)$this->input->get('edit');
        if ($action === 'delete' && $edit_id) {
            $count = (int)$this->db->query('SELECT COUNT(*) AS cnt FROM products WHERE category_id=? AND status=1', array($edit_id))->row()->cnt;
            if ($count > 0) {
                $this->session->set_flashdata('danger','Cannot delete — '.$count.' product(s) are linked.');
            } else {
                $this->db->query('UPDATE categories SET deleted_at=NOW() WHERE id=?', array($edit_id));
                $this->session->set_flashdata('success','Category deleted.');
            }
            redirect('admin-categories');
        }

        $cats = $this->db->query(
            'SELECT c.*, p.name AS parent_name,
                    (SELECT COUNT(*) FROM products WHERE category_id=c.id) AS product_count
             FROM categories c LEFT JOIN categories p ON p.id=c.parent_id
             WHERE c.deleted_at IS NULL
             ORDER BY c.parent_id, c.name'
        )->result_array();

        $data['js']      = 'admin-categories.inc';
        $data['page']    = 'categories';
        $data['cats']    = $cats;
        $data['errors']  = $errors;
        $data['success'] = $success;

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/categories', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── Brands ────────────────────────────────────────────────────

    public function brands()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        $errors = array(); $success = '';

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $brand_id = (int)$this->input->post('brand_id');
            $name     = trim($this->input->post('name') ?: '');
            $status   = (int)$this->input->post('status');
            if (!$name) $errors[] = 'Brand name is required.';

            $image_name = trim($this->input->post('existing_image') ?: '');
            if (!empty($_FILES['image']['name'])) {
                $this->load->library('upload', array(
                    'upload_path'   => FCPATH.'uploads/products/',
                    'allowed_types' => 'jpg|jpeg|png|webp|gif|svg',
                    'max_size'      => 1024,
                    'encrypt_name'  => TRUE,
                ));
                if ($this->upload->do_upload('image')) {
                    $image_name = $this->upload->data('file_name');
                } else {
                    $errors[] = $this->upload->display_errors('','');
                }
            }

            if (empty($errors)) {
                $slug = $this->spice_model->make_slug($name);
                if ($brand_id) {
                    $this->db->query('UPDATE brands SET name=?,slug=?,image=?,status=? WHERE id=?', array($name,$slug,$image_name,$status,$brand_id));
                    $success = 'Brand updated.';
                } else {
                    $this->db->query('INSERT INTO brands (name,slug,image,status) VALUES (?,?,?,?)', array($name,$slug,$image_name,$status));
                    $success = 'Brand added.';
                }
            }
        }

        $action  = $this->input->get('action') ?: '';
        $edit_id = (int)$this->input->get('edit');
        if ($action === 'delete' && $edit_id) {
            $this->db->query('UPDATE brands SET deleted_at=NOW() WHERE id=?', array($edit_id));
            redirect('admin-brands');
        }

        $brands = $this->db->query(
            'SELECT b.*, (SELECT COUNT(*) FROM products WHERE brand_id=b.id) AS product_count FROM brands b WHERE b.deleted_at IS NULL ORDER BY b.name'
        )->result_array();

        $data['js']      = 'admin-brands.inc';
        $data['page']    = 'brands';
        $data['brands']  = $brands;
        $data['errors']  = $errors;
        $data['success'] = $success;

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/brands', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── Orders ────────────────────────────────────────────────────

    public function orders()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        $errors = array(); $success = '';

        if ($this->input->post('update_status')) {
            $order_id    = (int)$this->input->post('order_id');
            $status      = $this->input->post('status');
            $tracking_no = trim($this->input->post('tracking_no') ?: '');
            $courier     = trim($this->input->post('courier_name') ?: '');
            $this->db->query(
                'UPDATE orders SET status=?,tracking_no=?,courier_name=? WHERE id=?',
                array($status, $tracking_no, $courier, $order_id)
            );
            if ($status === 'delivered') {
                $this->db->query("UPDATE orders SET payment_status='paid' WHERE id=? AND payment_method='cod'", array($order_id));
                // Auto-award loyalty points (only once per order)
                $already = $this->db->query(
                    "SELECT id FROM loyalty_ledger WHERE ref_type='order' AND ref_id=? AND type='earned' LIMIT 1",
                    array($order_id)
                )->row();
                if (!$already) {
                    $ord = $this->db->query('SELECT user_id, total_amount FROM orders WHERE id=?', array($order_id))->row_array();
                    $earn_rate = (int)($this->spice_model->get_setting('loyalty_earn_rate') ?: 1);
                    $earn_per  = (int)($this->spice_model->get_setting('loyalty_earn_per')  ?: 10);
                    if ($earn_per > 0 && $ord) {
                        $pts = (int)floor(((float)$ord['total_amount'] / $earn_per) * $earn_rate);
                        if ($pts > 0) {
                            $this->spice_model->add_loyalty_points(
                                $ord['user_id'], $pts, 'earned', 'order', $order_id,
                                'Earned on Order #'.str_pad($order_id, 5, '0', STR_PAD_LEFT)
                            );
                        }
                    }
                }
            }
            $success = 'Order updated.';
        }

        $filter_status = $this->input->get('status') ?: '';
        $where  = '1=1';
        $params = array();
        if ($filter_status) {
            $where   .= ' AND o.status=?';
            $params[] = $filter_status;
        }

        $orders = $this->db->query(
            'SELECT o.*, u.name AS customer_name,
                    (SELECT COUNT(*) FROM order_items WHERE order_id=o.id) AS item_count
             FROM orders o JOIN users u ON u.id=o.user_id
             WHERE '.$where.' ORDER BY o.created_at DESC', $params
        )->result_array();

        $view_order = null; $order_items = array();
        $view_id    = (int)$this->input->get('view');
        if ($view_id) {
            $view_order = $this->db->query(
                'SELECT o.*,u.name AS customer_name,u.email,u.phone FROM orders o JOIN users u ON u.id=o.user_id WHERE o.id=?', array($view_id)
            )->row_array();
            if ($view_order) {
                $order_items = $this->db->query(
                    'SELECT oi.*,p.image FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?', array($view_id)
                )->result_array();
            }
        }

        $data['js']            = 'admin-orders.inc';
        $data['page']          = 'orders';
        $data['orders']        = $orders;
        $data['view_order']    = $view_order;
        $data['order_items']   = $order_items;
        $data['filter_status'] = $filter_status;
        $data['errors']        = $errors;
        $data['success']       = $success;

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/orders', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── Customers ─────────────────────────────────────────────────

    public function customers()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        if ($this->input->get('block')) {
            $uid = (int)$this->input->get('block');
            $this->db->query('UPDATE users SET is_blocked=1-is_blocked WHERE id=? AND role="customer"', array($uid));
            redirect('admin-customers');
        }

        $customers = $this->db->query(
            "SELECT u.*, COUNT(DISTINCT o.id) AS order_count, COALESCE(SUM(o.total_amount),0) AS total_spent
             FROM users u LEFT JOIN orders o ON o.user_id=u.id
             WHERE u.role='customer' AND u.deleted_at IS NULL
             GROUP BY u.id ORDER BY u.created_at DESC"
        )->result_array();

        $view_customer = null; $cust_orders = array();
        $view_id       = (int)$this->input->get('view');
        if ($view_id) {
            $view_customer = $this->db->query(
                'SELECT * FROM users WHERE id=? AND role="customer" AND deleted_at IS NULL', array($view_id)
            )->row_array();
            if ($view_customer) {
                $cust_orders = $this->db->query(
                    'SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id=o.id) AS item_count
                     FROM orders o WHERE o.user_id=? ORDER BY o.created_at DESC', array($view_id)
                )->result_array();
            }
        }

        $data['js']            = 'admin-customers.inc';
        $data['page']          = 'customers';
        $data['customers']     = $customers;
        $data['view_customer'] = $view_customer;
        $data['view_id']       = $view_id;
        $data['cust_orders']   = $cust_orders;

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/customers', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── Coupons ───────────────────────────────────────────────────

    public function coupons()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        $errors = array(); $success = '';

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $coupon_id    = (int)$this->input->post('coupon_id');
            $code         = strtoupper(trim($this->input->post('code') ?: ''));
            $type         = $this->input->post('type');
            $value        = (float)$this->input->post('value');
            $min_order    = (float)$this->input->post('min_order') ?: 0;
            $max_discount = (float)$this->input->post('max_discount') ?: null;
            $uses_limit   = (int)$this->input->post('uses_limit') ?: null;
            $uses_per_user= (int)$this->input->post('uses_per_user') ?: null;
            $restrict_to  = $this->input->post('restrict_to') ?: 'all';
            $expires_at   = $this->input->post('expires_at') ?: null;
            $status       = (int)$this->input->post('status');
            $allowed_emails_raw = trim($this->input->post('allowed_emails') ?: '');

            if (!$code)    $errors[] = 'Coupon code is required.';
            if ($value<=0) $errors[] = 'Value must be greater than 0.';

            if (empty($errors)) {
                if ($coupon_id) {
                    $this->db->query(
                        'UPDATE coupons SET code=?,type=?,value=?,min_order=?,max_discount=?,uses_limit=?,uses_per_user=?,restrict_to=?,expires_at=?,status=? WHERE id=?',
                        array($code,$type,$value,$min_order,$max_discount,$uses_limit,$uses_per_user,$restrict_to,$expires_at,$status,$coupon_id)
                    );
                    $success = 'Coupon updated.';
                } else {
                    $this->db->query(
                        'INSERT INTO coupons (code,type,value,min_order,max_discount,uses_limit,uses_per_user,restrict_to,expires_at,status) VALUES (?,?,?,?,?,?,?,?,?,?)',
                        array($code,$type,$value,$min_order,$max_discount,$uses_limit,$uses_per_user,$restrict_to,$expires_at,$status)
                    );
                    $coupon_id = $this->db->insert_id();
                    $success = 'Coupon created.';
                }

                // Sync coupon_users allow-list
                $this->db->query('DELETE FROM coupon_users WHERE coupon_id=?', array($coupon_id));
                if ($restrict_to === 'specific' && $allowed_emails_raw) {
                    $emails = array_unique(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $allowed_emails_raw))));
                    foreach ($emails as $em) {
                        if (filter_var($em, FILTER_VALIDATE_EMAIL)) {
                            $this->db->query(
                                'INSERT IGNORE INTO coupon_users (coupon_id,user_email) VALUES (?,?)',
                                array($coupon_id, strtolower($em))
                            );
                        }
                    }
                }
            }
        }

        $action  = $this->input->get('action') ?: '';
        $edit_id = (int)$this->input->get('edit');
        if ($action === 'delete' && $edit_id) {
            $this->db->query('UPDATE coupons SET deleted_at=NOW() WHERE id=?', array($edit_id));
            redirect('admin-coupons');
        }

        $coupons = $this->db->query('SELECT * FROM coupons WHERE deleted_at IS NULL ORDER BY created_at DESC')->result_array();

        // Attach allowed email list to each coupon for the edit modal
        $cu_raw = $this->db->query(
            'SELECT coupon_id, GROUP_CONCAT(user_email ORDER BY user_email SEPARATOR "\n") AS emails
             FROM coupon_users GROUP BY coupon_id'
        )->result_array();
        $cu_map = array();
        foreach ($cu_raw as $cu) $cu_map[$cu['coupon_id']] = $cu['emails'];
        foreach ($coupons as &$c) $c['allowed_emails'] = $cu_map[$c['id']] ?? '';
        unset($c);

        $data['js']      = 'admin-coupons.inc';
        $data['page']    = 'coupons';
        $data['coupons'] = $coupons;
        $data['errors']  = $errors;
        $data['success'] = $success;

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/coupons', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── Banners ───────────────────────────────────────────────────

    public function banners()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        // ── Hard delete (runs first so it can't conflict with POST) ──
        $action  = $this->input->get('action') ?: '';
        $edit_id = (int)$this->input->get('edit');
        if ($action === 'delete' && $edit_id) {
            $row = $this->db->query('SELECT image FROM banners WHERE id=? LIMIT 1', array($edit_id))->row_array();
            if (!empty($row['image'])) {
                $file = FCPATH . 'uploads/banners/' . $row['image'];
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            $this->db->query('UPDATE banners SET deleted_at=NOW() WHERE id=?', array($edit_id));
            $this->session->set_flashdata('banner_msg', 'Banner deleted.');
            redirect('admin-banners');
        }

        // ── PRG: read flash success set by a previous redirect ──────
        $errors  = array();
        $success = $this->session->flashdata('banner_msg') ?: '';

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $banner_id  = (int)$this->input->post('banner_id');
            $title      = trim($this->input->post('title') ?: '');
            $subtitle   = trim($this->input->post('subtitle') ?: '');
            $link_url   = trim($this->input->post('link_url') ?: '');
            $btn_text   = trim($this->input->post('btn_text') ?: 'Shop Now');
            $type       = $this->input->post('type') ?: 'slider';
            $sort_order = (int)$this->input->post('sort_order');
            $status     = (int)$this->input->post('status');

            $image_name = trim($this->input->post('existing_image') ?: '');
            if (!empty($_FILES['image']['name'])) {
                $this->load->library('upload', array(
                    'upload_path'   => FCPATH . 'uploads/banners/',
                    'allowed_types' => 'jpg|jpeg|png|webp|gif|svg',
                    'max_size'      => 3072,
                    'encrypt_name'  => TRUE,
                ));
                if ($this->upload->do_upload('image')) {
                    $image_name = $this->upload->data('file_name');
                } else {
                    $errors[] = $this->upload->display_errors('', '');
                }
            }

            if (!$image_name && !$banner_id) {
                $errors[] = 'Banner image is required.';
            }

            if (empty($errors)) {
                if ($banner_id) {
                    $this->db->query(
                        'UPDATE banners SET title=?,subtitle=?,image=?,link_url=?,btn_text=?,type=?,sort_order=?,status=? WHERE id=?',
                        array($title, $subtitle, $image_name, $link_url, $btn_text, $type, $sort_order, $status, $banner_id)
                    );
                    $this->session->set_flashdata('banner_msg', 'Banner updated.');
                } else {
                    $this->db->query(
                        'INSERT INTO banners (title,subtitle,image,link_url,btn_text,type,sort_order,status) VALUES (?,?,?,?,?,?,?,?)',
                        array($title, $subtitle, $image_name, $link_url, $btn_text, $type, $sort_order, $status)
                    );
                    $this->session->set_flashdata('banner_msg', 'Banner added.');
                }
                // PRG: redirect so a page refresh never re-submits the form
                redirect('admin-banners');
            }
        }

        $banners = $this->db->query('SELECT * FROM banners WHERE deleted_at IS NULL ORDER BY type, sort_order')->result_array();

        $data['js']      = 'admin-banners.inc';
        $data['page']    = 'banners';
        $data['banners'] = $banners;
        $data['errors']  = $errors;
        $data['success'] = $success;

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/banners', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── CMS Pages ─────────────────────────────────────────────────

    public function cms_pages()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        $errors = array(); $success = '';

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $page_id   = (int)$this->input->post('page_id');
            $title     = trim($this->input->post('title') ?: '');
            $slug      = $this->spice_model->make_slug(trim($this->input->post('slug') ?: $title));
            $content   = $this->input->post('content') ?: '';
            $meta_title= trim($this->input->post('meta_title') ?: '');
            $meta_desc = trim($this->input->post('meta_desc') ?: '');
            $status    = (int)$this->input->post('status');

            if (!$title)   $errors[] = 'Title is required.';
            if (!$content) $errors[] = 'Content is required.';

            if (empty($errors)) {
                if ($page_id) {
                    $this->db->query(
                        'UPDATE cms_pages SET title=?,slug=?,content=?,meta_title=?,meta_desc=?,status=? WHERE id=?',
                        array($title,$slug,$content,$meta_title,$meta_desc,$status,$page_id)
                    );
                    $success = 'Page updated.';
                } else {
                    $this->db->query(
                        'INSERT INTO cms_pages (title,slug,content,meta_title,meta_desc,status) VALUES (?,?,?,?,?,?)',
                        array($title,$slug,$content,$meta_title,$meta_desc,$status)
                    );
                    $success = 'Page created.';
                }
            }
        }

        $action  = $this->input->get('action') ?: '';
        $edit_id = (int)$this->input->get('edit');
        if ($action === 'delete' && $edit_id) {
            $this->db->query('UPDATE cms_pages SET deleted_at=NOW() WHERE id=?', array($edit_id));
            redirect('admin-cms');
        }

        $pages = $this->db->query('SELECT * FROM cms_pages WHERE deleted_at IS NULL ORDER BY title')->result_array();

        $data['js']      = 'admin-cms.inc';
        $data['page']    = 'cms';
        $data['pages']   = $pages;
        $data['errors']  = $errors;
        $data['success'] = $success;

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/cms', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── Why Choose Us ─────────────────────────────────────────────

    public function why_choose_us()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        $errors = array(); $success = ''; $form_data = array();

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $item_id     = (int)$this->input->post('item_id');
            $icon        = trim($this->input->post('icon') ?: '🌿');
            $title       = trim($this->input->post('title') ?: '');
            $description = trim($this->input->post('description') ?: '');
            $sort_order  = (int)$this->input->post('sort_order');
            $status      = (int)$this->input->post('status');

            if (!$title)       $errors[] = 'Title is required.';
            if (!$description) $errors[] = 'Description is required.';

            if (empty($errors)) {
                if ($item_id) {
                    $this->db->query(
                        'UPDATE why_choose_us SET icon=?,title=?,description=?,sort_order=?,status=? WHERE id=?',
                        array($icon,$title,$description,$sort_order,$status,$item_id)
                    );
                    $success = 'Item updated.';
                } else {
                    $this->db->query(
                        'INSERT INTO why_choose_us (icon,title,description,sort_order,status) VALUES (?,?,?,?,?)',
                        array($icon,$title,$description,$sort_order,$status)
                    );
                    $success = 'Item added.';
                }
            } else {
                $form_data = array(
                    'id'          => $item_id,
                    'icon'        => $icon,
                    'title'       => $title,
                    'description' => $description,
                    'sort_order'  => $sort_order,
                    'status'      => $status,
                );
            }
        }

        $action  = $this->input->get('action') ?: '';
        $edit_id = (int)$this->input->get('edit');
        if ($action === 'delete' && $edit_id) {
            $this->db->query('UPDATE why_choose_us SET deleted_at=NOW() WHERE id=?', array($edit_id));
            redirect('admin-why-choose-us');
        }

        $items = $this->db->query('SELECT * FROM why_choose_us WHERE deleted_at IS NULL ORDER BY sort_order, id')->result_array();

        $data['page']      = 'why_choose_us';
        $data['items']     = $items;
        $data['errors']    = $errors;
        $data['success']   = $success;
        $data['form_data'] = $form_data;

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/why_choose_us', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── Testimonials ───────────────────────────────────────────────

    public function testimonials()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        $errors = array(); $success = ''; $form_data = array();

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $testimonial_id = (int)$this->input->post('testimonial_id');
            $customer_name  = trim($this->input->post('customer_name') ?: '');
            $rating         = (int)$this->input->post('rating');
            $quote          = trim($this->input->post('quote') ?: '');
            $sort_order     = (int)$this->input->post('sort_order');
            $status         = (int)$this->input->post('status');

            if (!$customer_name) $errors[] = 'Customer name is required.';
            if (!$quote)         $errors[] = 'Quote is required.';
            if ($rating < 1 || $rating > 5) $rating = 5;

            if (empty($errors)) {
                if ($testimonial_id) {
                    $this->db->query(
                        'UPDATE testimonials SET customer_name=?,rating=?,quote=?,sort_order=?,status=? WHERE id=?',
                        array($customer_name,$rating,$quote,$sort_order,$status,$testimonial_id)
                    );
                    $success = 'Testimonial updated.';
                } else {
                    $this->db->query(
                        'INSERT INTO testimonials (customer_name,rating,quote,sort_order,status) VALUES (?,?,?,?,?)',
                        array($customer_name,$rating,$quote,$sort_order,$status)
                    );
                    $success = 'Testimonial added.';
                }
            } else {
                $form_data = array(
                    'id'            => $testimonial_id,
                    'customer_name' => $customer_name,
                    'rating'        => $rating,
                    'quote'         => $quote,
                    'sort_order'    => $sort_order,
                    'status'        => $status,
                );
            }
        }

        $action  = $this->input->get('action') ?: '';
        $edit_id = (int)$this->input->get('edit');
        if ($action === 'delete' && $edit_id) {
            $this->db->query('UPDATE testimonials SET deleted_at=NOW() WHERE id=?', array($edit_id));
            redirect('admin-testimonials');
        }

        $testimonials = $this->db->query('SELECT * FROM testimonials WHERE deleted_at IS NULL ORDER BY sort_order, id')->result_array();

        $data['page']         = 'testimonials';
        $data['testimonials'] = $testimonials;
        $data['errors']       = $errors;
        $data['success']      = $success;
        $data['form_data']    = $form_data;

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/testimonials', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── Returns ───────────────────────────────────────────────────

    public function returns()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        $success = ''; $error = '';
        $allowed_statuses = array('pending','approved','rejected','resolved');

        // Single update
        if ($this->input->post('update_return')) {
            $ret_id     = (int)$this->input->post('return_id');
            $ret_status = $this->input->post('ret_status');
            $note       = trim($this->input->post('admin_note') ?: '');
            if (!in_array($ret_status, $allowed_statuses)) $ret_status = 'pending';
            $this->db->query(
                'UPDATE returns SET status=?,admin_note=? WHERE id=?',
                array($ret_status, $note, $ret_id)
            );
            // Restore stock when a return is approved
            if ($ret_status === 'approved') {
                $ret = $this->db->query('SELECT * FROM returns WHERE id=?', array($ret_id))->row_array();
                if ($ret && $ret['type'] === 'return') {
                    $items = $this->db->query('SELECT * FROM order_items WHERE order_id=?', array($ret['order_id']))->result_array();
                    foreach ($items as $it) {
                        $this->db->query('UPDATE products SET stock_qty=stock_qty+? WHERE id=?', array($it['quantity'], $it['product_id']));
                    }
                    $this->db->query("UPDATE orders SET status='returned' WHERE id=?", array($ret['order_id']));
                }
            }
            $success = 'Request updated successfully.';
        }

        // Bulk action
        if ($this->input->post('bulk_action')) {
            $bulk_status = $this->input->post('bulk_status');
            $ids         = $this->input->post('bulk_ids') ?: array();
            if (in_array($bulk_status, $allowed_statuses) && is_array($ids) && count($ids)) {
                $ids = array_map('intval', $ids);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $this->db->query(
                    "UPDATE returns SET status=? WHERE id IN ($placeholders)",
                    array_merge(array($bulk_status), $ids)
                );
                $success = count($ids).' request(s) marked as '.ucfirst($bulk_status).'.';
            }
        }

        // Filters
        $filter_type   = $this->input->get('type')   ?: '';
        $filter_status = $this->input->get('status') ?: '';
        $date_from     = $this->input->get('date_from') ?: '';
        $date_to       = $this->input->get('date_to')   ?: '';

        $where = '1=1'; $params = array();
        if ($filter_type)   { $where .= ' AND r.type=?';   $params[] = $filter_type; }
        if ($filter_status) { $where .= ' AND r.status=?'; $params[] = $filter_status; }
        if ($date_from)     { $where .= ' AND DATE(r.created_at)>=?'; $params[] = $date_from; }
        if ($date_to)       { $where .= ' AND DATE(r.created_at)<=?'; $params[] = $date_to; }

        $returns = $this->db->query(
            "SELECT r.*, o.total_amount, o.payment_method, o.status AS order_status,
                    u.name AS customer_name, u.email, u.phone
             FROM returns r
             JOIN orders o ON o.id=r.order_id
             JOIN users u ON u.id=r.user_id
             WHERE $where
             ORDER BY r.created_at DESC",
            $params
        )->result_array();

        // Enrich with order items (one query for all)
        $order_items_map = array();
        if (!empty($returns)) {
            $order_ids    = array_unique(array_column($returns, 'order_id'));
            $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
            $items        = $this->db->query(
                "SELECT oi.order_id, oi.product_name, oi.quantity, oi.unit_price, oi.variant_label, p.image
                 FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id
                 WHERE oi.order_id IN ($placeholders)",
                $order_ids
            )->result_array();
            foreach ($items as $it) {
                $order_items_map[$it['order_id']][] = $it;
            }
        }

        // Stats
        $stats = $this->db->query(
            "SELECT
               COUNT(*) AS total,
               SUM(r.status='pending')  AS pending,
               SUM(r.status='approved') AS approved,
               SUM(r.status='rejected') AS rejected,
               SUM(r.status='resolved') AS resolved,
               SUM(r.type='return')     AS type_returns,
               SUM(r.type='cancel')     AS type_cancels,
               SUM(o.total_amount)      AS total_amount,
               SUM(CASE WHEN r.status='approved' THEN o.total_amount ELSE 0 END) AS approved_amount,
               SUM(DATE(r.created_at)=CURDATE()) AS today_count
             FROM returns r JOIN orders o ON o.id=r.order_id"
        )->row_array();

        // Top reasons (word frequency on reason text — group by first 60 chars as proxy)
        $top_reasons = $this->db->query(
            "SELECT LEFT(reason,80) AS reason_text, COUNT(*) AS cnt
             FROM returns GROUP BY LEFT(reason,80) ORDER BY cnt DESC LIMIT 5"
        )->result_array();

        $data['js']              = 'admin-returns.inc';
        $data['page']            = 'returns';
        $data['returns']         = $returns;
        $data['order_items_map'] = $order_items_map;
        $data['stats']           = $stats;
        $data['top_reasons']     = $top_reasons;
        $data['success']         = $success;
        $data['error']           = $error;
        $data['filter_type']     = $filter_type;
        $data['filter_status']   = $filter_status;
        $data['date_from']       = $date_from;
        $data['date_to']         = $date_to;

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/returns', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── Contacts ──────────────────────────────────────────────────

    public function contacts()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        $view_id = (int)$this->input->get('view');
        if ($view_id) {
            $this->db->query('UPDATE contacts SET is_read=1 WHERE id=?', array($view_id));
        }

        $contacts = $this->db->query('SELECT * FROM contacts ORDER BY created_at DESC')->result_array();
        $view_msg = $view_id ? $this->db->query('SELECT * FROM contacts WHERE id=?', array($view_id))->row_array() : null;

        $data['js']       = 'admin-contacts.inc';
        $data['page']     = 'contacts';
        $data['contacts'] = $contacts;
        $data['view_msg'] = $view_msg;

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/contacts', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── Shipping Settings ─────────────────────────────────────────

    public function shipping()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        $success = '';
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $keys = array(
                'free_shipping_above','standard_charge','express_charge','estimated_days',
                'cod_enabled','cod_surcharge','cod_min_order',
                'packaging_charge','handling_fee',
                'dispatch_cutoff','shipping_message',
                'return_window_days','free_returns',
                'courier_partners',
            );
            foreach ($keys as $k) {
                $val = trim($this->input->post($k) ?: '');
                $this->db->query(
                    'INSERT INTO shipping_settings (key_name,key_value) VALUES (?,?) ON DUPLICATE KEY UPDATE key_value=?',
                    array($k,$val,$val)
                );
            }
            $success = 'Shipping settings saved.';
        }

        $settings_raw = $this->db->query('SELECT * FROM shipping_settings')->result_array();
        $settings = array();
        foreach ($settings_raw as $s) $settings[$s['key_name']] = $s['key_value'];

        $data['js']       = 'admin-shipping.inc';
        $data['page']     = 'shipping';
        $data['settings'] = $settings;
        $data['success']  = $success;

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/shipping', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── Payment Settings ──────────────────────────────────────────

    public function payment_settings()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        $success = '';

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $toggle_keys = array(
                'payment_cod_enabled', 'payment_razorpay_enabled',
                'payment_cards_enabled', 'payment_netbanking_enabled',
                'payment_wallets_enabled', 'payment_upi_enabled',
                'payment_applepay_enabled',
            );
            $text_keys = array('razorpay_key_id', 'razorpay_key_secret');

            foreach ($toggle_keys as $k) {
                $val = $this->input->post($k) ? '1' : '0';
                $this->db->query(
                    'INSERT INTO shipping_settings (key_name,key_value) VALUES (?,?) ON DUPLICATE KEY UPDATE key_value=?',
                    array($k, $val, $val)
                );
            }
            foreach ($text_keys as $k) {
                $val = trim($this->input->post($k) ?: '');
                $this->db->query(
                    'INSERT INTO shipping_settings (key_name,key_value) VALUES (?,?) ON DUPLICATE KEY UPDATE key_value=?',
                    array($k, $val, $val)
                );
            }
            $success = 'Payment settings saved.';
        }

        $settings_raw = $this->db->query('SELECT * FROM shipping_settings')->result_array();
        $settings     = array();
        foreach ($settings_raw as $s) $settings[$s['key_name']] = $s['key_value'];

        $payment_stats = $this->db->query(
            "SELECT payment_method,
                    COUNT(*) AS cnt,
                    COALESCE(SUM(CASE WHEN payment_status='paid' THEN total_amount ELSE 0 END),0) AS total,
                    COALESCE(SUM(CASE WHEN payment_status='paid' THEN 1 ELSE 0 END),0) AS paid_cnt,
                    COALESCE(SUM(CASE WHEN payment_status='failed' THEN 1 ELSE 0 END),0) AS failed_cnt
             FROM orders
             GROUP BY payment_method ORDER BY cnt DESC"
        )->result_array();

        $transactions = $this->db->query(
            'SELECT o.id, o.total_amount, o.payment_method, o.payment_status,
                    o.status AS order_status, o.transaction_id, o.created_at,
                    u.name AS customer_name, u.email
             FROM orders o JOIN users u ON u.id=o.user_id
             ORDER BY o.created_at DESC LIMIT 100'
        )->result_array();

        $data['js']            = 'admin-payments.inc';
        $data['page']          = 'payments';
        $data['settings']      = $settings;
        $data['payment_stats'] = $payment_stats;
        $data['transactions']  = $transactions;
        $data['success']       = $success;

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/payments', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── Loyalty & Promotion Engine ────────────────────────────────

    public function loyalty()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        $success = ''; $errors = array();

        // ── Save loyalty settings ──────────────────────────────────
        if ($this->input->post('save_settings')) {
            $ly_keys = array('loyalty_earn_rate','loyalty_earn_per','loyalty_redeem_rate',
                             'loyalty_redeem_value','loyalty_min_redeem','loyalty_expiry_days');
            foreach ($ly_keys as $k) {
                $v = max(0, (int)$this->input->post($k));
                $this->db->query(
                    'INSERT INTO shipping_settings (key_name,key_value) VALUES (?,?) ON DUPLICATE KEY UPDATE key_value=?',
                    array($k, $v, $v)
                );
            }
            $success = 'Loyalty settings saved.';
        }

        // ── Save / update campaign ─────────────────────────────────
        if ($this->input->post('save_campaign')) {
            $cid          = (int)$this->input->post('campaign_id');
            $name         = trim($this->input->post('name') ?: '');
            $type         = $this->input->post('type') ?: 'general';
            $description  = trim($this->input->post('description') ?: '');
            $offer_type   = $this->input->post('offer_type') ?: 'points_bonus';
            $offer_value  = (float)$this->input->post('offer_value') ?: 0;
            $coupon_code  = strtoupper(trim($this->input->post('coupon_code') ?: ''));
            $target       = $this->input->post('target') ?: 'all';
            $fest_date    = $this->input->post('festival_date') ?: null;
            $trig_days    = (int)$this->input->post('trigger_days') ?: 0;
            $start_date   = $this->input->post('start_date') ?: null;
            $end_date     = $this->input->post('end_date') ?: null;
            $message      = trim($this->input->post('message') ?: '');
            $status       = $this->input->post('status') ?: 'draft';

            if (!$name) $errors[] = 'Campaign name is required.';

            if (empty($errors)) {
                if ($cid) {
                    $this->db->query(
                        'UPDATE campaigns SET name=?,type=?,description=?,offer_type=?,offer_value=?,coupon_code=?,target=?,festival_date=?,trigger_days=?,start_date=?,end_date=?,message=?,status=? WHERE id=?',
                        array($name,$type,$description,$offer_type,$offer_value,$coupon_code,$target,$fest_date,$trig_days,$start_date,$end_date,$message,$status,$cid)
                    );
                    $success = 'Campaign updated.';
                } else {
                    $this->db->query(
                        'INSERT INTO campaigns (name,type,description,offer_type,offer_value,coupon_code,target,festival_date,trigger_days,start_date,end_date,message,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
                        array($name,$type,$description,$offer_type,$offer_value,$coupon_code,$target,$fest_date,$trig_days,$start_date,$end_date,$message,$status)
                    );
                    $success = 'Campaign created.';
                }
            }
        }

        // ── Manually adjust customer points ───────────────────────
        if ($this->input->post('adjust_points')) {
            $uid    = (int)$this->input->post('adj_user_id');
            $pts    = (int)$this->input->post('adj_points');
            $note   = trim($this->input->post('adj_note') ?: 'Admin adjustment');
            if ($uid && $pts !== 0) {
                $this->spice_model->add_loyalty_points($uid, $pts, 'adjusted', 'admin', null, $note);
                $success = 'Points adjusted successfully.';
            }
        }

        // ── Set customer birthday ──────────────────────────────────
        if ($this->input->post('save_birthday')) {
            $uid = (int)$this->input->post('bday_user_id');
            $bday = $this->input->post('bday_date') ?: null;
            if ($uid) {
                $this->db->query(
                    'INSERT INTO user_loyalty (user_id,birthday) VALUES (?,?) ON DUPLICATE KEY UPDATE birthday=?',
                    array($uid, $bday, $bday)
                );
                $success = 'Birthday updated.';
            }
        }

        // ── Trigger campaign ──────────────────────────────────────
        if ($this->input->get('trigger')) {
            $cid      = (int)$this->input->get('trigger');
            $campaign = $this->db->query('SELECT * FROM campaigns WHERE id=?', array($cid))->row_array();
            if ($campaign) {
                $users = $this->_get_segment_users($campaign['target'], $campaign['type']);
                $count = 0;
                foreach ($users as $u) {
                    $exists = $this->db->query(
                        'SELECT id FROM campaign_logs WHERE campaign_id=? AND user_id=?',
                        array($cid, $u['id'])
                    )->row();
                    if (!$exists) {
                        $this->db->query(
                            'INSERT INTO campaign_logs (campaign_id,user_id) VALUES (?,?)',
                            array($cid, $u['id'])
                        );
                        if ($campaign['offer_type'] === 'points_bonus' && $campaign['offer_value'] > 0) {
                            $this->spice_model->add_loyalty_points(
                                $u['id'], (int)$campaign['offer_value'],
                                'bonus', 'campaign', $cid, 'Campaign: '.$campaign['name']
                            );
                        }
                        $count++;
                    }
                }
                $this->db->query(
                    'UPDATE campaigns SET sent_count=sent_count+?, status="active" WHERE id=?',
                    array($count, $cid)
                );
                $this->session->set_flashdata('ly_success', 'Campaign sent to '.$count.' customers.');
            }
            redirect('admin-loyalty');
        }

        // ── Run automated birthday / anniversary campaigns ─────────
        if ($this->input->get('run_auto')) {
            $auto_count = 0;

            // Birthday campaigns
            $bday_campaigns = $this->db->query("SELECT * FROM campaigns WHERE type='birthday' AND status='active' AND deleted_at IS NULL")->result_array();
            foreach ($bday_campaigns as $camp) {
                $bday_users = $this->db->query(
                    "SELECT u.id FROM users u JOIN user_loyalty ul ON ul.user_id=u.id
                     WHERE DATE_FORMAT(ul.birthday,'%m-%d')=DATE_FORMAT(NOW(),'%m-%d')
                     AND ul.birthday IS NOT NULL AND u.role='customer' AND u.deleted_at IS NULL"
                )->result_array();
                foreach ($bday_users as $u) {
                    $exists = $this->db->query('SELECT id FROM campaign_logs WHERE campaign_id=? AND user_id=? AND DATE(sent_at)=CURDATE()', array($camp['id'],$u['id']))->row();
                    if (!$exists) {
                        $this->db->query('INSERT INTO campaign_logs (campaign_id,user_id) VALUES (?,?)', array($camp['id'],$u['id']));
                        if ($camp['offer_type'] === 'points_bonus' && $camp['offer_value'] > 0) {
                            $this->spice_model->add_loyalty_points($u['id'], (int)$camp['offer_value'], 'bonus', 'campaign', $camp['id'], '🎂 Birthday bonus from: '.$camp['name']);
                        }
                        $auto_count++;
                    }
                }
            }

            // Anniversary campaigns (account creation anniversary)
            $anni_campaigns = $this->db->query("SELECT * FROM campaigns WHERE type='anniversary' AND status='active' AND deleted_at IS NULL")->result_array();
            foreach ($anni_campaigns as $camp) {
                $anni_users = $this->db->query(
                    "SELECT id FROM users WHERE role='customer' AND deleted_at IS NULL
                     AND DATE_FORMAT(created_at,'%m-%d')=DATE_FORMAT(NOW(),'%m-%d')
                     AND YEAR(NOW())>YEAR(created_at)"
                )->result_array();
                foreach ($anni_users as $u) {
                    $exists = $this->db->query('SELECT id FROM campaign_logs WHERE campaign_id=? AND user_id=? AND DATE(sent_at)=CURDATE()', array($camp['id'],$u['id']))->row();
                    if (!$exists) {
                        $this->db->query('INSERT INTO campaign_logs (campaign_id,user_id) VALUES (?,?)', array($camp['id'],$u['id']));
                        if ($camp['offer_type'] === 'points_bonus' && $camp['offer_value'] > 0) {
                            $this->spice_model->add_loyalty_points($u['id'], (int)$camp['offer_value'], 'bonus', 'campaign', $camp['id'], '🎉 Anniversary bonus from: '.$camp['name']);
                        }
                        $auto_count++;
                    }
                }
            }

            $this->session->set_flashdata('ly_success', 'Automation run complete. '.$auto_count.' reward(s) sent today.');
            redirect('admin-loyalty');
        }

        // ── Delete campaign ────────────────────────────────────────
        if ($this->input->get('del_campaign')) {
            $cid = (int)$this->input->get('del_campaign');
            $this->db->query('UPDATE campaigns SET deleted_at=NOW() WHERE id=?', array($cid));
            $this->db->query('UPDATE campaign_logs SET deleted_at=NOW() WHERE campaign_id=?', array($cid));
            redirect('admin-loyalty');
        }

        // ── Load data ──────────────────────────────────────────────
        $flash = $this->session->flashdata('ly_success');
        if ($flash) $success = $flash;

        // Settings
        $sr = $this->db->query("SELECT * FROM shipping_settings WHERE key_name LIKE 'loyalty_%'")->result_array();
        $ly = array('loyalty_earn_rate'=>'1','loyalty_earn_per'=>'10','loyalty_redeem_rate'=>'100',
                    'loyalty_redeem_value'=>'10','loyalty_min_redeem'=>'100','loyalty_expiry_days'=>'365');
        foreach ($sr as $s) $ly[$s['key_name']] = $s['key_value'];

        // Customers with segment + loyalty
        $customers = $this->_get_segment_users('all');

        // Campaigns
        $campaigns = $this->db->query('SELECT c.*, (SELECT COUNT(*) FROM campaign_logs WHERE campaign_id=c.id) AS actual_sent FROM campaigns c WHERE c.deleted_at IS NULL ORDER BY c.created_at DESC')->result_array();

        // Recent ledger (last 100 entries)
        $ledger = $this->db->query(
            'SELECT l.*,u.name AS customer_name FROM loyalty_ledger l JOIN users u ON u.id=l.user_id ORDER BY l.created_at DESC LIMIT 100'
        )->result_array();

        // Stats
        $total_pts_issued   = (int)$this->db->query("SELECT COALESCE(SUM(points),0) AS t FROM loyalty_ledger WHERE points>0")->row()->t;
        $total_pts_redeemed = (int)$this->db->query("SELECT COALESCE(SUM(ABS(points)),0) AS t FROM loyalty_ledger WHERE points<0")->row()->t;
        $total_enrolled     = (int)$this->db->query("SELECT COUNT(*) AS cnt FROM user_loyalty WHERE points_earned>0")->row()->cnt;
        $active_campaigns   = (int)$this->db->query("SELECT COUNT(*) AS cnt FROM campaigns WHERE status='active' AND deleted_at IS NULL")->row()->cnt;

        $data['js']                  = 'admin-loyalty.inc';
        $data['page']                = 'loyalty';
        $data['ly']                  = $ly;
        $data['customers']           = $customers;
        $data['campaigns']           = $campaigns;
        $data['ledger']              = $ledger;
        $data['total_pts_issued']    = $total_pts_issued;
        $data['total_pts_redeemed']  = $total_pts_redeemed;
        $data['total_enrolled']      = $total_enrolled;
        $data['active_campaigns']    = $active_campaigns;
        $data['errors']              = $errors;
        $data['success']             = $success;

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/loyalty', $data);
        $this->load->view('inc/footer', $data);
    }

    private function _get_segment_users($segment = 'all', $campaign_type = 'general')
    {
        $having = '';
        switch ($segment) {
            case 'new':
                $having = "HAVING order_count=0 AND DATEDIFF(NOW(),u.created_at)<=30";
                break;
            case 'frequent':
                $having = "HAVING freq_orders>=5";
                break;
            case 'highvalue':
                $having = "HAVING total_spent>=10000";
                break;
            case 'inactive':
                $having = "HAVING (last_order_at IS NULL OR DATEDIFF(NOW(),last_order_at)>90) AND DATEDIFF(NOW(),u.created_at)>30";
                break;
        }
        // birthday / anniversary handled separately via run_auto
        return $this->db->query(
            "SELECT u.id, u.name, u.email, u.phone, u.created_at,
                    COALESCE(ul.points_balance,0)  AS points_balance,
                    COALESCE(ul.points_earned,0)   AS points_earned,
                    COALESCE(ul.tier,'bronze')     AS tier,
                    ul.birthday,
                    COUNT(DISTINCT o.id)           AS order_count,
                    COALESCE(SUM(o.total_amount),0) AS total_spent,
                    MAX(o.created_at)              AS last_order_at,
                    (SELECT COUNT(*) FROM orders o2
                     WHERE o2.user_id=u.id
                       AND o2.created_at>=DATE_SUB(NOW(),INTERVAL 6 MONTH)
                       AND o2.status NOT IN ('cancelled')) AS freq_orders,
                    CASE
                      WHEN COUNT(DISTINCT o.id)=0 AND DATEDIFF(NOW(),u.created_at)<=30 THEN 'new'
                      WHEN (SELECT COUNT(*) FROM orders o3 WHERE o3.user_id=u.id AND o3.created_at>=DATE_SUB(NOW(),INTERVAL 6 MONTH) AND o3.status NOT IN ('cancelled'))>=5 THEN 'frequent'
                      WHEN COALESCE(SUM(o.total_amount),0)>=10000 THEN 'highvalue'
                      WHEN (MAX(o.created_at) IS NULL OR DATEDIFF(NOW(),MAX(o.created_at))>90) AND DATEDIFF(NOW(),u.created_at)>30 THEN 'inactive'
                      ELSE 'regular'
                    END AS segment
             FROM users u
             LEFT JOIN orders o   ON o.user_id=u.id AND o.status NOT IN ('cancelled')
             LEFT JOIN user_loyalty ul ON ul.user_id=u.id
             WHERE u.role='customer' AND u.is_blocked=0 AND u.deleted_at IS NULL
             GROUP BY u.id
             $having
             ORDER BY u.created_at DESC"
        )->result_array();
    }

    // ── Admin Roles ───────────────────────────────────────────────

    public function admin_roles()
    {
        $this->_require_admin();
        if ($this->session->userdata(SESS_HEAD.'_user_role') !== 'admin') redirect('admin');
        $this->_admin_base($data);

        $errors = array(); $success = '';

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $uid      = (int)$this->input->post('user_id');
            $name     = trim($this->input->post('name') ?: '');
            $email    = strtolower(trim($this->input->post('email') ?: ''));
            $password = trim($this->input->post('password') ?: '');
            $role     = $this->input->post('role') === 'staff' ? 'staff' : 'admin';
            $perms    = implode(',', (array)$this->input->post('permissions') ?: array());

            if (!$name || !$email) $errors[] = 'Name and email are required.';

            if (empty($errors)) {
                if ($uid) {
                    $sql = 'UPDATE users SET name=?,email=?,role=?,permissions=?';
                    $params = array($name,$email,$role,$perms);
                    if ($password) { $sql .= ',password=?'; $params[] = $password; }
                    $this->db->query($sql.' WHERE id=?', array_merge($params, array($uid)));
                    $success = 'Admin user updated.';
                } else {
                    if (!$password) $errors[] = 'Password is required for new user.';
                    else {
                        $this->db->query(
                            'INSERT INTO users (name,email,password,role,permissions) VALUES (?,?,?,?,?)',
                            array($name,$email,$password,$role,$perms)
                        );
                        $success = 'Admin user created.';
                    }
                }
            }
        }

        if ($this->input->get('delete')) {
            $uid = (int)$this->input->get('delete');
            $this->db->query('UPDATE users SET deleted_at=NOW() WHERE id=? AND role IN ("admin","staff")', array($uid));
            redirect('admin-roles');
        }

        $admins = $this->db->query(
            'SELECT * FROM users WHERE role IN ("admin","staff") AND deleted_at IS NULL ORDER BY role, name'
        )->result_array();

        $data['js']      = 'admin-roles.inc';
        $data['page']    = 'roles';
        $data['admins']  = $admins;
        $data['errors']  = $errors;
        $data['success'] = $success;

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/roles', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── Reports ───────────────────────────────────────────────────

    public function reports()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        $date_from = $this->input->get('from') ?: date('Y-m-01');
        $date_to   = $this->input->get('to')   ?: date('Y-m-d');

        if ($this->input->get('export') === 'csv') {
            $this->_export_csv($date_from, $date_to);
            return;
        }

        $summary = $this->db->query(
            "SELECT COUNT(*) AS total_orders,
                    COALESCE(SUM(total_amount),0) AS total_revenue,
                    COALESCE(AVG(total_amount),0) AS avg_order,
                    COALESCE(SUM(oi.qty),0) AS units_sold
             FROM orders o
             LEFT JOIN (SELECT order_id, SUM(quantity) AS qty FROM order_items GROUP BY order_id) oi ON oi.order_id=o.id
             WHERE status!='cancelled' AND DATE(o.created_at) BETWEEN ? AND ?",
            array($date_from, $date_to)
        )->row_array();

        $daily = $this->db->query(
            "SELECT DATE(created_at) AS sale_date, COUNT(*) AS orders, SUM(total_amount) AS revenue
             FROM orders WHERE status!='cancelled' AND DATE(created_at) BETWEEN ? AND ?
             GROUP BY sale_date ORDER BY sale_date DESC",
            array($date_from, $date_to)
        )->result_array();

        $top_products = $this->db->query(
            "SELECT oi.product_name AS name, SUM(oi.quantity) AS qty_sold, SUM(oi.quantity*oi.unit_price) AS revenue
             FROM order_items oi
             JOIN orders o ON o.id=oi.order_id
             WHERE o.status!='cancelled' AND DATE(o.created_at) BETWEEN ? AND ?
             GROUP BY oi.product_name ORDER BY revenue DESC LIMIT 10",
            array($date_from, $date_to)
        )->result_array();

        $data['js']            = 'admin-reports.inc';
        $data['page']          = 'reports';
        $data['date_from']     = $date_from;
        $data['date_to']       = $date_to;
        $data['summary']       = $summary;
        $data['daily']         = $daily;
        $data['top_products']  = $top_products;
        $data['chart_labels']  = json_encode(array_column(array_reverse($daily),'sale_date'));
        $data['chart_revenue'] = json_encode(array_map(function($r){ return round((float)$r['revenue'],2); }, array_reverse($daily)));

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/reports', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── Product Edit Page ─────────────────────────────────────────

    public function product_edit($product_id = 0)
    {
        $this->_require_admin();
        $product_id = (int)$product_id;
        if (!$product_id) redirect('admin-products');

        $this->_admin_base($data);

        $categories = $this->db->query('SELECT id,name FROM categories WHERE status=1 AND deleted_at IS NULL ORDER BY name')->result_array();
        $brands     = $this->db->query('SELECT id,name FROM brands WHERE status=1 AND deleted_at IS NULL ORDER BY name')->result_array();

        $product = $this->db->query('SELECT * FROM products WHERE id=?', array($product_id))->row_array();
        if (!$product) redirect('admin-products');

        $errors = array(); $success = '';

        if ($this->input->server('REQUEST_METHOD') === 'POST' && $this->input->post('update_basic')) {
            $cat_id      = (int)$this->input->post('category_id');
            $brand_id    = (int)$this->input->post('brand_id') ?: null;
            $name        = trim($this->input->post('name') ?: '');
            $description = trim($this->input->post('description') ?: '');
            $price       = (float)$this->input->post('price');
            $offer_price = (float)$this->input->post('offer_price') ?: null;
            $gst         = (float)$this->input->post('gst') ?: 0;
            $stock_qty   = (int)$this->input->post('stock_qty');
            $weight      = trim($this->input->post('weight') ?: '');
            $tags        = trim($this->input->post('tags') ?: '');
            $meta_title  = trim($this->input->post('meta_title') ?: '');
            $meta_desc   = trim($this->input->post('meta_desc') ?: '');
            $is_featured = (int)$this->input->post('is_featured');
            $status      = (int)$this->input->post('status');

            if (!$name)      $errors[] = 'Product name is required.';
            if (!$cat_id)    $errors[] = 'Please select a category.';
            if ($price <= 0) $errors[] = 'Price must be greater than 0.';

            $image_name = $product['image'];
            if (!empty($_FILES['image']['name'])) {
                $this->load->library('upload', array(
                    'upload_path'   => FCPATH.'uploads/products/',
                    'allowed_types' => 'jpg|jpeg|png|webp|gif|svg',
                    'max_size'      => 2048,
                    'encrypt_name'  => TRUE,
                ));
                if ($this->upload->do_upload('image')) {
                    $image_name = $this->upload->data('file_name');
                } else {
                    $errors[] = $this->upload->display_errors('','');
                }
            }

            if (empty($errors)) {
                $slug = $this->spice_model->make_slug($name);
                $this->db->query(
                    'UPDATE products SET category_id=?,brand_id=?,name=?,slug=?,description=?,price=?,offer_price=?,gst=?,stock_qty=?,weight=?,image=?,tags=?,meta_title=?,meta_desc=?,is_featured=?,status=? WHERE id=?',
                    array($cat_id,$brand_id,$name,$slug,$description,$price,$offer_price,$gst,$stock_qty,$weight,$image_name,$tags,$meta_title,$meta_desc,$is_featured,$status,$product_id)
                );
                $success = 'Product updated successfully.';
                $product = $this->db->query('SELECT * FROM products WHERE id=?', array($product_id))->row_array();
            }
        }

        $variants = $this->db->query(
            'SELECT * FROM product_variants WHERE product_id=? AND deleted_at IS NULL ORDER BY variant_type, id', array($product_id)
        )->result_array();

        $gallery = $this->db->query(
            'SELECT * FROM product_images WHERE product_id=? AND deleted_at IS NULL ORDER BY is_primary DESC, sort_order', array($product_id)
        )->result_array();

        $data['page']       = 'products';
        $data['js']         = 'admin-product-edit.inc';
        $data['product']    = $product;
        $data['categories'] = $categories;
        $data['brands']     = $brands;
        $data['variants']   = $variants;
        $data['gallery']    = $gallery;
        $data['errors']     = $errors;
        $data['success']    = $success;

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/product-edit', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── Variant AJAX ──────────────────────────────────────────────

    public function get_product_gallery($product_id = 0)
    {
        $this->_require_admin();
        header('Content-Type: application/json');
        $product_id = (int)$product_id;

        $images = $this->db->query(
            'SELECT * FROM product_images WHERE product_id=? AND deleted_at IS NULL ORDER BY is_primary DESC, sort_order',
            array($product_id)
        )->result_array();

        foreach ($images as &$img) {
            $img['url'] = base_url('uploads/products/'.$img['image']);
        }

        echo json_encode(array('success'=>true,'images'=>$images));
    }

    public function get_product_variants($product_id = 0)
    {
        $this->_require_admin();
        header('Content-Type: application/json');
        $product_id = (int)$product_id;

        $product = $this->db->query(
            'SELECT id, name, price, offer_price FROM products WHERE id=?', array($product_id)
        )->row_array();

        if (!$product) {
            echo json_encode(array('success'=>false,'message'=>'Product not found.'));
            return;
        }

        $variants = $this->db->query(
            'SELECT * FROM product_variants WHERE product_id=? AND deleted_at IS NULL ORDER BY variant_type, variant_value',
            array($product_id)
        )->result_array();

        echo json_encode(array(
            'success'  => true,
            'product'  => $product,
            'variants' => $variants,
        ));
    }

    public function variant_save()
    {
        $this->_require_admin();
        header('Content-Type: application/json');

        $product_id     = (int)$this->input->post('product_id');
        $variant_id     = (int)$this->input->post('variant_id');
        $variant_type   = trim($this->input->post('variant_type') ?: '');
        $variant_value  = trim($this->input->post('variant_value') ?: '');
        $price_modifier = (float)$this->input->post('price_modifier');
        $stock_qty      = max(0, (int)$this->input->post('stock_qty'));
        $sku            = trim($this->input->post('sku') ?: '');
        $raw_hex        = trim($this->input->post('color_hex') ?: '');
        $color_hex      = ($variant_type === 'color' && preg_match('/^#[0-9a-fA-F]{3,6}$/', $raw_hex))
                          ? $raw_hex : null;

        if (!$product_id || !$variant_type || !$variant_value) {
            echo json_encode(array('success'=>false,'message'=>'Type and value are required.'));
            return;
        }

        if ($variant_id) {
            $this->db->query(
                'UPDATE product_variants SET variant_type=?,variant_value=?,price_modifier=?,stock_qty=?,sku=?,color_hex=? WHERE id=? AND product_id=?',
                array($variant_type,$variant_value,$price_modifier,$stock_qty,$sku,$color_hex,$variant_id,$product_id)
            );
        } else {
            $this->db->query(
                'INSERT INTO product_variants (product_id,variant_type,variant_value,price_modifier,stock_qty,sku,color_hex) VALUES (?,?,?,?,?,?,?)',
                array($product_id,$variant_type,$variant_value,$price_modifier,$stock_qty,$sku,$color_hex)
            );
            $variant_id = $this->db->insert_id();
        }

        echo json_encode(array('success'=>true,'variant_id'=>(int)$variant_id));
    }

    public function variant_delete($variant_id = 0)
    {
        $this->_require_admin();
        header('Content-Type: application/json');
        $this->db->query('UPDATE product_variants SET deleted_at=NOW() WHERE id=?', array((int)$variant_id));
        echo json_encode(array('success'=>true));
    }

    // ── Gallery Image AJAX ────────────────────────────────────────

    public function image_upload()
    {
        $this->_require_admin();
        header('Content-Type: application/json');

        $product_id = (int)$this->input->post('product_id');
        if (!$product_id || empty($_FILES['images']['name'][0])) {
            echo json_encode(array('success'=>false,'message'=>'No images selected or invalid product.'));
            return;
        }

        $uploaded = array();
        $errors   = array();
        $files    = $_FILES['images'];
        $count    = count($files['name']);

        $has_existing = (int)$this->db->query(
            'SELECT COUNT(*) AS cnt FROM product_images WHERE product_id=? AND deleted_at IS NULL', array($product_id)
        )->row()->cnt;

        $this->load->library('upload', array(
            'upload_path'   => FCPATH.'uploads/products/',
            'allowed_types' => 'jpg|jpeg|png|webp|gif|svg',
            'max_size'      => 2048,
            'encrypt_name'  => TRUE,
        ));

        for ($i = 0; $i < $count; $i++) {
            if (empty($files['name'][$i])) continue;

            $_FILES['img_item'] = array(
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            );

            $this->upload->initialize(array(
                'upload_path'   => FCPATH.'uploads/products/',
                'allowed_types' => 'jpg|jpeg|png|webp|gif|svg',
                'max_size'      => 2048,
                'encrypt_name'  => TRUE,
            ));

            if ($this->upload->do_upload('img_item')) {
                $filename   = $this->upload->data('file_name');
                $is_primary = ($has_existing === 0 && $i === 0) ? 1 : 0;
                $this->db->query(
                    'INSERT INTO product_images (product_id,image,is_primary,sort_order) VALUES (?,?,?,?)',
                    array($product_id, $filename, $is_primary, $has_existing + $i)
                );
                $img_id     = $this->db->insert_id();
                $uploaded[] = array(
                    'id'         => (int)$img_id,
                    'image'      => $filename,
                    'url'        => base_url('uploads/products/'.$filename),
                    'is_primary' => $is_primary,
                );
            } else {
                $errors[] = $files['name'][$i].': '.$this->upload->display_errors('','');
            }
        }

        echo json_encode(array('success'=>true,'uploaded'=>$uploaded,'errors'=>$errors));
    }

    public function image_delete($image_id = 0)
    {
        $this->_require_admin();
        header('Content-Type: application/json');
        $image_id = (int)$image_id;

        $img = $this->db->query('SELECT * FROM product_images WHERE id=? AND deleted_at IS NULL', array($image_id))->row_array();
        if ($img) {
            $this->db->query('UPDATE product_images SET deleted_at=NOW() WHERE id=?', array($image_id));

            if ($img['is_primary']) {
                $next = $this->db->query(
                    'SELECT id FROM product_images WHERE product_id=? AND deleted_at IS NULL ORDER BY sort_order LIMIT 1',
                    array($img['product_id'])
                )->row();
                if ($next) {
                    $this->db->query('UPDATE product_images SET is_primary=1 WHERE id=?', array($next->id));
                }
            }
        }

        echo json_encode(array('success'=>true));
    }

    public function image_set_primary($image_id = 0)
    {
        $this->_require_admin();
        header('Content-Type: application/json');
        $image_id = (int)$image_id;

        $img = $this->db->query('SELECT product_id FROM product_images WHERE id=?', array($image_id))->row();
        if ($img) {
            $this->db->query('UPDATE product_images SET is_primary=0 WHERE product_id=?', array($img->product_id));
            $this->db->query('UPDATE product_images SET is_primary=1 WHERE id=?', array($image_id));
        }

        echo json_encode(array('success'=>true));
    }

    // ── Site Settings ─────────────────────────────────────────

    public function site_settings()
    {
        $this->_require_admin();
        if ($this->session->userdata(SESS_HEAD.'_user_role') !== 'admin') redirect('admin');
        $this->_admin_base($data);

        $success = '';
        $errors  = array();
        $tab     = $this->input->post('settings_tab') ?: ($this->input->get('tab') ?: 'general');

        if ($this->input->server('REQUEST_METHOD') === 'POST') {

            $map = array(
                'general' => array('site_name','site_tagline','top_strip_text'),
                'contact' => array('contact_phone','contact_email','contact_address'),
                'social'  => array('social_facebook','social_instagram','social_youtube',
                                   'social_whatsapp','social_twitter'),
                'footer'  => array('footer_about','footer_copyright'),
                'seo'     => array('meta_title','meta_desc','google_analytics'),
            );

            $keys = isset($map[$tab]) ? $map[$tab] : array();
            foreach ($keys as $key) {
                $this->_save_setting($key, trim($this->input->post($key) ?: ''));
            }

            // Logo upload (general tab only)
            if ($tab === 'general' && !empty($_FILES['site_logo']['name'])) {
                if (!is_dir(FCPATH.'uploads/logo/')) {
                    mkdir(FCPATH.'uploads/logo/', 0755, true);
                }
                $this->load->library('upload', array(
                    'upload_path'   => FCPATH.'uploads/logo/',
                    'allowed_types' => 'jpg|jpeg|png|webp|svg|gif',
                    'max_size'      => 1024,
                    'encrypt_name'  => TRUE,
                ));
                if ($this->upload->do_upload('site_logo')) {
                    $this->_save_setting('site_logo', $this->upload->data('file_name'));
                } else {
                    $errors[] = $this->upload->display_errors('','');
                }
            }

            if (empty($errors)) {
                $success = 'Settings saved successfully.';
                // Bust the model cache so next page load picks up new values
                $this->spice_model->get_all_settings();
            }
        }

        $rows = $this->db->query('SELECT key_name, key_value FROM site_settings')->result_array();
        $settings = array();
        foreach ($rows as $r) { $settings[$r['key_name']] = $r['key_value']; }

        $data['page']     = 'settings';
        $data['js']       = 'admin-settings.inc';
        $data['settings'] = $settings;
        $data['success']  = $success;
        $data['errors']   = $errors;
        $data['tab']      = $tab;

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/settings', $data);
        $this->load->view('inc/footer', $data);
    }

    private function _save_setting($key, $value)
    {
        $this->db->query(
            'INSERT INTO site_settings (key_name, key_value) VALUES (?,?)
             ON DUPLICATE KEY UPDATE key_value=?',
            array($key, $value, $value)
        );
    }

    private function _export_csv($from, $to)
    {
        $rows = $this->db->query(
            "SELECT o.id, u.name, u.email, o.total_amount, o.payment_method, o.status, DATE(o.created_at) AS date
             FROM orders o JOIN users u ON u.id=o.user_id
             WHERE DATE(o.created_at) BETWEEN ? AND ? ORDER BY o.created_at DESC",
            array($from, $to)
        )->result_array();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="orders_'.$from.'_to_'.$to.'.csv"');
        $f = fopen('php://output','w');
        fputcsv($f, array('Order ID','Customer','Email','Amount','Payment','Status','Date'));
        foreach ($rows as $r) {
            fputcsv($f, array('#'.str_pad($r['id'],5,'0',STR_PAD_LEFT),$r['name'],$r['email'],'₹'.$r['total_amount'],strtoupper($r['payment_method']),$r['status'],$r['date']));
        }
        fclose($f);
        exit;
    }

    // ── POS Integration ───────────────────────────────────────────

    public function pos()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        $success = ''; $error = '';

        // ── Generate new API key ───────────────────────────────────
        if ($this->input->post('create_api_key')) {
            $label      = trim($this->input->post('label') ?: '');
            $pos_system = trim($this->input->post('pos_system') ?: '');
            if (!$label) { $error = 'Label is required.'; }
            else {
                $new_key = bin2hex(random_bytes(24)); // 48-char hex key
                $this->db->query(
                    'INSERT INTO pos_api_keys (label,api_key,pos_system,sync_stock,sync_price,sync_coupon,sync_avail)
                     VALUES (?,?,?,?,?,?,?)',
                    array(
                        $label, $new_key, $pos_system,
                        (int)(bool)$this->input->post('sync_stock'),
                        (int)(bool)$this->input->post('sync_price'),
                        (int)(bool)$this->input->post('sync_coupon'),
                        (int)(bool)$this->input->post('sync_avail'),
                    )
                );
                $success = 'API key created: '.$new_key;
            }
        }

        // ── Toggle / delete API key ────────────────────────────────
        if ($this->input->get('toggle_key')) {
            $kid = (int)$this->input->get('toggle_key');
            $this->db->query('UPDATE pos_api_keys SET status=1-status WHERE id=?', array($kid));
            redirect('admin-pos?tab=keys');
        }
        if ($this->input->get('delete_key')) {
            $kid = (int)$this->input->get('delete_key');
            $this->db->query('UPDATE pos_api_keys SET deleted_at=NOW() WHERE id=?', array($kid));
            redirect('admin-pos?tab=keys');
        }

        // ── Manual sync ───────────────────────────────────────────
        if ($this->input->post('manual_sync')) {
            $sync_type   = $this->input->post('manual_type');
            $product_code= trim($this->input->post('manual_product_code') ?: '');
            $updated = 0; $err_msg = '';

            $this->db->query(
                'INSERT INTO pos_sync_logs (sync_type,source,records_sent,request_ip,status)
                 VALUES (?,?,?,?,?)',
                array($sync_type,'manual',1,$this->input->ip_address(),'running')
            );
            $log_id = $this->db->insert_id();

            if (!$product_code) {
                $err_msg = 'Product code required.';
            } else {
                switch ($sync_type) {
                    case 'stock':
                        $qty = (int)$this->input->post('manual_stock_qty');
                        $this->db->query('UPDATE products SET stock_qty=? WHERE product_code=?', array($qty, $product_code));
                        $updated = $this->db->affected_rows();
                        break;
                    case 'price':
                        $price = (float)$this->input->post('manual_price');
                        $offer = $this->input->post('manual_offer_price');
                        $offer = ($offer !== '' && $offer !== null) ? (float)$offer : null;
                        if ($offer !== null) {
                            $this->db->query('UPDATE products SET price=?,offer_price=? WHERE product_code=?', array($price,$offer,$product_code));
                        } else {
                            $this->db->query('UPDATE products SET price=? WHERE product_code=?', array($price,$product_code));
                        }
                        $updated = $this->db->affected_rows();
                        break;
                    case 'availability':
                        $avail = (int)(bool)$this->input->post('manual_available');
                        $this->db->query('UPDATE products SET status=? WHERE product_code=?', array($avail,$product_code));
                        $updated = $this->db->affected_rows();
                        break;
                }
                if ($updated === 0) $err_msg = "No product found with code '$product_code'.";
            }

            $final = $err_msg ? 'failed' : 'success';
            $this->db->query(
                'UPDATE pos_sync_logs SET status=?,records_updated=?,error_message=?,completed_at=NOW() WHERE id=?',
                array($final, $updated, $err_msg ?: null, $log_id)
            );
            if ($final === 'success') $success = "Manual $sync_type sync applied to product '$product_code'.";
            else $error = $err_msg;
        }

        // ── Data ──────────────────────────────────────────────────
        $api_keys = $this->db->query(
            'SELECT k.*, (SELECT COUNT(*) FROM pos_sync_logs WHERE api_key_id=k.id) AS sync_count
             FROM pos_api_keys k WHERE k.deleted_at IS NULL ORDER BY k.created_at DESC'
        )->result_array();

        $tab = $this->input->get('tab') ?: 'overview';

        // Log filters
        $log_type   = $this->input->get('log_type')   ?: '';
        $log_status = $this->input->get('log_status') ?: '';
        $log_from   = $this->input->get('log_from')   ?: '';
        $log_to     = $this->input->get('log_to')     ?: '';

        $where = '1=1'; $params = array();
        if ($log_type)   { $where .= ' AND l.sync_type=?';        $params[] = $log_type; }
        if ($log_status) { $where .= ' AND l.status=?';           $params[] = $log_status; }
        if ($log_from)   { $where .= ' AND DATE(l.started_at)>=?';$params[] = $log_from; }
        if ($log_to)     { $where .= ' AND DATE(l.started_at)<=?';$params[] = $log_to; }

        $sync_logs = $this->db->query(
            "SELECT l.*, k.label AS key_label, k.pos_system
             FROM pos_sync_logs l
             LEFT JOIN pos_api_keys k ON k.id=l.api_key_id
             WHERE $where
             ORDER BY l.started_at DESC LIMIT 200",
            $params
        )->result_array();

        // Overview stats
        $stats = $this->db->query(
            "SELECT
               COUNT(*) AS total_syncs,
               SUM(l.status='success')  AS total_success,
               SUM(l.status='failed')   AS total_failed,
               SUM(l.status='partial')  AS total_partial,
               SUM(l.records_updated)   AS total_records_updated,
               SUM(l.sync_type='stock')        AS stock_syncs,
               SUM(l.sync_type='price')        AS price_syncs,
               SUM(l.sync_type='coupon')       AS coupon_syncs,
               SUM(l.sync_type='availability') AS avail_syncs,
               SUM(DATE(l.started_at)=CURDATE()) AS today_syncs,
               MAX(l.started_at) AS last_sync_at
             FROM pos_sync_logs l"
        )->row_array();

        $recent_logs = $this->db->query(
            "SELECT l.*, k.label AS key_label
             FROM pos_sync_logs l LEFT JOIN pos_api_keys k ON k.id=l.api_key_id
             ORDER BY l.started_at DESC LIMIT 8"
        )->result_array();

        // Product lookup for manual sync autocomplete
        $products_with_code = $this->db->query(
            'SELECT product_code, name, price, offer_price, stock_qty, status
             FROM products WHERE product_code IS NOT NULL AND product_code != ""
             ORDER BY name LIMIT 200'
        )->result_array();

        $data['js']                 = 'admin-pos.inc';
        $data['page']               = 'pos';
        $data['tab']                = $tab;
        $data['api_keys']           = $api_keys;
        $data['sync_logs']          = $sync_logs;
        $data['stats']              = $stats;
        $data['recent_logs']        = $recent_logs;
        $data['products_with_code'] = $products_with_code;
        $data['log_type']           = $log_type;
        $data['log_status']         = $log_status;
        $data['log_from']           = $log_from;
        $data['log_to']             = $log_to;
        $data['success']            = $success;
        $data['error']              = $error;
        $data['webhook_url']        = site_url('pos-sync');

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/pos', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── AJAX: POS manual sync ─────────────────────────────────

    public function ajax_pos_sync()
    {
        $this->_require_admin();
        header('Content-Type: application/json');

        $sync_type    = $this->input->post('manual_type');
        $product_code = trim($this->input->post('manual_product_code') ?: '');
        $updated = 0; $err_msg = '';

        $this->db->query(
            'INSERT INTO pos_sync_logs (sync_type,source,records_sent,request_ip,status) VALUES (?,?,?,?,?)',
            array($sync_type, 'manual', 1, $this->input->ip_address(), 'running')
        );
        $log_id = $this->db->insert_id();

        if (!$product_code) {
            $err_msg = 'Product code is required.';
        } else {
            switch ($sync_type) {
                case 'stock':
                    $qty = (int)$this->input->post('manual_stock_qty');
                    $this->db->query('UPDATE products SET stock_qty=? WHERE product_code=?', array($qty, $product_code));
                    $updated = $this->db->affected_rows();
                    if (!$updated) $err_msg = "No product found with code '$product_code'.";
                    break;
                case 'price':
                    $price = (float)$this->input->post('manual_price');
                    $offer = $this->input->post('manual_offer_price');
                    $offer = ($offer !== '' && $offer !== null) ? (float)$offer : null;
                    if ($offer !== null) {
                        $this->db->query('UPDATE products SET price=?,offer_price=? WHERE product_code=?', array($price, $offer, $product_code));
                    } else {
                        $this->db->query('UPDATE products SET price=? WHERE product_code=?', array($price, $product_code));
                    }
                    $updated = $this->db->affected_rows();
                    if (!$updated) $err_msg = "No product found with code '$product_code'.";
                    break;
                case 'availability':
                    $avail = (int)(bool)$this->input->post('manual_available');
                    $this->db->query('UPDATE products SET status=? WHERE product_code=?', array($avail, $product_code));
                    $updated = $this->db->affected_rows();
                    if (!$updated) $err_msg = "No product found with code '$product_code'.";
                    break;
                default:
                    $err_msg = 'Invalid sync type.';
            }
        }

        $final = $err_msg ? 'failed' : 'success';
        $this->db->query(
            'UPDATE pos_sync_logs SET status=?,records_updated=?,error_message=?,completed_at=NOW() WHERE id=?',
            array($final, $updated, $err_msg ?: null, $log_id)
        );

        // Fetch updated product info for UI refresh
        $product = null;
        if (!$err_msg && $product_code) {
            $product = $this->db->query(
                'SELECT product_code, name, price, offer_price, stock_qty, status FROM products WHERE product_code=?',
                array($product_code)
            )->row_array();
        }

        echo json_encode(array(
            'success' => !$err_msg,
            'message' => $err_msg ?: ucfirst($sync_type)." sync applied to product '$product_code'.",
            'product' => $product,
            'log_id'  => $log_id,
        ));
    }

    // ── AJAX: Returns status update ───────────────────────────

    public function ajax_returns_update()
    {
        $this->_require_admin();
        header('Content-Type: application/json');

        $ret_id     = (int)$this->input->post('return_id');
        $ret_status = $this->input->post('ret_status');
        $note       = trim($this->input->post('admin_note') ?: '');
        $allowed    = array('pending','approved','rejected','resolved');

        if (!$ret_id || !in_array($ret_status, $allowed)) {
            echo json_encode(array('success'=>false,'message'=>'Invalid request.'));
            return;
        }

        $this->db->query('UPDATE returns SET status=?,admin_note=? WHERE id=?', array($ret_status, $note, $ret_id));

        if ($ret_status === 'approved') {
            $ret = $this->db->query('SELECT * FROM returns WHERE id=?', array($ret_id))->row_array();
            if ($ret && $ret['type'] === 'return') {
                $items = $this->db->query('SELECT * FROM order_items WHERE order_id=?', array($ret['order_id']))->result_array();
                foreach ($items as $it) {
                    $this->db->query('UPDATE products SET stock_qty=stock_qty+? WHERE id=?', array($it['quantity'], $it['product_id']));
                }
                $this->db->query("UPDATE orders SET status='returned' WHERE id=?", array($ret['order_id']));
            }
        }

        echo json_encode(array(
            'success'    => true,
            'message'    => 'Request '.ucfirst($ret_status).' successfully.',
            'ret_id'     => $ret_id,
            'new_status' => $ret_status,
        ));
    }

    // ── AJAX: Orders status update ────────────────────────────

    public function ajax_orders_update()
    {
        $this->_require_admin();
        header('Content-Type: application/json');

        $order_id    = (int)$this->input->post('order_id');
        $status      = $this->input->post('status');
        $tracking_no = trim($this->input->post('tracking_no') ?: '');
        $courier     = trim($this->input->post('courier_name') ?: '');
        $allowed     = array('pending','processing','shipped','delivered','cancelled');

        if (!$order_id || !in_array($status, $allowed)) {
            echo json_encode(array('success'=>false,'message'=>'Invalid request.'));
            return;
        }

        $this->db->query(
            'UPDATE orders SET status=?,tracking_no=?,courier_name=? WHERE id=?',
            array($status, $tracking_no, $courier, $order_id)
        );

        if ($status === 'delivered') {
            $this->db->query("UPDATE orders SET payment_status='paid' WHERE id=? AND payment_method='cod'", array($order_id));
            $already = $this->db->query(
                "SELECT id FROM loyalty_ledger WHERE ref_type='order' AND ref_id=? AND type='earned' LIMIT 1",
                array($order_id)
            )->row();
            if (!$already) {
                $ord = $this->db->query('SELECT user_id, total_amount FROM orders WHERE id=?', array($order_id))->row_array();
                $earn_rate = (int)($this->spice_model->get_setting('loyalty_earn_rate') ?: 1);
                $earn_per  = (int)($this->spice_model->get_setting('loyalty_earn_per')  ?: 10);
                if ($earn_per > 0 && $ord) {
                    $pts = (int)floor(((float)$ord['total_amount'] / $earn_per) * $earn_rate);
                    if ($pts > 0) {
                        $this->spice_model->add_loyalty_points(
                            $ord['user_id'], $pts, 'earned', 'order', $order_id,
                            'Earned on Order #'.str_pad($order_id, 5, '0', STR_PAD_LEFT)
                        );
                    }
                }
            }
        }

        // Return new payment_status for UI update
        $pay_status = $this->db->query('SELECT payment_status FROM orders WHERE id=?', array($order_id))->row()->payment_status ?? '';

        echo json_encode(array(
            'success'        => true,
            'message'        => 'Order #'.str_pad($order_id,5,'0',STR_PAD_LEFT).' updated to '.ucfirst($status).'.',
            'order_id'       => $order_id,
            'new_status'     => $status,
            'payment_status' => $pay_status,
        ));
    }

    // ── Reviews Management ───────────────────────────────────────
    public function reviews()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        $filter_status  = $this->input->get('status')   ?: '';
        $filter_rating  = (int)$this->input->get('rating');
        $filter_date    = $this->input->get('date_from') ?: '';
        $filter_search  = trim($this->input->get('q')   ?: '');

        // ── Stats ─────────────────────────────────────────────────
        $stats = $this->db->query(
            "SELECT
               COUNT(*) AS total,
               SUM(status='approved')  AS approved,
               SUM(status='pending')   AS pending,
               SUM(status='rejected')  AS rejected,
               SUM(is_featured=1)      AS featured,
               COALESCE(ROUND(AVG(CASE WHEN status='approved' THEN rating END),1),0) AS avg_rating
             FROM reviews WHERE status != 'deleted'"
        )->row_array();

        $rating_dist = $this->db->query(
            "SELECT rating, COUNT(*) AS cnt FROM reviews WHERE status='approved' GROUP BY rating ORDER BY rating DESC"
        )->result_array();

        // ── Filtered list ─────────────────────────────────────────
        $where = "r.status != 'deleted'"; $params = array();
        if ($filter_status)       { $where .= ' AND r.status=?';           $params[] = $filter_status; }
        if ($filter_rating > 0)   { $where .= ' AND r.rating=?';           $params[] = $filter_rating; }
        if ($filter_date)         { $where .= ' AND DATE(r.created_at)>=?'; $params[] = $filter_date; }
        if ($filter_search !== '') {
            $where .= ' AND (u.name LIKE ? OR p.name LIKE ? OR r.comment LIKE ?)';
            $s = '%'.$filter_search.'%';
            $params[] = $s; $params[] = $s; $params[] = $s;
        }

        $reviews = $this->db->query(
            "SELECT r.*, u.name AS customer_name, p.name AS product_name, p.id AS pid
               FROM reviews r
               JOIN users u ON u.id=r.user_id
               JOIN products p ON p.id=r.product_id
              WHERE $where
              ORDER BY r.created_at DESC LIMIT 200", $params
        )->result_array();

        $data['page']           = 'reviews';
        $data['title']          = 'Review Management';
        $data['stats']          = $stats;
        $data['rating_dist']    = $rating_dist;
        $data['reviews']        = $reviews;
        $data['filter_status']  = $filter_status;
        $data['filter_rating']  = $filter_rating;
        $data['filter_date']    = $filter_date;
        $data['filter_search']  = $filter_search;
        $data['js']             = 'admin-reviews.inc';

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/reviews', $data);
        $this->load->view('inc/footer', $data);
    }

    // POST  ajax/review-action
    // Body: review_id, action (approve|reject|delete|feature|unfeature)
    //       OR bulk_ids[], bulk_action
    public function ajax_review_action()
    {
        $this->_require_admin();
        header('Content-Type: application/json');

        $bulk_ids    = $this->input->post('bulk_ids')    ?: array();
        $bulk_action = trim($this->input->post('bulk_action') ?: '');
        $review_id   = (int)$this->input->post('review_id');
        $action      = trim($this->input->post('action')      ?: '');

        // ── Bulk action ───────────────────────────────────────────
        if (!empty($bulk_ids) && $bulk_action) {
            $ids = array_map('intval', (array)$bulk_ids);
            $ids = array_filter($ids);
            if (empty($ids)) { echo json_encode(array('success'=>false,'message'=>'No reviews selected.')); return; }
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            if ($bulk_action === 'approve') {
                $this->db->query("UPDATE reviews SET status='approved' WHERE id IN ($placeholders)", $ids);
                echo json_encode(array('success'=>true,'message'=>count($ids).' review(s) approved.','bulk'=>true,'new_status'=>'approved','ids'=>$ids)); return;
            }
            if ($bulk_action === 'reject') {
                $this->db->query("UPDATE reviews SET status='rejected' WHERE id IN ($placeholders)", $ids);
                echo json_encode(array('success'=>true,'message'=>count($ids).' review(s) rejected.','bulk'=>true,'new_status'=>'rejected','ids'=>$ids)); return;
            }
            if ($bulk_action === 'delete') {
                $this->db->query("UPDATE reviews SET status='deleted' WHERE id IN ($placeholders)", $ids);
                echo json_encode(array('success'=>true,'message'=>count($ids).' review(s) deleted.','bulk'=>true,'deleted'=>true,'ids'=>$ids)); return;
            }
            echo json_encode(array('success'=>false,'message'=>'Unknown bulk action.')); return;
        }

        // ── Single action ─────────────────────────────────────────
        if (!$review_id) { echo json_encode(array('success'=>false,'message'=>'Invalid review.')); return; }

        $map = array(
            'approve'   => array('sql'=>"UPDATE reviews SET status='approved'  WHERE id=?", 'label'=>'Approved'),
            'reject'    => array('sql'=>"UPDATE reviews SET status='rejected'  WHERE id=?", 'label'=>'Rejected'),
            'pending'   => array('sql'=>"UPDATE reviews SET status='pending'   WHERE id=?", 'label'=>'Pending'),
            'feature'   => array('sql'=>"UPDATE reviews SET is_featured=1      WHERE id=?", 'label'=>'Featured'),
            'unfeature' => array('sql'=>"UPDATE reviews SET is_featured=0      WHERE id=?", 'label'=>'Unfeatured'),
        );

        if ($action === 'delete') {
            $this->db->query("UPDATE reviews SET status='deleted' WHERE id=?", array($review_id));
            echo json_encode(array('success'=>true,'message'=>'Review deleted.','deleted'=>true,'review_id'=>$review_id)); return;
        }

        if (!isset($map[$action])) { echo json_encode(array('success'=>false,'message'=>'Unknown action.')); return; }

        $this->db->query($map[$action]['sql'], array($review_id));
        echo json_encode(array(
            'success'    => true,
            'message'    => $map[$action]['label'].'.',
            'review_id'  => $review_id,
            'action'     => $action,
        ));
    }

    // ── Fazaa / Isaad Settings ────────────────────────────────────
    public function fazaa_settings()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        $success = ''; $errors = array();

        if ($this->input->post('save_fazaa')) {
            $programs = array('fazaa', 'isaad');
            foreach ($programs as $prog) {
                $enabled      = $this->input->post('enabled_'.$prog)     ? 1 : 0;
                $discount_pct = (float)$this->input->post('discount_pct_'.$prog);
                $max_discount = (float)$this->input->post('max_discount_'.$prog);
                $min_order    = (float)$this->input->post('min_order_'.$prog);
                $api_url      = trim($this->input->post('api_url_'.$prog)  ?: '');
                $api_key      = trim($this->input->post('api_key_'.$prog)  ?: '');
                $otp_enabled  = $this->input->post('otp_enabled_'.$prog)  ? 1 : 0;
                $logo_url     = trim($this->input->post('logo_url_'.$prog) ?: '');

                $this->db->query(
                    'UPDATE fazaa_settings SET enabled=?, discount_pct=?, max_discount=?, min_order=?,
                     api_url=?, api_key=?, otp_enabled=?, logo_url=? WHERE program=?',
                    array($enabled, $discount_pct, $max_discount, $min_order,
                          $api_url, $api_key, $otp_enabled, $logo_url, $prog)
                );
            }
            $success = 'Fazaa / Isaad settings saved.';
        }

        $programs = $this->db->query('SELECT * FROM fazaa_settings ORDER BY id')->result_array();
        $prog_map  = array();
        foreach ($programs as $p) $prog_map[$p['program']] = $p;

        $data['page']     = 'fazaa';
        $data['title']    = 'Fazaa / Isaad Settings';
        $data['success']  = $success;
        $data['errors']   = $errors;
        $data['prog_map'] = $prog_map;
        $data['js']       = '';

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/fazaa-settings', $data);
        $this->load->view('inc/footer', $data);
    }

    // ── Fazaa / Isaad Usage Report ────────────────────────────────
    public function fazaa_report()
    {
        $this->_require_admin();
        $this->_admin_base($data);

        $filter_program  = $this->input->get('program')   ?: '';
        $filter_date_from= $this->input->get('date_from') ?: '';
        $filter_date_to  = $this->input->get('date_to')   ?: '';

        // ── CSV export ────────────────────────────────────────────
        if ($this->input->get('export') === 'csv') {
            $where = '1=1';
            $params = array();
            if ($filter_program)   { $where .= ' AND fu.program=?';                    $params[] = $filter_program; }
            if ($filter_date_from) { $where .= ' AND DATE(fu.created_at)>=?';          $params[] = $filter_date_from; }
            if ($filter_date_to)   { $where .= ' AND DATE(fu.created_at)<=?';          $params[] = $filter_date_to; }
            $rows = $this->db->query(
                "SELECT fu.id, fu.program, fu.member_no, u.name AS customer_name, fu.order_id,
                        fu.discount_pct, fu.discount_amt, fu.order_total, fu.created_at
                   FROM fazaa_usages fu LEFT JOIN users u ON u.id=fu.user_id
                  WHERE $where ORDER BY fu.id DESC", $params
            )->result_array();

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="fazaa-report-'.date('Ymd').'.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, array('ID','Program','Member No','Customer','Order','Disc%','Disc Amt','Order Total','Date'));
            foreach ($rows as $r) {
                fputcsv($out, array($r['id'], strtoupper($r['program']), $r['member_no'],
                    $r['customer_name'], '#'.str_pad($r['order_id'],5,'0',STR_PAD_LEFT),
                    $r['discount_pct'].'%', '₹'.$r['discount_amt'], '₹'.$r['order_total'],
                    date('d M Y', strtotime($r['created_at']))));
            }
            fclose($out);
            exit;
        }

        // ── Stats ─────────────────────────────────────────────────
        $stats = $this->db->query(
            "SELECT COUNT(*) AS total_uses,
                    COALESCE(SUM(discount_amt),0) AS total_discount,
                    COUNT(DISTINCT member_no) AS unique_members,
                    COALESCE(SUM(order_total),0) AS total_gmv
               FROM fazaa_usages"
        )->row_array();

        $by_program = $this->db->query(
            "SELECT program, COUNT(*) AS uses, COALESCE(SUM(discount_amt),0) AS disc_total
               FROM fazaa_usages GROUP BY program"
        )->result_array();

        // ── Filtered list ─────────────────────────────────────────
        $where = '1=1'; $params = array();
        if ($filter_program)   { $where .= ' AND fu.program=?';           $params[] = $filter_program; }
        if ($filter_date_from) { $where .= ' AND DATE(fu.created_at)>=?'; $params[] = $filter_date_from; }
        if ($filter_date_to)   { $where .= ' AND DATE(fu.created_at)<=?'; $params[] = $filter_date_to; }

        $usages = $this->db->query(
            "SELECT fu.id, fu.program, fu.member_no, u.name AS customer_name,
                    fu.order_id, fu.discount_pct, fu.discount_amt, fu.order_total, fu.created_at
               FROM fazaa_usages fu LEFT JOIN users u ON u.id=fu.user_id
              WHERE $where ORDER BY fu.id DESC LIMIT 200", $params
        )->result_array();

        $data['page']           = 'fazaa';
        $data['title']          = 'Fazaa / Isaad Report';
        $data['stats']          = $stats;
        $data['by_program']     = $by_program;
        $data['usages']         = $usages;
        $data['filter_program'] = $filter_program;
        $data['filter_date_from']= $filter_date_from;
        $data['filter_date_to'] = $filter_date_to;
        $data['js']             = '';

        $this->load->view('inc/header', $data);
        $this->load->view('inc/left-menu', $data);
        $this->load->view('page/admin/fazaa-report', $data);
        $this->load->view('inc/footer', $data);
    }
}
