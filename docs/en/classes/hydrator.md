[Back to Contents](../index.md)

**EN** | [UK](../../uk/classes/hydrator.md) | [RU](../../ru/classes/hydrator.md)
#  Hydrator

**Hydrator** — a base class for initializing objects from a configuration array and creating instances.

##  Purpose

- Automatic initialization of objects from a data array
- Creating a class instance with constructor parameters
- Unification of object initialization in the project

##  Main Features

| Method | Purpose |
|:------|:--------|
| `__construct(array $config = [])` | Initialize an object using a config |
| `createInstance(mixed $input)` | Create a class instance from a string or an array with configuration |

##  Usage Examples

###  Initializing an object with a config

```php
$config = [
    'property1' => 'value1',
    'property2' => 'value2',
];

$object = new Hydrator($config);
```

###  Creating an instance via `createInstance`

```php
$instance = Hydrator::createInstance([
    'class' => MyClass::class,
    'config' => ['param1' => 'value1', 'param2' => 'value2'],
]);
```

###  Creating an instance by class name

```php
$instance = Hydrator::createInstance(MyClass::class);
```

##  Additional Information

The class uses `InitTrait` for initialization.

The `createInstance` method automatically:
- Checks if the class exists.
- Creates an instance, passing constructor parameters.
- If the class implements `HydratorInterface`, passes the entire config directly.

##  Exceptions

- `RuntimeException` — if the class does not exist.
- `ERuntimeError` — if a required constructor parameter is missing.

[Back to Contents](../index.md)