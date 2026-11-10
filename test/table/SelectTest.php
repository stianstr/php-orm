<?php

use eonbit\orm\Driver;
use eonbit\orm\DB;
use eonbit\orm\SqlException;

class TableSelectTest extends \PHPUnit\Framework\TestCase {

	private $db;
	private $pdo;

	protected function setUp(): void {
		$driver = new eonbit\orm\Driver(getenv('ORM_TEST_DSN') ?: 'mysql:host=localhost;dbname=eonbit_orm_test', getenv('ORM_TEST_USER') ?: 'root', getenv('ORM_TEST_PASSWORD') ?: '');
		$this->db = new eonbit\orm\DB($driver);
		$this->pdo = $driver->get_pdo();
		$this->pdo->query('DELETE FROM user WHERE _id > 2');
	}

	function test_Select_first() {

		$user = $this->db
			->table('user')
			->select('_id, email')
			->where('email=?', ['bob@example.com'])
			->first();

		$this->assertNotEmpty($user);
		$this->assertEquals(2, count($user));
		$this->assertEquals(2, $user['_id']);
		$this->assertEquals('bob@example.com', $user['email']);

	}

	function test_Select_all() {

		$users = $this->db
			->table('user')
			->select(['_id','email'])
			->all();

		$this->assertNotEmpty($users);
		$this->assertEquals(2, count($users));
		$this->assertEquals(1, $users[0]['_id']);
		$this->assertEquals('alice@example.com', $users[0]['email']);
		$this->assertEquals(2, $users[1]['_id']);
		$this->assertEquals('bob@example.com', $users[1]['email']);

	}

	function test_Select_field() {

		$email = $this->db
			->table('user')
			->select('email')
			->where(['_id' => 2])
			->field();

		$this->assertEquals('bob@example.com', $email);

	}

	function test_Select_column() {

		$emails = $this->db
			->table('user')
			->select('email')
			->order_by('_id')
			->column();

		$this->assertEquals('alice@example.com', $emails[0]);
		$this->assertEquals('bob@example.com', $emails[1]);

	}

	function test_Select_raw() {
		$user = $this->db
			->sql('SELECT _id, email FROM user WHERE _id=2')
			->first();
		$this->assertNotEmpty($user);
		$this->assertEquals(2, count($user));
		$this->assertEquals(2, $user['_id']);
		$this->assertEquals('bob@example.com', $user['email']);
	}

	function test_Select_invalid_throws_SqlException() {
		$this->expectException(SqlException::class);
		$this->db->table('user')->select('nosuchfield')->first();
	}

}
