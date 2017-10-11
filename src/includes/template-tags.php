<?php

if(!function_exists("wbwpf_get_current_active_filters")):
	/**
	 * Get the current active filters in array or json format
	 *
	 * @return array|string
	 */
	function wbwpf_get_current_active_filters($json_output = false){
		$available_filters = \WBWPF\includes\Filter_Factory::parse_available_params();
		if(!$json_output){
			return $available_filters;
		}else{
			$str = json_encode($available_filters);
			return $str;
		}
	}
endif;

if(!function_exists("wbwpf_get_base_url")):
	function wbwpf_get_base_url(){
		if(is_product_taxonomy()){
			$o = get_queried_object();
			if($o instanceof \WP_Term){
				$link = get_term_link($o->term_id,$o->taxonomy);
				return $link;
			}
		}
		return wc_get_page_permalink("shop");
	}
endif;

if(!function_exists("wbwpf_show_products_async")):
	/**
	 * Display the template to handle the async product list
	 */
	function wbwpf_show_products_async(){
		$theme = wp_get_theme();

		$tpl = "src/views/async-loops/".$theme->get_template().".php"; //search for a standard template
		try{
			//Search for a loop template with the name of the current template within the plugin (this is intended to fail most of the time)
			$v = new \WBF\components\mvc\HTMLView($tpl,"waboot-woo-product-filters");
			$v->display();
		}catch (Exception $e){
			$plugin = \WBWPF\Plugin::get_instance_from_global();
			if($plugin->Settings->use_custom_product_loop_template){
				$tpl = "src/views/async-loops/base-custom.php";
			}else{
				$tpl = "src/views/async-loops/base.php";
			}
			$v = new \WBF\components\mvc\HTMLView($tpl,"waboot-woo-product-filters");
			$v->display();
		}
	}
endif;

if(!function_exists("wbwpf_show_filters_async")):
	function wbwpf_show_filters_async($args = [], $display_apply_button = true){
		wbwpf_show_filters($args,true,$display_apply_button);
	}
endif;

if(!function_exists("wbwpf_show_filters")):
	/**
	 * Display filters
	 *
	 * @param array $args the params for displaying the filters
	 * @param bool $async
	 * @param bool $display_apply_button
	 *
	 * @example for $args:
	 *
	 * [
	 *      'product_cat' => [
	 *          'type' => "checkbox" //How to display the values  //These values ARE NOT REQUIREDs
	 *          'dataType' => "taxonomies" //How to manage the values  //These values ARE NOT REQUIRED
	 *      ],
	 *      'product_tag' => [
	 *          'type' => "checkbox" //These values ARE NOT REQUIRED
	 *          'dataType' => "taxonomies"  //These values ARE NOT REQUIRED
	 *      ]
	 *      ...
	 * ]
	 *
	 */
	function wbwpf_show_filters($args = [],$async = false,$display_apply_button = true){
		$plugin = \WBWPF\Plugin::get_instance_from_global();
		$settings = $plugin->get_plugin_settings();
		if(!isset($settings['filters_params'])) $settings['filters_params'] = [];

		if(empty($args)){
			foreach ($settings['filters_params'] as $filter_slug => $filters_param){
				$args[] = $filter_slug;
			}
		}

		$filters = [];

		foreach ($args as $filter_slug => $filter_params){
			if(is_int($filter_slug)){ //We have an array of simple strings
				$filter_slug = $filter_params;
				$filter_params = $filter_params = [];
			}

			$dataType_slug = isset($filter_params['dataType']) ? $filter_params['dataType'] : $settings['filters_params'][$filter_slug]['dataType'];
			$uiType_slug = isset($filter_params['type']) ? $filter_params['type'] : $settings['filters_params'][$filter_slug]['uiType'];

			if(!isset($dataType_slug) || !isset($uiType_slug)) continue;

			$f = \WBWPF\includes\Filter_Factory::build($filter_slug,$dataType_slug,$uiType_slug);

			if($f instanceof \WBWPF\includes\Filter){
				if(isset($filter_params['label'])){
					$f->set_label($filter_params['label']);
				}else{
					$f->set_label();
				}
				$filters[] = $f;
			}
		}

		$form_action_url = wbwpf_get_base_url();

		if(isset($_GET['orderby'])){
			$form_action_url = add_query_arg(["orderby"=>$_GET['orderby']],$form_action_url);
		}

		$has_products = call_user_func(function(){
			$q = \WBWPF\Plugin::get_query_from_global();
			if($q instanceof \WBWPF\includes\Filter_Query && !$q->has_products()){
				return false;
			}
			return true;
		});

		$container_classes[] = "wbwpf-filters";
		if(!$has_products) $container_classes[] = "no-products";
		$container_classes = apply_filters("wbwpf/filters/container/classes",$container_classes);

		$v = new \WBF\components\mvc\HTMLView("views/filters.php",$plugin);
		$v->display([
			'container_classes' => implode(" ",$container_classes),
			'filters' => $filters,
			'form_action_url' => $form_action_url,
			'has_filters' => is_array($filters) && !empty($filters),
			'has_products' => $has_products,
			'async' => $async,
			'display_apply_button' => $display_apply_button,
			'textdomain' => $plugin->get_textdomain()
		]);
	}
