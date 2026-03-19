[Повернутись до змісту](../../index.md)

[EN](../../../en/components/http/controller-invoker.md) | **UK** | [RU](../../../ru/components/http/controller-invoker.md)
# ControllerInvoker

`ControllerInvoker` — клас, відповідальний за виклик методу контролера, дозволеного `ControllerResolver`, і повертає `ResponderInterface`.

## Призначення

- Викликати вказаний у маршруті метод контролера з передачею аргументів.
- Підтримувати fallback на метод `__dispatch`, якщо вказаний метод відсутній.
- Викидати виключення при відсутності визначення callback або методу.

## Метод invoke

```php
public function invoke(object $controller, array $routeInfo): ResponderInterface
```

1. Витягує з `$routeInfo['callback']`:
   - `$method` — ім'я методу контролера.
   - `$args`   — масив аргументів для передачі.
2. Перевіряє наявність `$method`:
   - Якщо `$method === null` — викидає `ERuntimeError('Controller method is not defined')`.
3. Перевіряє `method_exists($controller, $method)`:
   - Якщо метод відсутній, але існує `__dispatch` — використовує його.
   - Інакше — викидає `EMethodNotFoundError`.
4. Викликає метод контролера через `call_user_func_array([$controller, $method], [...$args])`.
5. Повертає об'єкт, що реалізує `ResponderInterface`.

## Приклади

### 1. Звичайний виклик методу
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
$controller  = new HelloController(); // отриманий через Resolver
$responder   = $invoker->invoke($controller, $routeInfo);
// Викличе HelloController::sayHello('Alice')
```

### 2. Fallback на __dispatch
```php
class ApiController {
    public function __dispatch(string $action): ResponderInterface {
        // універсальна точка входу
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

// ApiController::list() не існує, буде викликаний __dispatch
$responder = (new ControllerInvoker())->invoke(new ApiController(), $routeInfo);
```

### 3. Помилки
```php
// a) Немає callback
(new ControllerInvoker())->invoke($ctrl, []);
// ERuntimeError: "Controller callback is not defined"

// b) Не вказано ім'я методу
(new ControllerInvoker())->invoke($ctrl, ['callback' => []]);
// ERuntimeError: "Controller method is not defined"

// c) Метод і __dispatch відсутні
class FooController {}
$routeInfo = ['callback' => ['method' => 'bar']];
(new ControllerInvoker())->invoke(new FooController(), $routeInfo);
// EMethodNotFoundError: "Method \"bar\" does not exist in controller \"FooController\""
```

[Повернутись до змісту](../../index.md)