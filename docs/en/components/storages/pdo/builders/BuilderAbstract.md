[Back to Contents](../../../../index.md)

**EN** | [UK](../../../../../uk/components/storages/pdo/builders/BuilderAbstract.md) | [RU](../../../../../ru/components/storages/pdo/builders/BuilderAbstract.md)
#  BuilderAbstract

`BuilderAbstract` is a base utility class for all SQL builders in the **Scaleum\Storages\PDO\Builders** package. It does NOT store the table name or the `CREATE / ALTER / DROP` modes; this logic is implemented in specific descendants (for example, `SchemaBuilder`, `ColumnBuilder`). The purpose of `BuilderAbstract` is to provide:

* database connection (inherited from `DatabaseProvider`),
* a registry of adapters for different drivers (`$adapters`),
* safe escaping of identifiers and values,
* a switch for "only generate SQL without execution" (`$prepare`),
* minimal optimization or pretty-printing of the final query.

##  Properties

| Property                | Type                   | Access           | Default Value         | Purpose |
| ----------------------- | ---------------------- | ---------------- | --------------------- | ------- |
| `$adapters`             | `array<string,string>` | protected static | `[]`                  | Map of `driverType ➜ adapter class`. Used by the `create()` factory. |
| `$identifierQuoteLeft`  | `string`               | protected        | `` ` ``               | Left quote character for identifiers.                                 |
| `$identifierQuoteRight` | `string`               | protected        | `` ` ``               | Right quote character for identifiers.                                |
| `$reservedIdentifiers`  | `array<int,string>`    | protected        | `['*']`               | Names that are not quoted.                                            |
| `$prepare`              | `bool`                 | protected        | `false`               | If `true`, methods return SQL without execution.                      |
| `$optimize`             | `bool`                 | protected        | `true`                | Minimal optimization (whitespace compression) of the final SQL.      |

##  Public Methods

| Signature                                                       | Return Type | Purpose |
| --------------------------------------------------------------- | ----------- | ------- |
| `static create(string $driverType, array $args = []): static`   | `static`    | Factory method: returns an adapter by driver type or throws an exception. |
| `__construct(?Database $database = null)`                       | —           | Passes the connection to the parent `DatabaseProvider`.                  |
| `getPrepare(): bool`                                            | `bool`      | Current state of the "generate but do not execute" flag.                 |
| `setPrepare(bool $prepare): self`                               | `self`      | Enables/disables the query preparation mode.                             |
| `getOptimize(): bool`                                           | `bool`      | Whether SQL minimization is enabled.                                    |
| `setOptimize(bool $optimize): self`                             | `self`      | Toggles minimization / pretty-print.                                    |
| `getOptimizedQuery(string $sql): string`                        | `string`    | Removes extra spaces, tabs, and line breaks.                            |
| `getPrettyQuery(string $sql): string`                           | `string`    | Formats SQL for readability.                                            |
| `getUniqueName(array $columns, string $prefix = 'key'): string` | `string`    | Generates a unique index/constraint name (adds a hash).                 |
| `__toString(): string`                                          | `string`    | Returns the result of `makeSQL()`.                                      |

###  Utilities (protected)

| Signature | Purpose |
| --------- | ------- |
| `makeSQL(): string`  | **Abstract**: descendant must return the final SQL. |
| `flush(): self`      | Resets the `$prepare` and `$optimize` flags. |
| `realize(string $sql, array $params = [], string $method = 'execute', array $args = []): mixed` | Executes SQL or returns the string depending on `$prepare`. |
| `quote(mixed $value): mixed`| Escapes a value via `DatabaseHelper::quote()`. |
| `quoteIdentifier(string $identifier): string`| Quotes an identifier if it is not in `$reservedIdentifiers`. |
| `protectIdentifiers(array\|string $item, bool $protect = true): array\|string` | Recursively applies `quoteIdentifier()`. |


##  Mini-example of a descendant

```php
<?php

declare(strict_types=1);

namespace App\Builders;

use Scaleum\Storages\PDO\Builders\BuilderAbstract;
use Scaleum\Storages\PDO\Database;

class RawSqlBuilder extends BuilderAbstract
{
    private string $sql = '';

    public function raw(string $sql): self
    {
        $this->sql = $sql;
        return $this;
    }

    protected function makeSQL(): string
    {
        return $this->sql;
    }
}
```

###  Usage

```php
<?php
$db = new Database('sqlite::memory:');

$sql = (new RawSqlBuilder($db))
    ->raw('SELECT 1 as result')
    ->setPrepare(true) // only get SQL
    ->__toString();

echo $sql; // SELECT 1 as result
```

---

##  Important points

* Use `setPrepare(true)` in tests or dry-run migrations to see the SQL without changing the database.

[Back to the table of contents](../../../../index.md)
