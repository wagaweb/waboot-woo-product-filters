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

		$plugin = \WBWPF\Plugin::get_instance_from_global();

		$page = isset($_POST['page']) ? max(1,intval($_POST['page'])) : 1; //See: result-count.php
		$posts_per_page = apply_filters( 'loop_shop_per_page', get_option( 'posts_per_page' ) );

		$get_posts_args = [
			'posts_per_page' => $posts_per_page,
			'paged' => $page,
			'page' => $page
		];

		//Detect ordering (see: class-wc-query.php)
		$ordering = isset($_POST['ordering']) && !empty($_POST['ordering']) ? $_POST['ordering'] : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
		$orderby_value = $ordering;
		$orderby_value = explode( '-', $orderby_value );
		$orderby = esc_attr( $orderby_value[0] );
		$order = ! empty( $orderby_value[1] ) ? $orderby_value[1] : '';

		$wc_query = new \WC_Query(); //Yes, we need to redeclare this, or we have to rewrite the entire function :(
		$wc_ordering_args = $wc_query->get_catalog_ordering_args($orderby,$order);
		$get_posts_args = array_merge($get_posts_args,$wc_ordering_args);

		if(empty($filters)){
			$filter_query = Query_Factory::build([],$ordering);
		}else{
			$active_filters = call_user_func(function() use($filters){
				$slugs = [];
				$values = [];
				foreach ($filters as $filter){
					$slugs[] = $filter['slug'];
					$values[$filter['slug']] = $filter['value'];
				}
				return Filter_Factory::build_from_slugs($slugs,$values);
			});
			if(is_array($active_filters) && !empty($active_filters)){
				$filter_query = Query_Factory::build($active_filters,$ordering);
			}else{
				$filter_query = Query_Factory::build([],$ordering);
			}
		}

		if(!$filter_query instanceof Filter_Query){
			wp_send_json_error(['error' => "Unable to build Filter_Query"]);
		}

		$ids = $filter_query->get_results(Filter_Query::RESULT_FORMAT_IDS);
		if(is_array($ids) && count($ids) > 0){
			$get_posts_args['post__in'] = $ids;
		}else{
			$get_posts_args['post__in'] = [0];
		}

		if($filter_query->query_variations){
			$get_posts_args['post_type'] = ['product','product_variation'];
		}else{
			$get_posts_args['post_type'] = ['product'];
		}

		$get_posts_args = apply_filters('wbwpf/ajax/get_products_for_filters/args', $get_posts_args);

		$raw_products = get_posts($get_posts_args);

		if(is_array($raw_products) && !empty($raw_products)){
			$products = [];

			$use_custom_product_loop_template = $plugin->Settings->use_custom_product_loop_template;
			//Above setting can be overridden:
			if(isset($_POST['use_custom_product_loop_template']) && !empty($_POST['use_custom_product_loop_template'])){
				$use_custom_product_loop_template = $_POST['use_custom_product_loop_template'] == "1";
			}

			foreach ($raw_products as $raw_product){
				$wc_product = wc_get_product($raw_product->ID);
				//$wc_product_meta = get_post_meta($raw_product->ID);

				if(!$use_custom_product_loop_template){
					//Retrieve a full html output

					$content = $this->get_content_product_ouput($wc_product);
					$content = apply_filters("wbwpf/ajax/get_products/content",$content);

					$products[] = [
						'ID' => $raw_product->ID,
						'content' => $content,
						'wrapper_class' => implode(" ",get_post_class('wbwpf-product-wrapper',$raw_product->ID))
					];
				}else{
					//Retrieve specific fields to display later

					$products[] = [
						'ID' => $raw_product->ID,
						'post_class' => implode(" ",get_post_class('',$raw_product->ID)),
						'img_html' => $wc_product->get_image(),
						'title' => $wc_product->get_title(),
						'price' => $wc_product->get_display_price(),
						'price_html' => $wc_product->get_price_html(),
						'image' => $wc_product->get_image(),
						'add_to_cart' => "",
						'rating_html' => $wc_product->get_rating_html()
					];
				}
			}

			if($use_custom_product_loop_template){
				$products = apply_filters("wbwpf/ajax/get_products/retrieved",$products,$filters);
			}
		}else{
			$products = [];
		}

		//Additional data
		$total_pages = ceil(count($ids) / $posts_per_page);
		$found_products = isset($filter_query) ? count($filter_query->found_products) : 0;
		$showing_from = ( $posts_per_page * $page ) - $posts_per_page + 1; //See: result-count.php
		$showing_to = min($found_products, $posts_per_page * $page); //See: result-count.php

		if($found_products <= $posts_per_page || $posts_per_page == -1){ //See: result-count.php
			$result_count_label = sprintf( _n( 'Showing the single result', 'Showing all %d results', $found_products, 'woocommerce' ), $found_products );
		}else{
			$result_count_label = sprintf( _nx( 'Showing the single result', 'Showing %1$d&ndash;%2$d of %3$d results', $found_products, '%1$d = first, %2$d = last, %3$d = total', 'woocommerce' ), $showing_from, $showing_to, $found_products );
		}

		$result = [
			'products' => $products,
			'found_products' => $found_products,
			'current_page' => $page,
			'total_pages' => $total_pages,
			'showing_from' => $showing_from,
			'showing_to' => $showing_to,
			'products_per_page' => $posts_per_page,
			'result_count_label' => $result_count_label
		];

		wp_send_json_success($result);
	}

	/**
	 * Get the output of content-product.php for a specific $product
	 *
	 * @param \WC_Product|mixed $product
	 *
	 * @return string
	 */
	private function get_content_product_ouput($product){
		if(!is_object($product)) return "[Invalid object provided]";

		ob_start();
		$GLOBALS['product'] = $product;
		$GLOBALS['post'] = $product->post;
		wc_get_template_part( 'content', 'product' );
		$output = trim(preg_replace( "|[\r\n\t]|", "", ob_get_clean()));

		return $output;
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
				$all_values = apply_filters("wbwpf/filter/available_values",$all_values,$f);

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