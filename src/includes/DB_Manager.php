<?php

namespace WBWPF\includes;

use WBWPF\db_backends\Backend;

class DB_Manager{
	/**
	 * @var Backend
	 */
	var $Backend;

	/**
	 * DB_Manager constructor.
	 *
	 * @param Backend $Backend
	 */
	function __construct(Backend $Backend) {
		$this->Backend = $Backend;
	}

	/**
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	function __call( $name, $arguments ) {
		if(method_exists($name,$this->Backend)){
			return $this->Backend->$name($arguments);
		}else{
			throw new \Exception("No method called $name found in DB_Manager or its Backend");
		}
	}
}