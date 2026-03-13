[Вернуться к оглавлению](../../index.md)
# Security RBAC

Компонент `Security RBAC` отвечает за ролевую модель доступа на уровне произвольного объекта (`object_id`).
RBAC-правила агрегируются по субъекту (`user`, `group`, `role`) и проверяются через bitmask-модель `Permission`.

## Назначение

- Централизованная проверка доступа по ролям/группам/пользователю
- Поддержка проверки прав к одному объекту по `object_id`
- Ленивая загрузка RBAC-записей через `RbacLoaderInterface`
- Сессионный in-memory cache записей и вычисленных масок
- Поддержка сценария с несколькими субъектами в одной сессии

## Основные компоненты

| Класс/Интерфейс | Назначение |
|:----------------|:-----------|
| `Security/Permission` | Набор bitmask-констант разрешений |
| `Security/Subject` | Контекст субъекта (`userId`, `groupIds`, `roleIds`) |
| `Security/SubjectType` | Тип субъекта записи (`USER`, `GROUP`, `ROLE`) |
| `Security/Contracts/RbacResourceInterface` | Контракт ресурса с `getId(): string` |
| `Security/Contracts/RbacLoaderInterface` | Контракт lazy-загрузки RBAC-записей |
| `Security/Services/RbacAccessResolver` | Проверка прав (`isAllowed`, `isAllowedAny`, `assert...`) |
| `Security/Services/RbacResourceRegistry` | Реестр ресурс-классов и проверка уникальности `getId()` |

## Структура RBAC-записей

Базовая структура хранения:

- `object_id`
- `subject_type`
- `subject_id`
- `permissions`

Рекомендуемый SQL-вариант:

```sql
CREATE TABLE rbac_entries (
    id BIGINT NOT NULL AUTO_INCREMENT,
    object_id VARCHAR(64) NOT NULL,
    subject_type SMALLINT NOT NULL,
    subject_id INT NOT NULL,
    permissions INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    INDEX idx_rbac_object_id (object_id),
    INDEX idx_rbac_subject_type (subject_type),
    INDEX idx_rbac_subject_id (subject_id)
);
```

Если в проекте используется UUID, его обычно применяют для `object_id`. `subject_id` как правило ссылается на `INT` PK таблиц пользователей/групп/ролей.
Для защиты от дубликатов по связке `object_id + subject_type + subject_id` можно использовать прикладную валидацию на уровне сервиса.

## Разрешения (`Permission`)

Проверка в режиме ALL:

```php
($mask & $permission) === $permission
```

Проверка в режиме ANY:

```php
($mask & $permission) !== 0
```

`RbacAccessResolver` внутри агрегирует (OR) все совпавшие записи субъекта:

```php
$effectiveMask = userPerms | groupPerms | rolePerms;
```

## Контракт ресурса (`RbacResourceInterface`)

```php
interface RbacResourceInterface
{
    public static function getSupportedPermissions(): array;
    public static function getDescription(): ?string;
    public static function getId(): string;
    public static function getName(): string;
}
```

Пример ресурса:

```php
final class DocumentRbacResource implements RbacResourceInterface
{
    public static function getSupportedPermissions(): array
    {
        return [Permission::READ, Permission::WRITE, Permission::DELETE];
    }

    public static function getDescription(): ?string
    {
        return 'Документы';
    }

    public static function getId(): string
    {
        return 'document';
    }

    public static function getName(): string
    {
        return 'Document';
    }
}
```

## Контракт загрузчика (`RbacLoaderInterface`)

```php
interface RbacLoaderInterface
{
    /**
     * @return array<int, array{subject_type:int,subject_id:int,permissions:int}>
     */
    public function load(string $objectId): array;
}
```

Загрузчик вызывается резолвером только при первом обращении к `object_id` (lazy load), далее используются кэши.

## Реестр ресурсов (`RbacResourceRegistry`)

`RbacResourceRegistry` нужен для централизованной регистрации ресурс-классов и ранней валидации:

