<?php

namespace WBWPF\uitypes;

abstract class UIType{
	/**
	 * @var string
	 */
	var $input_name;
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
	 * Generate the HTML output
	 *
	 * @param bool $input_name
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function generate_output($input_name = false){
		if(!$input_name){
			if(!$this->input_name) throw new \Exception("Unable to assign a name to the UIType.");
			$input_name = $this->input_name;
		}
		return implode(",",$this->values);
	}
}