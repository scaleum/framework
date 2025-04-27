[Вернуться к оглавлению](../index.md)
# UniqueHelper

`UniqueHelper` — утилитарный класс для генерации уникальных идентификаторов в Scaleum Framework.

## Назначение

- Генерация хэшированных строк UID
- Создание префиксов из чисел

## Основные методы

| Метод | Назначение |
|:------|:-----------|
| `getUniqueID(?string $prefix = NULL)` | Генерация уникального ID c префиксом |
| `getUniquePrefix(int $prefix_size = 32)` | Генерация числового префикса |

## Примеры использования

### Генерация уникального ID

```php
$uid = UniqueHelper::getUniqueID('order_');
// Пример: "order_fa23c6d7823f8aee4d6f435ebfcaa102"
```

### Генерация числового префикса

```php
$prefix = UniqueHelper::getUniquePrefix(16);
// Вывод: "3754891278345621"
```
[Вернуться к оглавлению](../index.md)