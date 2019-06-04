<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Supplier_list extends CI_Model
{

    // GET LIST OF CONTACT NAME and (supplier details) USING SUPPLIER NOTE ID
    public function get_supplier__list_from_from_event_id_h($id)
    {
        $this->db->select(" `b`.* , `a`.`supplier_name`,");
        $this->db->from(' `suppliers` AS b ');
        $this->db->where('`b`.event_id IN (' . $id . ')');
        $this->db->join('suppliers_name AS a' , '`a`.`supplier_id` = `b`.`supplier_id`', 'left');
        $this->db->group_by("b.supplier_note_id");
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function get_supplier_list_from_event_id($id)
    {
        return $this->_get_supplier_list_from_event_id($id);
    }

    private function admin_ctrl()
    {
        if ($this->session->userdata('user_type') != 'notes_admin') {
            $this->db->where('b.`status` = "normal"');
            $this->db->where('b.`user_id` = "' . $this->session->userdata('user_id') . '' . '"');
        }
    }

    private function _get_supplier_list_from_event_id($event_id = -1, $select_all = false, $add_data = null)
    {
        if ($select_all) {
            $this->db->select("a.supplier_name,b.*, `d`.`user_name` AS `user_name`");
            $this->db->from('`suppliers` AS b');
            $this->db->where('b.`user_id` = `d`.`user_id`');
            $this->db->where('b.event_id', $event_id);
            $this->db->join('`suppliers_name` AS a', 'a.supplier_id = b.supplier_id','left');
            $this->db->join('`users` AS d', 'd.user_id = b.user_id','left');

        } else {
            $this->db->select('b.supplier_note_id,b.supplier_id,a.supplier_name,b.creation_date');
            $this->db->where('b.event_id', $event_id);
            $this->db->from('`suppliers` AS b');
            $this->db->join('`suppliers_name` AS a', 'b.supplier_id = a.supplier_id');
        }

        $this->admin_ctrl();
        ////////////////////* Select Query *//////////////////////
        if ($add_data != null) {
            if ($add_data['order_by']) {
                $this->db->order_by($add_data['order_by']);
            } else {
                $this->db->order_by('b.update_date', 'DESC');
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

    public function count_suppliers($event_id, $where = null)
    {
        $this->admin_ctrl();

        if ($where) {
            $this->db->where($where);
        }

        $this->db->from('`suppliers` AS b');
        $this->db->where('b.`user_id` = `d`.`user_id`');
        $this->db->where('b.event_id', $event_id);
        $this->db->join('`suppliers_name` AS a', 'a.supplier_id = b.supplier_id','left');
        $this->db->join('`users` AS d', 'd.user_id = b.user_id','left');
        return $this->db->count_all_results();
    }

    public function get_supplier_list_complete_from_event_id($event_id, $limit_start = null, $limit_length = null, $order_by = null, $where = null)
    {
        $add_data = array(
            'limit_start' => $limit_start,
            'limit_length' => $limit_length,
            'order_by' => $order_by,
            'where' => $where,
        );

        $all_data = $this->_get_supplier_list_from_event_id($event_id, true, $add_data);
        return array(
            'data' => $all_data,
            'all_count' => $this->count_suppliers($event_id, $where),
            'display_count' => count($all_data),
        );
    }

    public function get_supplier_complete_by_supplier_note_id($supplier_note_id)
    {
        $this->admin_ctrl();
        $this->db->select('`b`.*, `suppliers_name`.`supplier_name`, `users`.`user_name`');
        $this->db->join('suppliers_name', '`suppliers_name`.`supplier_id` = `b`.`supplier_id`');
        $this->db->from('`suppliers` AS b, `users`');
        $this->db->where("`supplier_note_id` = '$supplier_note_id' AND `b`.`user_id` = `users`.`user_id`");
        return $this->db->get()->result_array();
    }

    public function get_supplier_history_supplier_note_id($supplier_note_id)
    {
        
        $this->db->select('`b`.*, `suppliers_name`.`supplier_name`, `users`.`user_name`, `events`.`event_name`');
        $this->db->join('suppliers_name', '`suppliers_name`.`supplier_id` = `b`.`supplier_id`', 'left');
        $this->db->join('events', '`events`.`event_id` = `b`.`event_id`', 'left');
        $this->db->from('`suppliers` AS b, `users`');
        $this->db->where("`supplier_note_id` = '".$supplier_note_id."' AND `b`.`user_id` = `users`.`user_id` AND `events`.`event_id` = `b`.`event_id`");
        return $this->db->get()->result_array();
    }

    public function search_by_keyword($keyword, $add_on_query = null, $limit_start = 0, $limit_length = 10)
    {
        $this->admin_ctrl();
        $this->db->select('a.supplier_name, b.*, events.event_name');
        $this->db->from('suppliers as b, suppliers_name as a, events');
        $this->db->where('a.supplier_id = b.supplier_id');
        $this->db->where('b.event_id = events.event_id');
        $this->db->where("(b.note LIKE '%" . $keyword . "%' OR a.supplier_name LIKE '%" . $keyword . "%' )");
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
