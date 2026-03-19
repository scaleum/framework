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

[Вернутися до змісту](../index.md)