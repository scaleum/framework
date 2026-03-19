[Back to table of contents](../../../../../index.md)

**EN** | [UK](../../../../../../uk/components/storages/pdo/builders/contracts/QueryBuilderInterface.md) | [RU](../../../../../../ru/components/storages/pdo/builders/contracts/QueryBuilderInterface.md)
#  QueryBuilderInterface

`QueryBuilderInterface` describes the contract of an SQL builder for working with `SELECT`, `INSERT`, `UPDATE`, `DELETE`, CTE (`WITH`), unions (`UNION`), and filtering conditions in the PDO layer of Scaleum.

The interface is located in the namespace `Scaleum\Storages\PDO\Builders\Contracts` and defines a fluent API: most methods return `self` for chain calls.

##  Main methods

| Signature | Return type | Purpose |
| ------- | ---------------- | ---------- |
| `select(array\|string $select = '*', bool $quoting = true): self` | `self` | Forms the list of selected fields. |
| `from(array\|string $from): self` | `self` | Specifies the data source (`FROM`). |
| `insert(?string $table = null, array $set = [], bool $replaceIfExists = false): mixed` | `mixed` | Executes `INSERT` (or `REPLACE`, if supported and enabled). |
| `update(?string $table = null, array $set = [], array\|string $where = null, ?string $whereKey = null, ?int $limit = null): mixed` | `mixed` | Executes `UPDATE` with conditions and row limit. |
| `delete(array\|string $table = null, array\|string $where = null, ?int $limit = null): mixed` | `mixed` | Executes `DELETE` with optional conditions. |
| `truncate(?string $table = null): mixed` | `mixed` | Clears the table (`TRUNCATE`). |

##  WHERE and HAVING

| Signature | Return type | Purpose |
| ------- | ---------------- | ---------- |
| `where(array\|string $field, mixed $value = null, bool $quoting = true): self` | `self` | Adds a condition to `WHERE` (AND). |
| `orWhere(array\|string $field, mixed $value = null, bool $quoting = true): self` | `self` | Adds a condition to `WHERE` (OR). |
| `whereIn(string $field, array $values): self` | `self` | `IN` condition. |
| `whereNotIn(string $field, array $values): self` | `self` | `NOT IN` condition. |
| `whereBetween(string $field, array $range): self` | `self` | `BETWEEN` condition. |
| `whereNotBetween(string $field, array $range): self` | `self` | `NOT BETWEEN` condition. |
| `whereNull(string $field): self` | `self` | `IS NULL` condition. |
| `whereNotNull(string $field): self` | `self` | `IS NOT NULL` condition. |
| `orWhereIn(string $field, array $values): self` | `self` | `OR ... IN`. |
| `orWhereNotIn(string $field, array $values): self` | `self` | `OR ... NOT IN`. |
| `orWhereBetween(string $field, array $range): self` | `self` | `OR ... BETWEEN`. |
| `orWhereNotBetween(string $field, array $range): self` | `self` | `OR ... NOT BETWEEN`. |
| `orWhereNull(string $field): self` | `self` | `OR ... IS NULL`. |
| `orWhereNotNull(string $field): self` | `self` | `OR ... IS NOT NULL`. |
| `having(array\|string $field, mixed $value = null, bool $quoting = true): self` | `self` | Adds a condition to `HAVING` (AND). |
| `orHaving(array\|string $field, mixed $value, bool $quoting = true): self` | `self` | Adds a condition to `HAVING` (OR). |
| `whereWrap(): self` | `self` | Opens a logical group in `WHERE` (`(`). |
| `whereWrapOr(): self` | `self` | Opens an `OR` group in `WHERE`. |
| `whereWrapEnd(): self` | `self` | Closes a group in `WHERE` (`)`). |
| `havingWrap(): self` | `self` | Opens a logical group in `HAVING`. |
| `havingWrapOr(): self` | `self` | Opens an `OR` group in `HAVING`. |
| `havingWrapEnd(): self` | `self` | Closes a group in `HAVING`. |

##  LIKE conditions

