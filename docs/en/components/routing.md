[Back to Contents](../index.md)

**EN** | [UK](../../uk/components/routing.md) | [RU](../../ru/components/routing.md)
#  Router

The `Router` component is responsible for matching incoming HTTP requests with specific routes and controllers.  
It implements the basic routing of the Scaleum framework and operates through the `RouteInterface` contracts.

##  Purpose

- Registration of routes (`RouteInterface`)
- Searching for a route by URI and HTTP method
- Generating URLs based on routes
- Extracting parameters from the URI
- Building the controller, method, and request arguments

##  `RouteInterface` Interface

Each route must implement the following methods:

```php
interface RouteInterface
{
    public function getPath(): string;
    public function setPath(string $path): self;
    public function getMethods(): array;
    public function setMethods(string|array $methods): self;
    public function getCallback(): ?array;
    public function setCallback(array $callback): self;
    public function getUrl(array $params): string;
}
```

###  Route Registration
####  Programmatic Route Registration
```php
$route = new Route(
    path: '/user/([0-9]+)',
    methods: ['GET'],
    callback: [
        'controller' => UserController::class,
        'method'     => 'view',
    ]
);

$router->addRoute('user.view', $route);
```

####  Route Registration via Configuration
Routes can be added from a configuration array:
```php
[
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
When loaded, such arrays are converted into instances of classes implementing `RouteInterface`.

###  URL Generation
```php
$url = $router->getUrl('user.view', ['id' => 42]);
// Returns a URL with parameters, based on the route's getUrl() method
```

##  Route Matching Process
The method `match(string $uri, string $method)`:
- Iterates over all routes.
- Compares the URI with the route's regular expression (`getPath()`).
- Checks HTTP method match (GET, POST, etc.).
- Extracts parameters from the URI.
- Builds the callback array:
    - `controller` (class)
    - `method` (controller method)
    - `args` (arguments extracted from the URI)

If no route is found, an `ENotFoundError` exception is thrown.

###  Structure of the Match Result
```php
[
    'uri' => '/user/42',
    'callback' => [
        'controller' => [
            'class' => UserController::class,
            'args'  => [], // constructor arguments
        ],
        'method' => 'view',
        'args'   => ['42'], // method arguments
    ],
]
```

###  Full Usage Example
```php
$route = new Route(
    path: '/post/([0-9]+)',
    methods: ['GET'],
    callback: [
        'controller' => PostController::class,
        'method'     => 'show',
    ]
);

$router = new Router();
$router->addRoute('post.show', $route);

$matched = $router->match('/post/15', 'GET');

$controller = new $matched['callback']['controller']['class'](...$matched['callback']['controller']['args']);
$response = call_user_func([$controller, $matched['callback']['method']], ...$matched['callback']['args']);
```

###  Example of Parameter Substitution in `callback['method']`
If the method name contains placeholders like `{:name}`, the router substitutes them with values
from named parameters of the regular expression. Parameters used
to form the method name are removed from `$params`, while the rest remain as method arguments.

```php
$route = new Route(
    path: '/post/(?P<id>[0-9]+)/(?P<action>[a-z]+)/(?P<slug>[a-z0-9\-]+)',
    methods: ['GET'],
    callback: [
        'controller' => PostController::class,
        'method'     => 'action{:action}By{:id}',
    ]
);

$router = new Router();
$router->addRoute('post.dynamic-method', $route);

$matched = $router->match('/post/42/edit/my-first-post', 'GET');

$matched['callback']['method']; // actioneditBy42
$matched['callback']['args'];   // ['my-first-post', ...]
```

In this example, `action` and `id` are used to build the controller method name,
so after substitution they do not participate in forming the method arguments.
The `slug` parameter is not used in the `method` template, so it remains in the
match data and goes into `callback['args']`.


##  Key Methods of `Router`
| Method | Purpose |
|:------|:--------|
| `addRoute(string $alias, RouteInterface $route)` | Register a single route |
| `addRoutes(array $routes)` | Register an array of routes |
| `getRoute(string $alias): RouteInterface` | Get a route by alias |
| `getRoutes(): array` | Get all routes |
| `getUrl(string $name, array $parameters = []): string` | Generate URL by route |
| `match(string $uri, string $method = 'GET'): array` | Match a route by URI and method |

##  Error Handling
- `ERuntimeError` — for incorrect route configuration or missing controller.
- `ENotFoundError` — if no route is found for the URI.

[Return to table of contents](../index.md)