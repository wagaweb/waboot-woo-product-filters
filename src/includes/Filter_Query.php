<?php

namespace WBWPF\includes;

class Filter_Query{
	/**
	 * @var
	 */
	var $select_statement;
	/**
	 * @var
	 */
	var $from_statement;
	/**
	 * @var
	 */
	var $where_statements;
	/**
	 * @var
	 */
	var $query;

	/**
	 * Assemble the query
	 *
	 * @return $this
	 */
	public function build(){
		$query = "SELECT ".$this->select_statement;
		$query.= " FROM ".$this->from_statement;
		if(is_array($this->where_statements) && !empty($this->where_statements)){
			$query .= " WHERE ";
			$i = 0;
			foreach ($this->where_statements as $statement){
				if($i > 0){
					$query .= " AND ";
				}
				$query .= "(".$statement.")";
				$i++;
			}
		}
		$this->query = $query;
		return $this;
	}

	/**
	 * Performs the query and return the result
	 *
	 * @return array|null|object
	 * @throws \Exception
	 */
	public function perform(){
		if($this->has_query()){
			global $wpdb;
			$r = $wpdb->get_results($this->query);
			return $r;
		}else{
			throw new \Exception("Invalid or not existent query");
		}
	}

	/**
	 * Checks if the query string is filled correctly
	 *
	 * @return bool
	 */
	public function has_query(){
		return isset($this->query) && $this->query != "";
	}
}