<?php

namespace WBWPF;
use WBF\components\assets\AssetsManager;
use WBF\components\mvc\HTMLView;
use WBF\components\pluginsframework\BasePlugin;
use WBF\components\pluginsframework\TemplatePlugin;
use WBF\components\utils\DB;
use WBWPF\datatypes\DataType;
use WBWPF\db_backends\MYSQL;
use WBWPF\filters\Filter;
use WBWPF\includes\AjaxEndpoint;
use WBWPF\includes\DB_Manager;
use WBWPF\includes\Filter_Factory;
use WBWPF\includes\Filter_Query;
use WBWPF\includes\Query_Factory;
use WBWPF\includes\Settings_Manager;

/**
 * The core plugin class.
 *
 * @package    WBSample
 * @subpackage WBSample/includes
 */
class Plugin extends TemplatePlugin {
	/*
	 * This is the name of the table that contains all products id with their filterable values
	 */
	const CUSTOM_PRODUCT_INDEX_TABLE = "wbwpf_products_index";
	/**
	 * @var DB_Manager
	 */
	var $DB;
	/**
	 * @var Settings_Manager
	 */
	var $Settings;
	/**
	 * @var AjaxEndpoint
	 */
	var $AjaxEndpoint;

	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {
		parent::__construct( "waboot-woo-product-filters", plugin_dir_path( dirname(  __FILE__  ) ) );

		//Setting the update server:
		//$this->set_update_server("http://update.waboot.org/?action=get_metadata&slug={$this->plugin_name}&type=plugin");

		$this->add_wc_template("loop/orderby.php");
		if($this->Settings->use_async_product_list){
			if(wp_get_theme()->get_template() === 'waboot'){
				$this->loader->add_action('init',$this,'waboot_wc_async_compatibility',14);
			}else{
				$this->add_wc_template("archive-product.php");
			}
		}
		$this->hooks();
	}

	/**
	 * Loads plugin dependencies. Called by parent during __construct();
	 */
	public function load_dependencies() {
		parent::load_dependencies();
		$this->DB = new DB_Manager(new MYSQL()); //todo: allows multiple backend
		$this->Settings = new Settings_Manager($this);
		$this->AjaxEndpoint = new AjaxEndpoint();
	}

	/**
	 * Return the plugin instance
	 *
	 * @return Plugin
	 * @throws \Exception
	 */
	public static function get_instance_from_global(){
		$plugin = BasePlugin::get_instances_of("waboot-woo-product-filters");
		if(!isset($plugin['core'])) throw new \Exception("Unable to find the plugin during get_instance_from_global()");
		$plugin = $plugin['core'];
		if(!$plugin instanceof Plugin) throw new \Exception("get_instance_from_global() found an invalid plugin instance");

		return $plugin;
	}

	/**
	 * Return the current global instance of Filter_Query
	 *
	 * @return bool
	 */
	public static function get_query_from_global(){
		global $wbwpf_query_instance;

		if(isset($wbwpf_query_instance) && $wbwpf_query_instance instanceof Filter_Query){
			return $wbwpf_query_instance;
		}

		return false;
	}

