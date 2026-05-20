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
        $categories = $this->db->query('SELECT id,name FROM categories WHERE status=1 ORDER BY name')->result_array();
        $brands     = $this->db->query('SELECT id,name FROM brands WHERE status=1 ORDER BY name')->result_array();

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $post_id     = (int)$this->input->post('product_id');
            $cat_id      = (int)$this->input->post('category_id');
            $brand_id    = (int)$this->input->post('brand_id') ?: null;
            $name        = trim($this->input->post('name'));
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
                    'allowed_types' => 'jpg|jpeg|png|webp|gif',
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
                    $success = 'Product added.';
                }
            }
        }

        $action  = $this->input->get('action') ?: '';
        $edit_id = (int)$this->input->get('edit');
        if ($action === 'delete' && $edit_id) {
            // status = -1 marks the product as soft-deleted (hidden from both admin list and shop)
            $this->db->query('UPDATE products SET status=-1 WHERE id=?', array($edit_id));
            $this->session->set_flashdata('success', 'Product deleted. You can restore it from the Deleted filter.');
            redirect('admin-products');
        }
        if ($action === 'restore' && $edit_id) {
            $this->db->query('UPDATE products SET status=0 WHERE id=?', array($edit_id));
            $this->session->set_flashdata('success', 'Product restored as Inactive. Use the toggle to activate it.');
            redirect('admin-products?filter=deleted');
        }
        if ($action === 'toggle' && $edit_id) {
            // Only toggle between active (1) and inactive (0); do not touch deleted (-1)
            $this->db->query('UPDATE products SET status=1-status WHERE id=? AND status >= 0', array($edit_id));
            redirect('admin-products');
        }

        $filter      = $this->input->get('filter') ?: '';
        $filter_low  = ($filter === 'low_stock');
        $filter_del  = ($filter === 'deleted');
        if ($filter_low) {
            $products = $this->db->query(
                'SELECT p.*,c.name AS cat_name,b.name AS brand_name FROM products p
                 JOIN categories c ON c.id=p.category_id
                 LEFT JOIN brands b ON b.id=p.brand_id
                 WHERE p.stock_qty<20 AND p.status=1 ORDER BY p.stock_qty'
            )->result_array();
        } elseif ($filter_del) {
            $products = $this->db->query(
                'SELECT p.*,c.name AS cat_name,b.name AS brand_name FROM products p
                 JOIN categories c ON c.id=p.category_id
                 LEFT JOIN brands b ON b.id=p.brand_id
                 WHERE p.status=-1 ORDER BY p.id DESC'
            )->result_array();
        } else {
            $products = $this->db->query(
                'SELECT p.*,c.name AS cat_name,b.name AS brand_name FROM products p
                 JOIN categories c ON c.id=p.category_id
                 LEFT JOIN brands b ON b.id=p.brand_id
                 WHERE p.status >= 0 ORDER BY p.id DESC'
            )->result_array();
        }

        $deleted_count = (int)$this->db->query(
            'SELECT COUNT(*) AS cnt FROM products WHERE status=-1'
        )->row()->cnt;

        $data['js']            = 'admin-products.inc';
        $data['page']          = 'products';
        $data['products']      = $products;
        $data['categories']    = $categories;
        $data['brands']        = $brands;
        $data['errors']        = $errors;
        $data['success']       = $success;
        $data['filter_low']    = $filter_low;
        $data['filter_del']    = $filter_del;
        $data['deleted_count'] = $deleted_count;

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
            $name      = trim($this->input->post('name'));
            $status    = (int)$this->input->post('status');

            if (!$name) $errors[] = 'Category name is required.';

            $image_name = trim($this->input->post('existing_image') ?: '');
            if (!empty($_FILES['image']['name'])) {
                $this->load->library('upload', array(
                    'upload_path'   => FCPATH.'uploads/products/',
                    'allowed_types' => 'jpg|jpeg|png|webp|gif',
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
            $count = (int)$this->db->query('SELECT COUNT(*) AS cnt FROM products WHERE category_id=?', array($edit_id))->row()->cnt;
            if ($count > 0) {
                $this->session->set_flashdata('danger','Cannot delete — '.$count.' product(s) are linked.');
            } else {
                $this->db->query('DELETE FROM categories WHERE id=?', array($edit_id));
                $this->session->set_flashdata('success','Category deleted.');
            }
            redirect('admin-categories');
        }

        $cats = $this->db->query(
            'SELECT c.*, p.name AS parent_name,
                    (SELECT COUNT(*) FROM products WHERE category_id=c.id) AS product_count
             FROM categories c LEFT JOIN categories p ON p.id=c.parent_id
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
            $name     = trim($this->input->post('name'));
            $status   = (int)$this->input->post('status');
            if (!$name) $errors[] = 'Brand name is required.';

            $image_name = trim($this->input->post('existing_image') ?: '');
            if (!empty($_FILES['image']['name'])) {
                $this->load->library('upload', array(
                    'upload_path'   => FCPATH.'uploads/products/',
                    'allowed_types' => 'jpg|jpeg|png|webp',
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
            $this->db->query('DELETE FROM brands WHERE id=?', array($edit_id));
            redirect('admin-brands');
        }

        $brands = $this->db->query(
            'SELECT b.*, (SELECT COUNT(*) FROM products WHERE brand_id=b.id) AS product_count FROM brands b ORDER BY b.name'
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
             WHERE u.role='customer'
             GROUP BY u.id ORDER BY u.created_at DESC"
        )->result_array();

        $view_customer = null; $cust_orders = array();
        $view_id       = (int)$this->input->get('view');
        if ($view_id) {
            $view_customer = $this->db->query(
                'SELECT * FROM users WHERE id=? AND role="customer"', array($view_id)
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
            $coupon_id   = (int)$this->input->post('coupon_id');
            $code        = strtoupper(trim($this->input->post('code')));
            $type        = $this->input->post('type');
            $value       = (float)$this->input->post('value');
            $min_order   = (float)$this->input->post('min_order') ?: 0;
            $max_discount= (float)$this->input->post('max_discount') ?: null;
            $uses_limit  = (int)$this->input->post('uses_limit') ?: null;
            $expires_at  = $this->input->post('expires_at') ?: null;
            $status      = (int)$this->input->post('status');

            if (!$code)   $errors[] = 'Coupon code is required.';
            if ($value<=0)$errors[] = 'Value must be greater than 0.';

            if (empty($errors)) {
                if ($coupon_id) {
                    $this->db->query(
                        'UPDATE coupons SET code=?,type=?,value=?,min_order=?,max_discount=?,uses_limit=?,expires_at=?,status=? WHERE id=?',
                        array($code,$type,$value,$min_order,$max_discount,$uses_limit,$expires_at,$status,$coupon_id)
                    );
                    $success = 'Coupon updated.';
                } else {
                    $this->db->query(
                        'INSERT INTO coupons (code,type,value,min_order,max_discount,uses_limit,expires_at,status) VALUES (?,?,?,?,?,?,?,?)',
                        array($code,$type,$value,$min_order,$max_discount,$uses_limit,$expires_at,$status)
                    );
                    $success = 'Coupon created.';
                }
            }
        }

        $action  = $this->input->get('action') ?: '';
        $edit_id = (int)$this->input->get('edit');
        if ($action === 'delete' && $edit_id) {
            $this->db->query('DELETE FROM coupons WHERE id=?', array($edit_id));
            redirect('admin-coupons');
        }

        $coupons = $this->db->query('SELECT * FROM coupons ORDER BY created_at DESC')->result_array();

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

        $errors = array(); $success = '';

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $banner_id = (int)$this->input->post('banner_id');
            $title     = trim($this->input->post('title') ?: '');
            $subtitle  = trim($this->input->post('subtitle') ?: '');
            $link_url  = trim($this->input->post('link_url') ?: '');
            $btn_text  = trim($this->input->post('btn_text') ?: 'Shop Now');
            $type      = $this->input->post('type') ?: 'slider';
            $sort_order= (int)$this->input->post('sort_order');
            $status    = (int)$this->input->post('status');

            $image_name = trim($this->input->post('existing_image') ?: '');
            if (!empty($_FILES['image']['name'])) {
                $this->load->library('upload', array(
                    'upload_path'   => FCPATH.'uploads/banners/',
                    'allowed_types' => 'jpg|jpeg|png|webp',
                    'max_size'      => 3072,
                    'encrypt_name'  => TRUE,
                ));
                if ($this->upload->do_upload('image')) {
                    $image_name = $this->upload->data('file_name');
                } else {
                    $errors[] = $this->upload->display_errors('','');
                }
            }

            if (!$image_name && !$banner_id) $errors[] = 'Banner image is required.';

            if (empty($errors)) {
                if ($banner_id) {
                    $this->db->query(
                        'UPDATE banners SET title=?,subtitle=?,image=?,link_url=?,btn_text=?,type=?,sort_order=?,status=? WHERE id=?',
                        array($title,$subtitle,$image_name,$link_url,$btn_text,$type,$sort_order,$status,$banner_id)
                    );
                    $success = 'Banner updated.';
                } else {
                    $this->db->query(
                        'INSERT INTO banners (title,subtitle,image,link_url,btn_text,type,sort_order,status) VALUES (?,?,?,?,?,?,?,?)',
                        array($title,$subtitle,$image_name,$link_url,$btn_text,$type,$sort_order,$status)
                    );
                    $success = 'Banner added.';
                }
            }
        }

        $action  = $this->input->get('action') ?: '';
        $edit_id = (int)$this->input->get('edit');
        if ($action === 'delete' && $edit_id) {
            $this->db->query('DELETE FROM banners WHERE id=?', array($edit_id));
            redirect('admin-banners');
        }

        $banners = $this->db->query('SELECT * FROM banners ORDER BY type, sort_order')->result_array();

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
            $title     = trim($this->input->post('title'));
            $slug      = $this->spice_model->make_slug(trim($this->input->post('slug') ?: $title));
            $content   = $this->input->post('content');
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
            $this->db->query('DELETE FROM cms_pages WHERE id=?', array($edit_id));
            redirect('admin-cms');
        }

        $pages = $this->db->query('SELECT * FROM cms_pages ORDER BY title')->result_array();

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
            $title       = trim($this->input->post('title'));
            $description = trim($this->input->post('description'));
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
            $this->db->query('DELETE FROM why_choose_us WHERE id=?', array($edit_id));
            redirect('admin-why-choose-us');
        }

        $items = $this->db->query('SELECT * FROM why_choose_us ORDER BY sort_order, id')->result_array();

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
            $customer_name  = trim($this->input->post('customer_name'));
            $rating         = (int)$this->input->post('rating');
            $quote          = trim($this->input->post('quote'));
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
            $this->db->query('DELETE FROM testimonials WHERE id=?', array($edit_id));
            redirect('admin-testimonials');
        }

        $testimonials = $this->db->query('SELECT * FROM testimonials ORDER BY sort_order, id')->result_array();

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

        $success = '';
        if ($this->input->post('update_return')) {
            $ret_id    = (int)$this->input->post('return_id');
            $ret_status= $this->input->post('ret_status');
            $note      = trim($this->input->post('admin_note') ?: '');
            $this->db->query(
                'UPDATE returns SET status=?,admin_note=? WHERE id=?', array($ret_status,$note,$ret_id)
            );
            $success = 'Return/cancel request updated.';
        }

        $returns = $this->db->query(
            'SELECT r.*, o.total_amount, o.status AS order_status, u.name AS customer_name, u.email
             FROM returns r
             JOIN orders o ON o.id=r.order_id
             JOIN users u ON u.id=r.user_id
             ORDER BY r.created_at DESC'
        )->result_array();

        $data['js']      = 'admin-returns.inc';
        $data['page']    = 'returns';
        $data['returns'] = $returns;
        $data['success'] = $success;

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
            $keys = array('free_shipping_above','standard_charge','express_charge',
                          'razorpay_key_id','razorpay_key_secret','estimated_days');
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

    // ── Admin Roles ───────────────────────────────────────────────

    public function admin_roles()
    {
        $this->_require_admin();
        if ($this->session->userdata(SESS_HEAD.'_user_role') !== 'admin') redirect('admin');
        $this->_admin_base($data);

        $errors = array(); $success = '';

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $uid      = (int)$this->input->post('user_id');
            $name     = trim($this->input->post('name'));
            $email    = strtolower(trim($this->input->post('email')));
            $password = trim($this->input->post('password'));
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
            $this->db->query('DELETE FROM users WHERE id=? AND role IN ("admin","staff")', array($uid));
            redirect('admin-roles');
        }

        $admins = $this->db->query(
            'SELECT * FROM users WHERE role IN ("admin","staff") ORDER BY role, name'
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

        $categories = $this->db->query('SELECT id,name FROM categories WHERE status=1 ORDER BY name')->result_array();
        $brands     = $this->db->query('SELECT id,name FROM brands WHERE status=1 ORDER BY name')->result_array();

        $product = $this->db->query('SELECT * FROM products WHERE id=?', array($product_id))->row_array();
        if (!$product) redirect('admin-products');

        $errors = array(); $success = '';

        if ($this->input->server('REQUEST_METHOD') === 'POST' && $this->input->post('update_basic')) {
            $cat_id      = (int)$this->input->post('category_id');
            $brand_id    = (int)$this->input->post('brand_id') ?: null;
            $name        = trim($this->input->post('name'));
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
                    'allowed_types' => 'jpg|jpeg|png|webp|gif',
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
            'SELECT * FROM product_variants WHERE product_id=? ORDER BY variant_type, id', array($product_id)
        )->result_array();

        $gallery = $this->db->query(
            'SELECT * FROM product_images WHERE product_id=? ORDER BY is_primary DESC, sort_order', array($product_id)
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
            'SELECT * FROM product_images WHERE product_id=? ORDER BY is_primary DESC, sort_order',
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
            'SELECT * FROM product_variants WHERE product_id=? ORDER BY variant_type, variant_value',
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
        $variant_type   = trim($this->input->post('variant_type'));
        $variant_value  = trim($this->input->post('variant_value'));
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
        $this->db->query('DELETE FROM product_variants WHERE id=?', array((int)$variant_id));
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
            'SELECT COUNT(*) AS cnt FROM product_images WHERE product_id=?', array($product_id)
        )->row()->cnt;

        $this->load->library('upload', array(
            'upload_path'   => FCPATH.'uploads/products/',
            'allowed_types' => 'jpg|jpeg|png|webp|gif',
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
                'allowed_types' => 'jpg|jpeg|png|webp|gif',
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

        $img = $this->db->query('SELECT * FROM product_images WHERE id=?', array($image_id))->row_array();
        if ($img) {
            $file = FCPATH.'uploads/products/'.$img['image'];
            if (file_exists($file)) @unlink($file);
            $this->db->query('DELETE FROM product_images WHERE id=?', array($image_id));

            if ($img['is_primary']) {
                $next = $this->db->query(
                    'SELECT id FROM product_images WHERE product_id=? ORDER BY sort_order LIMIT 1',
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
}
