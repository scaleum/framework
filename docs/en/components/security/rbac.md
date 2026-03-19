[Back to contents](../../index.md)

**EN** | [UK](../../../uk/components/security/rbac.md) | [RU](../../../ru/components/security/rbac.md)
#  Security RBAC

The `Security RBAC` component is responsible for the role-based access model at the level of an arbitrary object (`object_id`).
RBAC rules are aggregated by subject (`user`, `group`, `role`) and checked through the bitmask model `Permission`.

##  Purpose

- Centralized access check by roles/groups/user
- Support for permission checks to a single object by `object_id`
- Lazy loading of RBAC records via `RbacLoaderInterface`
- Session in-memory cache of records and computed masks
- Support for scenarios with multiple subjects in one session
- Preparation of `Subject` via `SubjectHydrator` and membership resolvers
- Support for nested membership structures (groups/roles) without cycles

##  Main Components

| Class/Interface | Purpose |
|:----------------|:--------|
| `Security/Permission` | Set of bitmask permission constants |
| `Security/Subject` | Subject context (`userId`, `groupIds`, `roleIds`) |
| `Security/SubjectType` | Subject record type (`USER`, `GROUP`, `ROLE`) |
| `Security/Contracts/RbacResourceInterface` | Resource contract with `getId(): string` |
| `Security/Contracts/RbacLoaderInterface` | Contract for lazy loading of RBAC records |
| `Security/Contracts/SubjectMembershipLoaderInterface` | Contract for loading direct membership IDs for `(member_type, member_id)` |
| `Security/Contracts/SubjectMembershipHierarchyLoaderInterface` | Contract for loading parent membership IDs |
| `Security/Contracts/SubjectIdsResolverInterface` | Unified contract for resolving IDs (groups/roles) |
| `Security/Services/SubjectMembershipIdsResolver` | Resolver of direct + inherited membership IDs |
| `Security/Services/SubjectHydrator` | In-place filling of `Subject::groupIds/roleIds` |
| `Security/Services/RbacAccessResolver` | Permission checks (`isAllowed`, `isAllowedAny`, `assert...`) |
| `Security/Services/RbacResourceRegistry` | Registry of resource classes and uniqueness check of `getId()` |

##  Preparing Subject via membership (real scenario)

RBAC checks accept an already prepared `Subject`, so in practice there is usually a stage
of preparing `groupIds` and `roleIds` via project membership data.

Typical pipeline:

1. Obtain direct membership IDs for the user (or other subject).
2. Build the hierarchy (parent groups/roles).
3. Normalize the list of IDs (only positive, unique, sorted).
4. Hydrate the result into the current `Subject`.

###  Minimal tables for groups

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

###  Real data (user 321, default group 743, 3 levels)

```sql
INSERT INTO user_group_memberships (user_id, group_id) VALUES
(321, 743),   -- default group
(321, 900);   -- additional group

INSERT INTO groups (group_id, parent_group_id) VALUES
(743, 800),
(800, 900),
(900, 1000),
(1000, NULL);
```

Effective group set for `321`: `743, 800, 900, 1000`.

###  Loader contracts for this scenario

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

        // CTE is formed by a separate builder and materialized SQL via rows().
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

        // The main query is built by another builder.
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

Usually, the first option (step-by-step traversal) is sufficient. The CTE option is convenient when
you need to get all ancestors with a single SQL query.

###  Subject hydration before RBAC check

```php
use Scaleum\Security\Services\SubjectHydrator;
use Scaleum\Security\Services\SubjectMembershipIdsResolver;
use Scaleum\Security\Subject;

$subject = new Subject(321);

$groupResolver = new SubjectMembershipIdsResolver(
    new PdoGroupMembershipLoader($database),
    new PdoGroupHierarchyLoader($database)
    // Alternative: new PdoGroupHierarchyLoaderCte($database)
);

$hydrator = new SubjectHydrator();
$hydrator->hydrateGroupIdsForUser($subject, $groupResolver, [743]);

// After hydration: [743, 800, 900, 1000]
```

###  Variant with a Single SQL (CTE via QueryBuilder)

If you need to get only actually existing ids with hierarchy in one query,
you can build the SQL entirely through QueryBuilder and execute it via the Database API:

```php

// QueryBuilder unionAll — via callback; CTE materializes in rows().
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
// $ids: only real ids from groups, for example [743, 800, 900, 1000]
```

This approach is useful when you need to strictly filter out "dangling" seed/direct ids,
which no longer exist in the target `groups` table.

##  RBAC Entry Structure

Basic storage structure:

- `object_id`
- `subject_type`
- `subject_id`
- `permissions`

Recommended SQL variant:

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

If UUID is used in the project, it is usually applied for `object_id`. `subject_id` typically refers to the `INT` PK of user/group/role tables.
To protect against duplicates on the combination `object_id + subject_type + subject_id`, application-level validation at the service layer can be used.

##  Permissions (`Permission`)

