<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Login extends CI_Controller
{

    /**    Login Controller     */

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('cookie');
        $this->load->helper(array('form', 'url'));
        $this->load->database();
        $this->load->model('user_login');
        $this->load->helper('cookie');
        if ($this->session->userdata('logged_in')) {
            redirect('events');
        }
    }

 

    public function loginhere()
    {

        $stream_clean = json_decode($this->security->xss_clean($this->input->raw_input_stream),true);
 
        $username = $this->security->xss_clean($stream_clean['employeeid']);
        $password = $this->security->xss_clean($stream_clean['password']);
        ////////////////////////////////////////////////////////////////////////
        $result = $this->user_login->get_user_info($username, md5($password));
   

        $result_count = count($result);
        
        if ($result_count > 0) {
            foreach ($result as $value) {
                $data['user_category'] = $value['user_category'];
                $data['user_type'] = $value['user_type'];
                $data['tester'] = $value['tester'];
                $data['user_name'] = $value['user_name'];
                $data['user_id'] = $value['user_id'];
                $data['email'] = strpos($value['email'], 'marjanegs.com.cn') ? $value['email'] : false; 
            }
            //// Update last login date/////////////////////
            ///////////////////////////////////////////////
            $this->user_login->lastlogin($data['user_id']);
            ///////Set User session///////////////////////
            /////////////////////////////////////////////

            $newdata = array(
                'user_id' => $data['user_id'],
                'user_name' => $data['user_name'],
                'user_type' => $data['user_type'],
                'tester' => $data['tester'],
                'user_category' => $data['user_category'],
                'email' => $data['email'],
                'logged_in' => true,
            );
       
            //////////////////////////////////////////// 
            echo json_encode(array("result"=>true,"msg"=>"Login successful","cookie_data"=>$newdata)); 
            ///////////////////////////////////////////
            //////////////////////////////////////////
            $this->session->set_userdata($newdata);
            // redirect('Events/index');
        } else {
            echo json_encode(array("result"=>true,"msg"=>"Login failed","data"=>'')); 
            //     $this->load->view('head');
            //    $data['login_info'] = 'YOUR LOGIN NAME OR PASSWORD IS INVALID';
            //    $this->load->view('/login/login', $data);
        }
        // print_r($_SESSION);
        // echo  $this->session->userdata('user_name');
        // echo  $this->session->userdata('user_type');
        // echo  $this->session->userdata('user_category');

    }

    public function reset_password()
    {
        $this->load->view('head');
        $this->load->view('/login/account', array('head' => 'Foget Password'));
    }

    public function request_new_user()
    {
        $this->load->view('head');
        $this->load->view('/login/account', array('head' => 'New User Request'));
    }

}
