[Back to Contents](./index.md)

**EN** | [UK](../../uk/components/cache.md) | [RU](../../ru/components/cache.md)
#  Cache
The `Cache` component in Scaleum provides centralized caching operations, abstracting interaction through the `CacheInterface`.

##  Purpose

- Unified access to cache
- Ability to dynamically substitute different drivers
- Centralized enabling/disabling of caching
- Support for multiple cache drivers

##  Main Features

- Operates through the `CacheInterface`
- Flexible driver setup (`RedisDriver`, `FilesystemDriver`, `NullDriver`)
- Management of caching activity (`enabled`)
- Lazy driver connection (`getDriverDefault()`)

##  `CacheInterface` Interface

```php
interface CacheInterface
{
    public function clean(): bool;
    public function has(string $id): bool;
    public function delete(string $id): bool;
    public function get(string $id): mixed;
    public function getMetadata(string $id): mixed;
    public function save(string $id, mixed $data): bool;
}
```

##  `Cache` Implementation
The Cache class implements the proxy pattern:
- All operations are delegated to the current driver (`CacheInterface`).
- Checks if caching is enabled (`enabled`).
- Loads the default driver if necessary (`NullDriver`).

##  Supported Drivers
Driver | Purpose
|:------|:-----------|
`RedisDriver` | Caching in Redis
`FilesystemDriver` | Caching in the filesystem
`NullDriver` | Stub without real caching

##  Usage Examples

####  Quick Driver Setup
```php
$cache = new Cache();
$cache->setEnabled(true);
$cache->setDriver(new RedisDriver([
    'host' => '127.0.0.1',
    'port' => 6379,
]));
```
####  Saving Data to Cache
```php
$cache->save('user_123', ['id' => 123, 'name' => 'Maxim']);
```

####  Reading Data from Cache
```php
$user = $cache->get('user_123');
if ($user) {
    echo $user['name']; // Maxim
}
```

####  Checking for Key Existence
```php
if ($cache->has('user_123')) {
    echo "Cache found!";
}
```

####  Deleting an Item from Cache
```php
$cache->delete('user_123');
```

####  Clearing the Entire Cache
```php
$cache->clean();
```

##  `Cache` Class Methods
Method | Purpose
|:------|:-----------|
`setEnabled(bool $enabled): self` | Enable/disable cache usage
`getEnabled(): bool` | Check caching status
`setDriver(mixed $driver): self` | Set the cache driver
`getDriver(): CacheInterface` | Get the current driver
`clean(): bool` | Clear the entire cache
`has(string $id): bool` | Check for an item by key
`get(string $id): mixed` | Get an item by key
`save(string $id, mixed $data): bool` | Save data by key
`delete(string $id): bool` | Delete an item by key
`getMetadata(string $id): mixed` | Get metadata by key

##  Features
- When caching is disabled (`enabled = false`), all operations return default values (false, null).
- If the driver is not explicitly set, `NullDriver` is used.
- Supports automatic driver creation via `createInstance()` when setting a string.

##  Errors
Exception | Condition
|:------|:-----------|
`InvalidArgumentException` | An invalid driver was passed


[Back to Contents](./index.md)