<?php
defined('BASEPATH') or exit('No direct script access allowed');
// error_reporting(0);
// ini_set('display_errors', 0);

class History_page extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('images_data/Images_load');
        $this->load->model('history_data/history_list');
        $this->load->model('supplier_data/Supplier_list');
        $this->load->model('contact_data/contact_list');
        $this->load->model('product_data/Product_list');
        $this->load->model('event_data/Event_list');
        $this->load->model('Users');
        $this->load->model('Likes');
        $this->load->model('audio_data/Audio');
    }

    public function history_data()
    { 

        $stream_clean = json_decode($this->security->xss_clean($this->input->raw_input_stream),true);
        $limit = $stream_clean['limit'];
        $user_id = $this->input->post('value');
        $user_id = str_replace('-1,', '', $user_id);
      

      

        $history_data = $this->history_list->recent_history($limit,1);
        

        $i=0;
        $history_array = [];

        

        foreach ($history_data as $data_type) { 

            //  echo "table name =".$data_type->event.'<br>';
            if ($data_type->event == 'event') {
                        
                        $data['data_type']="event";
                        $data['event'] = $this->Event_list->get_event_complete_by_event_id($data_type->event_id)[0]; 
                        $history_array[$i] = $data;
                        $data =null;
                        
            }

            //////// Suppliers///////////////
            if ($data_type->event == 'supplier') { 
                        
             
                        $data['data_type']="supplier";
                        
                        $data['data'] = $this->Supplier_list->get_supplier_history_supplier_note_id($data_type->event_id);   
                       



                        if(!empty($data['data'])){ 
                        // FIND The Images//////////////////////////////////////////////////
                       // $data['supplier_images'] = $this->Images_load->process_images($data['data'], 'suppliers', 'supplier_note_id'); 
                            // $data['supplier']['supplier_images'] = $this->Images_load->get_images_from_table_and_id('suppliers', $images_id, $main_photo_id);
                            //////////////////////////////////////////////////////////////////// 
                            
                        }

                        $history_array[$i] = $data;
                        $data =null;
              
            }

            /////// Contacts /////////////////
            if ($data_type->event == 'contact') {
                        
                        $data['data_type']="contact";
                        $data['data'] = $this->contact_list->get_contacts_for_history_by_contact_id($data_type->event_id)[0];
                        if(!empty($data['data'])){
                           
                            // GET ID YO FIND IMAGES ///////////////////////////////////////////
                            $images_id = $data['data']['contact_id'];
                            $main_photo_id = $data['data']['main_photo_id'];
                            // FIND The Images//////////////////////////////////////////////////
                            
                            $image = $this->Images_load->process_images($data['data'], 'contacts', 'contact_id');
                            // $data['contact_images'] 
                            $data['contact_images'] = $image['src'];

                            // $data['contact']['contact_images'] = $this->Images_load->get_images_from_table_and_id('contacts', $images_id, $main_photo_id);

                        }

                        
                        $history_array[$i] = $data;
                        $data =null;
                      
            }

            //////// Products ////////////////
            if ($data_type->event == 'product') {
                      
                    $data['data_type']="product";
                    $data['data'] = $this->Product_list->get_product_for_history_by_product_id($data_type->event_id)[0]; 
                    
                    // For audio 
                    // GET ID YO FIND IMAGES ///////////////////////////////////////////
                    $images_id = $data['data']['product_id'];
                    $main_photo_id = $data['data']['main_photo_id'];
                    // FIND The Images//////////////////////////////////////////////////
                    $image= $this->Images_load->process_images($data['data'], 'products', 'product_id');
                    $data['pro_images'] = $image['src'];
                    //$data['pro']['pro_images'] = $this->Images_load->get_images_from_table_and_id('products', $images_id, $main_photo_id);
                    ////////////////////////////////////////////////////////////////////
                    $history_array[$i] = $data;
                    $data =null;
                 
            }

            $i++;
        }

        ///////////////////////////////
        
        echo json_encode($history_array);
        
        ///////////////////////////////


    }

    public function history_load_more()
    {

        $user_id = $this->input->post('value');
        $user_id = str_replace('-1,', '', $user_id);
        $limit = $this->input->post('limit');
        $history_data = $this->history_list->recent_history((int) ($limit / 10 * 25), $user_id);


        $i=0;
        $history_array2 = [];

        foreach ($history_data as $data_type) {

            //  echo "table name =".$data_type->event.'<br>';

            if ($data_type->event == 'event') {
                $data =[];
                $data['event'] = $this->Event_list->get_event_complete_by_event_id($data_type->event_id);
                $history_array2[$i] = $data;
            }

            if ($data_type->event == 'supplier') {
                $data =[];
                $data['supp'] = $this->Supplier_list->get_supplier_history_supplier_note_id($data_type->event_id);
                // GET ID YO FIND IMAGES ///////////////////////////////////////////
                $images_id = $data['supp'][0]['supplier_note_id'];
                $main_photo_id = $data['supp'][0]['main_photo_id'];
                // FIND The Images//////////////////////////////////////////////////
                $data['supp']['supp_images'] = $this->Images_load->get_images_from_table_and_id('suppliers', $images_id, $main_photo_id);
                ////////////////////////////////////////////////////////////////////
                $history_array2[$i] = $data;
            }

            if ($data_type->event == 'contact') {
                $data =[];
                $data['contact'] = $this->contact_list->get_contacts_for_history_by_contact_id($data_type->event_id);
                // GET ID YO FIND IMAGES ///////////////////////////////////////////
                $images_id = $data['contact'][0]['contact_id'];
                $main_photo_id = $data['contact'][0]['main_photo_id'];
                // For audio
                $data['contact']['audio_info'] = $this->Audio->get_audio('contacts', $data['con'][0]['contact_id'], true);
                // FIND The Images//////////////////////////////////////////////////
                $data['contact']['con_images'] = $this->Images_load->get_images_from_table_and_id('contacts', $images_id, $main_photo_id);
                ////////////////////////////////////////////////////////////////////
                $history_array2[$i] = $data;
            }

            if ($data_type->event == 'product') {
                $data =[];
                $data['product'] = $this->Product_list->get_product_for_history_by_product_id($data_type->event_id);
                // GET ID YO FIND IMAGES ///////////////////////////////////////////
                $images_id = $data['product'][0]['product_id'];
                $main_photo_id = $data['product'][0]['main_photo_id'];
                // For audio
          
                // FIND The Images//////////////////////////////////////////////////
                $data['product']['pro_images'] = $this->Images_load->get_images_from_table_and_id('products', $images_id, $main_photo_id);
                ////////////////////////////////////////////////////////////////////
                $history_array2[$i] = $data;
            }

            $i++;
        }

        echo json_encode($history_array2);

    }

}
