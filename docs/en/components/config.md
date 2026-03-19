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
- Strict typed access via `getString()`, `getInt()`, `getFloat()`, `getBool()`, `getArray()`
- Using nested keys with a delimiter (`.`)
- Explicit placeholder interpolation through `resolvePlaceholders()`

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

$port = $config->getInt('database.port', 5432);
$debug = $config->getBool('app.debug', false);
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

##  Environment Placeholders (`resolvePlaceholders`)
`resolvePlaceholders()` is an explicit opt-in step that processes placeholders in string values after configuration loading/merging.

Supported syntax:
- `${VAR}` — required variable
- `${VAR:-default}` — use `default` if variable is missing
- `${VAR:?message}` — throw an exception with `message` if variable is missing

```php
$config = (new Config([], '.'))
    ->fromFiles([
        '/config/app.php',
        '/config/database.php',
    ])
    ->resolvePlaceholders([
        'strict' => true,
        'allowEmpty' => false,
        'preserveUnknown' => false,
    ]);
```

Example in PHP config file:
```php
return [
    'database' => [
        'host' => '${DB_HOST}',
        'port' => '${DB_PORT:-5432}',
        'user' => '${DB_USER:?DB_USER is required}',
    ],
];
```

`resolvePlaceholders()` options:
- `strict` (bool): if `true`, `${VAR}` without value throws an exception
- `allowEmpty` (bool): if `false`, empty env values are treated as missing
- `preserveUnknown` (bool): if `true`, unresolved placeholders are kept as-is
- `variables` (array|null): custom placeholder variables map (recommended)

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
`resolvePlaceholders(array $options = []): self` | Resolve placeholders in string values
`getString(string $key, ?string $default = null): string` | Get a required/typed string value
`getInt(string $key, ?int $default = null): int` | Get a required/typed int value
`getFloat(string $key, ?float $default = null): float` | Get a required/typed float value
`getBool(string $key, ?bool $default = null): bool` | Get a required/typed bool value
`getArray(string $key, ?array $default = null): array` | Get a required/typed array value
`setResolver(LoaderResolver $resolver): self` | Set configuration loader
`getResolver(): LoaderResolver` | Get current loader
Inherited from `Registry` | get(), set(), has(), unset(), merge()

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
`ENotFoundError` | Required key was not found (typed getter without default)
`ETypeException` | Value type does not match typed getter contract

[Back to Contents](../index.md)