[Вернуться к оглавлению](../index.md)

[EN](../../en/helpers/time-helper.md) | [UK](../../uk/helpers/time-helper.md) | **RU**
# TimeHelper

`TimeHelper` — утилитарный класс для работы со временем в Scaleum Framework.

## Константы

| Имя | Значение | Описание |
|:----|:----|:----|
| `Second` | 1 | Секунда |
| `Minute` | 60 | Минута (60 секунд) |
| `Hour` | 3600 | Час (60 минут) |
| `Day` | 86400 | Сутки (24 часа) |

## Основные методы

| Метод | Назначение |
|:------|:-----------|
| `getEndOfDayTimestamp(int $offset = 0)` | Получение временной метки конца текущего дня |
| `getBeginOfDayTimestamp(int $offset = 0)` | Получение временной метки начала текущего дня |
| `getUnixtimeWithOffset(int $unixtime, string $interval)` | Сдвиг Unix-метки на относительный интервал |
| `getTimestampDiff(int $fromTimestamp, int $toTimestamp, string $unit = 'second', bool $absolute = true): int` | Разница между двумя метками времени в выбранной единице |

## Поддерживаемые единицы для `getTimestampDiff`

- `year`, `years`, `y`
- `month`, `months`, `mo`
- `week`, `weeks`, `w`
- `day`, `days`, `d`
- `hour`, `hours`, `h`
- `minute`, `minutes`, `m`
- `second`, `seconds`, `s`

## Примеры использования

### Получение конца сегодняшнего дня

```php
$timestamp = TimeHelper::getEndOfDayTimestamp();
// вернёт таймстамп на 23:59:59 текущего дня
```

### Сдвиг дня на заданное количество секунд

```php
$timestamp = TimeHelper::getEndOfDayTimestamp(TimeHelper::Day);
// время завтрашнего 23:59:59
```

### Разница между двумя метками времени в часах

```php
$from = strtotime('2026-01-01 12:00:00');
$to = strtotime('2026-01-01 10:00:00');

$diff = TimeHelper::getTimestampDiff($from, $to, 'hour', false);
// вернёт -2
```

### Разница между двумя метками времени в месяцах

```php
$from = strtotime('2026-01-15 00:00:00');
$to = strtotime('2026-04-15 00:00:00');

$diff = TimeHelper::getTimestampDiff($from, $to, 'month');
// вернёт 3
```

[Вернуться к оглавлению](../index.md)