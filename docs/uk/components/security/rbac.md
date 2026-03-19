[Повернутися до змісту](../../index.md)

[EN](../../../en/components/security/rbac.md) | **UK** | [RU](../../../ru/components/security/rbac.md)
#  Security RBAC

Компонент `Security RBAC` відповідає за рольову модель доступу на рівні довільного об’єкта (`object_id`).
RBAC-правила агрегуються за суб’єктом (`user`, `group`, `role`) і перевіряються через bitmask-модель `Permission`.

##  Призначення

- Централізована перевірка доступу за ролями/групами/користувачем
- Підтримка перевірки прав до одного об’єкта за `object_id`
- Лінива завантаження RBAC-записів через `RbacLoaderInterface`
- Сесійний in-memory кеш записів і обчислених масок
- Підтримка сценарію з кількома суб’єктами в одній сесії
- Підготовка `Subject` через `SubjectHydrator` і membership-резолвери
- Підтримка вкладених membership-структур (групи/ролі) без циклів

##  Основні компоненти

| Клас/Інтерфейс | Призначення |
|:----------------|:-----------|
| `Security/Permission` | Набір bitmask-констант дозволів |
| `Security/Subject` | Контекст суб’єкта (`userId`, `groupIds`, `roleIds`) |
| `Security/SubjectType` | Тип суб’єкта запису (`USER`, `GROUP`, `ROLE`) |
| `Security/Contracts/RbacResourceInterface` | Контракт ресурсу з `getId(): string` |
| `Security/Contracts/RbacLoaderInterface` | Контракт lazy-завантаження RBAC-записів |
| `Security/Contracts/SubjectMembershipLoaderInterface` | Контракт завантаження прямих membership-id для `(member_type, member_id)` |
| `Security/Contracts/SubjectMembershipHierarchyLoaderInterface` | Контракт завантаження батьківських membership-id |
| `Security/Contracts/SubjectIdsResolverInterface` | Уніфікований контракт резолву ID (групи/ролі) |
| `Security/Services/SubjectMembershipIdsResolver` | Резолв прямих + успадкованих membership-id |
| `Security/Services/SubjectHydrator` | In-place заповнення `Subject::groupIds/roleIds` |
| `Security/Services/RbacAccessResolver` | Перевірка прав (`isAllowed`, `isAllowedAny`, `assert...`) |
| `Security/Services/RbacResourceRegistry` | Реєстр ресурс-класів і перевірка унікальності `getId()` |

##  Підготовка Subject через membership (реальний сценарій)

RBAC-перевірка приймає вже готовий `Subject`, тому на практиці зазвичай є етап
підготовки `groupIds` і `roleIds` через membership-дані проєкту.

Типовий pipeline:

1. Отримати прямі membership-id для користувача (або іншого суб’єкта).
2. Побудувати ієрархію (батьківські групи/ролі).
3. Нормалізувати список ID (тільки додатні, унікальні, відсортовані).
4. Гідрувати підсумок у поточний `Subject`.

###  Мінімальні таблиці для груп

```sql
CREATE TABLE user_group_memberships (
    user_id INT NOT NULL,
    group_id INT NOT NULL,
    PRIMARY KEY (user_id, group_id)
);

CREATE TABLE groups (
    group_id INT NOT NULL PRIMARY KEY,
    parent_group_id INT NULL
);
```

###  Реальні дані (user 321, default group 743, 3 рівні)

```sql
INSERT INTO user_group_memberships (user_id, group_id) VALUES
(321, 743),   -- default group
(321, 900);   -- додаткова група

INSERT INTO groups (group_id, parent_group_id) VALUES
(743, 800),
(800, 900),
(900, 1000),
(1000, NULL);
```

Ефективний набір груп для `321`: `743, 800, 900, 1000`.

###  Контракти завантажувачів для цього сценарію

