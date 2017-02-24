<?php

namespace WBWPF\includes;

use WBWPF\Plugin;

class Query_Factory{
	/**
	 * Create a new query
	 *
	 * @param array $filters (array of \WBWPF\filters\Filter)
	 *
	 * @return Filter_Query
	 */
	public static function build($filters = []){
		global $wpdb;

		$query = new Filter_Query();

		$query->select_statement = "product_id";
		$query->from_statement = $wpdb->prefix.Plugin::CUSTOM_PRODUCT_INDEX_TABLE;

		if(!empty($filters)){
			foreach ($filters as $filter){
				if($filter instanceof Filter){
					$filter->parse_query($query);
				}
			}

			/*
			 * We are testing two database structures, see Plugin::fill_products_index_table()
			 */

			/*
			 * This is for the first: (we do not use sub queries)
			 */
			//$query->build();

			/*
			 * This is for the second:
			 */
			$query->build_from_sub_queries();
		}

		return $query;
	}

	/**
	 * Build a Filter_Query from params
	 *
	 * @param array $active_filters
	 * @param array $current_values
	 *
	 * @return Filter_Query
	 */
	public static function build_from_params($active_filters,$current_values){
		$filters = Filter_Factory::build_from_params($active_filters,$current_values);
		$filter_query = self::build($filters);
		return $filter_query;
	}

	/**
	 * Build a Filter_Query from $_GET params (it looks for a specific-predefined get params)
	 *
	 * @return Filter_Query
	 */
	public static function build_from_get_params(){
		$filters = Filter_Factory::build_from_get_params();
		$filter_query = self::build($filters);
		return $filter_query;
	}

	/**
	 * Setup a Filter_Query starting from current $_POST params (it looks for specific-predefined post params)
	 *
	 * @return Filter_Query
	 */
	public static function build_from_post_params(){
		$filters =  Filter_Factory::build_from_post_params();
		$filter_query = self::build($filters);
		return $filter_query;
	}
}