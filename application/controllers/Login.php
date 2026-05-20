<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

    public function index()
    {
        if ($this->session->userdata(SESS_HEAD.'_logged_in')) {
            $role = $this->session->userdata(SESS_HEAD.'_user_role');
            if ($role === 'admin' || $role === 'staff') redirect('admin');
            else redirect('home');
        }

        $data['js']    = 'login.inc';
        $data['login'] = true;
        $data['error'] = '';

        if ($this->input->post('mode') == 'Login') {
            $email    = strtolower(trim($this->input->post('email')));
            $password = $this->input->post('password');

            if (empty($email) || empty($password)) {
                $data['login'] = false;
                $data['error'] = 'Please enter your email and password.';
            } else {
                $row = $this->db->query(
                    'SELECT * FROM users WHERE email = ?', array($email)
                )->row();

                if ($row && $row->password === $password) {
                    if ($row->is_blocked) {
                        $data['login'] = false;
                        $data['error'] = 'Your account has been blocked. Please contact support.';
                    } else {
                        $this->session->set_userdata(array(
                            SESS_HEAD.'_user_id'     => $row->id,
                            SESS_HEAD.'_user_name'   => $row->name,
                            SESS_HEAD.'_user_email'  => $row->email,
                            SESS_HEAD.'_user_role'   => $row->role,
                            SESS_HEAD.'_permissions' => $row->permissions,
                            SESS_HEAD.'_logged_in'   => TRUE,
                        ));
                        if ($row->role === 'admin' || $row->role === 'staff') redirect('admin');
                        else redirect('home');
                    }
                } else {
                    $data['login'] = false;
                    $data['error'] = 'Invalid email or password.';
                }
            }
        }

        $this->load->view('page/login', $data);
    }

    public function register()
    {
        if ($this->session->userdata(SESS_HEAD.'_logged_in')) redirect('home');

        $data['js']     = 'register.inc';
        $data['errors'] = array();
        $data['form']   = array();

        if ($this->input->post('mode') == 'Register') {
            $name     = trim($this->input->post('name'));
            $email    = strtolower(trim($this->input->post('email')));
            $phone    = trim($this->input->post('phone') ?: '');
            $password = $this->input->post('password');
            $confirm  = $this->input->post('confirm_password');

            $data['form'] = compact('name', 'email', 'phone');

            if (empty($name))                               $data['errors'][] = 'Name is required.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $data['errors'][] = 'Enter a valid email.';
            if (strlen($password) < 6)                      $data['errors'][] = 'Password must be at least 6 characters.';
            if ($password !== $confirm)                     $data['errors'][] = 'Passwords do not match.';

            if (empty($data['errors'])) {
                $exists = $this->db->query('SELECT id FROM users WHERE email=?', array($email))->row();
                if ($exists) {
                    $data['errors'][] = 'An account with this email already exists.';
                } else {
                    $this->db->query(
                        'INSERT INTO users (name,email,phone,password,role) VALUES (?,?,?,?,?)',
                        array($name, $email, $phone, $password, 'customer')
                    );
                    $uid = $this->db->insert_id();
                    $this->session->set_userdata(array(
                        SESS_HEAD.'_user_id'    => $uid,
                        SESS_HEAD.'_user_name'  => $name,
                        SESS_HEAD.'_user_email' => $email,
                        SESS_HEAD.'_user_role'  => 'customer',
                        SESS_HEAD.'_logged_in'  => TRUE,
                    ));
                    $this->session->set_flashdata('success', 'Welcome to myeoncasuals, '.$name.'!');
                    redirect('home');
                }
            }
        }

        $this->load->view('page/register', $data);
    }

    public function forgot_password()
    {
        $data['js']      = 'forgot-password.inc';
        $data['step']    = 'email';
        $data['error']   = '';
        $data['success'] = '';
        $data['token']   = '';
        $data['demo_otp']= '';

        if ($this->input->post('step') === 'email') {
            $email = strtolower(trim($this->input->post('email')));
            $user  = $this->db->query('SELECT id,name FROM users WHERE email=?', array($email))->row();
            if (!$user) {
                $data['error'] = 'No account found with this email address.';
            } else {
                $otp   = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
                $token = bin2hex(random_bytes(24));
                $exp   = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                $this->db->query(
                    'INSERT INTO otp_codes (email,otp,token,type,expires_at) VALUES (?,?,?,?,?)',
                    array($email, $otp, $token, 'reset', $exp)
                );
                $data['step']    = 'otp';
                $data['token']   = $token;
                $data['demo_otp']= $otp;
                $data['success'] = 'OTP generated! Check below (demo mode — in production this is sent to your email).';
            }
        } elseif ($this->input->post('step') === 'otp') {
            $token = $this->input->post('token');
            $otp   = trim($this->input->post('otp'));
            $rec   = $this->db->query(
                'SELECT * FROM otp_codes WHERE token=? AND otp=? AND type="reset" AND is_used=0 AND expires_at>NOW()',
                array($token, $otp)
            )->row();
            if (!$rec) {
                $data['step']  = 'otp';
                $data['token'] = $token;
                $data['error'] = 'Invalid or expired OTP. Please try again.';
            } else {
                $data['step']  = 'reset';
                $data['token'] = $token;
            }
        } elseif ($this->input->post('step') === 'reset') {
            $token    = $this->input->post('token');
            $password = $this->input->post('password');
            $confirm  = $this->input->post('confirm_password');
            $rec      = $this->db->query(
                'SELECT * FROM otp_codes WHERE token=? AND type="reset" AND is_used=0 AND expires_at>NOW()',
                array($token)
            )->row();
            if (!$rec) {
                $data['error'] = 'Session expired. Please start again.';
            } elseif (strlen($password) < 6) {
                $data['step']  = 'reset';
                $data['token'] = $token;
                $data['error'] = 'Password must be at least 6 characters.';
            } elseif ($password !== $confirm) {
                $data['step']  = 'reset';
                $data['token'] = $token;
                $data['error'] = 'Passwords do not match.';
            } else {
                $this->db->query('UPDATE users SET password=? WHERE email=?', array($password, $rec->email));
                $this->db->query('UPDATE otp_codes SET is_used=1 WHERE id=?', array($rec->id));
                $data['success'] = 'Password reset successfully! <a href="'.site_url('login').'">Login now →</a>';
            }
        }

        $this->load->view('page/forgot-password', $data);
    }

    public function logout()
    {
        $this->session->unset_userdata(array(
            SESS_HEAD.'_user_id',   SESS_HEAD.'_user_name',
            SESS_HEAD.'_user_email',SESS_HEAD.'_user_role',
            SESS_HEAD.'_permissions',SESS_HEAD.'_logged_in',
        ));
        $this->session->sess_destroy();
        redirect('login');
    }
}
