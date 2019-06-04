<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Datatable extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Users');
        $this->load->model('event_data/Event_list');
        $this->load->model('supplier_data/Supplier_list');
        $this->load->model('product_data/Product_list');
        $this->load->model('contact_data/Contact_list');
        $this->load->model('dashboard/Todo');
    }

    private function _process_datatable_org_data($aColumns)
    {
        $limit_start = $this->input->get_post('iDisplayStart', true);
        $limit_length = $this->input->get_post('iDisplayLength', true);

        $sOrder = '';
        if ($this->input->get_post('iSortCol_0', true)) {
            $cols = (int) $this->input->get_post('iSortingCols', true);
            for ($i = 0; $i < $cols; $i++) {
                if ($this->input->get_post('bSortable_' . (int) $this->input->get_post('iSortCol_' . $i, true), true) == 'true') {
                    $sOrder .= '`' . $aColumns[(int) $this->input->get_post('iSortCol_' . $i, false)] . '` ' .
                        ($this->input->get_post('sSortDir_' . $i, true) === 'desc' ? 'asc' : 'desc') . ', ';
                }
            }
            $sOrder = substr_replace($sOrder, '', -2);
        }

        $sWhere = '';
        $sSearch = $this->input->get_post('sSearch', true);
        if ($sSearch && $sSearch != '') {
            $sWhere = '(';
            for ($i = 0; $i < count($aColumns); $i++) {
                if ($this->input->get_post('bSearchable_' . $i, true) &&
                    $this->input->get_post('bSearchable_' . $i, true) == 'true') {
                    $sWhere .= '`' . $aColumns[$i] . "` LIKE '%" . $sSearch . "%' OR ";
                }
            }
            $sWhere = substr_replace($sWhere, '', -3);
            $sWhere .= ')';
        }
        return array(
            'limit_start' => $limit_start,
            'limit_length' => $limit_length,
            'sOrder' => $sOrder,
            'sWhere' => $sWhere,
            'sEcho' => intval(($this->input->get_post('sEcho', true))),
        );
    }

    private function data_output($aColumns, $model, $function_to_use, $id)
    {
        $p_data = $this->_process_datatable_org_data($aColumns);
        $data = call_user_func_array(array($model, $function_to_use), array(
            $id,
            $p_data['limit_start'],
            $p_data['limit_length'],
            $p_data['sOrder'],
            $p_data['sWhere'],
        ));

        $return_data = array(
            'sEcho' => $p_data['sEcho'],
            'iTotalRecords' => $data['display_count'],
            'iTotalDisplayRecords' => $data['all_count'],
            'aaData' => $data['data'],
        );

        header('Content-type: application/json');
        echo json_encode($return_data);
    }

    public function events()
    {
        $aColumns = array('update_date', 'event_id', 'event_name', 'city', 'from_date', 'to_date', 'user_name', 'update_date', 'status');
        $this->data_output($aColumns, $this->Event_list, 'get_event_list_complete_user_id', null);
    }

    public function event_suppliers($event_id)
    {
        $aColumns = array("b.update_date", "b.update_date", "b.update_date", "a.supplier_name", "b.note", "d.user_name", "b.update_date");
        $this->data_output($aColumns, $this->Supplier_list, 'get_supplier_list_complete_from_event_id', $event_id);
    }

    public function supplier_products($supplier_note_id)
    {
        $aColumns = array('update_date', 'product_name', 'supplier_reference', 'fob_price', 'currency', 'moq', 'note', 'status', 'user_name', 'update_date');
        $this->data_output($aColumns, $this->Product_list, 'get_products_list_complete_from_supplier_note_id', $supplier_note_id);
    }

    public function supplier_contacts($supplier_note_id)
    {
        $aColumns = array('update_date', 'contact_name', 'position', 'phone', 'email', 'note', 'status', 'user_name', 'update_date', 'creation_date');
        $this->data_output($aColumns, $this->Contact_list, 'get_contact_list_complete_from_supplier_note_id', $supplier_note_id);
    }

    public function todo()
    {
        $aColumns = array('user_id', 'user_name');
        $this->data_output($aColumns, $this->Todo, 'todo_datatable', null);
    }
}
