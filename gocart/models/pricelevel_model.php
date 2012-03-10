<?php
Class Pricelevel_model extends CI_Model
{
	function page_model()
	{
			parent::__construct();
	}
	
	/********************************************************************
	Pricelevel functions
	********************************************************************/
	function get_pricelevel($group_id = 0)
	{
		$query = $this->db->query('SELECT p.* FROM ' . $this->db->dbprefix('customer_groups') . ' g JOIN ' . $this->db->dbprefix('pricelevels') . ' p ON p.id = g.pricelevelid  WHERE g.id=' . $group_id);
	
		$return	= array();
		foreach ($query->result() as $row)
		{	
			$return['tierdescription'] = $row->name;
			$return['option1'] = $row->option1;
			$return['option2'] = $row->option2;
			$return['option3'] = $row->option3;
			$return['option4'] = $row->option4;
			$return['option5'] = $row->option5;
			$return['extra1'] = $row->extra1;
			$return['extra2'] = $row->extra1;
		}		
		return $return;
	}
	
	
	function save($data)
	{
		if($data['id'])
		{
			$this->db->where('id', $data['id']);
			$this->db->update('pricelevels', $data);
			return $data['id'];
		}
		else
		{
			$this->db->insert('pricelevels', $data);
			return $this->db->insert_id();
		}
	}
	
	function delete_pricelevel($id)
	{
		//delete the page
		$this->db->where('id', $id);
		$this->db->delete('pricelevels');
	
	}
}