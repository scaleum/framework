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
| `get(string $key, mixed $default = null): mixed` | Get the value of an environment variable |
| `set(string $key, mixed $value): void` | Set the value of an environment variable |

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

###  Get a variable value
```php
$isDebug = EnvHelper::get('APP_DEBUG', false);
// true
```

##  Features
- Uses `getenv()` and `putenv()` functions
- Values are automatically converted to string when set
- Returns the value or default without errors
- Supports simple usage without dependency on additional libraries

[Back to Table of Contents](../index.md)