	/**
	 * Define plugins hooks
	 */
	public function hooks(){
		$this->loader->add_action("admin_enqueue_scripts", $this, "admin_assets");
		$this->loader->add_action("wp_enqueue_scripts", $this, "public_assets");
		$this->loader->add_action("admin_menu",$this,"display_admin_page");

		//$this->loader->add_ajax_action("create_products_index_table",$this,"ajax_populate_products_index");
		$this->loader->add_action("wp_ajax_populate_products_index",$this,"ajax_populate_products_index");
		$this->loader->add_action("wp_ajax_nopriv_create_products_index_table",$this,"ajax_populate_products_index");

		$this->loader->add_action("wp_ajax_wbwpf_get_products",$this,"get_filtered_products_callback");
		$this->loader->add_action("wp_ajax_nopriv_wbwpf_get_products",$this,"get_filtered_products_callback");

		//Query
		$this->loader->add_action("query_vars",$this,"add_query_vars",1);
		$this->loader->add_action("parse_tax_query",$this,"remove_tax_query",10,1);
		$this->loader->add_action("woocommerce_product_query",$this,"alter_product_query",10,2);
		$this->loader->add_filter("woocommerce_pagination_args",$this,"alter_woocommerce_pagination_args",10,1);

		$this->loader->add_action("save_post"."_product",$this,"reindex_product_on_save",10,3);
		$this->loader->add_action("save_post"."_product_variation",$this,"reindex_product_variation_on_save",10,3);
		$this->loader->add_action("before_delete_post",$this,"remove_product_from_index_on_delete",10,1);

		//Hooks during indexing
		$this->loader->add_action("wbwpf/db/insert_new_product/after",$this,"on_product_indexed",10,4);

		//Filters parsing Customizations
		$this->loader->add_filter("wbwpf/filters/detected/parsed",$this,"inject_wbwpf_query_params_into_detect_filters",10,1);

		//Catalog visualizations hooks
		$this->loader->add_filter("the_title",$this,"alter_variations_title",10,2);

		//Ajax
		$this->AjaxEndpoint->setup_endpoints();
		$this->loader->add_filter("wbwpf/ajax/get_products/content",$this,"alter_single_product_content",10,1);

		//Widgets
		$this->loader->add_action("widgets_init", $this, "register_widgets");
	}

	/**
	 * Enqueue admin assets
	 *
	 * @hooked 'admin_enqueue_scripts'
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
						'ajax_url' => admin_url('admin-ajax.php'),
						'wbwpf_query_separator' => Filter_Factory::WPWPF_QUERY_SEPARATOR,
					]
				],
				'deps' => ['jquery','underscore']
			]
		];

		(new AssetsManager($assets))->enqueue();
	}

	/**
	 * Enqueue public assets
	 */
	public function public_assets(){
		$settings = $this->get_plugin_settings();
		$assets = [
			'wbwpf-public' => [
				'uri' => defined("SCRIPT_DEBUG") && SCRIPT_DEBUG ? $this->get_uri()."/assets/dist/js/frontend.pkg.js" : $this->get_uri()."/assets/dist/js/frontend.min.js",
				'path' => defined("SCRIPT_DEBUG") && SCRIPT_DEBUG ? $this->get_dir()."/assets/dist/js/frontend.pkg.js" : $this->get_dir()."/assets/dist/js/frontend.min.js",
				'type' => 'js',
				'i10n' => [
					'name' => "wbwpf",
					'params' => [
						'ajax_url' => admin_url('admin-ajax.php'),
						'wbwpf_query_separator' => Filter_Factory::WPWPF_QUERY_SEPARATOR,
						'components' => [
							'filtersList' => [
								'submitOnSelect' => (bool) !$settings['widget_display_apply_button'],
								'hasSubmitButton' => (bool) $settings['widget_display_apply_button'],
								'reloadProductsListOnSubmit' => $settings['use_async_product_list']
							]
						]
					]
				],
				'deps' => ['jquery','underscore']
			],
			'wbwpf-css' => [
				'uri' => $this->get_uri()."/assets/dist/css/waboot-woo-product-filters.min.css",
				'path' => $this->get_dir()."/assets/dist/css/waboot-woo-product-filters.min.css",
				'type' => 'css',
			]
		];

		(new AssetsManager($assets))->enqueue();
	}

	/**
	 * Adds query vars
	 *
	 * @hooked 'query_vars'
	 *
	 * @param $vars
	 *
	 * @return array
	 */
	public function add_query_vars($vars){
		$vars[] = "wbwpf_query";
		return $vars;
	}

