[Вернуться к оглавлению](../../../../index.md)

# QueryBuilder

`QueryBuilder` - основной SQL-конструктор в `Scaleum\Storages\PDO\Builders` для сборки и выполнения `SELECT`, `INSERT`, `UPDATE`, `DELETE`, `TRUNCATE`, CTE (`WITH`) и `UNION`-запросов.

Класс наследует [BuilderAbstract](./BuilderAbstract.md) и реализует [QueryBuilderInterface](./contracts/QueryBuilderInterface.md), поэтому поддерживает флюентный стиль (`chain`-вызовы) и режимы `prepare()`/`optimize()`.

## Поддерживаемые драйверы

| Драйвер | Адаптер |
| ------- | ------- |
| `mysql` | `Adapters\MySQL\Query` |
| `pgsql` | `Adapters\PostgreSQL\Query` |
| `sqlite` | `Adapters\SQLite\Query` |
| `sqlsrv` | `Adapters\SQLServer\Query` |
| `mssql` | `Adapters\SQLServer\Query` |

## Ключевые свойства

| Свойство | Тип | Назначение |
| -------- | --- | ---------- |
| `$from` | `array` | Источники для `FROM`. |
| `$select` | `array` | Список выбираемых полей и флагов quoting. |
| `$join` | `array` | Накопленные `JOIN`-части. |
| `$where` | `array` | Условия блока `WHERE`. |
| `$having` | `array` | Условия блока `HAVING`. |
| `$groupBy` | `array` | Поля группировки. |
| `$orderBy` | `array` | Сортировка. |
| `$limit` / `$offset` | `int` | Ограничение и смещение выборки. |
| `$set` | `array` | Данные для `INSERT`/`UPDATE`, включая batch-режим. |
| `$whereKey` | `?string` | Ключ для пакетного `UPDATE` (`makeUpdateBatch`). |
| `$ctes` | `array` | CTE-описания для `WITH`/`WITH RECURSIVE`. |
| `$unions` | `array` | Подзапросы для `UNION`/`UNION ALL`. |
| `$cachedState` | `array` | Снимок состояния билдера (используется при batch-`delete`). |

## Основные публичные методы

| Подпись | Возвращаемый тип | Назначение |
| ------- | ---------------- | ---------- |
| `select(array\|string $select = '*', bool $quoting = true): self` | `self` | Определяет набор выбираемых полей. |
| `from(array\|string $from): self` | `self` | Устанавливает таблицы/алиасы в `FROM`. |
| `join(string $table, string $rule, ?string $type = null): self` | `self` | Добавляет соединение таблиц (`JOIN`). |
| `where(array\|string $field, mixed $value = null, bool $quoting = true): self` | `self` | Добавляет условие в `WHERE` (AND). |
| `orWhere(array\|string $field, mixed $value = null, bool $quoting = true): self` | `self` | Добавляет условие в `WHERE` (OR). |
| `groupBy(array\|string $field): self` | `self` | Добавляет `GROUP BY`. |
| `having(array\|string $field, mixed $value = null, bool $quoting = true): self` | `self` | Добавляет `HAVING`. |
| `orderBy(array\|string $field, array\|string $direction = 'ASC'): self` | `self` | Добавляет `ORDER BY` с валидацией `ASC/DESC`. |
| `limit(int $value, ?int $offset = null): self` | `self` | Ограничивает количество строк и при необходимости устанавливает `offset`. |
| `offset(int $offset): self` | `self` | Устанавливает смещение. |
| `insert(?string $table = null, array $set = [], bool $replaceIfExists = false): mixed` | `mixed` | Генерирует и выполняет `INSERT` или `REPLACE`. |
| `update(array\|string $tableName = null, array $set = [], array\|string $where = null, ?string $whereKey = null, ?int $limit = null): mixed` | `mixed` | Генерирует и выполняет `UPDATE`, включая batch-вариант. |
| `delete(array\|string $table = null, array\|string $where = null, ?int $limit = null): mixed` | `mixed` | Генерирует и выполняет `DELETE` (требует `WHERE`). |
| `truncate(string $table = null): mixed` | `mixed` | Генерирует и выполняет `TRUNCATE`. |
| `rows(array $args = []): mixed` | `mixed` | Выполняет `SELECT` и возвращает набор строк (`fetchAll`). |
| `row(array $args = []): mixed` | `mixed` | Выполняет `SELECT` и возвращает одну запись (`fetch`). |
| `rowColumn(array $args = []): mixed` | `mixed` | Выполняет `SELECT` и возвращает одну колонку (`fetchColumn`). |
| `union(callable\|string\|self $query): self` | `self` | Добавляет `UNION` из callback, готового SQL или другого builder-инстанса. |
| `unionAll(callable\|string\|self $query): self` | `self` | Добавляет `UNION ALL` из callback, готового SQL или другого builder-инстанса. |

