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
	 *
	 * @return mixed
	 */
	public function table_exists($table_name);

	/**
	 * @param $table_name
	 * @param $conditions
	 *
	 * @return mixed
	 */
	public function get($table_name,$conditions);

	/**
	 * @param $table_name
	 * @param $var_name
	 * @param $conditions
	 *
	 * @return mixed
	 */
	public function get_var($table_name,$var_name,$conditions);

	/**
	 * @param $table_name
	 * @param $col_name
	 * @param $conditions
	 *
	 * @return mixed
	 */
	public function get_col($table_name,$col_name,$conditions);

	/**
	 * @param $table_name
	 * @param $params
	 *
	 * @return mixed
	 */
	public function insert($table_name,$params);

	/**
	 * @param $table_name
	 * @param $parms
	 *
	 * @return mixed
	 */
	public function update($table_name,$parms);

	/**
	 * @param $table_name
	 * @param $params
	 *
	 * @return mixed
	 */
	public function delete($table_name,$params);
}