	/**
	 * Alter the woocommerce product query
	 *
	 * @hooked 'woocommerce_product_query'
	 *
	 * @param $query
	 * @param $wc_query
	 */
	public function alter_product_query($query,$wc_query){
		if(!$query instanceof \WP_Query) return;

		$can_alter_query = apply_filters("wbwpf/can_alter_query",true,$query,$wc_query,$this); //It is possible to prevent the plugin to alter the query by this filter

		if(!$can_alter_query) return;

		try{
			/*
			 * /!\
			 * THE CORE FUNCTIONALITY OF THIS PLUGIN STARTS HERE ;)
			 * /!\
			 */
			$filter_query = Query_Factory::build_from_available_params();

			$GLOBALS['wbwpf_query_instance'] = &$filter_query;

			if(isset($filter_query) && $filter_query instanceof Filter_Query && $filter_query->has_query()){
				$ids = $filter_query->get_results(Filter_Query::RESULT_FORMAT_IDS);
				if(is_array($ids) && count($ids) > 0){
					$query->set('post__in',$ids);
				}else{
					$query->set('post__in',[0]);
				}
				if($filter_query->query_variations){
					$query->set('post_type',['product','product_variation']);
				}
			}
		}catch (\Exception $e){}
	}

	/**
	 * WooCommerce does not adds variations ids in terms relationship table. So, if we are in a taxonomy page, and have chosen to display variations, they are not retrieved.
	 * BUT, in our query, we just have the filtered ids, so in that case, we don't need tax_query.
	 *
	 * @param \WP_Query $wp_query
	 *
	 * @hooked 'parse_tax_query'
	 */
	public function remove_tax_query(\WP_Query $wp_query){
		global $wbwpf_query_instance;
		global $wp_query;
		if(is_product_category() && isset($wbwpf_query_instance) && $wbwpf_query_instance->query_variations){
			$wp_query->tax_query = new \WP_Tax_Query( [] );
		}
	}

	/**
	 * Adds out query string to woocommerce pagination
	 *
	 * @hooked 'woocommerce_pagination_args'
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function alter_woocommerce_pagination_args($args){
		if(isset($_POST['wbwpf_search_by_filters'])){
			$filter_string = Filter_Factory::stringify_from_post_params();
			$args['add_fragment'] = "?wbwpf_query=$filter_string";
		}
		return $args;
	}

	/**
	 * Reindex a product upon save
	 *
	 * @hooked 'save_post_product'
	 *
	 * @param $post_ID
	 * @param $post
	 * @param $update
	 */
	public function reindex_product_on_save($post_ID,$post,$update){
		$this->DB->Backend->erase_product_data( $post_ID );
		$this->populate_products_index([$post_ID]);
	}

	/**
	 * Reindex a product variation upon save
	 *
	 * @hooked 'save_post_product_variation'
	 *
	 * @param $post_ID
	 * @param $post
	 * @param $update
	 */
	public function reindex_product_variation_on_save($post_ID,$post,$update){
		$this->DB->Backend->erase_product_data( $post_ID );
		$this->populate_products_index([$post_ID]);
	}

	/**
	 * Remove a product from the index table upon deletion
	 *
	 * @param $post_ID
	 */
	public function remove_product_from_index_on_delete($post_ID){
		$product = wc_get_product($post_ID);

		if($product instanceof \WC_Product){
			$this->DB->Backend->erase_product_data( $post_ID );
		}
	}

