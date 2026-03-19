[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/http/controller-invoker.md) | [RU](../../../ru/components/http/controller-invoker.md)
#  ControllerInvoker

`ControllerInvoker` is a class responsible for invoking the controller method allowed by `ControllerResolver` and returning a `ResponderInterface`.

##  Purpose

- Invoke the controller method specified in the route with the provided arguments.
- Support fallback to the `__dispatch` method if the specified method is missing.
- Throw exceptions if the callback or method is not defined.

##  Method invoke

```php
public function invoke(object $controller, array $routeInfo): ResponderInterface
```

1. Extracts from `$routeInfo['callback']`:
   - `$method` — the name of the controller method.
   - `$args`   — an array of arguments to pass.
2. Checks for the presence of `$method`:
   - If `$method === null` — throws `ERuntimeError('Controller method is not defined')`.
3. Checks `method_exists($controller, $method)`:
   - If the method does not exist but `__dispatch` exists — uses it.
   - Otherwise — throws `EMethodNotFoundError`.
4. Calls the controller method via `call_user_func_array([$controller, $method], [...$args])`.
5. Returns an object implementing `ResponderInterface`.

##  Examples

###  1. Regular method call
```php
class HelloController {
    public function sayHello(string $name): ResponderInterface {
        return new HtmlResponder("Hello, {$name}!");
    }
}

$routeInfo = [
    'callback' => [
        'controller' => HelloController::class,
        'method'     => 'sayHello',
        'args'       => ['Alice'],
    ],
];

$invoker     = new ControllerInvoker();
$controller  = new HelloController(); // obtained via Resolver
$responder   = $invoker->invoke($controller, $routeInfo);
// Calls HelloController::sayHello('Alice')
```

###  2. Fallback to __dispatch
```php
class ApiController {
    public function __dispatch(string $action): ResponderInterface {
        // universal entry point
        switch ($action) {
            case 'list': return new JsonResponder(['data' => []]);
            default:     throw new RuntimeException('Unknown action');
        }
    }
}

$routeInfo = [
    'callback' => [
        'controller' => ApiController::class,
        'method'     => 'list',
        'args'       => ['list'],
    ],
];

// ApiController::list() does not exist, __dispatch will be called
$responder = (new ControllerInvoker())->invoke(new ApiController(), $routeInfo);
```

###  3. Errors
```php
// a) No callback
(new ControllerInvoker())->invoke($ctrl, []);
// ERuntimeError: "Controller callback is not defined"

// b) Method name not specified
(new ControllerInvoker())->invoke($ctrl, ['callback' => []]);
// ERuntimeError: "Controller method is not defined"

// c) Method and __dispatch are missing
class FooController {}
$routeInfo = ['callback' => ['method' => 'bar']];
(new ControllerInvoker())->invoke(new FooController(), $routeInfo);
// EMethodNotFoundError: "Method \"bar\" does not exist in controller \"FooController\""
```

[Back to Contents](../../index.md)

