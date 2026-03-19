[Повернутись до змісту](../../index.md)

[EN](../../../../en/components/storages/pdo/DatabaseProviderInterface.md) | **UK** | [RU](../../../../ru/components/storages/pdo/DatabaseProviderInterface.md)
# DatabaseProviderInterface

`DatabaseProviderInterface` описує контракт для класів, які надають об'єкт підключення до бази даних (`Database`) і дозволяють замінити його на інший екземпляр під час роботи додатку.

## Методи

| Підпис                            | Повертаємий тип | Призначення                                         |
| -------------------------------- | --------------- | -------------------------------------------------- |
| `getDatabase(): Database`         | `Database`      | Повертає активне підключення до бази даних.         |
| `setDatabase(Database $db): void` | `void`          | Встановлює (замінює) підключення до бази даних.    |

## Приклад реалізації

```php
<?php

declare(strict_types=1);

namespace App\Storage;

use Scaleum\Storages\PDO\DatabaseProviderInterface;
use Scaleum\Storages\PDO\Database; // припустимо, що це обгортка над PDO

class PdoDatabaseProvider implements DatabaseProviderInterface
{
    private Database $database;

    public function __construct(string $dsn, string $user = '', string $password = '')
    {
        $this->database = new Database($dsn, $user, $password);
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function setDatabase(Database $db): void
    {
        $this->database = $db;
    }
}
```

## Приклад використання

```php
<?php

use App\Storage\PdoDatabaseProvider;
use Scaleum\Storages\PDO\Database;

$provider = new PdoDatabaseProvider('sqlite::memory:'); // змінна lowerCamelCase

// Отримуємо підключення і виконуємо запит
$db = $provider->getDatabase();
$db->query('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');

// За потреби замінюємо підключення (наприклад, при зміні оточення)
$provider->setDatabase(new Database('mysql:host=db;dbname=prod', 'root', 'secret'));
```

## Ідеї розширення

* **Lazy‑loading**: відкладіть створення екземпляра `Database` до першого виклику `getDatabase()`, щоб пришвидшити запуск додатку.
* **Пул з'єднань**: зберігайте і повертайте об'єкти з пулу; метод `setDatabase()` може поміщати нове з'єднання назад у пул замість прямої заміни.
* **Логування**: обгорніть `Database` декоратором, який додає логування запитів, і замінюйте його через `setDatabase()` у debug‑режимі.

[Повернутись до змісту](../../index.md)