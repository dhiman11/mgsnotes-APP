<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Audio extends CI_Model
{
    public function insert_audio($file_name, $key, $type, $id)
    {
        // DELETE THIS RETURN TO RESUM THE FUNCTION
        return [];
        // DELETE THIS RETURN TO RESUM THE FUNCTION
        $fd = 'supplier_contact';
        $table_name = 'contacts';
        if ($type == 'product') {
            $fd = 'supplier_product';
            $table_name = 'products';
        }
        echo $file_name;
        copy(FCPATH . "temp_audios/$key/$file_name", FCPATH . "0_data/$fd/audios/$file_name");
        $data = array(
            'file_name' => $file_name,
            'file_format' => 'mp3',
            'path' => "./0_data/$fd/audios/",
            'connect_table' => $table_name,
            'connect_id' => $id,
            'status' => 'normal',
            'update_date' => date('Y-m-d H:i:s'),
            'creation_date' => date('Y-m-d H:i:s'),
            'user_id' => $this->session->userdata('user_id'),
        );

        $this->db->insert('audio_records', $data);
    }

    public function get_audio($type, $id, $single = false)
    {
        // DELETE THIS RETURN TO RESUM THE FUNCTION
        if ($single) {
            return array('title' => 'DELETED', 'mp3' => '/DELETED.MP3');
        }
        return [];
        // DELETE THIS RETURN TO RESUM THE FUNCTION
        $this->db->select('*');
        $this->db->from('audio_records');
        $this->db->where('connect_table', $type);
        $this->db->where('connect_id', $id);
        if ($_SESSION['user_type'] != 'notes_admin') {
            $this->db->where('user_id', $_SESSION['user_id']);
        }
        $data = $this->db->get()->result_array();

        $return_data = [];
        foreach ($data as $audio) {
            $file = $audio['path'] . $audio['file_name'];
            if (file_exists($file)) {
                $one_data = array(
                    'title' => $audio['creation_date'],
                    'mp3' => base_url() . substr($file, 2),
                );
                if ($single) {
                    return $one_data;
                }
                array_push($return_data, $one_data);
            }
        }
        return $return_data;
    }
}
