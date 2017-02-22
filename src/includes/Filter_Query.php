<?php

namespace WBWPF\includes;

class Filter_Query{
	const RESULT_FORMAT_IDS = 0;
	const RESULT_FORMAT_OBJECTS = 1;
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
	 * @var array
	 */
	var $found_products;

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
	 * @param int $result_format
	 *
	 * @return Filter_Query
	 * @throws \Exception
	 */
	public function perform($result_format = self::RESULT_FORMAT_OBJECTS){
		if($this->has_query()){
			global $wpdb;
			if($result_format == self::RESULT_FORMAT_OBJECTS){
				$r = $wpdb->get_results($this->query);
			}elseif($result_format == self::RESULT_FORMAT_IDS){
				$r = $wpdb->get_col($this->query);
				$r = array_unique($r);
			}
			$this->found_products = $r;
			return $this;
		}else{
			throw new \Exception("Invalid or not existent query");
		}
	}

	/**
	 * Get the query result
	 *
	 * @param int $result_format
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function get_results($result_format = self::RESULT_FORMAT_OBJECTS){
		if(!isset($this->found_products)){
			$this->perform($result_format);
		}
		if(is_array($this->found_products)){
			return $this->found_products;
		}else{
			throw new \Exception("Filter_Query was unable to retrieve any products");
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