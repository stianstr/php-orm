<?php

use eonbit\orm\Driver;
use eonbit\orm\DB;
use test\entities\User;

/**
 * Do tests similar to TableSelectTest,
 * but get the Query via orm(User::class)
 * and see that it returns User object(s).
 */
class ObjectSelectViaQueryTest extends \PHPUnit\Framework\TestCase {

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
			->orm(User::class)
			->query()
			->where('email=?', ['bob@example.com'])
			->first();

		$this->assertNotEmpty($user);
		$this->assertTrue($user instanceof User);
		$this->assertEquals(2, $user->_id);
		$this->assertEquals('bob@example.com', $user->email);

	}

	function test_Select_all() {

		$users = $this->db
			->orm(User::class)
			->query()
			->all();

		$this->assertNotEmpty($users);
		$this->assertEquals(2, count($users));
		$this->assertEquals(1, $users[0]->_id);
		$this->assertEquals('alice@example.com', $users[0]->email);
		$this->assertEquals(2, $users[1]->_id);
		$this->assertEquals('bob@example.com', $users[1]->email);

	}

}
