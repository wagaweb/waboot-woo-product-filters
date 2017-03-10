<?php

namespace WBWPF\uitypes;

/*
 * Manage and output a range-type filter
 */
use WBF\components\mvc\HTMLView;
use WBWPF\includes\Filter_Query;
use WBWPF\Plugin;

class Range extends UIType {
	/**
	 * @var int|float
	 */
	var $min;
	/**
	 * @var int|float
	 */
	var $max;

	/**
	 * Set the values
	 *
	 * @param array $values
	 */
	public function set_values(array $values){

		//Strip non-numeric values
		$values = array_filter($values,function($item){
			return is_numeric($item);
		});

		$this->min = min($values);
		$this->max = max($values);

		$this->values = $values;
	}

	/**
	 * Generate the output HTML
	 *
	 * @param bool $input_name
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function generate_output($input_name = false) {
		$this->adjust_min_max_values();

		$v = new HTMLView("src/views/uitypes/range.php","waboot-woo-product-filters");
		$output = $v->get([
			"values" => $this->values,
			"selected_values" => $this->selected_values,
			"range_current_min" => $this->min,
			"range_current_max" => $this->max,
			"input_name" => $this->input_name
		]);
		return $output;
	}

	/**
	 * Adjust min and max values accordingly to current queried objects
	 */
	public function adjust_min_max_values(){
		$wbwpf_query = Plugin::get_query_from_global();

		if(!$wbwpf_query instanceof Filter_Query || !isset($wbwpf_query->available_col_values[$this->name])) return;

		$current_values = $wbwpf_query->available_col_values[$this->name];

		//Strip non-numeric values
		$current_values = array_filter($current_values,function($item){
			return is_numeric($item);
		});

		$this->min = min($current_values);
		$this->max = max($current_values);
	}
}