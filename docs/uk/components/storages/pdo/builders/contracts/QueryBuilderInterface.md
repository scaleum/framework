[Повернутися до змісту](../../../../../index.md)

[EN](../../../../../../en/components/storages/pdo/builders/contracts/QueryBuilderInterface.md) | **UK** | [RU](../../../../../../ru/components/storages/pdo/builders/contracts/QueryBuilderInterface.md)
#  QueryBuilderInterface

`QueryBuilderInterface` описує контракт SQL-конструктора для роботи з `SELECT`, `INSERT`, `UPDATE`, `DELETE`, CTE (`WITH`), об’єднаннями (`UNION`) та умовами фільтрації в PDO-шарі Scaleum.

Інтерфейс знаходиться в просторі імен `Scaleum\Storages\PDO\Builders\Contracts` і задає флюїдний API: більшість методів повертають `self` для chain-викликів.

##  Основні методи

| Підпис | Повертаємий тип | Призначення |
| ------- | ---------------- | ---------- |
| `select(array\|string $select = '*', bool $quoting = true): self` | `self` | Формує список вибираних полів. |
| `from(array\|string $from): self` | `self` | Вказує джерело даних (`FROM`). |
| `insert(?string $table = null, array $set = [], bool $replaceIfExists = false): mixed` | `mixed` | Виконує `INSERT` (або `REPLACE`, якщо підтримується і увімкнено). |
| `update(?string $table = null, array $set = [], array\|string $where = null, ?string $whereKey = null, ?int $limit = null): mixed` | `mixed` | Виконує `UPDATE` з умовами та обмеженням рядків. |
| `delete(array\|string $table = null, array\|string $where = null, ?int $limit = null): mixed` | `mixed` | Виконує `DELETE` з необов’язковими умовами. |
| `truncate(?string $table = null): mixed` | `mixed` | Очищує таблицю (`TRUNCATE`). |

##  WHERE та HAVING

| Підпис | Повертаємий тип | Призначення |
| ------- | ---------------- | ---------- |
| `where(array\|string $field, mixed $value = null, bool $quoting = true): self` | `self` | Додає умову в `WHERE` (AND). |
| `orWhere(array\|string $field, mixed $value = null, bool $quoting = true): self` | `self` | Додає умову в `WHERE` (OR). |
| `whereIn(string $field, array $values): self` | `self` | Умова `IN`. |
| `whereNotIn(string $field, array $values): self` | `self` | Умова `NOT IN`. |
| `whereBetween(string $field, array $range): self` | `self` | Умова `BETWEEN`. |
| `whereNotBetween(string $field, array $range): self` | `self` | Умова `NOT BETWEEN`. |
| `whereNull(string $field): self` | `self` | Умова `IS NULL`. |
| `whereNotNull(string $field): self` | `self` | Умова `IS NOT NULL`. |
| `orWhereIn(string $field, array $values): self` | `self` | `OR ... IN`. |
| `orWhereNotIn(string $field, array $values): self` | `self` | `OR ... NOT IN`. |
| `orWhereBetween(string $field, array $range): self` | `self` | `OR ... BETWEEN`. |
| `orWhereNotBetween(string $field, array $range): self` | `self` | `OR ... NOT BETWEEN`. |
| `orWhereNull(string $field): self` | `self` | `OR ... IS NULL`. |
| `orWhereNotNull(string $field): self` | `self` | `OR ... IS NOT NULL`. |
| `having(array\|string $field, mixed $value = null, bool $quoting = true): self` | `self` | Додає умову в `HAVING` (AND). |
| `orHaving(array\|string $field, mixed $value, bool $quoting = true): self` | `self` | Додає умову в `HAVING` (OR). |
| `whereWrap(): self` | `self` | Відкриває логічну групу в `WHERE` (`(`). |
| `whereWrapOr(): self` | `self` | Відкриває `OR`-групу в `WHERE`. |
| `whereWrapEnd(): self` | `self` | Закриває групу в `WHERE` (`)`). |
| `havingWrap(): self` | `self` | Відкриває логічну групу в `HAVING`. |
| `havingWrapOr(): self` | `self` | Відкриває `OR`-групу в `HAVING`. |
| `havingWrapEnd(): self` | `self` | Закриває групу в `HAVING`. |

##  LIKE-умови

