[Back to Contents](../index.md)

**EN** | [UK](../../uk/components/logger.md) | [RU](../../ru/components/logger.md)
#  Logger

The logging component in Scaleum consists of several parts:

- `LoggerManager` — a provider of loggers by channels.
- `LoggerGateway` — a facade for centralized access to loggers.
- `LoggerChannelTrait` — a trait for convenient work with channels in classes.

Full support for the PSR-3 standard (`LoggerInterface`).

##  Main Features

- Registration of multiple loggers for different channels
- Safe logger overwrite with `shutdown()` call
- Access to loggers via the `LoggerGateway` facade
- Strict mode management (`strictMode`)
- Easy integration of logging into classes via `LoggerChannelTrait`

##  Component Structure

###  LoggerManager

`LoggerManager` manages a collection of loggers:

```php
$loggerManager = new LoggerManager();
$loggerManager->setLogger('app', new FileLogger('/var/log/app.log'));

$logger = $loggerManager->getLogger('app');
$logger->info('Application started');
```

###  LoggerGateway

`LoggerGateway` is a facade for global access to loggers.
- Allows setting a logger provider (`LoggerProviderInterface`).
- Supports strict mode: throws `ERuntimeError` if no provider is set.
- Provides quick logging methods (info, error, debug, etc.)

Usage example:  
```php
LoggerGateway::setProvider($loggerManager);

// Logging via facade
LoggerGateway::info('User logged in', ['channel' => 'auth']);
LoggerGateway::error('Database error', ['channel' => 'db']);
```
\* - during the execution of the main ядро thread (`KernelAbstract`), an instance of `LoggerManager::class` is automatically registered in `LoggerGateway` as a logger provider, simultaneously available in the контейнер under the alias `Framework::SVC_LOGGERS`.

###  LoggerChannelTrait
The `LoggerChannelTrait` trait allows easy integration of logging into any class:
```php
class UserService
{
    use LoggerChannelTrait;

    public function getLoggerChannel(): string
    {
        return 'user';
    }

    public function createUser(array $data): void
    {
        $this->info('Creating new user', ['data' => $data]);
    }
}
```

##  Strict Mode Operation
- Enabled (`strictModeOn()`) by default.
- Throws `ERuntimeError` if the logger provider is not set.
- Can be disabled via `strictModeOff()`.

```php
LoggerGateway::strictModeOff();
LoggerGateway::info('This log will not throw error even if no logger is set');
```

##  Key Methods
Class/Trait | Method | Purpose
|:------|:------|:-----------|
| `LoggerManager` | setLogger($channel, LoggerInterface $logger) | Set a logger |
| | getLogger($channel) | Get logger by channel |
| | hasLogger($channel) | Check if logger exists |
| `LoggerGateway` | log($level, $message, array $context = []) | Log arbitrary level |
| | info(), error(), debug() etc. | Quick logging of standard levels |
| | setProvider(LoggerProviderInterface $instance) | Set logger provider |
| `LoggerChannelTrait` | getLogger() | Get logger for channel |
| | log($level, $message, array $context = []) | Logging from class with bound channel |

##  Error Handling
- `ERuntimeError` — if logger provider is not set in strict mode.
- `InvalidArgumentException` — if a non-existent logger is requested.

[Back to Contents](../index.md)