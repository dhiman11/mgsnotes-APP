<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Contact_api extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Insert_data');
        $this->load->model('images_data/Images_load');
        $this->load->model('supplier_data/Supplier_existence');
        $this->load->model('images_data/Images_insert');
        $this->load->model('audio_data/Audio');
    }

    public function add_contact()
    {

          $data = json_decode($this->security->xss_clean($this->input->raw_input_stream),true);
       
        //  header('Content-Type: application/json');
        

        $user_id = $this->security->xss_clean($data['user_id']);
        $user_name = $this->security->xss_clean($data['user_name']);
        $event_id = $this->security->xss_clean($data['event_id']);
        $con_sup = $this->security->xss_clean($data['con_sup']);
        $con_name = $this->security->xss_clean($data['contact_name']);
        $con_position = $this->security->xss_clean($data['con_position']);
        $con_mobile = $this->security->xss_clean($data['con_mobile']);
        $con_email = $this->security->xss_clean($data['con_email']);
        $con_note = $this->security->xss_clean($data['con_note']);
        $images = $data['images'];
     
        $supp_id = $this->security->xss_clean($data['supp_id']);
 
        if ($con_sup ===''){ 
             
            $response['response'] = array("result"=>false,"msg"=> "Supplier name name can not be empty");
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();

        }elseif($con_name ===''){ 
            $response['response'] = array("result"=>false,"msg"=> "Contact name can not be empty"); 
            header('Content-Type: application/json');
            echo json_encode($response);
            exit(); 
        }


        // Check if Supplier name exist or not ////
        ///////////////////////////////////////////
        $this->db->select('`supplier_id`,`supplier_name`');
        $this->db->where('supplier_name', $con_sup);
        $supp_count = $this->db->count_all_results('suppliers_name');
        /////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////
        $response = [];

        $last_supplier_note_id = $this->Supplier_existence->supp_code($con_sup, $supp_count,$user_id,$event_id);
        $response['last_supplier_note_id'] = $last_supplier_note_id;

        if ($supp_id != 'undefined' || $supp_count != 0) {
            $user_id = $user_id;
            if ($supp_id == 0) {
                $this->db->select('`supplier_id`');
                $this->db->where('supplier_name', $con_sup);
                $t_data = $this->db->get('suppliers_name')->result_array();
                $supp_id = $t_data[0]['supplier_id'];
            }
            // Get supplier_note_id
            $where = "supplier_id = '$supp_id'";
            $this->db->where($where);
            $result = $this->db->select('supplier_note_id')->get('suppliers')->result_array();
            // Build where for contact
            $supp_note_ids = [];
            foreach ($result as $value) {
                array_push($supp_note_ids, $value['supplier_note_id']);
            }
            $where = "contact_name = '$con_name' AND supplier_note_id IN ('" . implode("','", $supp_note_ids) . "') AND user_id = '$user_id'";

            $update_array = array(
                'position' => $con_position,
                'phone' => $con_mobile,
                'email' => $con_email,
            );
            $this->db->where($where);
            $this->db->update('contacts', $update_array);
        }

        $result = $this->Insert_data->insert_contacts($con_sup, $con_name, $con_position, $con_mobile,$con_email, $con_note, $last_supplier_note_id,$user_id);
        $response['response'] = $result;

       
        //Last inserted contact id here
        $connect_id = $result['last_inserted_id'];

        /////////////////////////////////////
        //////Upload image to server folder
        //This is storage path of image
        

        if ($images) {
            $path_of_image = '../0_data/supplier_contact/';
            $table_name = 'contacts';
            $this->Images_insert->insert_images($table_name, $connect_id, $path_of_image, $images, null,$user_id,$user_name);
        } 

        /////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////
  

        header('Content-Type: application/json');
        echo json_encode($response);

    }

}
