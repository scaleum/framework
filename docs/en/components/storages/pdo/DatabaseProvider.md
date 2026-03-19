[Back to Contents](../../index.md)

**EN** | [UK](../../../../uk/components/storages/pdo/DatabaseProvider.md) | [RU](../../../../ru/components/storages/pdo/DatabaseProvider.md)
#  DatabaseProvider

`DatabaseProvider` is the standard implementation of `DatabaseProviderInterface` from the `Scaleum\Storages\PDO` namespace. The class simplifies obtaining a database connection via the service locator and allows overriding the connection at runtime.

##  Properties

| Property       | Type             | Access    | Default Value         | Purpose |
| -------------- | ---------------- | --------- | --------------------- | ------- |
| `$database`    | `Database\|null` | protected | `null`                | The current database connection. Lazily initialized on the first call to `getDatabase()`.             |
| `$serviceName` | `string`         | protected | `'db'`                | The service name in the `ServiceLocator` from which the `Database` object will be retrieved if it is not set directly. |

##  Methods

| Signature                                   | Return Type | Purpose |
| ------------------------------------------- | ----------- | ------- |
| `__construct(?Database $database = null)`  | —           | Accepts an optional `Database` instance. If provided, it is stored in the internal property.                                                                                    |
| `getDatabase(): Database`                   | `Database`  | Returns the connection object. If the internal property is empty, it attempts to get the `$serviceName` service from the `ServiceLocator`; if absent or of incorrect type, throws `ERuntimeError`. |
| `setDatabase(Database $database): void`    | `void`      | Replaces the current connection with a new `Database` instance.                                                                                                                            |

##  Usage Example

```php
<?php

declare(strict_types=1);

use Scaleum\Storages\PDO\DatabaseProvider;
use Scaleum\Storages\PDO\Database;
use Scaleum\Services\ServiceLocator;

// Register the connection in the service locator
ServiceLocator::set('db', new Database('sqlite::memory:'));

$provider = new DatabaseProvider(); // lowerCamelCase variable

// Get the connection (will be taken from ServiceLocator)
$db = $provider->getDatabase();
$db->query('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');

// Replace the connection with another one (e.g., MySQL in production)
$provider->setDatabase(new Database('mysql:host=db;dbname=prod', 'root', 'secret'));
```

##  Practical Tips

* **Lazy-load**: if the `$serviceName` service is not yet registered, create the connection manually and pass it to the `DatabaseProvider` constructor.
* **Testing**: in unit tests, pass mock `Database` objects via `setDatabase()` to isolate from the real storage.
* **Multiple connections**: when working with multiple databases, create several providers with different `$serviceName` values or store providers in a DI container.

[Back to Contents](../../index.md)
