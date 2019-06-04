<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Suppliers extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('supplier_data/Supplier_list');
    }

    public function get_supplier_list_by_event_id($event_id)
    {
        $return_data = $this->Supplier_list->get_supplier_list_from_event_id($event_id);
        header('Content-type: application/json');
        echo json_encode($return_data);
    }
}
