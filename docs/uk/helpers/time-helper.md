[Вернутися до змісту](../index.md)

[EN](../../en/helpers/time-helper.md) | **UK** | [RU](../../ru/helpers/time-helper.md)
# TimeHelper

`TimeHelper` — утилітарний клас для роботи з часом у Scaleum Framework.

## Константи

| Ім'я | Значення | Опис |
|:----|:----|:----|
| `Second` | 1 | Секунда |
| `Minute` | 60 | Хвилина (60 секунд) |
| `Hour` | 3600 | Година (60 хвилин) |
| `Day` | 86400 | Добa (24 години) |

## Основні методи

| Метод | Призначення |
|:------|:-----------|
| `getEndOfDayTimestamp(int $offset = 0)` | Отримання часової мітки кінця поточного дня |
| `getBeginOfDayTimestamp(int $offset = 0)` | Отримання часової мітки початку поточного дня |
| `getUnixtimeWithOffset(int $unixtime, string $interval)` | Зсув Unix-мітки на відносний інтервал |
| `getTimestampDiff(int $fromTimestamp, int $toTimestamp, string $unit = 'second', bool $absolute = true): int` | Різниця між двома мітками часу в обраній одиниці |

## Підтримувані одиниці для `getTimestampDiff`

- `year`, `years`, `y`
- `month`, `months`, `mo`
- `week`, `weeks`, `w`
- `day`, `days`, `d`
- `hour`, `hours`, `h`
- `minute`, `minutes`, `m`
- `second`, `seconds`, `s`

## Приклади використання

### Отримання кінця сьогоднішнього дня

```php
$timestamp = TimeHelper::getEndOfDayTimestamp();
// поверне таймстамп на 23:59:59 поточного дня
```

### Зсув дня на задану кількість секунд

```php
$timestamp = TimeHelper::getEndOfDayTimestamp(TimeHelper::Day);
// час завтрашнього 23:59:59
```

### Різниця між двома мітками часу в годинах

```php
$from = strtotime('2026-01-01 12:00:00');
$to = strtotime('2026-01-01 10:00:00');

$diff = TimeHelper::getTimestampDiff($from, $to, 'hour', false);
// поверне -2
```

### Різниця між двома мітками часу в місяцях

```php
$from = strtotime('2026-01-15 00:00:00');
$to = strtotime('2026-04-15 00:00:00');

$diff = TimeHelper::getTimestampDiff($from, $to, 'month');
// поверне 3
```

[Вернутися до змісту](../index.md)