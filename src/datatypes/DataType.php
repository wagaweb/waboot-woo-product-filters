<?php

namespace WBWPF\datatypes;

use WBF\components\pluginsframework\BasePlugin;
use WBWPF\includes\Filter;
use WBWPF\Plugin;

abstract class DataType{
	const VALUES_FOR_FORMAT_COMMA_SEPARATED = 0;
	const VALUES_FOR_VALUES_FORMAT_ARRAY = 1;
	const VALUES_TYPE_INT = "integer";
	const VALUES_TYPE_FLOAT = "float";
	const VALUES_TYPE_STRING = "string";
	/**
	 * @var Filter
	 */
	var $parent_filter;
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
	/**
	 * @var int the type (integer, float or string) of the values. Useful for SQL escaping
	 */
	var $value_type;

	/**
	 * DataType constructor.
	 *
	 * @param Filter|null $parent_filter
	 */
	public function __construct(Filter &$parent_filter = null) {
		$plugin = Plugin::get_instance_from_global();
		$dataTypes = $plugin->get_available_dataTypes();
		foreach ($dataTypes as $type_slug => $classname){
			if($classname == static::class){
				$this->type_slug = $type_slug;
				$this->slug = $type_slug;
				break;
			}
		}

		if(isset($parent_filter)) $this->parent_filter = $parent_filter;
	}

	/**
	 * @param Filter $parent_filter
	 */
	public function setParentFilter(Filter &$parent_filter){
		$this->parent_filter = $parent_filter;
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
	 * @param $key
	 * @param Filter|null $parent_filter
	 *
	 * @return string
	 */
	public function getPublicLabelOf($key, Filter $parent_filter = null){
		return $key;
	}

	/**
	 * Given a item $key, retrieve the public label for that item (eg: term with id "23" of "product_cat" => Clothing)
	 *
	 * @param $key
	 *
	 * @param Filter|null $parent_filter
	 *
	 * @return string
	 */
	public function getPublicItemLabelOf($key, Filter $parent_filter = null){
		return $key;
	}

	/**
	 * Return the value for $product_id for data type called $key (eg: the value of "product_cat" for a specified product)
	 *
	 * @param $product_id
	 * @param $key
	 * @param int $format
	 * @param Filter|null $parent_filter
	 *
	 * @return mixed
	 */
	public function getValueOf($product_id,$key,$format = self::VALUES_FOR_VALUES_FORMAT_ARRAY, Filter $parent_filter = null){
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
	 * @param Filter|null $parent_filter
	 *
	 * @return array
	 */
	public function getAvailableValuesFor($key, Filter $parent_filter = null){
		global $wpdb;
		$table_name = $wpdb->prefix.Plugin::CUSTOM_PRODUCT_INDEX_TABLE;
		$values = $wpdb->get_col("SELECT DISTINCT `$key` FROM $table_name");
		$values = array_filter($values); //Remove null values
		return $values;
	}
}