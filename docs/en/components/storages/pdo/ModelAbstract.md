[Back to Contents](../../index.md)

**EN** | [UK](../../../../uk/components/storages/pdo/ModelAbstract.md) | [RU](../../../../ru/components/storages/pdo/ModelAbstract.md)
#  ModelAbstract

`ModelAbstract` is the base **Active‑Record** class in Scaleum for working with `PDO` storage. It implements most of the `ModelInterface` contract, including state management (`insert`, `update`, `readonly`) and **relations support** (*relations*). All instance data is stored in an internal `ModelData` object, and the database connection is passed through the constructor.

---

##  Properties

| Property      | Type                                                     | Purpose                                             |
| ------------- | -------------------------------------------------------- | --------------------------------------------------- |
| `$pdo`        | `PDO`                                                    | Active database connection.                         |
| `$data`       | `ModelData`                                              | Container for model attributes.                     |
| `$lastStatus` | `array{status:bool,status_text:string,relations:array}` | Result of the last operation.                        |

---

##  Relations Configuration

Each model descendant can override the `getRelations()` method and return an array of configurations:

```php
protected function getRelations(): array
{
    return [
        // One-to-one (hasOne)
        'profile' => [
            'model'       => ProfileModel::class,   // related model class
            'method'      => 'findByUserId',        // method to call for loading
            'primary_key' => 'id',                  // PK of profile table
            'foreign_key' => 'user_id',             // FK in profile pointing to users
            'type'        => 'hasOne',              // hasOne | hasMany | belongsTo
            'persist'     => true,                  // save automatically
        ],

        // One-to-many (hasMany)
        'posts' => [
            'model'       => PostModel::class,
            'method'      => 'findByUserId',
            'primary_key' => 'id',
            'foreign_key' => 'user_id',
            'type'        => 'hasMany',
            'persist'     => true,
        ],
    ];
}
```

###  Relations Lifecycle

| Phase          | What `ModelAbstract` does                                                                                   |
| -------------- | ---------------------------------------------------------------------------------------------------------- |
| **Loading**    | After `find()` / `findAll*()` calls `loadRelations()`, which initializes models from the configuration and loads data into properties (`$this->profile`, `$this->posts`). |
| **Saving**     | In `insert()` / `update()`, the `updateRelations()` method determines new/changed/deleted related objects and calls their `insert()` / `update()` / `delete()`. |
| **Deleting**   | `delete(true)` recursively deletes all relations where `persist === true`.                                |

`persist = false` disables automatic saving — the relation will only be loaded.

---

##  Key Methods (public)

| Signature                                        | Return Type     | Purpose                                                                          |
| ------------------------------------------------ | --------------- | -------------------------------------------------------------------------------- |
| `__construct(PDO $pdo, array $attributes = [])`  | —               | Accepts connection and initial attributes; selects `insert` or `readonly` mode. |
| `load(array $input): self`                        | `self`          | Loads data into internal `ModelData`, switches model to `update` mode.          |
| `find(mixed $id): ?self`                          | `self\|null`    | Loads record including relations.                                               |
| `insert(): int`                                   | `int`           | Inserts record and related models (`persist = true`).                           |
| `update(): int`                                   | `int`           | Updates record and synchronizes relation changes.                               |
| `delete(bool $cascade = false): int`              | `int`           | Deletes record; with `$cascade = true` recursively deletes relations.           |
| `isExisting(): bool`                              | `bool`          | Was the model retrieved from the DB?                                           |
| `getId(): mixed`                                  | `mixed`         | Primary key value.                                                              |
| `toArray(bool $strict = true): array`             | `array`         | Associative data array; if `$strict = false`, includes related objects.         |

---

##  Full example:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Scaleum\Storages\PDO\ModelAbstract;
use PDO;

class UserModel extends ModelAbstract
{
    protected function getTable(): string { return 'users'; }
    protected function getPrimaryKey(): string { return 'id'; }

    protected function getRelations(): array
    {
        return [
            'profile' => [
                'model'       => ProfileModel::class,
                'method'      => 'findByUserId',
                'primary_key' => 'id',
                'foreign_key' => 'user_id',
                'type'        => 'hasOne',
                'persist'     => true,
            ],
            'posts' => [
                'model'       => PostModel::class,
                'method'      => 'findByUserId',
                'primary_key' => 'id',
                'foreign_key' => 'user_id',
                'type'        => 'hasMany',
                'persist'     => true,
            ],
        ];
    }
}

class ProfileModel extends ModelAbstract
{
    protected function getTable(): string { return 'profiles'; }
    protected function getPrimaryKey(): string { return 'id'; }
    protected function getRelations(): array { return []; }
}

class PostModel extends ModelAbstract
{
    protected function getTable(): string { return 'posts'; }
    protected function getPrimaryKey(): string { return 'id'; }
    protected function getRelations(): array { return []; }
}
```

###  Usage scenario

```php
<?php
$pdo = new PDO('sqlite::memory:');

$user = (new UserModel($pdo))->find(1); // will load profile and posts

// Change name and add a new post
$user->load([
    'user_id' => $user->getId(), // to update an existing user
    'name' => 'Bob',    
    'posts' => [ ['title' => 'New article','body'  => 'Text…'] ] // new post
]);


// Everything is saved cascade
$user->update();

// Delete user and all related data
$user->delete(true);
```

---

##  Useful tips

* **Factory stubs**: if models are created by DI container, override `createModelInstance()` or use the `$relationFactories` array.
* **Deferred synchronization**: set `persist = false` for large collections to save them manually (lazy-write).
* **SQL override**: override `buildInsertSql()` / `buildUpdateSql()` for UPSERT or bulk operations.

[Back to contents](../../index.md)
