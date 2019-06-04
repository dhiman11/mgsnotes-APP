<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Event_list extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Users');
    }

    public function last_event($user_id)
    {
        $where = " (update_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)) &&	user_id ='" . $user_id . "' AND status='normal' ";
        $this->db->order_by('update_date', 'DESC');
        $this->db->limit(3);
        $this->db->where($where);
        $query = $this->db->select('event_id,event_name')->get('events');
        $result = $query->result_array();
        return $result;
    }

    public function last_event_select($user_id)
    {
        $where = " (user_id ='" . $user_id . "' AND status='normal')  ";
        $this->db->order_by('event_name', 'ASC');
        $this->db->where($where);
        $query = $this->db->select('event_name,event_id')->get('events');
        $result = $query->result_array();
       return $result;
    }

    ///////////////////////////////////////
    //  GET EVENT LIST ///////////////////
    public function event_list_by_user_id($user_id)
    {
        $this->db->select('*');
        $this->db->where('user_id', $user_id);
        $this->db->order_by('event_name','asc');
        $query = $this->db->get('events');
        $result = $query->result_array();
        return $result;
    }

    private function admin_ctrl($user_id = null)
    {
        if ($_SESSION['user_category'] == 'buyer') {
            $this->db->where('`events`.`status` = "normal"');
            if ($user_id != null) {
                $this->db->where('`events`.`user_id` = "' . $user_id . '' . '"');
            }
            return;
        }
        if ($this->session->userdata('user_type') == 'notes_admin') {
            if ($user_id != null) {
                $this->db->where('`events`.`user_id` = "' . $user_id . '' . '"');
            }
        } else {
            $this->db->where('`events`.`user_id` = "' . $this->session->userdata('user_id') . '" AND `events`.`status` = "normal"');
        }
    }

    private function _get_event_list_by_user_id($user_id = null, $select_all = false, $add_data = null)
    {
        if ($select_all) {
            $this->db->select("`update_date`, `event_id`, `event_name`, `city`, `from_date`, `to_date`, `users`.`user_name` AS `user_name`, `update_date`, `status`, `events`.`user_id`");
            $this->db->from('`events`, `users`');
            $this->db->where('`events`.`user_id` = `users`.`user_id`');
        } else {
            $this->db->distinct('`event_name`,`event_id`, `user_id`');
            $this->db->select('`event_name`,`event_id`, `user_id`, `creation_date`');
            $this->db->from('`events`');
        }

        $this->admin_ctrl($user_id);
        ////////////////////* Select Query *//////////////////////
        if ($add_data != null) {
            if ($add_data['order_by']) {
                $this->db->order_by($add_data['order_by']);
            } else {
                $this->db->order_by('update_date DESC');
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

    public function count_event($user_id, $where = null)
    {
        $this->admin_ctrl($user_id);

        if ($where) {
            $this->db->where($where);
        }
        $this->db->where('`events`.`user_id` = `users`.`user_id`');

        $this->db->from('events, users');
        return $this->db->count_all_results();
    }

    public function get_current_user_event_list()
    {
        return $this->_get_event_list_by_user_id(null);
    }

    public function get_event_list_user_name($username)
    {
        if ($username == 'all') {
            return $this->get_current_user_event_list();
        }
        $user_id = $this->Users->get_user_id_by_user_name($username);
        return $this->_get_event_list_by_user_id($user_id);
    }

    public function get_event_list_user_id($user_id)
    {
        return $this->_get_event_list_by_user_id($user_id);
    }

    public function get_event_list_complete_user_id($user_id, $limit_start, $limit_length, $order_by, $where)
    {
        $add_data = array(
            'limit_start' => $limit_start,
            'limit_length' => $limit_length,
            'order_by' => $order_by,
            'where' => $where,
        );

        $all_data = $this->_get_event_list_by_user_id($user_id, true, $add_data);
        return array(
            'data' => $all_data,
            'all_count' => $this->count_event($user_id, $where),
            'display_count' => count($all_data),
        );
    }

    public function get_event_complete_by_event_id($event_id = null)
    {
        if ($event_id != null) {$event_id = "`event_id` = '$event_id' AND ";} else {
            $event_id = '';
        }
        //$this->admin_ctrl();
        $this->db->select('`events`.*, `users`.`user_name`');
        $this->db->from('`events`, `users`');
        $this->db->where($event_id . " `events`.`user_id` = `users`.`user_id`");
        return $this->db->get()->result_array();
    }

    public function search_by_keyword($keyword, $add_on_query = null, $limit_start = 0, $limit_length = 10)
    {
        $this->admin_ctrl();
        $this->db->select('*');
        $this->db->from('events');
        $this->db->where("(event_name LIKE '%" . $keyword . "%'	OR	group_name LIKE '%" . $keyword . "%' OR city LIKE '%" . $keyword . "%' ) ");
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
