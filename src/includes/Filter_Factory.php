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

	/**
	 * Build an array of filters from a string format
	 *
	 * @param string $params
	 *
	 * @return array
	 */
	public static function build_from_stringified_params($params){
		$r = self::unwrap_stringified($params);

		$active_filters = $r['filters'];
		$current_values = $r['values'];

		return self::build_from_params($active_filters,$current_values);
	}

	/**
	 * Unwrap a stringified format
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public static function unwrap_stringified($params){
		$stringified_filters = explode("-",$params);

		$active_filters = [];
		$current_values = [];

		foreach ($stringified_filters as $filter_string){
			$filter_string_values = explode("|",$filter_string);
			$active_filters[$filter_string_values[0]] = [
				'slug' => $filter_string_values[0],
				'type' => $filter_string_values[1],
				'dataType' => $filter_string_values[2]
			];
			$current_values[$filter_string_values[0]] = explode(",",$filter_string_values[3]);
		}

		return [
			'filters' => $active_filters,
			'values' => $current_values
		];
	}

	/**
	 * Build a string that represent active filters and their values
	 *
	 * @param $active_filters
	 * @param $filter_values
	 *
	 * @return string
	 */
	public static function stringify_from_params($active_filters,$filter_values){
		$out =  "";
		$i = 0;
		foreach($active_filters as $filter_slug => $filter_params){
			if($i > 0){
				$out .= "-";
			}
			$out = $filter_slug."|".$filter_params['type']."|".$filter_params['dataType']."|";
			if(is_array($filter_values[$filter_slug])){
				$out .= implode(",",$filter_values[$filter_slug]);
			}else{
				$out .= $filter_values[$filter_slug];
			}
			$i++;
		}
		return $out;
	}

	/**
	 * Build a string that represent active filters and their values (starting from $_POST)
	 *
	 * @return string
	 */
	public static function stringify_from_post_params(){
		$active_filters = $_POST['wbwpf_active_filters'];
		$filter_current_values = call_user_func(function(){
			$posted_params = $_POST;
			$ignorelist = ["wbwpf_active_filters","wbwpf_search_by_filters"];
			$current_values = [];
			foreach ($posted_params as $param => $param_values){
				if(!in_array($param,$ignorelist)){
					$param = preg_replace("/wbwpf_/","",$param);
					$current_values[$param] = $param_values;
				}
			}
			return $current_values;
		});

		return self::stringify_from_params($active_filters,$filter_current_values);
	}
}