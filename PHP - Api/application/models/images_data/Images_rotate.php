<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Images_rotate extends CI_Model
{
    public function rotate_image($id, $deg)
    {
        if (is_numeric($id) && is_numeric($deg) && $deg >= 0 && $deg < 360) {
            $update_data = array(
                'orientation' => $deg,
            );
            return $this->db->update('photos', $update_data, "`photo_id`='$id'");
        }
        return false;
    }
}
