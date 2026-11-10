<?php

use eonbit\orm\Query;
use test\utils\DummyConnection;

class TableDeleteQueryTest extends \PHPUnit\Framework\TestCase {

	private $query;

	protected function setUp(): void {
		$conn = new DummyConnection();
		$this->query = new Query($conn);
	}

	function test_main() {

		$query = $this->query;

		$query->table('user');
		$query->where(['_id' => 123]);
		$query->delete();

		$actual = $query->sql();
		$expect = 'DELETE FROM user WHERE _id=?';
		$this->assertEquals($expect, $actual);

		$this->assertEquals(1, count($query->params()));
		$this->assertEquals(123, $query->params()[0]);

	}


}
