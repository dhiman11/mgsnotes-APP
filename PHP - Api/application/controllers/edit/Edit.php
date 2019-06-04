<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Edit extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('event_data/Event_list');
        $this->load->model('supplier_data/Supplier_list');
        $this->load->model('contact_data/Contact_list');
        $this->load->model('product_data/Product_list');
        $this->load->model('images_data/Images_load');
        $this->load->model('audio_data/Audio');
    }
 

    private function is_admin()
    {
        if ($this->session->userdata('user_type') != 'notes_admin') {
            return ' readonly  disabled ';
        }
        return null;
    }

    public function event($event_id)
    {
        $data = $this->Event_list->get_event_complete_by_event_id($event_id);
        if (count($data) != 1) {
            exit('ERROR 404');
        }
        $data = $data[0];

        $user_id = $data['user_id'];
        $header['name'] = 'Event - ' . $data['event_name'];
        $this->load->view('header', $header);
        $this->load->view(
            'edit/event',
            array(
                'row' => $data,
                'edit' => $this->admin_ctrl($user_id),
                'admin_only' => $this->is_admin(),
                'user_id' => $user_id,
            )
        );
        $this->load->view('footer');
    }

    public function supplier($supplier_note_id)
    {
        $data = $this->Supplier_list->get_supplier_complete_by_supplier_note_id($supplier_note_id);
        if (count($data) != 1) {
            exit('ERROR 404');
        }
        $data = $data[0];

        $user_id = $data['user_id'];
        $header['name'] = 'Suppliers - ' . $data['supplier_name'];
        $this->load->view('header', $header);

        // For images
        $num_photos = $this->Images_load->count_photos('suppliers', $supplier_note_id);
        $images = $this->Images_load->process_images($data, 'suppliers', 'supplier_note_id');

        $this->load->view(
            'edit/supplier',
            array(
                'row' => $data,
                'num_contacts' => $this->Contact_list->count_contacts($supplier_note_id),
                'num_products' => $this->Product_list->count_products($supplier_note_id),
                'num_photos' => $num_photos,
                'all_events' => $this->Event_list->get_event_list_user_id($data['user_id']),
                'images' => $images,
                'edit' => $this->admin_ctrl($user_id),
                'admin_only' => $this->is_admin(),
                'user_id' => $user_id,
            )
        );
        $this->load->view('footer');
    }

    public function contact($contact_id)
    {
        $data = $this->Contact_list->get_contact_complete_by_contact_id($contact_id);

        if (count($data) != 1) {
            exit('ERROR 404');
        }
        $data = $data[0];

        $user_id = $data['user_id'];
        $supplier_note_id = $data['supplier_note_id'];
        $supplier_data = $this->Supplier_list->get_supplier_complete_by_supplier_note_id($supplier_note_id)[0];
        $event_id = $supplier_data['event_id'];

        $data['event_id'] = $event_id;
        $data['supplier_id'] = $supplier_data['supplier_id'];

        // For images
        $num_photos = $this->Images_load->count_photos('contacts', $contact_id);
        $images = $this->Images_load->process_images($data, 'contacts', 'contact_id');

        // For audio
        $audio = json_encode($this->Audio->get_audio('contacts', $contact_id));

        $header['name'] = 'Contact - ' . $data['contact_name'];
        $this->load->view('header', $header);

        $this->load->view(
            'edit/contact',
            array(
                'row' => $data,
                'num_products' => $this->Product_list->count_products($supplier_note_id),
                'num_photos' => $num_photos,
                'all_events' => $this->Event_list->get_event_list_user_id($data['user_id']),
                'all_suppliers' => $this->Supplier_list->get_supplier_list_from_event_id($event_id),
                'images' => $images,
                'audio' => $audio,
                'edit' => $this->admin_ctrl($user_id),
                'admin_only' => $this->is_admin(),
                'user_id' => $user_id,
            )
        );
        $this->load->view('footer');
    }

    public function product($product_id)
    {
 
        $data=[];   
        $data['product'] = $this->Product_list->get_product_fulldata($product_id)[0];

        if(!empty($data)){
            $data['product']['num_photos'] = $this->Images_load->count_photos('products', $product_id);
            $data['product']['main_image'] = $this->Images_load->process_images($data['product'], 'products', 'product_id');
            $all_images = $this->Images_load->get_images_from_table_and_id('products',$data['product']['product_id']);


            foreach($all_images as $key => $value){
               // $path = substr($data[$i]['path'], 9) . $data[$i]['file_name']; 
                  $path  = substr($value['path'], 9).$value['file_name'];
                  
                if (file_exists(IMAGEPATH.$path)) {

                    $src =  IMAGEURL.$path;

                } else {
                    $src = base_url('assets/img/noimage.png');
                }

                $data['product']['all_images'][$key] = $src;
                
            }
 


           // $data['product']['all_images'] = $this->Images_load->get_images_from_table_and_id('products',$data['product']['product_id']);
            $data['result'] = true;
        }else{
            $data['result'] = false;
        }
        // // For images
      
        header('Content-Type: application/json');
        echo json_encode($data); 
 
    }

    private function _image_view($type, $id, $edit)
    {
        $id_name = substr($type, 0, -1) . '_id';

        $data = array(
            'main_photo_id' => false,
            $id_name => $id,
        );
        // For images
        $num_photos = $this->Images_load->count_photos($type, $id);
        $images = $num_photos == 0 ? [] : $this->Images_load->process_images($data, $type, $id_name, false);

        $this->load->view(
            'edit/chose_main_photo',
            array(
                'images' => $images,
                'type' => $type,
                'id' => $id,
                'edit' => $edit,
            )
        );
    }

    public function chose_main_image($type, $id)
    {
        $header['name'] = 'Change Main Photo';
        $this->load->view('header', $header);
        $this->_image_view($type, $id, true);
        $this->load->view('footer');
    }

    public function view_images_no_edit($type, $id)
    {
        $header['name'] = 'All images';
        $this->load->view('header', $header);
        $this->_image_view($type, $id, false);
        $this->load->view('footer');
    }
}
