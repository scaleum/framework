[Повернутись до змісту](../../../../index.md)

[EN](../../../../../en/components/storages/pdo/builders/ColumnBuilder.md) | **UK** | [RU](../../../../../ru/components/storages/pdo/builders/ColumnBuilder.md)
#  ColumnBuilder

`ColumnBuilder` — клас для декларативного опису стовпців таблиці та генерації відповідних SQL-операцій (створення, додавання, зміни, видалення) у Scaleum. Наслідує `BuilderAbstract` і реалізує `ColumnBuilderInterface`, надаючи повний набір методів для налаштування параметрів стовпця.

##  Константи

| Константа     | Значення   | Опис                                                      |
| ------------- | ---------- | --------------------------------------------------------- |
| `TYPE_PK`     | `'pk'`     | Первинний ключ                                            |
| `TYPE_BIGPK`  | `'bigpk'`  | Великий первинний ключ                                    |
| `TYPE_STRING` | `'string'` | Рядковий тип                                             |
| ...           | ...        | Інші типи: `TEXT`, `INTEGER`, `DATE`, `JSON` тощо         |
| `MODE_CREATE` | `2`        | Режим створення стовпця                                   |
| `MODE_ADD`    | `4`        | Режим додавання стовпця                                   |
| `MODE_UPDATE` | `8`        | Режим зміни стовпця                                      |
| `MODE_DROP`   | `16`       | Режим видалення стовпця                                  |

##  Властивості

| Властивість   | Тип        | Доступ    | Опис                                                        |
| ------------- | ---------- | --------- | ----------------------------------------------------------- |
| `$type`       | `string`   | protected | Базовий тип стовпця (`TYPE_*`).                            |
| `$constraint` | `mixed`    | protected | Параметр розміру/точності (наприклад, довжина для VARCHAR). |
| `$database`   | `Database` | protected | Підключення до бази даних, успадковане від `BuilderAbstract`. |
| `$mode`       | `int`      | private   | Поточний режим операції (`MODE_*`).                         |
| `$table`      | `?string`  | protected | Ім'я таблиці.                                              |
| `$column`     | `?string`  | protected | Ім'я стовпця.                                              |
| `$isNotNull`  | `bool`     | protected | Прапорець `NOT NULL`.                                      |
| `$isUnique`   | `bool`     | protected | Прапорець `UNIQUE`.                                        |
| `$isUnsigned` | `bool`     | protected | Прапорець `UNSIGNED` (для числових типів MySQL).           |
| `$default`    | `mixed`    | protected | Значення за замовчуванням.                                |
| `$comment`    | `?string`  | protected | Коментар стовпця.                                          |

##  Методи

| Підпис | Повертаємий тип | Опис|
| ------- | ---------------- | --------|
| `__construct(string $type = self::TYPE_STRING, mixed $constraint = null, ?Database $db = null)` | —                | Ініціалізація типу та параметра; конструктор батьківського класу.          |
| `setTable(string $table): self`                                                                 | `self`           | Встановити ім'я таблиці.                                            |
| `setTableMode(int $mode): self`                                                                 | `self`           | Встановити режим операції (`MODE_*`).                              |
| `setColumn(string $column): self`                                                               | `self`           | Встановити ім'я стовпця.                                            |
| `setNotNull(bool $flag = true): self`                                                           | `self`           | Увімкнути або вимкнути `NOT NULL`.                                 |
| `setUnique(bool $flag = true): self`                                                            | `self`           | Увімкнути або вимкнути `UNIQUE`.                                   |
| `setUnsigned(bool $flag = true): self`                                                          | `self`           | Увімкнути або вимкнути `UNSIGNED`.                                 |
| `setDefaultValue(mixed $value, bool $quoted = true): self`                                      | `self`           | Встановити значення за замовчуванням; екранує рядок при `$quoted`. |
| `setComment(string $comment): self`                                                             | `self`           | Встановити коментар стовпця.                                    |
| `getMode(): int`                                                                                | `int`            | Отримати поточний режим операції.                                   |
| `getTable(): ?string`                                                                           | `string\|null`   | Отримати ім'я таблиці.                                              |
| `getColumn(): ?string`                                                                          | `string\|null`   | Отримати ім'я стовпця.                                              |
| `getNotNull(): bool`                                                                            | `bool`           | Перевірити прапорець `NOT NULL`.                                         |
| `getUnique(): bool`                                                                             | `bool`           | Перевірити прапорець `UNIQUE`.                                           |
| `getUnsigned(): bool`                                                                           | `bool`           | Перевірити прапорець `UNSIGNED`.                                         |
| `getDefaultValue(): mixed`                                                                      | `mixed`          | Отримати поточне значення за замовчуванням.                            |
| `getComment(): ?string`                                                                         | `string\|null`   | Отримати коментар стовпця.                                      |
| `__toString(): string`                                                                          | `string`         | Повернути SQL-фрагмент (через `makeSQL()`).                          |

##  Внутрішні методи

| Підпис                 | Опис                                                                    |
| ----------------------- | --------------------------------------------------------------------------- |
| `makeColumn(): string`  | Сформувати частину SQL з ім'ям стовпця та його типом.                        |
| `makeType(): string`    | Сформувати SQL-частину з типом та обмеженням (`constraint`).               |
| `makeNotNull(): string` | Повернути `NOT NULL` або `NULL` залежно від прапорця.                       |
| `makeUnique(): string`  | Повернути `UNIQUE` або порожній рядок.                                         |
| `makeDefault(): string` | Повернути фрагмент `DEFAULT ...` або порожній рядок.                           |
| `makeSQL(): string`     | Зібрати та повернути повний SQL-фрагмент стовпця, викликається в `__toString()`. |

##  Приклад використання

```php
use Scaleum\Storages\PDO\Builders\ColumnBuilder;
use Scaleum\Storages\PDO\Database;

$db = new Database('mysql:host=localhost;dbname=app', 'root', 'secret');

// Створення стовпця status у режимі ADD
$sql = (new ColumnBuilder(ColumnBuilder::TYPE_INTEGER, null, $db))
    ->setTable('users')
    ->setTableMode(ColumnBuilder::MODE_ADD)
    ->setColumn('status')
    ->setDefaultValue(0)
    ->setNotNull()
    ->setComment('Статус користувача')
    ->__toString();

$db->exec($sql);
```

##  Рекомендації

* Використовуйте методи `set*()` для налаштування стовпця та `__toString()` для отримання SQL.
* Переконайтеся, що `MODE_*` відповідає потрібній операції (`CREATE`, `ADD`, `UPDATE`, `DROP`).
* Для складних обмежень можна комбінувати `setDefaultValue` та `setComment` разом із зовнішніми констрейнтами.

[Повернутися до змісту](../../../../index.md)
