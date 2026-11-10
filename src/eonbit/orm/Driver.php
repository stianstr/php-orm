<?php

namespace eonbit\orm;

use PDO;
use PDOException;

class Driver {

	private $pdo;

	private $slow_query_threshold = 0; // millisec threshold
	private $slow_query_log_file = '';

	/**
	 * @param array $attributes extra PDO attributes (PDO::ATTR_* => value) applied after the defaults
	 */
	function __construct($dsn, $username, $password, $attributes = []) {
		$this->pdo = new PDO($dsn, $username, $password);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// PHP 8.1+ pdo_mysql returns native int/float for numeric columns. This
		// library and its consumers were written against PHP 7 behaviour where
		// every fetched value is a string (bcmath, strict comparisons, JSON
		// shape), so keep that behaviour by default. Override via $attributes.
		$this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);
		foreach ($attributes as $attribute => $value) {
			$this->pdo->setAttribute($attribute, $value);
		}
	}

	public function set_slow_query_threshold($millisec) {
		$this->slow_query_threshold = $millisec;
	}

	public function set_slow_query_log_file($file) {
		$this->slow_query_log_file = $file;
	}

	function get_pdo() {
		return $this->pdo;
	}

	/**
	 * @param $sql
	 * @param null $params
	 * @return mixed|void
	 * @throws SqlException
	 */
	function fetch_one($sql, $params = null) {
		return $this->wrap($sql, $params, function() use ($sql, $params) {
			$sth = $this->pdo->prepare($sql);
			$this->execute_query($sth, $params);
			$rows = $sth->fetchAll(PDO::FETCH_ASSOC);
			if (empty($rows)) {
				return;
			}
			return $rows[0];
		});
	}

	/**
	 * @param $sql
	 * @param null $params
	 * @return array|false
	 * @throws SqlException
	 */
	function fetch_all($sql, $params = null) {
		return $this->wrap($sql, $params, function() use ($sql, $params) {
			$sth = $this->pdo->prepare($sql);
			$this->execute_query($sth, $params);
			return $sth->fetchAll(PDO::FETCH_ASSOC);
		});
	}

	/**
	 * @param $sql
	 * @param null $params
	 * @return mixed
	 * @throws SqlException
	 */
	function fetch_field($sql, $params = null) {
		return $this->wrap($sql, $params, function() use ($sql, $params) {
			$sth = $this->pdo->prepare($sql);
			$this->execute_query($sth, $params);
			return $sth->fetchColumn(0);
		});
	}

	/**
	 * @param $sql
	 * @param null $params
	 * @return array|false
	 * @throws SqlException
	 */
	function fetch_column($sql, $params = null) {
		return $this->wrap($sql, $params, function() use ($sql, $params) {
			$sth = $this->pdo->prepare($sql);
			$this->execute_query($sth, $params);
			return $sth->fetchAll(PDO::FETCH_COLUMN, 0);
		});
	}

	/**
	 * @param $sql
	 * @param null $params
	 * @return int affected row count
	 * @throws SqlException
	 */
	function execute($sql, $params = null) {
		return $this->wrap($sql, $params, function() use ($sql, $params) {
			$sth = $this->pdo->prepare($sql);
			$this->execute_query($sth, $params);
			return $sth->rowCount();
		});
	}

	function get_insert_id() {
		return $this->pdo->lastInsertId();
	}

	private function execute_query($sth, $params) {
		// PDO treats all $params as PARAM_STR so must change
		// false to "0" instead of ""
		if ($params) {
			foreach ($params as $n => $param) {
				if ($param === false)
					$params[$n] = '0';
				else if (is_array($param) || is_object($param))
					$params[$n] = json_encode($param);
				else if (is_string($param) && preg_match('/^(\d{4}-\d{2}-\d{2})(T| )(\d{2}:\d{2}:\d{2})(\.\d+)?Z?$/', $param, $matches))
					$params[$n] = "{$matches[1]} {$matches[3]}";
			}
		}
		try {
			$sth->execute($params ?: []);
		} catch (PDOException $e) {
			throw new SqlException(
				sprintf("%s\n\nSQL: %s", $e->getMessage(), $sth->queryString),
				$e->getCode(),
				$e->errorInfo[0],
				$e->errorInfo[1],
				$e->errorInfo[2]
			);
		}
	}

	private function wrap($sql, $params, $callback) {
		if (!$this->slow_query_threshold || !$this->slow_query_log_file) {
			return $callback();
		}
		$time = microtime(true);
		$result = $callback();
		$duration = intval((microtime(true) - $time) * 1000);
		if ($duration > $this->slow_query_threshold) {
			$this->logSlowQuery($sql, $params, $duration);
		}
		return $result;
	}

	private function logSlowQuery($sql, $params, $duration) {
		$fp = @fopen($this->slow_query_log_file, 'a');
		if (!$fp) {
			return;
		}
		ob_start();
		print_r($params);
		$paramsDebug = ob_get_clean();
		@fwrite($fp, sprintf(
			"\n-- %-20s %7s ms:\n%s\n%s\n",
			gmdate('Y-m-d H:i:s'),
			$duration,
			$sql,
			$paramsDebug
		));
		@fclose($fp);
	}
}
