[Back to Contents](../index.md)

**EN** | [UK](../../uk/components/dependency-injection.md) | [RU](../../ru/components/dependency-injection.md)
#  Dependency Injection

The `DependencyInjection\Container` component is a powerful dependency management container implementing the `Psr\Container\ContainerInterface` standard.  
It provides registration, resolution, and automatic assembly of objects and their dependencies in the Scaleum project.


##  Purpose

- Registration of dependencies (objects, factories, references, environment values)
- Resolution of dependencies through autowiring (`Autowire`)
- Support for references to other services (`Reference`)
- Retrieval of configurations from the environment (`Environment`)
- Management of object lifecycle (singleton/factory)

##  Main Features

- Registration of singleton objects
- Registration of factories (`callable`)
- Automatic object construction via constructor
- Working with references to other services
- Resolution of dependencies inside arrays
- Handling of environment variables
- Full support of the `PSR-11` interface

##  Dependency Registration

###  Instance Registration

```php
$container->addDefinition('logger', new Logger\FileLogger('/var/log/app.log'));
```

###  Factory Registration
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

###  Registration via Autowiring (Autowire)
```php
use Scaleum\DependencyInjection\Helpers\Autowire;

$container->addDefinition('mailer', new Autowire(Mailer::class));
$container->addDefinition(Mailer::class, Autowire::create());
```

###  Registration of Configuration Array
```php
$container->addDefinition('config', [
    'host' => 'localhost',
    'port' => 3306,
    'logger' => '@logger', // reference to a registered service
]);
```

###  Retrieving Dependencies
```php
$logger = $container->get('logger');
$config = $container->get('config');
```
- If the service is registered as a `singleton`, a cached instance will be returned.
- If the requested identifier is a class name, the container will attempt to create an instance via autowiring.

##  Working with Environment and References

###  Retrieving Value from Environment
```php
use Scaleum\DependencyInjection\Helpers\Environment;

$container->addDefinition('db_host', new Environment('DB_HOST', 'localhost'));
```
If the environment variable `DB_HOST` is not found, the default value 'localhost' will be returned.

###  Using References to Services
```php
use Scaleum\DependencyInjection\Helpers\Reference;

$container->addDefinition('database', new Reference('db_host'));
```
`Reference` allows embedding a dependency on another service by its identifier.

##  Working with Dependency Arrays
The container supports nested structures:  
- Inside arrays, you can use Autowire, Reference, Factory, Environment.
- It automatically resolves all nested elements recursively.
```php
$container->addDefinitions([
    'redis_driver' => new Autowire(RedisDriver::class),
    'redis_retry'  => 3,
]);
```

##  Key Container Methods
| Method | Purpose |
|:------|:-----------|
| addDefinition(string $id, mixed $value, bool $singleton = true) | Register a dependency |
| addDefinitions(array $definitions, bool $singleton = true) | Register a set of dependencies |
| get(string $id): mixed | Retrieve a service or create a new instance |
| has(string $id): bool | Check if a service exists in the container |


##  Exceptions
- `NotFoundException` — service with the specified ID not found in the container.
- `ReflectionException` — unable to create an instance of the class (e.g., missing constructor parameter types).

[Back to Contents](../index.md)