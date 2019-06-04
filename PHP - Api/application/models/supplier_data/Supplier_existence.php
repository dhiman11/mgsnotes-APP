<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Supplier_existence extends CI_Model
{
    //////////////////////////////UPLOAD Supplier If not existed/(Table : suppliers_name ) ////////////////
    public function supp_code($suppliername, $supp_count,$user_id,$event_id)
    {
        if ($supp_count == 0) {
            $data = array(
                'supplier_name' => $suppliername,
                'status' => 'normal',
                'creation_date' => date('Y-m-d H:i:s'),
                'user_id' => $user_id,
            );

            $this->db->insert('suppliers_name', $data);
            $last_supplier_id = $this->db->insert_id();

            ////////////////////////////////////////////////////
            // Create supplier NOte id ,Note (Table : suppliers )
            ////////////////////////////////////////////////////
            $suppliers_data = array(
                'supplier_id' => $last_supplier_id,
                'event_id' => $event_id,
                'status' => 'normal',
                'creation_date' => date('Y-m-d H:i:s'),
                'update_date' => date('Y-m-d H:i:s'),
                'user_id' => $user_id,
            );

            $this->db->insert('suppliers', $suppliers_data);
            $last_supplier_note_id = $this->db->insert_id();
            return $last_supplier_note_id;
        } else {
            $this->db->select('supplier_note_id');
            $this->db->from('suppliers');
            $this->db->where('suppliers_name.supplier_name', $suppliername);
            $this->db->where('suppliers.user_id', $user_id);
            $this->db->where('suppliers.event_id', $event_id);
            $this->db->join('suppliers_name', 'suppliers_name.supplier_id = suppliers.supplier_id', 'LEFT');
            $this->db->limit(1);
            $query = $this->db->get();
            $data = $query->result_array();

            if (count($data) > 0) {
                return $data[0]['supplier_note_id'];
            } else {
                $this->db->select('supplier_id');
                $this->db->from('suppliers_name');
                $this->db->where('supplier_name', $suppliername);
                $this->db->limit(1);
                $supplier_id = $this->db->get()->result_array()[0]['supplier_id'];

                $suppliers_data = array(
                    'supplier_id' => $supplier_id,
                    'event_id' => $event_id,
                    'status' => 'normal',
                    'creation_date' => date('Y-m-d H:i:s'),
                    'update_date' => date('Y-m-d H:i:s'),
                    'user_id' => $user_id,
                );

                $this->db->insert('suppliers', $suppliers_data);
                $last_supplier_note_id = $this->db->insert_id();
                return $last_supplier_note_id;
            }
        }
    }

    // Creat new supplier name, if exist, return the supplier id.
    public function creat_new_supplier_name($supplier_name, $user_id = null)
    {
        $this->db->select('supplier_id');
        $this->db->from('suppliers_name');
        $this->db->where('supplier_name', $supplier_name);
        $this->db->limit(1);
        $count_data = $this->db->get()->result_array();
        if (count($count_data) != 0) {
            return $count_data[0]['supplier_id'];
        } else {
            $data = array(
                'supplier_name' => $supplier_name,
                'status' => 'normal',
                'creation_date' => date('Y-m-d H:i:s'),
                'user_id' => $user_id ? $user_id : $this->session->userdata('user_id'),
            );

            $this->db->insert('suppliers_name', $data);
            return $this->db->insert_id();
        }
    }

}