| Підпис | Повертаємий тип | Призначення |
| ------- | ---------------- | ---------- |
| `like(string $field, ?string $match = null, string $side = 'both'): mixed` | `mixed` | Додає `LIKE` (зазвичай `%value%`, залежно від `$side`). |
| `notLike(string $field, ?string $match = null, string $side = 'both'): self` | `self` | Додає `NOT LIKE`. |
| `orLike(string $field, ?string $match = null, string $side = 'both'): self` | `self` | Додає `OR LIKE`. |
| `orNotLike(string $field, ?string $match = null, string $side = 'both'): self` | `self` | Додає `OR NOT LIKE`. |

##  JOIN, сортування та агрегація

| Підпис | Повертаємий тип | Призначення |
| ------- | ---------------- | ---------- |
| `join(string $table, string $rule, ?string $type = null): self` | `self` | Універсальний `JOIN` з типом з’єднання. |
| `joinInner(string $table, string $rule): self` | `self` | `INNER JOIN`. |
| `joinLeft(string $table, string $rule): self` | `self` | `LEFT JOIN`. |
| `joinRight(string $table, string $rule): self` | `self` | `RIGHT JOIN`. |
| `joinOuter(string $table, string $rule): self` | `self` | `OUTER JOIN`. |
| `groupBy(array\|string $field): self` | `self` | Групування (`GROUP BY`). |
| `orderBy(array\|string $field, array\|string $direction = 'ASC'): self` | `self` | Сортування (`ORDER BY`). |
| `limit(int $value): self` | `self` | Обмежує кількість рядків (`LIMIT`). |
| `offset(int $offset): self` | `self` | Зсув вибірки (`OFFSET`). |
| `modifiers(array\|string $modifiers): self` | `self` | SQL-модифікатори запиту (наприклад, `DISTINCT`). |

##  CTE, UNION та налаштування виконання

| Підпис | Повертаємий тип | Призначення |
| ------- | ---------------- | ---------- |
| `with(string $alias, string $sql, array $columns = []): self` | `self` | Додає CTE `WITH alias AS (...)`. |
| `withRecursive(string $alias, string $sql, array $columns = []): self` | `self` | Додає рекурсивний CTE (`WITH RECURSIVE`). |
| `union(callable\|string\|self $query): self` | `self` | Об’єднує поточний запит з іншим (`UNION`) через callback, готовий SQL або builder. |
| `unionAll(callable\|string\|self $query): self` | `self` | Об’єднує запити без видалення дублікатів (`UNION ALL`) через callback, готовий SQL або builder. |
| `prepare(bool $value = false): self` | `self` | Увімкнення/вимкнення режиму підготовки SQL без негайного виконання. |
| `optimize(bool $value = false): self` | `self` | Перемикає оптимізацію/форматування SQL. |
| `flush(): self` | `self` | Скидає внутрішній стан билдера. |
| `execute(string $sql, array $params = [], string $method = 'execute', array $args = []): mixed` | `mixed` | Виконує довільний SQL через вибраний метод драйвера. |

##  Дані та вибірка результату

| Підпис | Повертаємий тип | Призначення |
| ------- | ---------------- | ---------- |
| `set(array\|string $field, mixed $value = null, bool $quoting = true, bool $isBatch = false): self` | `self` | Встановлює дані для `INSERT`/`UPDATE`. |
| `setAsBatch(array $field, mixed $value = null, bool $quoting = true): self` | `self` | Пакетне встановлення даних. |
| `whereKey(string $key): self` | `self` | Визначає ключ, що використовується в деяких методах оновлення/фільтрації. |
| `rows(array $args = []): mixed` | `mixed` | Повертає набір рядків результату. |
| `row(array $args = []): mixed` | `mixed` | Повертає один рядок результату. |
| `rowColumn(array $args = []): mixed` | `mixed` | Повертає значення однієї колонки/скаляр. |

##  Приклад використання

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

##  Практичні рекомендації

* Для складної логіки фільтрації комбінуйте `whereWrap()`/`whereWrapEnd()` та `havingWrap()`/`havingWrapEnd()`.
* Для генерації SQL без виконання вмикайте `prepare(true)`.
* Для CTE-сценаріїв (`WITH`) спочатку визначайте підзапити через `with()`/`withRecursive()`, потім збирайте основний `select()`.
* Для `union()`/`unionAll()` можна передавати callback, SQL-рядок або інший builder; підзапит фіксується одразу (snapshot), тому пізні зміни джерела не впливають на вже доданий `UNION`.

[Повернутися до змісту](../../../../../index.md)