- класс должен реализовывать `RbacResourceInterface`
- `getId()` не должен быть пустым
- `getId()` должен быть уникальным во всем наборе ресурсов

Пример:

```php
$registry = new RbacResourceRegistry();
$registry->registerMany([
    DocumentRbacResource::class,
    ReportRbacResource::class,
]);

// Альтернатива: регистрация из ассоциативных данных (например, из БД/конфига)
$registry->registerDefinitions([
    [
        'id' => 'invoice',
        'name' => 'Invoice',
        'description' => 'Invoice resource',
        'permissions' => [Permission::READ, Permission::WRITE],
    ],
]);

$meta = $registry->describe('document');
// ['id' => 'document', 'name' => 'Document', 'description' => '...', 'permissions' => [...]]

$diff = $registry->compareWith($legacyRegistry);
// [
//   'onlyInCurrent' => [<definition>, ...],
//   'onlyInOther' => [<definition>, ...],
//   'outdatedInOther' => [<definition>, ...],
//   'classMismatches' => [['current' => <definition>, 'other' => <definition>], ...],
// ]

$outdated = $registry->getOutdatedInOther($legacyRegistry);
// Ресурсы, которые есть в legacy-реестре, но больше не объявлены в текущем коде.
```

Наглядный пример "было -> стало -> разница":

```php
// Было (legacy snapshot)
$legacyRegistry = new RbacResourceRegistry();
$legacyRegistry->registerDefinitions([
        ['id' => 'document', 'name' => 'Document', 'permissions' => [Permission::READ, Permission::WRITE]],
        ['id' => 'report', 'name' => 'Report', 'permissions' => [Permission::READ]],
        ['id' => 'archive', 'name' => 'Archive', 'permissions' => [Permission::READ]],
]);

// Стало (current snapshot)
$currentRegistry = new RbacResourceRegistry();
$currentRegistry->registerDefinitions([
        ['id' => 'document', 'name' => 'Documents', 'permissions' => [Permission::READ, Permission::WRITE, Permission::DELETE]],
        ['id' => 'report', 'name' => 'Report', 'permissions' => [Permission::READ]],
        ['id' => 'invoice', 'name' => 'Invoice', 'permissions' => [Permission::READ]],
]);

$diff = $currentRegistry->compareWith($legacyRegistry);

/*
$diff = [
    'onlyInCurrent' => [
        [
            'id' => 'invoice',
            'name' => 'Invoice',
            'description' => null,
            'permissions' => [Permission::READ],
            'class' => null,
        ],
    ],
    'onlyInOther' => [
        [
            'id' => 'archive',
            'name' => 'Archive',
            'description' => null,
            'permissions' => [Permission::READ],
            'class' => null,
        ],
    ],
    'outdatedInOther' => [
        [
            'id' => 'archive',
            'name' => 'Archive',
            'description' => null,
            'permissions' => [Permission::READ],
            'class' => null,
        ],
    ],
    'classMismatches' => [],
];
*/
```

Интерпретация:

- `invoice` - новый ресурс, появился в текущем наборе.
- `archive` - устаревший ресурс, остался только в legacy-наборе.
- `document` и `report` присутствуют в обоих наборах.

Пример расхождения по классу (один и тот же `id`, но разная реализация):

```php
$current = new RbacResourceRegistry();
$current->register(DocumentRbacResource::class);      // getId() === 'document'

$legacy = new RbacResourceRegistry();
$legacy->register(DocumentLegacyRbacResource::class); // getId() === 'document'

$diff = $current->compareWith($legacy);

/*
$diff['classMismatches'] = [
    [
        'current' => [
            'id' => 'document',
            'name' => 'Document',
            'description' => '...',
            'permissions' => [...],
            'class' => DocumentRbacResource::class,
        ],
        'other' => [
            'id' => 'document',
            'name' => 'Document Legacy',
            'description' => '...',
            'permissions' => [...],
            'class' => DocumentLegacyRbacResource::class,
        ],
    ],
];
*/
```

