<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Rotate extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Images');
    }

    public function rotate_image()
    {
        $id = $this->input->post_get('photo_id', false);
        $deg = $this->input->post_get('rotate_deg', false);

        if (!$this->Images->rotate_image($id, $deg)) {
            show_error('Error', 500);
        }
    }
}