`Permission` can be used as is or extended in the project for domain-specific rights.
The basic constant contract is available via `Security/Contracts/PermissionInterface`.

Check in ALL mode:

```php
($mask & $permission) === $permission
```

Check in ANY mode:

```php
($mask & $permission) !== 0
```

Equivalent helper methods:

```php
Permission::has($mask, $permission);      // ALL
Permission::hasAny($mask, $permission);   // ANY
Permission::label(Permission::READ);      // Read
Permission::labels($mask);                // [bit => label, ...]
Permission::all();                        // Full mask from the current rights registry
```

Bit limitation:

- By default, a soft-limit of `31` bits is used (indexes `0..30`)
- Why not `32`: in signed `INT` the highest (32nd) bit is the sign bit,
  so it is usually not used for bitmask rights.
- If necessary (and with suitable runtime/storage), the limit can be raised to `63`:
- For `63` bits, store the mask in `BIGINT` (usually signed `BIGINT`) and
    use a 64-bit PHP runtime.

```php
Permission::setMaxBits(63);
```

For `Permission` descendants, it is recommended to use `YourPermission::all()`,
rather than relying on `BASE_ALL`, if the set of project-specific bits may change.

`RbacAccessResolver` internally aggregates (OR) all matched subject entries:

```php
$effectiveMask = userPerms | groupPerms | rolePerms;
```

##  Resource Contract (`RbacResourceInterface`)

```php
interface RbacResourceInterface
{
    public static function getSupportedPermissions(): array;
    public static function getDescription(): ?string;
    public static function getId(): string;
    public static function getName(): string;
}
```

Resource example:

```php
final class DocumentRbacResource implements RbacResourceInterface
{
    public static function getSupportedPermissions(): array
    {
        return [Permission::READ, Permission::WRITE, Permission::DELETE];
    }

    public static function getDescription(): ?string
    {
        return 'Documents';
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

##  Where to get resource and policy in RBAC

Briefly:

- `RbacAccessResolver` receives not the resource class, but a string `objectId`.
- `RbacResourceInterface` and `RbacResourceRegistry` are responsible for the resource catalog (id, name, description, supported bits),
  but do not compute access themselves.
- The "policy" in RBAC is usually implemented in the application layer as a mapping rule:
  `action -> permission bit` and `domain object -> objectId`.
- `objectId` can be any stable resource identifier accepted in the project:
    a string slug (`document`), a composite key (`document:123`), a UUID (`d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1`), etc.
    The specific format is determined by the project's architectural decision.

So the data source is as follows:

1. `objectId` comes from your domain object/context (for example, `document` or `document:123`).
2. `permission` is determined from the use-case action (for example, `update -> Permission::WRITE`).
3. `RbacAccessResolver` checks if the required bitmask exists for the given `objectId` and `Subject`.

###  Practical policy class template

```php
use Scaleum\Security\Contracts\RbacResourceInterface;
use Scaleum\Security\Permission;
use Scaleum\Stdlib\Exceptions\EInvalidArgumentException;

final class DocumentRbacPolicy
{
    public static function resourceTypeId(): string
    {
        // Usually matches RbacResourceInterface::getId().
        return DocumentRbacResource::getId(); // 'document'
    }

