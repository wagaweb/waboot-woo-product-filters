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
	 * @param $prop_name
	 * @param $prop_value
	 *
	 * @return array
	 */
	public function get_products_id_by_property( $table_name, $prop_name, $prop_value ) {
		global $wpdb;
		$r = $wpdb->get_col("SELECT product_id FROM ".$wpdb->prefix.self::CUSTOM_PRODUCT_INDEX_TABLE." WHERE $prop_name = '$prop_value'");
		return $r;
	}

	/**
	 * @param $table_name
	 * @param $id
	 * @param $data
	 *
	 * @return bool
	 */
	public function insert_product_data( $table_name, $id, $data ) {
		if(!isset($data['product_id'])){
			$data['product_id'] = $id;
		}

		global $wpdb;

		$r = $wpdb->insert($wpdb->prefix.$table_name,$data);

		return $r > 0;
	}

	/**
	 * @param $table_name
	 * @param $id
	 *
	 * @return bool
	 */
	public function erase_product_data($table_name, $id) {
		global $wpdb;

		$r = $wpdb->delete($wpdb->prefix.$table_name,['product_id' => $id]);

		return $r > 0;
	}
}