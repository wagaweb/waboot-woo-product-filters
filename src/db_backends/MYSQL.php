<?php

namespace WBWPF\db_backends;

use WBF\components\utils\DB;

class MYSQL implements Backend {
	/*
	 * This is the name of the table that contains all products id with their filterable values
	 */
	const CUSTOM_PRODUCT_INDEX_TABLE = "wbwpf_products_index";

	/**
	 * Creates the main index table
	 *
	 * @param string $params
	 *
	 * @return array|bool
	 * @internal param $table_name
	 */
	public function structure_db( $params ) {
		global $wpdb;

		$table_name = self::CUSTOM_PRODUCT_INDEX_TABLE;

		$r = false;

		if(self::collection_exists( $table_name )){
			//Create table
			$wpdb->query("DROP TABLE ".$wpdb->prefix.$table_name);
			$dropped = true;
		}else{
			$dropped = false;
		}

		if( !self::collection_exists( $table_name ) || $dropped){
			$table_name = $wpdb->prefix.$table_name;
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (\n";

			$fields = [
				"relation_id bigint(20) NOT NULL AUTO_INCREMENT",
				"product_id bigint(20) NOT NULL",
			];

			$default_extra_fields = [
				"post_type varchar(20) NOT NULL",
				"post_parent bigint(20) NOT NULL DEFAULT 0",
				"has_variations boolean NOT NULL DEFAULT FALSE",
				"total_sales bigint(20)", //to order by popularity
				"price varchar(255)", //to order by price
				"post_date_gmt DATETIME NOT NULL", //to order by date:
				"post_modified_gmt DATETIME NOT NULL"
			];

			foreach ($params as $datatype_slug => $data_key){
				foreach ($data_key as $k => $v){
					$fields[] = "$v VARCHAR(255)";
				}
			}

			$fields = array_merge($fields,$default_extra_fields);

			$fields[] = "PRIMARY KEY (relation_id)";

			$sql.= implode(",\n",$fields);

			$sql.= ") $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			$r = dbDelta( $sql );
		}

		return $r;
	}

	/**
	 * Checks if a table exists in the database
	 *
	 * @param $table_name
	 *
	 * @return bool
	 */
	public function collection_exists( $table_name ) {
		return DB::table_exists($table_name);
	}

	/**
	 * Gets all products that meets a certain property (in mysql context: the WHERE clause).
	 *
	 * @param $prop_name
	 * @param $prop_value
	 *
	 * @return array
	 * @internal param $table_name
	 */
	public function get_products_id_by_property( $prop_name, $prop_value ) {
		global $wpdb;
		$r = $wpdb->get_col("SELECT product_id FROM ".$wpdb->prefix.self::CUSTOM_PRODUCT_INDEX_TABLE." WHERE $prop_name = '$prop_value'");
		return $r;
	}

	/**
	 * Insert a product data into the database
	 *
	 * @param $id
	 * @param $data
	 *
	 * @return bool
	 * @internal param $table_name
	 */
	public function insert_product_data( $id, $data ) {
		if(!isset($data['product_id'])){
			$data['product_id'] = $id;
		}

		global $wpdb;

		/*
		 * The completion below could be done by: fill_entry_with_default_data, otherwise all rows will have those values.
		 * BUT AT THE MOMENT WE ARE OK WITH THAT!
		 */

		//Get default extra fields values
		$post_data = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID = $id");
		$post_data = $post_data[0];

		//Get if has variations
		$variations_ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_parent = $id AND post_type = 'product_variation'");
		$has_variations = !empty($variations_ids);

		//Check if is variation and if has parent
		if($post_data->post_type == 'product_variation'){
			$parent = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID = $post_data->post_parent AND post_type = 'product'");
			$has_parent = !empty($parent);
			if(!$has_parent){
				return false; //Do not insert variations without parents
			}
		}

		$extra_fields = [
			'post_type' => $post_data->post_type,
			'post_parent' => $post_data->post_parent,
			'has_variations' => $has_variations,
			'post_date_gmt' => $post_data->post_date_gmt,
			'post_modified_gmt' => $post_data->post_modified_gmt,
			'total_sales' => get_post_meta($id,"total_sales",true),
			'price' => get_post_meta($id,"_price",true)
		];

		$data = array_merge($data,$extra_fields);

		do_action("wbwpf/db/insert_new_product/before",$id,$post_data,$data);

		$r = $wpdb->insert($wpdb->prefix.self::CUSTOM_PRODUCT_INDEX_TABLE,$data);

		do_action("wbwpf/db/insert_new_product/after",$id,$post_data,$data,$r);

		return $r > 0;
	}

	/**
	 * Delete an indexed product data
	 *
	 * @param $id
	 *
	 * @return bool
	 * @internal param $table_name
	 */
	public function erase_product_data( $id) {
		global $wpdb;

		$r = $wpdb->delete($wpdb->prefix.self::CUSTOM_PRODUCT_INDEX_TABLE,['product_id' => $id]);

		return $r > 0;
	}

	/**
	 * @param array $ids
	 * @param array $col_names
	 *
	 * @return array
	 * @internal param $table_name
	 */
	public function get_available_property_values_for_ids( array $ids, array $col_names ) {
		global $wpdb;

		$results = [];

		$ids = array_unique($ids);

		if(!empty($ids)){
			$query = "SELECT ".implode(",",$col_names)." FROM ".$wpdb->prefix.self::CUSTOM_PRODUCT_INDEX_TABLE." WHERE product_id IN (".implode(",",$ids).")";

			$raw_results = $wpdb->get_results($query,ARRAY_A);

			foreach ($col_names as $col_name){
				//$col_values = wp_list_pluck($raw_results,$col_name);
				$col_values = array_column($raw_results,$col_name);
				$results[$col_name] = array_unique(array_filter($col_values)); //<- this is better, but wont work on CWG?
			}
		}

		return $results;
	}

	/**
	 * Complete an entry array before insert it into the database. NOT USED AT THE MOMENT.
	 *
	 * @param int $id the product id
	 * @param array $entry
	 */
	public function fill_entry_with_default_data(&$entry,$id){
		//Get default extra fields values
		global $wpdb;
		$post_data = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID = $id");
		$post_data = $post_data[0];

		$extra_fields = [
			'post_type' => $post_data->post_type,
			'post_parent' => $post_data->post_parent,
			'post_date_gmt' => $post_data->post_date_gmt,
			'post_modified_gmt' => $post_data->post_modified_gmt,
			'total_sales' => get_post_meta($id,"total_sales",true),
			'price' => get_post_meta($id,"_price",true)
		];

		$entry = array_merge($entry,$extra_fields);
	}

	/**
	 * WooCommerce ordering form use values as "popularity", "rating", ect... which are converted in meta keys names later on by WC_Query.
	 * Our queries must do the same, taking these values and convert them to appropriate col names for ordering purposes.
	 *
	 * @param $orderby
	 * @param $order
	 *
	 * @return array
	 */
	public function transform_wc_ordering_param( $orderby, $order ) {
		switch($orderby){
			case "menu_order":
				$orderby = "product_id"; //todo: implement
				$order = "DESC";
				break;
			case "popularity":
				$orderby = "total_sales";
				$order = "DESC";
				break;
			case "price":
				$orderby = "price";
				$order = "DESC";
				break;
			case "price-desc":
				$orderby = "price";
				$order = "ASC";
				break;
			case "date":
				$orderby = "post_modified_gmt";
				$order = "DESC";
				break;
			case "rating":
				$orderby = "product_id"; //todo: implement
				$order = "DESC";
				break;
		}

		return [
			'order' => $order,
			'orderby' => $orderby
		];
	}
}