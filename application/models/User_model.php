<?php

class User_model extends CI_Model {

	public function __construct()
	{
		$this->load->database();
	}

	public function get_user($user_id)
	{
		$query = $this->db->get_where('users', array('id' => $user_id));
		return $query->row_array();
	}

	public function set_token ($user_id, $username, $access_token)
	{
		$data = array(
			'username' => $username,
			'dropbox_token' => $access_token
		);

		$this->db->where('id', $user_id);
		$this->db->update('users', $data);
	}

}
