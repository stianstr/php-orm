<?php

use eonbit\orm\Driver;
use eonbit\orm\DB;
use test\entities\User;

class ObjectDeleteTest extends \PHPUnit\Framework\TestCase {

	private $db;
	private $pdo;

	protected function setUp(): void {
		$driver = new eonbit\orm\Driver(getenv('ORM_TEST_DSN') ?: 'mysql:host=localhost;dbname=eonbit_orm_test', getenv('ORM_TEST_USER') ?: 'root', getenv('ORM_TEST_PASSWORD') ?: '');
		$this->db = new eonbit\orm\DB($driver);
		$this->pdo = $driver->get_pdo();
		$this->pdo->query('DELETE FROM user WHERE _id > 2');
	}

	function test_Delete_removes_row() {

		$user = new User();
		$user->email = 'jan@banan.no';
		$this->db->orm(User::class)->save($user);
		$this->assertNotEmpty($user->_id);
		$this->assertTrue($this->check_exists($user->_id));

		$this->db->orm(User::class)->delete($user);
		$this->assertFalse($this->check_exists($user->_id));

	}

	private function check_exists($id) {
		$sth = $this->pdo->query('SELECT * FROM user WHERE _id = ' . $id);
		$sth->execute();
		$db_users = $sth->fetchAll(PDO::FETCH_ASSOC);
		return empty($db_users) ? false : true;
	}

}
