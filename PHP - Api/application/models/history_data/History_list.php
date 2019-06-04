<?php
defined('BASEPATH') or exit('No direct script access allowed');

class History_list extends CI_Model
{

    public function recent_history($limit, $user_id=1)
    {
        ///////////////////////////////////////////////
        //////////////////////////////////////////////
        // if ($this->session->userdata('user_type') == 'notes_admin' && $user_id) {
        //     $user_id = $user_id;
        // } else {
        //     $user_id = $this->session->userdata('user_id');
        // }
        ///////////////////////////////////////////////
        //////////////////////////////////////////////
        if ($limit) {
            $limit = $limit;
        } else {
            $limit = '0';
        }
        ////////////////////////////////////////////////
        ////////////////////////////////////////////////

        $query_val = "SELECT creation_date AS A, event_id ,'event' FROM events
        WHERE user_id IN (" . $user_id . ") UNION SELECT creation_date AS A, supplier_note_id ,'supplier' FROM suppliers
        WHERE user_id IN (" . $user_id . ") UNION SELECT creation_date AS A, contact_id ,'contact' FROM contacts
        WHERE user_id IN (" . $user_id . ") UNION SELECT creation_date AS A, product_id ,'product' FROM products
        WHERE user_id IN (" . $user_id . ") ORDER BY `A` DESC LIMIT " . $limit . ",20";

        $query = $this->db->query($query_val);

        $result = $query->result();
        return $result;

    }

}
