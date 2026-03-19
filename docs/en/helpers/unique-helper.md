[Back to Contents](../index.md)

**EN** | [UK](../../uk/helpers/unique-helper.md) | [RU](../../ru/helpers/unique-helper.md)
#  UniqueHelper

`UniqueHelper` is a utility class for generating unique identifiers in the Scaleum Framework.

##  Purpose

- Generation of hashed UID strings
- Creation of numeric prefixes

##  Main methods

| Method | Purpose |
|:------|:--------|
| `getUniqueID(?string $prefix = NULL)` | Generate a unique ID with a prefix |
| `getUniquePrefix(int $prefix_size = 32)` | Generate a numeric prefix |

##  Usage examples

###  Generating a unique ID

```php
$uid = UniqueHelper::getUniqueID('order_');
// Example: "order_fa23c6d7823f8aee4d6f435ebfcaa102"
```

###  Generating a numeric prefix

```php
$prefix = UniqueHelper::getUniquePrefix(16);
// Output: "3754891278345621"
```
[Back to Contents](../index.md)