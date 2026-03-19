[Back to Contents](../index.md)

**EN** | [UK](../../uk/helpers/string-case-helper.md) | [RU](../../ru/helpers/string-case-helper.md)
#  StringCaseHelper

`StringCaseHelper` is a utility class for working with cases and string transformations in the Scaleum Framework.

##  Purpose

- Converting strings to `CamelCase`, `SnakeCase`, `PascalCase`
- Checking string format
- Splitting strings by case or delimiter

##  Main Methods

| Method | Purpose |
|:------|:--------|
| `camelize($str)` | Converts a string to `camelCase` |
| `humanize($str)` | Converts a string to a human-readable format |
| `isCamelCase(string $string)` | Checks if a string is `camelCase` |
| `isSnakeCase(string $string)` | Checks if a string is `snake_case` |
| `isPascalCase(string $string)` | Checks if a string is `PascalCase` |
| `splitString(string $string, string $delimiter = '.')` | Splits a string into parts using the specified delimiter |

##  Usage Examples

###  Converting a string to camelCase

```php
$camel = StringCaseHelper::camelize('path_to_folder'); // pathToFolder
```

###  Converting a string to a human-readable form

```php
$human = StringCaseHelper::humanize('path_to_folder'); // Path To Folder
```

###  Checking if a string is camelCase

```php
$isCamel = StringCaseHelper::isCamelCase('pathToFolder'); // true
```

###  Checking if a string is snake_case

```php
$isSnake = StringCaseHelper::isSnakeCase('path_to_folder'); // true
```

###  Checking if a string is PascalCase

```php
$isPascal = StringCaseHelper::isPascalCase('PathToFolder'); // true
```

###  Splitting a string using a different delimiter

```php
$split = StringCaseHelper::splitString('PathToFolder', '_'); // path_to_folder
```

[Back to Contents](../index.md)