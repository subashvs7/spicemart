<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spice_model extends CI_Model {

    // ── Cart ──────────────────────────────────────────────────────

    public function get_cart_count()
    {
        $user_id = (int)$this->session->userdata(SESS_HEAD.'_user_id');
        if (!$user_id) return 0;
        return (int)$this->db->query(
            'SELECT COALESCE(SUM(quantity),0) AS cnt FROM cart WHERE user_id=?', array($user_id)
        )->row()->cnt;
    }

    public function get_cart_items()
    {
        $user_id = (int)$this->session->userdata(SESS_HEAD.'_user_id');
        if (!$user_id) return array();
        return $this->db->query(
            'SELECT c.id AS cart_id, c.product_id, c.quantity,
                    c.variant_id, c.variant_label,
                    p.name, p.price, p.offer_price, p.image, p.stock_qty,
                    pv.variant_type, pv.variant_value, pv.color_hex, pv.sku
             FROM cart c
             JOIN products p ON p.id = c.product_id
             LEFT JOIN product_variants pv ON pv.id = c.variant_id
             WHERE c.user_id=?', array($user_id)
        )->result_array();
    }

    public function add_to_cart($product_id, $qty = 1, $variant_id = null, $variant_label = '')
    {
        $user_id    = (int)$this->session->userdata(SESS_HEAD.'_user_id');
        $variant_id = $variant_id ? (int)$variant_id : null;
        if (!$user_id) return;

        if ($variant_id) {
            $existing = $this->db->query(
                'SELECT id FROM cart WHERE user_id=? AND product_id=? AND variant_id=?',
                array($user_id, $product_id, $variant_id)
            )->row();
        } else {
            $existing = $this->db->query(
                'SELECT id FROM cart WHERE user_id=? AND product_id=? AND variant_id IS NULL',
                array($user_id, $product_id)
            )->row();
        }

        if ($existing) {
            $this->db->query(
                'UPDATE cart SET quantity=quantity+? WHERE id=?', array($qty, $existing->id)
            );
        } else {
            $this->db->query(
                'INSERT INTO cart (user_id,product_id,variant_id,variant_label,quantity) VALUES (?,?,?,?,?)',
                array($user_id, $product_id, $variant_id, $variant_label, $qty)
            );
        }
    }

    public function update_cart_item($cart_id, $qty)
    {
        $user_id = (int)$this->session->userdata(SESS_HEAD.'_user_id');
        if ($qty <= 0) {
            $this->db->query('DELETE FROM cart WHERE id=? AND user_id=?', array($cart_id, $user_id));
        } else {
            $this->db->query('UPDATE cart SET quantity=? WHERE id=? AND user_id=?', array($qty, $cart_id, $user_id));
        }
    }

    public function remove_cart_item($cart_id)
    {
        $user_id = (int)$this->session->userdata(SESS_HEAD.'_user_id');
        $this->db->query('DELETE FROM cart WHERE id=? AND user_id=?', array($cart_id, $user_id));
    }

    public function clear_cart()
    {
        $user_id = (int)$this->session->userdata(SESS_HEAD.'_user_id');
        $this->db->query('DELETE FROM cart WHERE user_id=?', array($user_id));
    }

    // ── Wishlist ──────────────────────────────────────────────────

    public function get_wishlist_count()
    {
        $user_id = (int)$this->session->userdata(SESS_HEAD.'_user_id');
        if (!$user_id) return 0;
        return (int)$this->db->query(
            'SELECT COUNT(*) AS cnt FROM wishlist WHERE user_id=?', array($user_id)
        )->row()->cnt;
    }

    public function is_in_wishlist($product_id)
    {
        $user_id = (int)$this->session->userdata(SESS_HEAD.'_user_id');
        if (!$user_id) return false;
        return (bool)$this->db->query(
            'SELECT id FROM wishlist WHERE user_id=? AND product_id=?', array($user_id, $product_id)
        )->row();
    }

    // ── Coupon ────────────────────────────────────────────────────

    public function validate_coupon($code, $subtotal, $user_id = 0)
    {
        $coupon = $this->db->query(
            'SELECT * FROM coupons WHERE code=? AND status=1', array($code)
        )->row_array();

        if (!$coupon) return array('valid'=>false,'message'=>'Invalid coupon code.');

        if ($coupon['expires_at'] && $coupon['expires_at'] < date('Y-m-d')) {
            return array('valid'=>false,'message'=>'This coupon has expired.');
        }
        if ($coupon['uses_limit'] && $coupon['uses_count'] >= $coupon['uses_limit']) {
            return array('valid'=>false,'message'=>'This coupon has reached its usage limit.');
        }
        if ($subtotal < $coupon['min_order']) {
            return array('valid'=>false,'message'=>'Minimum order of ₹'.number_format($coupon['min_order'],2).' required for this coupon.');
        }

        if ($user_id) {
            // Per-user usage check
            if ($coupon['uses_per_user']) {
                $user_uses = (int)$this->db->query(
                    'SELECT COUNT(*) AS cnt FROM coupon_usage WHERE coupon_id=? AND user_id=?',
                    array($coupon['id'], $user_id)
                )->row()->cnt;
                if ($user_uses >= (int)$coupon['uses_per_user']) {
                    return array('valid'=>false,'message'=>'You have already used this coupon the maximum number of times.');
                }
            }

            // User-restriction check
            if ($coupon['restrict_to'] === 'staff') {
                $role = $this->db->query(
                    'SELECT role FROM users WHERE id=?', array($user_id)
                )->row()->role ?? 'customer';
                if ($role === 'customer') {
                    return array('valid'=>false,'message'=>'This coupon is for staff/alumni only.');
                }
            } elseif ($coupon['restrict_to'] === 'specific') {
                $email = $this->db->query(
                    'SELECT email FROM users WHERE id=?', array($user_id)
                )->row()->email ?? '';
                $allowed = $this->db->query(
                    'SELECT id FROM coupon_users WHERE coupon_id=? AND user_email=?',
                    array($coupon['id'], $email)
                )->row();
                if (!$allowed) {
                    return array('valid'=>false,'message'=>'This coupon is not available for your account.');
                }
            }
        }

        if ($coupon['type'] === 'percent') {
            $discount = ($subtotal * $coupon['value']) / 100;
            if ($coupon['max_discount']) $discount = min($discount, (float)$coupon['max_discount']);
        } else {
            $discount = (float)$coupon['value'];
        }
        $discount = round($discount, 2);

        return array(
            'valid'     => true,
            'coupon_id' => (int)$coupon['id'],
            'type'      => $coupon['type'],
            'value'     => $coupon['value'],
            'discount'  => $discount,
            'message'   => 'Coupon applied! You save ₹'.number_format($discount, 2),
        );
    }

    // ── Loyalty ───────────────────────────────────────────────────

    public function add_loyalty_points($user_id, $points, $type = 'earned', $ref_type = 'order', $ref_id = null, $note = null)
    {
        $this->db->query(
            'INSERT INTO loyalty_ledger (user_id,points,type,ref_type,ref_id,note) VALUES (?,?,?,?,?,?)',
            array($user_id, $points, $type, $ref_type, $ref_id, $note)
        );

        if ($points >= 0) {
            $this->db->query(
                'INSERT INTO user_loyalty (user_id,points_balance,points_earned) VALUES (?,?,?)
                 ON DUPLICATE KEY UPDATE points_balance=points_balance+?, points_earned=points_earned+?',
                array($user_id, $points, $points, $points, $points)
            );
        } else {
            $this->db->query(
                'INSERT INTO user_loyalty (user_id,points_balance,points_redeemed) VALUES (?,0,?)
                 ON DUPLICATE KEY UPDATE points_balance=GREATEST(0,points_balance+?), points_redeemed=points_redeemed+?',
                array($user_id, abs($points), $points, abs($points))
            );
        }

        // Recalculate tier
        $earned = (int)($this->db->query('SELECT points_earned FROM user_loyalty WHERE user_id=?', array($user_id))->row()->points_earned ?? 0);
        $tier = $earned >= 5000 ? 'platinum' : ($earned >= 2000 ? 'gold' : ($earned >= 500 ? 'silver' : 'bronze'));
        $this->db->query('UPDATE user_loyalty SET tier=? WHERE user_id=?', array($tier, $user_id));
    }

    public function get_loyalty_balance($user_id)
    {
        $row = $this->db->query('SELECT * FROM user_loyalty WHERE user_id=?', array($user_id))->row_array();
        return $row ?: array('points_balance'=>0,'points_earned'=>0,'points_redeemed'=>0,'tier'=>'bronze','birthday'=>null);
    }

    // ── Shipping ──────────────────────────────────────────────────

    public function get_shipping_charge($subtotal)
    {
        $free  = (float)$this->get_setting('free_shipping_above') ?: 499;
        $charge= (float)$this->get_setting('standard_charge')     ?: 60;
        return $subtotal >= $free ? 0 : $charge;
    }

    public function get_setting($key)
    {
        $row = $this->db->query(
            'SELECT key_value FROM shipping_settings WHERE key_name=?', array($key)
        )->row();
        return $row ? $row->key_value : null;
    }

    // ── Reviews ───────────────────────────────────────────────────

    public function avg_rating($product_id)
    {
        return round((float)$this->db->query(
            'SELECT COALESCE(AVG(rating),0) AS avg FROM reviews WHERE product_id=?', array($product_id)
        )->row()->avg, 1);
    }

    public function review_count($product_id)
    {
        return (int)$this->db->query(
            'SELECT COUNT(*) AS cnt FROM reviews WHERE product_id=?', array($product_id)
        )->row()->cnt;
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function get_all_settings()
    {
        static $cache = null;
        if ($cache !== null) return $cache;
        $rows = $this->db->query('SELECT key_name, key_value FROM site_settings')->result_array();
        $cache = array();
        foreach ($rows as $r) {
            $cache[$r['key_name']] = $r['key_value'];
        }
        return $cache;
    }

    public function make_slug($str)
    {
        $str = strtolower(trim((string)($str ?? '')));
        $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
        $str = preg_replace('/[\s-]+/', '-', $str);
        return trim($str, '-');
    }

    public function rupees($amount)
    {
        return '₹'.number_format((float)$amount, 2);
    }

    public function effective_price($product)
    {
        return (!empty($product['offer_price']) && $product['offer_price'] > 0)
            ? (float)$product['offer_price']
            : (float)$product['price'];
    }

    public function star_rating($avg)
    {
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($avg >= $i)          $stars .= '<i class="bi bi-star-fill text-warning"></i>';
            elseif ($avg >= $i-0.5) $stars .= '<i class="bi bi-star-half text-warning"></i>';
            else                     $stars .= '<i class="bi bi-star text-warning"></i>';
        }
        return $stars;
    }

    public function order_status_badge($status)
    {
        $status = (string)($status ?? '');
        $map = array(
            'pending'    => 'secondary',
            'processing' => 'primary',
            'shipped'    => 'info',
            'delivered'  => 'success',
            'cancelled'  => 'danger',
        );
        $class = isset($map[$status]) ? $map[$status] : 'secondary';
        return '<span class="badge bg-'.$class.'">'.ucfirst($status).'</span>';
    }

    public function payment_status_badge($status)
    {
        $status = (string)($status ?? '');
        $map = array(
            'pending'  => 'warning',
            'paid'     => 'success',
            'failed'   => 'danger',
            'refunded' => 'info',
        );
        $class = isset($map[$status]) ? $map[$status] : 'secondary';
        return '<span class="badge bg-'.$class.'">'.ucfirst($status).'</span>';
    }

    public function product_image($filename)
    {
        if ($filename && file_exists(FCPATH.'uploads/products/'.$filename)) {
            return base_url('uploads/products/'.$filename);
        }
        return 'https://placehold.co/300x300/f5f0eb/c8956c?text=myeoncasuals';
    }

    public function truncate_text($str, $len = 80)
    {
        $str = (string)($str ?? '');
        if (strlen($str) <= $len) return $str;
        return substr($str, 0, $len).'…';
    }
}
