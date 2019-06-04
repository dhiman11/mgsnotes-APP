<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Check_availability_model extends CI_Model
{
 

    public function con($con_sup)
    { 
        $this->db->where('contact_name',$con_sup); 
        $this->db->where('user_id',$this->session->userdata('user_id')); 
        $query = $this->db->get('contacts'); 
        return $query;
    }

    
    public function pro($pro_ref)
    { 
        $this->db->where('supplier_reference',$pro_ref); 
        $this->db->where('user_id',$this->session->userdata('user_id')); 
        $query = $this->db->get('products'); 
        return $query;
    }

   
}
