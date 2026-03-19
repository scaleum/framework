[Back to Contents](../index.md)

**EN** | [UK](../../uk/helpers/utf8-helper.md) | [RU](../../ru/helpers/utf8-helper.md)
#  Utf8Helper

`Utf8Helper` is a utility class for working with UTF-8 encoded strings in the Scaleum Framework.

##  Purpose

- Removing invalid characters
- Removing BOM from files
- Checking for UTF-8
- Working with spaces and code marks

##  Main methods

| Method | Purpose |
|:------|:--------|
| `clean(string $str, string $replacement = '?')` | Removing non-UTF-8 characters |
| `cleanUtf8Bom(string $str)` | Removing BOM from a string |
| `getUtf8Bom()` | Returning the BOM sequence |
| `getUtf8WhiteSpaces()` | Getting the set of UTF-8 white spaces |
| `isUtf8(string $str)` | Checking if a string is UTF-8 encoded |
| `isUtf8Bom(string $chr)` | Checking if a character is BOM |
| `isUtf8Enabled()` | Checking if UTF-8 support is enabled |

##  Usage examples

###  Removing non-UTF-8 characters

```php
$cleanString = Utf8Helper::clean("Hello\x80World");
```

###  Removing BOM

```php
$noBomString = Utf8Helper::cleanUtf8Bom($string);
```

###  Checking a string for UTF-8

```php
$isUtf8 = Utf8Helper::isUtf8($string);
```

###  Checking BOM

```php
$isBom = Utf8Helper::isUtf8Bom(substr($string, 0, 3));
```

###  Getting BOM

```php
$bom = Utf8Helper::getUtf8Bom();
```

###  Getting the list of UTF-8 white spaces

```php
$whiteSpaces = Utf8Helper::getUtf8WhiteSpaces();
```

###  Checking if UTF-8 support is enabled

```php
$isUtf8Enabled = Utf8Helper::isUtf8Enabled();
```
[Back to Contents](../index.md)