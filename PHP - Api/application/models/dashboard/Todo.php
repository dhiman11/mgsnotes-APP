<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Todo extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Users');
        $this->load->library('Cache');
        $this->cache = new Cache();
        $this->calc_xp_method = array(
            'all_suppliers_count' => function ($x) {
                return $x * 1.1;
            },
            'bad_suppliers_count' => function ($x) {
                return -$x * 1.5;
            },
            'no_everything' => function ($x) {
                return -$x * 2.0;
            },
            'all_contacts' => function ($x) {
                return $x * 1.5;
            },
            'no_phone' => function ($x) {
                return -$x * 0.5;
            },
            'no_email' => function ($x) {
                return -$x * 0.3;
            },
            'all_products' => function ($x) {
                return $x * 1.5;
            },
            'no_product_name' => function ($x) {
                return -$x * 0.3;
            },
            'no_fob_price' => function ($x) {
                return -$x * 0.8;
            },
            'no_product_cat_id' => function ($x) {
                return -$x * 0.05;
            },
            'no_photo' => function ($x) {
                return -$x * 1.2;
            },
            'count_like' => function ($x) {
                return $x * 0.8;
            },
            'count_dislike' => function ($x) {
                return -$x * 0.6;
            },
            'count_comment' => function ($x) {
                return $x * 0.3;
            },
            'count_share' => function ($x) {
                return $x * 0.5;
            },
        );

        $this->rank_system = array(
            ['-', 0, -1, ' New '],
            [0, 100, 0, 'Scout'],
            [100, 200, 1, 'Grunt'],
            [200, 300, 2, 'Sergeant'],
            [300, 500, 3, 'Senior Sergeant'],
            [500, 700, 4, 'First Sergeant'],
            [700, 900, 5, 'Stone Guard'],
            [900, 1100, 6, 'Blood Guard'],
            [1100, 1600, 7, 'Legionnare'],
            [1600, 2100, 8, 'Centurion'],
            [2100, 2600, 9, 'Champion'],
            [2600, 3100, 10, 'Lieutenant General'],
            [3100, 4100, 11, 'General'],
            [4100, 5100, 12, 'Warlord'],
            [5100, 7100, 13, 'High Warlord'],
            [7100, '+', 14, 'Epic Master'],
        );
    }

    private function get_rank($xp)
    {
        $top_level_count = count($this->rank_system) - 1;
        foreach ($this->rank_system as $value) {
            $is_this_rank = true;
            if ($value[0] == '-') {
                $is_this_rank &= ($xp < $value[1]);
            } else {
                $is_this_rank &= ($xp >= $value[0]);
            }
            if ($value[1] != '+') {
                $is_this_rank &= ($xp < $value[1]);
            }
            if ($is_this_rank) {
                $next_level = $value[2] + 2 <= $top_level_count ? $value[2] + 2 : $top_level_count;
                return array(
                    'level' => $value[2],
                    'title' => $value[3],
                    'next_level' => $this->rank_system[$next_level][0] - $xp,
                    'level_diff' => $this->rank_system[$value[2] + 1][1] - $this->rank_system[$value[2] + 1][0],
                );
            }
        }
    }

    private function check_supplier($supp_id, $supp_note_id)
    {
        $where = "`supplier_note_id` = '$supp_note_id'";

        $this->db->where($where);
        $this->db->where("`status` != 'trash'");
        $res_contact = $this->db->count_all_results('contacts');

        $this->db->where($where);
        $this->db->where("`status` != 'trash'");
        $res_product = $this->db->count_all_results('products');
        return array(
            'supp_name' => $this->get_supplier_name($supp_id),
            'no_contact' => $res_contact == 0 ? true : false,
            'no_product' => $res_product == 0 ? true : false,
        );
    }

    private function get_supplier_name($supp_id)
    {
        $this->db->select('supplier_name');
        $this->db->where("`supplier_id` = '$supp_id'");
        $this->db->from('suppliers_name');
        $data = $this->db->get()->result_array();
        if (count($data) == 0) {
            return '[NO SUPPLIER NAME]';
        }
        return $data[0]['supplier_name'];
    }

    private function check_photo($table_name, $id)
    {
        $where = "`connect_table` = '$table_name' AND `connect_id` = '$id'";
        $this->db->where($where);
        $this->db->where("`status` != 'trash'");
        $res_product = $this->db->count_all_results('photos');
        return $res_product == 0;
    }

    public function _supplier_notes_to_creat($no_product = true, $no_contact = true, $display_count = 10, $user_id = false, $no_cache = false)
    {
        if (!$user_id) {
            $user_id = $_SESSION['user_id'];
            session_write_close();
        }
        if (is_array($user_id)) {
            $user_id = implode(",", $user_id);
        }

        $cache_key = '_supplier_notes_to_creat' . $no_product . $no_contact . $display_count . str_replace("'", "", $user_id);
        if (!$no_cache) {
            $cache_value = $this->cache->get($cache_key);
            if ($cache_value) {
                return $cache_value;
            }
        }

        $this->db->select('supplier_note_id, supplier_id');
        if ($user_id != "'-1'") {
            $this->db->where("`user_id` IN ($user_id)");
        }

        $this->db->where("`status` != 'trash'");
        $this->db->from('suppliers');
        $this->db->order_by('update_date DESC');
        $data = $this->db->get()->result_array();

        $final_data = [];
        $i = 0;
        $all_bad = 0;
        foreach ($data as $supp) {
            $key = $supp['supplier_note_id'];
            $value = $supp['supplier_id'];

            $res = $this->check_supplier($value, $key);
            if ($no_contact && $no_product) {
                if ($res['no_product'] || $res['no_contact']) {
                    $all_bad++;
                    if ($i < $display_count) {
                        $final_data[$key] = $res;
                        $i++;
                    }
                }
            } else {
                if (($res['no_product'] || !$no_product) && ($res['no_contact'] || !$no_contact)) {
                    $all_bad++;
                    if ($i < $display_count) {
                        $final_data[$key] = $res;
                        $i++;
                    }
                }
            }
        }
        $cache_value = array(
            'data' => $final_data,
            'all_suppliers_count' => count($data),
            'bad_suppliers_count' => $all_bad,
        );
        $this->cache->set($cache_key, $cache_value);
        return $cache_value;
    }

    private function notes_to_update_something(
        $fields_to_check,
        $table_name,
        $id,
        $name,
        $display_count = 10,
        $user_id = false,
        $count_like_comment_share = false,
        $no_cache = false
    ) {
        if (!$user_id) {
            $user_id = $_SESSION['user_id'];
            session_write_close();
        }
        if (is_array($user_id)) {
            $user_id = implode(",", $user_id);
        }
        $cache_key = 'notes_to_update_something' . serialize($fields_to_check) . $table_name . $id . $name . $display_count . str_replace("'", "", $user_id);
        if (!$no_cache) {
            $cache_value = $this->cache->get($cache_key);
            if ($cache_value) {
                return $cache_value;
            }
        }

        $no_name_hint = false;
        if (in_array($name, $fields_to_check)) {
            $name_array = $name . '_';
            $no_name_hint = true;
        } else {
            $name_array = $name;
        }

        if ($user_id != "'-1'") {
            $this->db->where("`user_id` IN ($user_id)");
        }

        $this->db->where("`status` != 'trash'");
        $count_all_sth = $this->db->count_all_results($table_name);
        $count = array(
            'all_' . $table_name => $count_all_sth,
            'no_everything' => 0,
        );

        $select = "$id,$name,suppliers_name.supplier_name";
        foreach ($fields_to_check as $value) {
            if ($value != 'photo') {
                $select .= ",$value";
            }
        }
        $this->db->select($select);
        if ($user_id != "'-1'") {
            $this->db->where("`$table_name.user_id` IN (" . $user_id . ")");
        }

        $this->db->where("`$table_name.status` != 'trash'");
        $where = '(';
        foreach ($fields_to_check as $value) {
            $count['no_' . $value] = 0;
            if ($value == 'photo') {
                continue;
            }

            if ($where != '(') {
                $where .= ' OR ';
            }
            $where .= "`$value` = '' OR  `$value` = ' ' OR `$value` = 0";
        }

        $this->db->where($where . ')');
        $this->db->from($table_name);
        // Join to get supplier_name
        $this->db->join('suppliers', "suppliers.supplier_note_id = $table_name.supplier_note_id", 'left');
        $this->db->join('suppliers_name', "suppliers_name.supplier_id = suppliers.supplier_id", 'left');

        $this->db->order_by("$table_name.update_date DESC");
        $data = $this->db->get()->result_array();
        $final_data = [];

        $i = 0;
        $count_like = 0;
        $count_dislike = 0;
        $count_comment = 0;
        $count_share = 0;

        if ($count_like_comment_share && $table_name == 'products') {
            $this->db->select("SUM(`quantity_of_recipients`) AS `sum`");
            $this->db->where("user_id IN ($user_id)");
            $this->db->from('sharings');
            $count_share = (int) $this->db->get()->result_array()[0]['sum'];
        }
        foreach ($data as $value) {
            if ($count_like_comment_share && $table_name == 'products') {
                $this->db->where("connect_table = 'products'");
                $this->db->where("connect_id = '" . $value['product_id'] . "'");
                $this->db->where("value = 1");
                $this->db->where("status = 'normal'");
                $this->db->from('likes');
                $count_like += $this->db->count_all_results();

                $this->db->where("connect_table = 'products'");
                $this->db->where("connect_id = '" . $value['product_id'] . "'");
                $this->db->where("value = -1");
                $this->db->where("status = 'normal'");
                $this->db->from('likes');
                $count_dislike += $this->db->count_all_results();

                $this->db->where("connect_table = 'products'");
                $this->db->where("connect_id = '" . $value['product_id'] . "'");
                $this->db->where("status = 'normal'");
                $this->db->from('comments');
                $count_comment += $this->db->count_all_results();
            }
            $res = [];
            if ($no_name_hint) {
                if (trim($value[$name]) == '') {
                    $res[$name_array] = '[NO PRODUCT NAME]';
                } else {
                    $res[$name_array] = $value[$name];
                }
            } else {
                $res[$name_array] = $value[$name];
            }
            $res['supplier_name'] = $value['supplier_name'];
            $is_add = false;
            $no_everything = true;
            foreach ($fields_to_check as $field) {
                if ($field == 'photo') {
                    if ($this->check_photo($table_name, $value[$id])) {
                        $res[$field] = true;
                        $count['no_' . $field]++;
                        $is_add = true;
                    } else {
                        $no_everything = false;
                        $res[$field] = false;
                    }
                    continue;
                }
                if ($value[$field] == '' || $value[$field] == ' ' || $value[$field] == '0') {
                    $res[$field] = true;
                    $count['no_' . $field]++;
                    $is_add = true;
                } else {
                    $no_everything = false;
                    $res[$field] = false;
                }
            }
            if ($is_add && $i < $display_count) {
                $final_data[$value[$id]] = $res;
                $i++;
            }
            if ($no_everything) {
                $count['no_everything']++;
            }
        }
        if ($count_like_comment_share && $table_name == 'products') {
            $count['count_like'] = $count_like;
            $count['count_dislike'] = $count_dislike;
            $count['count_comment'] = $count_comment;
            $count['count_share'] = $count_share;
        }
        $cache_value = array(
            'data' => $final_data,
            'count_info' => $count,
        );
        $this->cache->set($cache_key, $cache_value);
        return $cache_value;
    }

    public function supplier_notes_to_creat($no_product = true, $no_contact = true, $display_count = 10, $user_id = false, $admin_ctrl = true, $no_cache = false)
    {
        if ($admin_ctrl && (!$user_id || $_SESSION['user_type'] != 'notes_admin')) {
            $user_id = $_SESSION['user_id'];
        }
        session_write_close();
        return $this->_supplier_notes_to_creat($no_product, $no_contact, $display_count, $user_id, $no_cache);
    }

    public function notes_to_update_cotacts($fields_to_check = false, $display_count = 10, $user_id = false, $admin_ctrl = true, $no_cache = false)
    {
        if ($admin_ctrl && (!$user_id || $_SESSION['user_type'] != 'notes_admin')) {
            $user_id = $_SESSION['user_id'];
        }
        session_write_close();

        if (!$fields_to_check || !is_array($fields_to_check)) {
            $fields_to_check = ['phone', 'email'];
        }
        return $this->notes_to_update_something($fields_to_check, 'contacts', 'contact_id', 'contact_name', $display_count, $user_id, false, $no_cache);
    }

    public function notes_to_update_products($fields_to_check = false, $display_count = 10, $user_id = false, $admin_ctrl = true, $no_cache = false)
    {
        if ($admin_ctrl && (!$user_id || $_SESSION['user_type'] != 'notes_admin')) {
            $user_id = $_SESSION['user_id'];
        }
        session_write_close();

        if (!$fields_to_check || !is_array($fields_to_check)) {
            $fields_to_check = ['product_name', 'fob_price', 'product_cat_id', 'photo'];
        }
        return $this->notes_to_update_something($fields_to_check, 'products', 'product_id', 'product_name', $display_count, $user_id, true, $no_cache);
    }

    public function todo_datatable($id = null, $limit_start = 0, $limit_length = 10, $order = null, $where = null)
    {
        if ($_SESSION['user_type'] != 'notes_admin') {
            exit('ERROR 404 NOT FOUND');
        }
        session_write_close();

        $all_users = $this->Users->get_user_name_list('shanghai');
        $return_array = array();
        foreach ($all_users as $user) {
            $supplier = $this->Todo->supplier_notes_to_creat(true, true, 10, $user['user_id']);
            $contact = $this->Todo->notes_to_update_cotacts(false, 10, $user['user_id']);
            $product = $this->Todo->notes_to_update_products(false, 10, $user['user_id']);
            $xp = $this->get_xp($supplier) + $this->get_xp($contact['count_info']) + $this->get_xp($product['count_info']);
            array_push($return_array,
                array(
                    'user_id' => $user['user_id'],
                    'user_name' => $user['user_name'],
                    'supplier' => array(
                        'supp_all' => $supplier['all_suppliers_count'],
                        'supp_bad' => $supplier['bad_suppliers_count'],
                    ),
                    'contact' => $contact['count_info'],
                    'product' => $product['count_info'],
                    'xp' => $xp,
                    'rank' => $this->get_rank($xp),
                )
            );
        }
        usort($return_array, function ($a, $b) {
            return ($a['xp'] < $b['xp']);
        });
        return array(
            'data' => $return_array,
            'sEcho' => 1,
            'display_count' => count($return_array),
            'all_count' => count($all_users),
        );
    }

    private function get_xp($one_data)
    {
        $score = 0;
        foreach ($one_data as $key => $value) {
            if (array_key_exists($key, $this->calc_xp_method)) {
                $score += $this->calc_xp_method[$key]($value);
            }
        }
        return $score;
    }

    public function get_xp_with_user_id($user_id)
    {
        if (!is_array($user_id)) {
            $user_id = str_replace("'", '', $user_id);
            $user_id = explode(',', $user_id);
        }
        $return_array = [];
        foreach ($user_id as $id) {
            if (!empty($id)) {
                $supplier = $this->Todo->supplier_notes_to_creat(true, true, 10, $id);
                $contact = $this->Todo->notes_to_update_cotacts(false, 10, $id);
                $product = $this->Todo->notes_to_update_products(false, 10, $id);
                $xp = $this->get_xp($supplier) + $this->get_xp($contact['count_info']) + $this->get_xp($product['count_info']);
                $return_array[$id] = array(
                    'xp' => $xp,
                    'rank' => $this->get_rank($xp),
                );
            }
        }
        if (count($return_array) == 0) {
            show_404();
        }
        return count($user_id) == 1 ? $return_array[$user_id[0]] : $return_array;
    }

    public function get_top_or_last_xp($is_top = true, $count = 3)
    {
        $this_id = $_SESSION['user_id'];
        session_write_close();
        $this->is_top = $is_top;
        $all_users = $this->Users->get_user_name_list('shanghai');
        $return_array = [];
        foreach ($all_users as $user) {
            $supplier = $this->Todo->supplier_notes_to_creat(true, true, 10, $user['user_id'], false);
            $contact = $this->Todo->notes_to_update_cotacts(false, 10, $user['user_id'], false);
            $product = $this->Todo->notes_to_update_products(false, 10, $user['user_id'], false);
            $xp = $this->get_xp($supplier) + $this->get_xp($contact['count_info']) + $this->get_xp($product['count_info']);
            if ($this_id == $user['user_id']) {
                $user['user_name'] = '<span style="color:#2fafe1!important;font-size: 18px;font-weight: bolder;">YOU</span>';
            }
            array_push($return_array,
                array(
                    'user_id' => $user['user_id'],
                    'user_name' => $user['user_name'],
                    'xp' => $xp,
                    'rank' => $this->get_rank($xp),
                )
            );
        }

        usort($return_array, function ($a, $b) {
            return ($a['xp'] > $b['xp']) xor $this->is_top;
        });

        return $count ? array_slice($return_array, 0, $count) : $return_array;
    }

    public function get_my_closest_user($user_id)
    {
        $user_id = str_replace("'", '', $user_id);
        $data = $this->get_top_or_last_xp(true, false);
        $this_id = $_SESSION['user_id'];
        session_write_close();
        $return_array = [];
        $i = 0;
        $s_count = count($data);
        foreach ($data as $user) {
            if ($user['user_id'] == $user_id) {
                if ($this_id == $data[$i]['user_id']) {
                    $data[$i]['user_name'] = '<span style="color:#2fafe1!important;font-size: 18px;font-weight: bolder;">YOU</span>';
                }

                if ($i == 0) {
                    array_push($return_array, $data[$i]);
                    if ($i + 1 < $s_count) {
                        array_push($return_array, $data[$i + 1]);
                    }
                    if ($i + 2 < $s_count) {
                        array_push($return_array, $data[$i + 2]);
                    }
                    break;
                }
                if ($i > 0 && $i < $s_count - 1) {
                    array_push($return_array, $data[$i - 1]);
                    array_push($return_array, $data[$i]);
                    if ($i + 1 < $s_count) {
                        array_push($return_array, $data[$i + 1]);
                    }
                    break;
                }
                if ($i = $s_count - 1) {
                    if ($i - 2 >= 0) {
                        array_push($return_array, $data[$i - 2]);
                    }
                    if ($i - 1 >= 0) {
                        array_push($return_array, $data[$i - 1]);
                    }
                    array_push($return_array, $data[$i]);
                    break;
                }
            }
            $i++;
        }
        if (count($return_array) == 0) {
            show_404();
        }
        return $return_array;
    }
}
