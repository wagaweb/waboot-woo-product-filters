<?php

namespace WBWPF\uitypes;

abstract class UIType{
	/**
	 * @var array
	 */
	var $values = [];

	/**
	 * Set the values
	 *
	 * @param array $values
	 */
	public function set_values(array $values){
		$this->values = $values;
	}

	/**
	 * Display the HTML for the filter
	 *
	 * @return string
	 */
	public function display(){
		return implode(",",$this->values);
	}
}