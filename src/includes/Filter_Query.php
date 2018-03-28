<?php

namespace WBWPF\includes;

use WBWPF\datatypes\DataType;
use WBWPF\db_backends\Backend;
use WBWPF\Plugin;

class Filter_Query implements Filter_Query_Interface {
	const RESULT_FORMAT_IDS = 0;
	const RESULT_FORMAT_OBJECTS = 1;
	/**
	 * @var Backend
	 */
	var $DB;
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
	 * @var array the available col values for current $found_products (used to filter UITypes values before displaying)
	 */
	var $available_col_values;
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
	 * @var bool
	 */
	var $query_variations = false;
	/**
	 * @var bool
	 */
	var $do_not_query_parent_product = false;

	/**
	 * Filter_Query constructor.
	 *
	 * @param Backend $backend
	 */
	function __construct(Backend $backend){
		$this->DB = $backend;

		try{
			$plugin = Plugin::get_instance_from_global();
			$settings = $plugin->get_plugin_settings();
			$this->query_variations = $settings['show_variations'];
			$this->do_not_query_parent_product = $settings['hide_parent_products'];
		}catch (\Exception $e){}
	}

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
	 * Allows developer to inject custom properties
	 *
	 * @param $properties
	 */
	function inject_properties($properties){
		if(!is_array($properties) || count($properties) === 0) return;
		foreach ($properties as $k => $v){
			$this->$k = $v;
		}
	}

	/**
	 * Assemble the query using the where statements. This is the first method we are testing.
	 *
	 * @return $this
	 */
	public function build($head_only = false){
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

		if(!$head_only){
			//Post types
			if($this->query_variations){
				if($this->do_not_query_parent_product){
					//Here we want VARIATIONS and PRODUCT WITHOUT VARIATIONS ONLY
					$query .= " WHERE (post_type = 'product' OR post_type = 'product_variation') AND has_variations = 0 ";
				}else{
					$query .= " WHERE post_type = 'product' OR post_type = 'product_variation' ";
				}
			}else{
				$query .= " WHERE post_type = 'product' ";
			}

			//Ordering
			if(isset($this->orderby) && isset($this->order)){
				$query .= " ORDER BY ".$this->orderby." ".$this->order;
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
				$query->build(true);
				$partials[] = "(".$query->query.") t$k USING(product_id)";
			}
		}

		/*
		 * We are testing two database structures, see: Plugin::populate_products_index().
		 * With the structures with the incomplete rows (some rows with NULL values) we have to fake an AND condition by using subsequent inner joins: http://stackoverflow.com/questions/3899614/mysql-intersect-results
		 */

		$final_query = "SELECT ".$this->select_statement;
		$final_query.= " FROM ".$this->from_statement;
		if(!empty($partials)){
			$final_query.= " INNER JOIN ";
			$final_query .= implode(" INNER JOIN ",$partials);
		}

		//Post types
		if($this->query_variations){
			if($this->do_not_query_parent_product){
				//Here we want VARIATIONS and PRODUCT WITHOUT VARIATIONS ONLY
				$final_query .= " WHERE (post_type = 'product' OR post_type = 'product_variation') AND has_variations = 0 ";
			}else{
				$final_query .= " WHERE post_type = 'product' OR post_type = 'product_variation' ";
			}
		}else{
			$final_query .= " WHERE post_type = 'product' ";
		}

		//Ordering
		if(isset($this->orderby) && isset($this->order)){
			$final_query .= " ORDER BY ".$this->orderby." ".$this->order;
		}

		$this->query = $final_query;

		return $this;
	}

	/**
	 * Adds a sub query
	 *
	 * @param Filter_Query $query
	 */
	public function add_sub_query(Filter_Query_Interface $query){
		$this->sub_queries[] = $query;
	}

	/**
	 * Set the select statement
	 *
	 * @param $statement
	 */
	public function set_fields_to_retrieve($statement){
		$statement = sanitize_text_field($statement);
		$this->select_statement = $statement;
	}

