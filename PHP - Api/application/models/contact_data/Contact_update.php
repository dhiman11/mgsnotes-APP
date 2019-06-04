<?php
defined('BASEPATH') or exit('No direct script access allowed');

include_once './application/libraries/Update_model.php';

class Contact_update extends Update_model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function contact_update($contact_id, $data)
    {
        $this->admin_ctrl(null, 'contacts');
        return $this->update('contacts', 'contact_id', $contact_id, $data);
    }
}
