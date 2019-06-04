<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Comments extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('date');
    }

    // Add new comment
    public function add_new_comment($user_id, $oid, $type, $data)
    {
        $created = date_create($data['created']);
        $created_at = date_format($created, 'Y-m-d H:i:s');
        $parent = $data['parent'];
        $parent_id = -1;
        if ($parent && is_numeric($parent)) {
            $parent_id = $parent;
        }

        $insert_data = array(
            'connect_table' => $type,
            'connect_id' => $oid,
            'replied_comment_id' => $parent_id,
            'comment' => $data['content'],
            'status' => 'normal',
            'update_date' => $created_at,
            'creation_date' => $created_at,
            'user_id' => $user_id,
        );

        $this->db->insert('comments', $insert_data);
        $new_id = $this->db->insert_id();

        $data['id'] = $new_id;
        return $data;
    }

    // Is current user
    private function _is_current_user($cid)
    {
        $user_id = $this->session->userdata('user_id');
        $this->db->from('`comments`');
        $this->db->where("`user_id`='$user_id' AND `comment_id` = '$cid'");
        $this->db->get();
        return $this->db->count_all_results() == 0 ? false : true;
    }

    // Delete comment
    public function delete_comment($cid)
    {
        if (!$this->_is_current_user($cid)) {return false;}
        $data = array('status' => 'trash');
        return $this->db->update('comments', $data, "`comment_id` = $cid");
    }

    // Update comment
    public function update_comment($cid, $data)
    {
        if (!$this->_is_current_user($cid)) {return false;}
        $modified_at = mdate('%Y-%m-%d %H:%i:%s', (int) $data['modified'] / 1000);

        $update_data = array(
            'comment' => $data['content'],
            'update_date' => $modified_at,
        );
        return $this->db->update('comments', $update_data, "`comment_id` = $cid");
    }

    // Get comments
    public function get_comments($type, $id)
    {
        $this->db->select('`comments`.*, `users`.`user_name`');
        $this->db->from('`comments`,`users`');
        $this->db->where("`connect_table` = '$type' AND `connect_id` = '$id' AND `status` = 'normal' AND `users`.`user_id`=`comments`.`user_id`");

        $after_get = $this->db->get();
        $all_comments_count = $after_get->num_rows();
        $all_comments = $after_get->result_array();
        $user_id = $this->session->userdata('user_id');

        $r_data = array();
        foreach ($all_comments as $comment) {
            $temp_c = array(
                'id' => $comment['comment_id'],
                'content' => stripslashes($comment['comment']),
                'created' => $comment['creation_date'],
                'modified' => $comment['update_date'],
                'parent' => $comment['replied_comment_id'] == -1 ? null : $comment['replied_comment_id'],
                'created_by_current_user' => $comment['user_id'] == $user_id ? true : false,
                'fullname' => $comment['user_id'] == $user_id ? 'You' : $comment['user_name'],
            );
            array_push($r_data, $temp_c);
        }

        return array('count' => $all_comments_count, 'comments' => $r_data);
    }

    // Count comment
    public function get_num_comment_by_id($type, $id)
    {
        $this->db->from('`comments`');
        $this->db->where("`connect_table` = '$type' AND `connect_id` = '$id' AND `status` = 'normal'");
        return $this->db->count_all_results();
    }
}