```php
use Scaleum\Security\Contracts\SubjectMembershipHierarchyLoaderInterface;
use Scaleum\Security\Contracts\SubjectMembershipLoaderInterface;
use Scaleum\Security\SubjectType;
use Scaleum\Storages\PDO\Database;

final class PdoGroupMembershipLoader implements SubjectMembershipLoaderInterface
{
    public function __construct(private Database $database)
    {
    }

    public function loadDirectMembershipIds(int $memberType, int $memberId): array
    {
        if ($memberType !== SubjectType::USER) {
            return [];
        }

        $rows = $this->database
            ->getQueryBuilder()
            ->select(['group_id'])
            ->from('user_group_memberships')
            ->where('user_id', $memberId)
            ->rows();

        if (! is_array($rows)) {
            return [];
        }

        return array_values(array_map(
            static fn(array $row): int => (int) $row['group_id'],
            $rows
        ));
    }
}

final class PdoGroupHierarchyLoader implements SubjectMembershipHierarchyLoaderInterface
{
    public function __construct(private Database $database)
    {
    }

    public function loadParentMembershipIds(int $membershipId): array
    {
        $visited = [];
        $queue = [$membershipId];
        $parents = [];

        while (! empty($queue)) {
            $currentId = (int) array_shift($queue);
            if ($currentId <= 0 || isset($visited[$currentId])) {
                continue;
            }

            $visited[$currentId] = true;

            $rows = $this->database
                ->getQueryBuilder()
                ->select(['parent_group_id'])
                ->from('groups')
                ->where('group_id', $currentId)
                ->rows();

            if (! is_array($rows)) {
                continue;
            }

            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $parentId = (int) ($row['parent_group_id'] ?? 0);
                if ($parentId <= 0 || isset($parents[$parentId])) {
                    continue;
                }

                $parents[$parentId] = true;
                $queue[] = $parentId;
            }
        }

        $result = array_map('intval', array_keys($parents));
        sort($result);

        return $result;
    }
}

final class PdoGroupHierarchyLoaderCte implements SubjectMembershipHierarchyLoaderInterface
{
    public function __construct(private Database $database)
    {
    }

    public function loadParentMembershipIds(int $membershipId): array
    {
        $membershipId = (int) $membershipId;
        if ($membershipId <= 0) {
            return [];
        }

        // CTE формуємо окремим білдером і матеріалізуємо SQL через rows().
        $cteSql = $this->database
            ->getQueryBuilder()
            ->prepare(true)
            ->select(['group_id', 'parent_group_id'])
            ->from('groups')
            ->where('group_id', $membershipId)
            ->unionAll(function ($q) {
                $q
                    ->prepare(true)
                    ->select(['g.group_id', 'g.parent_group_id'])
                    ->from('groups g')
                    ->joinInner('group_tree gt', 'gt.parent_group_id = g.group_id');
            })
            ->rows();

        // Основний запит будуємо іншим білдером.
        $sql = $this->database
            ->getQueryBuilder()
            ->prepare(true)
            ->withRecursive('group_tree', $cteSql, ['group_id', 'parent_group_id'])
            ->select('DISTINCT group_id', false)
            ->from('group_tree')
            ->where('group_id <>', $membershipId, false)
            ->orderBy('group_id')
            ->rows();

        $rows = $this->database
            ->setQuery($sql)
            ->fetchAll();

        $ids = array_map('intval', array_column(is_array($rows) ? $rows : [], 'group_id'));
        $ids = array_values(array_filter($ids, static fn(int $id): bool => $id > 0));
        sort($ids);

        return array_values(array_unique($ids));
    }
}
```

Зазвичай достатньо першого варіанту (покроковий обхід). CTE-варіант зручний, коли
потрібно отримати всіх предків одним SQL-запитом.

###  Гідрація Subject перед RBAC-перевіркою

```php
use Scaleum\Security\Services\SubjectHydrator;
use Scaleum\Security\Services\SubjectMembershipIdsResolver;
use Scaleum\Security\Subject;

$subject = new Subject(321);

$groupResolver = new SubjectMembershipIdsResolver(
    new PdoGroupMembershipLoader($database),
    new PdoGroupHierarchyLoader($database)
    // Альтернатива: new PdoGroupHierarchyLoaderCte($database)
);

$hydrator = new SubjectHydrator();
$hydrator->hydrateGroupIdsForUser($subject, $groupResolver, [743]);

// Після гідрації: [743, 800, 900, 1000]
```

###  Варіант з єдиним SQL (CTE через QueryBuilder)

Якщо потрібно одним запитом отримати лише реально існуючі id з ієрархією,
можна зібрати SQL повністю через QueryBuilder і виконати через Database API:

