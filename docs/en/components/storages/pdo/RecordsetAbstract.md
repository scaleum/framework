[Back to table of contents](../../index.md)

**EN** | [UK](../../../../uk/components/storages/pdo/RecordsetAbstract.md) | [RU](../../../../ru/components/storages/pdo/RecordsetAbstract.md)
#  RecordsetAbstract

`RecordsetAbstract` is a base class for record collections in the Scaleum PDO layer.
It can:

- load records from an SQL query (`load()`),
- hydrate database rows into models (`$modelClass`),
- filter records in memory (`filter()`),
- track deleted records and save changes (`save()`).

The class implements `IteratorAggregate`, so it can be used in `foreach`.

---

##  When to use

`RecordsetAbstract` is suitable when you need to:

- encapsulate SQL selections into a separate collection object,
- apply post-processing/filtering to already loaded data,
- work with a set of `ModelInterface` objects and perform batch update/delete.

---

##  Minimal implementation

```php
<?php

declare(strict_types=1);

namespace App\Storage;

use Scaleum\Storages\PDO\RecordsetAbstract;

final class UserRecordset extends RecordsetAbstract
{
    protected function getQuery(): string
    {
        return 'SELECT id, email, status FROM users ORDER BY id DESC LIMIT :limit OFFSET :offset';
    }
}
```

Usage:

```php
<?php

$recordset = (new UserRecordset($database))
    ->setParams([':limit' => 50, ':offset' => 0])
    ->load();

foreach ($recordset as $row) {
    // $row — array or model, depends on $modelClass
}

$total = $recordset->getRecordTotalCount();
```

---

##  Main methods

| Method | Purpose |
| --- | --- |
| `load()` | Executes the `getQuery()` and fills the recordset. |
| `filter(callable $callback)` | Returns a new recordset instance with filtered elements. |
| `add(mixed $record)` | Adds an associative array or `ModelInterface`. |
| `remove(ModelInterface $model)` | Removes a model by primary key and puts it into the removed list. |
| `removeByIndex(int $index)` | Removes a collection element by index. |
| `save()` | Calls `delete()` for removed and `update()` for current models. |
| `toArray()` | Converts the collection to an array (calls `toArray()` for models). |

---

##  ACL filters: examples

Below are examples in two modes:

- filtering after loading (in memory),
- filtering at the SQL query level via `AclAccessQueryApplier`.

###  1. ACL filtering in memory via `filter(...)`

```php
<?php

use Scaleum\Security\Permission;
use Scaleum\Security\Subject;

$subject = new Subject(userId: 10, groupIds: [2, 3]);
$required = Permission::READ | Permission::WRITE;

$visible = $recordset->filter(function (array $row) use ($subject, $required): bool {
    $isOwner = (int) ($row['owner_id'] ?? 0) === $subject->getUserId();
    if ($isOwner) {
        return (((int) ($row['owner_perms'] ?? 0)) & $required) === $required;
    }

    $groupId = (int) ($row['group_id'] ?? 0);
    if (in_array($groupId, $subject->getGroupIds(), true)) {
        return (((int) ($row['group_perms'] ?? 0)) & $required) === $required;
    }

    return (((int) ($row['other_perms'] ?? 0)) & $required) === $required;
});
```

This approach is convenient if the data is already loaded and you need to apply an additional ACL context at the application level.

###  2. ACL filtering in SQL (`apply`) for "all permissions" mode

```php
<?php

declare(strict_types=1);

namespace App\Storage;

use Scaleum\Security\Contracts\AclResourceInterface;
use Scaleum\Security\Permission;
use Scaleum\Security\Services\AclAccessQueryApplier;
use Scaleum\Security\Subject;
use Scaleum\Storages\PDO\RecordsetAbstract;

final class DocumentRecordset extends RecordsetAbstract
{
    public function __construct(
        private readonly Subject $subject,
        private readonly AclResourceInterface|string $aclResource,
        ?\Scaleum\Storages\PDO\Database $database = null,
        ?string $modelClass = null,
        array $records = []
    ) {
        parent::__construct($database, $modelClass, $records);
    }

    protected function getQuery(): string
    {
        $query = $this->getDatabase()->getQueryBuilder();
        $query->select('d.*')->from('documents AS d');

        (new AclAccessQueryApplier())->apply(
            query: $query,
            resource: $this->aclResource,
            recordField: 'd.id',
            subject: $this->subject,
            permission: Permission::READ | Permission::WRITE
        );

        return (string) $query;
    }
}
```

In the `apply(...)` mode, a record passes only if the full set of required bits is present.

###  3. ACL filtering in SQL (`applyAny`) for "any permission" mode

```php
<?php

$applier = new AclAccessQueryApplier();
$applier->applyAny(
    query: $query,
    resource: 'document_acl',
    recordField: 'd.id',
    subject: $subject,
    permission: Permission::READ | Permission::WRITE
);
```

`applyAny(...)` passes records where at least one of the required bits is present.

---

##  Recommendations

- For heavy ACL scenarios, prefer SQL filtering (`apply`/`applyAny`) to reduce the amount of data in memory.
- Use `filter(...)` for local/post-rules after the main SQL filtering.
- If `$modelClass` is provided, ensure that records are associative arrays and contain keys expected by `ModelAbstract::load(...)`.

[Back to the table of contents](../../index.md)
