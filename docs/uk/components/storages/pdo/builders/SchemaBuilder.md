[Повернутись до змісту](../../../../index.md)

[EN](../../../../../en/components/storages/pdo/builders/SchemaBuilder.md) | **UK** | [RU](../../../../../ru/components/storages/pdo/builders/SchemaBuilder.md)
#  SchemaBuilder

`SchemaBuilder` відповідає за DDL-операції: створення/видалення таблиць і БД, додавання/зміну/видалення стовпців та індексів, а також перейменування таблиць і стовпців.

##  Основні методи

| Метод | Опис |
| ----- | ---- |
| `createTable(string $tableName, bool $ifNotExists = false): mixed` | Створити таблицю з накопичених стовпців/індексів. |
| `dropTable(string $tableName, bool $ifExists = false): mixed` | Видалити таблицю. |
| `createColumn(array|ColumnBuilderInterface $column, ?string $tableName = null): mixed` | Додати стовпець(ці) в білдер або одразу застосувати до таблиці. |
| `updateColumn(mixed $column, string $tableName): mixed` | Змінити визначення стовпця. |
| `dropColumn(array|string $column, string $tableName): mixed` | Видалити стовпець(ці). |
| `createIndex(array|IndexBuilderInterface $index, ?string $tableName = null): mixed` | Додати індекс(и). |
| `dropIndex(string $indexName, string $tableName): mixed` | Видалити індекс. |
| `renameTable(string $fromTable, string $toTable): mixed` | Перейменувати таблицю. |
| `renameColumn(string $tableName, string $fromColumn, string $toColumn): mixed` | Перейменувати стовпець. |

##  Перейменування стовпця

```php
use Scaleum\Storages\PDO\Database;

$db = new Database('mysql:host=localhost;dbname=app', 'root', 'secret');

$db->getSchemaBuilder()->renameColumn('users', 'full_name', 'display_name');
```

##  SQL за драйверами

* MySQL/PostgreSQL/SQLite: `ALTER TABLE <table> RENAME COLUMN <old> TO <new>;`
* SQL Server: `sp_rename '<table>.<old>', '<new>', 'COLUMN';`

##  Рекомендації

* Перейменування стовпця може впливати на індекси, тригери та представлення: перевірте залежні об'єкти в міграції.
* Для безпечніших міграцій виконуйте операції в транзакції, якщо СУБД підтримує транзакційний DDL.
* Використовуйте `prepare(true)`, коли потрібно отримати SQL без виконання.

[Повернутися до змісту](../../../../index.md)
