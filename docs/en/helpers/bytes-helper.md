[Back to Contents](../index.md)

**EN** | [UK](../../uk/helpers/bytes-helper.md) | [RU](../../ru/helpers/bytes-helper.md)
#  BytesHelper

`BytesHelper` is a utility class for working with data sizes in bytes, kilobytes, and other units.

##  Purpose

- Convert bytes to a human-readable format
- Convert between different storage units
- Extract numeric value from a size string
- Format size into a string

##  Main Methods

| Method | Purpose |
|:------|:--------|
| `bytesAssoc(int $size): mixed` | Convert bytes to an array `[size, unit]` |
| `bytesStr(int $size, string $format = '%d%s'): mixed` | Convert bytes to a formatted string |
| `bytesNumber(string $str): int\|float` | Extract the number of bytes from a string with a unit |
| `bytesTo(int $size, string $from = 'b', string $to = 'kb'): float` | Convert between units |

##  Supported Units

- `b` — bytes
- `kb` — kilobytes
- `mb` — megabytes
- `gb` — gigabytes
- `tb` — terabytes
- `pb` — petabytes
- `eb` — exabytes
- `zb` — zettabytes
- `yb` — yottabytes

---

##  Usage Examples

###  Convert bytes to string

```php
echo BytesHelper::bytesStr(1048576); // "1mb"
```

###  Get array: value + unit
```php
$result = BytesHelper::bytesAssoc(1536);
// [1.5, 'kb']
```

###  Convert size string to bytes
```php
$bytes = BytesHelper::bytesNumber('10MB');
// 10485760
```

###  Convert between units
```php
$sizeInGb = BytesHelper::bytesTo(1048576, 'kb', 'gb');
// 1
```

##  Features
- Smart conversion of strings without strict case sensitivity (`10Mb`, `10mb`, `10MB` — all valid).
- Returns 0 if an invalid unit is passed to `bytesNumber()`.
- Internal rounding of values to three decimal places (`round(..., 3)`).

[Back to Contents](../index.md)