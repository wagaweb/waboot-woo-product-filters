<?php

namespace WBWPF\filters;

use League\Flysystem\Exception;

abstract class Filter{
	/**
	 * @var string the data type this filter operate on (eg: metas, taxonomies, custom fields...)
	 */
	var $dataType;

	/**
	 * Filter constructor.
	 *
	 * @param string $dataType
	 */
	public function __construct($dataType) {
		$this->dataType = $dataType;
	}

	/*
	 * Adds the correct "where" clause to the query
	 */
	public function parse_query(&$query){}

	/**
	 * Display the HTML for the filter
	 */
	public function display(){}

	/**
	 * @param $product_id
	 * @param $data_name
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function get_data_of($product_id,$data_name){
		switch ($this->dataType){
			case "taxonomies":
				$value = "foo";
				break;
			case "metas":
				$value = "bar";
				break;
		}

		if(!isset($value)){
			throw new \Exception("Invalid data type provided");
		}

		return $value;
	}
}