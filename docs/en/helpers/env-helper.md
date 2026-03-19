[Back to Table of Contents](../index.md)

**EN** | [UK](../../uk/helpers/env-helper.md) | [RU](../../ru/helpers/env-helper.md)
#  EnvHelper

`EnvHelper` is a utility class for safe handling of environment variables.

---

##  Purpose

- Retrieving environment variables with a default value
- Setting environment variables during script execution

---

##  Main Methods

| Method | Purpose |
|:------|:--------|
| `has(string $key): bool` | Check whether an environment variable exists |
| `get(string $key, mixed $default = null): mixed` | Get the value of an environment variable |
| `set(string $key, mixed $value): void` | Set the value of an environment variable |
| `interpolateArray(array $items, array $options = []): array` | Resolve placeholders recursively in array values |
| `interpolateString(string $value, array $options = []): string` | Resolve placeholders in a string value |

---

##  Usage Examples

###  Get a variable value

```php
$databaseHost = EnvHelper::get('DB_HOST', 'localhost');
```
If the environment variable `DB_HOST` is missing, 'localhost' will be returned.

###  Set a variable value
```php
EnvHelper::set('APP_DEBUG', true);
```

###  Check variable existence
```php
if (EnvHelper::has('APP_DEBUG')) {
	// variable exists even if value is an empty string
}
```

###  Resolve placeholders in config values
```php
$config = [
	'db' => [
		'host' => '${DB_HOST}',
		'port' => '${DB_PORT:-5432}',
	],
];

$resolved = EnvHelper::interpolateArray($config, [
	'variables' => ['DB_HOST' => 'localhost'],
]);
```

###  Get a variable value
```php
$isDebug = EnvHelper::get('APP_DEBUG', false);
// true
```

##  Features
- Uses `getenv()` and `putenv()` functions
- Also uses `$_ENV` for consistent runtime visibility
- Values are automatically converted to string when set
- Preserves empty string and `'0'` values (does not replace them with default)
- Returns default only when a variable is truly missing
- Supports placeholder interpolation syntax: `${VAR}`, `${VAR:-default}`, `${VAR:?message}`
- Supports simple usage without dependency on additional libraries

[Back to Table of Contents](../index.md)