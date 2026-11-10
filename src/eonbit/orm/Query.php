<?php

namespace eonbit\orm;

class Query {

	private $connection;
	private $select = [];
	private $select_params = [];
	private $from = [];
	private $from_params = [];
	private $where = [];
	private $where_params = [];
	private $order_by = [];
	private $order_by_params = [];
	private $group_by = [];
	private $group_by_params = [];
	private $offset = 0;
	private $limit = 0;
	private $raw_sql;
	private $raw_params;

	private $values = [];

	private $sql = '';
	private $params = [];

	private $mapper;

	function __construct($connection) {
		$this->connection = $connection;
	}

	function mapper($mapper) {
		$this->mapper = $mapper;
	}

	function select($sql_or_array, $params = null) {
		if (is_array($sql_or_array)) {
			foreach ($sql_or_array as $field) {
				$this->select[] = $field;
			}
		} else {
			$this->select[] = $sql_or_array;
		}
		if (!empty($params)) {
			foreach ($params as $value) {
				$this->select_params[] = $value;
			}
		}
		return $this;
	}

	function table($sql, $params = null) {
		$this->from[] = $sql;
		if (!empty($params)) {
			foreach ($params as $value) {
				$this->from_params[] = $value;
			}
		}
		return $this;
	}

	function where($sql_or_array, $params = null) {
		if (is_array($sql_or_array)) {
			foreach ($sql_or_array as $key => $value) {
				$this->where[] = "{$key}=?";
				$this->where_params[] = $value;
			}
		} else {
			$this->where[] = $sql_or_array;
			if (!empty($params)) {
				foreach ($params as $value) {
					$this->where_params[] = $value;
				}
			}
		}
		return $this;
	}

	function offset($offset) {
		$this->offset = $offset;
		return $this;
	}

	function limit($limit) {
		$this->limit = $limit;
		return $this;
	}

	function order_by($sql_or_array, $params = null) {
		if (is_array($sql_or_array)) {
			foreach ($sql_or_array as $key => $value) {
				if (is_numeric($key))
					$this->order_by[] = $value;
				else
					$this->order_by[] = $key . ' ' . ($value ? 'DESC' : 'ASC');
			}
		} else {
			$this->order_by[] = $sql_or_array;
		}
		if (!empty($params)) {
			foreach ($params as $value) {
				$this->order_by_params[] = $value;
			}
		}
		return $this;
	}

	function group_by($sql_or_array, $params = null) {
		if (is_array($sql_or_array)) {
			foreach ($sql_or_array as $value) {
				$this->group_by[] = $value;
			}
		} else {
			$this->group_by[] = $sql_or_array;
		}
		if (!empty($params)) {
			foreach ($params as $value) {
				$this->group_by_params[] = $value;
			}
		}
		return $this;
	}

	function raw($sql, $params = null) {
		$this->raw_sql = $sql;
		$this->raw_params = $params;
		return $this;
	}

	function sql() {
		return $this->sql;
	}

	function params() {
		return $this->params;
	}

	/**
	 * @return mixed
	 * @throws SqlException
	 */
	function first() {
		$this->build_select();
		return $this->try_map(
			$this->connection->fetch_one($this->sql(), $this->params())
		);
	}

	function all() {
		$this->build_select();
		return array_map(
			function($array){ return $this->try_map($array); },
			$this->connection->fetch_all($this->sql(), $this->params())
		);
	}

	function field() {
		$this->build_select();
		return $this->connection->fetch_field($this->sql(), $this->params());
	}

	function column() {
		$this->build_select();
		return $this->connection->fetch_column($this->sql(), $this->params());
	}

	function insert($data) {
		$this->values = $data;
		$this->build_insert();
		$this->connection->execute($this->sql(), $this->params());
		return $this->connection->get_insert_id();
	}

	/**
	 * @return int affected row count (for MySQL ON DUPLICATE KEY UPDATE: 1=inserted, 2=updated, 0=unchanged)
	 */
	function upsert($data, $no_update_keys) {
		$this->values = $data;
		$this->build_insert(true, $no_update_keys);
		return $this->connection->execute($this->sql(), $this->params());
	}

	function update($data) {
		$this->values = $data;
		$this->build_update();
		$this->connection->execute($this->sql(), $this->params());
	}

	function execute() {
		if ($this->raw_sql) {
			$this->connection->execute($this->raw_sql, $this->raw_params);
		} else {
			$this->connection->execute($this->sql(), $this->params());
		}
	}

