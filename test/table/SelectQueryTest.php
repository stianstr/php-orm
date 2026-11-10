<?php

use eonbit\orm\Query;
use test\utils\DummyConnection;

class TableSelectQueryTest extends \PHPUnit\Framework\TestCase {

	private $query;

	protected function setUp(): void {
		$conn = new DummyConnection();
		$this->query = new Query($conn);
	}

	// ============================================================
	// Select
	// ============================================================

	function test_Single_select_as_string() {
		$query = $this->query;
		$query->select('id');
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id', $actual);
	}

	function test_Single_select_as_array() {
		$query = $this->query;
		$query->select(['id']);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id', $actual);
	}

	function test_Multiple_select_as_string() {
		$query = $this->query;
		$query->select('id, name');
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id, name', $actual);
	}

	function test_Multiple_select_as_array() {
		$query = $this->query;
		$query->select(['id', 'name']);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id, name', $actual);
	}

	function test_Multiple_select_as_multiple_calls() {
		$query = $this->query;
		$query->select('id');
		$query->select('name');
		$query->select(['array1']);
		$query->select(['array2']);
		$query->select(['array3', 'array4']);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id, name, array1, array2, array3, array4', $actual);
	}

	function test_Field_as_string_with_params() {
		$query = $this->query;
		$query->select('IF(quantity > ?, ?, ?)', [4, 'large', 'small']);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT IF(quantity > ?, ?, ?)', $actual);
		$this->assertEquals(3, count($query->params()));
		$this->assertEquals(4, $query->params()[0]);
		$this->assertEquals('large', $query->params()[1]);
		$this->assertEquals('small', $query->params()[2]);
	}

	function test_Field_as_array_with_params() {
		$query = $this->query;
		$query->select(['IF(quantity > ?, ?, ?)'], [4, 'large', 'small']);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT IF(quantity > ?, ?, ?)', $actual);
		$this->assertEquals(3, count($query->params()));
		$this->assertEquals(4, $query->params()[0]);
		$this->assertEquals('large', $query->params()[1]);
		$this->assertEquals('small', $query->params()[2]);
	}

	function test_Multiple_calls_to_select_with_params() {
		$query = $this->query;
		$query->select('IFNULL(quantity,?) AS quantity', [4]);
		$query->select('IFNULL(price,?) AS price', [100]);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT IFNULL(quantity,?) AS quantity, IFNULL(price,?) AS price', $actual);
		$this->assertEquals(2, count($query->params()));
		$this->assertEquals(4, $query->params()[0]);
		$this->assertEquals(100, $query->params()[1]);
	}

	// ============================================================
	// Table
	// ============================================================

