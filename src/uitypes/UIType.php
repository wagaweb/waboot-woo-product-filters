<?php

namespace WBWPF\uitypes;

use WBF\components\pluginsframework\BasePlugin;
use WBWPF\Plugin;

abstract class UIType{
	/**
	 * @var string
	 */
	var $type_slug;
	/**
	 * @var string
	 */
	var $name;
	/**
	 * @var
	 */
	var $input_name;
	/**
	 * @var array
	 */
	var $values = [];

	public function __construct() {
		$plugin = Plugin::get_instance_from_global();
		$uiTypes = $plugin->get_available_uiTypes();
		foreach ($uiTypes as $type_slug => $classname){
			if($classname == static::class){
				$this->type_slug = $type_slug;
				break;
			}
		}
	}

	public function set_name($name){
		$this->name = $name;
		$this->input_name = "wbwpf_".$this->name;
	}

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
	 * @return string
	 * @throws \Exception
	 */
	public function generate_output(){
		return implode(",",$this->values);
	}
}