<?php

namespace WBWPF\includes;

use WBWPF\datatypes\DataType;
use WBWPF\uitypes\UIType;

class Filter{
	/**
	 * @var UIType
	 */
	var $uitype;
	/**
	 * @var DataType
	 */
	var $datatype;
	/**
	 * @var string the filter slug (eg: "product_cat")
	 */
	var $slug;

	/**
	 * Filter constructor.
	 *
	 * @param $slug
	 * @param DataType $datatype
	 * @param UIType $uitype
	 */
	function __construct($slug,DataType $datatype,UIType $uitype) {
		$this->slug = $slug;
		$this->datatype = $datatype;
		$this->uitype = $uitype;
	}
}