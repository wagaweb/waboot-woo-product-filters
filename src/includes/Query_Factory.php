<?php

namespace WBWPF\includes;

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
	 *
	 * @return Filter_Query
	 */
	public static function build($filters = [], $orderby = self::DEFAULT_ORDERBY, $order = self::DEFAULT_ORDER, $limit = -1, $offset = -1){
		global $wpdb;

		$query = new Filter_Query($orderby,$order,$limit,$offset);

		//Here we might have the woocommerce ordering and orderby names, we must standardize them to our query system
		$ordering = self::transform_wc_ordering_params($orderby,$order);

		$query->set_ordering($ordering['orderby'],$ordering['order']);
		$query->set_pagination($offset,$limit);
		$query->select_statement = "product_id";
		$query->from_statement = $wpdb->prefix.Plugin::CUSTOM_PRODUCT_INDEX_TABLE;

		//Additional settings:
		$plugin = Plugin::get_instance_from_global();
		if($plugin instanceof Plugin){
			$settings = $plugin->get_plugin_settings();
			$query_settings = [
				'show_variations' => $settings['show_variations'],
				'hide_parent_products' => $settings['hide_parent_products'],
			];
			$query_settings = apply_filters("wbwpf/query/settings",$query_settings);
			$query->query_variations = $query_settings['show_variations'];
			$query->do_not_query_parent_product = $query_settings['hide_parent_products'];
		}else{
			$query->query_variations = false;
			$query->do_not_query_parent_product = false;
		}

		if(!empty($filters)){
			foreach ($filters as $filter){
				if($filter instanceof Filter){
					$filter->parse_query($query);
				}
			}

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
			$shop_page_id = wc_get_page_id('shop');
			$queried_object = get_queried_object_id();
			if(is_numeric($shop_page_id) && $shop_page_id > 0 && $shop_page_id == $queried_object){
				$query->build(); //Build the query for selecting all the products
			}
		}

		return $query;
	}

	/**
	 * Build a Filter_Query from params
	 *
	 * @param array $active_filters
	 * @param array $current_values
	 *
	 * @return Filter_Query
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
	 * @return Filter_Query
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
	 * @return Filter_Query
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
	 * @return Filter_Query
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
	 * @return bool|Filter_Query
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
			$params['orderby'] = $_GET['wbwpf_orderby'];
		}elseif(isset($_POST['wbwpf_orderby'])){
			$params['orderby'] = $_POST['wbwpf_orderby'];
		}

		if(isset($wp_query->query['order'])){
			$params['order'] = $wp_query->query['order'];
		}elseif(isset($_GET['wbwpf_order'])){
			$params['order'] = $_GET['wbwpf_order'];
		}elseif(isset($_POST['wbwpf_order'])){
			$params['order'] = $_POST['wbwpf_order'];
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
			$transformation = $plugin->DB->Backend->transform_wc_ordering_param($orderby,$order);
		}

		return $transformation;
	}
}