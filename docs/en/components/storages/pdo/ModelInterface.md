[Back to Contents](../../index.md)

**EN** | [UK](../../../../uk/components/storages/pdo/ModelInterface.md) | [RU](../../../../ru/components/storages/pdo/ModelInterface.md)
#  ModelInterface

`ModelInterface` defines the contract for active models operating on top of `PDO` storages in **Scaleum**. The interface covers the full CRUD set, supports filtering of selections, and provides access to table metadata.

##  Methods

| Signature                                                       | Return Type      | Purpose                                                                                      |
| --------------------------------------------------------------- | ---------------- | --------------------------------------------------------------------------------------------- |
| `find(mixed $id): ?self`                                        | `self\|null`     | Finds a record by primary key. Returns a model instance or `null` if no data is found.         |
| `findOneBy(array $conditions, string $operator = 'AND'): ?self` | `self\|null`     | Selects one record by a set of conditions.                                                    |
| `findAll(): array`                                              | `array`          | Returns an array of all records (each is a model instance).                                   |
| `findAllBy(array $conditions, string $operator = 'AND'): array` | `array`          | Returns an array of records matching the conditions.                                         |
| `load(array $input): self`                                      | `self`           | Loads data into model properties (mass-assignment).                                          |
| `insert(): int`                                                 | `int`            | Inserts a record into the DB, returns the number of affected rows (1 on success).             |
| `update(): int`                                                 | `int`            | Updates an existing record.                                                                   |
| `delete(bool $cascade = false): int`                            | `int`            | Deletes the record; optionally cascades deletion to dependencies.                             |
| `isExisting(): bool`                                            | `bool`           | Determines if the model was loaded from the DB (not newly created).                           |
| `getId(): mixed`                                                | `mixed`          | Returns the value of the primary key.                                                        |
| `getMode(): ?string`                                            | `string\|null`   | Model mode (e.g., `readonly`, `insert`, `update`).                                           |
| `setMode(string $mode): self`                                   | `self`           | Sets the model mode, returns self for chain calls.                                           |
| `getTable(): string`                                            | `string`         | Name of the table in the database.                                                          |
| `getPrimaryKey(): string`                                       | `string`         | Name of the primary key.                                                                     |
| `getData()`                                                     | `mixed`          | Returns the internal data of the model (raw array/object).                                   |
| `getLastStatus(): array`                                        | `array`          | Status of the last operation (`[code, message]`).                                            |
| `toArray(): array`                                              | `array`          | Represents the model as an associative array.                                               |

##  Basic Implementation Example

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Scaleum\Storages\PDO\ModelInterface;
use PDO;

class UserModel implements ModelInterface
{
    private PDO $pdo;
    private array $data = [];
    private ?string $mode = null;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Implementation of only a couple of methods as an example
    public function find(mixed $id): ?self
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return $this->load($row);
    }

    public function load(array $input): self
    {
        $this->data = $input;
        $this->mode = 'readonly';
        return $this;
    }

    // … other methods should be implemented similarly …

    public function insert(): int {/* ... */}
    public function update(): int {/* ... */}
    public function delete(bool $cascade = false): int {/* ... */}
    public function isExisting(): bool {/* ... */}
    public function getId(): mixed {return $this->data['id'] ?? null;}
    public function getMode(): ?string {return $this->mode;}
    public function setMode(string $mode): self {$this->mode = $mode; return $this;}
    public function getTable(): string {return 'users';}
    public function getPrimaryKey(): string {return 'id';}
    public function findOneBy(array $conditions,string $operator = 'AND'): ?self {/* ... */}
    public function findAll(): array {/* ... */}
    public function findAllBy(array $conditions,string $operator = 'AND'): array {/* ... */}
    public function getData() {return $this->data;}
    public function getLastStatus(): array {/* ... */}
    public function toArray(): array {return $this->data;}
}
```

##  Usage Example

```php
<?php

use App\Models\UserModel;
use PDO;

$pdo   = new PDO('sqlite::memory:');
$user  = new UserModel($pdo); // variable in lowerCamelCase

// Creating a new record
$user->load(['name' => 'Alice'])->insert();

// Loading an existing one
$existing = $user->find(1);
if ($existing) {
    echo $existing->toArray()['name']; // Alice
}
```

[Back to the table of contents](../../index.md)
