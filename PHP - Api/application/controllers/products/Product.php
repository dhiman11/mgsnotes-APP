<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('product_data/Product_list');
        $this->load->model('Insert_data');
        $this->load->model('images_data/Images_load');
        $this->load->model('supplier_data/Supplier_existence');
        $this->load->model('images_data/Images_insert');
        $this->load->model('audio_data/Audio');
    }

    ///Last used category
    public function last_category_selected($json_array)
    {
        //    print_r($json_array);
        $product_category = $this->security->xss_clean($json_array['product_category']);
        $category_name = $this->security->xss_clean($json_array['category_name']);
        $pro_sup = $this->security->xss_clean($json_array['pro_sup']);

        $newdata = array(
            'last_category_id' => $product_category,
            'last_category_name' => $category_name,
            'last_used_supplier' => $pro_sup,
        );

        $this->session->set_userdata($newdata);
    }

    public function add_products()
    {
        $data = json_decode($this->security->xss_clean($this->input->raw_input_stream),true); 

        //$this->last_category_selected($this->input->post());
        ///////////////////////////////////////////////////////
        $user_id = $this->security->xss_clean($data['user_id']);
        $user_name = $this->security->xss_clean($data['user_name']);
        $event_id = $this->security->xss_clean($data['event_id']);
        ///////////////////////////////////////////////////
        $pro_sup = $this->security->xss_clean($data['pro_sup']);
        $proname = $this->security->xss_clean($data['proname']);
        $proref = $this->security->xss_clean($data['proref']);
        $profob = $this->security->xss_clean($data['profob']);
        $moq = $this->security->xss_clean($data['moq']);
        $product_category = $this->security->xss_clean($data['product_category']);
        $pronote = $this->security->xss_clean($data['pronote']);
        $images = $data['images'];
  
        if ($pro_sup ===''){ 
             
            $response['response'] = array("result"=>false,"msg"=> "Supplier name name can not be empty");
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();

        }elseif($proname ===''){ 
            $response['response'] = array("result"=>false,"msg"=> "Product name can not be empty"); 
            header('Content-Type: application/json');
            echo json_encode($response);
            exit(); 
        }

       
        // Check if Supplier name exist or not ////
        ///////////////////////////////////////////
        $this->db->select('`supplier_id`,`supplier_name`');
        $this->db->where('supplier_name', $pro_sup);
        $supp_count = $this->db->count_all_results('suppliers_name');
        // /////////////////////////////////////////////////////////////////
        // /////////////////////////////////////////////////////////////////
        $response = [];
        $last_supplier_note_id = $this->Supplier_existence->supp_code($pro_sup, $supp_count,$user_id,$event_id);
        $response['last_supplier_note_id'] = $last_supplier_note_id;
        // /////////////////////////////////////////////////////////////////
        $result = $this->Insert_data->insert_products($pro_sup, $proname, $proref, $profob,
            $moq, $product_category, $pronote, $last_supplier_note_id,$user_id);

        $response['response'] = $result;

        //Last inserted contact id here//////////////////////////////////
        /////////////////////////////////////////////////////////////////
        $connect_id = $result['last_inserted_id'];
        

        /////////////////////////////////////
        //////Upload image to server folder
        //This is storage path of image
 

        if ($images) {
            $path_of_image = '../0_data/supplier_product/';
            $table_name = 'products';
            $this->Images_insert->insert_images($table_name, $connect_id, $path_of_image, $images, null,$user_id,$user_name);
        } 



        header('Content-Type: application/json');
        echo json_encode($response);
 
    }

}
