<?php

function wbwpf_show_filters($args){
	$defaults = [
		'price' => [
			'type' => "range",
			'dataType' => 'price'
		],
		'product_cat' => [
			'type' => "checkbox", //Come visualizzarli
			'dataType' => 'taxonomy' //Come prende i valori
		],
	];
}