Такой кейс полезно проверять при рефакторинге, когда `resource_id` сохранён,
но класс и/или поведение ресурса уже изменились.

## Проверка доступа (`RbacAccessResolver`)

Ключевые методы:

- `isAllowed(...)` — нужны все запрошенные биты
- `isAllowedAny(...)` — достаточно хотя бы одного бита
- `assertAllowed(...)` — исключение при отказе
- `assertAllowedAny(...)` — исключение при отказе в режиме ANY
- `seed(...)` — ручная подгрузка записей в кэш
- `clear(...)` — сброс кэша

Важная особенность: резолвер не хранит "текущего пользователя".
`Subject` всегда передаётся параметром, поэтому в одной сессии безопасно проверять разные субъекты.

## Пример полного цикла (DB -> Loader -> Resolver -> Access Check)

### 1. Миграция таблицы

```sql
CREATE TABLE rbac_entries (
    id BIGINT NOT NULL AUTO_INCREMENT,
    object_id CHAR(36) NOT NULL,
    subject_type SMALLINT NOT NULL,
    subject_id INT NOT NULL,
    permissions INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    INDEX idx_rbac_object_id (object_id),
    INDEX idx_rbac_subject_type (subject_type),
    INDEX idx_rbac_subject_id (subject_id)
);
```

### 2. Наполнение данными

```sql
-- object_id = d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1
INSERT INTO rbac_entries (object_id, subject_type, subject_id, permissions) VALUES
('d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1', 1, 1001, 1),  -- USER 1001: READ
('d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1', 2, 20,   2),  -- GROUP 20: WRITE
('d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1', 3, 7,    4);  -- ROLE 7: DELETE
```

### 3. Реализация загрузчика из таблицы

```php
use Scaleum\Security\Contracts\RbacLoaderInterface;
use Scaleum\Storages\PDO\Database;

final class PdoRbacLoader implements RbacLoaderInterface
{
    public function __construct(private Database $database)
    {
    }

    public function load(string $objectId): array
    {
        $rows = $this->database
            ->getQueryBuilder()
            ->select(['subject_type', 'subject_id', 'permissions'])
            ->from('rbac_entries')
            ->where('object_id', $objectId)
            ->rows();

        if (! is_array($rows)) {
            return [];
        }

        return array_map(
            static fn(array $row): array => [
                'subject_type' => (int) $row['subject_type'],
                'subject_id' => (int) $row['subject_id'],
                'permissions' => (int) $row['permissions'],
            ],
            $rows
        );
    }
}
```

### 4. Инициализация и проверка доступа

```php
use Scaleum\Security\Permission;
use Scaleum\Security\Services\RbacAccessResolver;
use Scaleum\Security\Subject;

$loader = new PdoRbacLoader($database);
$resolver = new RbacAccessResolver($loader);

$subject = new Subject(1001, [20], [7]);

$canWrite = $resolver->isAllowed('document', $subject, Permission::WRITE);
$canReadOrDelete = $resolver->isAllowedAny(
    'document',
    $subject,
    Permission::READ | Permission::DELETE
);

$resolver->assertAllowed('document', $subject, Permission::READ);
```

### 5. Сценарий одной сессии с несколькими субъектами

```php
$userA = new Subject(1001, [20], [7]);
$userB = new Subject(2002, [30], []);
$batchProcess = new Subject(9000, [99], [1]); // бэк-процесс как отдельный user

$canA = $resolver->isAllowedAny('document', $userA, Permission::READ | Permission::WRITE);
$canB = $resolver->isAllowed('document', $userB, Permission::READ);
$canBatch = $resolver->isAllowed('document', $batchProcess, Permission::DELETE);
```

Резолвер корректно обслуживает все проверки, потому что кеширует маски по паре:

- `object_id`
- `subjectKey` (derived from `userId`, `groupIds`, `roleIds`)

## Пример полного цикла без БД (ручной seed)

