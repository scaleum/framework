[Вернуться к оглавлению](../index.md)
# Dependency Injection

Компонент `DependencyInjection\Container` — это мощный контейнер управления зависимостями, реализующий стандарт `Psr\Container\ContainerInterface`.  
Он обеспечивает регистрацию, разрешение и автоматическую сборку объектов и их зависимостей в проекте Scaleum.


## Назначение

- Регистрация зависимостей (объектов, фабрик, ссылок, значений окружения)
- Разрешение зависимостей через автосвязывание (`Autowire`)
- Поддержка ссылок на другие сервисы (`Reference`)
- Получение конфигураций из окружения (`Environment`)
- Управление жизненным циклом объектов (singleton/factory)

## Основные возможности

- Регистрация singleton-объектов
- Регистрация фабрик (`callable`)
- Автоматическое построение объектов через конструктор
- Работа со ссылками на другие сервисы
- Разрешение зависимостей внутри массивов
- Обработка переменных окружения
- Полная поддержка `PSR-11` интерфейса

## Регистрация зависимостей

### Регистрация экземпляра

```php
$container->addDefinition('logger', new Logger\FileLogger('/var/log/app.log'));
```

### Регистрация фабрики
```php
$container->addDefinition('connection', function (ContainerInterface $container) {
    return new PDO('sqlite::memory:');
});

$container->addDefinition('adapter', Factory::create(function (ContainerInterface $container) {
        $result = new EventListener($container->get(KernelInterface::class));
        $result->register($container->get(Framework::SVC_EVENTS));
        return $result;
    })
);
```

### Регистрация через автосвязывание (Autowire)
```php
use Scaleum\DependencyInjection\Helpers\Autowire;

$container->addDefinition('mailer', new Autowire(Mailer::class));
$container->addDefinition(Mailer::class, Autowire::create());
```

### Регистрация массива конфигураций
```php
$container->addDefinition('config', [
    'host' => 'localhost',
    'port' => 3306,
    'logger' => '@logger', // ссылка на зарегистрированный сервис
]);
```

### Получение зависимостей
```php
$logger = $container->get('logger');
$config = $container->get('config');
```
- Если сервис зарегистрирован как `singleton`, будет возвращён кэшированный экземпляр.
- Если запрошенный идентификатор — это имя класса, контейнер попытается создать экземпляр через автосвязывание.

## Работа с окружением и ссылками

### Получение значения из окружения
```php
use Scaleum\DependencyInjection\Helpers\Environment;

$container->addDefinition('db_host', new Environment('DB_HOST', 'localhost'));
```
Если переменная окружения `DB_HOST` не найдена, будет возвращено значение по умолчанию 'localhost'.

### Использование ссылок на сервисы
```php
use Scaleum\DependencyInjection\Helpers\Reference;

$container->addDefinition('database', new Reference('db_host'));
```
`Reference` позволяет встроить зависимость на другой сервис по его идентификатору.

## Работа с массивами зависимостей
Контейнер поддерживает вложенные структуры:  
- Внутри массива можно использовать Autowire, Reference, Factory, Environment.
- Автоматически рекурсивно разрешает все вложенные элементы.
```php
$container->addDefinitions([
    'redis_driver' => new Autowire(RedisDriver::class),
    'redis_retry'  => 3,
]);
```

## Ключевые методы контейнера
| Метод | Назначение |
|:------|:-----------|
| addDefinition(string $id, mixed $value, bool $singleton = true) | Регистрация зависимости |
| addDefinitions(array $definitions, bool $singleton = true) | Регистрация набора зависимостей |
| get(string $id): mixed | Получение сервиса или создание нового экземпляра |
| has(string $id): bool | Проверка наличия сервиса в контейнере |


## Исключения
- `NotFoundException` — сервис с указанным ID не найден в контейнере.
- `ReflectionException` — невозможно создать экземпляр класса (например, отсутствуют типы параметров конструктора).

[Вернуться к оглавлению](../index.md)