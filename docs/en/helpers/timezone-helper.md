[Back to Contents](../index.md)

**EN** | [UK](../../uk/helpers/timezone-helper.md) | [RU](../../ru/helpers/timezone-helper.md)
#  TimezoneHelper

`TimezoneHelper` is a utility class for working with time zones and time offsets in the Scaleum Framework.

##  Purpose

- Conversion between UTC and local time
- Obtaining time zone offset
- Time zone reference

##  Main Methods

| Method | Purpose |
|:------|:--------|
| `UTCFromLocal($timestamp, $timezone)` | Convert time from local to UTC |
| `UTCToLocal($timestamp, $timezone)` | Convert time from UTC to local zone |
| `timezoneAssoc($tz = '')` | Get time zone description |
| `timezoneOffset($tz)` | Get time zone offset |

##  Usage Examples

###  Convert local time to UTC

```php
$utcTimestamp = TimezoneHelper::UTCFromLocal(time(), 'UTC+3');
```

###  Convert UTC time to local zone

```php
$localTimestamp = TimezoneHelper::UTCToLocal(time(), 'UTC+2');
```

###  Get time zone description

```php
$info = TimezoneHelper::timezoneAssoc('UTC+3');
```

###  Get time zone offset

```php
$offset = TimezoneHelper::timezoneOffset('UTC+3'); // 3
```

[Back to Contents](../index.md)