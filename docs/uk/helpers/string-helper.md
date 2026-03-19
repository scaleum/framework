[Повернутись до змісту](../index.md)

[EN](../../en/helpers/string-helper.md) | **UK** | [RU](../../ru/helpers/string-helper.md)
# StringHelper

`StringHelper` — це набір методів для роботи зі рядками в Scaleum Framework.

## Призначення

- Перевірка рядків на серіалізацію та ASCII
- Очищення невидимих символів
- Обмеження довжини рядка
- Обробка імен класів

## Основні методи

| Метод | Призначення |
|:------|:-----------|
| `isSerialized(string $str)` | Перевірка, чи є рядок серіалізованим |
| `isSerializable(mixed $data)` | Перевірка, чи є дані серіалізованими |
| `isAscii(string $str)` | Перевірка, чи складається рядок лише з ASCII-символів |
| `cleanInvisibleChars(string $str, bool $isUrlEncoded = true)` | Видалення невидимих символів |
| `clearCRLF(string $str)` | Видалення CRLF |
| `className(mixed $class, bool $basename = false)` | Отримання імені класу |
| `normalizeName(string $name)` | Нормалізація імені |
| `limitLength(string $string, int $length, string $replacement = '...')` | Обмеження довжини рядка |
| `throwPregError(int $code)` | Викидання виключення для preg-помилки |

## Приклади використання

### Перевірка рядка на серіалізацію

```php
$isSerialized = StringHelper::isSerialized('a:1:{i:0;s:3:"foo";}');
```

### Перевірка серіалізованості масиву

```php
$isSerializable = StringHelper::isSerializable(['key' => 'value']);
```

### Перевірка рядка на ASCII

```php
$isAscii = StringHelper::isAscii('Hello World');
```

### Видалення невидимих символів

```php
$clean = StringHelper::cleanInvisibleChars("Hello\0 World");
```

### Видалення CRLF

```php
$clear = StringHelper::clearCRLF("Line1\r\nLine2");
```

### Отримання базового імені класу

```php
$className = StringHelper::className(MyNamespace\MyClass::class, true); // поверне MyClass::class
```

### Нормалізація імені

```php
$name = StringHelper::normalizeName('My-Class_Name'); // поверне 'MyClassName'
```

### Обмеження довжини рядка

```php
$limited = StringHelper::limitLength('Very long string exceeding length', 10);
```

[Повернутись до змісту](../index.md)