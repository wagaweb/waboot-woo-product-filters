<?php

namespace WBWPF\datatypes;

class Meta extends DataType {
	var $slug = "meta";

	function __construct() {
		$this->label = __("Product metas","waboot-woo-product-filters");
		$this->admin_description = __("Select one or more metas","waboot-woo-product-filters");
	}

	public function getData() {
		global $wpdb;
		$metas = $wpdb->get_col("SELECT meta_key FROM $wpdb->postmeta as postmeta JOIN $wpdb->posts as posts ON postmeta.post_id = posts.ID WHERE post_type = 'product'");
		$metas = array_unique($metas);
		return $metas;
	}
}