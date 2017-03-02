<?php

namespace WBWPF\db_backends;

interface Backend{
	const RESULT_FORMAT_OBJECTS = 0;
	const RESULT_FORMAT_IDS = 1;

	/**
	 * @param $table_name
	 * @param $params
	 *
	 * @return mixed
	 */
	public function create_index_table($table_name,$params);

	/**
	 * @param $table_name
	 * @param $params
	 *
	 * @return mixed
	 */
	public function create_support_table($table_name,$params);

	/**
	 * @param $table_name
	 *
	 * @return mixed
	 */
	public function table_exists($table_name);

	/**
	 * @param $table_name
	 * @param $prop_name
	 * @param $prop_value
	 *
	 * @return array
	 */
	public function get_products_id_by_property($table_name,$prop_name,$prop_value);

	/**
	 * @param $table_name
	 * @param $id
	 * @param $data
	 *
	 * @return mixed
	 */
	public function insert_product_data($table_name,$id,$data);

	/**
	 * @param $table_name
	 * @param $id
	 *
	 * @return bool
	 */
	public function erase_product_data($table_name,$id);

	/**
	 * @param $entry
	 * @param $id
	 */
	public function fill_entry_with_default_data(&$entry,$id);
}