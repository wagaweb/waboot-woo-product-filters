<?php

namespace WBWPF;
use WBF\components\mvc\HTMLView;
use WBF\components\pluginsframework\BasePlugin;

/**
 * The core plugin class.
 *
 * @package    WBSample
 * @subpackage WBSample/includes
 */
class Plugin extends BasePlugin {
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
		$this->loader->add_action("admin_menu",$this,"display_admin_page");
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
	 * Creates the filters table
	 */
	public function create_filters_table(){

	}

	/**
	 * Returns a JSON of products for the frontend
	 */
	public function get_filtered_products_callback(){

	}
}
