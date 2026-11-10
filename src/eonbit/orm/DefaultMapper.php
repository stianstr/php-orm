<?php

namespace eonbit\orm;

class DefaultMapper {
	// implements Mapper { # todo: interface

	protected $class;
	private $table;

	function __construct($class) {
		$this->class = $class;
	}

	public function primary_key() {
		return '_id';
	}

	public function table() {
		if (!$this->table) {
			$class_with_namespace = explode('\\', $this->class);
			$class = array_pop($class_with_namespace);
			$this->table = $this->from_camel_case($class);
		}
		return $this->table;
	}

	public function to_object($array) {
		$class = $this->class;
		$obj = new $class();
		foreach (get_class_vars(get_class($obj)) as $key => $value) {
			if (isset($array[$key]))
				$obj->$key = $array[$key];
		}
		return $obj;
	}

	public function from_object($obj) {
		$array = [];
		foreach ($obj as $key => $value) {
			$array[$key] = $value;
		}
		return $array;
	}
	
	public function get_repository_class() {
		return ''; // Will use default if not overridden
	}

	private function from_camel_case($input) {
		  preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
		  $ret = $matches[0];
		  foreach ($ret as &$match) {
			$match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
		  }
		  return implode('_', $ret);
	}

}
