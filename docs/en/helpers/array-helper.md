[Back to Contents](../index.md)

**EN** | [UK](../../uk/helpers/array-helper.md) | [RU](../../ru/helpers/array-helper.md)
#  ArrayHelper

`ArrayHelper` is a utility class for working with arrays: safe access, filtering, merging, structure validation.

##  Purpose

- Safe value extraction
- Bulk element selection
- Filtering by keys
- Array structure validation
- Intelligent array merging

##  Main methods

| Method | Purpose |
|:------|:--------|
| `element($key, array $haystack, $default = false, $expectedType = null): mixed` | Get value by key |
| `elements(mixed $keys, array $haystack, mixed $default = false, mixed $expectedType = null, bool $keysPreserve = false): array` | Get multiple elements by keys |
| `filter(mixed $keys, array $haystack): array` | Remove specified elements |
| `keyFirst(array $array): mixed\|null` | Get first key |
| `keyLast(array $array): mixed\|null` | Get last key |
| `keysExists(array $keys, array $haystack): bool` | Check existence of multiple keys |
| `search(mixed $needle, array $haystack, bool $strict = false, mixed $column = null)` | Search for a value |
| `isAssociative(array $array): bool` | Check if array is associative |
| `merge(array ...$arrays): array` | Intelligent array merging |

---

##  Usage examples

###  Extracting a single element

```php
$data = ['id' => 123, 'name' => 'Maxim'];

$id = ArrayHelper::element('id', $data); // 123
$age = ArrayHelper::element('age', $data, 18); // 18 (default)
```

###  Extracting multiple elements
```php
$data = ['id' => 123, 'name' => 'Maxim', 'role' => 'admin'];

$info = ArrayHelper::elements(['id', 'role'], $data);
// ['id' => 123, 'role' => 'admin']
```

###  Filtering an array
```php
$data = ['id' => 123, 'name' => 'Maxim', 'role' => 'admin'];

$filtered = ArrayHelper::filter(['role'], $data);
// ['id' => 123, 'name' => 'Maxim']
```

###  Getting the first/last key
```php
$data = ['id' => 123, 'name' => 'Maxim'];

$firstKey = ArrayHelper::keyFirst($data); // 'id'
$lastKey  = ArrayHelper::keyLast($data);  // 'name'
```

###  Checking key existence
```php
$data = ['id' => 123, 'name' => 'Maxim'];

$exists = ArrayHelper::keysExists(['id', 'role'], $data); // true (id exists)
```

###  Searching for a value in an array
```php
$data = ['apple', 'banana', 'cherry'];

$found = ArrayHelper::search('banana', $data); // 1
```

###  Checking if an array is associative
```php
$assoc = ['name' => 'Maxim', 'role' => 'admin'];
$indexed = ['apple', 'banana'];

$isAssoc = ArrayHelper::isAssociative($assoc); // true
$isAssoc = ArrayHelper::isAssociative($indexed); // false
```

###  Intelligent array merging
```php
$base = ['id' => 1, 'tags' => ['php']];
$override = ['tags' => ['helpers', 'arrays']];

$result = ArrayHelper::merge($base, $override);
// ['id' => 1, 'tags' => ['php', 'helpers', 'arrays']]
```

##  Features
- Automatic type checking of extracted values (`expectedType` via `TypeHelper`)
- Intelligent handling of numeric/string keys during merging (`merge()`)
- Safe operation with non-existent array elements


[Back to Contents](../index.md)