[Повернутись до змісту](../index.md)

[EN](../../en/components/routing.md) | **UK** | [RU](../../ru/components/routing.md)
#  Router

Компонент `Router` відповідає за зіставлення вхідних HTTP-запитів з певними маршрутами та контролерами.  
Він реалізує базову маршрутизацію фреймворку Scaleum і працює через контракти `RouteInterface`.

##  Призначення

- Реєстрація маршрутів (`RouteInterface`)
- Пошук маршруту за URI та HTTP-методом
- Генерація URL за маршрутами
- Витяг параметрів з URI
- Побудова контролера, методу та аргументів запиту

##  Інтерфейс `RouteInterface`

Кожен маршрут повинен реалізовувати наступні методи:

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

###  Реєстрація маршрутів
####  Програмна реєстрація маршрутів
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

####  Реєстрація маршрутів через конфігурацію
Маршрути можуть бути додані з масиву конфігурації:
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
            'method'     => '*', // визначення методу динамічно, характерно для RESTful контролерів, коли ім'я методу визначається на основі типу запиту
        ],
    ],
];
```
Під час завантаження такі масиви перетворюються на екземпляри класів, що реалізують `RouteInterface`.

###  Генерація URL
```php
$url = $router->getUrl('user.view', ['id' => 42]);
// Поверне URL з параметрами, на основі методу getUrl() маршруту
```

##  Процес пошуку маршруту
Метод `match(string $uri, string $method)`:
- Перебір усіх маршрутів.
- Порівняння URI з регулярним виразом маршруту (`getPath()`).
- Перевірка відповідності HTTP-методу (GET, POST тощо).
- Витяг параметрів з URI.
- Побудова callback-масиву:
    - `controller` (клас)
    - `method` (метод контролера)
    - `args` (аргументи, витягнуті з URI)

Якщо маршрут не знайдено — викидається виключення `ENotFoundError`.

###  Структура результату пошуку/зіставлення
```php
[
    'uri' => '/user/42',
    'callback' => [
        'controller' => [
            'class' => UserController::class,
            'args'  => [], // аргументи конструктора
        ],
        'method' => 'view',
        'args'   => ['42'], // аргументи методу
    ],
]
```

###  Приклад повного використання
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

###  Приклад підстановки параметрів у `callback['method']`
Якщо в імені методу вказані плейсхолдери виду `{:name}`, роутер підставить у них значення
з іменованих параметрів регулярного виразу. Параметри, які були використані
для формування імені методу, видаляються з `$params`, а решта залишаються аргументами методу.

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

У цьому прикладі `action` і `id` використовуються для складання імені методу контролера,
тому після підстановки вони не беруть участі у формуванні аргументів методу.
Параметр `slug` не використовується у шаблоні `method`, тому залишається в даних
зіставлення і потрапляє у `callback['args']`.


##  Ключові методи `Router`
| Метод | Призначення |
|:------|:-----------|
| `addRoute(string $alias, RouteInterface $route)` | Реєстрація одного маршруту |
| `addRoutes(array $routes)` | Реєстрація масиву маршрутів |
| `getRoute(string $alias): RouteInterface` | Отримання маршруту за псевдонімом |
| `getRoutes(): array` | Отримання всіх маршрутів |
| `getUrl(string $name, array $parameters = []): string` | Генерація URL за маршрутом |
| `match(string $uri, string $method = 'GET'): array` | Співставлення маршруту за URI та методом |

##  Обробка помилок
- `ERuntimeError` — при неправильному налаштуванні маршруту або відсутньому контролері.
- `ENotFoundError` — якщо маршрут для URI не знайдено.

[Повернутися до змісту](../index.md)