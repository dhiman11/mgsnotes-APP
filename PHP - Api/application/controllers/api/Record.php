<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Record extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('audio_data/Audio');
    }

    public function get_js($file_name)
    {
        $arr = explode('.', $file_name);
        $l_arr = count($arr) - 1;
        if ($arr[$l_arr] === 'js') {
            $header = 'Content-type: application/javascript';
        } elseif ($arr[$l_arr] = 'wasm') {
            $header = 'Content-type: application/wasm';
        } else {
            show_404();
        }
        $fpath = "./assets/ogv_lib/$file_name";
        if (file_exists($fpath)) {
            header($header);
            $fp = fopen($fpath, "r");
            $filesize = filesize($fpath);
            $buffer = 1024;
            $buffer_count = 0;
            while (!feof($fp) && $filesize - $buffer_count > 0) {
                $data = fread($fp, $buffer);
                $buffer_count += $buffer;
                echo $data;
            }
            fclose($fp);
        } else {
            show_404();
        }
    }

    public function record_test()
    {
        // $this->load->view('record/record');
        echo json_encode($this->Audio->get_audio('contacts', 1727));
    }

    public function record_upload()
    {
        $i = 0;
        $type = $this->input->post('type', true);
        $no = $this->input->post('audio_no', true);
        $upload_key = $this->input->post('key', true);
        $upload_key = str_replace('.', '', $upload_key);
        $upload_key = str_replace('/', '', $upload_key);
        $random_str = '';
        for ($i = 0; $i < 80; $i++) {
            $random_str .= chr(mt_rand(33, 126));
        }
        $return_data = [];
        if (!is_dir("./temp_audios/$upload_key")) {
            mkdir("./temp_audios/$upload_key/", 0666);
        }
        foreach ($_FILES["data"] as $key => $value) {
            $audio_id = sha1($random_str . "-" . date('Ymd') . "-" . date("His") . "-00" . $i);
            $filename = $audio_id . ".mp3";
            if (move_uploaded_file($_FILES["data"]["tmp_name"], "./temp_audios/$upload_key/" . $filename)) {
                array_push($return_data, array(
                    'file_name' => $filename,
                    'id_on_page' => 'record_' . $no,
                    'type' => $type,
                ));
            }
            $i++;
        }
        header('Content-type: application/json');
        echo json_encode(array("data" => $return_data));
    }

    public function delete_audios($key)
    {
        $key = str_replace('.', '', $key);
        $key = str_replace('/', '', $key);
        system("rm -r ./temp_audios/$key");
    }
}
