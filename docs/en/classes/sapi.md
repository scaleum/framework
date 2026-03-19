[Back to Contents](../index.md)

**EN** | [UK](../../uk/classes/sapi.md) | [RU](../../ru/classes/sapi.md)
#  SAPI Explorer

**Explorer** and the enumerations **SapiIdentifier** and **SapiMode** are components for determining the type of environment (SAPI) in which the application is running.

##  Purpose

- Determining the type of SAPI (e.g., CLI, Apache, FastCGI)
- Classifying the application operating mode: Console, HTTP, Universal
- Simplifying work with various PHP runtime environments

##  Main Elements

###  Enum SapiIdentifier

| Value | Description |
|:---------|:---------|
| CLI | Command Line Interface |
| PHPDBG | PHP Debugger |
| APACHE | Apache Handler |
| CGI | Common Gateway Interface |
| FASTCGI | FastCGI |
| FPM | FastCGI Process Manager |
| LITESPEED | LiteSpeed Server |
| ISAPI | Internet Server API |
| EMBED | Embedded PHP |
| UWSGI | uWSGI Interface |
| UNKNOWN | Unknown type |

**Methods:**

- `getName()` — Get the human-readable name of the type.
- `fromString(string $str)` — Create an instance by name.
- `fromValue(int $value)` — Create an instance by numeric value.

###  Enum SapiMode

| Value | Description |
|:---------|:---------|
| CONSOLE | Console mode |
| HTTP | HTTP server |
| UNIVERSAL | Universal mode (built-in servers) |
| UNKNOWN | Unknown mode |

**Methods:**

- `getName()` — Get the name of the mode.
- `fromValue(int $value)` — Get an instance by value.
- `fromString(string $str)` — Get an instance by string description.

###  Class Explorer

| Method | Purpose |
|:------|:-----------|
| `getType()` | Determines and returns the current `SapiIdentifier` |
| `setType(SapiIdentifier $type)` | Forcefully sets the environment type |
| `getTypeFamily(?SapiIdentifier $type = null)` | Gets the operating mode (`SapiMode`) by type |

##  Usage Examples

###  Determining type and mode

```php
use Scaleum\Stdlib\SAPI\Explorer;

$type = Explorer::getType();
$mode = Explorer::getTypeFamily();

echo $type->getName(); // e.g.: SapiIdentifier::CLI
echo $mode->getName(); // e.g.: SapiMode::CONSOLE, SapiMode::HTTP, SapiMode::UNIVERSAL
```

###  Forcefully setting the type

```php
Explorer::setType(SapiIdentifier::CLI);
```

###  Getting mode by type manually

```php
$mode = Explorer::getTypeFamily(SapiIdentifier::FPM);
```

##  Exceptions

- `ERuntimeError` is thrown when an incorrect value is passed to the `fromString()` methods of `SapiIdentifier` and `SapiMode`.

[Back to Contents](../index.md)