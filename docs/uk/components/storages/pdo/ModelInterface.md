[Повернутись до змісту](../../index.md)

[EN](../../../../en/components/storages/pdo/ModelInterface.md) | **UK** | [RU](../../../../ru/components/storages/pdo/ModelInterface.md)
#  ModelInterface

`ModelInterface` визначає контракт для активних моделей, що працюють поверх `PDO`-сховищ у **Scaleum**. Інтерфейс охоплює повний CRUD-набір, підтримку фільтрації вибірок та доступ до метаданих таблиці.

##  Методи

| Підпис                                                          | Повертаємий тип  | Призначення                                                                                   |
| --------------------------------------------------------------- | ---------------- | --------------------------------------------------------------------------------------------- |
| `find(mixed $id): ?self`                                        | `self\|null`     | Знаходить запис за первинним ключем. Повертає екземпляр моделі або `null`, якщо даних немає.  |
| `findOneBy(array $conditions, string $operator = 'AND'): ?self` | `self\|null`     | Вибірка одного запису за набором умов.                                                       |
| `findAll(): array`                                              | `array`          | Повертає масив усіх записів (кожен — екземпляр моделі).                                       |
| `findAllBy(array $conditions, string $operator = 'AND'): array` | `array`          | Повертає масив записів, що задовольняють умови.                                              |
| `load(array $input): self`                                      | `self`           | Завантажує дані у властивості моделі (mass-assignment).                                      |
| `insert(): int`                                                 | `int`            | Вставляє запис у БД, повертає кількість затронутих рядків (1 при успіху).                     |
| `update(): int`                                                 | `int`            | Оновлює існуючий запис.                                                                       |
| `delete(bool $cascade = false): int`                            | `int`            | Видаляє запис; опційно каскадно видаляє залежності.                                          |
| `isExisting(): bool`                                            | `bool`           | Визначає, чи була модель завантажена з БД (а не створена заново).                            |
| `getId(): mixed`                                                | `mixed`          | Повертає значення первинного ключа.                                                          |
| `getMode(): ?string`                                            | `string\|null`   | Режим моделі (наприклад, `readonly`, `insert`, `update`).                                    |
| `setMode(string $mode): self`                                   | `self`           | Встановлює режим моделі, повертає себе для chain-викликів.                                   |
| `getTable(): string`                                            | `string`         | Ім'я таблиці в базі даних.                                                                    |
| `getPrimaryKey(): string`                                       | `string`         | Назва первинного ключа.                                                                       |
| `getData()`                                                     | `mixed`          | Повертає внутрішні дані моделі (сирий масив/об'єкт).                                         |
| `getLastStatus(): array`                                        | `array`          | Статус останньої операції (`[code, message]`).                                               |
| `toArray(): array`                                              | `array`          | Представляє модель у вигляді асоціативного масиву.                                           |

##  Приклад базової реалізації

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

    // Реалізація лише пари методів для прикладу
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

    // … решту методів необхідно реалізувати аналогічно …

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

##  Приклад використання

```php
<?php

use App\Models\UserModel;
use PDO;

$pdo   = new PDO('sqlite::memory:');
$user  = new UserModel($pdo); // змінна lowerCamelCase

// Створення нового запису
$user->load(['name' => 'Alice'])->insert();

// Завантаження існуючого
$existing = $user->find(1);
if ($existing) {
    echo $existing->toArray()['name']; // Alice
}
```

[Повернутися до змісту](../../index.md)
