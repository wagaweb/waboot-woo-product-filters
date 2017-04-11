<?php

namespace WBWPF\uitypes;

/*
 * Manage and output a checkbox-type filter
 */
use WBF\components\mvc\HTMLView;
use WBWPF\includes\Filter_Query;

class Checkbox extends ItemsList {
	var $selected_values = [];

	/**
	 * Generate the output HTML
	 *
	 * @param bool $input_name
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function generate_output($input_name = false) {
		parent::check_for_hidden_values();

		//Sort alphabetically by label
		uasort($this->values,function($a,$b){
			return strcmp($a,$b);
		});

		$v = new HTMLView("src/views/uitypes/checkbox.php","waboot-woo-product-filters");
		$output = $v->get([
			"values" => $this->values,
			"selected_values" => $this->selected_values,
			"hidden_values" => $this->hidden_values,
			"input_name" => $this->input_name
		]);
		return $output;
	}

	/**
	 * Generate the template for async use of this UIType
	 */
	public function generate_vue_template() {
		$v = new HTMLView("src/views/uitypes/async-checkbox.php","waboot-woo-product-filters");
		$output = $v->get([
			"input_name" => $this->input_name
		]);
		return $output;
	}
}