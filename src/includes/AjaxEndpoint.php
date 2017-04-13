<?php

namespace WBWPF\includes;

class AjaxEndpoint{
	public function __construct() {}

	public function setup_endpoints(){
		add_action("wp_ajax_"."get_values_for_filter",[$this,"get_values_for_filter"]);
		add_action("wp_ajax_nopriv_"."get_values_for_filter",[$this,"get_values_for_filter"]);
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
					if(isset($filters_query)){
						$is_visible = isset($filters_query->available_col_values[$f->slug]) && in_array($retrieved_value_id,$filters_query->available_col_values[$f->slug]);
					}

					$values[] = [
						'visible' => $is_visible,
						'id' => $retrieved_value_id,
						'label' => $retrieved_value_label
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