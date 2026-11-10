<?php

namespace eonbit\orm;

class DB {

	private $driver;
	private $mappers;

	function __construct(Driver $driver) {
		$this->driver = $driver;
		$this->mappers = new Mappers();
	}

	public function set_slow_query_log_file($file) {
		$this->driver->set_slow_query_log_file($file);
	}

	public function set_slow_query_threshold($millisec) {
		$this->driver->set_slow_query_threshold($millisec);
	}

	public function begin() {
		$this->driver->execute('BEGIN');
	}

	public function commit() {
		$this->driver->execute('COMMIT');
	}

	public function rollback() {
		$this->driver->execute('ROLLBACK');
	}

	public function orm($class) {
		$mapper = $this->mappers->get($class);
		$class = $mapper->get_repository_class() ?: Repository::class;
		return new $class($this->driver, $class, $mapper);
	}

	public function table($table, $params = null) {
		$query = new Query($this->driver);
		$query->table($table, $params);
		return $query;
	}

	public function sql($sql, $params = null) {
		$query = new Query($this->driver);
		$query->raw($sql, $params);
		return $query;
	}

	public function register_mapper_class($entity_class, $mapper_class) {
		$this->mappers->register_class($entity_class, $mapper_class);
	}

	public function register_mapper_namespace($entity_namespace, $mapper_namespace) {
		$this->mappers->register_namespace($entity_namespace, $mapper_namespace);
	}

	public function register_mapper_default($mapper_class) {
		$this->mappers->register_default($mapper_class);
	}

}
