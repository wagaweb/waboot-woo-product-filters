<?php

namespace WBWPF\filters;

abstract class Filter{
	/*
	 * Adds the correct "where" clause to the query
	 */
	public function parse_query(&$query){}
}