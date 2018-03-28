<?php

namespace WBWPF\includes;

use WBWPF\db_backends\Backend;
use WBWPF\db_backends\MYSQL;
use WBWPF\Plugin;

class Query_Factory{
	const DEFAULT_ORDERBY = "product_id";
	const DEFAULT_ORDER = "DESC";

	/**
	 * Create a new query
	 *
	 * @param array $filters (array of \WBWPF\filters\Filter)
	 * @param string $orderby
	 * @param string $order
	 * @param int $limit
	 * @param int $offset
	 * @param string $query_class
	 * @param string $backend_class
	 *
	 * @return Filter_Query_Interface|\WP_Error
	 */
	public static function build($filters = [], $orderby = self::DEFAULT_ORDERBY, $order = self::DEFAULT_ORDER, $limit = -1, $offset = -1, $query_class = null, $backend_class = null){
		try{
			global $wpdb;

			if(!isset($backend_class)){
				$backend_class = '\WBWPF\db_backends\MYSQL';
			}

			if(!isset($query_class)){
				$query_class = '\WBWPF\includes\Filter_Query';
			}

			$Backend = new $backend_class();
			if(!$Backend instanceof Backend){
				return new \WP_Error('Invalid backend class');
			}

			$query = new $query_class($Backend);
			if(!$query instanceof Filter_Query_Interface){
				return new \WP_Error('Invalid query class');
			}

			//Here we might have the woocommerce ordering and orderby names, we must standardize them to our query system
			$ordering = self::transform_wc_ordering_params($orderby,$order);

			$query->set_ordering($ordering['orderby'],$ordering['order']);
			$query->set_pagination($offset,$limit);
			$query->set_fields_to_retrieve("product_id");
			$query->set_source($wpdb->prefix.Plugin::CUSTOM_PRODUCT_INDEX_TABLE);

			//Additional settings:
			do_action('wbwpf/query/instance/after_initial_setup',$query);
			$properties = apply_filters('wbwpf/query/instance/additional_properties',[]);
			$query->inject_properties($properties);

			if(!empty($filters)){
				$query->parse_filters($filters);

				/*
				 * We are testing two database structures, see Plugin::populate_products_index()
				 */

				/*
				 * This is for the first: (we do not use sub queries)
				 */
				//$query->build();

				/*
				 * This is for the second:
				 */
				$query->build_from_sub_queries();
			}else{
				//Check if we are in main shop page
				if(is_shop()){
					$query->build(); //Build the query for selecting all the products
				}elseif(defined('DOING_AJAX') && DOING_AJAX){
					//If we are making a ajax request, and no filters provided, it means we are in the main shop page
					$query->build();
				}
			}

			return $query;
		}catch(\Exception $e){
			return new \WP_Error("filter-build-error",$e->getMessage()); //todo: loggin?
		}
	}

	/**
	 * Build a Filter_Query from params
	 *
	 * @param array $active_filters
	 * @param array $current_values
	 *
	 * @return Filter_Query_Interface
	 */
	public static function build_from_params($active_filters,$current_values){
		$filters = Filter_Factory::build_from_params($active_filters,$current_values);
		$ordering = self::retrieve_ordering_query_params();
		$filter_query = self::build($filters,$ordering['orderby'],$ordering['order']);
		return $filter_query;
	}

	/**
	 * Build a Filter_Query from any available params. At the moment we use this builder mainly.
	 *
	 * @return Filter_Query_Interface
	 */
	public static function build_from_available_params(){
		$filters = Filter_Factory::build_from_available_params();
		$ordering = self::retrieve_ordering_query_params();
		$filter_query = self::build($filters,$ordering['orderby'],$ordering['order']);
		return $filter_query;
	}

	/**
	 * Build a Filter_Query from $_GET params (it looks for a specific-predefined get params)
	 *
	 * @return Filter_Query_Interface
	 */
	public static function build_from_get_params(){
		$filters = Filter_Factory::build_from_get_params();
		$ordering = self::retrieve_ordering_query_params();
		$filter_query = self::build($filters,$ordering['orderby'],$ordering['order']);
		return $filter_query;
	}

	/**
	 * Setup a Filter_Query starting from current $_POST params (it looks for specific-predefined post params)
	 *
	 * @return Filter_Query_Interface
	 */
	public static function build_from_post_params(){
		$filters =  Filter_Factory::build_from_post_params();
		$ordering = self::retrieve_ordering_query_params();
		$filter_query = self::build($filters,$ordering['orderby'],$ordering['order']);
		return $filter_query;
	}

	/**
	 * Setup a Filter_Query starting from a WP_Query object
	 *
	 * @param \WP_Query|null $query
	 *
	 * @return bool|Filter_Query_Interface
	 */
	public static function build_from_wp_query(\WP_Query $query = null){
		if(!isset($query)){
			global $wp_query;
			$query = $wp_query;
		}

		$filters = Filter_Factory::build_from_wp_query($query);
		if(!empty($filters)){
			$ordering = self::retrieve_ordering_query_params();
			$filter_query = self::build($filters,$ordering['orderby'],$ordering['order']);
			return $filter_query;
		}else{
			return false;
		}
	}

	/**
	 * Retrieve ordering params from available sources (wp_query, $_GET or $_POST)
	 *
	 * @return array
	 */
	private static function retrieve_ordering_query_params(){
		global $wp_query;
		$params = [
			'order' => self::DEFAULT_ORDER,
			'orderby' => self::DEFAULT_ORDERBY
		];

		if(isset($wp_query->query['orderby'])){
			$params['orderby'] = $wp_query->query['orderby'];
		}elseif(isset($_GET['wbwpf_orderby'])){
			$params['orderby'] = sanitize_text_field($_GET['wbwpf_orderby']);
		}elseif(isset($_POST['wbwpf_orderby'])){
			$params['orderby'] = sanitize_text_field($_POST['wbwpf_orderby']);
		}

		if(isset($wp_query->query['order'])){
			$params['order'] = $wp_query->query['order'];
		}elseif(isset($_GET['wbwpf_order'])){
			$params['order'] = sanitize_text_field($_GET['wbwpf_order']);
		}elseif(isset($_POST['wbwpf_order'])){
			$params['order'] = sanitize_text_field($_POST['wbwpf_order']);
		}

		return $params;
	}

	/**
	 * Transform WooCommerce orderby and order nomenclature to a nomenclature compatible with our query system (See: MYSQL::structure_db())
	 *
	 * @param $orderby
	 *
	 * @return string
	 */
	private static function transform_wc_ordering_params($orderby,$order){
		$plugin = Plugin::get_instance_from_global();

		$transformation = [
			'orderby' => $orderby,
			'order' => $order
		];

		if($plugin instanceof Plugin){
			$transformation = $plugin->DB->transform_wc_ordering_param($orderby,$order);
		}

		return $transformation;
	}
}