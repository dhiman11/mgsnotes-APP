<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Images_load extends CI_Model
{
    private function admin_ctrl()
    {
        if ($this->session->userdata('user_type') != 'notes_admin') {
            $this->db->where('`status` = "normal"');
        }
    }

    public function get_images_from_table_and_id($table, $id, $main_photo_id = null)
    {
        $result = [];
        if ($main_photo_id) {
            $this->admin_ctrl();
            $this->db->select('*');
            $this->db->where('connect_table', $table);
            $this->db->where('photo_id', $main_photo_id);
            $this->db->from('photos');
            $result = array_merge($result, $this->db->get()->result_array());
        }
        $this->admin_ctrl();
        $this->db->select('*');
        $this->db->where('connect_table', $table);
        $this->db->where('connect_id', $id);
        if ($main_photo_id) {
            $this->db->where("photo_id != '$main_photo_id'");
        }

        $this->db->from('photos');
        $result = array_merge($result, $this->db->get()->result_array());
        return $result;
    }

    public function get_images_from_photo_id($id, $order = null, $limit = null)
    {
        $this->admin_ctrl();
        $this->db->select('*');
        if (strpos($id, ',')) {
            $arr = explode(',', $id);
            $this->db->where_in('photo_id', $arr);
        } else {
            $this->db->where('photo_id', $id);
        }

        $this->db->from('photos');
        if ($order) {
            $this->db->order_by($order);
        }
        if ($limit) {
            $this->db->limit($limit);
        }
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function count_photos($table, $id)
    {
        $this->admin_ctrl();
        $this->db->where('connect_table', $table);
        $this->db->where('connect_id', $id);
        $this->db->from('photos');
        return $this->db->count_all_results();
    }

    public function move_photos($new_path, $photo_id, $new_table, $new_connect_id)
    {
        if ($new_path == -1 || $new_table == -1 || $new_connect_id == -1) {
            return $this->db->delete('photos', 'photo_id  IN (' . $photo_id . ')');
        } else {
            $this->db->set('status', 'normal');
            $this->db->set('path', $new_path);
            $this->db->set('connect_table', $new_table);
            $this->db->set('connect_id', $new_connect_id);
        }
        $this->db->set('update_date', date("Y-m-d H:i:s"));
        $this->db->where('photo_id  IN (' . $photo_id . ')');
        $query = $this->db->update('photos'); // gives UPDATE `mytable` SET `field` = 'field+1' WHERE `id` = 2

    }

    public function process_images($one_something, $table_name, $id_name, $limit = 1)
    {
        
        if (array_key_exists('main_photo_id', $one_something) && $one_something['main_photo_id'] && $limit == 1) {
            $data = $this->get_images_from_photo_id($one_something['main_photo_id']);
           

        } else {

            ///////////////////////
            if ($limit) { 
                $data = $this->get_images_from_table_and_id($table_name, $one_something[$id_name], $one_something['main_photo_id']);
            } else {
                $data = $this->get_images_from_table_and_id($table_name, $one_something[$id_name], $one_something['main_photo_id']);
            }
            ////////////////////////
        }
        
        

        
        if (!empty($data)) {

            $count = count($data);
            for ($i = 0; $i < $count; $i++) {
                $path = substr($data[$i]['path'], 9) . $data[$i]['file_name']; 

                if ($path[0] == '/') {  
                }

                ///////////////////////////////
                //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@/
                /////////////////////////////
               

                if (file_exists(IMAGEPATH.$path)) {

                    $src =  IMAGEURL.$path;

                } else {
                    $src = base_url('assets/img/noimage.png');
                }

                ///////////////////////////////
                //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@/
                /////////////////////////////

                
                $data[$i]['src'] = $src;
            }
        } else {
            $data = [array(
                'file_name' => 'noimage.png',
                'path' => '../assets/img/',
                'photo_id' => -1,
                'orientation' => 0,
                'src' => base_url('assets/img/noimage.png'),
            )];
        }
        if ($limit == 1) {
            $data = $data[0];
        }
        return $data;
    }
}
