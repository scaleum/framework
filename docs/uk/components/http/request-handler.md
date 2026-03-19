[Вернутися до змісту](../../index.md)

[EN](../../../en/components/http/request-handler.md) | **UK** | [RU](../../../ru/components/http/request-handler.md)
# RequestHandler

`RequestHandler` — клас обробки HTTP-запиту у фреймворку Scaleum,
який реалізує `HandlerInterface` і відповідає за повний цикл маршрутизації,
дозволу та виклику контролера, а також генерацію HTTP-відповіді.

## Призначення

- Завантаження та об’єднання конфігурацій маршрутів з файлу та директорії через `LoaderResolver`.
- Реєстрація маршрутів у `Router`.
- Створення об’єкта запиту `InboundRequest::fromGlobals()` з санацією даних.
- Визначення маршруту методом `Router::match` за URI та HTTP-методом.
- Розв’язання контролера через `ControllerResolver`.
- Генерація подій до та після обробки запиту (`EVENT_GET_REQUEST`, `EVENT_GET_RESPONSE`).
- Виклик методу контролера через `ControllerInvoker` та повернення `ResponderInterface`.
- Обгортання помилок у `EHttpException` з коректними HTTP-кодами.

## Конструктор

```php
public function __construct(ContainerInterface $container)
```

- При ініціалізації отримує з контейнера сервіс подій `Framework::SVC_EVENTS`.
- Перевіряє, що сервіс реалізує `EventManagerInterface`, інакше кидає `ERuntimeError`.

## Метод handle

```php
public function handle(): ResponderInterface
```

1. **Завантаження маршрутів**
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
2. **Реєстрація в Router**
   ```php
   $router = $container->get('router');
   foreach ($routes as $name => $attrs) {
       $router->addRoute($name, new Route($attrs));
   }
   ```
3. **Створення запиту та зіставлення**
   ```php
   $request = InboundRequest::fromGlobals();
   $routeInfo = $router->match(
       $request->getUri()->getPath(),
       $request->getMethod()
   );
   ```
4. **Генерація події до контролера**
   ```php
   $this->events->dispatch(
       HandlerInterface::EVENT_GET_REQUEST,
       $this,
       ['request' => $request]
   );
   ```
5. **Виклик контролера та отримання відповіді**
   ```php
   $controller = (new ControllerResolver($container))->resolve($routeInfo);
   $response   = (new ControllerInvoker())->invoke($controller, $routeInfo);
   ```
6. **Генерація події після контролера**
   ```php
   $this->events->dispatch(
       HandlerInterface::EVENT_GET_RESPONSE,
       $this,
       ['response' => $response]
   );
   ```
7. **Повернення `ResponderInterface`**
   ```php
   return $response;
   ```
8. **Обробка помилок**
   - `ENotFoundError` → `EHttpException(404, ...)`
   - Будь-який `Throwable` → `EHttpException(500, ...)`

## Приклади використання

### 1. Маршрути з одного файлу
```php
// Припустимо, 'routes.file' вказує на routes.php:
return [
    'home' => ['path' => '/', 'callback' => ['controller' => HomeController::class, 'method' => 'index']],
];
```
```php
$handler = new RequestHandler($container);
$response = $handler->handle();
// HomeController::index() буде викликаний і поверне ResponderInterface
```

### 2. Об’єднання кількох директорій
```php
// У 'routes.directory' лежать файли:
// admin.php → маршрути для /admin
// api.php   → маршрути для /api
```
```php
$response = (new RequestHandler($container))->handle();
// Всі маршрути з двох файлів будуть доступні в єдиному маршрутизаторі
```

### 3. Підписка на події
```php
$events = $container->get(Framework::SVC_EVENTS);
$events->addListener(
    HandlerInterface::EVENT_GET_REQUEST,
    function ($handler, $payload) {
        // логування запиту
        error_log((string)$payload['request']->getUri());
    }
);
$handler = new RequestHandler($container);
$response = $handler->handle();
```

### 4. Обробка 404
```php
// При відсутності маршруту Router::match кине ENotFoundError
try {
    $response = $handler->handle();
} catch (EHttpException $e) {
    if ($e->getStatusCode() === 404) {
        echo "Сторінку не знайдено";
    }
}
```

[Вернутися до змісту](../../index.md)