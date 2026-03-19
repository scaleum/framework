[Back to Contents](../index.md)

**EN** | [UK](../../uk/components/session.md) | [RU](../../ru/components/session.md)
#  Session

The `Session` component in Scaleum implements an abstract mechanism for working with sessions with support for various data storage drivers.

##  Purpose

- Centralized management of user sessions
- Support for multiple session storage methods (Redis, Files, DB)
- Secure management of session ID via cookies
- Support for event-driven session closing and updating
- Handling additional metadata (user IP, User Agent)

##  Main Components

| Class/Interface | Purpose |
|:----------------|:--------|
| `SessionInterface` | Contract for working with sessions |
| `SessionAbstract` | Base abstract class for sessions |
| `RedisSession` | Sessions in Redis |
| `FileSession` | Sessions in the file system |
| `DatabaseSession` | Sessions in the database |

##  Key Features

- Operation through a universal interface
- Support for session activity validation (IP, last_activity)
- Data storage in Redis / FS / DB
- Automatic cleanup of expired sessions (`cleanup()`)
- Secure signing of cookie values (`salt`, `encode`)
- Support for event-driven updating (`KernelEvents::FINISH`)

##  `SessionInterface` Methods

| Method | Purpose |
|:-------|:--------|
| `get(string\|int $var, mixed $default = false): mixed` | Get a value from the session |
| `set(string\|int $var, mixed $value = null, bool $updateImmediately = true): static` | Set a value |
| `has(string\|int $var): bool` | Check if a variable exists |
| `remove(string $key, bool $updateImmediately = true): static` | Remove a variable |
| `removeByPrefix(string $prefix, bool $updateImmediately = true): static` | Remove variables by prefix |
| `flush(bool $updateImmediately = true): static` | Clear/reset the session |
| `getByPrefix(?string $prefix = null): array` | Get all variables by prefix |

##  Usage Examples

###  Session Initialization

```php
$session = new RedisSession([
    'host' => '127.0.0.1',
    'port' => 6379,
    'expiration' => 3600,
]);
// or
$session = new FileSession([
    'path' => '/tmp/sessions/',
]);

// or
$session = new DatabaseSession([
    'database' => $databaseConnection,    
]);
```

###  Working with Data
```php
// Set data
$session->set('user_id', 123);

// Get data
$userId = $session->get('user_id');

// Remove data
$session->remove('user_id');

// Clear the entire session
$session->flush();
```

###  Session Activity Check
```php
if (! $session->isValid()) {
    $session->flush();
    // user must re-authenticate
}
```

##  Session Drivers
`RedisSession`  
- Stores each variable as a separate key in Redis.
- Stores sessions as serialized text (`$data = base64_encode(gzcompress(serialize($data)))`).
- Keys have the format: `namespace:session_id:variable`.

`FileSession`  
- Stores sessions as text files (`$data = serialize($data)`).
- Automatically cleans up expired files on random trigger.

`DatabaseSession`  
- Stores sessions in a database table.
- Stores sessions as serialized text (`$data = base64_encode(gzcompress(serialize($data)))`).
- Automatically creates the `sessions` table if needed (configurable).
- Optimized for bulk cleanup of expired records.

##  Session Security
- Generates a unique ID based on IP + random prefix.
- Hashes cookie values using md5 + salt.
- Allows secure cookie updates without restarting the request.
- Protects against IP and User Agent spoofing.

##  Security Methods
Method | Purpose
|:-------|:-------|
`isValid()` | Checks session validity
`getAnchor(string $key)` | Gets a cookie value with signature validation
`setAnchor(string $key, mixed $value)` | Sets a cookie with signature protection

##  Full Usage Example
```php
$session = new FileSession([
    'path' => '/tmp/sessions/',
]);

// Write user data
$session->set('user', ['id' => 123, 'name' => 'Maxim']);

// Check for data presence
if ($session->has('user')) {
    $user = $session->get('user');
    echo "Hello, " . $user['name'];
}

// Remove by prefix
$session->removeByPrefix('cart_');

// Close and clear session on finish
$session->close();
```

##  Errors
Exception | Condition
|:---------|:---------|
`ERuntimeError` | Error when `EventManager` or `Database` is missing


[Back to Contents](../index.md)