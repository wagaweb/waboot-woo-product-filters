<?php

namespace WBWPF\uitypes;

/*
 * Manage and output a checkbox-type filter
 */
use WBF\components\mvc\HTMLView;

class Checkbox extends UIType {
	/**
	 * Generate the output HTML
	 *
	 * @param bool $input_name
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function generate_output($input_name = false) {
		if(!$input_name){
			if(!$this->input_name) throw new \Exception("Unable to assign a name to the UIType.");
			$input_name = $this->input_name;
		}else{
			$this->input_name = $input_name;
		}
		$this->input_name = "wbwpf_".$this->input_name;

		$v = new HTMLView("src/views/uitypes/checkbox.php","waboot-woo-product-filters");
		$output = $v->get([
			"values" => $this->values,
			"input_name" => $this->input_name
		]);
		return $output;
	}
}