[Вернуться к оглавлению](../index.md)
# Logger

Компонент логирования в Scaleum состоит из нескольких частей:

- `LoggerManager` — провайдер логгеров по каналам.
- `LoggerGateway` — фасад для централизованного доступа к логгерам.
- `LoggerChannelTrait` — трей для удобной работы с каналами в классах.

Полная поддержка стандарта PSR-3 (`LoggerInterface`).

## Основные возможности

- Регистрация нескольких логгеров для разных каналов
- Безопасная перезапись логгера с вызовом `shutdown()`
- Доступ к логгерам через фасад `LoggerGateway`
- Управление строгим режимом (`strictMode`)
- Лёгкая интеграция логирования в классы через `LoggerChannelTrait`

## Структура компонентов

### LoggerManager

`LoggerManager` управляет коллекцией логгеров:

```php
$loggerManager = new LoggerManager();
$loggerManager->setLogger('app', new FileLogger('/var/log/app.log'));

$logger = $loggerManager->getLogger('app');
$logger->info('Application started');
```

### LoggerGateway

`LoggerGateway` — это фасад для глобального доступа к логгерам.
- Позволяет задать провайдера логгеров (`LoggerProviderInterface`).
- Поддерживает строгий режим: при отсутствии провайдера выбрасывает `ERuntimeError`.
- Предоставляет методы быстрого логирования (info, error, debug и т.д.)

Пример использования:  
```php
LoggerGateway::setProvider($loggerManager);

// Логирование через фасад
LoggerGateway::info('User logged in', ['channel' => 'auth']);
LoggerGateway::error('Database error', ['channel' => 'db']);
```
\* - в рамках выполнения основного потока ядра(`KernelAbstract`) в `LoggerGateway` автоматически регистрируется экземпляр `LoggerManager::class` как провайдер логгеров, одновременно доступный в контейнере под псевдонимом `Framework::SVC_LOGGERS`.

### LoggerChannelTrait
Трейт `LoggerChannelTrait` позволяет легко подключить логирование в любой класс:
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

## Работа строгого режима
- Включён (`strictModeOn()`) по умолчанию.
- Если провайдер логгера не установлен — выбрасывается исключение `ERuntimeError`.
- Можно отключить строгий режим через `strictModeOff()`.

```php
LoggerGateway::strictModeOff();
LoggerGateway::info('This log will not throw error even if no logger is set');
```

## Ключевые методы
Класс/Трейт | Метод | Назначение
|:------|:------|:-----------|
| `LoggerManager` | setLogger($channel, LoggerInterface $logger) | Установить логгер |
| | getLogger($channel) | Получить логгер по каналу |
| | hasLogger($channel) | Проверить наличие логгера |
| `LoggerGateway` | log($level, $message, array $context = []) | Логирование произвольного уровня |
| | info(), error(), debug() и др. | Быстрое логирование стандартных уровней |
| | setProvider(LoggerProviderInterface $instance) | Установить провайдера логгеров |
| `LoggerChannelTrait` | getLogger() | Получение логгера для канала |
| | log($level, $message, array $context = []) | Логирование из класса с привязанным каналом |

## Обработка ошибок
- `ERuntimeError` — если в строгом режиме не установлен провайдер логгера.
- `InvalidArgumentException` — если запрашивается несуществующий логгер.

[Вернуться к оглавлению](../index.md)