<?php

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

if(!function_exists("wbwpf_show_filters")):
	/**
	 * Display filters
	 *
	 * @param array $args the params for displaying the filters
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
	function wbwpf_show_filters($args = []){
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

		$v = new \WBF\components\mvc\HTMLView("views/filters.php",$plugin);
		$v->display([
			'filters' => $filters,
			'form_action_url' => $form_action_url,
			'has_filters' => is_array($filters) && !empty($filters),
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
				if($f->is_current_filter()) continue;
				foreach ($f->current_values as $current_value){

					$single_filter_params = [
						$f->slug => [
							'type' => $f->uiType->type_slug,
							'dataType' => $f->dataType->type_slug
						]
					];
					$single_filter_values = [
						$f->slug => $current_value
					];
					$single_query_string = \WBWPF\includes\Filter_Factory::stringify_from_params($single_filter_params,$single_filter_values);

					$cloned_posted_filters = $posted_filters;
					if(isset($cloned_posted_filters['values']) && isset($cloned_posted_filters['values'][$f->slug])){
						foreach ($cloned_posted_filters['values'][$f->slug] as $k => $v){
							if($v == $current_value) unset($cloned_posted_filters['values'][$f->slug][$k]);
							if(empty($cloned_posted_filters['values'][$f->slug])) unset($cloned_posted_filters['values'][$f->slug]);
						}
					}
					$current_query_string_without_self = \WBWPF\includes\Filter_Factory::stringify_from_params($cloned_posted_filters['filters'],$cloned_posted_filters['values']);

					$breadcrumb[$i] = [
						'label' => $f->dataType->getPublicItemLabelOf($current_value,$f),
						'single_query_string' => $single_query_string,
						'current_query_string_without_self' => $current_query_string_without_self,
						'cumulated_query_string' => $i > 0 ? $breadcrumb[$i-1]['cumulated_query_string']."-".$single_query_string : $single_query_string
					];

					$breadcrumb[$i]['link'] = add_query_arg(["wbwpf_query"=>$breadcrumb[$i]['cumulated_query_string']]);
					$breadcrumb[$i]['delete_link'] = add_query_arg(["wbwpf_query"=>$breadcrumb[$i]['current_query_string_without_self']]);

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