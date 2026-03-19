[Повернутись до змісту](../index.md)

[EN](../../en/helpers/path-helper.md) | **UK** | [RU](../../ru/helpers/path-helper.md)
# PathHelper

`PathHelper` — це утилітарний клас для маніпуляції файловими шляхами.

## Призначення

- Видалення перекриваючихся сегментів у шляхах
- Отримання відносного шляху
- Збирання шляхів з частин
- Визначення директорії виконуваного скрипта

## Основні методи

| Метод | Призначення |
|:------|:-----------|
| `overlapPath(string $path, mixed $overlap = null)` | Видалення перекриваючихся сегментів із шляху |
| `relativePath(string $path, string $to)` | Обчислення відносного шляху |
| `join(...$parts)` | Збирання шляху з частин |
| `getScriptDir()` | Визначення директорії скрипта |

## Приклади використання

### Видалення перекриваючихся сегментів із шляху

```php
$result = PathHelper::overlapPath('/var/www/project/app', '/var/www');
// /project/app
```

### Обчислення відносного шляху

```php
$relative = PathHelper::relativePath('/var/www/project/index.php', '/var/www/storage/logs/error.log');
// ../storage/logs/error.log
```

### Збирання шляху з частин

```php
$fullPath = PathHelper::join('var', 'www', 'html', 'index.php');
```

### Визначення директорії скрипта

```php
$dir = PathHelper::getScriptDir();
```

[Повернутись до змісту](../index.md)