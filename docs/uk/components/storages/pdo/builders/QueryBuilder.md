[Вернутися до змісту](../../../../index.md)

[EN](../../../../../en/components/storages/pdo/builders/QueryBuilder.md) | **UK** | [RU](../../../../../ru/components/storages/pdo/builders/QueryBuilder.md)
#  QueryBuilder

`QueryBuilder` - основний SQL-конструктор у `Scaleum\Storages\PDO\Builders` для складання та виконання `SELECT`, `INSERT`, `UPDATE`, `DELETE`, `TRUNCATE`, CTE (`WITH`) та `UNION`-запитів.

Клас наслідує [BuilderAbstract](./BuilderAbstract.md) і реалізує [QueryBuilderInterface](./contracts/QueryBuilderInterface.md), тому підтримує флюентний стиль (`chain`-виклики) та режими `prepare()`/`optimize()`.

##  Підтримувані драйвери

| Драйвер | Адаптер |
| ------- | ------- |
| `mysql` | `Adapters\MySQL\Query` |
| `pgsql` | `Adapters\PostgreSQL\Query` |
| `sqlite` | `Adapters\SQLite\Query` |
| `sqlsrv` | `Adapters\SQLServer\Query` |
| `mssql` | `Adapters\SQLServer\Query` |

##  Ключові властивості

| Властивість | Тип | Призначення |
| -------- | --- | ---------- |
| `$from` | `array` | Джерела для `FROM`. |
| `$select` | `array` | Список вибраних полів і прапорців quoting. |
| `$join` | `array` | Накопичені `JOIN`-частини. |
| `$where` | `array` | Умови блоку `WHERE`. |
| `$having` | `array` | Умови блоку `HAVING`. |
| `$groupBy` | `array` | Поля групування. |
| `$orderBy` | `array` | Сортування. |
| `$limit` / `$offset` | `int` | Обмеження та зсув вибірки. |
| `$set` | `array` | Дані для `INSERT`/`UPDATE`, включно з batch-режимом. |
| `$whereKey` | `?string` | Ключ для пакетного `UPDATE` (`makeUpdateBatch`). |
| `$ctes` | `array` | CTE-описи для `WITH`/`WITH RECURSIVE`. |
| `$unions` | `array` | Підзапити для `UNION`/`UNION ALL`. |
| `$cachedState` | `array` | Знімок стану билдера (використовується при batch-`delete`). |

##  Основні публічні методи

| Підпис | Повертаємий тип | Призначення |
| ------- | ---------------- | ---------- |
| `select(array\|string $select = '*', bool $quoting = true): self` | `self` | Визначає набір вибраних полів. |
| `from(array\|string $from): self` | `self` | Встановлює таблиці/аліаси в `FROM`. |
| `join(string $table, string $rule, ?string $type = null): self` | `self` | Додає з'єднання таблиць (`JOIN`). |
| `where(array\|string $field, mixed $value = null, bool $quoting = true): self` | `self` | Додає умову в `WHERE` (AND). |
| `orWhere(array\|string $field, mixed $value = null, bool $quoting = true): self` | `self` | Додає умову в `WHERE` (OR). |
| `groupBy(array\|string $field): self` | `self` | Додає `GROUP BY`. |
| `having(array\|string $field, mixed $value = null, bool $quoting = true): self` | `self` | Додає `HAVING`. |
| `orderBy(array\|string $field, array\|string $direction = 'ASC'): self` | `self` | Додає `ORDER BY` з валідацією `ASC/DESC`. |
| `limit(int $value, ?int $offset = null): self` | `self` | Обмежує кількість рядків і за потреби встановлює `offset`. |
| `offset(int $offset): self` | `self` | Встановлює зсув. |
| `insert(?string $table = null, array $set = [], bool $replaceIfExists = false): mixed` | `mixed` | Генерує та виконує `INSERT` або `REPLACE`. |
| `update(array\|string $tableName = null, array $set = [], array\|string $where = null, ?string $whereKey = null, ?int $limit = null): mixed` | `mixed` | Генерує та виконує `UPDATE`, включно з batch-варіантом. |
| `delete(array\|string $table = null, array\|string $where = null, ?int $limit = null): mixed` | `mixed` | Генерує та виконує `DELETE` (потребує `WHERE`). |
| `truncate(string $table = null): mixed` | `mixed` | Генерує та виконує `TRUNCATE`. |
| `rows(array $args = []): mixed` | `mixed` | Виконує `SELECT` і повертає набір рядків (`fetchAll`). |
| `row(array $args = []): mixed` | `mixed` | Виконує `SELECT` і повертає один запис (`fetch`). |
| `rowColumn(array $args = []): mixed` | `mixed` | Виконує `SELECT` і повертає одну колонку (`fetchColumn`). |
| `union(callable\|string\|self $query): self` | `self` | Додає `UNION` з callback, готового SQL або іншого builder-інстанса. |
| `unionAll(callable\|string\|self $query): self` | `self` | Додає `UNION ALL` з callback, готового SQL або іншого builder-інстанса. |

