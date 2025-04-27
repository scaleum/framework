[Вернуться к оглавлению](../index.md)
# Service Manager

Компонент фреймворка Scaleum для управления сервисами состоит из двух частей:
- `ServiceManager` — контейнер сервисов с возможностью ленивого создания объектов.
- `ServiceLocator` — глобальный фасад для доступа к сервисам.


## Назначение

- Регистрация сервисов
- Ленивое создание экземпляров через рефлексию
- Поддержка конфигураций и зависимостей
- Глобальный доступ к сервисам через фасад
- Работа в строгом режиме с контролем ошибок

## Основные возможности

- Регистрация классов, объектов, массивов конфигураций
- Разрешение зависимостей при создании сервисов
- Автоматическая инициализация сервисов (`eager`)
- Глобальный доступ через `ServiceLocator`
- Управление строгим режимом (`strictMode`)

## Регистрация сервисов

### Регистрация класса провайдера

```php
ServiceLocator::setProvider(new ServiceManager());
ServiceLocator::set('mailer', Mailer::class);
```
\* - в рамках выполнения основного потока ядра(`KernelAbstract`) в `ServiceLocator` автоматически регистрируется экземпляр `ServiceManager::class` как провайдер сервисов, одновременно доступный в контейнере под псевдонимом `Framework::SVC_POOL`.
 
### Регистрация объекта
```php
$mailer = new Mailer('smtp.example.com');
ServiceLocator::set('mailer', $mailer);
```

### Регистрация через массив конфигурации
```php
ServiceLocator::set('db', [
    'class' => DatabaseConnection::class,
    'host'  => 'localhost',
    'port'  => 3306,
]);

ServiceLocator::set('translator',[
    'class'  => Scaleum\i18n\Translator::class,
    'config' => [
        // 'dependencies' => [
        //     'events'  => Framework::SVC_EVENTS, // for all services
        //     'session' => 'session',
        // ],
        // 'locale'        => 'en_US',
        // 'text_domain'   => 'default',
        // 'translation_dir' => __DIR__ . '/../../locale',
    ],
]);
```

### Автоматическая инициализация (eager)
```php
ServiceLocator::set('cache', [
    'class' => CacheService::class,
    'eager' => true,
]);
```
Сервис будет создан немедленно при регистрации.

### Получение сервисов
```php
$mailer = ServiceLocator::get('mailer');
$mailer->send('Welcome email');
```
Если сервис ещё не создан, `ServiceManager` создаст его автоматически.

### Работа с зависимостями  
Можно определить зависимости других сервисов:  
```php
ServiceLocator::set('reportService', [
    'class' => ReportService::class,
    'dependencies' => [
        'mailer' => 'mailer', // будет подставлен сервис mailer
    ],
]);
```
При создании `ReportService` зависимости будут автоматически разрешены через `getService()`.

## Методы `ServiceManager`
Метод | Назначение
|:------|:-----------|
| getService(string $name, mixed $default = null) | Получить сервис |
| setService(string $name, mixed $definition, bool $override = false) | Зарегистрировать сервис |
| hasService(string $name): bool | Проверить наличие сервиса |
| unlink(string $name): self | Удалить сервис |
| getAll(): array | Получить все созданные сервисы |

## Методы `ServiceLocator`

Метод | Назначение
|:------|:-----------|
setProvider(ServiceProviderInterface $instance) | Установить контейнер сервисов
getProvider(): ?ServiceProviderInterface | Получить контейнер
strictModeOn(), strictModeOff() | Управление строгим режимом
get(string $name, mixed $default = null) | Получить сервис
set(string $name, mixed $definition, bool $override = false) | Зарегистрировать сервис
has(string $name): bool | Проверить наличие сервиса
getAll(): array | Получить все сервисы


### Пример полного использования
```php
// Установка провайдера
$manager = new ServiceManager();
ServiceLocator::setProvider($manager);

// Регистрация сервисов
ServiceLocator::set('db', [
    'class' => DatabaseConnection::class,
    'host'  => 'localhost',
    'port'  => 3306,
]);

ServiceLocator::set('mailer', new Mailer('smtp.example.com'));

// Получение и использование
$db = ServiceLocator::get('db');
$mailer = ServiceLocator::get('mailer');

$mailer->send('Test email');
```

## Работа строгого режима
По умолчанию включен строгий режим:  
- Если провайдер не установлен — выбрасывается `ERuntimeError`.
- Можно отключить строгий режим для более мягкой обработки ошибок:
```php
ServiceLocator::strictModeOff();
$mailer = ServiceLocator::get('mailer', new NullMailer());
```

## Ошибки
Исключение | Условие
|:------|:-----------|
`ERuntimeError` | Нет провайдера в строгом режиме, или не найден обязательный параметр конструктора
`EComponentError` | Ошибка при регистрации неправильного сервиса
`ENotFoundError` | Класс сервиса не найден

[Вернуться к оглавлению](../index.md)