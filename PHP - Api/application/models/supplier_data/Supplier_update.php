<?php
defined('BASEPATH') or exit('No direct script access allowed');

include_once './application/libraries/Update_model.php';

class Supplier_update extends Update_model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function supplier_update($supplier_note_id, $data)
    {
        $this->admin_ctrl(null, 'suppliers');

        $this->db->where("suppliers.supplier_note_id = '$supplier_note_id'");
        $this->db->where('suppliers_name.supplier_id = suppliers.supplier_id');
        return $this->db->update('`suppliers`, `suppliers_name`', $data, null, null, false);

    }
}
