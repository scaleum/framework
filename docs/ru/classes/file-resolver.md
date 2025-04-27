[Вернуться к оглавлению](../index.md)
# FileResolver

**FileResolver** — класс для поиска и разрешения файлов по зарегистрированным путям.

## Назначение

- Разрешение относительных путей файлов
- Поиск файлов по списку базовых директорий
- Динамическое добавление и удаление путей поиска

## Основные возможности

| Метод | Назначение |
|:------|:-----------|
| `resolve(string $filename)` | Поиск файла в зарегистрированных путях |
| `addPath(string\|array $str, bool $unshift = true)` | Добавление пути или списка путей |
| `getPaths()` | Получение всех зарегистрированных путей |
| `setPaths(array $array)` | Установка списка путей |
| `deletePath(string\|array $str)` | Удаление пути или списка путей |

## Примеры использования

### Поиск файла

```php
$resolver = new FileResolver();
$resolver->addPath('/var/www/project/config');

$file = $resolver->resolve('app.php');
if ($file !== false) {
    echo "Файл найден: $file";
}
```

### Добавление нескольких путей

```php
$resolver->addPath(['/var/www/project/config', '/var/www/shared/config']);
```

### Удаление пути

```php
$resolver->deletePath('/var/www/project/config');
```

### Установка списка путей напрямую

```php
$resolver->setPaths(['/var/www/project/config', '/var/www/shared/config']);
```

## Исключения

- Отдельные исключения не выбрасываются; результат поиска — `false`, если файл не найден.

[Вернуться к оглавлению](../index.md)