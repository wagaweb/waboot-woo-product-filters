<?php

namespace WBWPF\includes;

use WBWPF\Plugin;

class Filter_Factory{

	/**
	 * Build a new filter instance
	 *
	 * @param $filterSlug
	 * @param $dataType_slug
	 * @param $uiType_slug
	 *
	 * @return bool|Filter
	 */
	public static function build($filterSlug,$dataType_slug,$uiType_slug){
		$plugin = Plugin::get_instance_from_global();
		$dataTypes = $plugin->get_available_dataTypes();
		$uiTypes = $plugin->get_available_uiTypes();

		if(!isset($dataTypes[$dataType_slug])) return false;
		$dataTypeClassName = $dataTypes[$dataType_slug];

		if(!isset($uiTypes[$uiType_slug])) return false;
		$uiTypeClassName = $uiTypes[$uiType_slug];

		$dataType = new $dataTypeClassName();
		$uiType = new $uiTypeClassName();

		$f = new Filter($filterSlug,$dataType,$uiType);
		$f->uiType->set_name($filterSlug);

		return $f;
	}

	/**
	 * Build an array of filter instances
	 *
	 * @param $params
	 *
	 * @param array|bool|FALSE $filter_values if provided, the filters will be assigned with these values
	 *
	 * @return array
	 */
	public static function build_from_params($params,$filter_values = false){
		$plugin = Plugin::get_instance_from_global();
		$dataTypes = $plugin->get_available_dataTypes();
		$uiTypes = $plugin->get_available_uiTypes();

		$filters = [];

		foreach ($params as $filter_slug => $filter_params){
			if(!isset($filter_params['dataType']) || !isset($filter_params['type'])) continue;

			$dataType_slug = $filter_params['dataType'];
			$uiType_slug = $filter_params['type'];

			if(!isset($dataTypes[$dataType_slug])) continue;
			$dataTypeClassName = $dataTypes[$dataType_slug];

			if(!isset($uiTypes[$uiType_slug])) continue;
			$uiTypeClassName = $uiTypes[$uiType_slug];

			$datatype = new $dataTypeClassName();
			$uitype = new $uiTypeClassName();

			$f = new Filter($filter_slug,$datatype,$uitype);
			$f->uiType->set_name($filter_slug);

			if(is_array($filter_values) && isset($filter_values[$filter_slug])){
				$f->set_value($filter_values[$filter_slug]);
			}

			$filters[] = $f;
		}

		return $filters;
	}
}