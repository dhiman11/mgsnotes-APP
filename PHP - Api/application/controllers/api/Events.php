<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Events extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Users');
        $this->load->model('event_data/Event_list');
    }
    public function get_list_by_user_id($user_id = null)
    {
        if ($user_id != null) {
            $return_data = array();
            $data = $this->Event_list->get_event_list_user_id($user_id);
            foreach ($data as $value) {
                $return_data[$value['event_id']] = $value['event_name'];
            }
            header('Content-type: application/json');
            echo json_encode($return_data);
        }
    }

    public function get_list_by_user_name($user_name = null)
    {
        if ($user_name != null) {
            if ($_SESSION['user_category'] == 'buyer') {
                $no_admin_ctrl = true;
            } else {
                $no_admin_ctrl = false;
            }
            $return_data = array();
            $data = $this->Event_list->get_event_list_user_name($user_name);
            foreach ($data as $value) {
                $return_data[$value['event_id']] = $value['event_name'];
            }
            if ($user_name == 'all') {
                if ($no_admin_ctrl || $this->session->userdata('user_type') == 'notes_admin') {
                    $return_data['this_admin'] = "All Events Of Users";
                } else {
                    $return_data['this_user'] = "All Events Of " . $this->session->userdata('user_name');
                }
            } else {
                if ($no_admin_ctrl || $this->session->userdata('user_type') == 'notes_admin') {
                    $return_data['this_admin'] = "All Events Of " . $user_name;
                }
            }
        }
        header('Content-type: application/json');
        echo json_encode($return_data);
    }

    public function get_current_user_list()
    {
        $return_data = array();
        $data = $this->Event_list->get_current_user_event_list();
        foreach ($data as $value) {
            $return_data[$value['event_id']] = $value['event_name'];
        }
        header('Content-type: application/json');
        echo json_encode($return_data);
    }
}
