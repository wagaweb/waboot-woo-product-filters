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
}