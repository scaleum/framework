[Повернутись до змісту](../index.md)

[EN](../../en/components/logger.md) | **UK** | [RU](../../ru/components/logger.md)
# Logger

Компонент логування в Scaleum складається з кількох частин:

- `LoggerManager` — провайдер логерів за каналами.
- `LoggerGateway` — фасад для централізованого доступу до логерів.
- `LoggerChannelTrait` — трей для зручної роботи з каналами в класах.

Повна підтримка стандарту PSR-3 (`LoggerInterface`).

## Основні можливості

- Реєстрація кількох логерів для різних каналів
- Безпечне перезаписування логера з викликом `shutdown()`
- Доступ до логерів через фасад `LoggerGateway`
- Керування суворим режимом (`strictMode`)
- Легка інтеграція логування в класи через `LoggerChannelTrait`

## Структура компонентів

### LoggerManager

`LoggerManager` керує колекцією логерів:

```php
$loggerManager = new LoggerManager();
$loggerManager->setLogger('app', new FileLogger('/var/log/app.log'));

$logger = $loggerManager->getLogger('app');
$logger->info('Application started');
```

### LoggerGateway

`LoggerGateway` — це фасад для глобального доступу до логерів.
- Дозволяє задати провайдера логерів (`LoggerProviderInterface`).
- Підтримує суворий режим: при відсутності провайдера викидає `ERuntimeError`.
- Надає методи швидкого логування (info, error, debug тощо)

Приклад використання:  
```php
LoggerGateway::setProvider($loggerManager);

// Логування через фасад
LoggerGateway::info('User logged in', ['channel' => 'auth']);
LoggerGateway::error('Database error', ['channel' => 'db']);
```
\* - в рамках виконання основного потоку ядра(`KernelAbstract`) в `LoggerGateway` автоматично реєструється екземпляр `LoggerManager::class` як провайдер логерів, одночасно доступний у контейнері під псевдонімом `Framework::SVC_LOGGERS`.

### LoggerChannelTrait
Трейд `LoggerChannelTrait` дозволяє легко підключити логування в будь-який клас:
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

## Робота суворого режиму
- Увімкнений (`strictModeOn()`) за замовчуванням.
- Якщо провайдер логера не встановлений — викидається виключення `ERuntimeError`.
- Можна вимкнути суворий режим через `strictModeOff()`.

```php
LoggerGateway::strictModeOff();
LoggerGateway::info('This log will not throw error even if no logger is set');
```

## Ключові методи
Клас/Трейд | Метод | Призначення
|:------|:------|:-----------|
| `LoggerManager` | setLogger($channel, LoggerInterface $logger) | Встановити логер |
| | getLogger($channel) | Отримати логер за каналом |
| | hasLogger($channel) | Перевірити наявність логера |
| `LoggerGateway` | log($level, $message, array $context = []) | Логування довільного рівня |
| | info(), error(), debug() та ін. | Швидке логування стандартних рівнів |
| | setProvider(LoggerProviderInterface $instance) | Встановити провайдера логерів |
| `LoggerChannelTrait` | getLogger() | Отримання логера для каналу |
| | log($level, $message, array $context = []) | Логування з класу з прив’язаним каналом |

## Обробка помилок
- `ERuntimeError` — якщо в суворому режимі не встановлено провайдера логера.
- `InvalidArgumentException` — якщо запитується неіснуючий логер.

[Повернутись до змісту](../index.md)