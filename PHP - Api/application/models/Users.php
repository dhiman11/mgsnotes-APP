<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get_user_name_list($category = null)
    {
        ////////////////////* Select Query *//////////////////////
        $this->db->select('user_name,user_id');
        if ($category) {
            $this->db->where("user_category", $category);
        }
        $this->db->order_by('user_name','asc');
        $query = $this->db->get('users');
        $result = $query->result_array();
        return $result;
        ////////////////////* Select Query *//////////////////////
    }

    public function get_user_id_by_user_name($username = null)
    {
        $sql = "SELECT `user_id` AS `userid` FROM `users` WHERE `user_name` = '$username' ORDER BY user_name ASC LIMIT 1";
        $result = $this->db->query($sql)->result_array();
        return $result[0]['userid'];
    }
}
