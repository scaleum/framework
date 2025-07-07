[Вернуться к оглавлению](../../../../index.md)

# BuilderAbstract

`BuilderAbstract` — базовый класс‑утилита для всех SQL‑конструкторов (*builders*) в пакете **Scaleum\Storages\PDO\Builders**. Он НЕ хранит имя таблицы либо режимы `CREATE / ALTER / DROP`; эта логика реализуется в конкретных наследниках (например, `SchemaBuilder`, `ColumnBuilder`).  Задача `BuilderAbstract` — предоставить:

* подключение к базе данных (унаследовано от `DatabaseProvider`),
* реестр адаптеров под разные драйверы (`$adapters`),
* безопасное экранирование идентификаторов и значений,
* переключатель «только сформировать SQL без выполнения» (`$prepare`),
* минимальную оптимизацию либо pretty‑print готового запроса.

## Свойства

| Свойство                | Тип                    | Доступ           | Значение по умолчанию | Назначение |
| ----------------------- | ---------------------- | ---------------- | --------------------- | ---------- |
| `$adapters`             | `array<string,string>` | protected static | `[]`                  | Карта `driverType ➜ класс‑адаптер`. Используется фабрикой `create()`. |
| `$identifierQuoteLeft`  | `string`               | protected        | `` ` ``               | Левый символ кавычек идентификаторов.                                 |
| `$identifierQuoteRight` | `string`               | protected        | `` ` ``               | Правый символ кавычек идентификаторов.                                |
| `$reservedIdentifiers`  | `array<int,string>`    | protected        | `['*']`               | Имена, которые не кавычатся.                                          |
| `$prepare`              | `bool`                 | protected        | `false`               | Если `true`, методы возвращают SQL без выполнения.                    |
| `$optimize`             | `bool`                 | protected        | `true`                | Мини‑оптимизация (сжатие пробелов) готового SQL.                      |

## Публичные методы

| Подпись                                                         | Возвращаемый тип | Назначение |
| --------------------------------------------------------------- | ---------------- | ---------- |
| `static create(string $driverType, array $args = []): static`   | `static`         | Фабричный метод: возвращает адаптер по типу драйвера либо бросает исключение. |
| `__construct(?Database $database = null)`                       | —                | Передаёт соединение в родительский `DatabaseProvider`.                        |
| `getPrepare(): bool`                                            | `bool`           | Текущее состояние флага «сформировать, но не выполнять».                      |
| `setPrepare(bool $prepare): self`                               | `self`           | Включает/выключает режим подготовки запроса.                                  |
| `getOptimize(): bool`                                           | `bool`           | Включена ли минимизация SQL.                                                  |
| `setOptimize(bool $optimize): self`                             | `self`           | Переключает минимизацию / pretty‑print.                                       |
| `getOptimizedQuery(string $sql): string`                        | `string`         | Удаляет лишние пробелы, табы и переносы.                                      |
| `getPrettyQuery(string $sql): string`                           | `string`         | Форматирует SQL для читаемости.                                               |
| `getUniqueName(array $columns, string $prefix = 'key'): string` | `string`         | Генерирует уникальное имя индекса/констрейнта (добавляет хеш).                |
| `__toString(): string`                                          | `string`         | Возвращает результат `makeSQL()`.                                             |

### Утилиты (protected)

| Подпись | Назначение |
| ------- | ---------- |
| `makeSQL(): string`  | **Абстрактный**: наследник должен вернуть итоговый SQL.|
| `flush(): self`      | Сбрасывает флаги `$prepare` и `$optimize`.|
| `realize(string $sql, array $params = [], string $method = 'execute', array $args = []): mixed` | Выполняет SQL или отдаёт строку в зависимости от `$prepare`.|
| `quote(mixed $value): mixed`| Экранирует значение через `DatabaseHelper::quote()`.|
| `quoteIdentifier(string $identifier): string`| Кавычит идентификатор, если он не попал в `$reservedIdentifiers`. |
| `protectIdentifiers(array\|string $item, bool $protect = true): array\|string` | Применяет `quoteIdentifier()` рекурсивно. |


## Мини‑пример наследника

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

### Использование

```php
<?php
$db = new Database('sqlite::memory:');

$sql = (new RawSqlBuilder($db))
    ->raw('SELECT 1 as result')
    ->setPrepare(true) // только получить SQL
    ->__toString();

echo $sql; // SELECT 1 as result
```

---

## Важные моменты

* Используйте `setPrepare(true)` в тестах или миграциях «dry‑run», чтобы увидеть SQL, не меняя базу.

[Вернуться к оглавлению](../../../../index.md)
