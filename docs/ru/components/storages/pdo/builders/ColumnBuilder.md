[Вернуться к оглавлению](../../../../index.md)

# ColumnBuilder

`ColumnBuilder` — класс для декларативного описания столбцов таблицы и генерации соответствующих SQL-операций (создание, добавление, изменение, удаление) в Scaleum. Наследует `BuilderAbstract` и реализует `ColumnBuilderInterface`, предоставляя полный набор методов для настройки параметров столбца.

## Константы

| Константа     | Значение   | Описание                                              |
| ------------- | ---------- | ----------------------------------------------------- |
| `TYPE_PK`     | `'pk'`     | Первичный ключ                                        |
| `TYPE_BIGPK`  | `'bigpk'`  | Крупный первичный ключ                                |
| `TYPE_STRING` | `'string'` | Строковый тип                                         |
| ...           | ...        | Другие типы: `TEXT`, `INTEGER`, `DATE`, `JSON` и т.д. |
| `MODE_CREATE` | `2`        | Режим создания столбца                                |
| `MODE_ADD`    | `4`        | Режим добавления столбца                              |
| `MODE_UPDATE` | `8`        | Режим изменения столбца                               |
| `MODE_DROP`   | `16`       | Режим удаления столбца                                |

## Свойства

| Свойство      | Тип        | Доступ    | Описание                                                      |
| ------------- | ---------- | --------- | ------------------------------------------------------------- |
| `$type`       | `string`   | protected | Базовый тип столбца (`TYPE_*`).                               |
| `$constraint` | `mixed`    | protected | Параметр размера/точности (например, длина для VARCHAR).      |
| `$database`   | `Database` | protected | Подключение к базе данных, унаследовано от `BuilderAbstract`. |
| `$mode`       | `int`      | private   | Текущий режим операции (`MODE_*`).                            |
| `$table`      | `?string`  | protected | Имя таблицы.                                                  |
| `$column`     | `?string`  | protected | Имя столбца.                                                  |
| `$isNotNull`  | `bool`     | protected | Флаг `NOT NULL`.                                              |
| `$isUnique`   | `bool`     | protected | Флаг `UNIQUE`.                                                |
| `$isUnsigned` | `bool`     | protected | Флаг `UNSIGNED` (для числовых типов MySQL).                   |
| `$default`    | `mixed`    | protected | Значение по умолчанию.                                        |
| `$comment`    | `?string`  | protected | Комментарий столбца.                                          |

## Методы

| Подпись | Возвращаемый тип | Описание|
| ------- | ---------------- | --------|
| `__construct(string $type = self::TYPE_STRING, mixed $constraint = null, ?Database $db = null)` | —                | Инициализация типа и параметра; родительский конструктор.          |
| `setTable(string $table): self`                                                                 | `self`           | Установить имя таблицы.                                            |
| `setTableMode(int $mode): self`                                                                 | `self`           | Установить режим операции (`MODE_*`).                              |
| `setColumn(string $column): self`                                                               | `self`           | Установить имя столбца.                                            |
| `setNotNull(bool $flag = true): self`                                                           | `self`           | Включить или отключить `NOT NULL`.                                 |
| `setUnique(bool $flag = true): self`                                                            | `self`           | Включить или отключить `UNIQUE`.                                   |
| `setUnsigned(bool $flag = true): self`                                                          | `self`           | Включить или отключить `UNSIGNED`.                                 |
| `setDefaultValue(mixed $value, bool $quoted = true): self`                                      | `self`           | Установить значение по умолчанию; экранирует строку при `$quoted`. |
| `setComment(string $comment): self`                                                             | `self`           | Установить комментарий столбца.                                    |
| `getMode(): int`                                                                                | `int`            | Получить текущий режим операции.                                   |
| `getTable(): ?string`                                                                           | `string\|null`   | Получить имя таблицы.                                              |
| `getColumn(): ?string`                                                                          | `string\|null`   | Получить имя столбца.                                              |
| `getNotNull(): bool`                                                                            | `bool`           | Проверить флаг `NOT NULL`.                                         |
| `getUnique(): bool`                                                                             | `bool`           | Проверить флаг `UNIQUE`.                                           |
| `getUnsigned(): bool`                                                                           | `bool`           | Проверить флаг `UNSIGNED`.                                         |
| `getDefaultValue(): mixed`                                                                      | `mixed`          | Получить текущее значение по умолчанию.                            |
| `getComment(): ?string`                                                                         | `string\|null`   | Получить комментарий столбца.                                      |
| `__toString(): string`                                                                          | `string`         | Вернуть SQL-фрагмент (через `makeSQL()`).                          |

## Внутренние методы

| Подпись                 | Описание                                                                    |
| ----------------------- | --------------------------------------------------------------------------- |
| `makeColumn(): string`  | Сформировать часть SQL c именем столбца и его типом.                        |
| `makeType(): string`    | Сформировать SQL-часть с типом и ограничением (`constraint`).               |
| `makeNotNull(): string` | Вернуть `NOT NULL` или `NULL` в зависимости от флага.                       |
| `makeUnique(): string`  | Вернуть `UNIQUE` или пустую строку.                                         |
| `makeDefault(): string` | Вернуть фрагмент `DEFAULT ...` или пустую строку.                           |
| `makeSQL(): string`     | Собрать и вернуть полный SQL-фрагмент столбца, вызываемый в `__toString()`. |

## Пример использования

```php
use Scaleum\Storages\PDO\Builders\ColumnBuilder;
use Scaleum\Storages\PDO\Database;

$db = new Database('mysql:host=localhost;dbname=app', 'root', 'secret');

// Создание столбца status в режиме ADD
$sql = (new ColumnBuilder(ColumnBuilder::TYPE_INTEGER, null, $db))
    ->setTable('users')
    ->setTableMode(ColumnBuilder::MODE_ADD)
    ->setColumn('status')
    ->setDefaultValue(0)
    ->setNotNull()
    ->setComment('Статус пользователя')
    ->__toString();

$db->exec($sql);
```

## Рекомендации

* Используйте методы `set*()` для настройки столбца и `__toString()` для получения SQL.
* Убедитесь, что `MODE_*` соответствует нужной операции (`CREATE`, `ADD`, `UPDATE`, `DROP`).
* Для сложных ограничений можно комбинировать `setDefaultValue` и `setComment` вместе с внешними констрейнтами.

[Вернуться к оглавлению](../../../../index.md)