```php

// QueryBuilder unionAll — через callback; CTE матеріалізується в rows().
$cteSql = $database
    ->getQueryBuilder()
    ->prepare(true)
    ->select(['group_id'])
    ->from('user_group_memberships')
    ->where('user_id', 321)
    ->unionAll(function ($q) {
        $q
            ->prepare(true)
            ->select(['g.group_id'])
            ->from('groups g')
            ->joinInner('resolved r', 'g.parent_group_id = r.group_id');
    })
    ->rows();

$sql = $database
    ->getQueryBuilder()
    ->prepare(true)
    ->withRecursive('resolved', $cteSql, ['group_id'])
    ->select('DISTINCT g.group_id', false)
    ->from('groups g')
    ->joinInner('resolved r', 'r.group_id = g.group_id')
    ->orderBy('g.group_id')
    ->rows();

$rows = $database
    ->setQuery($sql)
    ->fetchAll();

$ids = array_map('intval', array_column(is_array($rows) ? $rows : [], 'group_id'));
// $ids: лише реальні id з groups, наприклад [743, 800, 900, 1000]
```

Такий підхід корисний, коли потрібно жорстко відфільтрувати «підвісні» seed/direct id,
яких вже немає в цільовій таблиці `groups`.

##  Структура RBAC-записів

Базова структура зберігання:

- `object_id`
- `subject_type`
- `subject_id`
- `permissions`

Рекомендований SQL-варіант:

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

Якщо в проєкті використовується UUID, його зазвичай застосовують для `object_id`. `subject_id` як правило посилається на `INT` PK таблиць користувачів/груп/ролей.
Для захисту від дублікатів за зв’язкою `object_id + subject_type + subject_id` можна використовувати прикладну валідацію на рівні сервісу.

##  Дозволи (`Permission`)

`Permission` може використовуватися як є або розширюватися в проєкті для domain-specific прав.
Базовий контракт констант доступний через `Security/Contracts/PermissionInterface`.

Перевірка в режимі ALL:

```php
($mask & $permission) === $permission
```

Перевірка в режимі ANY:

```php
($mask & $permission) !== 0
```

Еквівалентні helper-методи:

```php
Permission::has($mask, $permission);      // ALL
Permission::hasAny($mask, $permission);   // ANY
Permission::label(Permission::READ);      // Read
Permission::labels($mask);                // [bit => label, ...]
Permission::all();                        // Повна маска з поточного реєстру прав
```

Обмеження по бітах:

- За замовчуванням використовується soft-limit `31` біт (індекси `0..30`)
- Чому не `32`: у signed `INT` старший (32-й) біт — це біт знаку,
  тому його зазвичай не використовують для bitmask-прав.
- За потреби (і підходящому runtime/storage) ліміт можна підняти до `63`:
- Для `63` біт зберігайте маску в `BIGINT` (зазвичай signed `BIGINT`) і
    використовуйте 64-bit PHP runtime.

```php
Permission::setMaxBits(63);
```

Для нащадків `Permission` рекомендується використовувати `YourPermission::all()`,
а не покладатися на `BASE_ALL`, якщо набір project-specific бітів може змінюватися.

`RbacAccessResolver` всередині агрегує (OR) всі співпавші записи суб’єкта:

```php
$effectiveMask = userPerms | groupPerms | rolePerms;
```

##  Контракт ресурсу (`RbacResourceInterface`)

```php
interface RbacResourceInterface
{
    public static function getSupportedPermissions(): array;
    public static function getDescription(): ?string;
    public static function getId(): string;
    public static function getName(): string;
}
```

Приклад ресурсу:

