<?php

use eonbit\orm\Query;
use test\utils\DummyConnection;

class TableUpdateQueryTest extends \PHPUnit\Framework\TestCase {

	private $query;

	protected function setUp(): void {
		$conn = new DummyConnection();
		$this->query = new Query($conn);
	}

	function test_Simple_insert_produces_correct_sql() {

		$query = $this->query;

		$query->table('user');
		$query->where(['_id' => 123]);
		$query->update([
			'email' => 'mr.foo@example.com',
			'name' => 'Mr. Foo'
		]);

		$actual = $query->sql();
		$expect = 'UPDATE user SET `email`=?, `name`=? WHERE _id=?';
		$this->assertEquals($expect, $actual);

		$this->assertEquals(3, count($query->params()));
		$this->assertEquals('mr.foo@example.com', $query->params()[0]);
		$this->assertEquals('Mr. Foo', $query->params()[1]);
		$this->assertEquals(123, $query->params()[2]);

	}


}
