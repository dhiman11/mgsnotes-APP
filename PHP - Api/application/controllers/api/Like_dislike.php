<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Like_dislike extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Likes');
    }

    public function cancel_like_dislike($id)
    {
        $res = $this->Likes->cancel_like_dislike($id);
        $return_data['is_error'] = !$res;
        $return_data['error_info'] = (!$res) ? 'You can not cancel this like!' : 'OK';
        $return_data['info'] = array('id' => -1);
        header('Content-type: application/json');
        echo json_encode($return_data);
    }

    public function add_like_dislike($type)
    {
        $raw = $this->input->raw_input_stream;
        $json_array = json_decode($raw, true);

        $pid = $json_array['spid'];
        $action_org = $json_array['action'];
        $action_type = $json_array['action_type'];
        $user_id = $this->session->userdata('user_id');

        $return_data = array(
            'is_error' => true,
            'error_info' => 'Wrong data!',
            'info' => -1,
        );

        if ($action_type == 'APPROVE') {
            $res = $this->Likes->add_like_dislike($user_id, $type, $pid, $action_org);
            $return_data['is_error'] = !$res;
            $return_data['error_info'] = $res ? 'OK' : 'You can ONLY like or dislike it once!';
            $return_data['info'] = $res;
        }

        header('Content-type: application/json');
        echo json_encode($return_data);
    }
}
