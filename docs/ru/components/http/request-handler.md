[Вернуться к оглавлению](../../index.md)
# RequestHandler

`RequestHandler` — класс обработки HTTP-запроса во фреймворке Scaleum,
реализующий `HandlerInterface` и отвечающий за полный цикл маршрутизации,
разрешения и вызова контроллера, а также генерацию HTTP-ответа.

## Назначение

- Загрузка и объединение конфигураций маршрутов из файла и директории через `LoaderResolver`.
- Регистрация маршрутов в `Router`.
- Создание объекта запроса `InboundRequest::fromGlobals()` с санацией данных.
- Определение маршрута методом `Router::match` по URI и HTTP-методу.
- Разрешение контроллера через `ControllerResolver`.
- Генерация событий до и после обработки запроса (`EVENT_GET_REQUEST`, `EVENT_GET_RESPONSE`).
- Вызов метода контроллера через `ControllerInvoker` и возврат `ResponderInterface`.
- Оборачивание ошибок в `EHttpException` с корректными HTTP-кодами.

## Конструктор

```php
public function __construct(ContainerInterface $container)
```

- При инициализации получает из контейнера сервис событий `Framework::SVC_EVENTS`.
- Проверяет, что сервис реализует `EventManagerInterface`, иначе кидает `ERuntimeError`.

## Метод handle

```php
public function handle(): ResponderInterface
```

1. **Загрузка маршрутов**
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
2. **Регистрация в Router**
   ```php
   $router = $container->get('router');
   foreach ($routes as $name => $attrs) {
       $router->addRoute($name, new Route($attrs));
   }
   ```
3. **Создание запроса и сопоставление**
   ```php
   $request = InboundRequest::fromGlobals();
   $routeInfo = $router->match(
       $request->getUri()->getPath(),
       $request->getMethod()
   );
   ```
4. **Генерация события до контроллера**
   ```php
   $this->events->dispatch(
       HandlerInterface::EVENT_GET_REQUEST,
       $this,
       ['request' => $request]
   );
   ```
5. **Вызов контроллера и получение ответа**
   ```php
   $controller = (new ControllerResolver($container))->resolve($routeInfo);
   $response   = (new ControllerInvoker())->invoke($controller, $routeInfo);
   ```
6. **Генерация события после контроллера**
   ```php
   $this->events->dispatch(
       HandlerInterface::EVENT_GET_RESPONSE,
       $this,
       ['response' => $response]
   );
   ```
7. **Возврат `ResponderInterface`**
   ```php
   return $response;
   ```
8. **Обработка ошибок**
   - `ENotFoundError` → `EHttpException(404, ...)`
   - Любой `Throwable` → `EHttpException(500, ...`)`

## Примеры использования

### 1. Маршруты из одного файла
```php
// Предположим, 'routes.file' указывает на routes.php:
return [
    'home' => ['path' => '/', 'callback' => ['controller' => HomeController::class, 'method' => 'index']],
];
```
```php
$handler = new RequestHandler($container);
$response = $handler->handle();
// HomeController::index() будет вызван и вернёт ResponderInterface
```

### 2. Объединение нескольких директорий
```php
// В 'routes.directory' лежат файлы:
// admin.php → маршруты для /admin
// api.php   → маршруты для /api
```
```php
$response = (new RequestHandler($container))->handle();
// Все маршруты из двух файлов будут доступны в едином роутере
```

### 3. Подписка на события
```php
$events = $container->get(Framework::SVC_EVENTS);
$events->addListener(
    HandlerInterface::EVENT_GET_REQUEST,
    function ($handler, $payload) {
        // логирование запроса
        error_log((string)$payload['request']->getUri());
    }
);
$handler = new RequestHandler($container);
$response = $handler->handle();
```

### 4. Обработка 404
```php
// При отсутствии маршрута Router::match бросит ENotFoundError
try {
    $response = $handler->handle();
} catch (EHttpException $e) {
    if ($e->getStatusCode() === 404) {
        echo "Страница не найдена";
    }
}
```

[Вернуться к оглавлению](../../index.md)
