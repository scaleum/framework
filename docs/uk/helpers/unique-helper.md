[Повернутись до змісту](../index.md)

[EN](../../en/helpers/unique-helper.md) | **UK** | [RU](../../ru/helpers/unique-helper.md)
# UniqueHelper

`UniqueHelper` — утилітарний клас для генерації унікальних ідентифікаторів у Scaleum Framework.

## Призначення

- Генерація хешованих рядків UID
- Створення префіксів з чисел

## Основні методи

| Метод | Призначення |
|:------|:------------|
| `getUniqueID(?string $prefix = NULL)` | Генерація унікального ID з префіксом |
| `getUniquePrefix(int $prefix_size = 32)` | Генерація числового префікса |

## Приклади використання

### Генерація унікального ID

```php
$uid = UniqueHelper::getUniqueID('order_');
// Приклад: "order_fa23c6d7823f8aee4d6f435ebfcaa102"
```

### Генерація числового префікса

```php
$prefix = UniqueHelper::getUniquePrefix(16);
// Вивід: "3754891278345621"
```
[Повернутись до змісту](../index.md)