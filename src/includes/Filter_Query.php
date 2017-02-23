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
			foreach ($this->sub_queries as $query){
				$query->build();
				$partials[] = $query->query;
			}
		}
		$final_query = implode(" UNION ALL ",$partials);
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
			/*
			 * If we have a database structured with complete rows, we can do this:
			 */
			//$result = array_unique($result);

			/*
			 * If we have a database structured with incomplete rows, we have to do this, to simulate an "AND".
			 * We create "AND" by using "UNION ALL" to subsequent select (see: build_from_sub_queries() )
			 */
			$counts = array_count_values($result);
			$duplicates = array_filter($counts,function($item){ return $item > 1; });
			$duplicates = array_keys($duplicates);
			$result = $duplicates;

			/*
			 * We are testing two database structures, see: Plugin::fill_products_index_table()
			 */
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