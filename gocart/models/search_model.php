<?php
Class Search_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	/********************************************************************

	********************************************************************/
	
	function record_term($term)
	{
		$code	= md5($this->session->userdata('session_id') . $term);//
		$this->db->where('code', $code);
		$exists	= $this->db->count_all_results('search');
		if ($exists < 1)
		{
			$this->db->insert('search', array('code'=>$code, 'term'=>$term));
		}
		return $code;
	}
	
	function get_term($code)
	{
		$this->db->select('term');
		$result	= $this->db->get_where('search', array('code'=>$code));
		$result	= $result->row();
		return $result->term;
	}
	
	function set_term($code, $term)
	{
		$this->db->where('code', $code);
		$this->db->update('search', array(
		               'term' => $term
			            ));
	}
}