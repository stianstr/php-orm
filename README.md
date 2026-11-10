# eonbit orm

Small PDO-based query builder and ORM.

## Install

Add the repository and require the package:

    {
        "repositories": [
            {"type": "vcs", "url": "https://github.com/stianstr/php-orm.git"}
        ],
        "require": {
            "eonbit/orm": "dev-master"
        }
    }

## Connecting

    $driver = new eonbit\orm\Driver('mysql:host=localhost;dbname=test', 'root', 'pass');
    $db = new eonbit\orm\DB($driver);

## SQL

### Select

#### Select one

    $db
      ->table('user')
      ->select('id, email')
      ->where('email=?', ['mr.foo@example.com'])
      ->first();

Corresponds to:

    SELECT id,email
      FROM user
     WHERE email='mr.foo@example.com'

#### Select many

    $db
      ->table('user')
      ->select('*')
      ->where('active=?', [true])
      ->order_by('name')
      ->offset(5)
      ->limit(10)
      ->all();

Corresponds to:

      SELECT *
        FROM user
       WHERE active=true
    ORDER BY name
       LIMIT 5,10

#### Select fields

    // String
    $db->select('id, name')

    // Array
    $db->select(['id', 'name'])

    // Multiple calls
    $db->select('id');
    $db->select('name');

All corresponds to:

    SELECT id, name

#### Select fields with parameters

    $db->select("IF(importance > ?, 'vip', 'normal')", [5])

Correspond to:

    SELECT IF(importance > 5, 'vip', 'normal')

#### Select field (one column from one row)

	$db->select('...')->field()

#### Select column (one column from many rows)

	$db->select('...')->column()

#### From

    ->table('user')

#### Join

     ->table('user')
     ->table('JOIN profile ON profile.user_id=user.id')

     // With parameters
     ->table('user')
     ->table('JOIN profile ON profile.user_id=user.id AND active=?', [true])

#### Where

    // String
    ->where("active=true AND name='Joe'")

    // String with parameters
    ->where('active=? AND name=?', [true, 'Joe'])

    // Array
    ->where(['active' => true, 'name' => 'Joe'])

    // Multiple calls
    ->where('active=true')
    ->where(['name' => 'Joe'])

All correspond to:

    WHERE active=true AND name='Joe'

### Insert

    $id = $db
      ->table('user')
      ->insert(['email' => 'mr.foo@example.com', 'name' => 'Mr. Foo'])

Correspond to:

    INSERT INTO user (`email`, `name`) VALUES ('mr.foo@example.com', 'Mr. Foo')

### Update

	$db->table('user')->update(..)
	$db->table('user')->where(..)->update(..)

### Delete

	$db->table('user')->where(..)->delete()

### Raw SQL

	$db->sql('SELECT id FROM user')->all();
	$db->sql('DELETE FROM user WHERE _id=1')->execute();

## ORM

### Select

#### Find one by primary key

	$db->orm(User::class)->find(1)

Will select from user table where primary key is 1, and return result as a User object.

#### Find all

	$db->orm(User::class)->all()

Select all rows and return as array of User objects.

#### Find by WHERE

	$db->orm(User::class)->where(...)->first()
	$db->orm(User::class)->where(...)->all()

Exactly like where() documented under SQL section, but return array of User objects.

### Save

	$db->orm(User::class)->save($user)

Will update if $user has primary key set, and insert if not.
Will update $user->$primarykey with insert id.

### Delete

	$db->orm(User::class)->delete($user)

### Raw query

	$db->orm(User::class)->query()

Get a Query object that can be used exactly like documented under SQL,
except it returns objects of the given type instead of arrays.

### Mappers and repositories

This returns a Repository object:

	$db->orm(User::class)

The Repository has the functions like documented above:

	find()
	all()
	where()
	save()
	delete()
	query()

It also has a Mapper, which is responsible for converting arrays to objects an vice versa.
The Mapper also has info about the table name for a class, the primary key, etc.

By default a DefaultMapper is used, which uses \_id for primary key and tosnakecase(Class) for table name.

Mappers can be configured per class, per namespace, or as a new default:

	$db->register_mapper_class(User::class, UserMapper::class);
	$db->register_mapper_namespace('\\app\\entities', '\\app\\mappers');
	$db->register_mapper_default(UserMapper::class);

## Transactions

	$db->begin()
	$db->commit()
	$db->rollback()

## Debug queries

	// SELECT * FROM user WHERE email=mr.foo@example.com
	print $db->table('user')->where('email=?', ['mr.foo@example.com'])->debug()
	print $db->orm(User::class)->where('email=?', ['mr.foo@example.com'])->debug()

## Tests

Tests run against a MySQL database. Load the fixture and run phpunit:

    make test

Connection settings can be overridden with `ORM_TEST_DSN`, `ORM_TEST_USER`
and `ORM_TEST_PASSWORD` (defaults: `mysql:host=localhost;dbname=eonbit_orm_test`, `root`, empty).
