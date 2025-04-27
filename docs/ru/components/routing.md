[Вернуться к оглавлению](../index.md)
# Router

Компонент `Router` отвечает за сопоставление входящих HTTP-запросов с определёнными маршрутами и контроллерами.  
Он реализует базовую маршрутизацию фреймворка Scaleum и работает через контракты `RouteInterface`.

## Назначение

- Регистрация маршрутов (`RouteInterface`)
- Поиск маршрута по URI и HTTP-методу
- Генерация URL по маршрутам
- Извлечение параметров из URI
- Построение контроллера, метода и аргументов запроса

## Интерфейс `RouteInterface`

Каждый маршрут должен реализовать следующие методы:

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

### Регистрация маршрутов
#### Программная регистрация маршрутов
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

#### Регистрация маршрутов через конфигурацию
Маршруты могут быть добавлены из массива конфигурации:
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
            'method'     => '*', // определение метода динамически, характерно для RESTful контроллеров когда имя метода определяется на основании типа запроса
        ],
    ],
];
```
При загрузке такие массивы преобразуются в экземпляры классов, реализующих `RouteInterface`.

### Генерация URL
```php
$url = $router->getUrl('user.view', ['id' => 42]);
// Вернёт URL с параметрами, на основе метода getUrl() маршрута
```

## Процесс поиска маршрута
Метод `match(string $uri, string $method)`:
- Перебор всех маршрутов.
- Сравнение URI с регулярным выражением маршрута (`getPath()`).
- Проверка соответствия HTTP-метода (GET, POST и т.д.).
- Извлечение параметров из URI.
- Построение callback-массива:
    - `controller` (класс)
    - `method` (метод контроллера)
    - `args` (аргументы, извлечённые из URI)

Если маршрут не найден — выбрасывается исключение `ENotFoundError`.

### Структура результата поиска/сопоставления
```php
[
    'uri' => '/user/42',
    'callback' => [
        'controller' => [
            'class' => UserController::class,
            'args'  => [],
        ],
        'method' => 'view',
        'args'   => ['42'],
    ],
]
```

### Пример полного использования
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


## Ключевые методы `Router`
| Метод | Назначение |
|:------|:-----------|
| `addRoute(string $alias, RouteInterface $route)` | Регистрация одного маршрута |
| `addRoutes(array $routes)` | Регистрация массива маршрутов |
| `getRoute(string $alias): RouteInterface` | Получение маршрута по псевдониму |
| `getRoutes(): array` | Получение всех маршрутов |
| `getUrl(string $name, array $parameters = []): string` | Генерация URL по маршруту |
| `match(string $uri, string $method = 'GET'): array` | Сопоставление маршрута по URI и методу |

## Обработка ошибок
- `ERuntimeError` — при неправильной настройке маршрута или отсутствующем контроллере.
- `ENotFoundError` — если маршрут для URI не найден.

[Вернуться к оглавлению](../index.md)