	/**
	 * Set the from statement
	 *
	 * @param $statement
	 */
	public function set_source($statement){
		$statement = sanitize_text_field($statement);
		$this->from_statement = $statement;
	}

	/**
	 * Add a new where statement
	 */
	public function add_condition($statement){
		$statement = sanitize_text_field($statement);
		$this->where_statements[] = $statement;
	}

	/**
	 * Prepare a statement for SQL
	 *
	 * @param $string
	 * @param $args
	 *
	 * @return string
	 */
	public function prepare($string,$args){
		global $wpdb;
		$string = $wpdb->prepare($string,$args);
		return $string;
	}

	/**
	 * Get the corresponding value placeholder for type $type (used for preparing the statements)
	 *
	 * @param $type
	 *
	 * @return string
	 */
	public function get_placeholder_for_value_of_type($type){
		switch ($type){
			case DataType::VALUES_TYPE_INT:
				return "%d";
				break;
			case DataType::VALUES_TYPE_FLOAT:
				return "%f";
				break;
			case DataType::VALUES_TYPE_STRING:
				return "%s";
				break;
		}

		return "%s";
	}

	/**
	 * Completes the query by parsing an array of filters
	 *
	 * @param $filters
	 */
	public function parse_filters($filters){
		foreach ($filters as $filter){
			if(!$filter instanceof Filter){
				continue;
			}
			if(is_array($filter->current_values) && !empty($filter->current_values)){
				$value_placeholder = $this->get_placeholder_for_value_of_type($filter->dataType->value_type);

				//Replace all values with placeholders
				$placeholder_values = array_map(function($el) use($value_placeholder){ return $value_placeholder; },$filter->current_values);

				//Build up the statement
				$statement = implode(" OR `$filter->slug` = ",$placeholder_values);
				$statement = "`$filter->slug` = ".$statement;

				//Prepare the statement with actual values
				$statement = $this->prepare($statement,$filter->current_values);

				//Add the statement to the query
				//$query->where_statements[] = $statement;
				$new_query = Query_Factory::build();
				$new_query->add_condition($statement);
				$this->add_sub_query($new_query);
			}
		}
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
	 * @param int $format
	 *
	 * @return array
	 */
	private function parse_results($result, $format = self::RESULT_FORMAT_OBJECTS){

		/*
		 * We need a way to allows UITypes to know which of their values as an actual product associated in the current queried results
		 * (eg: the product color "red" doesn't has to to be visible when no product is red in the current visualization)
		 */

		//Here we get the current active filters
		$settings = Plugin::get_instance_from_global()->get_plugin_settings();
		$cols = call_user_func(function() use($settings){
			$r = [];
			if(isset($settings['filters'])){
				foreach ($settings['filters'] as $slug => $cols){
					$r = array_merge($r,$cols);
				}
			}

			return $r;
		});

		//Here we get the available values of the active filters for the current considered ids
		$available_col_values = $this->DB->get_available_property_values_for_ids( $result, $cols );

		$this->set_available_col_values($available_col_values);

		if($format == self::RESULT_FORMAT_IDS){
			$result = array_unique($result);
		}

		do_action_ref_array("wbwpf/query/parse_results",[&$result,&$this,$format]);

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
	 * Set the available col values. This is mainly used by Plugin to set the available col values during "wbwpf/query/parse_results"
	 *
	 * @param array $cols
	 */
	public function set_available_col_values($cols){
		$this->available_col_values = $cols;
	}

	/**
	 * Checks if the query string is filled correctly
	 *
	 * @return bool
	 */
	public function has_query(){
		return isset($this->query) && $this->query != "";
	}

	/**
	 * Checks if the query has found some products
	 *
	 * @return bool
	 */
	public function has_products(){
		return !empty($this->found_products);
	}
}