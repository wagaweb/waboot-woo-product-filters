<?php

namespace WBWPF\uitypes;

/*
 * Manage and output a select-type filter
 */
use WBF\components\mvc\HTMLView;
use WBWPF\includes\Filter_Query;

class Select extends ItemsList {
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
		usort($this->values,function($a,$b){
			return strcmp($a,$b);
		});

		$v = new HTMLView("src/views/uitypes/select.php","waboot-woo-product-filters");
		$output = $v->get([
			"values" => $this->values,
			"selected_values" => $this->selected_values,
			"hidden_values" => $this->hidden_values,
			"input_name" => $this->input_name
		]);
		return $output;
	}
}