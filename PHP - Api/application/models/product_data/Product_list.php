<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product_list extends CI_Model
{
    public function get_product_list_from_supplier_note_id($id)
    {
        return $this->_get_product_list_from_supplier_note_id($id);
    }

    private function admin_ctrl()
    {
        if ($this->session->userdata('user_type') != 'notes_admin') {
            $this->db->where('`products`.`status` = "normal"');
            $this->db->where('`products`.`user_id` = "' . $this->session->userdata('user_id') . '' . '"');
        }
    }

    private function _get_product_list_from_supplier_note_id($supplier_note_id = -1, $select_all = false, $add_data = null)
    {
        if ($select_all) {
            $this->db->select("`products`.*, `users`.`user_name`");
            $this->db->from('`products`, `users`');
            $this->db->where('`products`.`user_id` = `users`.`user_id`');
            $this->db->where('supplier_note_id', $supplier_note_id);
        } else {
            $this->db->select('product_id, product_name, creation_date');
            $this->db->where('supplier_note_id', $supplier_note_id);
            $this->db->from('products');
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

    public function count_products($supplier_note_id, $where = null)
    {
        $this->admin_ctrl();

        if ($where) {
            $this->db->where($where);
        }

        $this->db->where('`products`.`user_id` = `users`.`user_id`');
        $this->db->where('supplier_note_id', $supplier_note_id);
        $this->db->from('`products`, `users`');
        return $this->db->count_all_results();
    }

    public function get_products_list_complete_from_supplier_note_id($supplier_note_id, $limit_start = null, $limit_length = null, $order_by = null, $where = null)
    {
        $add_data = array(
            'limit_start' => $limit_start,
            'limit_length' => $limit_length,
            'order_by' => $order_by,
            'where' => $where,
        );

        $all_data = $this->_get_product_list_from_supplier_note_id($supplier_note_id, true, $add_data);
        return array(
            'data' => $all_data,
            'all_count' => $this->count_products($supplier_note_id, $where),
            'display_count' => count($all_data),
        );
    }

    public function get_product_complete_by_product_id($product_id)
    {
        $this->admin_ctrl();
        $this->db->select('`products`.*, `users`.`user_name`');
        $this->db->from('`products`, `users`');
        $this->db->where("`product_id` = '$product_id' AND `products`.`user_id` = `users`.`user_id`");
        return $this->db->get()->result_array();
    }

    public function get_product_fulldata($product_id)
    {
       
        $this->db->select('`products`.*,users.user_name,product_categories.category_name,suppliers_name.supplier_name,events.event_name');
        $this->db->from('`products`');
      
        $this->db->join('users','users.user_id = products.user_id','left');
        $this->db->join('product_categories','product_categories.id = products.product_cat_id','left');
        $this->db->join('suppliers','suppliers.supplier_note_id = products.supplier_note_id','left');
        $this->db->join('suppliers_name','suppliers_name.supplier_id = suppliers.supplier_id','left');
        $this->db->join('events','events.event_id = suppliers.event_id','left');
         
        $this->db->where("`product_id` = '".$product_id."' AND `products`.`user_id` = `users`.`user_id`");

        return $this->db->get()->result_array();
    }



    ////////////PRODUCT CATEGORY////////////////////////
    ////////////////////////////////////////////////////
    public function get_product_category()
    {
        $this->db->select('*');
        $this->db->order_by('category_name', 'asc');
        $this->db->from('`product_categories`');
        return $this->db->get()->result_array();
    }
    ////////////////////////////////////////////////////
    ////////////////////////////////////////////////////

    ////////////recent products with product cat id  ////////////////////
    ////////////////////////////////////////////////////
    public function get_products_category_by_recent_product()
    {
        //  $this->db->group_by("product_categories.category_name");
        //////////////////////////////////////////////////////////////
        //$this->db->distinct();
        $this->db->select('product_categories.category_name , product_categories.id');
        $this->db->from('product_categories,products');
        $this->db->order_by("products.product_id", "DESC");

        $this->db->limit(20);
        $this->db->where(" `product_categories`.`id` = `products`.`product_cat_id` ");
        $this->db->where(" `products`.`user_id` = " . $this->session->userdata('user_id'));
        return $this->db->get()->result_array();
        //////////////////////////////////////////////////////////////
    }
    ////////////////////////////////////////////////////
    ////////////////////////////////////////////////////

    ////////// GET LIST OF product data and (supplier details) USING SUPPLIER NOTE ID
    public function get_products_and_supplier__list_from_supplier_note_id($supplier_note_id)
    {
        $this->db->select(" `products`.* , `suppliers_name`.`supplier_name`");
        $this->db->from(' `products` ');
        $this->db->where('`suppliers`.supplier_note_id IN (' . $supplier_note_id . ')');
        $this->db->join('suppliers', '`products`.`supplier_note_id` = `suppliers`.`supplier_note_id`', 'left');
        $this->db->join('suppliers_name', '`suppliers_name`.`supplier_id` = `suppliers`.`supplier_id`', 'left');
        $this->db->group_by("products.product_id");
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    ////////// GET LIST OF product category
    public function category_list()
    {
        $this->db->select("*");
        $this->db->from('product_categories');
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;

    }
    //-------------------------------------------------------------------------------------------------//
    //-------------------------------------------------------------------------------------------------//
    /////// GET PRODUCT DETAIL FOR ( HISTORY PAGE )  //////////////////
    public function get_product_for_history_by_product_id($product_id)
    {
        $this->db->select('`suppliers_name`.`supplier_name`,`products`.*, `users`.`user_name`');
        $this->db->from('products');
        $this->db->join('suppliers', '`products`.`supplier_note_id` = `suppliers`.`supplier_note_id`', 'left');
        $this->db->join('suppliers_name', '`suppliers_name`.`supplier_id` = `suppliers`.`supplier_id`', 'left');
        $this->db->join('users', '`users`.`user_id` = `products`.`user_id`', 'left');
        //-------------------------------------------------------------------------------------------------//
        //-------------------------------------------------------------------------------------------------//
        $this->db->where("`product_id` = '" . $product_id . "' AND `products`.`user_id` = `users`.`user_id`");
        return $this->db->get()->result_array();
    }

    public function search_by_keyword($keyword, $add_on_query = null, $limit_start = 0, $limit_length = 10)
    {
        $this->admin_ctrl();
        $this->db->select('products.*, suppliers_name.supplier_name');
        $this->db->from('products, suppliers, suppliers_name');
        $this->db->where('products.supplier_note_id = suppliers.supplier_note_id');
        $this->db->where('suppliers_name.supplier_id = suppliers.supplier_id');
        $this->db->where("( product_name LIKE '%" . $keyword . "%' OR	supplier_reference LIKE '%" . $keyword . "%' OR products.note LIKE '%" . $keyword . "%' )");
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

    public function getProductsData($data)
    {
      
       

        $limit = array_key_exists('limit', $data) ? $data['limit'] : 0;

        $field_name = array_key_exists('field_name', $data) ? $data['field_name'] : 'products.creation_date';
        $field_order = array_key_exists('field_order', $data) ? $data['field_order'] : 'DESC';

        $event_id = array_key_exists('event_id', $data) ? $data['event_id'] : '  1 ';

        $min_usd = array_key_exists('min_usd', $data) ? $data['min_usd'] : false;
        $max_usd = array_key_exists('max_usd', $data) ? $data['max_usd'] : false;

        $org_category = explode(',', (array_key_exists('categories', $data) && $data['categories'] != '') ? $data['categories'] : '-1');
        $category = null;
        if (!in_array('-1', $org_category)) {
            $category = "'" . implode("','", $org_category) . "'";
        }

        $min_date = array_key_exists('min_date', $data) ? $data['min_date'] : false;
        $max_date = array_key_exists('max_date', $data) ? $data['max_date'] : false;

        $search_type = array_key_exists('search_type', $data) ? $data['search_type'] : 'all';
        $search_by_user = array_key_exists('search_by_user', $data) ? $data['search_by_user'] : 'all';

        $search_input = array_key_exists('search_input', $data) ? $data['search_input'] : false;

        // Build the query

        $category_q = "";
        if (!empty($category)) {
            $category_q = " && (products.product_cat_id IN ($category) )";
        }

        // Event id
        if ($event_id) {
            $where = ' WHERE ' . $event_id;
        } else {
            $where = 'WHERE 1';
        }

        // Money
        $min_max_usd = ' ';
        if (!empty($min_usd)) {
            $min_max_usd = ' && products.fob_price >= "' . $min_usd . '"';
        } 
        if (!empty($max_usd)) {
            $min_max_usd = ' && products.fob_price <= "' . $max_usd . '"';
        }
        if (!empty($min_usd) && !empty($max_usd)) {
            $min_max_usd = ' && products.fob_price BETWEEN "' . $min_usd . '" AND "' . $max_usd . '"';
        }

        // Date
        $min_max_date = ' ';
        if (!empty($min_date)) {
            $min_max_date = ' && products.creation_date >= "' . $min_date . '"';
        }
        if (!empty($max_moq)) {
            $min_max_date = ' && products.creation_date <= "' . $max_date . '"';
        }
        if (!empty($min_date) && !empty($max_date)) {
            $min_max_date = ' && products.creation_date BETWEEN "' . $min_date . ' 00:00:00" AND "' . $max_date . ' 23:59:59"';
        }

        // Search type
        $fiedname = ' ';
        if ($search_type == 'item_name') {
            $fiedname = ' products.product_name ';
        } elseif ($search_type == 'description') {
            $fiedname = 'products.note';
        } elseif ($search_type == 'item_ref') {
            $fiedname = 'products.supplier_reference';
        } elseif ($search_type == 'supp_nameeee') {
            $fiedname = 'suppliers_name.supplier_name';
        }
       

        // By user
        $by_user = ' ';
        if ($search_by_user != 'all') {
            $by_user = ' && products.user_id = "' . $search_by_user . '"';
        }

        // Keyword search
        $search_here = ' ';
        if (!empty($search_input)) {
            $searchK = $search_input;

            $searchArray = explode(' ', $searchK);
            $search_where = '';

            $i = 0;
            foreach ($searchArray as $value) {
                //strstr
                if (strstr($search_input, '"')) {
                    $string = ' = ' . $value . '"';
                    $string = $search_input;
                    preg_match('"([^\\"]+)"', $string, $result);
                    ///////////////////////////////////////////////////
                    ///////If Search Exactly (All Fields)
                    if ($search_type == 'all') {
                        $string = 'products.product_name  = "' . $result[0] . '" OR products.note  = "' . $result[0] . '" OR products.supplier_reference  = "' . $result[0] . '" OR suppliers_name.supplier_name  = "' . $result[0] . '"';
                    } else {
                        $string = $fiedname . ' = "' . $result[0] . '"';
                    }

                } else {
                    ///////////////////////////////////////////////////
                    ///////If Search Exactly (All Fields)
                    if ($search_type == 'all') {
                        $string = 'products.product_name  LIKE "%' . $value . '%" OR products.note  LIKE "%' . $value . '%" OR products.supplier_reference  LIKE "%' . $value . '%" OR suppliers_name.supplier_name  LIKE "%' . $value . '%"';
                    } else {
                        $string = $fiedname . '  LIKE "%' . $value . '%"';
                    }
                }

                $search_where .= ' && (' . $string . ')';
                $search_here = $search_where;
                $i++;
            }
        }

        $status_ctrl = '';
        if ($this->session->userdata('user_type') != 'notes_admin') {
            $status_ctrl = ' && `products`.`status` = "normal" ';
        }
        session_write_close();

        $QueryP = 'SELECT products.*, suppliers_name.supplier_name, users.user_name  FROM `products`
		LEFT JOIN suppliers on products.supplier_note_id = suppliers.supplier_note_id
		LEFT JOIN suppliers_name on suppliers_name.supplier_id = suppliers.supplier_id
		LEFT JOIN events on events.event_id = suppliers.event_id
        LEFT JOIN users on users.user_id = products.user_id
		' . $where . '  ' . $by_user . '  ' . $category_q . '  ' . $min_max_usd . '  ' . $min_max_date . ' ' . $search_here . $status_ctrl . '
		ORDER BY ' . $field_name . ' ' . $field_order;
        $limit = ' LIMIT ' . $limit . ',10';

        $QueryP2 = $QueryP . $limit;
        // echo $QueryP2;
        // Query
        $this->db->cache_on();
        $products_data = $this->db->query($QueryP2)->result_array();
        $count_total = $this->db->query($QueryP);
        $totalCount = $count_total->num_rows();

        $return_data = array('data' => $products_data, 'count' => $totalCount);
        return $return_data;
    }

    public function record_share_product($ids, $emails)
    {
        $count = count($emails);
        $db_emails = implode(',', $emails);
        $user_id = $_SESSION['user_id'];
        foreach ($ids as $id) {
            $insert_array = array(
                'product_id' => $id,
                'user_id' => $user_id,
                'email_recipients' => $db_emails,
                'quantity_of_recipients' => $count,
                'creation_date' => date('Y-m-d h:i:s'),
            );
            $this->db->insert('sharings', $insert_array);
        }
    }

    public function get_shared_count($id, $no_html = false)
    {
        $this->db->select('SUM(`quantity_of_recipients`) AS `res`');
        $this->db->from('sharings');
        $this->db->where("`product_id` = '$id'");
        $count_data = $this->db->get()->result_array();

        $this->db->distinct();
        $this->db->select('user_name');
        $this->db->from('sharings');
        $this->db->where("`product_id` = '$id'");
        $this->db->join('users', 'users.user_id = sharings.user_id', 'left');
        $_user_data = $this->db->get()->result_array();
        $user_data = [];
        $i = 0;
        foreach ($_user_data as $_) {
            array_push($user_data, $_['user_name']);
            $i++;
            if ($i > 10) {
                break;
            }

        }
        $count = $count_data[0]['res'] ? $count_data[0]['res'] : 0;
        $s_users = '';
        if ($count != 0) {
            if ($no_html) {
                $s_users = $user_data;
            } else {
                $s_users = '<i class=\'fa fa-envelope\' style=\'font-size: 15px; color: green\'></i> ' . implode(', ', $user_data);
            }
        }

        return array(
            'count' => $count,
            'users' => $s_users,
        );
    }

    private function sql_build($limit = 20, $add_on_query = null)
    {
        $this->admin_ctrl();
        $this->db->select('products.*, suppliers_name.supplier_name, users.user_name');
        $this->db->from('products, suppliers, suppliers_name, users');
        $this->db->where('products.supplier_note_id = suppliers.supplier_note_id');
        $this->db->where('suppliers_name.supplier_id = suppliers.supplier_id');
        $this->db->where('products.user_id = users.user_id');
        if (empty($limit)) {
            $limit = 20;
        }
        $this->db->limit($limit);
        if ($add_on_query) {
            $this->db->where($add_on_query);
        }
    }
    public function get_recent_products($limit = 2, $add_on_query = null)
    {
        $this->sql_build($limit, $add_on_query);
        $this->db->order_by('products.creation_date DESC');
        $count_all = $this->db->count_all_results(null, false);
        return $this->db->get()->result_array();
    }

    public function get_random_products($limit = 2, $add_on_query = null)
    {
        $this->sql_build($limit, $add_on_query);
        $this->db->order_by('RAND()');
        $count_all = $this->db->count_all_results(null, false);
        return $this->db->get()->result_array();
    }



        /////////////////////////////////////////////////////////////
        ////////////////////SUGGESTED PRODUCTS/////////////////////// 
        public function suggested_products($start=0,$limit = 5)
        {
            $this->db->select('users.user_name,suppliers_name.supplier_name,count(`sharings`.`product_id`) as product_sharing,count(`comments`.`connect_id`) as product_comments,count(`likes`.`connect_id`) as product_likes , `products`.*');
            $this->db->order_by('sharings.creation_date','desc');
            $this->db->limit($limit,$start);
            $this->db->from('products');
            ////////////////////////////// ////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////////////
        
            $this->db->join('sharings', 'sharings.product_id = products.product_id', 'left');
            $this->db->join('users', 'users.user_id = products.product_id', 'left');
            $this->db->join('comments', 'comments.connect_id = products.product_id', 'left');
            $this->db->join('suppliers', '`products`.`supplier_note_id` = `suppliers`.`supplier_note_id`', 'left');
            $this->db->join('suppliers_name', '`suppliers_name`.`supplier_id` = `suppliers`.`supplier_id`', 'left');
            $this->db->join('likes', 'likes.connect_id = products.product_id', 'left');
            $this->db->group_by('products.product_id');

            $this->db->where('sharings.user_id',$_SESSION['user_id']);
            return $this->db->get()->result_array();
        }
        /////////////////////////////////////////////////////////////
        ///////////////////INTEREDTED PRODUCTS//////////////////////// 
        public function most_interested($start = 10,$order,$limit=6)
        {
            $this->db->select('users.user_name,suppliers_name.supplier_name,count(`sharings`.`product_id`) as product_sharing,count(`comments`.`connect_id`) as product_comments,count(`likes`.`connect_id`) as product_likes , `products`.*');
            
            $this->db->order_by($order,'desc');
            $this->db->from('products'); 
        
            $this->db->join('sharings', 'sharings.product_id = products.product_id', 'left');
            $this->db->join('users', 'users.user_id = products.product_id', 'left');
            $this->db->join('suppliers', '`products`.`supplier_note_id` = `suppliers`.`supplier_note_id`', 'left');
            $this->db->join('suppliers_name', '`suppliers_name`.`supplier_id` = `suppliers`.`supplier_id`', 'left');
            $this->db->join('comments', 'comments.connect_id = products.product_id', 'left');
            $this->db->join('likes', 'likes.connect_id = products.product_id', 'left');
            $this->db->group_by('products.product_id');
            $this->db->limit($limit,$start);
            return $this->db->get()->result_array();
        

        }


        ///////////////////TRENDING PRODUCTS//////////////////////// 
        public function most_trending($start = 10,$order,$user=null,$limit=6)
        {
           

            $this->db->select('users.user_name,suppliers_name.supplier_name,count(`sharings`.`product_id`) as product_sharing,count(`comments`.`connect_id`) as product_comments,count(`likes`.`connect_id`) as product_likes , `products`.*');
             
            $this->db->order_by($order,'desc');
            $this->db->from('products'); 
            
            $this->db->join('sharings', 'sharings.product_id = products.product_id', 'left');
            $this->db->join('users', 'users.user_id = products.product_id', 'left');
            $this->db->join('suppliers', '`products`.`supplier_note_id` = `suppliers`.`supplier_note_id`', 'left');
            $this->db->join('suppliers_name', '`suppliers_name`.`supplier_id` = `suppliers`.`supplier_id`', 'left');
            $this->db->join('comments', 'comments.connect_id = products.product_id', 'left');
            $this->db->join('likes', 'likes.connect_id = products.product_id', 'left');
            
            $this->db->group_by('products.product_id');
            if ($_SESSION['user_category'] =='shanghai')
            {
                $this->db->where('products.user_id',$_SESSION['user_id']);
            }
            elseif($_SESSION['user_category'] =='buyer')
            {
                if ($user == null)
                {
                    $this->db->join('category_manager', 'category_manager.product_category_id = products.product_cat_id', 'left');
                    //$this->db->where('products.product_id','products.product_id');
                }else
                {
                    if ($user == 'onlyme')
                    {
                        $this->db->where('products.user_id',$_SESSION['user_id']);
                    }else
                    {
                        $this->db->where('products.user_id',$user);
                    } 
                } 
            }
            $this->db->where('products.creation_date > NOW() - INTERVAL 30 DAY');
            $this->db->limit($limit,$start); 
            return $this->db->get()->result_array();
            
    
        }


        ///////////////////NEW PRODUCTS//////////////////////// 
        public function new_products($start = 10,$order,$user=null,$limit=6)
        {
            $this->db->select('users.user_name,suppliers_name.supplier_name,count(`sharings`.`product_id`) as product_sharing,count(`comments`.`connect_id`) as product_comments,count(`likes`.`connect_id`) as product_likes , `products`.*');
                 
            $this->db->order_by($order,'desc');
            $this->db->from('products');  
            $this->db->join('sharings', 'sharings.product_id = products.product_id', 'left');
            $this->db->join('users', 'users.user_id = products.product_id', 'left');
            $this->db->join('suppliers', '`products`.`supplier_note_id` = `suppliers`.`supplier_note_id`', 'left');
            $this->db->join('suppliers_name', '`suppliers_name`.`supplier_id` = `suppliers`.`supplier_id`', 'left');
            $this->db->join('comments', 'comments.connect_id = products.product_id', 'left');
            $this->db->join('likes', 'likes.connect_id = products.product_id', 'left');
            $this->db->group_by('products.product_id');
            if ($_SESSION['user_category'] =='shanghai')
            {
                $this->db->where('products.user_id',$_SESSION['user_id']);
            }
            elseif($_SESSION['user_category'] =='buyer')
            {
                if ($user == null)
                {
                    $this->db->join('category_manager', 'category_manager.product_category_id = products.product_cat_id', 'left');
                    // $this->db->where('products.product_id','`products`.`product_id`');
                }else
                {
                    $this->db->where('products.user_id',$user);
                }  
            }
            
            $this->db->limit($limit,$start); 
            $data =  $this->db->get()->result_array();

            return $data;


        }





    // public function most_commented($limit = 5)
    // {
    //     $this->db->select('count(`sharings`.`product_id`) as product_sharing,count(`comments`.`connect_id`) as product_comments,count(`likes`.`connect_id`) as product_likes , `products`.*');
    //     $this->db->order_by('product_comments','desc');
    //     $this->db->from('products'); 
    //     $this->db->join('sharings', 'sharings.product_id = products.product_id', 'left');
    //     $this->db->join('comments', 'comments.connect_id = products.product_id', 'left');
    //     $this->db->join('likes', 'likes.connect_id = products.product_id', 'left');
    //     $this->db->group_by('products.product_id');
    //     $this->db->limit($limit);
    //     $this->db->where('likes.user_id',$_SESSION['user_id']);
    //     return $this->db->get()->result_array();
    // }
    // public function most_shared($limit = 5)
    // {
    //     $this->db->select('count(`sharings`.`product_id`) as product_sharing,count(`comments`.`connect_id`) as product_comments,count(`likes`.`connect_id`) as product_likes , `products`.*');
    //     $this->db->order_by('product_comments','desc');
    //     $this->db->from('products'); 
    //     $this->db->join('sharings', 'sharings.product_id = products.product_id', 'left');
    //     $this->db->join('comments', 'comments.connect_id = products.product_id', 'left');
    //     $this->db->join('likes', 'likes.connect_id = products.product_id', 'left');
    //     $this->db->group_by('products.product_id');
    //     $this->db->limit($limit);
    //     $this->db->where('likes.user_id',$_SESSION['user_id']);
    //     return $this->db->get()->result_array();
    //    // print_r($data);

    // }
 
    /////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////


    
}
