[Back to Contents](../index.md)

**EN** | [UK](../../uk/helpers/type-helper.md) | [RU](../../ru/helpers/type-helper.md)
#  TypeHelper

`TypeHelper` is a utility class for determining variable types and checking type compliance in the Scaleum Framework.

##  Purpose

- Determining the type of a value
- Checking the type of a variable

##  Main Types

| Constant | Description |
|:------|:-----------|
| `TYPE_ARRAY` | Array |
| `TYPE_BOOL` | Boolean type |
| `TYPE_CALLABLE` | Callable type |
| `TYPE_FLOAT` | Floating point number |
| `TYPE_INT` | Integer |
| `TYPE_NULL` | null |
| `TYPE_NUMERIC` | Numeric type |
| `TYPE_OBJECT` | Object |
| `TYPE_RESOURCE` | Resource |
| `TYPE_STRING` | String |

##  Main Methods

| Method | Purpose |
|:------|:-----------|
| `getType($var)` | Determine the type of a variable |
| `isType($var, $type)` | Check if the type matches |

##  Usage Examples

###  Determining the type of a variable

```php
$type = TypeHelper::getType(123); // int
```

###  Checking if type is array

```php
$isArray = TypeHelper::isType([1,2,3], TypeHelper::TYPE_ARRAY); // true
```

[Back to Contents](../index.md)