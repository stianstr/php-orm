<?php

namespace eonbit\orm;

use Exception;

class Repository {

	protected $driver;
	protected $class;
	protected $mapper;

	function __construct($driver, $class, $mapper) {
		$this->driver = $driver;
		$this->class = $class;
		$this->mapper = $mapper;
	}

	function select($sql_or_array, $params = null) {
		return $this->query()->select($sql_or_array, $params);
	}

	function query() {
		$query = new Query($this->driver);
		$query->table($this->mapper->table());
		$query->mapper($this->mapper);
		return $query;
	}

	function find($id) {
		return $this->query()
			->where($this->mapper->primary_key() . '=?', [$id])
			->first();
	}

	function all() {
		return $this->query()->all();
	}

	function where_in($field, $values) {
		if (!$values) {
			return $this->where("-1 = 1");
		}
		$placeholders = [];
		foreach ($values as $unused) {
			$placeholders[] = '?';
		}
		return $this->where("$field in (" . implode(',', $placeholders) . ")", $values);
	}

	function where($sql_or_array, $params = null) {
		return $this->query()
			->where($sql_or_array, $params);
	}

	function order_by($sql_or_array, $params = null) {
		return $this->query()
			->order_by($sql_or_array, $params);
	}

	function group_by($sql_or_array, $params = null) {
		return $this->query()
			->group_by($sql_or_array, $params);
	}

	function save($object) {
		$array = $this->mapper->from_object($object);
		$id = empty($array[ $this->mapper->primary_key() ]) ? null : $array[ $this->mapper->primary_key() ];
		$query = $this->query();
		if ($id) {
			$query->where([$this->mapper->primary_key() => $id]);
			$query->update($array);
		} else {
			$id = $query->insert($array);
			$object->{ $this->mapper->primary_key() } = $id; // todo: use mapper to set
		}
	}

	/**
	 * @return int affected row count (for MySQL ON DUPLICATE KEY UPDATE: 1=inserted, 2=updated, 0=unchanged)
	 */
	function upsert($object, $no_update_fields = []) {
		$array = $this->mapper->from_object($object);
		$id_field = $this->mapper->primary_key();
		$query = $this->query();
		if (!in_array($id_field, $no_update_fields)) {
			$no_update_fields[] = $id_field;
		}
		return $query->upsert($array, $no_update_fields);
	}

	function delete($object) {
		$id = empty($object->{ $this->mapper->primary_key() }) ? null : $object->{ $this->mapper->primary_key() };
		$this->delete_id($id);
	}

	function delete_id($id) {
		if (!$id)
			return;
		$this->query()
			->where([$this->mapper->primary_key() => $id])
			->limit(1)
			->delete();
	}

	function limit(int $limit) {
		return $this->query()->limit($limit);
	}

}
