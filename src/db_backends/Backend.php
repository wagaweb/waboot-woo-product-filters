<?php

namespace WBWPF\db_backends;

interface Backend{
	const RESULT_FORMAT_OBJECTS = 0;
	const RESULT_FORMAT_IDS = 1;

	/**
	 * Structures the database for indexing the products
	 *
	 * @param array $params
	 *
	 * @return mixed
	 * @internal param string $table_name main table name
	 */
	public function structure_db($params);

	/**
	 * Checks if a collection (a table or the equivalent in the referring db system) exists
	 *
	 * @param string $collection_name
	 *
	 * @return mixed
	 */
	public function collection_exists($collection_name);

	/**
	 * Gets all products that meets a certain property (in mysql context: the WHERE clause).
	 *
	 * @param $prop_name
	 * @param $prop_value
	 *
	 * @return array
	 * @internal param $table_name
	 */
	public function get_products_id_by_property($prop_name, $prop_value);

	/**
	 * Insert a product data into the database
	 *
	 * @param $id
	 * @param $data
	 *
	 * @return mixed
	 * @internal param $table_name
	 */
	public function insert_product_data($id, $data);

	/**
	 * Delete an indexed product data
	 *
	 * @param $id
	 *
	 * @return bool
	 * @internal param $table_name
	 */
	public function erase_product_data($id);

	/**
	 * We need a way to allows UITypes to know which of their values as an actual product associated in the current queried results;
	 * (eg: the product color "red" does not has to to be visible when no product is "red" in the current results)
	 *
	 * @param array $ids
	 * @param array $col_names
	 *
	 * @return mixed
	 * @internal param string $table_name
	 */
	public function get_available_property_values_for_ids(array $ids, array $col_names);

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