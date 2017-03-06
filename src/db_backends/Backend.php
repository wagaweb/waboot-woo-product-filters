<?php

namespace WBWPF\db_backends;

interface Backend{
	const RESULT_FORMAT_OBJECTS = 0;
	const RESULT_FORMAT_IDS = 1;

	/**
	 * Creates the main index table
	 *
	 * @param $table_name
	 * @param $params
	 *
	 * @return mixed
	 */
	public function create_index_table($table_name,$params);

	/**
	 * Creates the support table
	 *
	 * @param $table_name
	 * @param $params
	 *
	 * @return mixed
	 */
	public function create_support_table($table_name,$params);

	/**
	 * Checks if a table exists in the database
	 *
	 * @param $table_name
	 *
	 * @return mixed
	 */
	public function table_exists($table_name);

	/**
	 * Gets all products that meets a certain property (in mysql context: the WHERE clause).
	 *
	 * @param $table_name
	 * @param $prop_name
	 * @param $prop_value
	 *
	 * @return array
	 */
	public function get_products_id_by_property($table_name,$prop_name,$prop_value);

	/**
	 * Insert a product data into the database
	 *
	 * @param $table_name
	 * @param $id
	 * @param $data
	 *
	 * @return mixed
	 */
	public function insert_product_data($table_name,$id,$data);

	/**
	 * Delete an indexed product data
	 *
	 * @param $table_name
	 * @param $id
	 *
	 * @return bool
	 */
	public function erase_product_data($table_name,$id);

	/**
	 * We need a way to allows UITypes to know which of their values as an actual product associated in the current queried results;
	 * (eg: the product color "red" does not has to to be visible when no product is "red" in the current results)
	 *
	 * @param string $table_name
	 * @param array $ids
	 * @param array $col_names
	 *
	 * @return mixed
	 */
	public function get_available_property_values_for_ids($table_name, array $ids, array $col_names);

	/**
	 * Complete an entry array before insert it into the database
	 *
	 * @param $entry
	 * @param $id
	 */
	public function fill_entry_with_default_data(&$entry,$id);

	/**
	 * WooCommerce ordering form use values as "popularity", "rating", ect... which are converted in meta keys names later on by WC_Query.
	 * Our queries must do the same, taking these values and convert them to appropriate col names for ordering purposes.
	 *
	 * @param $orderby
	 * @param $order
	 *
	 * @return array
	 */
	public function transform_wc_ordering_param($orderby,$order);
}