[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/http/headers-manager.md) | [RU](../../../ru/components/http/headers-manager.md)
#  HeadersManager

`HeadersManager` is a utility class for managing HTTP headers as an associative array.

##  Purpose

- Storing headers and their values as an array of strings.
- Checking for the existence and retrieving header values.
- Setting, adding, and removing headers.
- Exporting headers in different formats: array of strings, associative flat array.

##  Constructor

```php
public function __construct(array $headers = [])
```

- Accepts an array in the format `['Name' => ['value1', 'value2'], ...]` or `['Name' => 'value1,value2', ...]`.
- Calls `setHeaders($headers, true)` for initialization.

##  Main Methods

| Method                                                      | Description                                                                                           |
|:-----------------------------------------------------------|:---------------------------------------------------------------------------------------------------|
| `hasHeader(string $name): bool`                            | Checks if a header with the name `$name` is set.                                                    |
| `getHeader(string $name, mixed $default = null): mixed`    | Returns the first element of the header values array or `$default` if not present.                   |
| `getHeaderLine(string $name): ?string`                     | Returns the first header value or `null`, without arrays.                                           |
| `setHeader(string $name, string $value): void`             | Sets the header, overwriting existing; splits the string by commas into values.                      |
| `addHeader(string $name, string $value): void`             | Adds values to an existing header without overwriting existing ones.                                 |
| `removeHeader(string $name): void`                         | Removes the header completely.                                                                       |
| `setHeaders(array $headers, bool $reset = false): void`    | Iterates over the passed headers array, setting or adding them; clears if `$reset=true`.            |
| `getAll(): array`                                          | Returns the internal headers array.                                                                  |
| `getAsStrings(): array`                                    | Returns an array of strings like `"Name: value1, value2"` for each pair.                             |
| `getAsFlattened(): array`                                  | Returns an associative array like `['Name' => 'value1, value2', ...]`.                              |
| `getCount(): int`                                          | Returns the number of set headers.                                                                   |
| `clear(): void`                                            | Completely clears all headers.                                                                       |

##  Usage Examples

###  1. Initialization and setting headers
```php
$manager = new HeadersManager([
    'Content-Type' => 'application/json',
    'X-Custom'     => ['A', 'B'],
]);

// Overwrite Content-Type
$manager->setHeader('Content-Type', 'text/plain');
echo $manager->getHeader('Content-Type'); // 'text/plain'
```

###  2. Adding values to an existing header
```php
$manager->addHeader('X-Custom', 'C,D');
print_r($manager->getAll());
// ['Content-Type' => ['text/plain'], 'X-Custom' => ['A','B','C','D']]
```

###  3. Removing a header and clearing all
```php
$manager->removeHeader('X-Custom');
echo $manager->hasHeader('X-Custom') ? 'yes' : 'no'; // 'no'

$manager->clear();
echo $manager->getCount(); // 0
```

###  4. Exporting headers in different formats
```php
$manager->setHeaders([
    'Accept'  => 'text/html,application/xml',
    'Cache-Control' => ['no-cache', 'must-revalidate'],
]);

// Array of strings
$lines = $manager->getAsStrings();
// ['Accept: text/html, application/xml', 'Cache-Control: no-cache, must-revalidate']

// Flat associative array
$flat = $manager->getAsFlattened();
// ['Accept' => 'text/html, application/xml', 'Cache-Control' => 'no-cache, must-revalidate']
```

[Back to Contents](../../index.md)