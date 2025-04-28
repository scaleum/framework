[Вернуться к оглавлению](../../index.md)
# ControllerInvoker

`ControllerInvoker` — класс, отвечающий за вызов метода контроллера, разрешённого `ControllerResolver`, и возвращающий `ResponderInterface`.

## Назначение

- Вызвать указанный в маршруте метод контроллера с передачей аргументов.
- Поддержать fallback на метод `__dispatch`, если указанный метод отсутствует.
- Бросать исключения при отсутствии определения callback или метода.

## Метод invoke

```php
public function invoke(object $controller, array $routeInfo): ResponderInterface
```

1. Извлекает из `$routeInfo['callback']`:
   - `$method` — имя метода контроллера.
   - `$args`   — массив аргументов для передачи.
2. Проверяет наличие `$method`:
   - Если `$method === null` — бросает `ERuntimeError('Controller method is not defined')`.
3. Проверяет `method_exists($controller, $method)`:
   - Если метод отсутствует, но существует `__dispatch` — использует его.
   - Иначе — бросает `EMethodNotFoundError`.
4. Вызывает метод контроллера через `call_user_func_array([$controller, $method], [...$args])`.
5. Возвращает объект, реализующий `ResponderInterface`.

## Примеры

### 1. Обычный вызов метода
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
$controller  = new HelloController(); // получен через Resolver
$responder   = $invoker->invoke($controller, $routeInfo);
// Вызовет HelloController::sayHello('Alice')
```

### 2. Fallback на __dispatch
```php
class ApiController {
    public function __dispatch(string $action): ResponderInterface {
        // универсальная точка входа
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

// ApiController::list() не существует, будет вызван __dispatch
$responder = (new ControllerInvoker())->invoke(new ApiController(), $routeInfo);
```

### 3. Ошибки
```php
// a) Нет callback
(new ControllerInvoker())->invoke($ctrl, []);
// ERuntimeError: "Controller callback is not defined"

// b) Не указано имя метода
(new ControllerInvoker())->invoke($ctrl, ['callback' => []]);
// ERuntimeError: "Controller method is not defined"

// c) Метод и __dispatch отсутствуют
class FooController {}
$routeInfo = ['callback' => ['method' => 'bar']];
(new ControllerInvoker())->invoke(new FooController(), $routeInfo);
// EMethodNotFoundError: "Method \"bar\" does not exist in controller \"FooController\""
```

[Вернуться к оглавлению](../../index.md)

