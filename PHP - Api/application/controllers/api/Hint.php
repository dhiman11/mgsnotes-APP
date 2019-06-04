
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Hint extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Likes');
        $this->load->model('Users');
        $this->load->model('event_data/Event_list');
        $this->load->model('supplier_data/Supplier_list');
    }

    //// Users lists /////////
    public function User_list()
    {
        $data  = $this->Users->get_user_name_list(); 
        if ($_SESSION['user_type'] != 'notes_admin'){
            $data = 
            array(0 =>
                array('user_name'=>$_SESSION['user_name'],
                'user_id' =>$_SESSION['user_id'])
            );
        }

       

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    //// Event list /////////
    public function Events_list_data()
    {     
        $user_id = $_POST['user_id'];

        if ($_SESSION['user_type'] != 'notes_admin'){
            $user_id = $_SESSION['user_id'];
        }
        ////////////////////////////////////////////////////////////
        $data = $this->Event_list->event_list_by_user_id($user_id); 
        ///////////////////////////////////////////////////////////
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    //// Supplier*list*hint///
    public function Supplier_list_data()
    {   
        $event_id = $_POST['event_id'];
        ////////////////////////////////////////////////////////////
        $data = $this->Supplier_list->get_supplier_list_from_event_id($event_id);
        ///////////////////////////////////////////////////////////
        header('Content-Type: application/json');
        echo json_encode($data);

    }




}

?>