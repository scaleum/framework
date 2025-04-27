[Вернуться к оглавлению](../index.md)
# PathHelper

`PathHelper` — это утилитарный класс для манипуляции файловыми путями.

## Назначение

- Удаление перекрывающихся сегментов в путях
- Получение относительного пути
- Собирание путей из частей
- Определение директории исполняемого скрипта

## Основные методы

| Метод | Назначение |
|:------|:-----------|
| `overlapPath(string $path, mixed $overlap = null)` | Удаление перекрывающихся сегментов из пути |
| `relativePath(string $path, string $to)` | Вычисление относительного пути |
| `join(...$parts)` | Собирание пути из частей |
| `getScriptDir()` | Определение директории скрипта |

## Примеры использования

### Удаление перекрывающихся сегментов из пути

```php
$result = PathHelper::overlapPath('/var/www/project/app', '/var/www');
// /project/app
```

### Вычисление относительного пути

```php
$relative = PathHelper::relativePath('/var/www/project/index.php', '/var/www/storage/logs/error.log');
// ../storage/logs/error.log
```

### Собирание пути из частей

```php
$fullPath = PathHelper::join('var', 'www', 'html', 'index.php');
```

### Определение директории скрипта

```php
$dir = PathHelper::getScriptDir();
```

[Вернуться к оглавлению](../index.md)