```php
final class DocumentRbacResource implements RbacResourceInterface
{
    public static function getSupportedPermissions(): array
    {
        return [Permission::READ, Permission::WRITE, Permission::DELETE];
    }

    public static function getDescription(): ?string
    {
        return 'Документи';
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

##  Звідки брати resource і policy в RBAC

Коротко:

- В `RbacAccessResolver` передається не клас ресурсу, а рядковий `objectId`.
- `RbacResourceInterface` і `RbacResourceRegistry` відповідають за каталог ресурсів (id, ім'я, опис, підтримувані біти),
  але не обчислюють доступ самі.
- "Політика" в RBAC зазвичай реалізується в прикладному шарі як правило мапінгу:
  `action -> permission bit` і `domain object -> objectId`.
- `objectId` може бути будь-яким стабільним ідентифікатором ресурсу, прийнятим у проєкті:
    рядковий slug (`document`), складний ключ (`document:123`), UUID (`d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1`) тощо.
    Конкретний формат визначається приватним архітектурним рішенням проєкту.

Тобто джерело даних таке:

1. `objectId` приходить з вашого доменного об'єкта/контексту (наприклад, `document` або `document:123`).
2. `permission` визначається з дії use-case (наприклад, `update -> Permission::WRITE`).
3. `RbacAccessResolver` перевіряє, чи є потрібний bitmask для даного `objectId` і `Subject`.

###  Практичний шаблон policy-класу

```php
use Scaleum\Security\Contracts\RbacResourceInterface;
use Scaleum\Security\Permission;
use Scaleum\Stdlib\Exceptions\EInvalidArgumentException;

final class DocumentRbacPolicy
{
    public static function resourceTypeId(): string
    {
        // Зазвичай співпадає з RbacResourceInterface::getId().
        return DocumentRbacResource::getId(); // 'document'
    }

    public static function objectIdForRecord(int $documentId): string
    {
        // Пер-об'єктний RBAC (тонка гранулярність).
        return self::resourceTypeId() . ':' . $documentId;
    }

    public static function permissionForAction(string $action): int
    {
        return match ($action) {
            'view', 'list' => Permission::READ,
            'create', 'update' => Permission::WRITE,
            'delete' => Permission::DELETE,
            default => throw new EInvalidArgumentException('Unknown RBAC action: ' . $action),
        };
    }
}
```

###  Як це стикується в use-case

```php
$objectId = DocumentRbacPolicy::objectIdForRecord($documentId);
$permission = DocumentRbacPolicy::permissionForAction('update');

$rbacResolver->assertAllowed($objectId, $subject, $permission);
```

Важливо: формат `objectId` має бути єдиним у всіх місцях проєкту:

- там, де пишете записи в `rbac_entries.object_id`
- там, де завантажуєте їх через `RbacLoaderInterface`
- там, де перевіряєте доступ через `RbacAccessResolver`

Якщо потрібна перевірка "на тип ресурсу", використовуйте `objectId = 'document'`.
Якщо потрібна перевірка "на конкретний запис", використовуйте `objectId = 'document:{id}'`.

##  Контракт завантажувача (`RbacLoaderInterface`)

```php
interface RbacLoaderInterface
{
    /**
     * @return array<int, array{subject_type:int,subject_id:int,permissions:int}>
     */
    public function load(string $objectId): array;
}
```

Завантажувач викликається резолвером лише при першому зверненні до `object_id` (lazy load), далі використовуються кеші.

##  Реєстр ресурсів (`RbacResourceRegistry`)

`RbacResourceRegistry` потрібен для централізованої реєстрації ресурс-класів та ранньої валідації:

- клас повинен реалізовувати `RbacResourceInterface`
- `getId()` не повинен бути порожнім
- `getId()` повинен бути унікальним у всьому наборі ресурсів

Приклад:

```php
$registry = new RbacResourceRegistry();
$registry->registerMany([
    DocumentRbacResource::class,
    ReportRbacResource::class,
]);

// Альтернатива: реєстрація з асоціативних даних (наприклад, з БД/конфігу)
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
// Ресурси, які є в legacy-реєстрі, але більше не оголошені в поточному коді.
```

Наглядний приклад "було -> стало -> різниця":

```php
// Було (legacy snapshot)
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

Інтерпретація:

- `invoice` - новий ресурс, з'явився в поточному наборі.
- `archive` - застарілий ресурс, залишився лише в legacy-наборі.
- `document` і `report` присутні в обох наборах.

Приклад розбіжності по класу (той самий `id`, але різна реалізація):

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

Такий кейс корисно перевіряти при рефакторингу, коли `resource_id` збережено,
але клас і/або поведінка ресурсу вже змінилися.

##  Перевірка доступу (`RbacAccessResolver`)

Ключові методи:

- `isAllowed(...)` — потрібні всі запитані біти
- `isAllowedAny(...)` — достатньо хоча б одного біта
- `assertAllowed(...)` — виключення при відмові
- `assertAllowedAny(...)` — виключення при відмові в режимі ANY
- `seed(...)` — ручне підвантаження записів у кеш
- `clear(...)` — скидання кешу

