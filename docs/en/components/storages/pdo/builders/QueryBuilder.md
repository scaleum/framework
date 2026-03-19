[Back to the table of contents](../../../../index.md)

**EN** | [UK](../../../../../uk/components/storages/pdo/builders/QueryBuilder.md) | [RU](../../../../../ru/components/storages/pdo/builders/QueryBuilder.md)
#  QueryBuilder

`QueryBuilder` is the main SQL builder in `Scaleum\Storages\PDO\Builders` for constructing and executing `SELECT`, `INSERT`, `UPDATE`, `DELETE`, `TRUNCATE`, CTE (`WITH`), and `UNION` queries.

The class extends [BuilderAbstract](./BuilderAbstract.md) and implements [QueryBuilderInterface](./contracts/QueryBuilderInterface.md), thus supporting fluent style (`chain` calls) and `prepare()`/`optimize()` modes.

##  Supported Drivers

| Driver  | Adapter                     |
| ------- | --------------------------- |
| `mysql` | `Adapters\MySQL\Query`      |
| `pgsql` | `Adapters\PostgreSQL\Query` |
| `sqlite`| `Adapters\SQLite\Query`     |
| `sqlsrv`| `Adapters\SQLServer\Query`  |
| `mssql` | `Adapters\SQLServer\Query`  |

##  Key Properties

| Property     | Type      | Purpose                                      |
| ------------ | --------- | --------------------------------------------|
| `$from`      | `array`   | Sources for `FROM`.                          |
| `$select`    | `array`   | List of selected fields and quoting flags. |
| `$join`      | `array`   | Accumulated `JOIN` parts.                    |
| `$where`     | `array`   | Conditions for the `WHERE` block.            |
| `$having`    | `array`   | Conditions for the `HAVING` block.           |
| `$groupBy`   | `array`   | Grouping fields.                             |
| `$orderBy`   | `array`   | Sorting.                                    |
| `$limit` / `$offset` | `int` | Limit and offset of the selection.         |
| `$set`       | `array`   | Data for `INSERT`/`UPDATE`, including batch mode. |
| `$whereKey`  | `?string` | Key for batch `UPDATE` (`makeUpdateBatch`). |
| `$ctes`      | `array`   | CTE descriptions for `WITH`/`WITH RECURSIVE`. |
| `$unions`    | `array`   | Subqueries for `UNION`/`UNION ALL`.          |
| `$cachedState` | `array` | Snapshot of builder state (used in batch `delete`). |

##  Main Public Methods

| Signature                                                                                         | Return Type | Purpose                                                                                   |
| ------------------------------------------------------------------------------------------------ | ----------- | ----------------------------------------------------------------------------------------- |
| `select(array|string $select = '*', bool $quoting = true): self`                                 | `self`      | Defines the set of fields to select.                                                     |
| `from(array|string $from): self`                                                                 | `self`      | Sets tables/aliases in `FROM`.                                                           |
| `join(string $table, string $rule, ?string $type = null): self`                                  | `self`      | Adds table join (`JOIN`).                                                                 |
| `where(array|string $field, mixed $value = null, bool $quoting = true): self`                    | `self`      | Adds a condition to `WHERE` (AND).                                                       |
| `orWhere(array|string $field, mixed $value = null, bool $quoting = true): self`                  | `self`      | Adds a condition to `WHERE` (OR).                                                        |
| `groupBy(array|string $field): self`                                                             | `self`      | Adds `GROUP BY`.                                                                          |
| `having(array|string $field, mixed $value = null, bool $quoting = true): self`                   | `self`      | Adds `HAVING`.                                                                           |
| `orderBy(array|string $field, array|string $direction = 'ASC'): self`                           | `self`      | Adds `ORDER BY` with validation of `ASC/DESC`.                                          |
| `limit(int $value, ?int $offset = null): self`                                                  | `self`      | Limits the number of rows and optionally sets `offset`.                                 |
| `offset(int $offset): self`                                                                      | `self`      | Sets the offset.                                                                         |
| `insert(?string $table = null, array $set = [], bool $replaceIfExists = false): mixed`           | `mixed`     | Generates and executes `INSERT` or `REPLACE`.                                           |
| `update(array|string $tableName = null, array $set = [], array|string $where = null, ?string $whereKey = null, ?int $limit = null): mixed` | `mixed` | Generates and executes `UPDATE`, including batch variant.                               |
| `delete(array|string $table = null, array|string $where = null, ?int $limit = null): mixed`      | `mixed`     | Generates and executes `DELETE` (requires `WHERE`).                                    |
| `truncate(string $table = null): mixed`                                                         | `mixed`     | Generates and executes `TRUNCATE`.                                                      |
| `rows(array $args = []): mixed`                                                                  | `mixed`     | Executes `SELECT` and returns a set of rows (`fetchAll`).                               |
| `row(array $args = []): mixed`                                                                   | `mixed`     | Executes `SELECT` and returns a single record (`fetch`).                                |
| `rowColumn(array $args = []): mixed`                                                             | `mixed`     | Executes `SELECT` and returns a single column (`fetchColumn`).                          |
| `union(callable|string|self $query): self`                                                      | `self`      | Adds `UNION` from a callback, raw SQL, or another builder instance.                      |
| `unionAll(callable|string|self $query): self`                                                   | `self`      | Adds `UNION ALL` from a callback, raw SQL, or another builder instance.                  |

