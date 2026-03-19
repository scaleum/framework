[Back to Contents](../index.md)

**EN** | [UK](../../uk/components/config.md) | [RU](../../ru/components/config.md)
#  Config

The `Config` component in Scaleum provides convenient loading, merging, and management of application configurations.

##  Purpose

- Loading configurations from different file formats
- Merging multiple configurations into a single structure
- Working with configurations as a nested registry (`Registry`)
- Supporting configuration extension through environment (`env`)
- Flexible substitution of format loaders via `LoaderDispatcher`

##  Main Components

| Class | Purpose |
|:------|:--------|
| `Config` | Working with configuration at the application level |
| `LoaderResolver` | Loading configurations from files/directories |
| `LoaderDispatcher` | Manager of loaders for different formats (`php`, `ini`, `json`, `xml`) |

##  Key Features

- Loading a single configuration from a file (`fromFile`)
- Loading and merging multiple files (`fromFiles`)
- Automatic substitution of environment configurations
- Unified access to parameters via `get()` and `set()`
- Using nested keys with a delimiter (`.`)

##  Usage Examples

####  Loading configuration from a single file

```php
$config = new Config();
$config->fromFile('/config/app.php');
```

####  Loading multiple configuration files
```php
$config = new Config();
$config->fromFiles([
    '/config/database.php',
    '/config/cache.php',
]);
```

####  Accessing configuration values
```php
$dbHost = $config->get('database.host');
$config->set('app.debug', true);
```

####  Loading configurations from a directory
```php
$resolver = new LoaderResolver();
$data = $resolver->fromDir('/config');
```

##  Environment (env) Support
If `env` is set, for example *production*, and the file `/config/database.php` exists, then additionally `/config/production/database.php` will be loaded and merged.
```php
$resolver = new LoaderResolver('production');
$config = new Config([], '.', $resolver);

$config->fromFile('/config/database.php');
```

##  Loading Structure
1. `LoaderResolver`
    - Determines file type by extension.
    - Uses the corresponding loader (PHP, JSON, INI, XML).
    - If environment is present, attempts to load environment-specific file.

2. `LoaderDispatcher`
    - Registers loaders (phparray, json, ini, xml).
    - Creates loader on demand.

##  `Config` Methods
Method | Purpose
|:------|:--------|
`fromFile(string $filename, ?string $key = null): self` | Load configuration from a file
`fromFiles(array $files, ?string $key = null): self` | Load and merge multiple files
`setResolver(LoaderResolver $resolver): self` | Set configuration loader
`getResolver(): LoaderResolver` | Get current loader
Inherited from `Registry` | get(), set(), has(), delete(), merge()

##  Full Usage Example
```php
use Scaleum\Config\Config;
use Scaleum\Config\LoaderResolver;

$resolver = new LoaderResolver('production');

$config = new Config([], '.', $resolver);

// Loading from files
$config->fromFiles([
    '/config/app.php',
    '/config/database.php',
]);

// Working with configuration
if ($config->get('app.debug')) {
    echo "Debug mode is enabled";
}

// Getting database host
$dbHost = $config->get('database.host');
```

##  Errors
Exception | Condition
|:------|:--------|
`ERuntimeError` | Attempt to load unsupported file type

[Back to Contents](../index.md)