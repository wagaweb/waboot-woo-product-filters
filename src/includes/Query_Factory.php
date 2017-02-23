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
	public static function build($filters){
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
		}

		$query->build();

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
		$params = $_GET['wbwpf_query'];

		$r = Filter_Factory::unwrap_stringified($params);

		$active_filters = $r['filters'];
		$filter_current_values = $r['values'];

		$filter_query = self::build_from_params($active_filters,$filter_current_values);

		return $filter_query;
	}

	/**
	 * Setup a Filter_Query starting from current $_POST params (it looks for specific-predefined post params)
	 *
	 * @return Filter_Query
	 */
	public static function build_from_post_params(){
		$active_filters = $_POST['wbwpf_active_filters'];

		$filter_current_values = call_user_func(function(){
			$posted_params = $_POST;
			$ignorelist = ["wbwpf_active_filters","wbwpf_search_by_filters"];
			$current_values = [];
			foreach ($posted_params as $param => $param_values){
				if(!in_array($param,$ignorelist) && preg_match("/wbwpf_/",$param)){
					$param = preg_replace("/wbwpf_/","",$param);
					$current_values[$param] = $param_values;
				}
			}
			return $current_values;
		});

		$filter_query = self::build_from_params($active_filters,$filter_current_values);

		return $filter_query;
	}
}