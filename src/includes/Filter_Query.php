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
	var $sub_queries;
	/**
	 * @var
	 */
	var $query;
	/**
	 * @var array
	 */
	var $found_products;
	/**
	 * @var string
	 */
	var $orderby;
	/**
	 * @var string
	 */
	var $order;
	/**
	 * @var int
	 */
	var $limit;
	/**
	 * @var int
	 */
	var $offset;

	/**
	 * Filter_Query constructor.
	 */
	function __construct(){}

	/**
	 * Set ordering params
	 *
	 * @param $orderby
	 * @param $order
	 */
	function set_ordering($orderby,$order){
		$this->order = $order;
		$this->orderby = $orderby;
	}

	/**
	 * Set pagination params
	 *
	 * @param $offset
	 * @param $limit
	 */
	function set_pagination($offset,$limit){
		$this->offset = $offset;
		$this->limit = $limit;
	}

	/**
	 * Assemble the query using the where statements. This is the first method we are testing.
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
	 * Assemble the query using the sub_queries property. This is the second method we are testing.
	 */
	public function build_from_sub_queries(){
		$partials = [];
		if(!empty($this->sub_queries)){
			foreach ($this->sub_queries as $k => $query){
				$query->build();
				$partials[] = "(".$query->query.") t$k USING(product_id)";
			}
		}

		/*
		 * We are testing two database structures, see: Plugin::fill_products_index_table().
		 * With the structures with the incomplete rows (some rows with NULL values) we have to fake an AND condition by using subsequent inner joins: http://stackoverflow.com/questions/3899614/mysql-intersect-results
		 */

		$final_query = "SELECT ".$this->select_statement;
		$final_query.= " FROM ".$this->from_statement;
		$final_query.= " INNER JOIN ";
		$final_query .= implode(" INNER JOIN ",$partials);

		$this->query = $final_query;
	}

	/**
	 * Adds a sub query
	 *
	 * @param Filter_Query $query
	 */
	public function add_sub_query(Filter_Query $query){
		$this->sub_queries[] = $query;
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
			if($result_format == self::RESULT_FORMAT_IDS){
				$r = $wpdb->get_col($this->query);
			}else{
				$r = $wpdb->get_results($this->query);
			}
			$r = $this->parse_results($r,$result_format);
			$this->found_products = $r;
			return $this;
		}else{
			throw new \Exception("Invalid or not existent query");
		}
	}

	/**
	 * Applies some actions to the result before store it
	 *
	 * @param array $result the result to parse
	 *
	 * @return array
	 */
	private function parse_results($result, $format = self::RESULT_FORMAT_OBJECTS){
		if($format == self::RESULT_FORMAT_IDS){
			$result = array_unique($result);
		}
		return $result;
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