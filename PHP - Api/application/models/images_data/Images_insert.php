<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Images_insert extends CI_Model
{
    // $table_name =  Name of table
    // $contact_id = Connect ID
    // $path_of_image = Path of the images
    // $images = Images array of base64()

    public function insert_images($table_name, $contact_id, $path_of_image, $images, $images_rotate = null,$user_id,$user_name)
    {
        $i = 1;
        if ($images_rotate) {
            $count_rotate = count($images_rotate);
        }
        foreach ($images as $value) {
            $img = $value;
            $img = str_replace('data:image/jpeg;base64,', '', $img);
            $data = base64_decode($img);

            $filename = $user_name . "_" . date('ymd') . "_" . date("his") . "_00" . $i . '.jpg';
            $fileformat = 'jpg';
            //*/*/*/*/*/*/*///*/*/*/*/*/*/*///*/*/*/*/*/*/*/
            $filepath = substr($path_of_image, 9);

         
            //*/*/*/*/*/*/*///*/*/*/*/*/*/*///*/*/*/*/*/*/*/
            $filename = $user_name . "_" . date('ymd') . "_" . date("his") . "_00" . $i . '.jpg';
            $success = file_put_contents(IMAGEPATH.$filepath. $filename, $data);

            ////////////////////////////////////
            //////INSERT IMAGES////////////////
            $ori = 0;
            if ($images_rotate) {
                if ($i <= $count_rotate) {
                    $ori = $images_rotate[$i - 1];
                }
            }
            $data = array(
                'file_name' => $filename,
                'file_format' => $fileformat,
                'path' => $path_of_image,
                'connect_table' => $table_name,
                'connect_id' => $contact_id,
                'status' => 'normal',
                'update_date' => date('Y-m-d H:i:s'),
                'creation_date' => date('Y-m-d H:i:s'),
                'user_id' => $user_id,
                'orientation' => $ori,
            );

           $this->db->insert('photos', $data);
            $i++;
        }
    }
}
