<?php

namespace WBWPF\includes;

use WBWPF\Plugin;

class Filter_Factory{
	const WPWPF_QUERY_SEPARATOR = ";";

	/**
	 * Build a new filter instance
	 *
	 * @param $filterSlug
	 * @param $dataType_slug
	 * @param $uiType_slug
	 * @param string|array|null $values
	 *
	 * @return \WP_Error|bool|Filter
	 */
	public static function build($filterSlug,$dataType_slug,$uiType_slug,$values = null){
		try{
			$plugin = Plugin::get_instance_from_global();
			$dataTypes = $plugin->get_available_dataTypes();
			$uiTypes = $plugin->get_available_uiTypes();

			if(!isset($dataTypes[$dataType_slug])) return false; //todo: return WP_Error?
			$dataTypeClassName = $dataTypes[$dataType_slug];

			if(!isset($uiTypes[$uiType_slug])) return false; //todo: return WP_Error?
			$uiTypeClassName = $uiTypes[$uiType_slug];

			$dataType = new $dataTypeClassName();
			$uiType = new $uiTypeClassName();

			$f = new Filter($filterSlug,$dataType,$uiType);
			$f->uiType->set_name($filterSlug);

			if(isset($values)){
				$f->set_value($values);
			}else{
				//Guess the value from $_POST or $_GET
				if(isset($_GET['wbwpf_query']) && $_GET['wbwpf_query'] != ""){
					$r = self::parse_stringified_params($_GET['wbwpf_query']);
				}elseif(isset($_GET['wbwpf_search_by_filters']) || isset($_POST['wbwpf_search_by_filters'])){
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
		}catch(\Exception $e){
			return new \WP_Error("filter-build-error",$e->getMessage()); //todo: loggin?
		}
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
	 * Build an array of filter instances starting from an array of filter slugs
	 *
	 * @param array $slugs
	 * @param array $filter_values
	 *
	 * @return array
	 */
	public static function build_from_slugs($slugs,$filter_values = false){
		$params = self::complete_active_filters($slugs);
		return self::build_from_params($params,$filter_values);
	}

	/**
	 * Build a Filters array from available params. At the moment we use this builder mainly.
	 *
	 * @return array
	 */
	public static function build_from_available_params(){
		$result_filters = self::parse_available_params();

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
		if(!isset($_GET['wbwpf_query']) && !isset($_GET['wbwpf_search_by_filters'])) return [];

		/*
		 * It is possible to specify filters in $_GET in two formats: one called "stringfied", and one generated directly from the FORM.
		 * We are testing the two...
		 */
		if(isset($_GET['wbwpf_query']) && $_GET['wbwpf_query'] != ""){
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
		if(!isset($_POST['wbwpf_search_by_filters'])) return [];

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
	 * Parse all available params to build an array of filters and their values.
	 *
	 * @return array
	 */
	public static function parse_available_params(){
		global $wp_query;

		$result_filters = [];
		$detected_filters = [];

		if($wp_query instanceof \WP_Query){
			$detected_filters[] = self::parse_wp_query_params($wp_query); //Get from WP_Query (so we detect active filter in taxonomy archive pages)
		}
		$detected_filters[] = self::parse_get_or_post_params();

		$detected_filters = array_filter($detected_filters); //Remove FALSE or NULL values

		$detected_filters = apply_filters("wbwpf/filters/detected",$detected_filters); //todo: this filter could be specific for each parse function

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
			if(!isset($filters['filters'])) continue;
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

		/*
		 * @hooked Plugin->inject_wbwpf_query_params_into_detect_filters() . Here we parse wbwpf_query param, and alter the $result_filters accordingly
		 */
		$result_filters = apply_filters("wbwpf/filters/detected/parsed",$result_filters); //todo: could this filter be specific for each parse function?

		return $result_filters;
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
			'_origin' => 'wp_query',
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
		if(isset($_POST['wbwpf_search_by_filters'])){
			//$active_filters = $_POST['wbwpf_active_filters'];
			$use = "POST";
		}elseif(isset($_GET['wbwpf_search_by_filters'])){
			//$active_filters = $_GET['wbwpf_active_filters'];
			$use = "GET";
		}else{
			return false;
		}

		//if(!isset($active_filters) && !isset($use)) return false;
		$active_filters = [];

		$filter_current_values = call_user_func(function() use($use,&$active_filters){
			$posted_params = $use == "GET" ? $_GET : $_POST;

			$ignorelist = ["wbwpf_active_filters","wbwpf_search_by_filters","wbwpf_query"];

			$current_values = [];

			foreach ($posted_params as $param => $param_values){
				if(!in_array($param,$ignorelist) && preg_match("/wbwpf_/",$param)){
					$param = preg_replace("/wbwpf_/","",$param); //Here the "param" is a filter slug

					//Sanitization:
					$param = sanitize_text_field($param);
					foreach ($param_values as $param_key => $param_value){
						$param_values[$param_key] = sanitize_text_field($param_value);
					}

					if(!in_array($param,$active_filters)){
						$active_filters[] = $param;
					}

					$current_values[$param] = $param_values;
				}
			}

			return $current_values;
		});

		$active_filters = self::complete_active_filters($active_filters);

		return [
			'_origin' => $use,
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
		$stringified_filters = explode(self::WPWPF_QUERY_SEPARATOR,$params);

		$plugin = Plugin::get_instance_from_global();
		$settings = $plugin->get_plugin_settings();

		$active_filters = [];
		$current_values = [];

		foreach ($stringified_filters as $filter_string){
			$filter_string_values = explode("|",$filter_string);

			//Here we can have either (a):
			//0 => the slug
			//1 => the UIType
			//2 => the DataType
			//3 => the values
			//-- OR (b):
			//0 => the slug
			//2 => the values

			if(count($filter_string_values) < 2 || count($filter_string_values) > 4){
				continue; //It is neither of the above case
			}elseif(count($filter_string_values) > 2){
				$f_slug = $filter_string_values[0];
				$f_uiType = $filter_string_values[1];
				$f_dataType = $filter_string_values[2];
				$f_value = isset($filter_string_values[3]) ? $filter_string_values[3] : "";
			}else{
				//Let's assume we are in the b case
				$f_slug = $filter_string_values[0];
				$f_uiType = $settings['filters_params'][$f_slug]['uiType'];
				$f_dataType = $settings['filters_params'][$f_slug]['dataType'];
				$f_value = $filter_string_values[1];
			}

			if($f_value == ""){
				$f_value = null;
			}else{
				$f_value = explode(",",$f_value);
				//Sanitization:
				if(is_array($f_value)){
					foreach ($f_value as $k => $v){
						$f_value[$k] = sanitize_text_field($v);
					}
				}else{
					$f_value = sanitize_text_field($f_value);
				}
			}

			$current_values[$f_slug] = $f_value;
			$active_filters[$f_slug] = [
				'slug' => $f_slug,
				'type' => $f_uiType,
				'dataType' => $f_dataType
			];
		}

		return [
			'_origin' => 'string',
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
				'_origin' => 'provided',
				'filters' => $active_filters,
				'values' => $current_values
			];
		}else{
			return false;
		}
	}

	/**
	 * Build a query string from specific params
	 *
	 * THIS IS TO TEST! NEVER USED AT THE MOMENT.
	 *
	 * @param $active_filters
	 * @param $filter_values
	 * @param bool $return_args
	 *
	 * @return array|string
	 */
	public static function querystring_from_params($active_filters,$filter_values,$return_args = false){
		$qs['wbwpf_active_filters'] = 1;

		foreach ($active_filters as $filter_slug){
			if(isset($filter_values[$filter_slug])){
				if(is_array($filter_values[$filter_slug])){
					foreach ($filter_values[$filter_slug] as $value){
						$qs[$filter_slug."[]"] = $value;
					}
				}else{
					$qs[$filter_slug] = $filter_values[$filter_slug];
				}
			}
		}

		if($return_args){
			return $qs;
		}else{
			return "?".implode("&",$qs);
		}
	}

	/**
	 * Build a string that represent active filters and their values
	 *
	 * @param $active_filters
	 * @param $filter_values
	 * @param bool $empty_for_no_values
	 *
	 * @return string
	 */
	public static function stringify_from_params($active_filters,$filter_values,$empty_for_no_values = false){
		$out =  "";
		$i = 0;
		foreach($active_filters as $filter_slug => $filter_params){
			if($empty_for_no_values){
				if(!isset($filter_values[$filter_slug]) || empty($filter_values[$filter_slug])){
					$out .= "";
					continue;
				}
			}
			if($i > 0){
				$out .= ";";
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

		$strings_from_post = explode(self::WPWPF_QUERY_SEPARATOR,self::stringify_from_post_params());
		$strings_from_get = explode(self::WPWPF_QUERY_SEPARATOR,self::stringify_from_get_params());

		$strings = array_merge((array) $strings_from_post, (array) $strings_from_get);
		$strings = array_filter(array_unique($strings));

		if(is_array($strings)){
			$string = implode(self::WPWPF_QUERY_SEPARATOR,$strings);
			$string = ltrim($string,self::WPWPF_QUERY_SEPARATOR);
			//$string = ltrim($string,"-");
			//$string = ltrim($string,"-");
		}

		return $string;
	}

	/**
	 * Build a string that represent active filters and their values (starting from $_POST)
	 *
	 * @return string
	 */
	public static function stringify_from_post_params(){
		if(!isset($_POST['wbwpf_search_by_filters'])) return "";

		$active_filters = [];

		$filter_current_values = call_user_func(function() use(&$active_filters){
			$posted_params = $_POST;
			$ignorelist = ["wbwpf_active_filters","wbwpf_search_by_filters","wbwpf_query"];
			$current_values = [];
			foreach ($posted_params as $param => $param_values){
				if(!in_array($param,$ignorelist)){
					$param = preg_replace("/wbwpf_/","",$param);

					if(!in_array($param,$active_filters)){
						$active_filters[] = $param;
					}

					$current_values[$param] = $param_values;
				}
			}
			return $current_values;
		});

		$active_filters = self::complete_active_filters($active_filters);

		return self::stringify_from_params($active_filters,$filter_current_values);
	}

	/**
	 * Build a string that represent active filters and their values (starting from $_POST)
	 *
	 * @return string
	 */
	public static function stringify_from_get_params(){
		if(!isset($_GET['wbwpf_search_by_filters'])) return "";

		$active_filters = [];

		$filter_current_values = call_user_func(function() use(&$active_filters){
			$posted_params = $_GET;
			$ignorelist = ["wbwpf_active_filters","wbwpf_search_by_filters","wbwpf_query"];
			$current_values = [];
			foreach ($posted_params as $param => $param_values){
				if(!in_array($param,$ignorelist)){
					$param = preg_replace("/wbwpf_/","",$param);
					$current_values[$param] = $param_values;

					if(!in_array($param,$active_filters)){
						$active_filters[] = $param;
					}
				}
			}
			return $current_values;
		});

		$active_filters = self::complete_active_filters($active_filters);

		return self::stringify_from_params($active_filters,$filter_current_values);
	}

	/**
	 * Takes an array of active filters slug, completes with other necessary data
	 *
	 * @param array $active_filters
	 *
	 * @return array
	 */
	private static function complete_active_filters($active_filters){
		$plugin = Plugin::get_instance_from_global();
		$settings = $plugin->get_plugin_settings();
		$completed_active_filters = [];
		foreach ($active_filters as $slug){
			if(!isset($settings['filters_params']) || !isset($settings['filters_params'][$slug])) continue;

			$completed_active_filters[$slug] = [
				'slug' => $slug,
				'dataType' => $settings['filters_params'][$slug]['dataType'],
				'type' => $settings['filters_params'][$slug]['uiType']
			];
		}

		return $completed_active_filters;
	}
}