[Вернуться к оглавлению](../../../../index.md)

[EN](../../../../../en/components/storages/pdo/builders/SchemaBuilder.md) | [UK](../../../../../uk/components/storages/pdo/builders/SchemaBuilder.md) | **RU**
# SchemaBuilder

`SchemaBuilder` отвечает за DDL-операции: создание/удаление таблиц и БД, добавление/изменение/удаление столбцов и индексов, а также переименование таблиц и столбцов.

## Основные методы

| Метод | Описание |
| ----- | -------- |
| `createTable(string $tableName, bool $ifNotExists = false): mixed` | Создать таблицу из накопленных столбцов/индексов. |
| `dropTable(string $tableName, bool $ifExists = false): mixed` | Удалить таблицу. |
| `createColumn(array|ColumnBuilderInterface $column, ?string $tableName = null): mixed` | Добавить столбец(ы) в билд или сразу применить к таблице. |
| `updateColumn(mixed $column, string $tableName): mixed` | Изменить определение столбца. |
| `dropColumn(array|string $column, string $tableName): mixed` | Удалить столбец(ы). |
| `createIndex(array|IndexBuilderInterface $index, ?string $tableName = null): mixed` | Добавить индекс(ы). |
| `dropIndex(string $indexName, string $tableName): mixed` | Удалить индекс. |
| `renameTable(string $fromTable, string $toTable): mixed` | Переименовать таблицу. |
| `renameColumn(string $tableName, string $fromColumn, string $toColumn): mixed` | Переименовать столбец. |

## Переименование столбца

```php
use Scaleum\Storages\PDO\Database;

$db = new Database('mysql:host=localhost;dbname=app', 'root', 'secret');

$db->getSchemaBuilder()->renameColumn('users', 'full_name', 'display_name');
```

## SQL по драйверам

* MySQL/PostgreSQL/SQLite: `ALTER TABLE <table> RENAME COLUMN <old> TO <new>;`
* SQL Server: `sp_rename '<table>.<old>', '<new>', 'COLUMN';`

## Рекомендации

* Переименование столбца может затрагивать индексы, триггеры и представления: проверьте зависимые объекты в миграции.
* Для безопасных миграций применяйте операции внутри транзакции, если СУБД поддерживает транзакционный DDL.
* Используйте `prepare(true)`, если нужно получить SQL без выполнения.

[Вернуться к оглавлению](../../../../index.md)
