<?php

use eonbit\orm\Driver;
use eonbit\orm\DB;
use eonbit\orm\DefaultMapper;
use test\entities\User;
use test\mappers\User as UserMapper;

class CustomerMapperTest extends \PHPUnit\Framework\TestCase {

	private $db;

	protected function setUp(): void {
		$driver = new eonbit\orm\Driver(getenv('ORM_TEST_DSN') ?: 'mysql:host=localhost;dbname=eonbit_orm_test', getenv('ORM_TEST_USER') ?: 'root', getenv('ORM_TEST_PASSWORD') ?: '');
		$this->db = new eonbit\orm\DB($driver);
	}

	function test_When_no_mapper_is_configured_Mapper_is_DefaultMapper() {
		$repository = $this->db->orm(User::class);
		$mapper = $this->get_mapper_class_name($repository);
		$this->assertEquals(DefaultMapper::class, $mapper);
	}

	function test_When_explicit_UserMapper_is_configured_Mapper_is_UserMapper() {
		$this->db->register_mapper_class(User::class, UserMapper::class);
		$repository = $this->db->orm(User::class);
		$mapper = $this->get_mapper_class_name($repository);
		$this->assertEquals(UserMapper::class, $mapper);
	}

	function test_When_mapper_namespace_is_configured_Mapper_is_UserMapper() {
		$this->db->register_mapper_namespace('\\test\\entities', '\\test\\mappers');
		$repository = $this->db->orm(User::class);
		$mapper = $this->get_mapper_class_name($repository);
		$this->assertEquals(UserMapper::class, $mapper);
	}

	function test_When_default_UserMapper_is_configured_Mapper_is_UserMapper() {
		$this->db->register_mapper_default(UserMapper::class);
		$repository = $this->db->orm(User::class);
		$mapper = $this->get_mapper_class_name($repository);
		$this->assertEquals(UserMapper::class, $mapper);
	}

	function test_Mapper_setup_does_not_leak() {
		$driver = new eonbit\orm\Driver(getenv('ORM_TEST_DSN') ?: 'mysql:host=localhost;dbname=eonbit_orm_test', getenv('ORM_TEST_USER') ?: 'root', getenv('ORM_TEST_PASSWORD') ?: '');
		$db1 = new eonbit\orm\DB($driver);
		$db2 = new eonbit\orm\DB($driver);
		$db1->register_mapper_class(User::class, UserMapper::class);
		$repository1 = $db1->orm(User::class);
		$repository2 = $db2->orm(User::class);
		$mapper1 = $this->get_mapper_class_name($repository1);
		$mapper2 = $this->get_mapper_class_name($repository2);
		$this->assertEquals(UserMapper::class, $mapper1);
		$this->assertEquals(DefaultMapper::class, $mapper2);
	}

	private function get_mapper_class_name($repository) {
		$class = new ReflectionClass($repository);
		$property = $class->getProperty('mapper');
		$property->setAccessible(true);
		$value = $property->getValue($repository);
		$mapper_class = get_class($value);
		return $mapper_class;
	}

}
