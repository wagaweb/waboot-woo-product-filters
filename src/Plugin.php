<?php

namespace WBWPF;
use WBF\components\assets\AssetsManager;
use WBF\components\mvc\HTMLView;
use WBF\components\pluginsframework\BasePlugin;
use WBF\components\utils\DB;

/**
 * The core plugin class.
 *
 * @package    WBSample
 * @subpackage WBSample/includes
 */
class Plugin extends BasePlugin {
	const CUSTOM_FILTERS_TABLE = "wbwpf_products_index";

	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {
		parent::__construct( "waboot-woo-product-filters", plugin_dir_path( dirname(  __FILE__  ) ) );

		$this->hooks();
	}

	/**
	 * Define plugins hooks
	 */
	public function hooks(){
		$this->loader->add_action("admin_enqueue_scripts", $this, "admin_assets");
		$this->loader->add_action("admin_menu",$this,"display_admin_page");
		$this->loader->add_ajax_action("create_filters_table",$this,"ajax_create_filters_table");
	}

	/**
	 * Enqueue admin assets
	 */
	public function admin_assets(){
		$assets = [
			'wbwpf-admin' => [
				'uri' => defined("SCRIPT_DEBUG") && SCRIPT_DEBUG ? $this->get_uri()."/assets/dist/js/dashboard.pkg.js" : $this->get_uri()."/assets/dist/js/dashboard.min.js",
				'path' => defined("SCRIPT_DEBUG") && SCRIPT_DEBUG ? $this->get_dir()."/assets/dist/js/dashboard.pkg.js" : $this->get_dir()."/assets/dist/js/dashboard.min.js",
				'type' => 'js',
				'i10n' => [
					'name' => "wbwpf",
					'params' => [
						'ajax_url' => admin_url('admin-ajax.php')
					]
				]
			]
		];

		(new AssetsManager($assets))->enqueue();
	}

	/**
	 * Displays the admin page
	 */
	public function display_admin_page(){
		add_submenu_page("woocommerce",__("Filters settings",$this->get_textdomain()),__("Filters settings",$this->get_textdomain()),"manage_woocommerce","wbwpf_settings",function(){
			global $wpdb;
			$v = new HTMLView($this->src_path."/views/admin/settings.php",$this,false);

			//Gets all taxonomies
			$raw_taxonomies = get_taxonomies([],"objects");
			foreach ($raw_taxonomies as $tax){
				$taxonomies[$tax->name] = $tax->labels->name;
			}

			//Gets all metas
			$metas = $wpdb->get_col("SELECT meta_key FROM $wpdb->postmeta as postmeta JOIN $wpdb->posts as posts ON postmeta.post_id = posts.ID WHERE post_type = 'product'");
			$metas = array_unique($metas);

			$v->for_dashboard()->display([
				'page_title' => __("Filters settings",$this->get_textdomain()),
				'taxonomies' => $taxonomies,
				'metas' => $metas,
				'has_taxonomies' => isset($taxonomies) && is_array($taxonomies) && !empty($taxonomies),
				'has_metas' => isset($metas) && is_array($metas) && !empty($metas),
				'textdomain' => $this->get_textdomain()
			]);
		});
	}

	/**
	 * Ajax callback to create the filters table
	 */
	public function ajax_create_filters_table(){
		$params = $_POST['params'];
		$table_params = $params['table_params'];
		$offset = $params['offset'];
		$limit = $params['limit'];

		if($offset == 0){ //We just started, so create the table
			$this->create_filters_table($table_params);
		}

		//Then begin to fill the table
		global $wpdb;
		if(!isset($params['found_products'])){
			$found_products = $wpdb->get_var("SELECT count(ID) FROM $wpdb->posts WHERE post_type = 'product' and post_status = 'publish'");
		}else{
			$found_products = $params['found_products'];
		}

		$ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_type = 'product' and post_status = 'publish' LIMIT {$limit} OFFSET {$offset}");

		if(is_array($ids) && !empty($ids)){
			$this->fill_filters_table($ids);
			wp_send_json_success([
				'offset' => $limit+$offset,
				'limit' => $limit,
				'found_products' => $found_products,
				'current_percentage' => ceil( ($limit+$offset)*(100/$found_products) ),
				'table_params' => $table_params,
				'status' => 'run'
			]);
		}else{
			wp_send_json_success([
				'status' => 'complete',
				'current_percentage' => 100,
				'found_products' => $found_products,
			]);
		}
	}

	/**
	 * Creates the filters table
	 */
	public function create_filters_table(array $params){
		global $wpdb;

		if(!DB::table_exists(Plugin::CUSTOM_FILTERS_TABLE)){
			$wpdb->query("DROP TABLE ".$wpdb->prefix.Plugin::CUSTOM_FILTERS_TABLE);
		}

		if(!DB::table_exists(Plugin::CUSTOM_FILTERS_TABLE)){
			$table_name = $wpdb->prefix.Plugin::CUSTOM_FILTERS_TABLE;
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
			relation_id bigint(20) NOT NULL AUTO_INCREMENT
			product_id bigint(20) NOT NULL";

			if(isset($params['taxonomies'])){
				foreach($params['taxonomies'] as $tax_name){
					$sql.= "$tax_name VARCHAR(255) NOT NULL";
				}
			}

			if(isset($params['metas'])){
				foreach($params['metas'] as $meta_name){
					$sql.= "$meta_name VARCHAR(255) NOT NULL";
				}
			}

			$sql.= ") $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
	}

	/**
	 * Fill filters table with data
	 *
	 * @param array $ids if EMPTY, then the function will get all the products before filling, otherwise it fills only the selected ids
	 */
	public function fill_filters_table($ids = []){
		if(empty($ids)){
			global $wpdb;
			$ids = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_type = 'product' and post_status = 'publish'");
		}
		foreach ($ids as $product_id){
			$product = wc_get_product($product_id);
			//Do operations...
		}
	}

	/**
	 * Returns a JSON of products for the frontend
	 */
	public function get_filtered_products_callback(){

	}
}
