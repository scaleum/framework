[Back to Contents](../index.md)

**EN** | [UK](../../uk/classes/registry.md) | [RU](../../ru/classes/registry.md)
#  Registry

**Registry** — a universal class for storing and accessing nested data using keys and delimiters.

##  Purpose

- Managing structured data in memory
- Supporting nested keys through a delimiter
- Flexible setting, getting, deleting, and merging of data

##  Main Features

| Method | Purpose |
|:------|:-----------|
| `__construct(array $items = [], string $separator = '/')` | Initializing the registry with data and setting the delimiter |
| `get(string $str, mixed $default = null)` | Getting a value by key |
| `set(string $str, mixed $value)` | Setting a value by key |
| `has(string $str)` | Checking for the existence of a value by key |
| `merge(array $items, ?string $key = null)` | Merging an array with the current data |
| `unset(string $str)` | Deleting a value by key |
| `getItems()` | Getting all registry values |
| `setItems(array $items)` | Setting registry values |
| `getSeparator()` | Getting the current delimiter |
| `setSeparator(string $separator)` | Setting a new delimiter |

##  Usage Examples

###  Creating a registry and setting values

```php
$registry = new Registry();
$registry->set('database/host', 'localhost');
$registry->set('database/port', 3306);
```

###  Getting a value

```php
$host = $registry->get('database/host');
```

###  Checking if a key exists

```php
if ($registry->has('database/port')) {
    echo 'Database port is set.';
}
```

###  Deleting a value

```php
$registry->unset('database/host');
```

###  Merging arrays

```php
$registry->merge([
    'cache' => ['enabled' => true, 'driver' => 'redis']
]);
```

###  Working with a different delimiter

```php
$registry->setSeparator('.');
$registry->set('app.config.debug', true);
```

##  Features

- Support for nested keys with dynamic creation of intermediate nodes.
- Safe array merging using `ArrayHelper::merge`.
- Flexible handling of configurations.

##  Exceptions

- No specific exceptions in the basic implementation.

[Back to Contents](../index.md)

