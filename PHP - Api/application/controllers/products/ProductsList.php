<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ProductsList extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
        $this->load->database();
        $this->load->model('product_data/Product_list');
        $this->load->model('Users');
        $this->load->model('event_data/Event_list');
        $this->load->model('images_data/Images_load');
        $this->load->model('Likes');
        $this->load->model('Comments');
        $this->cats = [0 => 'NO CATEGORY'];
        $this->load_cat();
    }

    private function load_cat()
    {
        $data = $this->Product_list->category_list();
        foreach ($data as $cat) {
            $this->cats[intval($cat['id'])] = $cat['category_name'];
        }
    }

    private function get_cat_name($cid)
    {
        return array_key_exists($cid, $this->cats) ? $this->cats[$cid] : 'UNKNOW CATEGORY';
    }

    public function index()
    {
     

        $result_1 = $this->Event_list->get_current_user_event_list();
        $result_2 = $this->Users->get_user_name_list();
        $result_3 = $this->Product_list->category_list();
        // print_r($result_3);

        $data_sorting_bar = array('result_1' => $result_1, 'result_2' => $result_2, 'result_3' => $result_3);
        
        echo json_encode($data_sorting_bar);
    }

    public function all_products($data = false)
    {
        
        $data = json_decode($this->security->xss_clean($this->input->raw_input_stream),true);
 
        if ($data) {
            $data = $data;
        } else {
            $data = [];
            $limit = empty($_POST['limit']) ? false : $_POST['limit'];
            $data['limit'] = empty($_POST['limit']) ? 0 : $_POST['limit'];

            $data['field_name'] = empty($_POST['f']) ? " products.creation_date " : $_POST['f'];
            $data['field_order'] = empty($_POST['o']) ? " DESC " : $_POST['o'];

            // Event id
            if ($_SESSION['user_category'] != 'buyer' && $_SESSION['user_type'] == 'notes_user') {
                $event_id = "  events.user_id = '" . $this->session->userdata('user_id') . "'";
            }
            if ($_POST['event_name'] != '') {
                $event_id = addslashes($_POST['event_name']);
                if ($_POST['event_name'] == 'this_user') {
                    $event_id = "  events.user_id = '" . $this->session->userdata('user_id') . "'";
                } elseif ($_POST['event_name'] == 'this_admin') {
                    $event_id = "  1 ";
                } else {
                    $event_id = "  events.event_id = '" . $event_id . "'";
                }
            }
            $data['event_id'] = $event_id;

            $data['min_usd'] = empty($_POST['min_usd']) ? false : $_POST['min_usd'];
            $data['max_usd'] = empty($_POST['max_usd']) ? false : $_POST['max_usd'];

            $data['min_date'] = empty($_POST['min_date']) ? false : $_POST['min_date'];
            $data['max_date'] = empty($_POST['max_date']) ? false : $_POST['max_date'];

            $data['search_type'] = empty($_POST['search_type']) ? false : $_POST['search_type'];
            $data['search_by_user'] = empty($_POST['search_by_user']) ? false : ($_POST['search_by_user'] != 'all') ? $this->Users->get_user_id_by_user_name($_POST['search_by_user']) : 'all';

            $data['search_input'] = empty($_POST['search_input']) ? false : $_POST['search_input'];
            $data['categories'] = ($_POST['categories'] == '') ? '-1' : $_POST['categories'];
        }
           
        $data = $this->Product_list->getProductsData($data);

      
        
        $all_pros = $data['data'];
        $count = $data['count'];
        $products = '';

        $final_data['limit'] = isset($limit) ? $limit : 0;
        $final_data['totalCount'] = $count;
        $user_id = $this->session->userdata('user_id');
        
        
        $j = 0; 

        foreach ($all_pros as $var) {
            $one_data[$j] = [];

         
          
            $one_data[$j]['mysqlarrayP'] = $var;
            $images_data = $this->Images_load->process_images($var, 'products', 'product_id', 1);
            $one_data[$j]['images'] = $images_data;
            $one_data[$j]['total_photos'] = count($images_data);

            // For like and dislike
            $res_ld = $this->Likes->get_user_status($user_id, 'products', $var['product_id']);
            $one_data[$j]['like_id'] = $res_ld['like_count'] == 0 ? '-1' : $res_ld['id'];
            $one_data[$j]['user_status_like'] = $res_ld['like_count'] == 0 ? '' : ' like_clicked ';
            $one_data[$j]['like_count'] = $this->Likes->count_like_dislike('LIKE', $var['product_id'], 'products');
            $one_data[$j]['dislike_id'] = $res_ld['dislike_count'] == 0 ? '-1' : $res_ld['id'];
            $one_data[$j]['user_status_dislike'] = $res_ld['dislike_count'] == 0 ? '' : ' like_clicked ';
            $one_data[$j]['dislike_count'] = $this->Likes->count_like_dislike('DISLIKE', $var['product_id'], 'products');
            $_user_liked = $this->Likes->get_like_dislike_username_list('LIKE', $var['product_id'], 'products', 10);
            $_user_disliked = $this->Likes->get_like_dislike_username_list('DISLIKE', $var['product_id'], 'products', 10);

            // For share info
            $one_data[$j]['share_info'] = $this->Product_list->get_shared_count($var['product_id']);

            $len = count($_user_liked);
            $user_liked = '';
            $user_disliked = '';
            if ($len > 0) {
                $user_liked = $_user_liked[0]['user_name'];
                for ($i = 1; $i < $len; $i++) {
                    $user_liked .= ', ' . $_user_liked[$i]['user_name'];
                }
            }

            $len = count($_user_disliked);
            if ($len > 0) {
                $user_disliked = $_user_disliked[0]['user_name'];
                for ($i = 1; $i < $len; $i++) {
                    $user_disliked .= ', ' . $_user_disliked[$i]['user_name'];
                }
            }

            $one_data[$j]['user_liked'] = $user_liked;
            $one_data[$j]['user_disliked'] = $user_disliked;
            // For comment
            $one_data[$j]['count'] = $this->Comments->get_num_comment_by_id('products', $var['product_id']); 
           
            $j++;
        }

        header('Content-Type: application/json');
        echo  json_encode($one_data);
    }
 
}
