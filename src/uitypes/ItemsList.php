<?php

namespace WBWPF\uitypes;

use WBWPF\includes\Filter_Query;

/**
 * Class ItemsList
 *
 * This UIType represent a list of items (eg: select and checkboxes)
 *
 * @package WBWPF\uitypes
 */
abstract class ItemsList extends UIType {
	/**
	 * The values that has to be hidden
	 *
	 * @var array
	 */
	var $hidden_values = [];

	/**
	 * Generate the HTML output
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function generate_output(){
		$this->check_for_hidden_values();
		return implode(",",$this->values);
	}

	/**
	 * Check if some values has to be display hidden
	 */
	public function check_for_hidden_values(){
		global $wbwpf_query_instance;

		if(isset($wbwpf_query_instance) && $wbwpf_query_instance instanceof Filter_Query && isset($wbwpf_query_instance->available_col_values[$this->name])){
			foreach ($this->values as $k => $value){
				$hide = !in_array($k,$wbwpf_query_instance->available_col_values[$this->name]); //todo: maybe a filter, later
				if($hide){
					$this->hidden_values[$k] = $value;
				}
			}
		}
	}
}