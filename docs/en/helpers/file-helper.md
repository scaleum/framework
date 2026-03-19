[Back to Contents](../index.md)

**EN** | [UK](../../uk/helpers/file-helper.md) | [RU](../../ru/helpers/file-helper.md)
#  FileHelper

`FileHelper` is a utility class for working with files and directories in the Scaleum Framework.

##  Purpose

- Deleting, reading, and writing files
- Working with access permissions
- Normalizing and preparing paths and file names
- Determining MIME type

##  Constants

| Name | Value | Description |
|:----|:----|:----|
| `FILE_READ_MODE` | 0644 | File read mode |
| `FILE_WRITE_MODE` | 0666 | File write mode |
| `DIR_READ_MODE` | 0755 | Directory read mode |
| `DIR_WRITE_MODE` | 0777 | Directory write mode |

##  Main Methods

| Method | Purpose |
|:------|:-----------|
| `deleteFile(string $filename)` | Delete a file |
| `deleteFiles(string $path, bool $deleteDir = false, int $level = 0)` | Recursive deletion of files and folders |
| `getDir(string $path, bool $onlyTop = true, bool $recursion = false)` | Get list of files in a directory |
| `getFileExtension(string $file)` | Get file extension |
| `getFileInfo(string $file, array $returnedValues = ['name', 'path', 'size', 'type'])` | Get file information |
| `getFileType(string $filename)` | Determine file MIME type |
| `getFiles(string $sourceDir, bool $includePath = false, bool $recursion = false)` | Get list of files |
| `isReallyWritable(string $file)` | Check if writable |
| `octalPermissions(int $perms)` | Permissions in octal format |
| `symbolicPermissions(mixed $perms)` | Permissions in symbolic format |
| `prepFilename(string $filename, bool $normalize = true)` | Prepare file name |
| `prepPath(string $path, bool $normalize = true)` | Prepare path |
| `prepLocation(string $location)` | Normalize path |
| `readFile(string $file)` | Read file contents |
| `writeFile(string $file, mixed $data, string $mode)` | Write to file |
| `flushFile(string $file)` | Clear file contents |

##  Usage Examples

###  Deleting a file

```php
FileHelper::deleteFile('/path/to/file.txt');
```

###  Recursive deletion of files and folders

```php
FileHelper::deleteFiles('/path/to/folder', true);
```

###  Getting list of files in a directory

```php
$dirs = FileHelper::getDir('/path/to/folder');
```

###  Getting file extension

```php
$ext = FileHelper::getFileExtension('image.jpg');
```

###  Getting file information

```php
$info = FileHelper::getFileInfo('/path/to/file.txt');
```

###  Determining file MIME type

```php
$type = FileHelper::getFileType('/path/to/file.jpg');
```

###  Getting list of files

```php
$files = FileHelper::getFiles('/path/to/folder', true);
```

###  Checking if writable

```php
if (FileHelper::isReallyWritable('/path/to/file.txt')) {
    // File is writable
}
```

###  Permissions in octal format

```php
$octal = FileHelper::octalPermissions(0755);
```

###  Permissions in symbolic format

```php
$symbolic = FileHelper::symbolicPermissions(0755);
```

###  Preparing file name

```php
$filename = FileHelper::prepFilename('uploads/image');
```

###  Preparing path

```php
$path = FileHelper::prepPath('/uploads/');
```

###  Normalizing path

```php
$location = FileHelper::prepLocation('uploads\\images');
```

###  Reading file contents

```php
$content = FileHelper::readFile('/path/to/file.txt');
```

###  Writing to file

```php
FileHelper::writeFile('/path/to/file.txt', 'Hello World!');
```

###  Clearing file contents

```php
FileHelper::flushFile('/path/to/file.txt');
```

[Back to Contents](../index.md)