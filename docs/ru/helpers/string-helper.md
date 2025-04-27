[Вернуться к оглавлению](../index.md)
# StringHelper

`StringHelper` — это набор методов для работы с строками в Scaleum Framework.

## Назначение

- Проверка строк на сериализацию и ASCII
- Очистка невидимых символов
- Ограничение длины строки
- Обработка имен классов

## Основные методы

| Метод | Назначение |
|:------|:-----------|
| `isSerialized(string $str)` | Проверка, является ли строка сериализованной |
| `isSerializable(mixed $data)` | Проверка, сериализуемы ли данные |
| `isAscii(string $str)` | Проверка, состоит ли строка только из ASCII-символов |
| `cleanInvisibleChars(string $str, bool $isUrlEncoded = true)` | Удаление невидимых символов |
| `clearCRLF(string $str)` | Удаление CRLF |
| `className(mixed $class, bool $basename = false)` | Получение имени класса |
| `normalizeName(string $name)` | Нормализация имени |
| `limitLength(string $string, int $length, string $replacement = '...')` | Ограничение длины строки |
| `throwPregError(int $code)` | Бросание исключения для preg-ошибки |

## Примеры использования

### Проверка строки на сериализацию

```php
$isSerialized = StringHelper::isSerialized('a:1:{i:0;s:3:"foo";}');
```

### Проверка сериализуемости массива

```php
$isSerializable = StringHelper::isSerializable(['key' => 'value']);
```

### Проверка строки на ASCII

```php
$isAscii = StringHelper::isAscii('Hello World');
```

### Удаление невидимых символов

```php
$clean = StringHelper::cleanInvisibleChars("Hello\0 World");
```

### Удаление CRLF

```php
$clear = StringHelper::clearCRLF("Line1\r\nLine2");
```

### Получение базового имени класса

```php
$className = StringHelper::className(MyNamespace\MyClass::class, true); // вернет MyClass::class
```

### Нормализация имени

```php
$name = StringHelper::normalizeName('My-Class_Name'); // вернет 'MyClassName'
```

### Ограничение длины строки

```php
$limited = StringHelper::limitLength('Very long string exceeding length', 10);
```

[Вернуться к оглавлению](../index.md)