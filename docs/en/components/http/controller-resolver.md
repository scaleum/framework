[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/http/controller-resolver.md) | [RU](../../../ru/components/http/controller-resolver.md)
#  ControllerResolver

`ControllerResolver` is a class responsible for resolving a controller based on route information and creating its instance via the container or reflection, followed by event dispatching.

##  Purpose

- Extracting the controller class name and arguments from `$routeInfo['callback']`.
- Obtaining the controller instance from the container if no arguments are provided.
- Creating the controller via `ReflectionClass`, passing arguments to the constructor by parameter names.
- Validation: throws `ERuntimeError` if the controller is missing, the class does not exist, or arguments are insufficient.
- Dispatches the event `ControllerResolver::CONTROLLER_RESOLVED` after successful creation.

##  Constructor

```php
public function __construct(ContainerInterface $container)
```

- Retrieves the event service `Framework::SVC_EVENTS` from the container and verifies it implements `EventManagerInterface`.

##  Method resolve

```php
public function resolve(array $routeInfo): object
```

1. Checks for the presence of `$routeInfo['callback']['controller']`, otherwise throws `RuntimeException`.
2. Determines:
   - if `callback['controller']` is a string, returns `$container->get($controller)`;
   - if an array with keys `class` and optionally `args`, uses reflection to create an instance:
     - collects constructor arguments by names from the `args` array or uses default values;
     - throws `ERuntimeError` if required parameters are missing.
3. Dispatches the event:

```php
events->dispatch(
    self::CONTROLLER_RESOLVED,
    $this,
    ['controller' => $result]
);
```
4. Returns the created controller object.

##  Examples

###  1. Simplest controller without arguments
```php
namespace App\Controller;
class HomeController {}

$routeInfo = ['callback' => ['controller' => HomeController::class]];
$resolver = new ControllerResolver($container);
$controller = $resolver->resolve($routeInfo);
// equivalent to: $container->get(HomeController::class)
```

###  2. Controller with dependencies via arguments
```php
namespace App\Controller;
use App\Service\UserService;
use Psr\Log\LoggerInterface;

class UserController {
    public function __construct(
        UserService $userService,
        LoggerInterface $logger
    ) { /* ... */ }
}

$routeInfo = [
    'callback' => [
        'controller' => [
            'class' => UserController::class,
            'args'  => [
                'userService' => $userServiceInstance,
                'logger'      => $loggerInstance,
            ],
        ],
    ],
];

$resolver = new ControllerResolver($container);
$controller = $resolver->resolve($routeInfo);
// UserController instance created with dependencies passed to the constructor
```

###  3. Error handling
```php
// a) Controller not defined
$resolver->resolve(['callback' => []]);
// ERuntimeError: "Controller is not defined"

// b) Class does not exist
$resolver->resolve(['callback' => ['controller' => 'NonExistent']]);
// ERuntimeError: "Controller \"NonExistent\" does not exist"

// c) Missing required parameter
class FooController { public function __construct($bar) {} }
$routeInfo = ['callback' => ['controller' => ['class' => FooController::class, 'args' => []]]];
$resolver->resolve($routeInfo);
// ERuntimeError: "Missing required parameter \"bar\" for \"FooController\""
```

##  Events

- `ControllerResolver::CONTROLLER_RESOLVED` — occurs after successful controller creation and passes the controller object itself.

[Back to Contents](../../index.md)