##  Advanced Features

| Group | Methods | What it does |
| ------ | ------ | ---------- |
| Parentheses groups | `whereWrap()`, `whereWrapOr()`, `whereWrapEnd()`, `havingWrap()`, `havingWrapOr()`, `havingWrapEnd()` | Allows explicit control of logical groups of conditions. |
| LIKE conditions | `like()`, `notLike()`, `orLike()`, `orNotLike()` | Forms `LIKE`/`NOT LIKE` with modes `both`, `before`, `after`, `none`. |
| Sets of conditions | `whereIn()`, `whereNotIn()`, `orWhereIn()`, `orWhereNotIn()` | Forms `IN`/`NOT IN`. |
| Ranges and NULL | `whereBetween()`, `orWhereBetween()`, `whereNotBetween()`, `orWhereNotBetween()`, `whereNull()`, `orWhereNull()`, `whereNotNull()`, `orWhereNotNull()` | Supports `BETWEEN` and `IS [NOT] NULL` checks. |
| CTE | `with()`, `withRecursive()` | Adds CTE expressions at the beginning of the query (`WITH ...`). |
| Union | `union()`, `unionAll()` | Combines queries via callback, raw SQL text, or another builder. |
| Execution and state | `execute()`, `prepare()`, `optimize()`, `flush()` | Executes arbitrary SQL, enables preparation modes, and clears builder state. |

##  Internal Logic

| Method | Purpose |
| ----- | ---------- |
| `makeSelect()` | Assembles a complete `SELECT` with `WITH`, `JOIN`, `WHERE`, `GROUP BY`, `HAVING`, `ORDER BY`, `LIMIT/OFFSET`, `UNION`. |
| `makeInsert()`, `makeInsertBatch()` | Builds single and batch `INSERT/REPLACE`. |
| `makeUpdate()`, `makeUpdateBatch()` | Builds single and batch `UPDATE` (via `CASE WHEN`). |
| `makeDelete()` | Builds `DELETE` with support for `ORDER BY` and `LIMIT`. |
| `makeWhere*()`, `makeHaving()`, `makeLike()` | Forms conditions considering nested parentheses. |
| `makeWith()` | Builds `WITH`/`WITH RECURSIVE` from `$ctes`. |
| `cache()` / `restore()` | Temporarily saves and restores builder state. |

##  Usage Example

```php
use Scaleum\Storages\PDO\Builders\QueryBuilder;
use Scaleum\Storages\PDO\Database;

$db = new Database('mysql:host=localhost;dbname=app', 'root', 'secret');

$result = (new QueryBuilder($db))
    ->select(['u.id', 'u.email'])
    ->from('users u')
    ->joinLeft('profiles p', 'p.user_id = u.id')
    ->whereWrap()
        ->where('u.active', 1)
        ->orWhereNull('u.deleted_at')
    ->whereWrapEnd()
    ->orderBy('u.id', 'DESC')
    ->limit(20)
    ->rows();
```

##  Examples with WITH

###  WITH (regular CTE)

```php
use Scaleum\Storages\PDO\Builders\QueryBuilder;
use Scaleum\Storages\PDO\Database;

$db = new Database('mysql:host=localhost;dbname=app', 'root', 'secret');

$result = (new QueryBuilder($db))
    ->with(
        'active_users',
        "SELECT id, email FROM users WHERE active = 1"
    )
    ->select(['a.id', 'a.email'])
    ->from('active_users a')
    ->orderBy('a.id', 'DESC')
    ->rows();
```

