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
	 * Display the filter
	 *
	 * @return void
	 */
	function display(){
		$values = $this->dataType->getAvailableValuesFor($this->slug);
		$this->uiType->set_values($values);
		$output = $this->uiType->generate_output();
		echo $output;
	}
}