## Расширенные возможности

| Группа | Методы | Что делает |
| ------ | ------ | ---------- |
| Скобочные группы | `whereWrap()`, `whereWrapOr()`, `whereWrapEnd()`, `havingWrap()`, `havingWrapOr()`, `havingWrapEnd()` | Позволяет явно управлять логическими группами условий. |
| LIKE-условия | `like()`, `notLike()`, `orLike()`, `orNotLike()` | Формирует `LIKE`/`NOT LIKE` с режимами `both`, `before`, `after`, `none`. |
| Наборы условий | `whereIn()`, `whereNotIn()`, `orWhereIn()`, `orWhereNotIn()` | Формирует `IN`/`NOT IN`. |
| Диапазоны и NULL | `whereBetween()`, `orWhereBetween()`, `whereNotBetween()`, `orWhereNotBetween()`, `whereNull()`, `orWhereNull()`, `whereNotNull()`, `orWhereNotNull()` | Поддерживает `BETWEEN` и проверки `IS [NOT] NULL`. |
| CTE | `with()`, `withRecursive()` | Добавляет CTE-выражения в начало запроса (`WITH ...`). |
| Объединение | `union()`, `unionAll()` | Объединяет запросы через callback, готовый SQL-текст или другой builder. |
| Выполнение и состояние | `execute()`, `prepare()`, `optimize()`, `flush()` | Выполняет произвольный SQL, включает режимы подготовки и очищает состояние билдера. |

## Внутренняя логика

| Метод | Назначение |
| ----- | ---------- |
| `makeSelect()` | Собирает полный `SELECT` с `WITH`, `JOIN`, `WHERE`, `GROUP BY`, `HAVING`, `ORDER BY`, `LIMIT/OFFSET`, `UNION`. |
| `makeInsert()`, `makeInsertBatch()` | Сборка одиночного и пакетного `INSERT/REPLACE`. |
| `makeUpdate()`, `makeUpdateBatch()` | Сборка одиночного и пакетного `UPDATE` (через `CASE WHEN`). |
| `makeDelete()` | Сборка `DELETE` с поддержкой `ORDER BY` и `LIMIT`. |
| `makeWhere*()`, `makeHaving()`, `makeLike()` | Формирование условий с учетом вложенных скобок. |
| `makeWith()` | Сборка `WITH`/`WITH RECURSIVE` из `$ctes`. |
| `cache()` / `restore()` | Временное сохранение и восстановление состояния билдера. |

## Пример использования

```php
use Scaleum\Storages\PDO\Builders\QueryBuilder;
use Scaleum\Storages\PDO\Database;

$db = new Database('mysql:host=localhost;dbname=app', 'root', 'secret');

$result = (new QueryBuilder($db))
    ->select(['u.id', 'u.email'])
    ->from('users u')
    ->joinLeft('profiles p', 'p.user_id = u.id')
    ->whereWrap()
        ->where('u.active', 1)
        ->orWhereNull('u.deleted_at')
    ->whereWrapEnd()
    ->orderBy('u.id', 'DESC')
    ->limit(20)
    ->rows();
```

## Примеры с WITH

### WITH (обычный CTE)

```php
use Scaleum\Storages\PDO\Builders\QueryBuilder;
use Scaleum\Storages\PDO\Database;

$db = new Database('mysql:host=localhost;dbname=app', 'root', 'secret');

$result = (new QueryBuilder($db))
    ->with(
        'active_users',
        "SELECT id, email FROM users WHERE active = 1"
    )
    ->select(['a.id', 'a.email'])
    ->from('active_users a')
    ->orderBy('a.id', 'DESC')
    ->rows();
```

### WITH RECURSIVE

