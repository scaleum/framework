[Вернутися до змісту](../index.md)

[EN](../../en/helpers/timezone-helper.md) | **UK** | [RU](../../ru/helpers/timezone-helper.md)
# TimezoneHelper

`TimezoneHelper` — утилітарний клас для роботи з часовими поясами та часовими зонами в Scaleum Framework.

## Призначення

- Конвертація між UTC та локальним часом
- Отримання зсуву часової зони
- Довідник часових поясів

## Основні методи

| Метод | Призначення |
|:------|:------------|
| `UTCFromLocal($timestamp, $timezone)` | Конвертація часу з локального в UTC |
| `UTCToLocal($timestamp, $timezone)` | Конвертація часу з UTC у локальну зону |
| `timezoneAssoc($tz = '')` | Отримання опису часового поясу |
| `timezoneOffset($tz)` | Отримання зсуву часового поясу |

## Приклади використання

### Конвертація локального часу в UTC

```php
$utcTimestamp = TimezoneHelper::UTCFromLocal(time(), 'UTC+3');
```

### Конвертація часу UTC у локальну зону

```php
$localTimestamp = TimezoneHelper::UTCToLocal(time(), 'UTC+2');
```

### Отримання опису часового поясу

```php
$info = TimezoneHelper::timezoneAssoc('UTC+3');
```

### Отримання зсуву часового поясу

```php
$offset = TimezoneHelper::timezoneOffset('UTC+3'); // 3
```

[Вернутися до змісту](../index.md)