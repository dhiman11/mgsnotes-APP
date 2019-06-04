<?php
defined('BASEPATH') or exit('No direct script access allowed');


/* This model allow you to update new password */ 

class Feedbacks extends CI_Model
{

	
    public function __construct()
    {
        parent::__construct();
    }

	
    public function get_latest_feedback()
    { 
        $this->db->select('users.user_name,feedbacks.*');
        $this->db->limit('40');
        $this->db->order_by('feedback_id','desc');
        $this->db->join('users', 'users.user_id = feedbacks.user_id', 'left');
        $this->db->from('feedbacks');
        $data = $this->db->get()->result_array();
        return $data;
    }

}
