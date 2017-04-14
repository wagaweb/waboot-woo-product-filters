<?php

namespace WBWPF\includes;

class AjaxEndpoint{
	public function __construct() {}

	public function setup_endpoints(){
		add_action("wp_ajax_"."get_values_for_filter",[$this,"get_values_for_filter"]);
		add_action("wp_ajax_nopriv_"."get_values_for_filter",[$this,"get_values_for_filter"]);

		add_action("wp_ajax_"."get_products_for_filters",[$this,"get_products_for_filters"]);
		add_action("wp_ajax_nopriv_"."get_products_for_filters",[$this,"get_products_for_filters"]);
	}

	/**
	 * Ajax endpoint to get the paged available products for a combination of filters
	 */
	public function get_products_for_filters(){
		$filters = isset($_POST['filters']) ? $_POST['filters'] : [];

		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;

		$get_posts_args = [
			'posts_per_page' => apply_filters( 'loop_shop_per_page', get_option( 'posts_per_page' ) ),
			'paged' => $page,
			'page' => $page
		];

		if(empty($filters)){
			$filter_query = Query_Factory::build([],"product_id","DESC");
		}else{
			$filter_query = Query_Factory::build([],"product_id","DESC"); //todo: change this
		}

		if($filter_query instanceof Filter_Query){
			$ids = $filter_query->get_results(Filter_Query::RESULT_FORMAT_IDS);
			if(is_array($ids) && count($ids) > 0){
				$get_posts_args['post__in'] = $ids;
			}else{
				$get_posts_args['post__in'] = [0];
			}
		}

		if($filter_query->query_variations){
			$get_posts_args['post_type'] = ['product','product_variation'];
		}else{
			$get_posts_args['post_type'] = ['product'];
		}

		$raw_products = get_posts($get_posts_args);

		if(is_array($raw_products) && !empty($raw_products)){
			$products = [];

			foreach ($raw_products as $raw_product){
				$wc_product = wc_get_product($raw_product->ID);
				//$wc_product_meta = get_post_meta($raw_product->ID);

				$products[] = [
					'ID' => $raw_product->ID,
					'title' => $wc_product->get_title(),
					'price' => $wc_product->get_display_price(),
					'price_html' => $wc_product->get_price_html(),
					'image' => $wc_product->get_image()
				];
			}

			$products = apply_filters("wbwpf/ajax/get_products/retrieved",$products,$filters);
		}else{
			$products = [];
		}

		wp_send_json_success($products);
	}

	/**
	 * Async endpoint to get the available values for a filter
	 */
	public function get_values_for_filter(){
		$filter_slug = isset($_POST['slug']) ? $_POST['slug'] : "";

		if(!isset($filter_slug) || empty($filter_slug)){
			wp_send_json_error([
				'error' => "Invalid or empty filter slug"
			]);
		}

		$values = [];

		$plugin = \WBWPF\Plugin::get_instance_from_global();
		$settings = $plugin->get_plugin_settings();
		if(!isset($settings['filters_params'])) $settings['filters_params'] = [];

		$dataType_slug = $settings['filters_params'][$filter_slug]['dataType'];
		$uiType_slug = $settings['filters_params'][$filter_slug]['uiType'];

		if(isset($dataType_slug) && isset($uiType_slug)){
			$f = Filter_Factory::build($filter_slug,$dataType_slug,$uiType_slug);

			if($f instanceof \WBWPF\includes\Filter){
				$all_values = $f->dataType->getAvailableValuesFor($f->slug);

				//Now we need to instantiate a new Filter_Query and then retrieve the available_col_values for current filter
				if(isset($_POST['current_filters'])){
					$slugs = [];
					$filter_values = [];
					foreach ($_POST['current_filters'] as $k => $v){
						$slugs[] = $v['slug'];
						$filter_values[$v['slug']] = $v['value'];
					}
					$filters = Filter_Factory::build_from_slugs($slugs,$filter_values);
					if(is_array($filters) && !empty($filters)){
						$filters_query = Query_Factory::build($filters);
						if($filters_query instanceof Filter_Query){
							$filters_query->perform(Filter_Query::RESULT_FORMAT_IDS);
						}else{
							wp_send_json_error([
								'error' => "Unable to instance Filter_Query"
							]);
						}
					}else{
						wp_send_json_error([
							'error' => "Unable to instance Filters"
						]);
					}
				}

				//Now we build a values array each one with hidden \ visible property
				foreach ($all_values as $retrieved_value_id => $retrieved_value_label){
					$is_visible = true;
					$is_selected = false;
					if(isset($filters_query)){
						$is_visible = isset($filters_query->available_col_values[$f->slug]) && in_array($retrieved_value_id,$filters_query->available_col_values[$f->slug]);
						if(isset($filters)){
							$is_selected = call_user_func(function() use($filters,$filter_slug,$retrieved_value_id){
								foreach ($filters as $f){
									if($f->slug == $filter_slug && is_array($f->current_values)){
										if(in_array($retrieved_value_id,$f->current_values)){
											return true;
										}
									}
								}
								return false;
							});
						}
					}

					$values[] = [
						'visible' => $is_visible,
						'id' => $retrieved_value_id,
						'label' => $retrieved_value_label,
						'selected' => $is_selected
					];
				}

				wp_send_json_success($values);
			}
		}

		wp_send_json_error([
			'error' => "Unable to retrieve dataType or uiType for $filter_slug"
		]);
	}
}