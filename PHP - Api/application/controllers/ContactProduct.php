<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ContactProduct extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
        $this->load->database();
        $this->load->model('event_data/event_list');
        $this->load->model('product_data/product_list');
    }

    public function index()
    {
        /////////////////////////////////////////////////////////////////////////////
        $stream_clean = json_decode($this->security->xss_clean($this->input->raw_input_stream),true);
        $user_id = $stream_clean['user_id'];
        // $data['session'] = $this->session->userdata();
        // $user_id = $this->session->userdata('user_id');
        $query = $this->db->query(' SELECT  a.*, b.* FROM suppliers_name as a, suppliers as b WHERE a.supplier_id = b.supplier_id AND a.user_id = "' . $user_id . '" ORDER BY b.`update_date` DESC LIMIT 3 ');

        //////////////////////////////////////////////////////////////////////////////
        $data['supplier_list1'] = $query->result_array();
        $data['tabdata'] = $this->input->get('tabdata', true);
        $data['active_suppliername'] = $this->input->get('suppname1', true);

        //////////////////Product Category////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////
        $data['product_category'] = $this->product_list->get_product_category();
        $data['recent_category'] = $this->product_list->get_products_category_by_recent_product();
        //////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////

        echo json_encode( $data);
        
    }

    public function supplier_suggestion()
    {
        $keyword = $_POST['keyword'];
        $where = "  supplier_name LIKE '%" . $keyword . "%' OR  supplier_full_name LIKE '%" . $keyword . "%' OR  supplier_full_name_cn LIKE '%" . $keyword . "%' LIMIT 10  ";
        $this->db->where($where);
        $result = $this->db->select('supplier_name, supplier_id')->get('suppliers_name');
        $data = $result->result_array();
        echo json_encode($data);
        //_compiled_select
    }

    public function category_suggestion()
    {
        $keyword = $_POST['keyword'];
        $where = " category_name LIKE '%$keyword%' ";
        // Get contact list
        $this->db->where($where);
        $this->db->limit(5);
        $data = $this->db->select('id,category_name')->get('product_categories')->result_array();
       
        echo json_encode($data);

    }

    public function contact_suggestion()
    {
        $keyword = $_POST['keyword'];
        $supp_id = $_POST['supp_id'];
        $user_id = $this->session->userdata('user_id');
        if ($supp_id != 'undefined') {
            // Get supplier_note_id
            $where = "supplier_id = '$supp_id'";
            $this->db->where($where);
            $result = $this->db->select('supplier_note_id')->get('suppliers')->result_array();
            // Build where for contact
            $supp_note_ids = [];
            foreach ($result as $value) {
                array_push($supp_note_ids, $value['supplier_note_id']);
            }
            $where = "contact_name LIKE '%$keyword%' AND supplier_note_id IN ('" . implode("','", $supp_note_ids) . "') AND user_id = '$user_id'";
            // Get contact list
            $this->db->where($where);
            $this->db->order_by('contact_name', 'ASC');
            $this->db->order_by('update_date', 'DESC');
            $data = $this->db->select('contact_id, contact_name, position, phone, email, update_date')->get('contacts')->result_array();
            $dict = [];
            $i = 0;
            $count = count($data);
            $new_data = [];
            for ($i = 0; $i < $count; $i++) {
                $name = $data[$i]['contact_name'];
                if (!array_key_exists($name, $dict)) {
                    $dict[$name] = true;
                    array_push($new_data, $data[$i]);
                }
            }
            echo json_encode($new_data);
        }
    }

    public function check_contact_availability()
    {
        $contactname = $_POST['con_name'];
        $username = $this->session->userdata('user_name');
        $where = " contact_name='" . addslashes($contactname) . "' && user_name='" . $username . "' ";
        $this->db->where($where);
        $result = $this->db->count_all_results('contacts');
        if ($result > 0) {
            $data = "<b  ><span style='color:red;'class='status-available'>
			 <i class='fa fa-bomb' aria-hidden='true'></i>Contact Name Already Exists</span></b>";
        } else {
            $data = "<b ><span style='color:green;' class='status-not-available'>
			<i class='fa fa-smile-o' aria-hidden='true'></i>Good, Contact Name Available</span></b>";
        }
        echo json_encode($data);
        //_compiled_select
    }

    public function check_product_availability()
    {
        $ref_name = $_POST['ref_name'];
        $username = $this->session->userdata('user_name');
        $where = "  supplier_reference='" . $ref_name . "' && user_name='" . $username . "' ";
        $this->db->where($where);
        $result = $this->db->count_all_results('products');
        if ($result > 0) {
            $data = "<b  ><span style='color:red;'class='status-available'><i class='fa fa-bomb' aria-hidden='true'></i>
				Product Name Already Exists</span></b>";
        } else {
            $data = "<b ><span style='color:green;' class='status-not-available'><i class='fa fa-smile-o' aria-hidden='true'></i>
					Good, Product Name Available</span></b>";
        }
        echo json_encode($data);
        //_compiled_select
    }

}
