<?php

use eonbit\orm\Driver;
use eonbit\orm\DB;

class TableInsertTest extends \PHPUnit\Framework\TestCase {

	private $db;
	private $pdo;

	protected function setUp(): void {
		$driver = new eonbit\orm\Driver(getenv('ORM_TEST_DSN') ?: 'mysql:host=localhost;dbname=eonbit_orm_test', getenv('ORM_TEST_USER') ?: 'root', getenv('ORM_TEST_PASSWORD') ?: '');
		$this->db = new eonbit\orm\DB($driver);
		$this->pdo = $driver->get_pdo();
		$this->pdo->query('DELETE FROM user WHERE _id > 2');
	}

	function test_Insert_creates_row() {

		$id = $this->db
			->table('user')
			->insert(['email' => 'someone@else.com']);

		$this->assertNotEmpty($id);

		$sth = $this->pdo->query('SELECT * FROM user WHERE _id = ' . $id);
		$sth->execute();
		$users = $sth->fetchAll(PDO::FETCH_ASSOC);

		$this->assertNotEmpty($users);
		$this->assertEquals(1, count($users));

		$user = $users[0];
		$this->assertNotEmpty($user);
		$this->assertEquals('someone@else.com', $user['email']);
		$this->assertEquals($id, $user['_id']);

	}

}