| Signature | Return type | Purpose |
| ------- | ---------------- | ---------- |
| `like(string $field, ?string $match = null, string $side = 'both'): mixed` | `mixed` | Adds `LIKE` (usually `%value%`, depending on `$side`). |
| `notLike(string $field, ?string $match = null, string $side = 'both'): self` | `self` | Adds `NOT LIKE`. |
| `orLike(string $field, ?string $match = null, string $side = 'both'): self` | `self` | Adds `OR LIKE`. |
| `orNotLike(string $field, ?string $match = null, string $side = 'both'): self` | `self` | Adds `OR NOT LIKE`. |

##  JOIN, sorting and aggregation

| Signature | Return type | Purpose |
| ------- | ---------------- | ---------- |
| `join(string $table, string $rule, ?string $type = null): self` | `self` | Universal `JOIN` with join type. |
| `joinInner(string $table, string $rule): self` | `self` | `INNER JOIN`. |
| `joinLeft(string $table, string $rule): self` | `self` | `LEFT JOIN`. |
| `joinRight(string $table, string $rule): self` | `self` | `RIGHT JOIN`. |
| `joinOuter(string $table, string $rule): self` | `self` | `OUTER JOIN`. |
| `groupBy(array\|string $field): self` | `self` | Grouping (`GROUP BY`). |
| `orderBy(array\|string $field, array\|string $direction = 'ASC'): self` | `self` | Sorting (`ORDER BY`). |
| `limit(int $value): self` | `self` | Limits the number of rows (`LIMIT`). |
| `offset(int $offset): self` | `self` | Offset of selection (`OFFSET`). |
| `modifiers(array\|string $modifiers): self` | `self` | SQL query modifiers (e.g., `DISTINCT`). |

##  CTE, UNION and Execution Configuration

| Signature | Return Type | Purpose |
| --------- | ----------- | ------- |
| `with(string $alias, string $sql, array $columns = []): self` | `self` | Adds a CTE `WITH alias AS (...)`. |
| `withRecursive(string $alias, string $sql, array $columns = []): self` | `self` | Adds a recursive CTE (`WITH RECURSIVE`). |
| `union(callable\|string\|self $query): self` | `self` | Combines the current query with another (`UNION`) via callback, raw SQL, or builder. |
| `unionAll(callable\|string\|self $query): self` | `self` | Combines queries without removing duplicates (`UNION ALL`) via callback, raw SQL, or builder. |
| `prepare(bool $value = false): self` | `self` | Enables/disables SQL preparation mode without immediate execution. |
| `optimize(bool $value = false): self` | `self` | Toggles SQL optimization/formatting. |
| `flush(): self` | `self` | Resets the internal state of the builder. |
| `execute(string $sql, array $params = [], string $method = 'execute', array $args = []): mixed` | `mixed` | Executes arbitrary SQL via the selected driver method. |

##  Data and Result Fetching

| Signature | Return Type | Purpose |
| --------- | ----------- | ------- |
| `set(array\|string $field, mixed $value = null, bool $quoting = true, bool $isBatch = false): self` | `self` | Sets data for `INSERT`/`UPDATE`. |
| `setAsBatch(array $field, mixed $value = null, bool $quoting = true): self` | `self` | Batch data setting. |
| `whereKey(string $key): self` | `self` | Sets the key used in some update/filter methods. |
| `rows(array $args = []): mixed` | `mixed` | Returns a set of result rows. |
| `row(array $args = []): mixed` | `mixed` | Returns a single result row. |
| `rowColumn(array $args = []): mixed` | `mixed` | Returns the value of one column/scalar. |

##  Usage Example

```php
use Scaleum\Storages\PDO\Builders\Contracts\QueryBuilderInterface;

/** @var QueryBuilderInterface $qb */
$users = $qb
    ->select(['u.id', 'u.email'])
    ->from('users u')
    ->where('u.active', 1)
    ->orWhereNull('u.deleted_at')
    ->orderBy('u.id', 'DESC')
    ->limit(20)
    ->rows();
```

##  Practical Recommendations

* For complex filtering logic, combine `whereWrap()`/`whereWrapEnd()` and `havingWrap()`/`havingWrapEnd()`.
* To generate SQL without execution, enable `prepare(true)`.
* For CTE scenarios (`WITH`), first define subqueries via `with()`/`withRecursive()`, then build the main `select()`.
* For `union()`/`unionAll()`, you can pass a callback, SQL string, or another builder; the subquery is snapshotted immediately, so later changes to the source do not affect the already added `UNION`.

[Back to Contents](../../../../../index.md)