endif;

if(!function_exists("wbwpf_filters_breadcrumb")):
	/**
	 * Display filters breadcrumb
	 */
	function wbwpf_filters_breadcrumb(){
		$filters = \WBWPF\includes\Filter_Factory::build_from_available_params();
		$plugin = \WBWPF\Plugin::get_instance_from_global();
		if(is_array($filters) && !empty($filters)){
			$posted_filters = \WBWPF\includes\Filter_Factory::parse_filters_array($filters);
			$breadcrumb = [];
			$i = 0;
			foreach ($filters as $f){
				if(!is_array($f->current_values)) continue;

				$skip = $f->is_current_filter();
				$skip = apply_filters("wbwpf/breadcrumb/skip_parsing",$skip,$f);
				if($skip) continue;

				foreach ($f->current_values as $current_value){

					//Getting the query string that includes the current filter only
					$single_query_string =  call_user_func(function() use($f,$current_value){
						$single_filter_params = [
							$f->slug => [
								'type' => $f->uiType->type_slug,
								'dataType' => $f->dataType->type_slug
							]
						];
						$single_filter_values = [
							$f->slug => $current_value
						];
						$single_query_string = \WBWPF\includes\Filter_Factory::stringify_from_params($single_filter_params,$single_filter_values,true);
						return $single_query_string;
					});

					//Getting the current query string WITHOUT the current filter
					$current_query_string_without_self = call_user_func(function() use($posted_filters,$f,$current_value){
						$cloned_posted_filters = $posted_filters;
						if(isset($cloned_posted_filters['values']) && isset($cloned_posted_filters['values'][$f->slug])){
							foreach ($cloned_posted_filters['values'][$f->slug] as $k => $v){
								if($v == $current_value) unset($cloned_posted_filters['values'][$f->slug][$k]);
								if(empty($cloned_posted_filters['values'][$f->slug])) unset($cloned_posted_filters['values'][$f->slug]);
							}
						}
						$current_query_string_without_self = \WBWPF\includes\Filter_Factory::stringify_from_params($cloned_posted_filters['filters'],$cloned_posted_filters['values'],true);
						return $current_query_string_without_self;
					});

					$breadcrumb[$i] = [
						'value' => $current_value,
						'label' => $f->dataType->getPublicItemLabelOf($current_value,$f),
						'single_query_string' => $single_query_string,
						'current_query_string_without_self' => $current_query_string_without_self,
						'cumulated_query_string' => $i > 0 ? $breadcrumb[$i-1]['cumulated_query_string'].\WBWPF\includes\Filter_Factory::WPWPF_QUERY_SEPARATOR.$single_query_string : $single_query_string
					];

					$breadcrumb[$i]['link'] = add_query_arg(["wbwpf_query"=>$breadcrumb[$i]['cumulated_query_string']]);
					$breadcrumb[$i]['delete_link'] = $breadcrumb[$i]['current_query_string_without_self'] != "" ? add_query_arg(["wbwpf_query"=>$breadcrumb[$i]['current_query_string_without_self']]) : wbwpf_get_base_url();
					//todo: now if wbf_query is empty, the detected filters are not overridden. Is this the desired behavior?

					$breadcrumb[$i] = apply_filters("wbwpf/breadcrumb/item",$breadcrumb[$i]);

					$i++;
				}
			}

			$v = new \WBF\components\mvc\HTMLView("views/filters-breadcrumb.php",$plugin);
			$v->display([
				'clear_all_label' => __("Clear all",$plugin->get_textdomain()),
				'clear_all_url' => wbwpf_get_base_url(),
				'breadcrumb' => $breadcrumb,
				'has_items' => !empty($breadcrumb)
			]);
		}
	}
endif;