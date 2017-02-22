<?php

namespace WBWPF\includes;

use WBWPF\datatypes\DataType;
use WBWPF\uitypes\UIType;

class Filter{
	/**
	 * @var UIType
	 */
	var $uiType;
	/**
	 * @var DataType
	 */
	var $dataType;
	/**
	 * @var string the filter slug (eg: "product_cat")
	 */
	var $slug;
	/**
	 * @var array the current selected values of the filter
	 */
	var $current_values;

	/**
	 * Filter constructor.
	 *
	 * @param $slug
	 * @param DataType $dataType
	 * @param UIType $uiType
	 */
	function __construct($slug,DataType $dataType,UIType $uiType) {
		$this->slug = $slug;
		$this->dataType = $dataType;
		$this->uiType = $uiType;
	}

	/**
	 * Complete the $query (passed by reference)
	 *
	 * @param Filter_Query $query
	 */
	function parse_query(Filter_Query &$query){
		$statement = implode(" OR $this->slug = ",$this->current_values);
		$statement = "$this->slug = ".$statement;
		$query->where_statements[] = $statement;
	}

	/**
	 * Set the current value
	 *
	 * @param mixed $value
	 */
	function set_value($value){
		if(!is_array($value)){
			$value = [$value];
		}
		$this->current_values = $value;
	}

	/**
	 * Display the filter
	 *
	 * @return void
	 */
	function display(){
		$values = $this->dataType->getAvailableValuesFor($this->slug);
		$this->uiType->set_name($this->slug);
		$this->uiType->set_values($values);
		$output = $this->uiType->generate_output();

		//Adds hidden input to output
		$output .= "<input type='hidden' name='wbwpf_active_filters[{$this->slug}][slug]' value='{$this->slug}'>";
		$output .= "<input type='hidden' name='wbwpf_active_filters[{$this->slug}][type]' value='{$this->uiType->type_slug}'>";
		$output .= "<input type='hidden' name='wbwpf_active_filters[{$this->slug}][dataType]' value='{$this->dataType->type_slug}'>";

		echo $output;
	}
}