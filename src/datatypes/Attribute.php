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