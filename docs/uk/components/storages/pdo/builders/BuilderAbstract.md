[Повернутись до змісту](../../../../index.md)

[EN](../../../../../en/components/storages/pdo/builders/BuilderAbstract.md) | **UK** | [RU](../../../../../ru/components/storages/pdo/builders/BuilderAbstract.md)
#  BuilderAbstract

`BuilderAbstract` — базовий клас‑утиліта для всіх SQL‑конструкторів (*builders*) у пакеті **Scaleum\Storages\PDO\Builders**. Він НЕ зберігає ім’я таблиці або режими `CREATE / ALTER / DROP`; ця логіка реалізується у конкретних нащадках (наприклад, `SchemaBuilder`, `ColumnBuilder`). Завдання `BuilderAbstract` — надати:

* підключення до бази даних (успадковано від `DatabaseProvider`),
* реєстр адаптерів під різні драйвери (`$adapters`),
* безпечне екранування ідентифікаторів і значень,
* перемикач «тільки сформувати SQL без виконання» (`$prepare`),
* мінімальну оптимізацію або pretty‑print готового запиту.

##  Властивості

| Властивість             | Тип                    | Доступ           | Значення за замовчуванням | Призначення |
| ----------------------- | ---------------------- | ---------------- | ------------------------- | ----------- |
| `$adapters`             | `array<string,string>` | protected static | `[]`                      | Мапа `driverType ➜ клас‑адаптер`. Використовується фабрикою `create()`. |
| `$identifierQuoteLeft`  | `string`               | protected        | `` ` ``                   | Лівий символ лапок ідентифікаторів.                                 |
| `$identifierQuoteRight` | `string`               | protected        | `` ` ``                   | Правий символ лапок ідентифікаторів.                                |
| `$reservedIdentifiers`  | `array<int,string>`    | protected        | `['*']`                   | Імена, які не лапкуються.                                           |
| `$prepare`              | `bool`                 | protected        | `false`                   | Якщо `true`, методи повертають SQL без виконання.                   |
| `$optimize`             | `bool`                 | protected        | `true`                    | Мінімальна оптимізація (стиснення пробілів) готового SQL.           |

##  Публічні методи

| Підпис                                                         | Повертаємий тип | Призначення |
| -------------------------------------------------------------- | --------------- | ----------- |
| `static create(string $driverType, array $args = []): static`  | `static`        | Фабричний метод: повертає адаптер за типом драйвера або кидає виключення. |
| `__construct(?Database $database = null)`                      | —               | Передає з’єднання у батьківський `DatabaseProvider`.                     |
| `getPrepare(): bool`                                           | `bool`          | Поточний стан прапорця «сформувати, але не виконувати».                 |
| `setPrepare(bool $prepare): self`                              | `self`          | Увімкнути/вимкнути режим підготовки запиту.                            |
| `getOptimize(): bool`                                          | `bool`          | Чи увімкнена мінімізація SQL.                                          |
| `setOptimize(bool $optimize): self`                            | `self`          | Перемикає мінімізацію / pretty‑print.                                  |
| `getOptimizedQuery(string $sql): string`                       | `string`        | Видаляє зайві пробіли, табуляції та переноси.                           |
| `getPrettyQuery(string $sql): string`                          | `string`        | Форматує SQL для кращої читабельності.                                 |
| `getUniqueName(array $columns, string $prefix = 'key'): string`| `string`        | Генерує унікальне ім’я індексу/констрейнта (додає хеш).                |
| `__toString(): string`                                         | `string`        | Повертає результат `makeSQL()`.                                        |

###  Утиліти (protected)

| Підпис | Призначення |
| ------- | ----------- |
| `makeSQL(): string`  | **Абстрактний**: нащадок повинен повернути кінцевий SQL. |
| `flush(): self`      | Скидає прапорці `$prepare` і `$optimize`. |
| `realize(string $sql, array $params = [], string $method = 'execute', array $args = []): mixed` | Виконує SQL або повертає рядок залежно від `$prepare`. |
| `quote(mixed $value): mixed`| Екранує значення через `DatabaseHelper::quote()`. |
| `quoteIdentifier(string $identifier): string`| Лапкує ідентифікатор, якщо він не входить у `$reservedIdentifiers`. |
| `protectIdentifiers(array\|string $item, bool $protect = true): array\|string` | Рекурсивно застосовує `quoteIdentifier()`. |


##  Міні‑приклад спадкоємця

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

###  Використання

```php
<?php
$db = new Database('sqlite::memory:');

$sql = (new RawSqlBuilder($db))
    ->raw('SELECT 1 as result')
    ->setPrepare(true) // тільки отримати SQL
    ->__toString();

echo $sql; // SELECT 1 as result
```

---

##  Важливі моменти

* Використовуйте `setPrepare(true)` у тестах або міграціях «dry‑run», щоб побачити SQL, не змінюючи базу.

[Повернутися до змісту](../../../../index.md)
