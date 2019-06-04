<?php
defined('BASEPATH') or exit('No direct script access allowed');


/* This model allow you to update new password */ 

class User extends CI_Model
{

	
    public function __construct()
    {
        parent::__construct();
    }

	
    public function change_password($id)
    { 
		$this->db->where('user_id',$id);
		$query = $this->db->get('users');
		$result = $query->result_array();
        return $result;  
    }


    public	function  new_password_update($new_password,$id)
    {  
		$data = array('password' => $new_password);
		$this->db->set($data);	
		$this->db->where('user_id', $id);
		$this->db->update('users', $data);  
    }


    
    public	function  insert_feedback($feedback)
    {  
		$data = array(
            'note' => $feedback,
            'update_date' => date('Y-m-d H:i:s'),
            'creation_date' => date('Y-m-d H:i:s'),
            'user_id' => $this->session->userdata('user_id')
    );
    
    $this->db->insert('feedbacks', $data);
    }




    

}
