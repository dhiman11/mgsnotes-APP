<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Likes extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    // return a bool
    public function cancel_like_dislike($id)
    {
        //Check user
        $user_id = $_SESSION['user_id'];
        $this->db->where("`like_id`='$id' AND `status`='normal' AND `user_id`='$user_id'");
        $this->db->from('`likes`');
        if ($this->db->count_all_results() == 0) {
            return false;
        }

        $data = array('status' => 'trash');
        return $this->db->update('likes', $data, "like_id = $id");
    }

    // return a bool
    public function is_user_has_liked($user_id, $type, $id)
    {
        $where = '`connect_table` = \'' . $type . '\' AND `status` = \'normal\' AND `connect_id` = \'' . $id . '\' AND ' . '`user_id`=\'' . $user_id . '\' LIMIT 1';

        $this->db->select('COUNT(1) AS `res`, like_id, value');
        $this->db->from('likes');
        $this->db->where($where);
        $check = $this->db->get()->result_array();
        return array(
            'is_true' => ($check[0]['res'] != 0) ? true : false,
            'id' => $check[0]['like_id'],
            'value' => $check[0]['value'],
        );
    }

    // return like id or -1
    public function get_user_status($user_id, $type, $id)
    {
        $user_status_sql = 'SELECT
        SUM(if(`value` = 1, 1, 0)) AS `like_count`,
        SUM(if(`value` = -1, 1, 0)) AS `dislike_count`,
        `like_id` AS `id`
        FROM `likes`
        WHERE `connect_table` = \'' . $type . '\' AND `status` = \'normal\' AND `connect_id` = \'' . $id . '\' AND ' . '`user_id`=\'' . $user_id . '\' LIMIT 1';

        $res = $this->db->query($user_status_sql)->result_array();
        return $res[0];
    }

    // private transfer 'LIKE' to 1 AND 'DISLIKE' to -1
    private function _transfer_ld_string_int($like_dislike)
    {
        if ($like_dislike == 'LIKE') {
            return 1;
        } else {
            if ($like_dislike == 'DISLIKE') {
                return -1;
            } else {
                return false;
            }
        }
    }

    // return a string array
    public function get_like_dislike_username_list($like_dislike, $id, $type, $limit = 10)
    {
        $like_dislike = $this->_transfer_ld_string_int($like_dislike);
        if (!$like_dislike) {
            return false;
        }

        $this->db->select('`users`.`user_name`');
        $this->db->from('`likes`, `users`');
        $this->db->where("`connect_table` = '$type' AND `connect_id` = '$id' AND `value` = '$like_dislike' AND `status`='normal' AND `users`.`user_id`=`likes`.`user_id`");
        $this->db->order_by('update_date', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    // return a int
    public function count_like_dislike($like_dislike, $id, $type)
    {
        $like_dislike = $this->_transfer_ld_string_int($like_dislike);
        $this->db->where("`connect_table` = '$type' AND `connect_id` = '$id' AND `value` = '$like_dislike' AND `status`='normal'");
        $this->db->from('`likes`');
        return $this->db->count_all_results();
    }

    // return the insert id and is changed if success.
    public function add_like_dislike($user_id, $type, $id, $like_dislike)
    {
        $like_dislike = $this->_transfer_ld_string_int($like_dislike);
        if (!$like_dislike) {
            return false;
        }

        $liked = $this->is_user_has_liked($user_id, $type, $id);
        $data = array(
            'connect_table' => $type,
            'connect_id' => $id,
            'value' => $like_dislike,
            'status' => 'normal',
            'creation_date' => date("Y-m-d H:i:s"),
            'user_id' => $user_id,
        );
        if (!$liked['is_true']) {
            $this->db->insert('likes', $data);
            return array(
                'id' => $this->db->insert_id(),
                'change' => false,
            );
        } else {
            if ($liked['value'] != $like_dislike) {
                $this->cancel_like_dislike($liked['id']);
                $this->db->insert('likes', $data);
                return array(
                    'id' => $this->db->insert_id(),
                    'change' => true,
                    'old_value' => (int) $liked['value'],
                    'old_id' => (int) $liked['id'],
                );
            } else {
                return false;
            }
        }
    }
}
