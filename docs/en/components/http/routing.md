[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/http/routing.md) | [RU](../../../ru/components/http/routing.md)
#  Routing

The class `Scaleum\Http\DependencyInjection\Routing` is a configurator of the dependency container for the HTTP routing module. It implements `ConfiguratorInterface`, registering services and parameters in the container necessary for the router's operation.

##  Purpose

- Define the `Router` service in the container for matching HTTP requests with controllers.
- Specify paths to the file and directory containing route descriptions.
- Provide the alias `router` for convenient access to the router instance.

##  Relation to ядро

Used during ядро configuration loading (`KernelAbstract::bootstrap()`), when HTTP module configurators, including `Routing`, are added to the container registry:
```php
$this->getRegistry()->set('kernel.configurators', [
    new Scaleum\Http\DependencyInjection\Routing(),
]);
```  
After this, routing services will be available upon container initialization.

##  Main tasks

- Autowire the router class `Router`.
- Configure the parameter `routes.file` → `<kernel.config_dir>/routes.php`.
- Configure the parameter `routes.directory` → `<kernel.config_dir>/routes`.
- Create the alias `router` for accessing the router service.

##  Definitions in the container

| Container name              | Value / Service                                                              | Description                                                          |
|:----------------------------|:------------------------------------------------------------------------------|:---------------------------------------------------------------------|
| `Router::class`             | `Autowire::create()`                                                          | Instance of `Scaleum\Routing\Router` with dependency autowiring.    |
| `routes.file`               | factory returning `get('kernel.config_dir') . '/routes.php'`                  | Path to the main route description file.                            |
| `routes.directory`          | factory returning `get('kernel.config_dir') . '/routes'`                      | Folder with additional route files.                                 |
| `router`                    | `Router::class`                                                               | Alias for convenient access to the `Router` service.                |

##  Route application process

1. Loading configurators: `Routing::configure()` is called during `KernelAbstract::bootstrap()`.
2. Container initialization: services and parameters are added to the DI container.
3. HTTP request processing:
   - In `RequestHandler`, `router = $container->get('router')` is retrieved.
   - Routes are loaded from `routes.file` and `routes.directory` via `LoaderResolver`.
   - Routes are registered in `Router`.
4. Request matching: when calling `router->match($path, $method)`, the controller and parameters are determined.

##  Configuration example

```php
// config/routes.php
return [
    'route_1' => [
        'path'     => '/api/request(?:/({:any}))?',
        'methods'  => 'GET|POST',
        'callback' => [
            'controller' => Application\Controllers\Controller::class,
            'method'     => 'requestData',
        ],
    ],
    'route_2' => [
        'path'     => '/api/user(?:/({:num}))?',
        'methods'  => 'GET|POST',
        'callback' => [
            'controller' => Application\Controllers\Controller::class,
            'method'     => '*', // method determined dynamically, typical for RESTful controllers where the method name is based on the request type
        ],
    ],
];
```

##  Operation check

```php
/ @var Router $router */
$router = $container->get('router');

// Load routes and verify matching
$routeInfo = $router->match('/api/user', 'GET');
assert($routeInfo['callback']['controller'] === Application\Controllers\Controller::class);
```

[Back to Contents](../../index.md)