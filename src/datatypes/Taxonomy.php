<?php

namespace WBWPF\datatypes;

class Taxonomy extends DataType{
	var $slug = "tax";

	function __construct() {
		parent::__construct();

		$this->label = __("Taxonomies","waboot-woo-product-filters");
		$this->admin_description = __("Select one or more taxonomies","waboot-woo-product-filters");
	}

	public function getData() {
		$taxonomies = [];
		//Gets all taxonomies
		$raw_taxonomies = get_taxonomies([],"objects");
		foreach ($raw_taxonomies as $tax){
			$taxonomies[$tax->name] = $tax->labels->name;
		}
		return $taxonomies;
	}

	/**
	 * Get the value for $key of $product_id. We retrieve terms id.
	 *
	 * @param $product_id
	 * @param $key
	 * @param int $format
	 *
	 * @return array|string
	 */
	public function getValueOf( $product_id, $key, $format = self::VALUES_FOR_FORMAT_COMMA_SEPARATED ) {
		$terms = [];
		$raw_terms = wp_get_post_terms($product_id,$key);
		if(is_array($raw_terms) && !empty($raw_terms)){
			$terms = wp_list_pluck($raw_terms,"term_id");
			if($format == self::VALUES_FOR_FORMAT_COMMA_SEPARATED){
				$terms = array_map(function($el){
					return "[".$el."]"; //To avoid issues with %LIKE% queries
				},$terms);
			}
		}
		if($format == self::VALUES_FOR_FORMAT_COMMA_SEPARATED){
			$terms = implode(",",$terms);
		}
		return $terms;
	}

	public function getAvailableValuesFor( $key ) {
		$values = [];

		$raw_values = parent::getAvailableValuesFor( $key );

		/*
		 * The commented parts are here for the case in which we used COMMA-INDEXING format during indexing.
		 * We are testing two method of indexing: multiple row per product (avoiding cols with comma separated item) and single row per product (otherwise)
		 */

		if(empty($raw_values)){
			$values = $raw_values;
		}else{
			/*foreach ($raw_values as $v){
				$v = explode(",",$v);
				$values = array_merge($values,$v);
			}
			$keys = array_unique($values);
			*/
			$keys = $raw_values;
			$values = call_user_func(function() use(&$keys){
				global $wpdb;
				$r = [];
				foreach ($keys as $k => $v){
					//$v = preg_replace("|[\[\]]|","",$v); //Remove [ ] added in getValueOf()
					$name = $wpdb->get_var("SELECT name FROM $wpdb->terms WHERE term_id = '$v'");
					if($name && $name != ""){
						$r[] = $name;
					}else{
						unset($keys[$k]); //There is no term with the id saved in the table
					}
				}
				return $r;
			});
			$values = array_combine($keys,$values);
		}

		return $values;
	}
}