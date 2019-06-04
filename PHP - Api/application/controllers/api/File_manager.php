<?php
defined('BASEPATH') or exit('No direct script access allowed');

class File_manager extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Users');
        $this->load->model('event_data/Event_list');
        $this->load->model('supplier_data/Supplier_list');
        $this->load->model('images_data/Images_load');
        $this->load->model('contact_data/Contact_list');
        $this->load->model('product_data/Product_list');
    }

    private function get_json()
    {
        return json_decode($this->input->raw_input_stream, true);
    }

    private function output($data)
    {
        header('Content-type: application/json');
        echo json_encode($data);
    }

    private function is_admin()
    {
        return $_SESSION['user_type'] == 'notes_admin';
    }

    public function handler()
    {
        $get_array = $this->input->get();
        if (count($get_array) == 0) {
            $data = $this->get_json();
        } else {
            $data = $get_array;
        }
        $action = $data['action'];
        switch ($action) {
            case 'list':
                $this->_list($data['path']);
                break;
            case 'download':
                $this->_download($data['path']);
                break;
            case 'move':
                $this->_move($data['items'], $data['newPath']);
                break;
            case 'remove':
                $this->_remove($data['items']);
                break;
            default:
                break;
        }
    }

    private function _download($path)
    {
        $file_name = explode('/', $path);
        $file_name = array_pop($file_name);

        $photo_id = explode('#', $file_name)[0];
        $data = $this->Images_load->get_images_from_photo_id($photo_id)[0];
        $file = substr($data['path'], 3) . $data['file_name'];
        if (file_exists($file)) {
            $src = base_url() . $file;
        } else {
            $src = base_url('assets/img/noimage.jpg');
        }
        header("location:$src");
    }

    private function _list($path)
    {
        if ($path == '/') {
            if ($this->is_admin()) {
                $users = $this->Users->get_user_name_list();
            } else {
                $users = array(
                    array(
                        'user_name' => $_SESSION['user_name'],
                        'user_id' => $_SESSION['user_id'],
                    ),
                );
            }
            session_write_close();

            $return_data = array('result' => array());
            foreach ($users as $user) {
                array_push($return_data['result'], array(
                    'name' => $user['user_name'] . ' #' . $user['user_id'],
                    'name_disp' => $user['user_name'],
                    'rights' => 'drw-rw-rw-',
                    'size' => '0',
                    'date' => '2018-01-01 10:10:10',
                    'type' => 'dir',
                ));
            }
            $this->output($return_data);
            return;
        }

        $path = explode('/', $path);
        $path_count = count($path);
        $return_data = array('result' => array());
        /**
         * 2 -> user
         * 3 -> event
         * 4 -> supplier + photos
         * 5 -> contact list/product list
         * 6 -> list of contact/product + photo
         */
        switch ($path_count) {
            case 2:
                $user_id = explode('#', $path[1]);
                $user_id = array_pop($user_id);

                $data = $this->Event_list->get_event_list_user_id($user_id);
                foreach ($data as $event) {
                    array_push($return_data['result'], array(
                        'name' => $event['event_name'] . ' #' . $event['event_id'],
                        'name_disp' => $event['event_name'],
                        'rights' => 'drw-rw-rw-',
                        'size' => '0',
                        'date' => $event['creation_date'],
                        'type' => 'dir',
                    ));
                }
                break;
            case 3:
                $user_id = explode('#', $path[1]);
                $user_id = array_pop($user_id);
                $event_id = explode('#', $path[2]);
                $event_id = array_pop($event_id);

                $data = $this->Supplier_list->get_supplier_list_from_event_id($event_id);
                foreach ($data as $supplier) {
                    array_push($return_data['result'], array(
                        'name' => $supplier['supplier_name'] . ' #' . $supplier['supplier_note_id'],
                        'name_disp' => $supplier['supplier_name'],
                        'rights' => 'drw-rw-rw-',
                        'size' => '0',
                        'date' => $supplier['creation_date'],
                        'type' => 'dir',
                    ));
                }
                break;
            case 4:
                $supplier_note_id = explode('#', $path[3]);
                $supplier_note_id = array_pop($supplier_note_id);

                $data = $this->Images_load->get_images_from_table_and_id('suppliers', $supplier_note_id);

                foreach ($data as $photo) {
                    array_push($return_data['result'], array(
                        'name' => $photo['photo_id'] . '# ' . $photo['file_name'],
                        'name_disp' => $photo['file_name'],
                        'rights' => '-rw-rw-rw-',
                        'size' => '1024',
                        'date' => $photo['creation_date'],
                        'type' => 'file',
                    ));
                }
                array_push($return_data['result'], array(
                    'name' => 'Contacts',
                    'name_disp' => 'Contacts',
                    'rights' => 'drw-rw-rw-',
                    'size' => '0',
                    'date' => '0000-00-00 00:00:00',
                    'type' => 'dir',
                ));
                array_push($return_data['result'], array(
                    'name' => 'Products',
                    'name_disp' => 'Products',
                    'rights' => 'drw-rw-rw-',
                    'size' => '0',
                    'date' => '0000-00-00 00:00:00',
                    'type' => 'dir',
                ));
                break;
            case 5:
                $supplier_note_id = explode('#', $path[3]);
                $supplier_note_id = array_pop($supplier_note_id);

                $is_contact = $path[4] == 'Contacts';
                $data = $is_contact ?
                $this->Contact_list->get_contact_list_from_supplier_note_id($supplier_note_id) :
                $this->Product_list->get_product_list_from_supplier_note_id($supplier_note_id);

                foreach ($data as $contact_pro) {
                    array_push($return_data['result'], array(
                        'name' => ($is_contact ? $contact_pro['contact_name'] : $contact_pro['product_name']) . '# ' .
                        ($is_contact ? $contact_pro['contact_id'] : $contact_pro['product_id']),
                        'name_disp' => ($is_contact ? $contact_pro['contact_name'] : $contact_pro['product_name']),
                        'rights' => 'drw-rw-rw-',
                        'size' => '0',
                        'date' => $contact_pro['creation_date'],
                        'type' => 'dir',
                    ));
                }
                break;
            case 6:
                $is_contact = $path[4] == 'Contacts';
                $cp_id = explode('#', $path[5]);
                $cp_id = array_pop($cp_id);

                $data = $this->Images_load->get_images_from_table_and_id($is_contact ? 'contacts' : 'products', $cp_id);
                foreach ($data as $photo) {
                    array_push($return_data['result'], array(
                        'name' => $photo['photo_id'] . '# ' . $photo['file_name'],
                        'name_disp' => $photo['file_name'],
                        'rights' => '-rw-rw-rw-',
                        'size' => '1024',
                        'date' => $photo['creation_date'],
                        'type' => 'file',
                    ));
                }
                break;
            default:
                break;
        }

        $this->output($return_data);
        return;
    }

    private function _move($items, $new_path)
    {
        $all_ids = [];
        foreach ($items as $image) {
            $np = explode('/', $image);
            $cnp = count($np);

            if (($cnp == 5 && ($np[4] != 'Contacts' && $np[4] != 'Products')) || $cnp == 7) {
                $pid = explode('/', $image);
                $pid = array_pop($pid);
                $pid = explode('#', $pid)[0];
            } else {
                $return_data = array(
                    'result' => array(
                        'success' => false,
                        "error" => 'Only move images! You can not move a User, Event, Supplier or Contact.',
                    ),
                );
                $this->output($return_data);
                return;
            }
            array_push($all_ids, $pid);
        }

        $np = explode('/', $new_path);
        $cnp = count($np);

        $supplier_path = '../0_data/supplier_info/';
        $product_path = '../0_data/supplier_product/';
        $supplier_contact = '../0_data/supplier_contact/';
        switch ($cnp) {
            case 4: //Move to supplier
                $new_path = $supplier_path;
                $table = 'suppliers';
                $cid = explode('#', $np[3]);
                $cid = array_pop($cid);
                break;
            case 6: //Move to Contact or Porduct
                if ($np[4] == 'Contacts') {
                    $new_path = $supplier_contact;
                    $table = 'contacts';
                } else {
                    $new_path = $product_path;
                    $table = 'products';
                }
                $cid = explode('#', $np[5]);
                $cid = array_pop($cid);
                break;
            default:
                $return_data = array(
                    'result' => array(
                        'success' => false,
                        "error" => 'You can not move to this path.',
                    ),
                );
                $this->output($return_data);
                return;
                break;
        }

        $photo_id = implode(',', $all_ids);
        $result = $this->Images_load->get_images_from_photo_id($photo_id);

        foreach ($result as $value) {
            rename(substr($value['path'], 3) . $value['file_name'], substr($new_path, 3) . $value['file_name']);
        }

        $result = $this->Images_load->move_photos($new_path, $photo_id, $table, $cid);
        $return_data = array(
            'result' => array(
                'success' => true,
                "error" => null,
            ),
        );
        $this->output($return_data);
    }

    private function _remove($items)
    {
        $all_ids = [];
        foreach ($items as $image) {
            $np = explode('/', $image);
            $cnp = count($np);

            if (($cnp == 5 && ($np[4] != 'Contacts' && $np[4] != 'Products')) || $cnp == 7) {
                $pid = explode('/', $image);
                $pid = array_pop($pid);
                $pid = explode('#', $pid)[0];
            } else {
                $return_data = array(
                    'result' => array(
                        'success' => false,
                        "error" => 'Only delete images! You can not delete a User, Event, Supplier or Contact.',
                    ),
                );
                $this->output($return_data);
                return;
            }
            array_push($all_ids, $pid);
        }

        $photo_id = implode(',', $all_ids);
        $result = $this->Images_load->get_images_from_photo_id($photo_id);

        foreach ($result as $value) {
            system("rm " . substr($value['path'], 3) . $value['file_name']);
        }

        $result = $this->Images_load->move_photos(-1, $photo_id, -1, -1);
        $return_data = array(
            'result' => array(
                'success' => true,
                "error" => null,
            ),
        );
        $this->output($return_data);
    }
}
