<?php

namespace WBWPF\datatypes;

use WBWPF\Plugin;

abstract class DataType{
	/**
	 * @var string
	 */
	var $label = "";
	/**
	 * @var string
	 */
	var $slug = "";
	/**
	 * @var string
	 */
	var $admin_description = "";
	/**
	 * Return valid values for the data type
	 *
	 * @return array
	 */
	public function getData(){
		return [];
	}
	/**
	 * Return the value for $product_id for data type called $key (eg: the value of "product_cat" for a specified product)
	 *
	 * @param $product_id
	 * @param $key
	 *
	 * @return mixed
	 */
	public function getValueOf($product_id,$key){
		return "";
	}

	/**
	 * Get all possible value of current data type for the key called $key. By default it uses the indexed values on the custom table.
	 *
	 * @param $key
	 *
	 * @return array
	 */
	public function getAvailableValuesFor($key){
		global $wpdb;
		$table_name = $wpdb->prefix.Plugin::CUSTOM_PRODUCT_INDEX_TABLE;
		$values = $wpdb->get_col("SELECT DISTINCT $key FROM $table_name");
		return $values;
	}
}