    public static function objectIdForRecord(int $documentId): string
    {
        // Per-object RBAC (fine granularity).
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

###  How this fits in the use-case

```php
$objectId = DocumentRbacPolicy::objectIdForRecord($documentId);
$permission = DocumentRbacPolicy::permissionForAction('update');

$rbacResolver->assertAllowed($objectId, $subject, $permission);
```

Important: the `objectId` format must be consistent throughout the project:

- where you write entries to `rbac_entries.object_id`
- where you load them via `RbacLoaderInterface`
- where you check access via `RbacAccessResolver`

If you need to check "by resource type", use `objectId = 'document'`.
If you need to check "by specific record", use `objectId = 'document:{id}'`.

##  Loader contract (`RbacLoaderInterface`)

```php
interface RbacLoaderInterface
{
    /**
     * @return array<int, array{subject_type:int,subject_id:int,permissions:int}>
     */
    public function load(string $objectId): array;
}
```

The loader is called by the resolver only on the first access to `object_id` (lazy load), caches are used afterwards.

##  Resource Registry (`RbacResourceRegistry`)

`RbacResourceRegistry` is used for centralized registration of resource classes and early validation:

- the class must implement `RbacResourceInterface`
- `getId()` must not be empty
- `getId()` must be unique across the entire set of resources

Example:

```php
$registry = new RbacResourceRegistry();
$registry->registerMany([
    DocumentRbacResource::class,
    ReportRbacResource::class,
]);

// Alternative: registration from associative data (e.g., from DB/config)
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
// Resources that exist in the legacy registry but are no longer declared in the current code.
```

Visual example "before -> after -> difference":

```php
// Before (legacy snapshot)
$legacyRegistry = new RbacResourceRegistry();
$legacyRegistry->registerDefinitions([
        ['id' => 'document', 'name' => 'Document', 'permissions' => [Permission::READ, Permission::WRITE]],
        ['id' => 'report', 'name' => 'Report', 'permissions' => [Permission::READ]],
        ['id' => 'archive', 'name' => 'Archive', 'permissions' => [Permission::READ]],
]);

// After (current snapshot)
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

Interpretation:

- `invoice` - new resource, appeared in the current set.
- `archive` - outdated resource, remains only in the legacy set.
- `document` and `report` are present in both sets.

Example of class mismatch (same `id`, but different implementation):

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

This case is useful to check during refactoring when the `resource_id` is preserved,
but the class and/or behavior of the resource have already changed.

##  Access Check (`RbacAccessResolver`)

Key methods:

- `isAllowed(...)` — all requested bits are required
- `isAllowedAny(...)` — at least one bit is sufficient
- `assertAllowed(...)` — throws exception on denial
- `assertAllowedAny(...)` — throws exception on denial in ANY mode
- `seed(...)` — manual loading of records into cache
- `clear(...)` — cache reset

Important feature: the resolver does not store the "current user".
`Subject` is always passed as a parameter, so it is safe to check different subjects within one session.

##  Example of full cycle (DB -> Loader -> Resolver -> Access Check)

###  1. Table Migration

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

###  2. Data Population

```sql
-- object_id = d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1
INSERT INTO rbac_entries (object_id, subject_type, subject_id, permissions) VALUES
('d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1', 1, 1001, 1),  -- USER 1001: READ
('d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1', 2, 20,   2),  -- GROUP 20: WRITE
('d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1', 3, 7,    4);  -- ROLE 7: DELETE
```

###  3. Implementation of Loader from Table

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

###  4. Initialization and Access Check

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

###  5. Scenario of One Session with Multiple Subjects

```php
$userA = new Subject(1001, [20], [7]);
$userB = new Subject(2002, [30], []);
$batchProcess = new Subject(9000, [99], [1]); // back-process as a separate user

$canA = $resolver->isAllowedAny('document', $userA, Permission::READ | Permission::WRITE);
$canB = $resolver->isAllowed('document', $userB, Permission::READ);
$canBatch = $resolver->isAllowed('document', $batchProcess, Permission::DELETE);
```

The resolver correctly handles all checks because it caches masks by the pair:

- `object_id`
- `subjectKey` (derived from `userId`, `groupIds`, `roleIds`)

##  Example of a Full Cycle Without DB (manual seed)

```php
$resolver = new RbacAccessResolver();

$resolver->seed('invoice:2026:157', [
    ['subject_type' => SubjectType::USER, 'subject_id' => 1001, 'permissions' => Permission::READ],
    ['subject_type' => SubjectType::GROUP, 'subject_id' => 20, 'permissions' => Permission::WRITE],
]);

$subject = new Subject(1001, [20], []);
$allowed = $resolver->isAllowed('invoice:2026:157', $subject, Permission::READ | Permission::WRITE);
```

##  Full Rights Update Cycle (Admin Panel)

Typical scenario: an operator edits the list of RBAC rights for one `object_id`.
A reliable way is to replace the set of records within a transaction.

###  1. Overwriting the Set of Rights in a Transaction

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

Here `3` = `READ | WRITE`.

###  2. Resolver Cache Invalidation

After modifying records, it is important to clear the cache for the specific object:

```php
$resolver->clear('d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1');
```

Otherwise, the current process will use previously computed masks until the resolver is fully reset.

###  3. Re-check

```php
$subject = new Subject(1001, [20], [7]);

$resolver->assertAllowed('document', $subject, Permission::READ | Permission::WRITE);
```

##  Partial Rights Update

When you need to change rights only for some subjects (without full set overwrite),
it is convenient to use update + insert within a transaction.

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

Deleting a specific entry:

```sql
DELETE FROM rbac_entries
WHERE object_id = 'd9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1'
    AND subject_type = 3
    AND subject_id = 7;
```

After any partial modification, also clear the cache:

```php
$resolver->clear('d9d7f2f6-2bf6-4f0d-a56d-e8e2a4d6f5a1');
```

##  Responsibility boundary

- `RbacLoaderInterface` — read-only RBAC entries from the data source
- `RbacAccessResolver` — only access calculation/checking and caching
- `RbacResourceInterface` — only provides `object_id`
- DDL/migrations/seed data — project infrastructure layer

##  Minimum to start

- [ ] RBAC table created: `object_id`, `subject_type`, `subject_id`, `permissions`
- [ ] Simple PK (`id`) and indexes on `object_id/subject_type/subject_id` used
- [ ] `RbacLoaderInterface` implemented (for lazy load) or `seed(...)` configured
- [ ] Resource implements `RbacResourceInterface`, checks performed via `RbacAccessResolver`
- [ ] Critical operations use `assertAllowed`/`assertAllowedAny`; when rules change, call `clear($objectId)`

[Back to contents](../../index.md)



