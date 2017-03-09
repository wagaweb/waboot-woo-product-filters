<?php

namespace WBWPF\datatypes;

use WBWPF\includes\Filter;

class Attribute extends Taxonomy{
	/**
	 * Attribute constructor
	 *
	 * @param Filter|null $parent_filter
	 */
	function __construct(Filter &$parent_filter = null) {
		parent::__construct($parent_filter);
		$this->label = __("Attributes","waboot-woo-product-filters");
		$this->admin_description = __("Select one or more attributes","waboot-woo-product-filters");
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

		if(in_array($product_id,[23,24])){
			xdebug_break();
		}

		$post_type = get_post_type($product_id);

		if($post_type == "product_variation"){
			$raw_terms = [];
			$product = wc_get_product($product_id);
			$attributes = $product->get_variation_attributes();
			foreach ($attributes as $k => $v){ //eg: $k = attribute_pa_color ; $v = 'black'
				if($k == "attribute_".$key){ //We need to get the term by its slug
					$term = get_term_by("slug",$v,$key);
					if($term instanceof \WP_Term){
						$raw_terms[] = $term;
					}
					break;
				}
			}
		}else{
			$raw_terms = wp_get_post_terms($product_id,$key);
		}

		if(empty($raw_terms) && $post_type == "product_variation"){
			//If it is a variation, try to get the terms from the parent
			$parent_product_id = wp_get_post_parent_id($product_id);
			$raw_terms = wc_get_product_variation_attributes($parent_product_id);
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

	function getData() {
		$taxonomies = [];
		//Gets all taxonomies
		$raw_taxonomies = get_taxonomies([],"objects");
		foreach ($raw_taxonomies as $tax){
			if(!taxonomy_is_product_attribute($tax->name)) continue; //Skip not product attributes
			$taxonomies[$tax->name] = $tax->labels->name;
		}
		return $taxonomies;
	}
}