```php
use Scaleum\Storages\PDO\Builders\QueryBuilder;
use Scaleum\Storages\PDO\Database;

$db = new Database('pgsql:host=localhost;dbname=app', 'postgres', 'secret');

$tree = (new QueryBuilder($db))
    ->withRecursive(
        'tree',
        "
        SELECT id, parent_id, title, 1 AS level
        FROM categories
        WHERE parent_id IS NULL
        UNION ALL
        SELECT c.id, c.parent_id, c.title, t.level + 1
        FROM categories c
        JOIN tree t ON t.id = c.parent_id
        ",
        ['id', 'parent_id', 'title', 'level']
    )
    ->select(['t.id', 't.title', 't.level'])
    ->from('tree t')
    ->orderBy(['t.level', 't.id'], ['ASC', 'ASC'])
    ->rows();
```

### WITH RECURSIVE через unionAll() builder API

```php
use Scaleum\Storages\PDO\Builders\QueryBuilder;
use Scaleum\Storages\PDO\Database;

$db = new Database('mysql:host=localhost;dbname=app', 'root', 'secret');

$cteSql = (new QueryBuilder($db))
    ->prepare(true)
    ->select(['id', 'parent_id', 'title'])
    ->from('categories')
    ->where('parent_id', null)
    ->unionAll(function ($q) {
        $q->prepare(true)
            ->select(['c.id', 'c.parent_id', 'c.title'])
            ->from('categories c')
            ->joinInner('tree t', 'c.parent_id = t.id');
    })
    ->rows();

$sql = (new QueryBuilder($db))
    ->prepare(true)
    ->withRecursive('tree', $cteSql, ['id', 'parent_id', 'title'])
    ->select(['t.id', 't.title'])
    ->from('tree t')
    ->rows();
```

## Примеры с UNION

### UNION через callback

```php
$sql = (new QueryBuilder($db))
    ->prepare(true)
    ->select(['id', 'email'])
    ->from('users')
    ->where('active', 1)
    ->union(function ($q) {
        $q->prepare(true)
            ->select(['id', 'email'])
            ->from('admins')
            ->where('active', 1);
    })
    ->rows();
```

### UNION через готовый SQL-текст

```php
$unionSql = (new QueryBuilder($db))
    ->prepare(true)
    ->select(['id', 'email'])
    ->from('admins')
    ->where('active', 1)
    ->rows();

$sql = (new QueryBuilder($db))
    ->prepare(true)
    ->select(['id', 'email'])
    ->from('users')
    ->where('active', 1)
    ->union($unionSql)
    ->rows();
```

### UNION ALL через другой builder-инстанс

```php
$unionBuilder = (new QueryBuilder($db))
    ->select(['id', 'email'])
    ->from('admins')
    ->where('active', 1);

$sql = (new QueryBuilder($db))
    ->prepare(true)
    ->select(['id', 'email'])
    ->from('users')
    ->where('active', 1)
    ->unionAll($unionBuilder)
    ->rows();
```

Во всех режимах `union()/unionAll()` материализуют SQL подзапроса сразу (snapshot),
поэтому дальнейшие изменения исходного callback/builder не меняют уже добавленный `UNION`.

### Получить SQL без выполнения

```php
$sql = (new QueryBuilder($db))
    ->with('u', 'SELECT id, email FROM users')
    ->select('*')
    ->from('u')
    ->prepare(true)
    ->rows();

// $sql содержит строку SELECT с CTE, без обращения к БД.
```

## Связанные документы

* [BuilderAbstract](./BuilderAbstract.md)
* [ColumnBuilder](./ColumnBuilder.md)
* [QueryBuilderInterface](./contracts/QueryBuilderInterface.md)
* [DatabaseProvider](../DatabaseProvider.md)
* [DatabaseProviderInterface](../DatabaseProviderInterface.md)

## Практические рекомендации

* Перед `update()` в batch-режиме обязательно задавайте `whereKey()`, иначе будет выброшено исключение.
* Для безопасного `delete()` всегда добавляйте `where()`: класс специально блокирует `DELETE` без условия.
* Если нужно получить SQL без выполнения, используйте `prepare(true)` и затем `rows()`/`row()` или `execute()`.

[Вернуться к оглавлению](../../../../index.md)