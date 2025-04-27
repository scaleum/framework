[Вернуться к оглавлению](../index.md)
# TimezoneHelper

`TimezoneHelper` — утилитарный класс для работы с часовыми поясами и временными зонами в Scaleum Framework.

## Назначение

- Конвертация между UTC и локальным временем
- Получение смещения временной зоны
- Справочник часовых поясов

## Основные методы

| Метод | Назначение |
|:------|:-----------|
| `UTCFromLocal($timestamp, $timezone)` | Конвертация времени из локального в UTC |
| `UTCToLocal($timestamp, $timezone)` | Конвертация времени из UTC в локальную зону |
| `timezoneAssoc($tz = '')` | Получение описания часового пояса |
| `timezoneOffset($tz)` | Получение смещения часового пояса |

## Примеры использования

### Конвертация локального времени в UTC

```php
$utcTimestamp = TimezoneHelper::UTCFromLocal(time(), 'UTC+3');
```

### Конвертация времени UTC в локальную зону

```php
$localTimestamp = TimezoneHelper::UTCToLocal(time(), 'UTC+2');
```

### Получение описания часового пояса

```php
$info = TimezoneHelper::timezoneAssoc('UTC+3');
```

### Получение смещения часового пояса

```php
$offset = TimezoneHelper::timezoneOffset('UTC+3'); // 3
```

[Вернуться к оглавлению](../index.md)