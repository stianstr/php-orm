<?php

use eonbit\orm\Driver;
use eonbit\orm\DB;
use test\entities\User;

class ObjectSaveTest extends \PHPUnit\Framework\TestCase {

	private $db;
	private $pdo;

	protected function setUp(): void {
		$driver = new eonbit\orm\Driver(getenv('ORM_TEST_DSN') ?: 'mysql:host=localhost;dbname=eonbit_orm_test', getenv('ORM_TEST_USER') ?: 'root', getenv('ORM_TEST_PASSWORD') ?: '');
		$this->db = new eonbit\orm\DB($driver);
		$this->pdo = $driver->get_pdo();
		$this->pdo->query('DELETE FROM user WHERE _id > 2');
	}

	function test_Save_new_creates_row() {

		$user = new User();
		$user->email = 'jan@banan.no';

		$this->db
			->orm(User::class)
			->save($user);

		$this->assertNotEmpty($user->_id);

		$sth = $this->pdo->query('SELECT * FROM user WHERE _id = ' . $user->_id);
		$sth->execute();
		$db_users = $sth->fetchAll(PDO::FETCH_ASSOC);

		$this->assertNotEmpty($db_users);
		$this->assertEquals(1, count($db_users));

		$db_user = $db_users[0];
		$this->assertNotEmpty($db_user);
		$this->assertEquals('jan@banan.no', $db_user['email']);
		$this->assertEquals($user->_id, $db_user['_id']);

	}

	function test_Save_existing_updates_row() {

		$user = new User();
		$user->email = 'jan@banan.no';
		$this->db->orm(User::class)->save($user);
		$this->assertNotEmpty($user->_id);
		$initial_id = $user->_id;

		$user->email = 'per@potet.se';
		$this->db->orm(User::class)->save($user);
		$this->assertNotEmpty($user->_id);
		$this->assertEquals($initial_id, $user->_id);

		$sth = $this->pdo->query('SELECT * FROM user WHERE _id = ' . $user->_id);
		$sth->execute();
		$db_users = $sth->fetchAll(PDO::FETCH_ASSOC);

		$this->assertNotEmpty($db_users);
		$this->assertEquals(1, count($db_users));

		$db_user = $db_users[0];
		$this->assertNotEmpty($db_user);
		$this->assertEquals('per@potet.se', $db_user['email']);
		$this->assertEquals($user->_id, $db_user['_id']);

	}

}
