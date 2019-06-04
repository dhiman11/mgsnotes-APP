<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Events extends MY_Controller
{
 
    public function __construct()
    {

        parent::__construct();
        $this->load->helper(array('form', 'url'));
        $this->load->database();
        $this->load->model('event_data/event_list');
        //$this->load->helper('cookie');

    }

    public function index()
    {
        $data = json_decode($this->security->xss_clean($this->input->raw_input_stream),true);
       
        $user_id = $data['user_id'];

        $data['interval_list'] = $this->event_list->last_event($user_id);
        $data['interval_list_select'] = $this->event_list->last_event_select($user_id); 
        $data['loggedin_user'] = $this->session->userdata('user_id');

        /* header Heading below*/
        $data['header_name'] = 'Choose Event';
 

        echo json_encode($data);
    }

    public function checkExistence()
    {

        $event_name = $this->input->post('fairnew');
        $username = $this->session->userdata('user_id');
        $this->db->where('event_name', $event_name);
        $this->db->where('user_id', $username);
        $result = $this->db->count_all_results('events');
        if ($result > 0) {
            echo json_encode(array("msg"=>"Event Name Already Exists"));
        } else {
            echo json_encode(array("msg"=>"Good, Event Name Available"));
        }

    }

    public function validateEvent()
    {

    }

    public function event_session()
    {
        $fairname = $this->input->post('value');
        echo json_encode(array("fairname"=>$fairname));
        //$this->session->set_userdata('fairname', $fairname);
    }

    public function addevent()
    {
        $addeventval = json_decode($this->security->xss_clean($this->input->raw_input_stream),true);


        $fairnew = $this->security->xss_clean($addeventval['fairnew']);
        $city = $this->security->xss_clean($addeventval['city']);
        $fromdate = $addeventval['fromdate'];
        $todate = $addeventval['todate'];
        $user_id = $addeventval['user_id'];
        /////////////////////////////////////////////////
        
       
        /////////////////////////////////////////////////
        /////////////////////////////////////////////////
        $data = array(
            'event_name' => $fairnew,
            'city' => $city,
            'from_date' => $fromdate,
            'to_date' => $todate,
            'status' => 'normal',
            'update_date' => date('Y-m-d H:i:s'),
            'creation_date' => date('Y-m-d H:i:s'),
            'user_id' => $user_id,
        );

        $this->db->insert('events', $data);
        $last_inserted = $this->db->insert_id();
        
        echo json_encode(array('msg'=>"New event Inserted",'last_inserted_id'=> $last_inserted));

        
    }

}
