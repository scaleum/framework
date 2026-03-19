[Back to Contents](../index.md)

**EN** | [UK](../../uk/classes/file-resolver.md) | [RU](../../ru/classes/file-resolver.md)
#  FileResolver

**FileResolver** — a class for searching and resolving files by registered paths.

##  Purpose

- Resolving relative file paths
- Searching files through a list of base directories
- Dynamically adding and removing search paths

##  Main Features

| Method | Purpose |
|:------|:-----------|
| `resolve(string $filename)` | Search for a file in registered paths |
| `addPath(string\|array $str, bool $unshift = true)` | Add a path or list of paths |
| `getPaths()` | Get all registered paths |
| `setPaths(array $array)` | Set the list of paths |
| `deletePath(string\|array $str)` | Remove a path or list of paths |

##  Usage Examples

###  File Search

```php
$resolver = new FileResolver();
$resolver->addPath('/var/www/project/config');

$file = $resolver->resolve('app.php');
if ($file !== false) {
    echo "File found: $file";
}
```

###  Adding Multiple Paths

```php
$resolver->addPath(['/var/www/project/config', '/var/www/shared/config']);
```

###  Removing a Path

```php
$resolver->deletePath('/var/www/project/config');
```

###  Setting the List of Paths Directly

```php
$resolver->setPaths(['/var/www/project/config', '/var/www/shared/config']);
```

##  Exceptions

- No specific exceptions are thrown; the search result is `false` if the file is not found.

[Back to Contents](../index.md)