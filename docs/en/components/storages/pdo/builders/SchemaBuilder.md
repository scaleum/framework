[Back to Contents](../../../../index.md)

**EN** | [UK](../../../../../uk/components/storages/pdo/builders/SchemaBuilder.md) | [RU](../../../../../ru/components/storages/pdo/builders/SchemaBuilder.md)
#  SchemaBuilder

`SchemaBuilder` is responsible for DDL operations: creating/dropping tables and databases, adding/updating/dropping columns and indexes, and renaming tables and columns.

##  Core Methods

| Method | Description |
| ------ | ----------- |
| `createTable(string $tableName, bool $ifNotExists = false): mixed` | Create a table from collected columns/indexes. |
| `dropTable(string $tableName, bool $ifExists = false): mixed` | Drop a table. |
| `createColumn(array|ColumnBuilderInterface $column, ?string $tableName = null): mixed` | Add column(s) to the builder or apply directly to a table. |
| `updateColumn(mixed $column, string $tableName): mixed` | Update a column definition. |
| `dropColumn(array|string $column, string $tableName): mixed` | Drop column(s). |
| `createIndex(array|IndexBuilderInterface $index, ?string $tableName = null): mixed` | Add index(es). |
| `dropIndex(string $indexName, string $tableName): mixed` | Drop an index. |
| `renameTable(string $fromTable, string $toTable): mixed` | Rename a table. |
| `renameColumn(string $tableName, string $fromColumn, string $toColumn): mixed` | Rename a column. |

##  Rename Column

```php
use Scaleum\Storages\PDO\Database;

$db = new Database('mysql:host=localhost;dbname=app', 'root', 'secret');

$db->getSchemaBuilder()->renameColumn('users', 'full_name', 'display_name');
```

##  SQL by Driver

* MySQL/PostgreSQL/SQLite: `ALTER TABLE <table> RENAME COLUMN <old> TO <new>;`
* SQL Server: `sp_rename '<table>.<old>', '<new>', 'COLUMN';`

##  Recommendations

* Renaming a column may affect indexes, triggers, and views: check dependent objects in the migration.
* For safer migrations, run operations inside a transaction where transactional DDL is supported.
* Use `prepare(true)` when you need SQL generation without execution.

[Back to the table of contents](../../../../index.md)
