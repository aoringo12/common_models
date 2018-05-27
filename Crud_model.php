<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Simpley crud model 
 * @access public
 * @package Model
 */
class Crud_model extends CI_Model
{
	protected $option_where = []; 

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Set the where clause
	 * @param array or string  $target [target column]
	 * @param string 		   $type   [where or like]
	 */
	public function set_where($target = [], $type = 'where')
	{
		if ($type === 'where') 
		{
			if (is_array($target)) 
			{
				foreach ($target as $key => $val) 
				{
					$this->db->where($key, $val);
				}
			}
			else
			{
				$this->db->where($target);
			}
		}
		elseif ($type === 'like')
		{
			if (is_array($target)) 
			{
				foreach ($target as $key => $val) 
				{
					$this->db->like($key, $val);
				}
			}
			else
			{
				$this->db->like($target);
			}
		}

		$this->option_where[$type] = $target;
		return $this;
	}

	/**
	 * Set the join clause
	 * @param string $table       [table name]
	 * @param string $join_column [target join column]
	 * @param string $type        [left, right, outer, inner, left outer, right outer]
	 */
	public function set_join($table, $join_column, $type = 'inner')
	{
		$this->db->join($table, $join_column, $type);
		return $this;
	}

	/**
	 * Simple pagination query
	 * @param  string $tbl_name [table name]
	 * @param  int    $limit    [limit]
	 * @param  int    $offset   [offset]
	 * @param  array  $sort     [array(column_name => asc or desc)]
	 * @return array            [array('rows' => result data,'total_count' => total_count)]
	 */
	public function get_lists($tbl_name, $limit, $offset, $sort = [])
	{
		$this->db->from($tbl_name);
		
		foreach($sort as $key => $srt) 
		{
			$this->db->order_by($key, $srt);
		}
		$this->db->limit($limit, $offset);
		$query = $this->db->get();
		$data['rows'] = $query->result_array();
		$data['total_count'] = $this->count_records($tbl_name);

		return $data;
	}

	/**
	 * Record count
	 * @param  string $tbl_name [table name]
	 * @return int              [count]
	 */
	public function count_records($tbl_name)
	{
		$this->db->select('COUNT(*) AS count')
			 	 ->from($tbl_name);
		
		foreach($this->option_where as $type => $target)
		{
			if (is_array($target)) 
			{
				foreach ($target as $key => $val) 
				{
					$this->db->{$type}($key, $val);
				}
			}
			else
			{
				$this->db->{$type}($target);
			}
		}
		
		$this->_init_property();

		$query = $this->db->get();
		if($query->num_rows() > 0)
		{
			$count = $query->row_array('count');
			return $count['count'];
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Data acquisition
	 * @param  string  $tbl_name [table name]
	 * @param  boolean $is_rows  [whether to return it on multiple lines or on a single line]
	 * @param  array   $sort     [array(column_name => asc or desc)]
	 * @param  integer $limit    [limit]
	 * @return type              [result data]
	 */
	public function get_record($tbl_name, $is_rows = true, $sort = [], $limit = 0)
	{
		$this->db->from($tbl_name);
		foreach($sort as $key => $srt) {$this->db->order_by($key, $srt);}
		if($limit) $this->db->limit($limit);
		$query = $this->db->get();

		$this->_init_property();

		if($is_rows)
		{
			return $query->result_array();
		}
		else
		{
			return $query->row_array() ? $query->row_array() : [];
		}
	}

	/**
	 * Insert data into designated table
	 * @param  string  $tbl_name [table name]
	 * @param  array   $params   [insertdata array(column_name => value)]
	 * @return integer 		     [id of the inserted record]
	 */
	public function add_record($tbl_name, $params)
	{
		$this->db->insert($tbl_name, $params);
		$this->_init_property();
		return $this->db->insert_id();
	}

	/**
	 * Update specified record
	 * @param  string $tbl_name [table name]
	 * @param  array  $params   [updatedata array(column_name => value)]
	 * @param  array  $target   [where clause array(column_name => value)]
	 */
	public function update_record($tbl_name, $params = [], $target = [])
	{
		$this->db->set($params);
		// make it possible to specify where clause with both setter function or parameter
		if($target) $this->db->where($target);
		$this->_init_property();
		return $this->db->update($tbl_name);
	}

	/**
	 * Delete specified record
	 * @param  string $tbl_name [table name]
	 * @param  array $target    [where clause array(column_name => value)]
	 */
	public function delete_record($tbl_name, $target = [])
	{	
		// make it possible to specify where clause with both setter function or parameter
		if($target) $this->db->where($target);
		$this->_init_property();
		return $this->db->delete($tbl_name);
	}

	/**
	 * Retrieve the column name of the specified table
	 * @param  string $tbl_name [table name]
	 * @return array            [column name]
	 */
	public function get_column_name($tbl_name)
	{
		$data = array();
		$query = $this->db->query('DESCRIBE ' . $tbl_name);
		foreach($query->result_array() as $col)
		{
			$data[] = $col['Field'];
		}
		$this->_init_property();

		return $data;
	}

	/**
	 * Get next id
	 * @param  string $tbl_name [table name]
	 * @return integer          [next id]
	 */
	public function next_id($tbl_name)
	{
		$sql = 'SHOW TABLE STATUS LIKE \'' . $tbl_name . '\'';
		$query = $this->db->query($sql);
		$ai = $query->row_array();
		if($ai)
		{
			return $ai['Auto_increment'];
		}
		$this->_init_property();

		return false;
	}

	/**
	 * Initialize properties
	 * @return
	 */
	private function _init_property()
	{
		$this->option_where = [];
	}
}
