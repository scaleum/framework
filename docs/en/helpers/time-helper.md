[Back to Contents](../index.md)

**EN** | [UK](../../uk/helpers/time-helper.md) | [RU](../../ru/helpers/time-helper.md)
#  TimeHelper

`TimeHelper` is a utility class for working with time in the Scaleum Framework.

##  Constants

| Name | Value | Description |
|:----|:----|:----|
| `Second` | 1 | Second |
| `Minute` | 60 | Minute (60 seconds) |
| `Hour` | 3600 | Hour (60 minutes) |
| `Day` | 86400 | Day (24 hours) |

##  Main Methods

| Method | Purpose |
|:------|:-----------|
| `getEndOfDayTimestamp(int $offset = 0)` | Get the timestamp for the end of the current day |

##  Usage Examples

###  Getting the end of today

```php
$timestamp = TimeHelper::getEndOfDayTimestamp();
// returns the timestamp for 23:59:59 of the current day
```

###  Shifting the day by a specified number of seconds

```php
$timestamp = TimeHelper::getEndOfDayTimestamp(TimeHelper::Day);
// time for tomorrow at 23:59:59
```

[Back to Contents](../index.md)