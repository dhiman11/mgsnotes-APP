<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Main_image_set extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('images_data/Images_load');
    }

    public function set_main_image($type, $key, $id, $main_image_id)
    {
        $data = $this->Images_load->get_images_from_photo_id($main_image_id);
        if (count($data) > 0 &&
            is_numeric($id) &&
            is_numeric($main_image_id) &&
            (
                $type == 'suppliers' ||
                $type == 'contacts' ||
                $type == 'products'
            )) {
            $update_data = array(
                'main_photo_id' => $main_image_id,
            );
            return $this->db->update($type, $update_data, "`$key`='$id'");
        }
        return false;
    }
}
