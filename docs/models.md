[Back to Index](index.md)

# Working with Databases

FEAST is designed to make working with databases easier for the developer. It contains tools for both introspection and
data modeling to decrease the time spent writing boilerplate.

[Configuring Connections](#configuring-connections)

[Working with Models](#working-with-mappers-and-models)

[Working with Migrations](#working-with-migrations)

## Configuring Connections

FEAST uses a simple configuration format for specifying Database connections. The [Config.md](config.md) contains
details on the configuration file itself, but below is a sample for database configuration.

```php
<?php
return [
 'database.default'   => [
        'host' => 'hostname',
        'name' => 'databaseName'
        'user' => 'username'
        'password' => 'password',
        'url' => 'mysql:host=%s;port=%s;dbname=%s' // OPTIONAL: A manually specified connection string. If blank, the framework
                                                   // will build from the other parameters
        'connectionType' => \Feast\Enums\DatabaseType::MYSQL,
        'queryClass' => \Feast\Database\MySQLQuery::class,
        // 'queryClass' => \Feast\Database\SQLiteQuery::class,
        // 'connectionType' => \Feast\Enums\DatabaseType::SQLITE,
        // 'connectionType' => \Feast\Enums\DatabaseType::POSTGRES,
        // 'queryClass' => \Feast\Database\PostgresQuery::class,     
        'options' => [ // NOTE: the below options are not required. The ones below are applied by default.
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    ]
];
```

[Back to Top](#working-with-databases)

## Working with Mappers and Models

FEAST makes use of a Mapper/Model architecture to separate Database access from Data representation.

### Creating Models and Mappers

One of the most time-consuming parts of development is often creating the classes to interact with your database. With
FEAST, this is no longer the case.

FEAST ships with a CLI tool for creating models/mappers via database introspection. To create a model/mapper pair,
simply run `feast:create:model`. You can read more about this command
[here](cli.md#feastcreatemodel).

### Using the Model/Mapper System

All Mappers in FEAST extend from `\Feast\BaseMapper`. This class contains many methods for working with data.

#### Fetching Data from the Database

FEAST has many methods to retrieve records from the database. The main methods to fetch data are listed below.

1. `BaseMapper::findByPrimaryKey` - This method takes a string or int parameter to look up and will return either the
   corresponding Model or null if not found.
2. `BaseMapper::findOneByField` - This method takes two arguments - a field name and a value. It will automatically
   convert this to a
   `where` clause and return either the first corresponding Model or null if not found.
3. `BaseMapper::findAllByField` - This method takes two arguments - a field name and a value. It behaves similarly
   to `findOneByField` except it returns a `\Feast\Collection\Set`
   containing all the matching Models.
4. `BaseMapper::findOneByFields` - This method takes an array of arguments in key => value format. It will automatically
   convert this to a `where` clause matching all values, and return either the first corresponding Model or null if not
   found.
5. `BaseMapper::findAllByFields` - This method takes an array of arguments in key => value format. It behaves similarly
   to `findOneByField` except it returns a `\Feast\Collection\Set`
   containing all the matching Models.

Examples:

```php
<?php
$mapper = new TestMapper();

$model = $mapper->findByPrimaryKey(1); // Single model

$model = $mapper->findOneByField('name','Feast'); // Single model

$model = $mapper->findOneByFields(['name' => 'Feast', 'status' => 'active']); // Single model

$models = $mapper->findAllByField('name','Feast'); // \Feast\Collection\Set of models

$models = $mapper->findAllByFields(['name' => 'Feast', 'status' => 'active']); // \Feast\Collection\Set of models
```

#### Saving Records

To save a model, simply call either the `save` method on the mapper, passing in the model as an argument or for
convenience, simply call the `save()` method on the model. FEAST will automatically perform an insert if it is a new
model, or an update (passing only the changed fields) if it is not a new model. When the save method is called, the
primary key is updated on the model for inserts.

Example:

```php
<?php
$model = new \Model\Test();
$model->name = 'Feast';

$model->save(); // Model saves directly, inserted.

$model->name = 'NotFeast';
$mapper = new \Mapper\TestMapper();
$mapper->save($model); // Model saved indirectly after previous save, updated.
```

#### Deleting Records

FEAST has many methods to delete records from the database. The main methods to delete data are listed below.

1. `BaseMapper::deleteByFields` - This method takes an array of arguments in key => value format. It will automatically
   convert this to a `where` clause matching all values, and will delete any matching records. It will return the
   deleted record count.
2. `BaseMapper::deleteByField` - This method takes two arguments - a field name and a value. It will automatically
   convert this to a
   `where` clause and will delete any matching records. It will return the deleted record count.
3. `BaseMapper::delete` - This method takes a Model as an argument and will delete the corresponding record based on the
   primary key. It will return either 0 or 1 (the count deleted from the database).

#### Events

FEAST has methods for different database events that you can override in the child class. These events can fire when a
model is saved or when a model is deleted. The methods are `onSave` and `onDelete`.

Note that `onDelete` will only fire if `delete` is called, rather than the other deletion methods, as delete is the only
method that has access to a model.

[Back to Top](#working-with-databases)

## Working with Migrations

FEAST has a robust Migration system to allow programmatic database changes to ensure consistency upon deployment.

### Creating Migrations

Migrations can be created quickly and easily with the `feast:migration:create` command. See
details [here](cli.md#feastmigrationcreate). Migrations can be used both for creating tables and altering them.
Migrations have both an `up` and `down` method. The up should be used for bringing your database up to date, and the
down should undo whatever is done in the up call.

#### Creating and Dropping Tables

FEAST provides a TableFactory to retrieve an instance of a table builder. Currently, this table builder is limited to
MySQL. Using this table builder, you can quickly specify your table details and run the create without writing a single
line of SQL. Example:

```php
    public function up() : void
    {
        TableFactory::getTable('videos')
            ->autoIncrement('id')
            ->varChar('title')
            ->text('description')
            ->varChar('heading')
            ->text('block_text')
            ->varChar('video')
            ->create();
        parent::up();
    }
    
    public function down() : void
    {
        /** @todo Create down query */
        TableFactory::getTable('videos')->drop();
        parent::down();
    }
```

The `create` method will create the table, `drop` will drop the table, and `dropColumn` will drop the specified column.

There are many other methods available to the Table instance returned by the Table factory for defining columns.

1. int
2. tinyInt
3. smallInt
4. mediumInt
5. bigInt
6. float
7. double
8. decimal
9. varChar
10. char
11. tinyText
12. text
13. mediumText
14. longText
15. tinyBlob
16. blob
17. mediumBlob
18. longBlob
19. date
20. datetime
21. timestamp
22. time
23. json
24. column - Column is used if you need a column type that does not fit into the other rules.

In addition, the `rawQuery` method can be used in a migration to run a specific query.

#### Adding indexes

FEAST can add an index at the same time as creating by using the `index` method. The `index` method takes the following
parameters.

1. `columns` - This can be a string for a single column, or an array of strings for multiple.
2. `name` - String or null. If null, a generic name is dynamically created.
3. `autoIncrement` - True if you wish for this to be an autoincrement column.

An easier way to create an auto incrementing primary key is with the `autoIncrement` method. This method will create an
int column with the passed in name and optional length.

#### Adding primary key

FEAST can add a primary key to specified column with the `primary` method. The `primary` method takes only one
parameter:
`columnName`. Note that the column specified within this parameter must exist. Also, `primary` method can be called only
once per table. Otherwise, an exception will be thrown.

The `autoIncrement` method already calls the `primary` method, so the `primary` method should not be called when
creating an auto incrementing column with the `autoIncrement` method.

#### Altering tables

FEAST can alter tables by using the `rawQuery` method rather than by using the TableFactory.

### Running Migrations

To quickly run all migrations that have not ran up, simply run
`php famine feast:migration:run-all` in your terminal. For more detailed or advanced usage,
see [feast:migration](cli.md#feastmigration) in the CLI docs.

If you have cached your database info (see [feast:cache:dbinfo-generate](cli.md#feastcachedbinfo-generate)), then the
cache will automatically re-generate after migrations are ran.

### List Migrations

You can quickly get a list of all migrations as well as their status by running `php famine feast:migration:list`

[Back to Top](#working-with-databases)

## Complex Queries

To run complex queries on your data, several additional methods exist on the DatabaseInterface that can be used inside
your Mappers. These methods return a Query instance which has even more methods for interacting with your database.

### Database Interface

The five basic methods in the `DatabaseInterface` are `select`,`update`,`insert`, `replace` and `delete`. Each of these
methods takes a table name as the argument, and `update`,`insert`, and `replace` take an array of parameters to be
inserted/updated.

In addition, the DatabaseInterface has several transactional based methods if you need to run a set of queries in a
transaction

1. `beginTransaction` - This will begin a transaction and return true if successful, false on failure or if already in a
   transaction.
2. `isInTransaction` - This method will return true if in a transaction or false otherwise.
3. `commit` - This will commit the current transaction's changes to the database.
4. `rollback` - This will rollback (or abort) the changes for the current transaction.

### Query class

The `Query` class contains several methods for working with your database and allows for fine-tuned queries without
writing any SQL. The methods can be called in any order, and the executed query will be ordered correctly.

All bindings in the below methods are passed in as a prepared statement execution.

#### Filtering queries

##### Where

The `where` method creates a where clause on the query. It takes in a statement (or the where clause)
and bindings as either a `\Feast\Date` argument, a scalar, or an array of bindings for multiple. Each call to
the `where` method will create a parenthesis wrapped group, allowing you to focus only on what you need for that piece
of the statement.

Example

```php
$query->where('test = ? or test_name = ?', ['feast','feast])->where('active' => 1);
// This will result in the following where clause on the query. 
// where (test = ? or test_name = ?) and (active = ?) 
```

##### Having

The `having` method is semantically identical to the `where` method described above, but places a having clause instead
of a where clause.

##### Group By

The `groupBy` method takes an SQL excerpt as a parameter and creates a
`group by` clause on the query.

##### Limit

The `limit` method takes the number to limit to and an optional offset as parameters. Example:

```php
$query->limit(5,15)
// Adds LIMIT 5,15 to the SQL query
```

##### Order By

The `orderBy` method takes an SQL excerpt as a parameter and creates an order by clause on the SQL query.

#### Using Joins

FEAST has several methods on the query class for joins. Two each for left, right, and inner joins.

##### Join vs JoinUsing

Each of the Join methods (`leftJoin`, `rightJoin`, and `innerJoin`) take three arguments. The first is the table to join
to, the second is the column or columns (string or array) in the parent table to join on, and the third is the column or
columns (string or array) on the joined table to join on.

Each of the Join using methods (`leftJoinUsing`, `rightJoinUsing`, and `innerJoinUsing`)
take two arguments - The table name and the column or columns (string or array) that exists on both tables to join on.

#### Debugging Queries

Sometimes when developing, it helps to see the query as it would be executed to check if anything seems incorrect or out
of place. The `Query` class has a method `getRawQueryWithParams` that returns a string representation of the query with
all ? bindings replaced with their appropriate value.

[Back to Top](#working-with-databases)