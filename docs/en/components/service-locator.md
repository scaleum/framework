[Back to Contents](../index.md)

**EN** | [UK](../../uk/components/service-locator.md) | [RU](../../ru/components/service-locator.md)
#  Service Manager

The Scaleum framework component for managing services consists of two parts:
- `ServiceManager` — a service container with lazy object creation capability.
- `ServiceLocator` — a global facade for accessing services.

##  Purpose

- Service registration
- Lazy instance creation via reflection
- Support for configurations and dependencies
- Global access to services through a facade
- Operation in strict mode with error control

##  Main Features

- Registration of classes, objects, configuration arrays
- Dependency resolution when creating services
- Automatic service initialization (`eager`)
- Global access via `ServiceLocator`
- Strict mode management (`strictMode`)

##  Service Registration

###  Registering a provider class

```php
ServiceLocator::setProvider(new ServiceManager());
ServiceLocator::set('mailer', Mailer::class);
```
\* - within the execution of the main ядро thread (`KernelAbstract`), an instance of `ServiceManager::class` is automatically registered in `ServiceLocator` as a service provider, simultaneously available in the container under the alias `Framework::SVC_POOL`.

###  Registering an object
```php
$mailer = new Mailer('smtp.example.com');
ServiceLocator::set('mailer', $mailer);
```

###  Registering via configuration array
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

###  Automatic initialization (eager)
```php
ServiceLocator::set('cache', [
    'class' => CacheService::class,
    'eager' => true,
]);
```
The service will be created immediately upon registration.

###  Getting services
```php
$mailer = ServiceLocator::get('mailer');
$mailer->send('Welcome email');
```
If the service is not yet created, `ServiceManager` will create it automatically.

###  Working with dependencies  
You can define dependencies on other services:  
```php
ServiceLocator::set('reportService', [
    'class' => ReportService::class,
    'dependencies' => [
        'mailer' => 'mailer', // the mailer service will be injected
    ],
]);
```
When creating `ReportService`, dependencies will be automatically resolved via `getService()`.

##  `ServiceManager` Methods
Method | Purpose
|:------|:-----------|
| getService(string $name, mixed $default = null) | Get a service |
| setService(string $name, mixed $definition, bool $override = false) | Register a service |
| hasService(string $name): bool | Check if a service exists |
| unlink(string $name): self | Remove a service |
| getAll(): array | Get all created services |

##  `ServiceLocator` Methods

Method | Purpose
|:------|:-----------|
setProvider(ServiceProviderInterface $instance) | Set the service container
getProvider(): ?ServiceProviderInterface | Get the container
strictModeOn(), strictModeOff() | Manage strict mode
get(string $name, mixed $default = null) | Get a service
set(string $name, mixed $definition, bool $override = false) | Register a service
has(string $name): bool | Check if a service exists
getAll(): array | Get all services

###  Full usage example
```php
// Setting the provider
$manager = new ServiceManager();
ServiceLocator::setProvider($manager);

// Registering services
ServiceLocator::set('db', [
    'class' => DatabaseConnection::class,
    'host'  => 'localhost',
    'port'  => 3306,
]);

ServiceLocator::set('mailer', new Mailer('smtp.example.com'));

// Getting and using
$db = ServiceLocator::get('db');
$mailer = ServiceLocator::get('mailer');

$mailer->send('Test email');
```

##  Strict mode operation
Strict mode is enabled by default:  
- If the provider is not set — `ERuntimeError` is thrown.
- You can disable strict mode for softer error handling:
```php
ServiceLocator::strictModeOff();
$mailer = ServiceLocator::get('mailer', new NullMailer());
```

##  Errors
Exception | Condition
|:------|:-----------|
`ERuntimeError` | No provider in strict mode, or required constructor parameter not found
`EComponentError` | Error registering an invalid service
`ENotFoundError` | Service class not found

[Back to Contents](../index.md)