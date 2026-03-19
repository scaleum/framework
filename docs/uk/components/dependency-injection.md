[Повернутись до змісту](../index.md)

[EN](../../en/components/dependency-injection.md) | **UK** | [RU](../../ru/components/dependency-injection.md)
# Dependency Injection

Компонент `DependencyInjection\Container` — це потужний контейнер управління залежностями, що реалізує стандарт `Psr\Container\ContainerInterface`.  
Він забезпечує реєстрацію, розв’язання та автоматичне складання об’єктів та їх залежностей у проєкті Scaleum.


## Призначення

- Реєстрація залежностей (об’єктів, фабрик, посилань, значень оточення)
- Розв’язання залежностей через автозв’язування (`Autowire`)
- Підтримка посилань на інші сервіси (`Reference`)
- Отримання конфігурацій з оточення (`Environment`)
- Управління життєвим циклом об’єктів (singleton/factory)

## Основні можливості

- Реєстрація singleton-об’єктів
- Реєстрація фабрик (`callable`)
- Автоматичне побудова об’єктів через конструктор
- Робота з посиланнями на інші сервіси
- Розв’язання залежностей всередині масивів
- Обробка змінних оточення
- Повна підтримка `PSR-11` інтерфейсу

## Реєстрація залежностей

### Реєстрація екземпляра

```php
$container->addDefinition('logger', new Logger\FileLogger('/var/log/app.log'));
```

### Реєстрація фабрики
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

### Реєстрація через автозв’язування (Autowire)
```php
use Scaleum\DependencyInjection\Helpers\Autowire;

$container->addDefinition('mailer', new Autowire(Mailer::class));
$container->addDefinition(Mailer::class, Autowire::create());
```

### Реєстрація масиву конфігурацій
```php
$container->addDefinition('config', [
    'host' => 'localhost',
    'port' => 3306,
    'logger' => '@logger', // посилання на зареєстрований сервіс
]);
```

### Отримання залежностей
```php
$logger = $container->get('logger');
$config = $container->get('config');
```
- Якщо сервіс зареєстрований як `singleton`, буде повернено кешований екземпляр.
- Якщо запитуваний ідентифікатор — це ім’я класу, контейнер спробує створити екземпляр через автозв’язування.

## Робота з оточенням і посиланнями

### Отримання значення з оточення
```php
use Scaleum\DependencyInjection\Helpers\Environment;

$container->addDefinition('db_host', new Environment('DB_HOST', 'localhost'));
```
Якщо змінна оточення `DB_HOST` не знайдена, буде повернено значення за замовчуванням 'localhost'.

### Використання посилань на сервіси
```php
use Scaleum\DependencyInjection\Helpers\Reference;

$container->addDefinition('database', new Reference('db_host'));
```
`Reference` дозволяє вбудувати залежність на інший сервіс за його ідентифікатором.

## Робота з масивами залежностей
Контейнер підтримує вкладені структури:  
- Всередині масиву можна використовувати Autowire, Reference, Factory, Environment.
- Автоматично рекурсивно розв’язує всі вкладені елементи.
```php
$container->addDefinitions([
    'redis_driver' => new Autowire(RedisDriver::class),
    'redis_retry'  => 3,
]);
```

## Ключові методи контейнера
| Метод | Призначення |
|:------|:-----------|
| addDefinition(string $id, mixed $value, bool $singleton = true) | Реєстрація залежності |
| addDefinitions(array $definitions, bool $singleton = true) | Реєстрація набору залежностей |
| get(string $id): mixed | Отримання сервісу або створення нового екземпляра |
| has(string $id): bool | Перевірка наявності сервісу в контейнері |


## Винятки
- `NotFoundException` — сервіс з вказаним ID не знайдений у контейнері.
- `ReflectionException` — неможливо створити екземпляр класу (наприклад, відсутні типи параметрів конструктора).

[Повернутись до змісту](../index.md)