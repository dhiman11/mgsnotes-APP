<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Images extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    // Input: tablename, id
    // Output: Array('count'=>10, 'data'=>Array())
    public function get_images_by_table_name_and_id($table = null, $id = null)
    {
        if ($table != null) {
            if ($id != null) {
                $st_query = "SELECT * FROM `photos` WHERE  `connect_table` = '" . $table . "' &&   `connect_id` = '" . $id . "' ORDER BY `photo_id` ASC ";
            } else {
                $st_query = "SELECT * FROM `photos` WHERE  `connect_table` = '" . $table . "' ORDER BY `photo_id` ASC ";
            }

            $q = $this->db->query($st_query);
            $data = $q->result_array();
            $count = $q->num_rows();

            return array('count' => $count, 'data' => $data);
        }
        return null;
    }

    // Input: photo_id, rotate degree
    // Output: boolean
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