	/**
	 * Displays the admin page
	 *
	 * @hooked 'admin_menu'
	 */
	public function display_admin_page(){
		add_submenu_page("woocommerce",__("Filters settings",$this->get_textdomain()),__("Filters settings",$this->get_textdomain()),"manage_woocommerce","wbwpf_settings",function(){
			if(isset($_POST['wbwpf_save_settings']) && $_POST['wbwpf_save_settings'] = 1){
				//Save settings
				$settings_to_save = $_POST['wbwpf_options'];
				$this->save_plugin_settings($settings_to_save,false);
			}

			$v = new HTMLView($this->src_path."/views/admin/settings.php",$this,false);

			$datatypes_tree = [];

			$datatypes = $this->get_available_dataTypes();

			foreach ($datatypes as $name => $classname){
				if(class_exists($classname)){
					$o = new $classname();
					if($o instanceof DataType){
						$datatypes_tree[] = [
							'label' => $o->label,
							'slug' => $o->slug,
							'description' => $o->admin_description,
							'data' => $o->getData()
						];
					}
				}
			}

			$v->for_dashboard()->display([
				'page_title' => __("Filters settings",$this->get_textdomain()),
				'current_url' => admin_url("admin.php?page=wbwpf_settings"),
				'data' => $datatypes_tree,
				'has_data' => isset($datatypes_tree) && is_array($datatypes_tree) && !empty($datatypes_tree),
				'current_settings' => $this->get_plugin_settings(),
				'available_uiTypes' => $this->get_available_uiTypes(),
				'textdomain' => $this->get_textdomain()
			]);
		});
	}

	/**
	 * Get which class to use to parse which data type
	 */
	public function get_available_dataTypes(){
		$datatypes = [
			'attributes' => __NAMESPACE__."\\datatypes\\Attribute",
			'taxonomies' => __NAMESPACE__."\\datatypes\\Taxonomy",
			'meta' => __NAMESPACE__."\\datatypes\\Meta"
		];
		$datatypes = apply_filters("wbwpf/datatypes/available",$datatypes);
		return $datatypes;
	}

	/**
	 * Get which class to use to display which ui type
	 */
	public function get_available_uiTypes(){
		$uitypes = [
			'checkbox' => __NAMESPACE__."\\uitypes\\Checkbox",
			'range' => __NAMESPACE__."\\uitypes\\Range",
			'select' => __NAMESPACE__."\\uitypes\\Select"
		];
		$uitypes = apply_filters("wbwpf/uitypes/available",$uitypes);
		return $uitypes;
	}

	/**
	 * Get which uiType has to be used to display which dataType
	 *
	 * @return array
	 */
	public function get_dataType_uiType_relations(){
		$relations = [
			'meta' => 'checkbox',
			'taxonomies' => 'checkbox',
			'attributes' => 'checkbox'
		];
		$relations = apply_filters("wbwpf/types/relations",$relations);
		return $relations;
	}

	/**
	 * Get a list of data type object in an associative array with slugs as keys
	 *
	 * @return array
	 */
	public function get_available_dataTypes_by_slug(){
		$dt = $this->get_available_dataTypes();
		$slugs = [];
		foreach ($dt as $classname){
			if(class_exists($classname)){
				$o = new $classname();
				if($o instanceof DataType){
					$slugs[$o->slug] = $o;
				}
			}
		}
		return $slugs;
	}

	/**
	 * Get the default settings
	 *
	 * @return array
	 */
	public function get_plugin_default_settings(){
		return $this->Settings->get_plugin_default_settings();
	}

	/**
	 * Save the plugin settings
	 *
	 * @param array $settings
	 * @param bool $autodetect_types
	 */
	public function save_plugin_settings($settings,$autodetect_types = true){
		$this->Settings->save_plugin_settings($settings,$autodetect_types);
	}

	/**
	 * Get the plugin settings
	 *
	 * @return array
	 */
	public function get_plugin_settings(){
		return $this->Settings->get_plugin_settings();
	}

