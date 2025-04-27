[Вернуться к оглавлению](../index.md)
# StringCaseHelper

`StringCaseHelper` — утилитарный класс для работы с регистрами и преобразованием строк в Scaleum Framework.

## Назначение

- Конвертация строк в `CamelCase`, `SnakeCase`, `PascalCase`
- Проверка формата строки
- Разбиение строк по регистру или разделителю

## Основные методы

| Метод | Назначение |
|:------|:-----------|
| `camelize($str)` | Преобразует строку в `camelCase` |
| `humanize($str)` | Преобразует строку в читаемый формат |
| `isCamelCase(string $string)` | Проверяет, является ли строка `camelCase` |
| `isSnakeCase(string $string)` | Проверяет, является ли строка `snake_case` |
| `isPascalCase(string $string)` | Проверяет, является ли строка `PascalCase` |
| `splitString(string $string, string $delimiter = '.')` | Разбивает строку на части с заданным разделителем |

## Примеры использования

### Преобразование строки в camelCase

```php
$camel = StringCaseHelper::camelize('path_to_folder'); // pathToFolder
```

### Преобразование строки в читаемую форму

```php
$human = StringCaseHelper::humanize('path_to_folder'); // Path To Folder
```

### Проверка, является ли строка camelCase

```php
$isCamel = StringCaseHelper::isCamelCase('pathToFolder'); // true
```

### Проверка, является ли строка snake_case

```php
$isSnake = StringCaseHelper::isSnakeCase('path_to_folder'); // true
```

### Проверка, является ли строка PascalCase

```php
$isPascal = StringCaseHelper::isPascalCase('PathToFolder'); // true
```

### Разбиение строки с использованием другого разделителя

```php
$split = StringCaseHelper::splitString('PathToFolder', '_'); // path_to_folder
```

[Вернуться к оглавлению](../index.md)