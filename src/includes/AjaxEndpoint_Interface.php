<?php

namespace WBWPF\includes;

interface AjaxEndpoint_Interface{
	public function setup_endpoints();
	public function get_products_for_filters();
	public function get_values_for_filter();
}