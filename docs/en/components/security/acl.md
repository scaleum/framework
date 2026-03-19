[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/security/acl.md) | [RU](../../../ru/components/security/acl.md)
#  Security ACL

The `Security ACL` component is responsible for checking access rights at the level of a specific record (Record ACL).
It complements RBAC and allows setting permissions for the owner, group, and other users.

##  Purpose

- Filtering list/select queries by ACL permissions
- Checking access to a single record (`read/update/delete`)
- Unified bitmask permission model via `Permission`
- Explicit policy when ACL row is missing (`isAllowedWhenAclMissing()`)
- Fail-fast check for the existence of the ACL table

##  Main Components

| Class/Interface | Purpose |
|:----------------|:--------|
| `Security/Permission` | Set of bitmask permission constants |
| `Security/Subject` | Subject context (`userId`, `groupIds`, `roleIds`) |
| `Security/Contracts/AclResourceInterface` | Contract for resource with ACL table and default policy |
| `Security/Contracts/AclQueryApplierInterface` | Contract for filtering queries by ACL |
| `Security/Services/AclAccessQueryApplier` | Applying ACL conditions to list/select queries |
| `Security/Services/AclAccessResolver` | Checking access to a single model |
| `Security/Services/AclTableGuard` | Checking existence of the ACL table |

##  ACL Table Structure

For each master table, a separate ACL table `*_acl` is used.
The relation is made via the `record_id` field.

For strict 1:1 relation, the normal approach is when `record_id` is simultaneously:

- a foreign key to the master table
- the primary key of the ACL table

This guarantees no more than one ACL row per record and simplifies querying.
If the project/ORM requires a separate surrogate key (`id`), it can be added,
while keeping `UNIQUE` + `FOREIGN KEY` on `record_id`.

Recommended structure:

- `record_id`
- `owner_id`
- `group_id`
- `owner_perms`
- `group_perms`
- `other_perms`

Example:

```sql
CREATE TABLE document_acl (
    record_id INT NOT NULL,
    owner_id INT NOT NULL,
    group_id INT NULL,
    owner_perms INT NOT NULL DEFAULT 0,
    group_perms INT NOT NULL DEFAULT 0,
    other_perms INT NOT NULL DEFAULT 0,
    PRIMARY KEY (record_id),
    CONSTRAINT fk_document_acl_record
        FOREIGN KEY (record_id) REFERENCES document(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    INDEX idx_document_acl_owner_id (owner_id),
    INDEX idx_document_acl_group_id (group_id)
);
```

Alternative with separate `id` (if required by the model layer):

```sql
CREATE TABLE document_acl (
    id INT NOT NULL AUTO_INCREMENT,
    record_id INT NOT NULL,
    owner_id INT NOT NULL,
    group_id INT NULL,
    owner_perms INT NOT NULL DEFAULT 0,
    group_perms INT NOT NULL DEFAULT 0,
    other_perms INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_document_acl_record_id (record_id),
    CONSTRAINT fk_document_acl_record
        FOREIGN KEY (record_id) REFERENCES document(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    INDEX idx_document_acl_owner_id (owner_id),
    INDEX idx_document_acl_group_id (group_id)
);
```

##  Permissions (`Permission`)

`Permission` uses bit flags (`READ`, `WRITE`, `DELETE`, etc.) and can be extended in the project via inheritance.
Base constants are also declared in `Security/Contracts/PermissionInterface`.

Checking is done by mask (ALL mode):

```php
($mask & $permission) === $permission
```

For the "at least one of the permissions" scenario (ANY mode):

```php
($mask & $permission) !== 0
```

Helper methods are available in the class for these scenarios:

```php
Permission::has($mask, Permission::READ | Permission::WRITE);
Permission::hasAny($mask, Permission::READ | Permission::WRITE);
Permission::label(Permission::READ);   // Read
Permission::labels($mask);             // [bit => label, ...]
```

Bit limitations:

- By default, a soft-limit of `31` bits is enabled (indexes `0..30`)
- Why not `32`: in signed `INT` the highest (32nd) bit is the sign bit,
  and using it leads to negative mask values.
- If necessary, the limit can be raised to `63` (on 64-bit runtime):
- If using `63` bits, the DB field for the mask should be `BIGINT`
    (usually signed `BIGINT`), and the PHP runtime must be 64-bit.

```php
Permission::setMaxBits(63);
```

Example of extending for project policies:

```php
final class AppPermission extends Permission
{
    public const APPROVE = 1 << 8;
    public const PUBLISH = 1 << 9;

    protected static array $perms = Permission::BASE_LABELS + [
        self::APPROVE => 'Approve',
        self::PUBLISH => 'Publish',
    ];
}

$all = AppPermission::all(); // Actual full mask from registered bits
```

##  Access Subject (`Subject`)

`Subject` contains:

- `userId` — user identifier
- `groupIds` — user groups (participate in Record ACL)
- `roleIds` — user roles (for RBAC and related tasks)

Only `userId` and `groupIds` participate in Record ACL.

##  Resource Contract (`AclResourceInterface`)

The resource provides the name of the ACL table and behavior when the ACL row is missing.

```php
interface AclResourceInterface
{
    public function getAclTable(): string;
    public function getAclData(): ?array;
    public function isAllowedWhenAclMissing(): bool;
}
```

