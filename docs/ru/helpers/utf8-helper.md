[Вернуться к оглавлению](../index.md)
# Utf8Helper

`Utf8Helper` — это утилитарный класс для работы с строками в кодировке UTF-8 в Scaleum Framework.

## Назначение

- Удаление недопустимых знаков
- Удаление BOM из файлов
- Проверка на UTF-8
- Работа с пробелами и кодовыми отметками

## Основные методы

| Метод | Назначение |
|:------|:-----------|
| `clean(string $str, string $replacement = '?')` | Удаление не-UTF-8 знаков |
| `cleanUtf8Bom(string $str)` | Удаление BOM из строки |
| `getUtf8Bom()` | Возвращение BOM последовательности |
| `getUtf8WhiteSpaces()` | Получение набора белых знаков UTF-8 |
| `isUtf8(string $str)` | Проверка, является ли строка кодировкой UTF-8 |
| `isUtf8Bom(string $chr)` | Проверка, является ли символ BOM |
| `isUtf8Enabled()` | Проверка, включена ли поддержка UTF-8 |

## Примеры использования

### Удаление не-UTF-8 знаков

```php
$cleanString = Utf8Helper::clean("Hello\x80World");
```

### Удаление BOM

```php
$noBomString = Utf8Helper::cleanUtf8Bom($string);
```

### Проверка строки на UTF-8

```php
$isUtf8 = Utf8Helper::isUtf8($string);
```

### Проверка BOM

```php
$isBom = Utf8Helper::isUtf8Bom(substr($string, 0, 3));
```

### Получение BOM

```php
$bom = Utf8Helper::getUtf8Bom();
```

### Получение списка UTF-8 пробелов

```php
$whiteSpaces = Utf8Helper::getUtf8WhiteSpaces();
```

### Проверка, включена ли поддержка UTF-8

```php
$isUtf8Enabled = Utf8Helper::isUtf8Enabled();
```
[Вернуться к оглавлению](../index.md)