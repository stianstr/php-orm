<?php

use eonbit\orm\Driver;
use eonbit\orm\DB;

class TableDeleteTest extends \PHPUnit\Framework\TestCase {

	private $db;
	private $pdo;

	protected function setUp(): void {
		$driver = new eonbit\orm\Driver(getenv('ORM_TEST_DSN') ?: 'mysql:host=localhost;dbname=eonbit_orm_test', getenv('ORM_TEST_USER') ?: 'root', getenv('ORM_TEST_PASSWORD') ?: '');
		$this->db = new eonbit\orm\DB($driver);
		$this->pdo = $driver->get_pdo();
		$this->pdo->query('DELETE FROM user WHERE _id > 2');
		$this->pdo->query('INSERT INTO user (_id, email) values (10, "moo@moo.com")');
	}

	function test_main() {

		$sth = $this->pdo->query('SELECT * FROM user WHERE _id = 10');
		$sth->execute();
		$users = $sth->fetchAll(PDO::FETCH_ASSOC);
		$this->assertEquals(10, $users[0]['_id']);

		$this->db
			->table('user')
			->where(['_id' => 10])
			->delete();

		$sth = $this->pdo->query('SELECT * FROM user WHERE _id = 10');
		$sth->execute();
		$users = $sth->fetchAll(PDO::FETCH_ASSOC);
		$this->assertEmpty($users);

	}

}
