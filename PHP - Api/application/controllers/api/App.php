<?php
defined('BASEPATH') or exit('No direct script access allowed');

class App extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->app_keys = array(
            '2d267d10556d4d1f28da2666ba9bd8ab405d3c119369d8434969b81f0d350b577f57edce5a10e402db551cdb075b4159c4003fe2e0e951e5ee7909290cd52a83' =>
            '#wIlF@pbeR2TNT0TROpQzKHqp29Y8HQWfgOD5z6VsJu%EJ*r$x&6PLilfXbIrO3UXTO*!!OPLnH!GsDX',

        );
        $this->load->model('product_data/Product_list');
        $this->load->model('images_data/Images_load');
        $this->load->model('Likes');
        $this->load->model('Comments');
        $this->load->model('dashboard/Todo');
    }

    private function output_json($return_data)
    {
        header('Content-type: application/json');
        echo json_encode($return_data);
    }

    private function return_error($error)
    {
        $this->output_json(array('is_error' => true, 'data' => $error));
        exit();
    }
    private function key_ctrl($key = null, $secret = null)
    {
        if (array_key_exists('logged_in', $_SESSION) && $_SESSION['logged_in']) {
            session_write_close();
            return session_id();
        }
        if (!$key) {
            $key = $this->input->get('key');
        }
        if (!$secret) {
            $secret = $this->input->get('secret');
        }

        if (array_key_exists($key, $this->app_keys)) {
            $prop_key = $this->app_keys[$key];
            $len_prop = strlen($prop_key);
            $len_give = strlen($secret);
            if ($len_give != $len_prop) {
                $secret = str_repeat('x', $len_prop);
            }
            $is_right = true;
            // Prevent the time attack
            // Normally, if we use === to two string, when a character not match, it will just return
            // Some attacker use the return time to guesse the password, this will be much more easier.
            for ($i = 0; $i < $len_prop; $i++) {
                if ($prop_key[$i] === $secret[$i]) {
                    $is_right &= true;
                } else {
                    $is_right &= false;
                }
            }
            if (!$is_right) {
                $this->return_error('403 Forbidden');
            }
        } else {
            $this->return_error('403 Forbidden');
        }
        $data = array(
            'user_id' => '-1',
            'user_name' => 'APP API',
            'user_type' => 'notes_admin',
            'tester' => true,
            'user_category' => 'APP',
            'email' => false,
            'logged_in' => true,
        );
        $this->session->set_userdata($data);
        session_write_close();
        return session_id();
    }

    private function process_allpro(&$all_pros)
    {
        $i = 0;
        foreach ($all_pros as $var) {
            $one_data = [];

            $images_data = $this->Images_load->process_images($var, 'products', 'product_id', false);
            $one_data['images'] = $images_data;
            $one_data['total_photos'] = count($images_data);
            // For like and dislike
            $one_data['like_count'] = $this->Likes->count_like_dislike('LIKE', $var['product_id'], 'products');
            $one_data['dislike_count'] = $this->Likes->count_like_dislike('DISLIKE', $var['product_id'], 'products');

            // For share info
            $one_data['share_info'] = $this->Product_list->get_shared_count($var['product_id'], true);

            // For comment
            $one_data['comment_count'] = $this->Comments->get_num_comment_by_id('products', $var['product_id']);
            $all_pros[$i] = array_merge($all_pros[$i], $one_data);
            $i++;
        }
    }

    public function get_recent_products()
    {
        $session = $this->key_ctrl();
        $return_data = array(
            'is_error' => false,
            'session_id' => $session,
            'data' => null,
        );
        $limit = $this->input->get('limit', true);
        $pdata = $this->Product_list->get_recent_products($limit);
        $this->process_allpro($pdata);
        $return_data['data'] = &$pdata;
        $this->output_json($return_data);
    }

    public function get_random_products()
    {
        $session = $this->key_ctrl();
        $return_data = array(
            'is_error' => false,
            'session_id' => $session,
            'data' => null,
        );
        $limit = $this->input->get('limit', true);
        $pdata = $this->Product_list->get_random_products($limit);
        $this->process_allpro($pdata);
        $return_data['data'] = &$pdata;
        $this->output_json($return_data);
    }

    public function get_performance()
    {
        $session = $this->key_ctrl();
        $return_data = array(
            'is_error' => false,
            'session_id' => $session,
            'data' => null,
        );
        $return_data['data'] = $this->Todo->todo_datatable()['data'];
        $this->output_json($return_data);
    }
}
