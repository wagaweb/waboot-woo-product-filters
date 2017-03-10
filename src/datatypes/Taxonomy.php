<?php

namespace WBWPF\datatypes;

use WBWPF\includes\Filter;

class Taxonomy extends DataType{
	/**
	 * Taxonomy constructor
	 *
	 * @param Filter|null $parent_filter
	 */
	function __construct(Filter &$parent_filter = null) {
		parent::__construct($parent_filter);
		$this->label = __("Taxonomies","waboot-woo-product-filters");
		$this->admin_description = __("Select one or more taxonomies","waboot-woo-product-filters");
	}

	/**
	 * @return array
	 */
	public function getData() {
		$taxonomies = [];
		//Gets all taxonomies
		$raw_taxonomies = get_taxonomies([],"objects");
		foreach ($raw_taxonomies as $tax){
			if(taxonomy_is_product_attribute($tax->name)) continue; //Skip product attributes
			$taxonomies[$tax->name] = $tax->labels->name;
		}
		return $taxonomies;
	}

	/**
	 * @param $key
	 * @param Filter|null $parent_filter
	 *
	 * @return string
	 */
	public function getPublicLabelOf( $key, Filter $parent_filter = null ) {
		global $wpdb;
		$taxonomy = get_taxonomy($key);
		$label = $taxonomy->label;
		$label = apply_filters("wbwpf/datatype/label",$label,$this);
		return $label;
	}

	/**
	 * @param $key
	 * @param Filter|null $parent_filter
	 *
	 * @return string
	 */
	public function getPublicItemLabelOf( $key, Filter $parent_filter = null ){
		$term = parent::getPublicItemLabelOf($key);

		if(!isset($parent_filter) && isset($this->parent_filter)){
			$parent_filter = $this->parent_filter;
		}

		if(!isset($parent_filter)) return $term;

		$term = get_term_by("id",$key,$parent_filter->slug);

		if($term instanceof \WP_Term){
			$term = $term->name;
		}

		return $term;
	}

	/**
	 * Get the value for $key of $product_id. We retrieve terms id.
	 *
	 * @param $product_id
	 * @param $key
	 * @param int $format
	 * @param Filter|null $parent_filter
	 *
	 * @return array|string
	 */
	public function getValueOf( $product_id, $key, $format = self::VALUES_FOR_FORMAT_COMMA_SEPARATED, Filter $parent_filter = null ) {
		$terms = [];

		$post_type = get_post_type($product_id);

		$raw_terms = wp_get_post_terms($product_id,$key);
		if(empty($raw_terms) && $post_type == "product_variation"){
			//If it is a variation, try to get the terms from the parent
			$parent_product_id = wp_get_post_parent_id($product_id);
			$raw_terms = wp_get_post_terms($parent_product_id,$key);
		}

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

	/**
	 * @param $key
	 * @param Filter|null $parent_filter
	 *
	 * @return array|mixed
	 */
	public function getAvailableValuesFor( $key, Filter $parent_filter = null ) {
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