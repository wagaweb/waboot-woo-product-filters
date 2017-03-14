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
	 * @param string|array|null $values
	 *
	 * @return bool|Filter
	 */
	public static function build($filterSlug,$dataType_slug,$uiType_slug,$values = null){
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

		if(isset($values)){
			$f->set_value($values);
		}else{
			//Guess the value from $_POST or $_GET
			if(isset($_GET['wbwpf_query'])){
				$r = self::parse_stringified_params($_GET['wbwpf_query']);
			}elseif(isset($_GET['wbwpf_active_filters']) || isset($_POST['wbwpf_active_filters'])){
				/*
				 * It is possible to specify filters in $_GET in two formats: one called "stringfied", and one generated directly from the FORM.
				 * We are testing the two...
				 */
				$r = self::parse_get_or_post_params();
			}else{
				$r = self::parse_wp_query_params();
			}
			if(isset($r) && isset($r['values']) && isset($r['values'][$filterSlug])){
				$values = $r['values'][$filterSlug];
				$f->set_value($values);
			}
		}

		return $f;
	}

	/**
	 * Build an array of filter instances
	 *
	 * @param $params
	 *
	 * @param array|bool|FALSE $filter_values if provided, the filters will be assigned with these values
	 *
	 * @example:
	 *
	 * $filter_params = [
	 *      'product_cat' => [
	 *          'slug' => 'product_cat',
	 *          'type  => 'checkbox',
	 *          'dataType => 'taxonomy'
 	 *      ]
	 *      ...
	 * ]
	 *
	 * $filter_values = [
	 *      'product_cat' => [12,13]
	 *      ...
	 * ]
	 *
	 * @return array
	 */
	public static function build_from_params($params,$filter_values = false){
		$filters = [];

		foreach ($params as $filter_slug => $filter_params){
			if(!isset($filter_params['dataType']) || !isset($filter_params['type'])) continue;

			$dataType_slug = $filter_params['dataType'];
			$uiType_slug = $filter_params['type'];

			if(is_array($filter_values) && isset($filter_values[$filter_slug])){
				$f = self::build($filter_slug,$dataType_slug,$uiType_slug,$filter_values[$filter_slug]); //Build and assign value
			}else{
				$f = self::build($filter_slug,$dataType_slug,$uiType_slug); //Build without assigning value
			}

			if($f instanceof Filter){
				$filters[] = $f;
			}
		}

		return $filters;
	}

	/**
	 * Build a Filters array from available params. At the moment we use this builder mainly.
	 *
	 * @return array
	 */
	public static function build_from_available_params(){
		global $wp_query;

		$result_filters = [];
		$detected_filters = [];

		if($wp_query instanceof \WP_Query){
			$detected_filters[] = self::parse_wp_query_params($wp_query); //Get from WP_Query (so we detect active filter in taxonomy archive pages)
		}
		$detected_filters[] = self::parse_get_or_post_params();

		$detected_filters = array_filter($detected_filters); //Remove FALSE or NULL values

		/*
		 * Now we have to merge the detected filters. We need to build an array like this:
		 *
		 * [
		 *      'filters' => [
		 *          'product_cat' => [
		 *              'slug' => ....
		 *              'type' => ....
		 *              'dataType => ....
		 *           ]
		 *      ]
		 *      'values' => [
		 *          'product_cat' => [...]
		 *      ]
		 * ]
		 *
		 */

		//todo: can we FURTHER optimize this cycle?
		foreach ($detected_filters as $filters){
			if(isset($filters['filters'])){
				foreach ($filters['filters'] as $filter_slug => $filter_params){
					//Get types
					if(!isset($result_filters['filters'][$filter_slug])){
						$result_filters['filters'][$filter_slug] = $filter_params;
					}
					//Get values
					if(isset($filters['values'][$filter_slug])){
						$filter_values = $filters['values'][$filter_slug];
						if(!isset($result_filters['values'][$filter_slug])){
							$result_filters['values'][$filter_slug] = $filter_values;
						}else{
							$result_filters['values'][$filter_slug] = array_merge($result_filters['values'][$filter_slug],$filter_values);
							$result_filters['values'][$filter_slug] = array_unique($result_filters['values'][$filter_slug]);
						}
					}
				}
			}
		}

		//The wbwpf_query param can further filter the generated detected filters array; namely, we:
		// - remove from $result_filters the filters not present in wbwpf_query)
		// - overwrite any values in $result_filters with wbwpf_query values
		if(isset($_GET['wbwpf_query'])){
			$wrapped_params = $_GET['wbwpf_query'];
			$unwrapped_filters = self::parse_stringified_params($wrapped_params);
			foreach ($result_filters['filters'] as $filter_slug => $filter_params){
				if(!isset($unwrapped_filters['filters'][$filter_slug])){
					unset($result_filters['filters'][$filter_slug]);
					if(isset($result_filters['values'][$filter_slug])){
						unset($result_filters['values'][$filter_slug]);
					}
				}
				if(isset($unwrapped_filters['values'][$filter_slug])){
					$result_filters['values'][$filter_slug] = $unwrapped_filters['values'][$filter_slug];
				}else{
					if(isset($result_filters['values'][$filter_slug])){
						unset($result_filters['values'][$filter_slug]);
					}
				}
			}
		}

		if(is_array($result_filters) && !empty($result_filters)){
			//Finally build them into an array of Filters
			$active_filters = $result_filters['filters'];
			if(isset($result_filters['values'])){
				$filter_current_values = $result_filters['values'];
				return self::build_from_params($active_filters,$filter_current_values);
			}else{
				return self::build_from_params($active_filters);
			}
		}else{
			return [];
		}
	}

	/**
	 * Build a Filters array from standardized get params
	 *
	 * @return array
	 */
	public static function build_from_get_params(){
		if(!isset($_GET['wbwpf_query']) && !isset($_GET['wbwpf_active_filters'])) return [];

		/*
		 * It is possible to specify filters in $_GET in two formats: one called "stringfied", and one generated directly from the FORM.
		 * We are testing the two...
		 */
		if(isset($_GET['wbwpf_query'])){
			$params = $_GET['wbwpf_query'];
			$r = self::parse_stringified_params($params);
		}else{
			$r = self::parse_get_or_post_params();
		}

		if($r){
			$active_filters = $r['filters'];
			$filter_current_values = $r['values'];
			return self::build_from_params($active_filters,$filter_current_values);
		}

		return [];
	}

	/**
	 * Build a Filters array from standardized post params
	 *
	 * @return array
	 */
	public static function build_from_post_params(){
		if(!isset($_POST['wbwpf_active_filters'])) return [];

		$r = self::parse_get_or_post_params();

		if($r){
			$active_filters = $r['filters'];
			$filter_current_values = $r['values'];
			return self::build_from_params($active_filters,$filter_current_values);
		}

		return [];
	}

	/**
	 * Build a Filters array from WP_Query params
	 *
	 * @return array
	 */
	public static function build_from_wp_query(\WP_Query $query){
		if(!is_product_taxonomy()){
			return []; //We are in main shop page, we do not need to apply any filters
		}else{
			$r = self::parse_wp_query_params();

			if($r){
				$active_filters = $r['filters'];
				$filter_current_values = $r['values'];
				return self::build_from_params($active_filters,$filter_current_values);
			}

			return [];
		}
	}

	/**
	 * Build an array of filters from a string format
	 *
	 * @param string $params
	 *
	 * @return array
	 */
	public static function build_from_stringified_params($params){
		$r = self::parse_stringified_params($params);

		$active_filters = $r['filters'];
		$current_values = $r['values'];

		return self::build_from_params($active_filters,$current_values);
	}

	/**
	 * Get filters and their values from WP_Query. The returned array will look like this:
	 *
	 * [
	 *      'filters' => [
	 *          'product_cat' => [
	 *              'slug' => ....
	 *              'type' => ....
	 *              'dataType => ....
	 *           ]
	 *      ]
	 *      'values' => [
	 *          'product_cat' => [...]
	 *      ]
	 * ]
	 *
	 * @param \WP_Query|null $query
	 *
	 * @return bool|array
	 */
	public static function parse_wp_query_params(\WP_Query $query = null){
		if(!isset($query)){
			global $wp_query;
			$query = $wp_query;
		}

		if(!$query instanceof \WP_Query) return false;

		$plugin = \WBWPF\Plugin::get_instance_from_global();
		$dataTypes = $plugin->get_available_dataTypes();
		$uiTypes = $plugin->get_available_uiTypes();
		$settings = $plugin->get_plugin_settings();

		$active_filters = [];
		$filter_current_values = [];

		$queried_object = $query->get_queried_object();

		/*
		 * todo: how many case we have to check?
		 */

		if($queried_object instanceof \WP_Term){
			if(array_key_exists($queried_object->taxonomy,$settings['filters_params'])){ //Proceed if the user as indexed the taxonomy
				$active_filters = [
					$queried_object->taxonomy => [
						'slug' => $queried_object->taxonomy,
						'type' => $settings['filters_params'][$queried_object->taxonomy]['uiType'],
						'dataType' => $settings['filters_params'][$queried_object->taxonomy]['dataType']
					]
				];
				$filter_current_values = [
					$queried_object->taxonomy => [$queried_object->term_id]
				];
			}
		}

		return [
			'filters' => $active_filters,
			'values' => $filter_current_values
		];
	}

	/**
	 * Get filters and their values from $_POST or $_GET. Return FALSE on error. The returned array will look like this:
	 *
	 * [
	 *      'filters' => [
	 *          'product_cat' => [
	 *              'slug' => ....
	 *              'type' => ....
	 *              'dataType => ....
	 *           ]
	 *      ]
	 *      'values' => [
	 *          'product_cat' => [...]
	 *      ]
	 * ]
	 *
	 * return bool|array
	 */
	public static function parse_get_or_post_params(){
		if(isset($_POST['wbwpf_active_filters'])){
			$active_filters = $_POST['wbwpf_active_filters'];
			$use = "POST";
		}elseif(isset($_GET['wbwpf_active_filters'])){
			$active_filters = $_GET['wbwpf_active_filters'];
			$use = "GET";
		}

		if(!isset($active_filters) && !isset($use)) return false;

		$filter_current_values = call_user_func(function() use($use){
			$posted_params = $use == "GET" ? $_GET : $_POST;

			$ignorelist = ["wbwpf_active_filters","wbwpf_search_by_filters","wbwpf_query"];

			$current_values = [];

			foreach ($posted_params as $param => $param_values){
				if(!in_array($param,$ignorelist) && preg_match("/wbwpf_/",$param)){
					$param = preg_replace("/wbwpf_/","",$param);

					//Sanitization:
					$param = sanitize_text_field($param);
					foreach ($param_values as $param_key => $param_value){
						$param_values[$param_key] = sanitize_text_field($param_value);
					}

					$current_values[$param] = $param_values;
				}
			}

			return $current_values;
		});

		return [
			'filters' => $active_filters,
			'values' => $filter_current_values
		];

	}

	/**
	 * Unwrap a stringified format. The returned array will look like this:
	 *
	 * [
	 *      'filters' => [
	 *          'product_cat' => [
	 *              'slug' => ....
	 *              'type' => ....
	 *              'dataType => ....
	 *           ]
	 *      ]
	 *      'values' => [
	 *          'product_cat' => [...]
	 *      ]
	 * ]
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public static function parse_stringified_params($params){
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
			if($filter_string_values[3] == ""){
				$current_values[$filter_string_values[0]] = null; //todo: move it to another place?
			}else{
				$the_filter_value = explode(",",$filter_string_values[3]);

				//Sanitization:
				$the_filter_value = sanitize_text_field($the_filter_value);

				$current_values[$filter_string_values[0]] = $the_filter_value;
			}
		}

		return [
			'filters' => $active_filters,
			'values' => $current_values
		];
	}

	/**
	 * Get filters and their values from an array of Filters. Return FALSE on error. The returned array will look like this:
	 *
	 * [
	 *      'filters' => [
	 *          'product_cat' => [
	 *              'slug' => ....
	 *              'type' => ....
	 *              'dataType => ....
	 *           ]
	 *      ]
	 *      'values' => [
	 *          'product_cat' => [...]
	 *      ]
	 * ]
	 *
	 * @param array
	 *
	 * @return bool|array
	 */
	public static function parse_filters_array($filters){
		$active_filters = [];
		$current_values = [];

		foreach ($filters as $filter){
			if(!$filter instanceof Filter) continue;
			$active_filters[$filter->slug] = [
				'slug' => $filter->slug,
				'type' => $filter->uiType->type_slug,
				'dataType' => $filter->dataType->type_slug
			];
			if(isset($filter->current_values)){
				$current_values[$filter->slug] = $filter->current_values;
			}
		}

		if(!empty($active_filters)){
			return [
				'filters' => $active_filters,
				'values' => $current_values
			];
		}else{
			return false;
		}
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
			$out .= $filter_slug."|".$filter_params['type']."|".$filter_params['dataType']."|";
			if(isset($filter_values[$filter_slug])){
				if(is_array($filter_values[$filter_slug])){
					$out .= implode(",",$filter_values[$filter_slug]);
				}else{
					$out .= $filter_values[$filter_slug];
				}
			}
			$i++;
		}
		return $out;
	}

	/**
	 * Build a string that represent active filters and their values (starting from any $_POST or $_GET params)
	 */
	public static function stringify_from_available_params(){
		$string = "";

		$strings_from_post = explode("-",self::stringify_from_post_params());
		$strings_from_get = explode("-",self::stringify_from_get_params());

		$strings = array_merge((array) $strings_from_post, (array) $strings_from_get);
		$strings = array_filter(array_unique($strings));

		if(is_array($strings)){
			$string = implode("-",$strings);
			$string = ltrim($string,"-");
		}

		return $string;
	}

	/**
	 * Build a string that represent active filters and their values (starting from $_POST)
	 *
	 * @return string
	 */
	public static function stringify_from_post_params(){
		if(!isset($_POST['wbwpf_active_filters'])) return "";

		$active_filters = $_POST['wbwpf_active_filters'];
		$filter_current_values = call_user_func(function(){
			$posted_params = $_POST;
			$ignorelist = ["wbwpf_active_filters","wbwpf_search_by_filters","wbwpf_query"];
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

	/**
	 * Build a string that represent active filters and their values (starting from $_POST)
	 *
	 * @return string
	 */
	public static function stringify_from_get_params(){
		if(!isset($_GET['wbwpf_active_filters'])) return "";

		$active_filters = $_GET['wbwpf_active_filters'];
		$filter_current_values = call_user_func(function(){
			$posted_params = $_GET;
			$ignorelist = ["wbwpf_active_filters","wbwpf_search_by_filters","wbwpf_query"];
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