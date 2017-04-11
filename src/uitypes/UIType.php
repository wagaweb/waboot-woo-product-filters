<?php

namespace WBWPF\uitypes;

use WBF\components\pluginsframework\BasePlugin;
use WBWPF\includes\Filter;
use WBWPF\includes\Filter_Query;
use WBWPF\Plugin;

abstract class UIType{
	/**
	 * @var Filter
	 */
	var $parent_filter;
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
	 * @var array the available values to display
	 */
	var $values = [];
	/**
	 * @var array the values currently selected
	 */
	var $selected_values = [];

	/**
	 * UIType constructor.
	 *
	 * @param Filter|null $parent_filter
	 */
	public function __construct(Filter &$parent_filter = null) {
		$plugin = Plugin::get_instance_from_global();
		$uiTypes = $plugin->get_available_uiTypes();
		foreach ($uiTypes as $type_slug => $classname){
			if($classname == static::class){
				$this->type_slug = $type_slug;
				break;
			}
		}

		if(isset($parent_filter)) $this->parent_filter = $parent_filter;
	}

	/**
	 * @param Filter $parent_filter
	 */
	public function setParentFilter(Filter &$parent_filter){
		$this->parent_filter = $parent_filter;
	}

	/**
	 * @param $name
	 */
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

	/**
	 * Generate the HTML template for output (used for vue async filters)
	 */
	public function generate_vue_template(){
		return "<ul><li v-for='item in items'>{{ item }}</li></ul>";
	}
}