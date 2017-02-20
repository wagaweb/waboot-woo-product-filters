<?php

namespace WBWPF\datatypes;

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
}