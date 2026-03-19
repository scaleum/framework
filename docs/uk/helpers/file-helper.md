[Повернутись до змісту](../index.md)

[EN](../../en/helpers/file-helper.md) | **UK** | [RU](../../ru/helpers/file-helper.md)
# FileHelper

`FileHelper` — утилітарний клас для роботи з файлами та директоріями в Scaleum Framework.

## Призначення

- Видалення, читання та запис файлів
- Робота з правами доступу
- Нормалізація та підготовка шляхів і імен файлів
- Визначення MIME-типу

## Константи

| Ім'я | Значення | Опис |
|:----|:----|:----|
| `FILE_READ_MODE` | 0644 | Режим читання файлу |
| `FILE_WRITE_MODE` | 0666 | Режим запису у файл |
| `DIR_READ_MODE` | 0755 | Читання директорій |
| `DIR_WRITE_MODE` | 0777 | Запис у директорії |

## Основні методи

| Метод | Призначення |
|:------|:-----------|
| `deleteFile(string $filename)` | Видалення файлу |
| `deleteFiles(string $path, bool $deleteDir = false, int $level = 0)` | Рекурсивне видалення файлів і папок |
| `getDir(string $path, bool $onlyTop = true, bool $recursion = false)` | Отримання списку файлів у директорії |
| `getFileExtension(string $file)` | Отримання розширення файлу |
| `getFileInfo(string $file, array $returnedValues = ['name', 'path', 'size', 'type'])` | Отримання інформації про файл |
| `getFileType(string $filename)` | Визначення MIME-типу файлу |
| `getFiles(string $sourceDir, bool $includePath = false, bool $recursion = false)` | Отримання списку файлів |
| `isReallyWritable(string $file)` | Перевірка можливості запису |
| `octalPermissions(int $perms)` | Права у форматі octal |
| `symbolicPermissions(mixed $perms)` | Права у символьному форматі |
| `prepFilename(string $filename, bool $normalize = true)` | Підготовка імені файлу |
| `prepPath(string $path, bool $normalize = true)` | Підготовка шляху |
| `prepLocation(string $location)` | Нормалізація шляху |
| `readFile(string $file)` | Читання вмісту файлу |
| `writeFile(string $file, mixed $data, string $mode)` | Запис у файл |
| `flushFile(string $file)` | Очищення вмісту файлу |

## Приклади використання

### Видалення файлу

```php
FileHelper::deleteFile('/path/to/file.txt');
```

### Рекурсивне видалення файлів і папок

```php
FileHelper::deleteFiles('/path/to/folder', true);
```

### Отримання списку файлів у директорії

```php
$dirs = FileHelper::getDir('/path/to/folder');
```

### Отримання розширення файлу

```php
$ext = FileHelper::getFileExtension('image.jpg');
```

### Отримання інформації про файл

```php
$info = FileHelper::getFileInfo('/path/to/file.txt');
```

### Визначення MIME-типу файлу

```php
$type = FileHelper::getFileType('/path/to/file.jpg');
```

### Отримання списку файлів

```php
$files = FileHelper::getFiles('/path/to/folder', true);
```

### Перевірка можливості запису

```php
if (FileHelper::isReallyWritable('/path/to/file.txt')) {
    // Файл доступний для запису
}
```

### Права у форматі octal

```php
$octal = FileHelper::octalPermissions(0755);
```

### Права у символьному форматі

```php
$symbolic = FileHelper::symbolicPermissions(0755);
```

### Підготовка імені файлу

```php
$filename = FileHelper::prepFilename('uploads/image');
```

### Підготовка шляху

```php
$path = FileHelper::prepPath('/uploads/');
```

### Нормалізація шляху

```php
$location = FileHelper::prepLocation('uploads\\images');
```

### Читання вмісту файлу

```php
$content = FileHelper::readFile('/path/to/file.txt');
```

### Запис у файл

```php
FileHelper::writeFile('/path/to/file.txt', 'Hello World!');
```

### Очищення вмісту файлу

```php
FileHelper::flushFile('/path/to/file.txt');
```

[Повернутись до змісту](../index.md)