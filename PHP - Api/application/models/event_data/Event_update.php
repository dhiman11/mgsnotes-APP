<?php
defined('BASEPATH') or exit('No direct script access allowed');

include_once './application/libraries/Update_model.php';

class Event_update extends Update_model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function event_update($event_id, $data)
    {
        $this->admin_ctrl(null, 'events');
        return $this->update('events', 'event_id', $event_id, $data);
    }
}
