<?php

namespace WBWPF\includes;

use WBWPF\filters\Filter;

class Query_Factory{
	/**
	 * Create a new query
	 *
	 * @param array $filters (array of \WBWPF\filters\Filter)
	 *
	 * @return Filter_Query
	 */
	public static function build($filters){
		$query = new Filter_Query();
		if(!empty($filters)){
			foreach ($filters as $filter){
				if($filter instanceof Filter){
					$filter->parse_query($query);
				}
			}
		}
		return $query;
	}
}