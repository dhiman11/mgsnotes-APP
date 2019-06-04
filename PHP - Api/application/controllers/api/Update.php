<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Update extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('event_data/Event_update');
        $this->load->model('supplier_data/Supplier_update');
        $this->load->model('contact_data/Contact_update');
        $this->load->model('product_data/Product_update');
        $this->load->model('images_data/Images_rotate');
        $this->load->model('images_data/Images_insert');
        $this->load->model('supplier_data/Supplier_existence');
        $this->load->model('images_data/Main_image_set');
    }

    private function images_rotate($id, $deg)
    {
        $this->Images_rotate->rotate_image($id, $deg);
    }

    private function get_cleaned_data()
    {
        //////////////////Recieve Json array
        $raw = $this->input->raw_input_stream;
        if (!empty($raw)) {
            $json_array = json_decode($raw, true);
        } else {
            $json_array = $this->input->post();
        }
        /////////////////////////////////XSS CLEAN DATA
        if (array_key_exists('request_from_report', $json_array)) {
            if (array_key_exists('data', $json_array)) {
                $json_array['data'] = $this->security->xss_clean($json_array['data']);
                return $json_array;
            }
        }
        exit('ERROR 500');
    }

    private function get_update_data_array($data_array, $accept_field)
    {
        $data = array();
        foreach ($accept_field as $key => $value) {
            if (array_key_exists($key, $data_array)) {
                if (!empty($data_array[$key])) {
                    $data[$value] = $data_array[$key];
                }
            }
        }
        return $data;
    }

    public function event($id)
    {
        $json_array = $this->get_cleaned_data();
        $accept_field = array(
            'event_name' => 'event_name',
            'city' => 'city',
            'from_date' => 'from_date',
            'to_date' => 'to_date',
            'group_name' => 'group_name',
            'status' => 'status',
        );

        if (!$json_array['request_from_report']) {
            $data = $this->get_update_data_array($json_array['data'], $accept_field);
        } else {
            // id, city, from_date, to_date
            $data = array(
                'city' => $json_array['data'][0],
                'from_date' => $json_array['data'][1],
                'to_date' => $json_array['data'][2],
            );
            // print_r($data);
        }
        $this->Event_update->event_update($id, $data);
        // images
        if (array_key_exists('image', $json_array)) {
            $this->images_rotate($json_array['image'][0], $json_array['image'][1]);
        }
    }

    public function supplier($id)
    {
        $json_array = $this->get_cleaned_data();
        $accept_field = array(
            'supplier_id' => 'suppliers.supplier_id',
            'update_date' => 'suppliers.update_date',
            'note' => 'suppliers.note',
            'event_id' => 'suppliers.event_id',
            'status' => 'suppliers.status',
        );

        if (!$json_array['request_from_report']) {
            $supplier_name = $json_array['data']['supplier_name'];
            if (!empty($supplier_name)) {
                $supplier_id = $this->Supplier_existence->creat_new_supplier_name($supplier_name);
                $json_array['data']['supplier_id'] = $supplier_id;
            }
            $data = $this->get_update_data_array($json_array['data'], $accept_field);
        } else {
            // id, supplier_name, note
            $data = array(
                'supplier_name' => $json_array['data'][0],
                'note' => $json_array['data'][1],
            );
        }
        // id, name, note
        $this->Supplier_update->supplier_update($id, $data);
        // images
        if (array_key_exists('image', $json_array)) {
            $this->images_rotate($json_array['image'][0], $json_array['image'][1]);
        }
        if (array_key_exists('images', $json_array)) {
            $images_add = $json_array['images'];
            $count_img = count($images_add);
            if ($count_img != 0) {
                $this->Images_insert->insert_images('suppliers', $id, '../0_data/supplier_info/', $images_add, $json_array['rotates']);
            }
        }
    }

    public function contact($id)
    {
        $json_array = $this->get_cleaned_data();
        $accept_field = array(
            'supplier_note_id' => 'supplier_note_id',
            'contact_name' => 'contact_name',
            'position' => 'position',
            'phone' => 'phone',
            'email' => 'email',
            'note' => 'note',
            'update_date' => 'update_date',
            'status' => 'status',
        );

        if (!$json_array['request_from_report']) {
            $data = $this->get_update_data_array($json_array['data'], $accept_field);
        } else {
            // id, contact_name, position, email, phone, note
            $data = array(
                'contact_name' => $json_array['data'][0],
                'position' => $json_array['data'][1],
                'email' => $json_array['data'][3],
                'phone' => $json_array['data'][2],
                'note' => $json_array['data'][4],
            );
        }
        // id, contact_name, position, email, phone, note
        $this->Contact_update->contact_update($id, $data);
        // images
        if (array_key_exists('image', $json_array)) {
            $this->images_rotate($json_array['image'][0], $json_array['image'][1]);
        }
        if (array_key_exists('images', $json_array)) {
            $images_add = $json_array['images'];
            $count_img = count($images_add);
            if ($count_img != 0) {
                $this->Images_insert->insert_images('contacts', $id, '../0_data/supplier_contact/', $images_add, $json_array['rotates']);
            }
        }
    }

    public function product($id)
    {
        $json_array = $this->get_cleaned_data();
        // print_r($json_array);

        $accept_field = array(
            'supplier_note_id' => 'supplier_note_id',
            'product_name' => 'product_name',
            'supplier_reference' => 'supplier_reference',
            'currency' => 'currency',
            'fob_price' => 'fob_price',
            'moq' => 'moq',
            'note' => 'note',
            'update_date' => 'update_date',
            'status' => 'status',
            'product_category' => 'product_cat_id',
        );

        if (!$json_array['request_from_report']) {
            $data = $this->get_update_data_array($json_array['data'], $accept_field);
        } else {
            // id, supplier_reference, product_name, note, fob_price, moq
            $data = array(
                'supplier_reference' => $json_array['data'][1],
                'product_name' => $json_array['data'][0],
                'note' => $json_array['data'][4],
                'fob_price' => $json_array['data'][2],
                'moq' => $json_array['data'][3],
            );
        }
        // id, supplier_reference, product_name, note, fob_price, moq
        $this->Product_update->product_update($id, $data);
        // images
        if (array_key_exists('image', $json_array)) {
            $this->images_rotate($json_array['image'][0], $json_array['image'][1]);
        }
        if (array_key_exists('images', $json_array)) {
            $images_add = $json_array['images'];
            $count_img = count($images_add);
            if ($count_img != 0) {
                $this->Images_insert->insert_images('products', $id, '../0_data/supplier_product/', $images_add, $json_array['rotates']);
            }
        }
    }

    public function chose_main_photo($type, $id)
    {
        $main_image_id = $this->input->get_post('main_image_id');
        $id_array = array(
            'suppliers' => 'supplier_note_id',
            'contacts' => 'contact_id',
            'products' => 'product_id',
        );
        $this->Main_image_set->set_main_image($type, $id_array[$type], $id, $main_image_id);
    }

    public function update_pro_cat()
    {
        $pro_ids = implode(",", explode(',', $this->input->post('productid', true)));

        $cid = $this->input->post('category_id', true);
        $this->Product_update->product_cat_update($pro_ids, $cid);

        echo json_encode(array("result"=>true));
    }
}
