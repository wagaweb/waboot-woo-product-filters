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
}