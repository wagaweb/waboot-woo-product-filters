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
		$values = apply_filters("wbwpf/filter/available_values",$values,$this);

		$this->uiType->set_name($this->slug);

		$this->uiType->set_values($values);

		if(!isset($this->label)) $this->set_label();

		if(isset($this->current_values)){
			$this->uiType->selected_values = $this->current_values;
		}

		$content = $this->uiType->generate_output();

		$display_hidden = call_user_func(function(){
			//Hide if the page displayed is the archive page of the current filter
 			$display_hidden = $this->is_current_filter() ? true : false;
			if(!$display_hidden){
				//Hide if all the values of the uiType are hidden
				$display_hidden = count($this->uiType->values) == count($this->uiType->hidden_values);
			}
			return $display_hidden;
		});

		$v = new HTMLView("src/views/single-filter.php","waboot-woo-product-filters");

		$display_args = [
			'slug' => $this->slug,
			'label' => $this->label,
			'uiType' => $this->uiType->type_slug,
			'dataType' => $this->dataType->type_slug,
			'content' => $content,
			'display_hidden' => $display_hidden
		];

		$display_args = apply_filters("wbwpf/filter/display_args",$display_args,$this);

		$v->display($display_args);
	}

	/**
	 * Check if this filters can be displayed or has to remain hidden
	 */
	public function is_current_filter(){
		$is_current_filter = false;

		if(is_product_taxonomy()){
			$q = get_queried_object();
			if($q->taxonomy == $this->slug){
				$is_current_filter = true; //We are in a taxonomy archive that is the current filter taxonomy
			}
		}

		return $is_current_filter;
	}
}