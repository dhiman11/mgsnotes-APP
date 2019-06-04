<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Insert_data extends CI_Model
{
 

    public function insert_contacts($con_sup, $con_name, $con_position, $con_mobile, $con_email, $con_note, $last_supplier_note_id, $user_id=0)
    {
       
       
        //////////// INSERT CONTACTS ////////////////////// 
        ///////////////////////////////////////////////////

        $data2 = array(
            'supplier_note_id' => $last_supplier_note_id,
            'contact_name' => $con_name,
            'position' => $con_position,
            'phone' => $con_mobile,
            'email' => $con_email,
            'note' => $con_note,
            'update_date' => date('Y-m-d H:i:s'),
            'creation_date' => date('Y-m-d H:i:s'),
            'user_id' => $user_id,
        );

        $this->db->insert('contacts', $data2);
        $con_last_id = $this->db->insert_id();
        if(!empty($con_last_id)){
            return array('last_inserted_id'=>$con_last_id,"result"=>true);
        }else{
            return array("result"=>false,"msg"=> "Error during insertion");
        }
        
        
      
    }

    public function insert_products($pro_sup,$proname,$proref,$profob,$moq,$product_category,$pronote,$last_supplier_note_id, $user_id=0)
    {
          //////////// INSERT CONTACTS ////////////////////// 
        ///////////////////////////////////////////////////
     

        $data2 = array(
            'supplier_note_id' => $last_supplier_note_id,
            'product_name' => $proname,
            'supplier_reference' => $proref,
            'fob_price' => $profob,
            'moq' => $moq,
            'product_cat_id' => $product_category,
            'note' => $pronote,
            'update_date' => date('Y-m-d H:i:s'),
            'creation_date' => date('Y-m-d H:i:s'),
            'user_id' => $user_id,
        );

         $this->db->insert('products', $data2);
        $pro_last_id = $this->db->insert_id();

        if(!empty($pro_last_id)){

            return array('last_inserted_id'=>$pro_last_id,"result"=>true);

        }else{

            return array("result"=>false,"msg"=> "Error during insertion");
            
        }
 
 

    }
    
}
