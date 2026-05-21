<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shop extends CI_Controller {

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

    public function index($page_num = 1)
    {
        $this->_front_base($data);

        $q         = $this->input->get('q') ?: '';
        $category  = $this->input->get('category') ?: '';
        $brand_id  = (int)$this->input->get('brand');
        $sort      = $this->input->get('sort') ?: 'newest';
        $max_price = (int)$this->input->get('max_price') ?: 2000;
        $per_page  = 12;
        $page_num  = max(1, (int)$page_num);

        $where  = 'WHERE p.status=1';
        $params = array();

        if ($q) {
            $where   .= ' AND (p.name LIKE ? OR p.tags LIKE ? OR p.description LIKE ?)';
            $params[] = '%'.$q.'%';
            $params[] = '%'.$q.'%';
            $params[] = '%'.$q.'%';
        }

        if ($category) {
            $cat = $this->db->query('SELECT id FROM categories WHERE slug=?', array($category))->row();
            if ($cat) {
                $sub_ids = $this->db->query(
                    'SELECT id FROM categories WHERE id=? OR parent_id=?', array($cat->id, $cat->id)
                )->result_array();
                $ids = implode(',', array_column($sub_ids, 'id'));
                $where .= ' AND p.category_id IN ('.$ids.')';
            }
        }

        if ($brand_id) {
            $where   .= ' AND p.brand_id=?';
            $params[] = $brand_id;
        }

        $where   .= ' AND COALESCE(p.offer_price,p.price) <= ?';
        $params[] = $max_price;

        $sort_sql = array(
            'newest'     => 'p.id DESC',
            'price_asc'  => 'COALESCE(p.offer_price,p.price) ASC',
            'price_desc' => 'COALESCE(p.offer_price,p.price) DESC',
            'popular'    => 'avg_rating DESC',
            'name_asc'   => 'p.name ASC',
        );
        $order_by = isset($sort_sql[$sort]) ? $sort_sql[$sort] : 'p.id DESC';

        $total = (int)$this->db->query(
            'SELECT COUNT(DISTINCT p.id) AS total FROM products p
             JOIN categories c ON c.id=p.category_id '.$where, $params
        )->row()->total;

        $offset   = ($page_num - 1) * $per_page;
        $products = $this->db->query(
            'SELECT p.*, c.name AS cat_name, b.name AS brand_name,
                    COALESCE(AVG(r.rating),0) AS avg_rating,
                    COUNT(DISTINCT r.id) AS review_count
             FROM products p
             JOIN categories c ON c.id=p.category_id
             LEFT JOIN brands b ON b.id=p.brand_id
             LEFT JOIN reviews r ON r.product_id=p.id
             '.$where.'
             GROUP BY p.id
             ORDER BY '.$order_by.'
             LIMIT ? OFFSET ?',
            array_merge($params, array($per_page, $offset))
        )->result_array();

        $brands = $this->db->query(
            'SELECT b.*, COUNT(p.id) AS product_count
             FROM brands b JOIN products p ON p.brand_id=b.id
             WHERE b.status=1 AND p.status=1
             GROUP BY b.id ORDER BY b.name'
        )->result_array();

        $data['js']          = 'shop.inc';
        $data['products']    = $products;
        $data['brands']      = $brands;
        $data['total']       = $total;
        $data['total_pages'] = (int)ceil($total / $per_page);
        $data['page_num']    = $page_num;
        $data['q']           = $q;
        $data['category']    = $category;
        $data['brand_id']    = $brand_id;
        $data['sort']        = $sort;
        $data['max_price']   = $max_price;

        $this->load->view('inc/front-header', $data);
        $this->load->view('page/shop', $data);
        $this->load->view('inc/front-footer', $data);
    }

    public function ajax_products()
    {
        $q         = $this->input->get('q') ?: '';
        $category  = $this->input->get('category') ?: '';
        $brand_id  = (int)$this->input->get('brand');
        $sort      = $this->input->get('sort') ?: 'newest';
        $max_price = (int)$this->input->get('max_price') ?: 2000;
        $page_num  = max(1, (int)$this->input->get('page'));
        $per_page  = 12;

        $where  = 'WHERE p.status=1';
        $params = array();

        if ($q) {
            $where   .= ' AND (p.name LIKE ? OR p.tags LIKE ? OR p.description LIKE ?)';
            $params[] = '%'.$q.'%';
            $params[] = '%'.$q.'%';
            $params[] = '%'.$q.'%';
        }

        if ($category) {
            $cat = $this->db->query('SELECT id FROM categories WHERE slug=?', array($category))->row();
            if ($cat) {
                $sub_ids = $this->db->query(
                    'SELECT id FROM categories WHERE id=? OR parent_id=?', array($cat->id, $cat->id)
                )->result_array();
                $ids = implode(',', array_column($sub_ids, 'id'));
                $where .= ' AND p.category_id IN ('.$ids.')';
            }
        }

        if ($brand_id) {
            $where   .= ' AND p.brand_id=?';
            $params[] = $brand_id;
        }

        $where   .= ' AND COALESCE(p.offer_price,p.price) <= ?';
        $params[] = $max_price;

        $sort_sql = array(
            'newest'     => 'p.id DESC',
            'price_asc'  => 'COALESCE(p.offer_price,p.price) ASC',
            'price_desc' => 'COALESCE(p.offer_price,p.price) DESC',
            'popular'    => 'avg_rating DESC',
            'name_asc'   => 'p.name ASC',
        );
        $order_by = isset($sort_sql[$sort]) ? $sort_sql[$sort] : 'p.id DESC';

        $total = (int)$this->db->query(
            'SELECT COUNT(DISTINCT p.id) AS total FROM products p
             JOIN categories c ON c.id=p.category_id '.$where, $params
        )->row()->total;

        $offset   = ($page_num - 1) * $per_page;
        $products = $this->db->query(
            'SELECT p.*, c.name AS cat_name, b.name AS brand_name,
                    COALESCE(AVG(r.rating),0) AS avg_rating,
                    COUNT(DISTINCT r.id) AS review_count
             FROM products p
             JOIN categories c ON c.id=p.category_id
             LEFT JOIN brands b ON b.id=p.brand_id
             LEFT JOIN reviews r ON r.product_id=p.id
             '.$where.'
             GROUP BY p.id
             ORDER BY '.$order_by.'
             LIMIT ? OFFSET ?',
            array_merge($params, array($per_page, $offset))
        )->result_array();

        $total_pages = (int)ceil($total / $per_page);

        $vd = array(
            'products'    => $products,
            'total'       => $total,
            'total_pages' => $total_pages,
            'page_num'    => $page_num,
        );

        $html       = $this->load->view('inc/shop-products-partial', $vd, TRUE);
        $pagination = $this->load->view('inc/shop-pagination-partial', $vd, TRUE);

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(array(
                 'html'       => $html,
                 'pagination' => $pagination,
                 'total'      => $total,
             )));
    }

    public function product($id = 0)
    {
        $this->_front_base($data);

        $id      = (int)$id;
        $product = $this->db->query(
            'SELECT p.*, c.name AS cat_name, c.slug AS cat_slug, b.name AS brand_name
             FROM products p
             JOIN categories c ON c.id=p.category_id
             LEFT JOIN brands b ON b.id=p.brand_id
             WHERE p.id=? AND p.status=1', array($id)
        )->row_array();

        if (!$product) show_404();

        $variants = $this->db->query(
            'SELECT * FROM product_variants WHERE product_id=? ORDER BY variant_type, price_modifier',
            array($id)
        )->result_array();

        $extra_images = $this->db->query(
            'SELECT * FROM product_images WHERE product_id=? ORDER BY is_primary DESC, sort_order',
            array($id)
        )->result_array();

        $reviews = $this->db->query(
            'SELECT r.*, u.name AS user_name FROM reviews r
             JOIN users u ON u.id=r.user_id
             WHERE r.product_id=? AND r.status="approved" ORDER BY r.is_featured DESC, r.created_at DESC', array($id)
        )->result_array();

        $related = $this->db->query(
            'SELECT p.*, COALESCE(AVG(r.rating),0) AS avg_rating
             FROM products p LEFT JOIN reviews r ON r.product_id=p.id
             WHERE p.category_id=? AND p.id!=? AND p.status=1
             GROUP BY p.id ORDER BY RAND() LIMIT 4',
            array($product['category_id'], $id)
        )->result_array();

        $in_wishlist    = $this->spice_model->is_in_wishlist($id);
        $avg_rating     = $this->spice_model->avg_rating($id);
        $review_count   = $this->spice_model->review_count($id);
        $error          = '';
        $review_success = '';

        if ($this->input->post('submit_review')) {
            if (!$this->session->userdata(SESS_HEAD.'_logged_in')) {
                $error = 'Please login to submit a review.';
            } else {
                $user_id = (int)$this->session->userdata(SESS_HEAD.'_user_id');
                $rating  = (int)$this->input->post('rating');
                $comment = trim($this->input->post('comment') ?: '');

                if ($rating < 1 || $rating > 5) {
                    $error = 'Please select a rating (1-5 stars).';
                } elseif ($this->db->query(
                    'SELECT id FROM reviews WHERE product_id=? AND user_id=?', array($id, $user_id)
                )->row()) {
                    $error = 'You have already reviewed this product.';
                } else {
                    $this->db->query(
                        'INSERT INTO reviews (product_id,user_id,rating,comment) VALUES (?,?,?,?)',
                        array($id, $user_id, $rating, $comment)
                    );
                    $review_success = 'Thank you for your review!';
                    $reviews        = $this->db->query(
                        'SELECT r.*, u.name AS user_name FROM reviews r
                         JOIN users u ON u.id=r.user_id
                         WHERE r.product_id=? AND r.status="approved"
                         ORDER BY r.is_featured DESC, r.created_at DESC', array($id)
                    )->result_array();
                    $avg_rating   = $this->spice_model->avg_rating($id);
                    $review_count = $this->spice_model->review_count($id);
                }
            }
        }

        $data['js']             = 'product.inc';
        $data['product']        = $product;
        $data['variants']       = $variants;
        $data['extra_images']   = $extra_images;
        $data['reviews']        = $reviews;
        $data['related']        = $related;
        $data['avg_rating']     = $avg_rating;
        $data['review_count']   = $review_count;
        $data['in_wishlist']    = $in_wishlist;
        $data['error']          = $error;
        $data['review_success'] = $review_success;

        $this->load->view('inc/front-header', $data);
        $this->load->view('page/product', $data);
        $this->load->view('inc/front-footer', $data);
    }

    public function variant_info($id = 0)
    {
        $id = (int)$id;
        $this->output->set_content_type('application/json');

        if (!$id) {
            echo json_encode(['ok' => false, 'message' => 'Invalid ID']);
            return;
        }

        $v = $this->db->query(
            'SELECT variant_type, variant_value, price_modifier, stock_qty, sku
             FROM product_variants WHERE id=? LIMIT 1',
            [$id]
        )->row_array();

        if (!$v) {
            echo json_encode(['ok' => false, 'message' => 'Not found']);
            return;
        }

        echo json_encode([
            'ok'             => true,
            'variant_type'   => $v['variant_type'],
            'variant_value'  => $v['variant_value'],
            'price_modifier' => (float)$v['price_modifier'],
            'stock_qty'      => (int)$v['stock_qty'],
            'sku'            => $v['sku'] ?? '',
        ]);
    }
}