```php
$resolver = new RbacAccessResolver();

$resolver->seed('invoice:2026:157', [
    ['subject_type' => SubjectType::USER, 'subject_id' => 1001, 'permissions' => Permission::READ],
    ['subject_type' => SubjectType::GROUP, 'subject_id' => 20, 'permissions' => Permission::WRITE],
]);

$subject = new Subject(1001, [20], []);
$allowed = $resolver->isAllowed('invoice:2026:157', $subject, Permission::READ | Permission::WRITE);
```

## Полный цикл обновления прав (админ-панель)

Типичный сценарий: оператор редактирует список RBAC-прав для одного `object_id`.
Надежный путь - выполнять замену набора записей в транзакции.

### 1. Перезапись набора прав в транзакции

```sql
BEGIN;

DELETE FROM rbac_entries
WHERE object_id = 'd9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1';

INSERT INTO rbac_entries (object_id, subject_type, subject_id, permissions) VALUES
('d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1', 1, 1001, 3),
('d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1', 2, 20,   1),
('d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1', 3, 7,    4);

COMMIT;
```

Здесь `3` = `READ | WRITE`.

### 2. Инвалидация кэша резолвера

После изменения записей важно очистить кэш для конкретного объекта:

```php
$resolver->clear('d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1');
```

Иначе текущий процесс будет использовать ранее вычисленные маски до полного сброса резолвера.

### 3. Повторная проверка

```php
$subject = new Subject(1001, [20], [7]);

$resolver->assertAllowed('document', $subject, Permission::READ | Permission::WRITE);
```

## Частичное обновление прав

Когда нужно изменить права только для части субъектов (без полной перезаписи набора),
удобно использовать update + insert в транзакции.

### MySQL

```sql
START TRANSACTION;

UPDATE rbac_entries
SET permissions = 1
WHERE object_id = 'd9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1'
    AND subject_type = 1
    AND subject_id = 1001;

INSERT INTO rbac_entries (object_id, subject_type, subject_id, permissions)
SELECT 'd9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1', 1, 1001, 1
WHERE NOT EXISTS (
        SELECT 1
        FROM rbac_entries
        WHERE object_id = 'd9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1'
            AND subject_type = 1
            AND subject_id = 1001
);

COMMIT;
```

### PostgreSQL

```sql
BEGIN;

UPDATE rbac_entries
SET permissions = 3
WHERE object_id = 'd9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1'
    AND subject_type = 2
    AND subject_id = 20;

INSERT INTO rbac_entries (object_id, subject_type, subject_id, permissions)
SELECT 'd9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1', 2, 20, 3
WHERE NOT EXISTS (
        SELECT 1
        FROM rbac_entries
        WHERE object_id = 'd9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1'
            AND subject_type = 2
            AND subject_id = 20
);

COMMIT;
```

Удаление точечной записи:

```sql
DELETE FROM rbac_entries
WHERE object_id = 'd9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1'
    AND subject_type = 3
    AND subject_id = 7;
```

После любой частичной модификации также очищайте кэш:

```php
$resolver->clear('d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1');
```

## Граница ответственности

- `RbacLoaderInterface` — только чтение RBAC-записей из источника данных
- `RbacAccessResolver` — только вычисление/проверка доступа и кэширование
- `RbacResourceInterface` — только поставка `object_id`
- DDL/миграции/seed-данные — инфраструктурный слой проекта

## Минимум для запуска

- [ ] Создана таблица RBAC: `object_id`, `subject_type`, `subject_id`, `permissions`
- [ ] Используется простой PK (`id`) и индексы по `object_id/subject_type/subject_id`
- [ ] Реализован `RbacLoaderInterface` (для lazy load) или настроен `seed(...)`
- [ ] Ресурс реализует `RbacResourceInterface`, проверки выполняются через `RbacAccessResolver`
- [ ] Для критичных операций используются `assertAllowed`/`assertAllowedAny`; при изменении правил вызывается `clear($objectId)`

[Вернуться к оглавлению](../../index.md)



