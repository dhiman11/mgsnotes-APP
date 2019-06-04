<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_login extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get_user_info($username, $password)
    {
        $username = $this->security->xss_clean($username);
        $password = $this->security->xss_clean($password);
        $where = " user_name= '" . $username . "' AND password='" . $password . "'";
        $this->db->where($where);
        $query = $this->db->select('user_id,user_category,user_type,user_name,tester,email')->get('users');
        $result = $query->result_array();
        return $result;
    }

    public function lastlogin($user_id)
    {
        $this->db->set('last_login_date',date('Y-m-d H:i:s'));
        $this->db->where('user_id',$user_id);
        $this->db->update('users'); // gives UPDATE `mytable` SET `field` = 'field+1' WHERE `id` = 2
    }

}