`getAclData()` allows returning an already loaded ACL row (for example, via a `hasOne` relation of the model).
If `null` or an empty array is returned, `AclAccessResolver` will perform a fallback load from the ACL table.

Example model:

```php
final class Document extends ModelAbstract implements AclResourceInterface
{
    public function getAclTable(): string
    {
        return 'document_acl';
    }

    public function getAclData(): ?array
    {
        // For example, if the `acl` relation is already loaded in the model.
        // Format: owner_id/group_id/owner_perms/group_perms/other_perms.
        return isset($this->acl) ? $this->acl->toArray() : null;
    }

    public function isAllowedWhenAclMissing(): bool
    {
        return false;
    }
}
```

##  List Filtering (`AclAccessQueryApplier`)

`AclAccessQueryApplier` adds an `INNER JOIN` to the ACL table and applies conditions:

- owner (`owner_id` + `owner_perms`)
- group (`group_id IN (...)` + `group_perms`)
- others (`other_perms`)

If the query builder has access to `getDatabase()`, a fail-fast check for the existence of the ACL table is additionally performed via `AclTableGuard`.

Usage example:

```php
$query = $this->getDatabase()->getQueryBuilder()
    ->select('d.*')
    ->from('document d');

$this->aclQueryApplier->apply(
    $query,
    $resource,
    'd.id',
    $subject,
    Permission::READ
);
```

Example for ANY mode (at least one permission is sufficient):

```php
$this->aclQueryApplier->applyAny(
    $query,
    $resource,
    'd.id',
    $subject,
    Permission::READ | Permission::WRITE
);
```

##  Single Record Check (`AclAccessResolver`)

`AclAccessResolver` checks access to a specific model:

1. Verifies that the model implements `AclResourceInterface`
2. Checks that the model exists (`isExisting()`)
3. Attempts to get ACL data from the model via `getAclData()`
4. If data is missing/empty — checks the ACL table (`AclTableGuard`) and loads the row by `record_id`
5. If the row is missing — returns `isAllowedWhenAclMissing()`
6. Otherwise, checks owner/group/others permissions

Example:

```php
$allowed = $resolver->isAllowed($document, $subject, Permission::WRITE);
if (! $allowed) {
    throw new RuntimeException('Access denied');
}
```

Example for ANY mode (at least one permission):

```php
$allowedAny = $resolver->isAllowedAny(
    $document,
    $subject,
    Permission::READ | Permission::WRITE
);

if (! $allowedAny) {
    throw new RuntimeException('Access denied');
}
```

##  Filtering Contract (`AclQueryApplierInterface`)

```php
interface AclQueryApplierInterface
{
    public function apply(
        QueryBuilderInterface $query,
        AclResourceInterface|string $resource,
        string $recordField,
        Subject $subject,
        int $permission
    ): void;

    public function applyAny(
        QueryBuilderInterface $query,
        AclResourceInterface|string $resource,
        string $recordField,
        Subject $subject,
        int $permission
    ): void;
}
```

For list/select filtering, you can pass either an ACL resource or a string with the ACL table name.

`$recordField` is passed explicitly to work correctly with different aliases and join structures (for example, `d.id`, `document.id`, `id`).

##  Fail-fast Table Check (`AclTableGuard`)

`AclTableGuard` is a utility static guard.
It checks the existence of the ACL table and caches the check result within the process.

Usage:

```php
AclTableGuard::assertTableExists($database, $tableName);
```

If the table is missing, an exception is thrown:

```text
RuntimeException: ACL table `document_acl` is not found. Create it via migration before using ACL resource.
```

##  Responsibility Boundary

- `AclAccessQueryApplier` — only filtering list/select queries
- `AclAccessResolver` — access check for a single record
- `AclResourceInterface` — only ACL resource metadata
- `AclTableGuard` — only table existence check
- Migrations/DDL — outside ACL services, in the project's infrastructure layer

##  Key Methods

| Class | Method | Purpose |
|:------|:-------|:--------|
| `AclAccessQueryApplier` | `apply(...)` | Apply ACL conditions to the query |
| `AclAccessQueryApplier` | `applyAny(...)` | Apply ACL conditions in ANY mode (at least one permission) |
| `AclAccessResolver` | `isAllowed(...)` | Check access to a record |
| `AclAccessResolver` | `isAllowedAny(...)` | Check access in ANY mode |
| `AclAccessResolver` | `assertAllowed(...)` | Throw exception on access denial |
| `AclAccessResolver` | `assertAllowedAny(...)` | Throw exception on access denial in ANY mode |
| `AclTableGuard` | `assertTableExists(...)` *(static)* | Check ACL table existence |

##  Minimum for Launch

- [ ] Created `*_acl` table: `record_id`, `owner_id`, `group_id`, `owner_perms`, `group_perms`, `other_perms`
- [ ] Configured keys for relations and searches (`PK/UNIQUE` on `record_id`, plus `FOREIGN KEY` if necessary)
- [ ] Model implements `AclResourceInterface` and defines a policy for the absence of an ACL row
- [ ] `AclAccessQueryApplier` is applied for lists, `AclAccessResolver` for single-record checks
- [ ] `update/delete` operations are performed only after ACL verification; `roleIds` are not mixed into Record ACL

[Back to table of contents](../../index.md)