##  Розширені можливості

| Група | Методи | Що робить |
| ------ | ------ | ---------- |
| Дужкові групи | `whereWrap()`, `whereWrapOr()`, `whereWrapEnd()`, `havingWrap()`, `havingWrapOr()`, `havingWrapEnd()` | Дозволяє явно керувати логічними групами умов. |
| LIKE-умови | `like()`, `notLike()`, `orLike()`, `orNotLike()` | Формує `LIKE`/`NOT LIKE` з режимами `both`, `before`, `after`, `none`. |
| Набори умов | `whereIn()`, `whereNotIn()`, `orWhereIn()`, `orWhereNotIn()` | Формує `IN`/`NOT IN`. |
| Діапазони та NULL | `whereBetween()`, `orWhereBetween()`, `whereNotBetween()`, `orWhereNotBetween()`, `whereNull()`, `orWhereNull()`, `whereNotNull()`, `orWhereNotNull()` | Підтримує `BETWEEN` та перевірки `IS [NOT] NULL`. |
| CTE | `with()`, `withRecursive()` | Додає CTE-вирази на початок запиту (`WITH ...`). |
| Об’єднання | `union()`, `unionAll()` | Об’єднує запити через callback, готовий SQL-текст або інший builder. |
| Виконання та стан | `execute()`, `prepare()`, `optimize()`, `flush()` | Виконує довільний SQL, включає режими підготовки та очищає стан билдера. |

##  Внутрішня логіка

| Метод | Призначення |
| ----- | ---------- |
| `makeSelect()` | Збирає повний `SELECT` з `WITH`, `JOIN`, `WHERE`, `GROUP BY`, `HAVING`, `ORDER BY`, `LIMIT/OFFSET`, `UNION`. |
| `makeInsert()`, `makeInsertBatch()` | Збірка одиночного та пакетного `INSERT/REPLACE`. |
| `makeUpdate()`, `makeUpdateBatch()` | Збірка одиночного та пакетного `UPDATE` (через `CASE WHEN`). |
| `makeDelete()` | Збірка `DELETE` з підтримкою `ORDER BY` та `LIMIT`. |
| `makeWhere*()`, `makeHaving()`, `makeLike()` | Формування умов з урахуванням вкладених дужок. |
| `makeWith()` | Збірка `WITH`/`WITH RECURSIVE` з `$ctes`. |
| `cache()` / `restore()` | Тимчасове збереження та відновлення стану билдера. |

##  Приклад використання

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

##  Приклади з WITH

###  WITH (звичайний CTE)

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

###  WITH RECURSIVE

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

###  WITH RECURSIVE через unionAll() builder API

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

##  Приклади з UNION

###  UNION через callback

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

###  UNION через готовий SQL-текст

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

###  UNION ALL через інший builder-інстанс

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

У всіх режимах `union()/unionAll()` матеріалізують SQL підзапиту одразу (snapshot),
тому подальші зміни вихідного callback/builder не змінюють вже доданий `UNION`.

###  Отримати SQL без виконання

```php
$sql = (new QueryBuilder($db))
    ->with('u', 'SELECT id, email FROM users')
    ->select('*')
    ->from('u')
    ->prepare(true)
    ->rows();

// $sql містить рядок SELECT з CTE, без звернення до БД.
```

##  Пов’язані документи

* [BuilderAbstract](./BuilderAbstract.md)
* [ColumnBuilder](./ColumnBuilder.md)
* [QueryBuilderInterface](./contracts/QueryBuilderInterface.md)
* [DatabaseProvider](../DatabaseProvider.md)
* [DatabaseProviderInterface](../DatabaseProviderInterface.md)

##  Практичні рекомендації

* Перед `update()` у batch-режимі обов’язково задавайте `whereKey()`, інакше буде викинуто виключення.
* Для безпечного `delete()` завжди додавайте `where()`: клас спеціально блокує `DELETE` без умови.
* Якщо потрібно отримати SQL без виконання, використовуйте `prepare(true)` і потім `rows()`/`row()` або `execute()`.

[Повернутися до змісту](../../../../index.md)