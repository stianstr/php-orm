<?php

use eonbit\orm\Query;
use test\utils\DummyConnection;

class TableInsertQueryTest extends \PHPUnit\Framework\TestCase {

	private $query;

	protected function setUp(): void {
		$conn = new DummyConnection();
		$this->query = new Query($conn);
	}

	function test_Simple_insert_produces_correct_sql() {

		$query = $this->query;

		$query->table('user');
		$query->insert([
			'email' => 'mr.foo@example.com',
			'name' => 'Mr. Foo'
		]);

		$actual = $query->sql();
		$expect = 'INSERT INTO user (`email`,`name`) VALUES (?,?)';
		$this->assertEquals($expect, $actual);

		$this->assertEquals(2, count($query->params()));
		$this->assertEquals('mr.foo@example.com', $query->params()[0]);
		$this->assertEquals('Mr. Foo', $query->params()[1]);

	}


}
