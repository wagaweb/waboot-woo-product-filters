<?php


if(!function_exists("wbwpf_show_filters")):
	/**
	 * Display filters
	 *
	 * @param array $args the params for displaying the filters
	 */
	function wbwpf_show_filters($args){
		//Testing:
		$args = [
			'product_cat' => [
				'type' => "checkbox", //Come visualizzarli
				'dataType' => 'taxonomies' //Come prende i valori
			],
			'product_tag' => [
				'type' => "checkbox",
				'dataType' => 'taxonomies'
			],
		];

		$plugin = \WBWPF\Plugin::get_instance_from_global();

		$filters = [];

		foreach ($args as $filter_slug => $filter_params){
			if(!isset($filter_params['dataType']) || !isset($filter_params['type'])) continue;

			$dataType_slug = $filter_params['dataType'];
			$uiType_slug = $filter_params['type'];

			$f = \WBWPF\includes\Filter_Factory::build($filter_slug,$dataType_slug,$uiType_slug);

			if($f){
				if(isset($filter_params['label'])){
					$f->set_label($filter_params['label']);
				}else{
					$f->set_label();
				}
				$filters[] = $f;
			}
		}

		$v = new \WBF\components\mvc\HTMLView("views/filters.php",$plugin);
		$v->display([
			'filters' => $filters,
			'has_filters' => is_array($filters) && !empty($filters),
			'textdomain' => $plugin->get_textdomain()
		]);
	}
endif;