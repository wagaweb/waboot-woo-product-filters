<?php

namespace WBWPF\datatypes;

use WBF\components\pluginsframework\BasePlugin;
use WBWPF\Plugin;

abstract class DataType{
	const VALUES_FOR_FORMAT_COMMA_SEPARATED = 0;
	const VALUES_FOR_VALUES_FORMAT_ARRAY = 1;
	/**
	 * @var int|string
	 */
	var $type_slug;
	/**
	 * @var string
	 */
	var $label = "";
	/**
	 * @var string
	 */
	var $slug = "";
	/**
	 * @var string
	 */
	var $admin_description = "";

	public function __construct() {
		$plugin = Plugin::get_instance_from_global();
		$dataTypes = $plugin->get_available_dataTypes();
		foreach ($dataTypes as $type_slug => $classname){
			if($classname == static::class){
				$this->type_slug = $type_slug;
				break;
			}
		}
	}

	/**
	 * Return valid values for the data type
	 *
	 * @return array
	 */
	public function getData(){
		return [];
	}

	/**
	 * Given a $key, retrieve the public label for that key (eg: "product_cat" => Product Categories)
	 *
	 * @return string
	 */
	public function getPublicLabelOf($key){
		return $key;
	}

	/**
	 * Return the value for $product_id for data type called $key (eg: the value of "product_cat" for a specified product)
	 *
	 * @param $product_id
	 * @param $key
	 * @param int $format
	 *
	 * @return mixed
	 */
	public function getValueOf($product_id,$key,$format = self::VALUES_FOR_VALUES_FORMAT_ARRAY){
		if($format == self::VALUES_FOR_VALUES_FORMAT_ARRAY){
			return [];
		}elseif($format == self::VALUES_FOR_FORMAT_COMMA_SEPARATED){
			return "";
		}else{
			return false;
		}
	}

	/**
	 * Get all possible value of current data type for the key called $key. By default it uses the indexed values on the custom table.
	 *
	 * @param $key
	 *
	 * @return array
	 */
	public function getAvailableValuesFor($key){
		global $wpdb;
		$table_name = $wpdb->prefix.Plugin::CUSTOM_PRODUCT_INDEX_TABLE;
		$values = $wpdb->get_col("SELECT DISTINCT $key FROM $table_name");
		$values = array_filter($values); //Remove null values
		return $values;
	}
}