Важлива особливість: резолвер не зберігає "поточний користувач".
`Subject` завжди передається параметром, тому в одній сесії безпечно перевіряти різних суб’єктів.

##  Приклад повного циклу (DB -> Loader -> Resolver -> Access Check)

###  1. Міграція таблиці

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

###  2. Заповнення даними

```sql
-- object_id = d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1
INSERT INTO rbac_entries (object_id, subject_type, subject_id, permissions) VALUES
('d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1', 1, 1001, 1),  -- USER 1001: READ
('d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1', 2, 20,   2),  -- GROUP 20: WRITE
('d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1', 3, 7,    4);  -- ROLE 7: DELETE
```

###  3. Реалізація завантажувача з таблиці

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

###  4. Ініціалізація та перевірка доступу

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

###  5. Сценарій однієї сесії з кількома суб’єктами

```php
$userA = new Subject(1001, [20], [7]);
$userB = new Subject(2002, [30], []);
$batchProcess = new Subject(9000, [99], [1]); // бек-процес як окремий user

$canA = $resolver->isAllowedAny('document', $userA, Permission::READ | Permission::WRITE);
$canB = $resolver->isAllowed('document', $userB, Permission::READ);
$canBatch = $resolver->isAllowed('document', $batchProcess, Permission::DELETE);
```

Резолвер коректно обслуговує всі перевірки, тому що кешує маски за парою:

- `object_id`
- `subjectKey` (виводиться з `userId`, `groupIds`, `roleIds`)

##  Приклад повного циклу без БД (ручний seed)

```php
$resolver = new RbacAccessResolver();

$resolver->seed('invoice:2026:157', [
    ['subject_type' => SubjectType::USER, 'subject_id' => 1001, 'permissions' => Permission::READ],
    ['subject_type' => SubjectType::GROUP, 'subject_id' => 20, 'permissions' => Permission::WRITE],
]);

$subject = new Subject(1001, [20], []);
$allowed = $resolver->isAllowed('invoice:2026:157', $subject, Permission::READ | Permission::WRITE);
```

##  Повний цикл оновлення прав (адмін-панель)

Типовий сценарій: оператор редагує список RBAC-прав для одного `object_id`.
Надійний шлях — виконувати заміну набору записів у транзакції.

###  1. Перезапис набору прав у транзакції

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

Тут `3` = `READ | WRITE`.

###  2. Інвалідація кешу резолвера

Після зміни записів важливо очистити кеш для конкретного об’єкта:

```php
$resolver->clear('d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1');
```

Інакше поточний процес буде використовувати раніше обчислені маски до повного скидання резолвера.

###  3. Повторна перевірка

```php
$subject = new Subject(1001, [20], [7]);

$resolver->assertAllowed('document', $subject, Permission::READ | Permission::WRITE);
```

##  Часткове оновлення прав

Коли потрібно змінити права лише для частини суб’єктів (без повного перезапису набору),
зручно використовувати update + insert у транзакції.

###  MySQL

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

###  PostgreSQL

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

Видалення точкового запису:

```sql
DELETE FROM rbac_entries
WHERE object_id = 'd9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1'
    AND subject_type = 3
    AND subject_id = 7;
```

Після будь-якої часткової модифікації також очищуйте кеш:

```php
$resolver->clear('d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1');
```

##  Межа відповідальності

- `RbacLoaderInterface` — лише читання RBAC-записів із джерела даних
- `RbacAccessResolver` — лише обчислення/перевірка доступу та кешування
- `RbacResourceInterface` — лише постачання `object_id`
- DDL/міграції/seed-дані — інфраструктурний шар проєкту

##  Мінімум для запуску

- [ ] Створена таблиця RBAC: `object_id`, `subject_type`, `subject_id`, `permissions`
- [ ] Використовується простий PK (`id`) та індекси за `object_id/subject_type/subject_id`
- [ ] Реалізований `RbacLoaderInterface` (для lazy load) або налаштований `seed(...)`
- [ ] Ресурс реалізує `RbacResourceInterface`, перевірки виконуються через `RbacAccessResolver`
- [ ] Для критичних операцій використовуються `assertAllowed`/`assertAllowedAny`; при зміні правил викликається `clear($objectId)`

[Повернутися до змісту](../../index.md)



