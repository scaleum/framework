[Вернуться к оглавлению](../../../../../index.md)

# QueryBuilderInterface

`QueryBuilderInterface` описывает контракт SQL-конструктора для работы с `SELECT`, `INSERT`, `UPDATE`, `DELETE`, CTE (`WITH`), объединениями (`UNION`) и условиями фильтрации в PDO-слое Scaleum.

Интерфейс находится в пространстве имен `Scaleum\Storages\PDO\Builders\Contracts` и задает флюентный API: большинство методов возвращают `self` для chain-вызовов.

## Основные методы

| Подпись | Возвращаемый тип | Назначение |
| ------- | ---------------- | ---------- |
| `select(array\|string $select = '*', bool $quoting = true): self` | `self` | Формирует список выбираемых полей. |
| `from(array\|string $from): self` | `self` | Указывает источник данных (`FROM`). |
| `insert(?string $table = null, array $set = [], bool $replaceIfExists = false): mixed` | `mixed` | Выполняет `INSERT` (или `REPLACE`, если поддержано и включено). |
| `update(?string $table = null, array $set = [], array\|string $where = null, ?string $whereKey = null, ?int $limit = null): mixed` | `mixed` | Выполняет `UPDATE` с условиями и ограничением строк. |
| `delete(array\|string $table = null, array\|string $where = null, ?int $limit = null): mixed` | `mixed` | Выполняет `DELETE` с необязательными условиями. |
| `truncate(?string $table = null): mixed` | `mixed` | Очищает таблицу (`TRUNCATE`). |

## WHERE и HAVING

| Подпись | Возвращаемый тип | Назначение |
| ------- | ---------------- | ---------- |
| `where(array\|string $field, mixed $value = null, bool $quoting = true): self` | `self` | Добавляет условие в `WHERE` (AND). |
| `orWhere(array\|string $field, mixed $value = null, bool $quoting = true): self` | `self` | Добавляет условие в `WHERE` (OR). |
| `whereIn(string $field, array $values): self` | `self` | Условие `IN`. |
| `whereNotIn(string $field, array $values): self` | `self` | Условие `NOT IN`. |
| `whereBetween(string $field, array $range): self` | `self` | Условие `BETWEEN`. |
| `whereNotBetween(string $field, array $range): self` | `self` | Условие `NOT BETWEEN`. |
| `whereNull(string $field): self` | `self` | Условие `IS NULL`. |
| `whereNotNull(string $field): self` | `self` | Условие `IS NOT NULL`. |
| `orWhereIn(string $field, array $values): self` | `self` | `OR ... IN`. |
| `orWhereNotIn(string $field, array $values): self` | `self` | `OR ... NOT IN`. |
| `orWhereBetween(string $field, array $range): self` | `self` | `OR ... BETWEEN`. |
| `orWhereNotBetween(string $field, array $range): self` | `self` | `OR ... NOT BETWEEN`. |
| `orWhereNull(string $field): self` | `self` | `OR ... IS NULL`. |
| `orWhereNotNull(string $field): self` | `self` | `OR ... IS NOT NULL`. |
| `having(array\|string $field, mixed $value = null, bool $quoting = true): self` | `self` | Добавляет условие в `HAVING` (AND). |
| `orHaving(array\|string $field, mixed $value, bool $quoting = true): self` | `self` | Добавляет условие в `HAVING` (OR). |
| `whereWrap(): self` | `self` | Открывает логическую группу в `WHERE` (`(`). |
| `whereWrapOr(): self` | `self` | Открывает `OR`-группу в `WHERE`. |
| `whereWrapEnd(): self` | `self` | Закрывает группу в `WHERE` (`)`). |
| `havingWrap(): self` | `self` | Открывает логическую группу в `HAVING`. |
| `havingWrapOr(): self` | `self` | Открывает `OR`-группу в `HAVING`. |
| `havingWrapEnd(): self` | `self` | Закрывает группу в `HAVING`. |

## LIKE-условия

