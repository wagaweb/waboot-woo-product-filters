<?php

namespace WBWPF\includes;

use WBWPF\db_backends\Backend;

interface Filter_Query_Interface{
	function __construct(Backend $backend);
	public function set_ordering($property,$direction);
	public function set_pagination($offset,$limit);
	public function set_fields_to_retrieve($fields);
	public function set_source($source);
	public function add_condition($condition);
	public function add_sub_query(Filter_Query_Interface $query);
	public function inject_properties($properties);
	public function prepare($statement,$values);
	public function perform($result_format);
	public function parse_filters($filters);
	public function get_results($result_format);
	public function has_query();
	public function has_products();
	public function build();
	public function build_from_sub_queries();
}