	/**
	 * Ajax callback to create and populate the filters table
	 */
	public function ajax_populate_products_index(){
		$params = $_POST['params'];
		$table_params = $params['table_params'];
		$offset = $params['offset'];
		$limit = $params['limit'];

		if($offset == 0){ //We just started, so create the table
			$this->save_plugin_settings(['filters' => $table_params]);
			$r = $this->DB->Backend->structure_db( $table_params );
			if(!$r){
				wp_send_json_error([
					'status' => 'failed',
					'message' => __("Unable to create or update the product index table", $this->get_textdomain())
				]);
			}
		}

		//Then begin to fill the table
		global $wpdb;
		if(!isset($params['found_products'])){
			$found_products = $wpdb->get_var("SELECT count(ID) FROM $wpdb->posts WHERE post_type = 'product' and post_status = 'publish'");
		}else{
			$found_products = $params['found_products'];
		}

		$ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE (post_type = 'product' or post_type = 'product_variation') and post_status = 'publish' LIMIT {$limit} OFFSET {$offset}");

		if(is_array($ids) && !empty($ids)){
			$this->populate_products_index($ids);

			$current_percentage = ceil( ($limit+$offset)*(100/$found_products) );
			if($current_percentage > 100) $current_percentage = 100;

			wp_send_json_success([
				'offset' => $limit+$offset,
				'limit' => $limit,
				'found_products' => $found_products,
				'current_percentage' => $current_percentage,
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
	 * Fill filters table with data
	 *
	 * @param array $ids if EMPTY, then the function will get all the products before filling, otherwise it fills only the selected ids
	 */
	public function populate_products_index($ids = []){
		global $wpdb;
		if(empty($ids)){
			$ids = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE (post_type = 'product' or post_type = 'product_variation') and post_status = 'publish'");
		}

		$datatypes = $this->get_available_dataTypes_by_slug();
		$filters_settings = $this->get_plugin_settings()['filters'];
		$rows = [];

		/*
		 * We are testing two method of indexing: multiple row per product (avoiding cols with comma separated item) and single row per product (otherwise)
		 */

		/*
		 * This cycle create one row per product, with some cols with multiple values separated by comma
		 */
		/*foreach ($ids as $product_id){
			$new_row = [
				'product_id' => $product_id
			];
			foreach ($filters_settings as $datatype_slug => $values){
				foreach ($values as $value){
					$new_row[$value] = $datatypes[$datatype_slug]->getValueOf($product_id,$value,DataType::VALUES_FOR_FORMAT_COMMA_SEPARATED); //get the value for that data type of the current product
				}
			}
			$rows[] = $new_row;
		}*/

		/*
		 * These cycle will create many rows per product, so there is no column with multiple values
		 */
		foreach ($ids as $product_id){
			$new_row = [];
			//$this->DB->Backend->fill_entry_with_default_data($new_row,$product_id); //In this way we have only a single row with complete values. NOT USED AT THE MOMENT.
			foreach ($filters_settings as $datatype_slug => $properties){ //eg: $datatype_slug: taxonomies
				foreach ($properties as $property){ //eg: $property: product_cat
					$product_values = $datatypes[$datatype_slug]->getValueOf($product_id,$property,DataType::VALUES_FOR_VALUES_FORMAT_ARRAY); //get the value for that data type of the current product
					if(is_array($product_values) && !empty($product_values)){
						/*
						 * We have multiple values for this data type (eg: multiple product_cat terms), so we need to create multiple, incomplete rows
						 */
						array_walk($product_values,function($el) use(&$rows,$product_id,$property,$new_row){
							$rows[] = [
								'product_id' => $product_id,
								$property => $el
							];
						});
					}elseif(is_array($product_values) && empty($product_values)){
						if(!isset($new_row['product_id'])){
							$new_row['product_id'] = $product_id;
						}
						$new_row[$property] = null; //todo: change to null or ""?
					}else{
						/*
						 * We have have a single value for this data type. So it's ok to inject it into the main row. We want only one row that contains all single-valued data types for one product
						 */
						if(!isset($new_row['product_id'])){
							$new_row['product_id'] = $product_id;
						}
						$new_row[$property] = $product_values;
					}
				}
			}
			if(!empty($new_row)){
				$rows[] = $new_row;
			}
		}

		foreach ($rows as $new_row){
			//Insert the value
			$r = $this->DB->Backend->insert_product_data( $new_row['product_id'], $new_row );
		}
	}

	/**
	 * Performs some action after a product has been indexed
	 *
	 * @param int $product_id
	 * @param \stdClass $product_data
	 * @param array $entry_data
	 * @param mixed $index_result
	 */
	public function on_product_indexed($product_id,$product_data,$entry_data,$index_result){
		if($product_data->post_type == "product_variation"){
			//We need to assign the visibility of the parent to the variation, because woocommerce adds to the query: [...] ( wp_postmeta.meta_key = '_visibility' AND wp_postmeta.meta_value IN ('visible','catalog') ) [...]
			$parent_visibility = get_post_meta($product_data->post_parent,"_visibility",true);
			$r = update_post_meta($product_id,"_visibility",$parent_visibility);
		}
	}

	/**
	 * Assign variation parent title to variation title
	 *
	 * @hooked 'the_title'
	 *
	 * @param $title
	 * @param $post_ID
	 *
	 * @return string
	 */
	public function alter_variations_title($title,$post_ID){
		if(!$this->get_plugin_settings()['show_variations']) return $title; //Do nothing if variations are not displayed

		global $wpdb;
		$data = $wpdb->get_results("SELECT post_type,post_parent FROM $wpdb->posts WHERE ID = $post_ID");
		if(empty($data)) return $title; //Do nothing if no results found

		$data = array_pop($data);

		if($data->post_type != "product_variation") return $title; //Do nothing if it is not a variation

		$parent_title = get_the_title($data->post_parent);

		if(!$parent_title || !is_string($parent_title) || $parent_title == "") return $title; //Do nothing if it is not a valid title

		return $parent_title;
	}

	/**
	 * Remove the li wrapper around the content-single in loop (during ajax retrieving)
	 *
	 * @hooked 'wbwpf/ajax/get_products/content'
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function alter_single_product_content($content){
		//Remove <li></li>
		$strip_single_tag = function($str,$tag){
			$str=preg_replace('/<'.$tag.'[^>]*>/i', '', $str);
			$str=preg_replace('/<\/'.$tag.'>/i', '', $str);
			return $str;
		};
		$content = $strip_single_tag($content,"li");
		return $content;
	}

	/**
	 * Takes parsed format of detected filters, applies params specified in wbwpf_query param to them. This is used for BREADCRUMB.
	 *
	 * The wbwpf_query param can further filter the generated detected filters array; namely, we:
	 * - remove from $result_filters the filters not present in wbwpf_query)
	 * - overwrite any values in $result_filters with wbwpf_query values
	 *
	 * @param array $result_filters
	 *
	 * @hooked "wbwpf/filters/detected/parsed"
	 *
	 * @return array;
	 */
	public function inject_wbwpf_query_params_into_detect_filters($result_filters){
		if(!isset($_GET['wbwpf_query']) || $_GET['wbwpf_query'] == "") return $result_filters; //todo: now if wbf_query is empty, the detected filters are not overridden. Is this the desired behavior?

		$wrapped_params = $_GET['wbwpf_query'];
		$unwrapped_filters = Filter_Factory::parse_stringified_params($wrapped_params);

		$result_filters = $unwrapped_filters; //WBWPF Query always override all:

		return $result_filters;
	}

	/**
	 * Get the IDS of products with a specified col value
	 *
	 * @param $col_name
	 * @param $col_value
	 *
	 * @return array
	 */
	public function get_products_by_col($col_name,$col_value){
		return $this->DB->Backend->get_products_id_by_property( $col_name, $col_value );
	}

	/**
	 * Register plugins widgets
	 *
	 * @hooked 'widgets_init'
	 */
	public function register_widgets(){
		register_widget(__NAMESPACE__."\\widgets\\Filters");
	}

	/**
	 * Compatibility layer for Waboot Themes
	 *
	 * @hooked 'init,14'
	 */
	public function waboot_wc_async_compatibility(){
		remove_action('waboot/woocommerce/loop','Waboot\woocommerce\loop_template');
		add_action('waboot/woocommerce/loop',[$this,'waboot_wc_async_loop']);
	}

	/**
	 * Override default Waboot product loop template
	 *
	 * @hooked 'waboot/woocommerce/loop'
	 */
	public function waboot_wc_async_loop(){
		wbwpf_show_products_async();
	}
}
