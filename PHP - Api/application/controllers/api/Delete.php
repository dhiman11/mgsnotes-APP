<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Delete extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('product_data/Product_list');
        $this->load->model('supplier_data/supplier_list');
        $this->load->model('contact_data/Contact_list');
        $this->load->model('images_data/Images_load');
        $this->load->model('Likes');
        $this->load->model('Comments');
        $this->load->model('dashboard/Todo');
    }

    public function delete_data($table = null, $id = null)
    {
        $return_array = [];
        if ($_SESSION['user_type'] != 'notes_admin') {
            show_404();
            exit();
        }
        if (!($table && $id)) {
            $raw = $this->input->raw_input_stream;
            $json_array = json_decode($raw, true);
            if (!array_key_exists('name', $json_array) ||
                !array_key_exists('id', $json_array)
            ) {
                show_404();
                exit();
            }
            $table = $json_array['name'];
            $id = $json_array['id'];
        }
        if (empty($table) || empty($id)) {
            show_404();
            exit();
        }

        switch ($table) {

            case 'products':
                $to_delete = array(
                    'audio_records' => array(
                        'id_name' => 'connect_id',
                        'add_on_where' => "connect_table = 'products'",
                        'file' => true,
                    ),
                    'comments' => array(
                        'id_name' => 'connect_id',
                        'add_on_where' => "connect_table = 'products'",
                        'file' => false,
                    ),
                    'likes' => array(
                        'id_name' => 'connect_id',
                        'add_on_where' => "connect_table = 'products'",
                        'file' => false,
                    ),
                    'photos' => array(
                        'id_name' => 'connect_id',
                        'add_on_where' => "connect_table = 'products'",
                        'file' => true,
                    ),
                    'sharings' => array(
                        'id_name' => 'product_id',
                        'add_on_where' => "",
                        'file' => false,
                    ),
                    'products' => array(
                        'id_name' => 'product_id',
                        'add_on_where' => "",
                        'file' => false,
                    ),
                );
                    array_push($return_array, $this->delete_sth($to_delete, $id));
                    break;
                case 'contacts':
                    $to_delete = array(
                        'photos' => array(
                            'id_name' => 'connect_id',
                            'add_on_where' => "connect_table = 'contacts'",
                            'file' => true,
                        ),
                        'contacts' => array(
                            'id_name' => 'contact_id',
                            'add_on_where' => "",
                            'file' => false,
                        ),
                    );
                    array_push($return_array, $this->delete_sth($to_delete, $id));
                    break;
                case 'suppliers':
                    $contact_list = $this->Contact_list->get_contact_list_from_supplier_note_id($id);
                    foreach ($contact_list as $x) {
                        $this->delete_data('contacts', $x['contact_id']);
                    }
                    $product_list = $this->Product_list->get_product_list_from_supplier_note_id($id);
                    foreach ($product_list as $x) {
                        $this->delete_data('products', $x['product_id']);
                    }
                    $to_delete = array(
                        'suppliers' => array(
                            'id_name' => 'supplier_note_id',
                            'add_on_where' => "",
                            'file' => false,
                        ),
                    );
                    array_push($return_array, $this->delete_sth($to_delete, $id));
                    break;
                case 'events':
                    $supp_list = $this->supplier_list->get_supplier_list_from_event_id($id);
                    foreach ($supp_list as $x) {
                        $this->delete_data('suppliers', $x['supplier_note_id']);
                    }
                    $to_delete = array(
                        'events' => array(
                            'id_name' => 'event_id',
                            'add_on_where' => "",
                            'file' => false,
                        ),
                    );
                    array_push($return_array, $this->delete_sth($to_delete, $id));
                    break;
                default:
                    # code...
                    break;
        }

        header('Content-type: application/json');
        echo json_encode($return_array);
    }

    public function delete_tree()
    {
		if($_SESSION['user_type'] != 'notes_admin'){
			die('Not allowed to delete');
		}
		
        $post_data = $this->input->post();

        switch ($post_data['table']) {
            case 'events':
                $data['s_data'] = $this->supplier_list->get_supplier_list_from_event_id($post_data['event_id']);
                $events = ['eventname' => $post_data['event_name_'], 'event_id' => $post_data['event_id']];
                $supp_data = [];
                $contact_id = [];
				$pro_count=[];
				$con_count=[];
				foreach ($data['s_data'] as $value) {

                    $data['Contact_list'] = $this->Contact_list->get_contact_list_from_supplier_note_id($value['supplier_note_id']);
					 /////////////////////////////////////////////////////////////////////////////
                    $data['Product_list'] = $this->Product_list->get_product_list_from_supplier_note_id($value['supplier_note_id']);
					
					foreach($data['Contact_list'] as $id)
					{
						$con_count	= $this->Images_load->count_photos('contacts',$id['contact_id']);
						 
					}
					foreach($data['Product_list'] as $id)
					{ 
						 $pro_count	= $this->Images_load->count_photos('products',$id['product_id']); 
					}
					  
                    ////////////////////////////////////////////////////////////////////////////
                    ////////////////////////////////////////////////////////////////////////////
                    array_push($supp_data, array('supplier' => array($value['supplier_note_id'], $value['supplier_name'], array('contact' => array(count($data['Contact_list']),'photos'=>$con_count)), array('product' => array(count($data['Product_list']),'photo'=>$pro_count)))));
                }

                array_push($events, array("data" => $supp_data));

                header('Content-Type: application/json');
                echo json_encode($events);

                break;

        }

    }
    private function delete_sth($to_delete, $id)
    {
        $return_data = [];
        foreach ($to_delete as $key => $value) {
            $where = $value['id_name'] . " = '" . $id . "'";
            $add_on_where = $value['add_on_where'];
            if (!empty($add_on_where)) {
                $where .= " AND " . $add_on_where;
            }
            $file = null;
            if ($value['file']) {
                $this->db->select('path, file_name');
                $this->db->from($key);
                $this->db->where($where);
                $file = $this->db->get()->result_array();
                if (count($file) == 0) {
                    $file = null;
                }
            }
            $d_data = $this->db->delete($key, $where);
            $file_res = [];
            if ($file && $d_data) {
                foreach ($file as $f) {
                    if ($f['path'][1] == '.') {
                        $f['path'] = substr($f['path'], 1);
                    }
                    $file_to_delete = $f['path'] . $f['file_name'];
                    if (file_exists($file_to_delete)) {
                        system('rm ' . $file_to_delete);
                        $file_res[$f['file_name']] = true;
                    } else {
                        $file_res[$f['file_name']] = false;
                    }
                }
            }
            $return_data[$key] = array(
                'delete' => $d_data,
                'files' => $file_res,
            );
        }
        return $return_data;
    }
}