###  WITH RECURSIVE

```php
use Scaleum\Storages\PDO\Builders\QueryBuilder;
use Scaleum\Storages\PDO\Database;

$db = new Database('pgsql:host=localhost;dbname=app', 'postgres', 'secret');

$tree = (new QueryBuilder($db))
    ->withRecursive(
        'tree',
        "
        SELECT id, parent_id, title, 1 AS level
        FROM categories
        WHERE parent_id IS NULL
        UNION ALL
        SELECT c.id, c.parent_id, c.title, t.level + 1
        FROM categories c
        JOIN tree t ON t.id = c.parent_id
        ",
        ['id', 'parent_id', 'title', 'level']
    )
    ->select(['t.id', 't.title', 't.level'])
    ->from('tree t')
    ->orderBy(['t.level', 't.id'], ['ASC', 'ASC'])
    ->rows();
```

###  WITH RECURSIVE via unionAll() builder API

```php
use Scaleum\Storages\PDO\Builders\QueryBuilder;
use Scaleum\Storages\PDO\Database;

$db = new Database('mysql:host=localhost;dbname=app', 'root', 'secret');

$cteSql = (new QueryBuilder($db))
    ->prepare(true)
    ->select(['id', 'parent_id', 'title'])
    ->from('categories')
    ->where('parent_id', null)
    ->unionAll(function ($q) {
        $q->prepare(true)
            ->select(['c.id', 'c.parent_id', 'c.title'])
            ->from('categories c')
            ->joinInner('tree t', 'c.parent_id = t.id');
    })
    ->rows();

$sql = (new QueryBuilder($db))
    ->prepare(true)
    ->withRecursive('tree', $cteSql, ['id', 'parent_id', 'title'])
    ->select(['t.id', 't.title'])
    ->from('tree t')
    ->rows();
```

##  Examples with UNION

###  UNION via callback

```php
$sql = (new QueryBuilder($db))
    ->prepare(true)
    ->select(['id', 'email'])
    ->from('users')
    ->where('active', 1)
    ->union(function ($q) {
        $q->prepare(true)
            ->select(['id', 'email'])
            ->from('admins')
            ->where('active', 1);
    })
    ->rows();
```

###  UNION using ready SQL text

```php
$unionSql = (new QueryBuilder($db))
    ->prepare(true)
    ->select(['id', 'email'])
    ->from('admins')
    ->where('active', 1)
    ->rows();

$sql = (new QueryBuilder($db))
    ->prepare(true)
    ->select(['id', 'email'])
    ->from('users')
    ->where('active', 1)
    ->union($unionSql)
    ->rows();
```

###  UNION ALL using another builder instance

```php
$unionBuilder = (new QueryBuilder($db))
    ->select(['id', 'email'])
    ->from('admins')
    ->where('active', 1);

$sql = (new QueryBuilder($db))
    ->prepare(true)
    ->select(['id', 'email'])
    ->from('users')
    ->where('active', 1)
    ->unionAll($unionBuilder)
    ->rows();
```

In all modes, `union()/unionAll()` materialize the SQL subquery immediately (snapshot),
so further changes to the original callback/builder do not affect the already added `UNION`.

###  Get SQL without execution

```php
$sql = (new QueryBuilder($db))
    ->with('u', 'SELECT id, email FROM users')
    ->select('*')
    ->from('u')
    ->prepare(true)
    ->rows();

// $sql contains the SELECT string with CTE, without querying the database.
```

##  Related documents

* [BuilderAbstract](./BuilderAbstract.md)
* [ColumnBuilder](./ColumnBuilder.md)
* [QueryBuilderInterface](./contracts/QueryBuilderInterface.md)
* [DatabaseProvider](../DatabaseProvider.md)
* [DatabaseProviderInterface](../DatabaseProviderInterface.md)

##  Practical recommendations

* Before `update()` in batch mode, always set `whereKey()`, otherwise an exception will be thrown.
* For safe `delete()`, always add `where()`: the class specifically blocks `DELETE` without a condition.
* If you need to get SQL without execution, use `prepare(true)` and then `rows()`/`row()` or `execute()`.

[Back to contents](../../../../index.md)