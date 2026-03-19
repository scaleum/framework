[Back to Contents](../index.md)

**EN** | [UK](../../uk/classes/callback-instance.md) | [RU](../../ru/classes/callback-instance.md)
#  CallbackInstance

**CallbackInstance** — a wrapper for universal handling of callable and invokable structures.

##  Purpose

- Encapsulation of a callable object (function, method, closure)
- Support for invocation via array ['class' => ..., 'method' => ...]
- Storage of additional parameters for the call

##  Main Features

| Method | Purpose |
|:------|:--------|
| `__construct($callback)` | Constructor, accepts a callable or an array with class/method |
| `getCallback()` | Retrieves the final callable |
| `getParams()` | Retrieves the array of parameters |
| `setParams(array $params)` | Sets new parameters |

##  Usage Examples

###  Initialization from an anonymous function

```php
$instance = new CallbackInstance(function() {
    return 'Hello';
});

$callback = $instance->getCallback();
echo $callback(); // Hello
```

###  Initialization from ['class' => ..., 'method' => ...]

```php
$instance = new CallbackInstance([
    'class' => SomeClass::class,
    'method' => 'someMethod',
]);

[$object, $method] = $instance->getCallback();
echo $object->$method();
```

##  Exceptions

- `EObjectError` — if the callable array is incorrectly assembled.

[Back to Contents](../index.md)