	function test_Single_table_name() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user', $actual);
	}

	function test_Single_table_sql() {
		$query = $this->query;
		$query->select('id');
		$query->table('user JOIN profile ON user.id=profile.user_id');
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user JOIN profile ON user.id=profile.user_id', $actual);
	}

	function test_Multiple_table_calls() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->table('JOIN profile ON user.id=profile.user_id');
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user JOIN profile ON user.id=profile.user_id', $actual);
	}

	function test_Single_table_with_params() {
		$query = $this->query;
		$query->select('id');
		$query->table('user JOIN profile ON (user.global_profile=? OR user.profile=?)', [true, 0]);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user JOIN profile ON (user.global_profile=? OR user.profile=?)', $actual);
		$this->assertEquals(2, count($query->params()));
		$this->assertEquals(true, $query->params()[0]);
		$this->assertEquals(0, $query->params()[1]);
	}

	function test_Multiple_table_with_params() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->table('JOIN profile ON user.global_profile=?', [true]);
		$query->table('JOIN address ON address.type=?', ['primary']);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user JOIN profile ON user.global_profile=? JOIN address ON address.type=?', $actual);
		$this->assertEquals(2, count($query->params()));
		$this->assertEquals(true, $query->params()[0]);
		$this->assertEquals('primary', $query->params()[1]);
	}

	// ============================================================
	// Where
	// ============================================================

	function test_Single_where_as_string() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->where('id=1');
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user WHERE id=1', $actual);
	}

	function test_Single_where_as_string_with_params() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->where('id=?', [3]);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user WHERE id=?', $actual);
		$this->assertEquals(1, count($query->params()));
		$this->assertEquals(3, $query->params()[0]);
	}

	function test_Single_where_as_array() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->where(['id' => 3]);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user WHERE id=?', $actual);
		$this->assertEquals(1, count($query->params()));
		$this->assertEquals(3, $query->params()[0]);
	}

	function test_Multiple_where_as_array() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->where(['active' => true, 'name' => 'Stian']);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user WHERE active=? AND name=?', $actual);
		$this->assertEquals(2, count($query->params()));
		$this->assertEquals(true, $query->params()[0]);
		$this->assertEquals('Stian', $query->params()[1]);
	}

	function test_Multiple_where_as_multiple_calls() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->where('id=1');
		$query->where(['active' => true, 'deleted' => false]);
		$query->where('name like ?', ['Stian%']);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user WHERE id=1 AND active=? AND deleted=? AND name like ?', $actual);
		$this->assertEquals(3, count($query->params()));
		$this->assertEquals(true, $query->params()[0]);
		$this->assertEquals(false, $query->params()[1]);
		$this->assertEquals('Stian%', $query->params()[2]);
	}

	// ============================================================
	// Offset & limit
	// ============================================================

	function test_Limit() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->limit(5);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user LIMIT 0,5', $actual);
	}

	function test_Offset() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->offset(5);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user LIMIT 5,0', $actual);
	}

	function test_Limit_and_offset() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->offset(5);
		$query->limit(10);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user LIMIT 5,10', $actual);
	}

	// ============================================================
	// Order by
	// ============================================================

	function test_Single_order_as_string() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->order_by('id');
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user ORDER BY id', $actual);
	}

	function test_Single_order_as_array() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->order_by(['id']);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user ORDER BY id', $actual);
	}

	function test_Single_order_as_array_asc() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->order_by(['id' => false]);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user ORDER BY id ASC', $actual);
	}

	function test_Single_order_as_array_desc() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->order_by(['id' => true]);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user ORDER BY id DESC', $actual);
	}

	function test_Multiple_orders_as_string() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->order_by('id ASC, name DESC');
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user ORDER BY id ASC, name DESC', $actual);
	}

	function test_Multiple_order_as_array_asc_and_desc() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->order_by(['id', 'name' => true, 'city' => false]);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user ORDER BY id, name DESC, city ASC', $actual);
	}

	function test_Multiple_order_as_multiple_calls() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->order_by('id');
		$query->order_by('name desc');
		$query->order_by(['city asc']);
		$query->order_by(['country' => true, 'active' => false]);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user ORDER BY id, name desc, city asc, country DESC, active ASC', $actual);
	}

	function test_Order_as_string_with_params() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->order_by('IF(importance>?, 1, 0) DESC', [5]);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user ORDER BY IF(importance>?, 1, 0) DESC', $actual);
		$this->assertEquals(1, count($query->params()));
		$this->assertEquals(5, $query->params()[0]);
	}

	function test_Order_as_array_with_params() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->order_by(['IF(importance>?, 1, 0)' => true], [5]);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user ORDER BY IF(importance>?, 1, 0) DESC', $actual);
		$this->assertEquals(1, count($query->params()));
		$this->assertEquals(5, $query->params()[0]);
	}

	function test_Multiple_calls_to_order_with_params() {
		$query = $this->query;
		$query->select('id');
		$query->table('user');
		$query->order_by(['IF(importance>?, 1, 0)' => true], [5]);
		$query->order_by('name=?', ['Stian']);
		$query->first();
		$actual = $query->sql();
		$this->assertEquals('SELECT id FROM user ORDER BY IF(importance>?, 1, 0) DESC, name=?', $actual);
		$this->assertEquals(2, count($query->params()));
		$this->assertEquals(5, $query->params()[0]);
		$this->assertEquals('Stian', $query->params()[1]);
	}

	// ============================================================
	// Params combined
	// ============================================================

	function test_Combined_params_has_correct_order() {
		$query = $this->query;
		$query->select('IF(importance > ?, 1, 0) AS is_important', [5]);
		$query->table('user');
		$query->table('JOIN profile ON profile.user_id=user.id AND profile.active=?', [true]);
		$query->where(['active' => true]);
		$query->where('name like ?', ['Stian%']);
		$query->order_by(['IF(importance>?, 1, 0)' => true], [5]);
		$query->first();
		$actual = $query->sql();
		$expect = 'SELECT IF(importance > ?, 1, 0) AS is_important FROM user JOIN profile ON profile.user_id=user.id AND profile.active=? WHERE active=? AND name like ? ORDER BY IF(importance>?, 1, 0) DESC';
		$this->assertEquals($expect, $actual);
		$this->assertEquals(5, count($query->params()));
		$this->assertEquals(5, $query->params()[0]);
		$this->assertEquals(true, $query->params()[1]);
		$this->assertEquals(true, $query->params()[2]);
		$this->assertEquals('Stian%', $query->params()[3]);
		$this->assertEquals(5, $query->params()[4]);
	}

	// ============================================================
	// Chaining
	// ============================================================

	function test_Chaining_works() {
		$query = $this->query;
		$query
			->select('id,name')
			->table('user')
			->where('id=2')
			->order_by('id asc')
			->offset(5)
			->limit(10)
			->first();
		$actual = $query->sql();
		$expect = 'SELECT id,name FROM user WHERE id=2 ORDER BY id asc LIMIT 5,10';
		$this->assertEquals($expect, $actual);
	}

	// ============================================================
	// Raw SQL
	// ============================================================

	function test_Raw_sql() {
		$query = $this->query;
		$query
			->raw('SELECT foo FROM bar WHERE moo=?', ['param1'])
			->first();
		$this->assertEquals(
			'SELECT foo FROM bar WHERE moo=?',
			$query->sql()
		);
		$this->assertEquals(1, count($query->params()));
		$this->assertEquals('param1', $query->params()[0]);
	}

}