	function delete() {
		$this->build_delete();
		$this->connection->execute($this->sql(), $this->params());
	}

	function debug($type = 'select') {
		switch (strtolower($type)) {
			case 'insert':
				$this->build_insert();
				break;
			case 'update':
				$this->build_update();
				break;
			case 'delete':
				$this->build_delete();
				break;
			case 'select':
				$this->build_select();
				break;
			default:
				throw new \Exception("Unknown type: $type");
		}
		$sql = $this->sql();
		$params = $this->params();
		foreach ($params as $p) {
			$p = is_scalar($p) ? $p : json_encode($p);
			$sql = preg_replace('/\?/', $p, $sql, 1);
		}
		return $sql;
	}

	private function build_select() {

		if ($this->raw_sql) {
			$this->sql = $this->raw_sql;
			$this->params = $this->raw_params;
			return;
		}

		$sql  = ['SELECT'];
		$params = [];

		if ($this->select) {
			$sql[] = implode(', ', $this->select);
			$params = array_merge($params, $this->select_params);
		} else {
			$sql[] = '*';
		}

		if ($this->from) {
			$sql[] = 'FROM ' . implode(' ', $this->from);
			$params = array_merge($params, $this->from_params);
		}

		if ($this->where) {
			$sql[] = 'WHERE ' . implode(' AND ', $this->where);
			$params = array_merge($params, $this->where_params);
		}

		if ($this->group_by) {
			$sql[] = 'GROUP BY ' . implode(', ', $this->group_by);
			$params = array_merge($params, $this->group_by_params);
		}

		if ($this->order_by) {
			$sql[] = 'ORDER BY ' . implode(', ', $this->order_by);
			$params = array_merge($params, $this->order_by_params);
		}

		if ($this->offset || $this->limit) {
			$sql[] = 'LIMIT ' . ($this->offset ?: '0') . ',' . ($this->limit ?: '0');
		}

		$this->sql = implode(' ', $sql);
		$this->params = $params;

	}

	private function build_insert($is_upsert = false, $no_update_keys = []) {

		$fields = [];
		$placeholders = [];

		foreach ($this->values as $key => $value) {
			$fields[] = "`{$key}`";
			$placeholders[] = '?';
		}

		$this->sql = 'INSERT INTO ' . implode(' ', $this->from)
			. ' (' . implode(',', $fields) . ') VALUES'
			. ' (' . implode(',', $placeholders) . ')';

		if ($is_upsert) {
			$this->sql .= ' ON DUPLICATE KEY UPDATE ';
			$updates = [];
			foreach ($this->values as $key => $value) {
				if (in_array($key, $no_update_keys)) {
					continue;
				}
				$updates[] = "`{$key}`=VALUES(`{$key}`)";
			}
			if (!$updates) {
				throw new \Exception("Must have at least one field to update for upsert");
			}
			$this->sql .= ' ' . implode(',', $updates);
		}

		$this->params = array_values($this->values);

	}

	private function build_update() {

		$params = array_values($this->values);
		$sql = [];

		$set = [];
		foreach ($this->values as $key => $value) {
			$set[] = "`{$key}`=?";
		}

		// todo: need to differ on table and from(join) here...
		$sql[] = 'UPDATE ' . implode(' ', $this->from);
		$sql[] = 'SET ' . implode(', ', $set);

		// todo: join..

		if ($this->where) {
			$sql[] = 'WHERE ' . implode(' AND ', $this->where);
			$params = array_merge($params, $this->where_params);
		}

		if ($this->limit) {
			$sql[] = 'LIMIT ' . $this->limit;
		}

		$this->sql = implode(' ', $sql);
		$this->params = $params;
	}

	private function build_delete() {

		$sql = [];
		$params = [];

		// todo: table vs join
		$sql[] = 'DELETE FROM ' . implode(' ', $this->from);

		// todo: join?

		if ($this->where) {
			$sql[] = 'WHERE ' . implode(' AND ', $this->where);
			$params = array_merge($params, $this->where_params);
		}

		if ($this->limit) {
			$sql[] = 'LIMIT ' . $this->limit;
		}

		$this->sql = implode(' ', $sql);
		$this->params = $params;
	}

	private function try_map($array) {
		return $array && $this->mapper ? $this->mapper->to_object($array) : $array;
	}

}
