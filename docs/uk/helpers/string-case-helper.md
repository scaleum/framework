[Повернутись до змісту](../index.md)

[EN](../../en/helpers/string-case-helper.md) | **UK** | [RU](../../ru/helpers/string-case-helper.md)
# StringCaseHelper

`StringCaseHelper` — утилітний клас для роботи з регістрами та перетворенням рядків у Scaleum Framework.

## Призначення

- Конвертація рядків у `CamelCase`, `SnakeCase`, `PascalCase`
- Перевірка формату рядка
- Розбиття рядків за регістром або роздільником

## Основні методи

| Метод | Призначення |
|:------|:-----------|
| `camelize($str)` | Перетворює рядок у `camelCase` |
| `humanize($str)` | Перетворює рядок у читабельний формат |
| `isCamelCase(string $string)` | Перевіряє, чи є рядок `camelCase` |
| `isSnakeCase(string $string)` | Перевіряє, чи є рядок `snake_case` |
| `isPascalCase(string $string)` | Перевіряє, чи є рядок `PascalCase` |
| `splitString(string $string, string $delimiter = '.')` | Розбиває рядок на частини за заданим роздільником |

## Приклади використання

### Перетворення рядка у camelCase

```php
$camel = StringCaseHelper::camelize('path_to_folder'); // pathToFolder
```

### Перетворення рядка у читабельну форму

```php
$human = StringCaseHelper::humanize('path_to_folder'); // Path To Folder
```

### Перевірка, чи є рядок camelCase

```php
$isCamel = StringCaseHelper::isCamelCase('pathToFolder'); // true
```

### Перевірка, чи є рядок snake_case

```php
$isSnake = StringCaseHelper::isSnakeCase('path_to_folder'); // true
```

### Перевірка, чи є рядок PascalCase

```php
$isPascal = StringCaseHelper::isPascalCase('PathToFolder'); // true
```

### Розбиття рядка з використанням іншого роздільника

```php
$split = StringCaseHelper::splitString('PathToFolder', '_'); // path_to_folder
```

[Повернутись до змісту](../index.md)