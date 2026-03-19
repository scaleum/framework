[Back to Contents](../index.md)

**EN** | [UK](../../uk/helpers/string-helper.md) | [RU](../../ru/helpers/string-helper.md)
#  StringHelper

`StringHelper` is a set of methods for working with strings in the Scaleum Framework.

##  Purpose

- Checking strings for serialization and ASCII
- Cleaning invisible characters
- Limiting string length
- Handling class names

##  Main methods

| Method | Purpose |
|:------|:--------|
| `isSerialized(string $str)` | Checks if a string is serialized |
| `isSerializable(mixed $data)` | Checks if data is serializable |
| `isAscii(string $str)` | Checks if a string consists only of ASCII characters |
| `cleanInvisibleChars(string $str, bool $isUrlEncoded = true)` | Removes invisible characters |
| `clearCRLF(string $str)` | Removes CRLF |
| `className(mixed $class, bool $basename = false)` | Gets the class name |
| `normalizeName(string $name)` | Normalizes a name |
| `limitLength(string $string, int $length, string $replacement = '...')` | Limits string length |
| `throwPregError(int $code)` | Throws an exception for preg errors |

##  Usage examples

###  Checking if a string is serialized

```php
$isSerialized = StringHelper::isSerialized('a:1:{i:0;s:3:"foo";}');
```

###  Checking if an array is serializable

```php
$isSerializable = StringHelper::isSerializable(['key' => 'value']);
```

###  Checking if a string is ASCII

```php
$isAscii = StringHelper::isAscii('Hello World');
```

###  Removing invisible characters

```php
$clean = StringHelper::cleanInvisibleChars("Hello\0 World");
```

###  Removing CRLF

```php
$clear = StringHelper::clearCRLF("Line1\r\nLine2");
```

###  Getting the base class name

```php
$className = StringHelper::className(MyNamespace\MyClass::class, true); // returns MyClass::class
```

###  Normalizing a name

```php
$name = StringHelper::normalizeName('My-Class_Name'); // returns 'MyClassName'
```

###  Limiting string length

```php
$limited = StringHelper::limitLength('Very long string exceeding length', 10);
```

[Back to Contents](../index.md)