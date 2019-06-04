<?php
defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(E_ALL ^ E_NOTICE);

class Email_function extends CI_Controller
{
    private $filename_record = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->library('email');
        $this->load->helper('security');
        $this->load->model('Users');
        $this->load->model('event_data/Event_list');
        $this->load->model('supplier_data/Supplier_list');
        $this->load->model('product_data/Product_list');
        $this->load->model('contact_data/Contact_list');
        $this->load->model('images_data/Images_load');
        $this->user_email = null;
    }

    /////////////////////////////////////////////////////////////////////
    /////////// Check if login or not **********************************
    private function user_check()
    {
        if ($this->session->userdata('logged_in') != 1) {
            exit('404 ERROR NOT FOUND');
        }
    }

    /////////////////////////////////////////////////////////////////////
    ////////////////// Email Configuration ******************************
    private function init_email($n_pwd = null, $send_from = null)
    {
        $config['mailpath'] = '/usr/sbin/sendmail';
        $user_email = $this->session->userdata('email');
        $pwd = $this->session->userdata('pwd');
        if ($user_email) {
            if (!$pwd) {
                if ($n_pwd && !empty($n_pwd)) {
                    $pwd = $n_pwd;
                } else {
                    $pwd = false;
                }
            }
        }
        if ($user_email && $pwd && $send_from == '1_email') {
            $this->user_email = $user_email;
            $config['protocol'] = 'smtp';
            $config['smtp_host'] = 'smtpw.263.net';
            $config['smtp_user'] = $user_email;
            $config['smtp_pass'] = $pwd;
            $config['smtp_port'] = 465;
            $config['smtp_crypto'] = 'ssl';
            $config['newline'] = "\r\n";
            $config['crlf'] = "\r\n";
        } else {
            $config['protocol'] = 'smtp';
            $config['smtp_host'] = 'cp-hk-4.webhostbox.net';
            $config['smtp_user'] = 'no_reply@mgsnotes.com';
            $config['smtp_pass'] = 'nGgnea&,s&3(XZ_~65bgV2!4K!=bzH9+6P7y';
            $config['smtp_port'] = 465;
            $config['smtp_crypto'] = 'ssl';
        }
        $config['charset'] = 'utf-8';
        $config['mailtype'] = 'html';
        $config['validate'] = true;

        $this->email->initialize($config);
    }

    /////////////////////////////////////////////////////////////////////
    ////////// Product Html inside email ********************************
    private function one_product($id, $hide_price = null, $hide_supplier = null, $links_back = null)
    {
        $one_product = $this->Product_list->get_product_for_history_by_product_id($id)[0];
        $j = md5(uniqid(rand(20, 200), true));
        $one_data = [];
        ////////////////////////////////////////
        $one_data['j'] = $j;
        $images_data = $this->Images_load->process_images($one_product, 'products', 'product_id');
        ////////////////////////////////////////
        $src = $images_data['src'];

        if (!array_key_exists($src, $this->filename_record)) {
            $this->filename_record[$src] = true;
            // Process the image
            if ($images_data['orientation'] % 360 != 0) {
                $rotate = imagerotate(imagecreatefromjpeg($src), -$images_data['orientation'], 0);
                $src = './temp_image/' . $images_data['file_name'];
                imagejpeg($rotate, $src, 100);
                $images_data['orientation'] = 0;
            }
            $this->email->attach($src, 'inline');
        }

        $cid = $this->email->attachment_cid($src);
        $images_data['src'] = 'cid:' . $cid;
        $one_data['images'] = $images_data;
        $one_data['hide_price'] = $hide_price;
        $one_data['hide_supplier'] = $hide_supplier;
        $one_data['links_back'] = $links_back;
        $one_data['mysqlarrayP'] = $one_product;
        return $this->load->view("pro_list/one_pro_view", $one_data, true);
    }

    public function account_send($type)
    {
        $raw = $this->input->raw_input_stream;
        $json_array = json_decode($raw, true);
        echo $raw;
        $send_to = 'cedric@marjanegs.com.cn';
        $data = array(
            'type' => $type,
            'info' => $json_array['info'],
        );
        $this->init_email();
        $this->email->from('test@mgsnotes.com', "MGSNotes system");

        $this->email->subject("[MGSNotes] You have a new $type request");
        $page = '<html><head></head><body>';
        $page .= $this->load->view('email/account', $data, true);
        $page .= '</body></html>';

        $this->email->message($page);

        $this->email->to($send_to);
        $is_ok = false;
        $count = 0;
        while (!$is_ok && $count < 3) {
            $is_ok = $this->email->send(false);
            $count++;
        }
    }

    /////////////////////////////////////////////////////////////////////
    ///// FORMAT BYTES TO MB OR KB **************************************
    public function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('b', 'Kb', 'Mb', 'Gb', 'Tb');

        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }

    /////////////////////////////////////////////////////////////////////
    ///// Choose attachment and save in temporary folder ****************
    public function attachments()
    {

        if (!empty($_FILES["attachment1"]["name"])) {

            if (!file_exists('temp_files/' . $_SESSION['user_id'])) {
                mkdir('temp_files/' . $_SESSION['user_id'], 0777, true);
            }

            foreach ($_FILES['attachment1']['name'] as $key => $value) {
                $filedata = pathinfo($_FILES["attachment1"]["name"][$key]);
                $filesizeb = filesize($_FILES["attachment1"]["tmp_name"][$key]);
                $filesize = $this->formatBytes($filesizeb);

                if ($filesizeb < 10000000) {
                    copy($_FILES["attachment1"]["tmp_name"][$key], 'temp_files/' . $_SESSION['user_id'] . '/' . str_replace(' ', '', $filedata['filename']) . '.' . $filedata['extension']);

                    echo '<div class="attachment_0" style="font-size: 10px;"><input type="hidden" class="attachments009990" name="attachment_input[]" value="' . $filedata['filename'] . "." . $filedata['extension'] . '"><i class="fa fa-file" aria-hidden="true"></i> ' . $filedata['filename'] . "." . $filedata['extension'] . ' | ' . $filesize . "
							<a style='color: red;' href='#' class='delete_attachment' aria-hidden='true'>[×]</a>
							 <br></div>";
                } else {
                    echo '<div class="attachment_0" style="font-size: 10px;">' . $filedata['filename'] . "." . $filedata['extension'] . ' --' . $filesize . "<span style='color: red;'>[ ATTACHMENT FAILED. SIZE EXCEED LIMIT OF 10 MB] </span>
							<a style='color: red;' href='#' class='delete_attachment' aria-hidden='true'>[×]</a>
							<br> </div>";
                }

            }
        }

    }
    /////////////////////////////////////////////////////////////////////
    //// SEND EMAIL ****************************************************
    public function send()
    {
        $this->user_check();
        $raw = $this->input->raw_input_stream;
        $json_array = json_decode($raw, true);
        $return_data = array('is_error' => true, 'error_info' => 'Mail send error, please check the password or contact the administrator');

        try {
            $send_to = $json_array['send_to'];
            $message = $json_array['message'];
            ///////////////////////////////////////
            ///////////////////////////////////////
            if ($json_array['hide_price']) {
                $hide_price = $json_array['hide_price'];
            }
            //************************************
            //************************************
            if ($json_array['hide_supplier']) {
                $hide_supplier = $json_array['hide_supplier'];
            }
            //************************************
            //************************************
            if ($json_array['links_back']) {
                $links_back = $json_array['links_back'];
            }

            //////////////////////////////////////
            //////////////////////////////////////
            $attachments_array = $json_array['attachments_array'];
            $product_list = $json_array['product_list'];
            $send_from = $json_array['sfrom'];

            $user_name = $this->session->userdata('user_name');
            /////////////////////////////////////////////////////////////////////
            //// Configure Email  ****************************************************
            $this->init_email($json_array['pwd'], $send_from);

            $user_email = $this->session->userdata('email');

            if ($send_from == '1_email') {
                $this->email->from($user_email, "$user_name (MGSNotes)");
            } else {
                $this->email->from('no_reply@mgsnotes.com', "MGSNotes($user_name)");
            }

            $this->email->subject("[MGSNotes] $user_name has sent you some products you may interested in");
            /////////////////////////////////////////////////////////////////////
            $page = '<html><head>';
            $page .= $this->load->view('pro_list/one_pro_css', '', true);
            $page .= '</head><body>';
            $page .= $this->load->view(
                'email/header',
                array(
                    'username' => $this->session->userdata('user_name'),
                    'message' => $this->security->xss_clean($message),
                    'count' => count($product_list),
                ),
                true
            );

            /////////////////////////////////////////////////////////////////////
            //// SAVE SEND EMAILS RECORD INSIDE THE DATABASE/////////////////////
            $this->Product_list->record_share_product($product_list, $send_to);
            /////////////////////////////////////////////////////////////////////
            /////////////////////////////////////////////////////////////////////

            foreach ($product_list as $pid) {
                $page .= $this->one_product($pid, $hide_price, $hide_supplier, $links_back);
            }
            $page .= $this->load->view('email/footer', array('username' => $this->session->userdata('user_name')), true);
            $page .= '</body></html>';

            $this->email->message($page);

            ////////////////ATTACHMENTS HERE /////////////////////////////////////
            //////////////////////////////////////////////////////////////////////
            foreach ($attachments_array as $value) {
                $this->email->attach(base_url() . 'temp_files/' . $_SESSION['user_id'] . '/' . str_replace(' ', '', $value));
            }
            /////////////////////////////////////////////////////////////////////
            /////////////////////////////////////////////////////////////////////

            $status = [];
            $all_count = 1;
            $ok_count = 0;

            if ($this->user_email) {
                $this->email->cc($this->user_email);
            }

            // Send email
            $this->email->to($send_to);
            $count = 0;
            $is_ok = false;
            while (!$is_ok && $count < 3) {
                $is_ok = $this->email->send(false);
                $count++;
            }
            if ($is_ok) {
                $ok_count++;
                $status = true;
            } else {
                $status = $this->email->print_debugger(array('headers'));
            }

            ////REMOVE TEMPORARY FILES AFTER EMAIL ////
            system('rm ./temp_image/*');
            system('rm ./temp_files/' . $_SESSION['user_id'] . '/*');

            //////////////////////////////////////////////

            /////////////////////////////////////////////
            if ($all_count == $ok_count) {
                $return_data['is_error'] = false;
                $return_data['error_info'] = 'Mail sent';
                if ($json_array['pwd']) {
                    $this->session->set_userdata(array('pwd' => $json_array['pwd']));
                }
            } else {
                if ($ok_count > 0) {
                    $return_data['is_error'] = true;
                    $return_data['error_info'] = 'Part sent';
                    if ($json_array['pwd']) {
                        $this->session->set_userdata(array('pwd' => $json_array['pwd']));
                    }
                }
            }
            $return_data['detail_result'] = $status;
            $return_data['success_count'] = $ok_count;
            $return_data['all_count'] = $all_count;
        } catch (Exception $e) {
            $return_data['error_info'] = 'Network error';
        }

        header('Content-type: application/json');
        echo json_encode($return_data);
    }
}
