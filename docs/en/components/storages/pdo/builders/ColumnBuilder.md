[Back to Contents](../../../../index.md)

**EN** | [UK](../../../../../uk/components/storages/pdo/builders/ColumnBuilder.md) | [RU](../../../../../ru/components/storages/pdo/builders/ColumnBuilder.md)
#  ColumnBuilder

`ColumnBuilder` is a class for declarative description of table columns and generation of corresponding SQL operations (creation, addition, modification, deletion) in Scaleum. It inherits from `BuilderAbstract` and implements `ColumnBuilderInterface`, providing a full set of methods for configuring column parameters.

##  Constants

| Constant      | Value      | Description                                             |
| ------------- | ---------- | ------------------------------------------------------- |
| `TYPE_PK`     | `'pk'`     | Primary key                                             |
| `TYPE_BIGPK`  | `'bigpk'`  | Large primary key                                       |
| `TYPE_STRING` | `'string'` | String type                                            |
| ...           | ...        | Other types: `TEXT`, `INTEGER`, `DATE`, `JSON`, etc.   |
| `MODE_CREATE` | `2`        | Column creation mode                                    |
| `MODE_ADD`    | `4`        | Column addition mode                                    |
| `MODE_UPDATE` | `8`        | Column modification mode                                |
| `MODE_DROP`   | `16`       | Column deletion mode                                    |

##  Properties

| Property      | Type       | Access    | Description                                               |
| ------------- | ---------- | --------- | --------------------------------------------------------- |
| `$type`       | `string`   | protected | Base column type (`TYPE_*`).                              |
| `$constraint` | `mixed`    | protected | Size/precision parameter (e.g., length for VARCHAR).     |
| `$database`   | `Database` | protected | Database connection, inherited from `BuilderAbstract`.   |
| `$mode`       | `int`      | private   | Current operation mode (`MODE_*`).                        |
| `$table`      | `?string`  | protected | Table name.                                               |
| `$column`     | `?string`  | protected | Column name.                                              |
| `$isNotNull`  | `bool`     | protected | `NOT NULL` flag.                                          |
| `$isUnique`   | `bool`     | protected | `UNIQUE` flag.                                            |
| `$isUnsigned` | `bool`     | protected | `UNSIGNED` flag (for MySQL numeric types).                |
| `$default`    | `mixed`    | protected | Default value.                                            |
| `$comment`    | `?string`  | protected | Column comment.                                           |

##  Methods

| Signature                                                                                      | Return Type      | Description                                                      |
| ---------------------------------------------------------------------------------------------- | ---------------- | ---------------------------------------------------------------- |
| `__construct(string $type = self::TYPE_STRING, mixed $constraint = null, ?Database $db = null)` | —                | Initialize type and parameter; parent constructor.               |
| `setTable(string $table): self`                                                                | `self`           | Set the table name.                                              |
| `setTableMode(int $mode): self`                                                                | `self`           | Set the operation mode (`MODE_*`).                              |
| `setColumn(string $column): self`                                                              | `self`           | Set the column name.                                            |
| `setNotNull(bool $flag = true): self`                                                          | `self`           | Enable or disable `NOT NULL`.                                   |
| `setUnique(bool $flag = true): self`                                                           | `self`           | Enable or disable `UNIQUE`.                                     |
| `setUnsigned(bool $flag = true): self`                                                         | `self`           | Enable or disable `UNSIGNED`.                                   |
| `setDefaultValue(mixed $value, bool $quoted = true): self`                                     | `self`           | Set the default value; escapes string if `$quoted`.            |
| `setComment(string $comment): self`                                                            | `self`           | Set the column comment.                                         |
| `getMode(): int`                                                                               | `int`            | Get the current operation mode.                                |
| `getTable(): ?string`                                                                          | `string\|null`   | Get the table name.                                            |
| `getColumn(): ?string`                                                                         | `string\|null`   | Get the column name.                                           |
| `getNotNull(): bool`                                                                           | `bool`           | Check the `NOT NULL` flag.                                    |
| `getUnique(): bool`                                                                            | `bool`           | Check the `UNIQUE` flag.                                      |
| `getUnsigned(): bool`                                                                          | `bool`           | Check the `UNSIGNED` flag.                                    |
| `getDefaultValue(): mixed`                                                                     | `mixed`          | Get the current default value.                                |
| `getComment(): ?string`                                                                        | `string\|null`   | Get the column comment.                                      |
| `__toString(): string`                                                                         | `string`         | Return the SQL fragment (via `makeSQL()`).                   |

##  Internal Methods

| Signature           | Description                                                        |
| ------------------- | ----------------------------------------------------------------- |
| `makeColumn(): string`  | Generate the SQL part with the column name and its type.         |
| `makeType(): string`    | Generate the SQL part with type and constraint (`constraint`).   |
| `makeNotNull(): string` | Return `NOT NULL` or `NULL` depending on the flag.               |
| `makeUnique(): string`  | Return `UNIQUE` or an empty string.                              |
| `makeDefault(): string` | Return the `DEFAULT ...` fragment or an empty string.            |
| `makeSQL(): string`     | Assemble and return the full SQL fragment of the column, called in `__toString()`. |

##  Usage Example

```php
use Scaleum\Storages\PDO\Builders\ColumnBuilder;
use Scaleum\Storages\PDO\Database;

$db = new Database('mysql:host=localhost;dbname=app', 'root', 'secret');

// Creating the status column in ADD mode
$sql = (new ColumnBuilder(ColumnBuilder::TYPE_INTEGER, null, $db))
    ->setTable('users')
    ->setTableMode(ColumnBuilder::MODE_ADD)
    ->setColumn('status')
    ->setDefaultValue(0)
    ->setNotNull()
    ->setComment('User status')
    ->__toString();

$db->exec($sql);
```

##  Recommendations

* Use the `set*()` methods to configure the column and `__toString()` to get the SQL.
* Ensure that `MODE_*` corresponds to the required operation (`CREATE`, `ADD`, `UPDATE`, `DROP`).
* For complex constraints, you can combine `setDefaultValue` and `setComment` together with external constraints.

[Back to the table of contents](../../../../index.md)
