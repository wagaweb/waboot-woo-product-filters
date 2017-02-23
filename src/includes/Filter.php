<?php

namespace WBWPF\includes;

use WBF\components\mvc\HTMLView;
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
	 * @var string the label of the filter
	 */
	var $label;
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
		if(is_array($this->current_values) && !empty($this->current_values)){
			$statement = implode(" OR $this->slug = ",$this->current_values);
			$statement = "$this->slug = ".$statement;

			//$query->where_statements[] = $statement;
			$new_query = Query_Factory::build();
			$new_query->where_statements[] = $statement;
			$query->add_sub_query($new_query);
		}
	}

	/**
	 * Set the filter label
	 *
	 * @param string|bool|FALSE $label
	 *
	 * @return void
	 */
	function set_label($label = false){
		if(!$label){
			$label = $this->dataType->getPublicLabelOf($this->slug);
		}
		$this->label = $label;
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
		if(!isset($this->label)) $this->set_label();

		$v = new HTMLView("src/views/single-filter.php","waboot-woo-product-filters");

		$v->display([
			'slug' => $this->slug,
			'label' => $this->label,
			'uiType' => $this->uiType->type_slug,
			'dataType' => $this->dataType->type_slug,
			'content' => $this->uiType->generate_output()
		]);
	}
}