[Back to Contents](../index.md)

**EN** | [UK](../../uk/helpers/path-helper.md) | [RU](../../ru/helpers/path-helper.md)
#  PathHelper

`PathHelper` is a utility class for manipulating file paths.

##  Purpose

- Removing overlapping segments in paths
- Getting a relative path
- Joining paths from parts
- Determining the directory of the executing script

##  Main methods

| Method | Purpose |
|:------|:--------|
| `overlapPath(string $path, mixed $overlap = null)` | Removing overlapping segments from a path |
| `relativePath(string $path, string $to)` | Calculating a relative path |
| `join(...$parts)` | Joining a path from parts |
| `getScriptDir()` | Determining the script directory |

##  Usage examples

###  Removing overlapping segments from a path

```php
$result = PathHelper::overlapPath('/var/www/project/app', '/var/www');
// /project/app
```

###  Calculating a relative path

```php
$relative = PathHelper::relativePath('/var/www/project/index.php', '/var/www/storage/logs/error.log');
// ../storage/logs/error.log
```

###  Joining a path from parts

```php
$fullPath = PathHelper::join('var', 'www', 'html', 'index.php');
```

###  Determining the script directory

```php
$dir = PathHelper::getScriptDir();
```

[Back to Contents](../index.md)