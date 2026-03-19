[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/http/request-handler.md) | [RU](../../../ru/components/http/request-handler.md)
#  RequestHandler

`RequestHandler` is a class for handling HTTP requests in the Scaleum framework,
implementing `HandlerInterface` and responsible for the full cycle of routing,
resolving, and invoking the controller, as well as generating the HTTP response.

##  Purpose

- Loading and merging route configurations from a file and directory via `LoaderResolver`.
- Registering routes in the `Router`.
- Creating the request object `InboundRequest::fromGlobals()` with data sanitization.
- Determining the route using `Router::match` by URI and HTTP method.
- Resolving the controller via `ControllerResolver`.
- Generating events before and after request handling (`EVENT_GET_REQUEST`, `EVENT_GET_RESPONSE`).
- Invoking the controller method via `ControllerInvoker` and returning `ResponderInterface`.
- Wrapping errors in `EHttpException` with appropriate HTTP codes.

##  Constructor

```php
public function __construct(ContainerInterface $container)
```

- On initialization, obtains the event service `Framework::SVC_EVENTS` from the container.
- Checks that the service implements `EventManagerInterface`, otherwise throws `ERuntimeError`.

##  Method handle

```php
public function handle(): ResponderInterface
```

1. **Loading routes**
   ```php
   $loader = $container->get(LoaderResolver::class);
   $routes = [];
   if (file_exists($file = $container->get('routes.file'))) {
       $routes = $loader->fromFile($file);
   }
   if (is_dir($dir = $container->get('routes.directory'))) {
       $routes = ArrayHelper::merge($routes, $loader->fromDir($dir));
   }
   ```
2. **Registering in Router**
   ```php
   $router = $container->get('router');
   foreach ($routes as $name => $attrs) {
       $router->addRoute($name, new Route($attrs));
   }
   ```
3. **Creating request and matching**
   ```php
   $request = InboundRequest::fromGlobals();
   $routeInfo = $router->match(
       $request->getUri()->getPath(),
       $request->getMethod()
   );
   ```
4. **Generating event before controller**
   ```php
   $this->events->dispatch(
       HandlerInterface::EVENT_GET_REQUEST,
       $this,
       ['request' => $request]
   );
   ```
5. **Invoking controller and obtaining response**
   ```php
   $controller = (new ControllerResolver($container))->resolve($routeInfo);
   $response   = (new ControllerInvoker())->invoke($controller, $routeInfo);
   ```
6. **Generating event after controller**
   ```php
   $this->events->dispatch(
       HandlerInterface::EVENT_GET_RESPONSE,
       $this,
       ['response' => $response]
   );
   ```
7. **Returning `ResponderInterface`**
   ```php
   return $response;
   ```
8. **Error handling**
   - `ENotFoundError` → `EHttpException(404, ...)`
   - Any `Throwable` → `EHttpException(500, ...)`

##  Usage examples

###  1. Routes from a single file
```php
// Assume 'routes.file' points to routes.php:
return [
    'home' => ['path' => '/', 'callback' => ['controller' => HomeController::class, 'method' => 'index']],
];
```
```php
$handler = new RequestHandler($container);
$response = $handler->handle();
// HomeController::index() will be called and return ResponderInterface
```

###  2. Merging multiple directories
```php
// In 'routes.directory' there are files:
// admin.php → routes for /admin
// api.php   → routes for /api
```
```php
$response = (new RequestHandler($container))->handle();
// All routes from both files will be available in a single router
```

###  3. Subscribing to events
```php
$events = $container->get(Framework::SVC_EVENTS);
$events->addListener(
    HandlerInterface::EVENT_GET_REQUEST,
    function ($handler, $payload) {
        // logging the request
        error_log((string)$payload['request']->getUri());
    }
);
$handler = new RequestHandler($container);
$response = $handler->handle();
```

###  4. Handling 404
```php
// If no route is found, Router::match will throw ENotFoundError
try {
    $response = $handler->handle();
} catch (EHttpException $e) {
    if ($e->getStatusCode() === 404) {
        echo "Page not found";
    }
}
```

[Back to Contents](../../index.md)
