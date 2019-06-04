<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Products_list extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getProductsData($data)
    {
        $limit = $data['limit'];

        $field_name = $data['field_name'];
        $field_order = $data['field_order'];

        $event_id = $data['event_id'];

        $min_usd = $data['min_usd'];
        $max_usd = $data['max_usd'];

        $min_date = $data['min_date'];
        $max_date = $data['max_date'];

        $search_type = $data['search_type'];
        $search_by_user = $data['search_by_user'];

        $search_input = $data['search_input'];

        // Build the query

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

        $QueryP = 'SELECT products.product_id,products.product_name,suppliers.supplier_note_id,products.supplier_reference,products.fob_price ,products.moq,products.note,suppliers_name.supplier_name  FROM `products`
		LEFT JOIN suppliers on products.supplier_note_id = suppliers.supplier_note_id
		LEFT JOIN suppliers_name on suppliers_name.supplier_id = suppliers.supplier_id
		LEFT JOIN events on events.event_id = suppliers.event_id
		' . $where . '  ' . $by_user . '  ' . $min_max_usd . '  ' . $min_max_date . ' ' . $search_here . '
		ORDER BY ' . $field_name . ' ' . $field_order;
        $limit = ' LIMIT ' . $limit . ',100';

        $QueryP2 = $QueryP . $limit;

        // Query
        $products_data = $this->db->query($QueryP2)->result_array();
        $count_total = $this->db->query($QueryP);
        $totalCount = $count_total->num_rows();

        $return_data = array('data' => $products_data, 'count' => $totalCount);
        return $return_data;
    }
}
