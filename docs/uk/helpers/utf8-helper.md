[Повернутись до змісту](../index.md)

[EN](../../en/helpers/utf8-helper.md) | **UK** | [RU](../../ru/helpers/utf8-helper.md)
# Utf8Helper

`Utf8Helper` — це утилітарний клас для роботи зі рядками в кодуванні UTF-8 у Scaleum Framework.

## Призначення

- Видалення недопустимих символів
- Видалення BOM з файлів
- Перевірка на UTF-8
- Робота з пробілами та кодовими позначками

## Основні методи

| Метод | Призначення |
|:------|:-----------|
| `clean(string $str, string $replacement = '?')` | Видалення не-UTF-8 символів |
| `cleanUtf8Bom(string $str)` | Видалення BOM з рядка |
| `getUtf8Bom()` | Повернення BOM послідовності |
| `getUtf8WhiteSpaces()` | Отримання набору білих символів UTF-8 |
| `isUtf8(string $str)` | Перевірка, чи є рядок кодуванням UTF-8 |
| `isUtf8Bom(string $chr)` | Перевірка, чи є символ BOM |
| `isUtf8Enabled()` | Перевірка, чи увімкнена підтримка UTF-8 |

## Приклади використання

### Видалення не-UTF-8 символів

```php
$cleanString = Utf8Helper::clean("Hello\x80World");
```

### Видалення BOM

```php
$noBomString = Utf8Helper::cleanUtf8Bom($string);
```

### Перевірка рядка на UTF-8

```php
$isUtf8 = Utf8Helper::isUtf8($string);
```

### Перевірка BOM

```php
$isBom = Utf8Helper::isUtf8Bom(substr($string, 0, 3));
```

### Отримання BOM

```php
$bom = Utf8Helper::getUtf8Bom();
```

### Отримання списку UTF-8 пробілів

```php
$whiteSpaces = Utf8Helper::getUtf8WhiteSpaces();
```

### Перевірка, чи увімкнена підтримка UTF-8

```php
$isUtf8Enabled = Utf8Helper::isUtf8Enabled();
```
[Повернутись до змісту](../index.md)