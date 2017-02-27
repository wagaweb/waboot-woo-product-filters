<?php

namespace WBWPF\db_backends;

use WBF\components\utils\DB;

class MYSQL implements Backend {
	/**
	 * @param $table_name
	 * @param $params
	 *
	 * @return array|bool
	 */
	public function create_index_table( $table_name, $params ) {
		global $wpdb;

		$r = false;

		if(self::table_exists( $table_name )){
			//Create table
			$wpdb->query("DROP TABLE ".$wpdb->prefix.$table_name);
			$dropped = true;
		}else{
			$dropped = false;
		}

		if(!self::table_exists( $table_name ) || $dropped){
			$table_name = $wpdb->prefix.$table_name;
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (\n";

			$fields = [
				"relation_id bigint(20) NOT NULL AUTO_INCREMENT",
				"product_id bigint(20) NOT NULL"
			];

			foreach ($params as $datatype_slug => $data_key){
				foreach ($data_key as $k => $v){
					$fields[] = "$v VARCHAR(255)";
				}
			}

			$fields[] = "PRIMARY KEY (relation_id)";

			$sql.= implode(",\n",$fields);

			$sql.= ") $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			$r = dbDelta( $sql );
		}

		return $r;
	}

	/**
	 * @param $table_name
	 *
	 * @return bool
	 */
	public function table_exists( $table_name ) {
		return DB::table_exists($table_name);
	}

	/**
	 * @param $table_name
	 * @param $conditions
	 *
	 * @return array|null|object
	 * @throws \Exception
	 */
	public function get($table_name, $conditions = []) {
		global $wpdb;

		if(!empty($conditions)){
			if(!isset($conditions['relation'])){
				throw new \Exception("Incomplete params for get method");
			}

			$relation = $conditions['relation'];
			unset($conditions['relation']);

			$where_statement = implode(" ".$relation." ",$conditions);

			$r = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.$table_name." WHERE ".$where_statement);
		}else{
			$r = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.$table_name);
		}

		return $r;
	}

	/**
	 * @param $table_name
	 * @param $var_name
	 * @param $conditions
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function get_var( $table_name, $var_name, $conditions = [] ) {
		global $wpdb;

		if(!empty($conditions)){
			if(!isset($conditions['relation'])){
				throw new \Exception("Incomplete params for get method");
			}

			$relation = $conditions['relation'];
			unset($conditions['relation']);

			$where_statement = implode(" ".$relation." ",$conditions);

			$r = $wpdb->get_var("SELECT DISTINCT $var_name FROM ".$wpdb->prefix.$table_name." WHERE $where_statement");
		}else{
			$r = $wpdb->get_var("SELECT DISTINCT $var_name FROM ".$wpdb->prefix.$table_name);
		}

		return $r;
	}

	/**
	 * @param $table_name
	 * @param $col_name
	 * @param $conditions
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function get_col( $table_name, $col_name, $conditions = [] ) {
		global $wpdb;

		if(!empty($conditions)){
			if(!isset($conditions['relation'])){
				throw new \Exception("Incomplete params for get method");
			}

			$relation = $conditions['relation'];
			unset($conditions['relation']);

			$where_statement = implode(" ".$relation." ",$conditions);

			global $wpdb;

			$r = $wpdb->get_col("SELECT DISTINCT $col_name FROM ".$wpdb->prefix.$table_name." WHERE $where_statement");
		}else{
			$r = $wpdb->get_col("SELECT DISTINCT $col_name FROM ".$wpdb->prefix.$table_name);
		}

		return $r;
	}

	/**
	 * @param $table_name
	 * @param $params
	 *
	 * @return false|int
	 */
	public function insert($table_name, $params) {
		global $wpdb;
		$r = $wpdb->insert($wpdb->prefix.$table_name, $params);

		return $r;
	}

	/**
	 * @param $table_name
	 * @param $params
	 *
	 * @return mixed|void
	 */
	public function update($table_name, $params) {
		// TODO: Implement update() method.
	}

	/**
	 * @param $table_name
	 * @param $params
	 *
	 * @return mixed|void
	 */
	public function delete($table_name, $params) {
		// TODO: Implement delete() method.
	}
}