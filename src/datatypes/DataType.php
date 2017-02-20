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
}