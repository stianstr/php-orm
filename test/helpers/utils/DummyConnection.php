<?php

namespace test\utils;

class DummyConnection {
	function fetch_one($sql, $params) {}
	function fetch_all($sql, $params) {}
	function execute($sql, $params) {}
	function get_insert_id() {}
}
