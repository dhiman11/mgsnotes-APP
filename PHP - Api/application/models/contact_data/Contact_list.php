<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Contact_list extends CI_Model
{

    // GET LIST OF CONTACT NAME and (supplier details) USING SUPPLIER NOTE ID
    public function get_contact_and_supplier__list_from_supplier_note_id($supplier_note_id)
    {

        $this->db->select(" `contacts`.* , `suppliers_name`.`supplier_name`,");
        $this->db->from(' `contacts` ');
        $this->db->where('`contacts`.supplier_note_id IN (' . $supplier_note_id . ')');
        $this->db->join('suppliers', '`contacts`.`supplier_note_id` = `suppliers`.`supplier_note_id`', 'left');
        $this->db->join('suppliers_name', '`suppliers_name`.`supplier_id` = `suppliers`.`supplier_id`', 'left');
        $this->db->group_by("contacts.contact_id");
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    // GET LIST OF CONTACT NAME USING SUPPLIER NOTE ID
    public function get_contact_list_from_supplier_note_id($id)
    {
        $this->db->select('contact_id,contact_name,creation_date');
        $this->db->where('supplier_note_id', $id);
        $this->db->order_by('contact_name', 'ASC');
        $this->db->from('contacts');
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    private function admin_ctrl()
    {
        if ($this->session->userdata('user_type') != 'notes_admin') {
            $this->db->where('`contacts`.`status` = "normal"');
            $this->db->where('`contacts`.`user_id` = "' . $this->session->userdata('user_id') . '' . '"');
        }
    }

    private function _get_contact_list_from_supplier_note_id($supplier_note_id = -1, $select_all = false, $add_data = null)
    {
        if ($select_all) {
            $this->db->select("`contacts`.*, `users`.`user_name`");
            $this->db->from('`contacts`, `users`');
            $this->db->where('`contacts`.`user_id` = `users`.`user_id`');
            $this->db->where('supplier_note_id', $supplier_note_id);
        } else {
            $this->db->select('contact_id,contact_name');
            $this->db->where('supplier_note_id', $supplier_note_id);
            $this->db->from('contacts');
        }

        $this->admin_ctrl();
        ////////////////////* Select Query *//////////////////////
        if ($add_data != null) {
            if ($add_data['order_by']) {
                $this->db->order_by($add_data['order_by']);
            } else {
                $this->db->order_by('update_date', 'DESC');
            }

            if ($add_data['limit_start'] != null && $add_data['limit_length'] != -1) {
                $this->db->limit($add_data['limit_length'], $add_data['limit_start']);
            }

            if ($add_data['where']) {
                $this->db->where($add_data['where']);
            }
        }
        ////////////////////* Select Query *//////////////////////
        $result = $this->db->get()->result_array();
        return $result;
    }

    public function count_contacts($supplier_note_id, $where = null)
    {
        $this->admin_ctrl();

        if ($where) {
            $this->db->where($where);
        }

        $this->db->where('`contacts`.`user_id` = `users`.`user_id`');
        $this->db->where('supplier_note_id', $supplier_note_id);
        $this->db->from('`contacts`, `users`');
        return $this->db->count_all_results();
    }

    public function get_contact_list_complete_from_supplier_note_id($supplier_note_id, $limit_start = null, $limit_length = null, $order_by = null, $where = null)
    {
        $add_data = array(
            'limit_start' => $limit_start,
            'limit_length' => $limit_length,
            'order_by' => $order_by,
            'where' => $where,
        );

        $all_data = $this->_get_contact_list_from_supplier_note_id($supplier_note_id, true, $add_data);
        return array(
            'data' => $all_data,
            'all_count' => $this->count_contacts($supplier_note_id, $where),
            'display_count' => count($all_data),
        );
    }

    public function get_contact_complete_by_contact_id($contact_id)
    {
        $this->admin_ctrl();
        $this->db->select('`contacts`.*, `users`.`user_name`');
        $this->db->from('`contacts`, `users`');
        $this->db->where("`contact_id` = '$contact_id' AND `contacts`.`user_id` = `users`.`user_id`");
        return $this->db->get()->result_array();
    }

    /////// GET PRODUCT DETAIL FOR ( HISTORY PAGE )  //////////////////
    public function get_contacts_for_history_by_contact_id($contact_id)
    {
        $this->db->select('`suppliers_name`.`supplier_name`,`contacts`.* ,`users`.user_name');
        $this->db->from('contacts');
        $this->db->join('users', ' `users`.`user_id` = `contacts`.`user_id` ', 'left');
        $this->db->join('suppliers', ' `contacts`.`supplier_note_id` = `suppliers`.`supplier_note_id` ', 'left');
        $this->db->join('suppliers_name', ' `suppliers_name`.`supplier_id` = `suppliers`.`supplier_id` ', 'left');
        ///////////////////////////////////////////////////////////////////////////////////////////////
        $this->db->where("`contact_id` = '" . $contact_id . "' && `suppliers`.`supplier_id` = `suppliers_name`.`supplier_id` ");
        return $this->db->get()->result_array();
    }

    public function search_by_keyword($keyword, $add_on_query = null, $limit_start = 0, $limit_length = 10)
    {
        $this->admin_ctrl();
        $this->db->select('contacts.*, suppliers_name.supplier_name');
        $this->db->from('contacts, suppliers_name, suppliers');
        $this->db->where('contacts.supplier_note_id = suppliers.supplier_note_id');
        $this->db->where('suppliers.supplier_id = suppliers_name.supplier_id');
        $this->db->where("(contact_name LIKE '%$keyword%' OR phone LIKE '%$keyword%' OR email LIKE '%$keyword%' OR contacts.note LIKE '%$keyword%')");
        if ($add_on_query) {
            $this->db->where($add_on_query);
        }
        $count_all = $this->db->count_all_results(null, false);
        $this->db->limit($limit_length, $limit_start);
        return array(
            'data' => $this->db->get()->result_array(),
            'count' => $count_all,
        );
    }
}
