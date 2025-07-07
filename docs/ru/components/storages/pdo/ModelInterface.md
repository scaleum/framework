[Вернуться к оглавлению](../../index.md)

# ModelInterface

`ModelInterface` определяет контракт для активных моделей, работающих поверх `PDO`-хранилищ в **Scaleum**. Интерфейс охватывает полный CRUD-набор, поддержку фильтрации выборок и доступ к метаданным таблицы.

## Методы

| Подпись                                                         | Возвращаемый тип | Назначение                                                                                    |
| --------------------------------------------------------------- | ---------------- | --------------------------------------------------------------------------------------------- |
| `find(mixed $id): ?self`                                        | `self\|null`     | Находит запись по первичному ключу. Возвращает экземпляр модели либо `null`, если нет данных. |
| `findOneBy(array $conditions, string $operator = 'AND'): ?self` | `self\|null`     | Выборка одной записи по набору условий.                                                       |
| `findAll(): array`                                              | `array`          | Возвращает массив всех записей (каждая — экземпляр модели).                                   |
| `findAllBy(array $conditions, string $operator = 'AND'): array` | `array`          | Возвращает массив записей, удовлетворяющих условиям.                                          |
| `load(array $input): self`                                      | `self`           | Загружает данные в свойства модели (mass-assignment).                                         |
| `insert(): int`                                                 | `int`            | Вставляет запись в БД, возвращает число затронутых строк (1 при успехе).                      |
| `update(): int`                                                 | `int`            | Обновляет существующую запись.                                                                |
| `delete(bool $cascade = false): int`                            | `int`            | Удаляет запись; опционально каскадно удаляет зависимости.                                     |
| `isExisting(): bool`                                            | `bool`           | Определяет, была ли модель загружена из БД (а не создана заново).                             |
| `getId(): mixed`                                                | `mixed`          | Возвращает значение первичного ключа.                                                         |
| `getMode(): ?string`                                            | `string\|null`   | Режим модели (напр. `readonly`, `insert`, `update`).                                          |
| `setMode(string $mode): self`                                   | `self`           | Устанавливает режим модели, возвращает себя для chain-вызовов.                                |
| `getTable(): string`                                            | `string`         | Имя таблицы в базе данных.                                                                    |
| `getPrimaryKey(): string`                                       | `string`         | Название первичного ключа.                                                                    |
| `getData()`                                                     | `mixed`          | Возвращает внутренние данные модели (сырой массив/объект).                                    |
| `getLastStatus(): array`                                        | `array`          | Статус последней операции (`[code, message]`).                                                |
| `toArray(): array`                                              | `array`          | Представляет модель в виде ассоциативного массива.                                            |

## Пример базовой реализации

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

    // Реализация только пары методов для примера
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

    // … остальные методы необходимо реализовать аналогично …

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

## Пример использования

```php
<?php

use App\Models\UserModel;
use PDO;

$pdo   = new PDO('sqlite::memory:');
$user  = new UserModel($pdo); // переменная lowerCamelCase

// Создание новой записи
$user->load(['name' => 'Alice'])->insert();

// Загрузка существующей
$existing = $user->find(1);
if ($existing) {
    echo $existing->toArray()['name']; // Alice
}
```

[Вернуться к оглавлению](../../index.md)
