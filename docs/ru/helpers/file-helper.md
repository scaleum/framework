[Вернуться к оглавлению](../index.md)
# FileHelper

`FileHelper` — утилитарный класс для работы с файлами и директориями в Scaleum Framework.

## Назначение

- Удаление, чтение и запись файлов
- Работа с правами доступа
- Нормализация и подготовка путей и имен файлов
- Определение MIME-типа

## Константы

| Имя | Значение | Описание |
|:----|:----|:----|
| `FILE_READ_MODE` | 0644 | Режим чтения файла |
| `FILE_WRITE_MODE` | 0666 | Режим записи в файл |
| `DIR_READ_MODE` | 0755 | Чтение директорий |
| `DIR_WRITE_MODE` | 0777 | Запись в директории |

## Основные методы

| Метод | Назначение |
|:------|:-----------|
| `deleteFile(string $filename)` | Удаление файла |
| `deleteFiles(string $path, bool $deleteDir = false, int $level = 0)` | Рекурсивное удаление файлов и папок |
| `getDir(string $path, bool $onlyTop = true, bool $recursion = false)` | Получение списка файлов в директории |
| `getFileExtension(string $file)` | Получение расширения файла |
| `getFileInfo(string $file, array $returnedValues = ['name', 'path', 'size', 'type'])` | Получение информации о файле |
| `getFileType(string $filename)` | Определение MIME-типа файла |
| `getFiles(string $sourceDir, bool $includePath = false, bool $recursion = false)` | Получение списка файлов |
| `isReallyWritable(string $file)` | Проверка возможности записи |
| `octalPermissions(int $perms)` | Права в формате octal |
| `symbolicPermissions(mixed $perms)` | Права в символьном формате |
| `prepFilename(string $filename, bool $normalize = true)` | Подготовка имени файла |
| `prepPath(string $path, bool $normalize = true)` | Подготовка пути |
| `prepLocation(string $location)` | Нормализация пути |
| `readFile(string $file)` | Чтение содержимого файла |
| `writeFile(string $file, mixed $data, string $mode)` | Запись в файл |
| `flushFile(string $file)` | Очистка содержимого файла |

## Примеры использования

### Удаление файла

```php
FileHelper::deleteFile('/path/to/file.txt');
```

### Рекурсивное удаление файлов и папок

```php
FileHelper::deleteFiles('/path/to/folder', true);
```

### Получение списка файлов в директории

```php
$dirs = FileHelper::getDir('/path/to/folder');
```

### Получение расширения файла

```php
$ext = FileHelper::getFileExtension('image.jpg');
```

### Получение информации о файле

```php
$info = FileHelper::getFileInfo('/path/to/file.txt');
```

### Определение MIME-типа файла

```php
$type = FileHelper::getFileType('/path/to/file.jpg');
```

### Получение списка файлов

```php
$files = FileHelper::getFiles('/path/to/folder', true);
```

### Проверка возможности записи

```php
if (FileHelper::isReallyWritable('/path/to/file.txt')) {
    // Файл доступен для записи
}
```

### Права в формате octal

```php
$octal = FileHelper::octalPermissions(0755);
```

### Права в символьном формате

```php
$symbolic = FileHelper::symbolicPermissions(0755);
```

### Подготовка имени файла

```php
$filename = FileHelper::prepFilename('uploads/image');
```

### Подготовка пути

```php
$path = FileHelper::prepPath('/uploads/');
```

### Нормализация пути

```php
$location = FileHelper::prepLocation('uploads\\images');
```

### Чтение содержимого файла

```php
$content = FileHelper::readFile('/path/to/file.txt');
```

### Запись в файл

```php
FileHelper::writeFile('/path/to/file.txt', 'Hello World!');
```

### Очистка содержимого файла

```php
FileHelper::flushFile('/path/to/file.txt');
```

[Вернуться к оглавлению](../index.md)