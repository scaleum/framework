[Back to Contents](../../index.md)

**EN** | [UK](../../../../uk/components/storages/pdo/DatabaseProviderInterface.md) | [RU](../../../../ru/components/storages/pdo/DatabaseProviderInterface.md)
#  DatabaseProviderInterface

`DatabaseProviderInterface` describes the contract for classes that provide a database connection object (`Database`) and allow replacing it with another instance during the application's runtime.

##  Methods

| Signature                         | Return Type      | Purpose                                              |
| --------------------------------- | ---------------- | ---------------------------------------------------- |
| `getDatabase(): Database`         | `Database`       | Returns the active database connection.              |
| `setDatabase(Database $db): void` | `void`           | Sets (replaces) the database connection.             |

##  Implementation Example

```php
<?php

declare(strict_types=1);

namespace App\Storage;

use Scaleum\Storages\PDO\DatabaseProviderInterface;
use Scaleum\Storages\PDO\Database; // suppose this is a wrapper over PDO

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

##  Usage Example

```php
<?php

use App\Storage\PdoDatabaseProvider;
use Scaleum\Storages\PDO\Database;

$provider = new PdoDatabaseProvider('sqlite::memory:'); // lowerCamelCase variable

// Get the connection and execute a query
$db = $provider->getDatabase();
$db->query('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');

// Replace the connection if necessary (e.g., when changing environment)
$provider->setDatabase(new Database('mysql:host=db;dbname=prod', 'root', 'secret'));
```

##  Extension Ideas

* **Lazy-loading**: defer the creation of the `Database` instance until the first call to `getDatabase()` to speed up application startup.
* **Connection pool**: store and return objects from a pool; the `setDatabase()` method can put the new connection back into the pool instead of direct replacement.
* **Logging**: wrap `Database` with a decorator that adds query logging, and replace it via `setDatabase()` in debug mode.

[Back to Contents](../../index.md)