| Подпись | Возвращаемый тип | Назначение |
| ------- | ---------------- | ---------- |
| `like(string $field, ?string $match = null, string $side = 'both'): mixed` | `mixed` | Добавляет `LIKE` (обычно `%value%`, в зависимости от `$side`). |
| `notLike(string $field, ?string $match = null, string $side = 'both'): self` | `self` | Добавляет `NOT LIKE`. |
| `orLike(string $field, ?string $match = null, string $side = 'both'): self` | `self` | Добавляет `OR LIKE`. |
| `orNotLike(string $field, ?string $match = null, string $side = 'both'): self` | `self` | Добавляет `OR NOT LIKE`. |

## JOIN, сортировка и агрегация

| Подпись | Возвращаемый тип | Назначение |
| ------- | ---------------- | ---------- |
| `join(string $table, string $rule, ?string $type = null): self` | `self` | Универсальный `JOIN` с типом соединения. |
| `joinInner(string $table, string $rule): self` | `self` | `INNER JOIN`. |
| `joinLeft(string $table, string $rule): self` | `self` | `LEFT JOIN`. |
| `joinRight(string $table, string $rule): self` | `self` | `RIGHT JOIN`. |
| `joinOuter(string $table, string $rule): self` | `self` | `OUTER JOIN`. |
| `groupBy(array\|string $field): self` | `self` | Группировка (`GROUP BY`). |
| `orderBy(array\|string $field, array\|string $direction = 'ASC'): self` | `self` | Сортировка (`ORDER BY`). |
| `limit(int $value): self` | `self` | Ограничивает число строк (`LIMIT`). |
| `offset(int $offset): self` | `self` | Смещение выборки (`OFFSET`). |
| `modifiers(array\|string $modifiers): self` | `self` | SQL-модификаторы запроса (например, `DISTINCT`). |

## CTE, UNION и настройка выполнения

| Подпись | Возвращаемый тип | Назначение |
| ------- | ---------------- | ---------- |
| `with(string $alias, string $sql, array $columns = []): self` | `self` | Добавляет CTE `WITH alias AS (...)`. |
| `withRecursive(string $alias, string $sql, array $columns = []): self` | `self` | Добавляет рекурсивный CTE (`WITH RECURSIVE`). |
| `union(callable $callback): self` | `self` | Объединяет текущий запрос с другим (`UNION`). |
| `unionAll(callable $callback): self` | `self` | Объединяет запросы без удаления дублей (`UNION ALL`). |
| `prepare(bool $value = false): self` | `self` | Включает/выключает режим подготовки SQL без немедленного выполнения. |
| `optimize(bool $value = false): self` | `self` | Переключает оптимизацию/форматирование SQL. |
| `flush(): self` | `self` | Сбрасывает внутреннее состояние билдера. |
| `execute(string $sql, array $params = [], string $method = 'execute', array $args = []): mixed` | `mixed` | Выполняет произвольный SQL через выбранный метод драйвера. |

## Данные и выборка результата

| Подпись | Возвращаемый тип | Назначение |
| ------- | ---------------- | ---------- |
| `set(array\|string $field, mixed $value = null, bool $quoting = true, bool $isBatch = false): self` | `self` | Устанавливает данные для `INSERT`/`UPDATE`. |
| `setAsBatch(array $field, mixed $value = null, bool $quoting = true): self` | `self` | Пакетная установка данных. |
| `whereKey(string $key): self` | `self` | Задает ключ, используемый в некоторых методах обновления/фильтрации. |
| `rows(array $args = []): mixed` | `mixed` | Возвращает набор строк результата. |
| `row(array $args = []): mixed` | `mixed` | Возвращает одну строку результата. |
| `rowColumn(array $args = []): mixed` | `mixed` | Возвращает значение одной колонки/скаляр. |

## Пример использования

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

## Практические рекомендации

* Для сложной логики фильтрации комбинируйте `whereWrap()`/`whereWrapEnd()` и `havingWrap()`/`havingWrapEnd()`.
* Для генерации SQL без выполнения включайте `prepare(true)`.
* Для CTE-сценариев (`WITH`) сначала определяйте подзапросы через `with()`/`withRecursive()`, затем собирайте основной `select()`.

[Вернуться к оглавлению](../../../../../index.md)
