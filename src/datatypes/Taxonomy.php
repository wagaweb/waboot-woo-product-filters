<?php

namespace WBWPF\datatypes;

class Taxonomy extends DataType{
	var $slug = "tax";

	function __construct() {
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

	public function getValueOf( $product_id, $key ) {
		$terms = [];
		$raw_terms = wp_get_post_terms($product_id,$key);
		if(is_array($raw_terms) && !empty($raw_terms)){
			$terms = wp_list_pluck($raw_terms,"slug");
		}
		$terms = implode(",",$terms);
		return $terms;
	}
}