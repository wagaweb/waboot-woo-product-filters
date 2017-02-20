<?php

/**
 * Display filters
 *
 * @param $args
 */
function wbwpf_show_filters($args){
	//Testing:
	$args = [
		'price' => [
			'type' => "range",
			'dataType' => 'price'
		],
		'product_cat' => [
			'type' => "checkbox", //Come visualizzarli
			'dataType' => 'taxonomy' //Come prende i valori
		],
	];

	$plugin = \WBF\components\pluginsframework\BasePlugin::get_instances_of("waboot-woo-product-filters");
	if(!$plugin instanceof \WBWPF\Plugin) return;

	$dataTypes = $plugin->get_available_dataTypes();
	$uiTypes = $plugin->get_available_uiTypes();

	$filters = [];

	foreach ($args as $filter_slug => $filter_params){
		if(!isset($filter_params['dataType']) || !isset($filter_params['type'])) continue;

		$dataType_slug = $filter_params['dataType'];
		$uiType_slug = $filter_params['type'];

		if(!isset($dataTypes[$dataType_slug])) continue;
		$dataTypeClassName = $dataTypes[$dataType_slug];

		if(!isset($uiTypes[$uiType_slug])) continue;
		$uiTypeClassName = $uiTypes[$uiType_slug];

		$datatype = new $dataTypeClassName();
		$uitype = new $uiTypeClassName();

		$f = new \WBWPF\includes\Filter($filter_slug,$datatype,$uitype);

		$filters[] = $f;
	}

	$v = new \WBF\components\mvc\HTMLView("views/filters.php",$plugin);
	$v->display([
		'filters' => $filters
	]);
}