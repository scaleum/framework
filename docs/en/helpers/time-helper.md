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
| `getBeginOfDayTimestamp(int $offset = 0)` | Get the timestamp for the beginning of the current day |
| `getUnixtimeWithOffset(int $unixtime, string $interval)` | Shift a Unix timestamp by a relative interval |
| `getTimestampDiff(int $fromTimestamp, int $toTimestamp, string $unit = 'second', bool $absolute = true): int` | Get difference between two timestamps in the selected unit |

##  Supported Units For `getTimestampDiff`

- `year`, `years`, `y`
- `month`, `months`, `mo`
- `week`, `weeks`, `w`
- `day`, `days`, `d`
- `hour`, `hours`, `h`
- `minute`, `minutes`, `m`
- `second`, `seconds`, `s`

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

###  Difference between two timestamps in hours

```php
$from = strtotime('2026-01-01 12:00:00');
$to = strtotime('2026-01-01 10:00:00');

$diff = TimeHelper::getTimestampDiff($from, $to, 'hour', false);
// returns -2
```

###  Difference between two timestamps in months

```php
$from = strtotime('2026-01-15 00:00:00');
$to = strtotime('2026-04-15 00:00:00');

$diff = TimeHelper::getTimestampDiff($from, $to, 'month');
// returns 3
```

[Back to Contents](../index.md)