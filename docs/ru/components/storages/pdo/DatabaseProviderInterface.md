[Вернуться к оглавлению](../../index.md)

# DatabaseProviderInterface

`DatabaseProviderInterface` описывает контракт для классов, предоставляющих объект подключения к базе данных (`Database`) и позволяющих заменить его на другой экземпляр во время работы приложения.

## Методы

| Подпись                           | Возвращаемый тип | Назначение                                           |
| --------------------------------- | ---------------- | ---------------------------------------------------- |
| `getDatabase(): Database`         | `Database`       | Возвращает активное подключение к базе данных.       |
| `setDatabase(Database $db): void` | `void`           | Устанавливает (подменяет) подключение к базе данных. |

## Пример реализации

```php
<?php

declare(strict_types=1);

namespace App\Storage;

use Scaleum\Storages\PDO\DatabaseProviderInterface;
use Scaleum\Storages\PDO\Database; // предположим, что это обёртка над PDO

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

## Пример использования

```php
<?php

use App\Storage\PdoDatabaseProvider;
use Scaleum\Storages\PDO\Database;

$provider = new PdoDatabaseProvider('sqlite::memory:'); // переменная lowerCamelCase

// Получаем подключение и выполняем запрос
$db = $provider->getDatabase();
$db->query('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');

// При необходимости подменяем подключение (например, при смене окружения)
$provider->setDatabase(new Database('mysql:host=db;dbname=prod', 'root', 'secret'));
```

## Идеи расширения

* **Lazy‑loading**: отложите создание экземпляра `Database` до первого вызова `getDatabase()`, чтобы ускорить запуск приложения.
* **Пул соединений**: храните и возвращайте объекты из пула; метод `setDatabase()` может помещать новое соединение обратно в пул вместо прямой замены.
* **Логирование**: оборачивайте `Database` декоратором, добавляющим логирование запросов, и подменяйте его через `setDatabase()` в debug‑режиме.

[Вернуться к оглавлению](../../index.md)
