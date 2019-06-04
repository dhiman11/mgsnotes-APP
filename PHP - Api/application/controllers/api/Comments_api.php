<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Comments_api extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Comments');
    }

    public function add_comment($type, $id)
    {
        $raw = $this->input->raw_input_stream;
        $json_array = json_decode($raw, true);

        $user_id = $this->session->userdata('user_id');

        $return_data = array('is_error' => true, 'error_info' => 'Please log in or give the right data!', 'data' => null);
        $r = $this->Comments->add_new_comment($user_id, $id, $type, $json_array);
        if ($r) {
            $return_data['is_error'] = false;
            $return_data['error_info'] = 'OK';
            $return_data['data'] = $r;
        }
        header('Content-type: application/json');
        echo json_encode($return_data);
    }

    public function update_comment($cid)
    {
        $raw = $this->input->raw_input_stream;
        $json_array = json_decode($raw, true);
        $return_data = array('is_error' => true, 'error_info' => 'Please log in or give the right data!', 'data' => null);

        $r = $this->Comments->update_comment($cid, $json_array);
        if ($r) {
            $return_data['is_error'] = false;
            $return_data['error_info'] = 'OK';
            $return_data['data'] = $json_array;
        }
        header('Content-type: application/json');
        echo json_encode($return_data);
    }

    public function delete_comment($cid)
    {
        $return_data = array('is_error' => true, 'error_info' => 'Please log in or give the right data!', 'data' => null);

        $r = $this->Comments->delete_comment($cid);
        if ($r) {
            $return_data['is_error'] = false;
            $return_data['error_info'] = 'OK';
        }
        header('Content-type: application/json');
        echo json_encode($return_data);
    }

    public function get_comments($type, $id)
    {
        $return_data = array('is_error' => true, 'error_info' => 'Please log in or give the right data!', 'data' => null);
        $data = $this->Comments->get_comments($type, $id);
        if ($data) {
            $return_data['is_error'] = false;
            $return_data['error_info'] = 'OK';
            $return_data['data'] = $data;
        }
        header('Content-type: application/json');
        echo json_encode($return_data);
    }
}
