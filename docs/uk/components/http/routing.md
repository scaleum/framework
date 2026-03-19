[Повернутись до змісту](../../index.md)

[EN](../../../en/components/http/routing.md) | **UK** | [RU](../../../ru/components/http/routing.md)
# Routing

Клас `Scaleum\Http\DependencyInjection\Routing` — конфігуратор контейнера залежностей для модуля HTTP-маршрутизації. Реалізує `ConfiguratorInterface`, реєструючи в контейнері сервіси та параметри, необхідні для роботи маршрутизатора.

## Призначення

- Визначити в контейнері сервіс `Router` для зіставлення HTTP-запитів з контролерами.
- Вказати шляхи до файлу і директорії з описом маршрутів.
- Надати псевдонім `router` для зручного отримання екземпляра маршрутизатора.

## Зв’язок з ядром

Використовується в процесі завантаження конфігурації ядра (`KernelAbstract::bootstrap()`), коли в реєстр контейнера додаються конфігуратори модуля HTTP, зокрема `Routing`:
```php
$this->getRegistry()->set('kernel.configurators', [
    new Scaleum\Http\DependencyInjection\Routing(),
]);
```  
Після чого при ініціалізації контейнера будуть доступні сервіси маршрутизації.

## Основні завдання

- Автовпровадити клас маршрутизатора `Router`.
- Налаштувати параметр `routes.file` → `<kernel.config_dir>/routes.php`.
- Налаштувати параметр `routes.directory` → `<kernel.config_dir>/routes`.
- Створити псевдонім `router` для отримання сервісу маршрутизатора.

## Визначення в контейнері

| Ім'я в контейнері           | Значення / Сервіс                                                            | Опис                                                                |
|:----------------------------|:------------------------------------------------------------------------------|:--------------------------------------------------------------------|
| `Router::class`             | `Autowire::create()`                                                          | Екземпляр `Scaleum\Routing\Router` з автоін’єкцією залежностей.    |
| `routes.file`               | фабрика, що повертає `get('kernel.config_dir') . '/routes.php'`               | Шлях до основного файлу опису маршрутів.                            |
| `routes.directory`          | фабрика, що повертає `get('kernel.config_dir') . '/routes'`                   | Папка з додатковими файлами маршрутів.                             |
| `router`                    | `Router::class`                                                               | Псевдонім для зручного доступу до сервісу `Router`.                 |

## Процес застосування маршрутів

1. Завантаження конфігураторів: `Routing::configure()` викликається в процесі `KernelAbstract::bootstrap()`.
2. Ініціалізація контейнера: сервіси та параметри додаються в DI-контейнер.
3. Обробка HTTP-запиту:
   - У `RequestHandler` отримується `router = $container->get('router')`.
   - Завантажуються маршрути з `routes.file` і `routes.directory` через `LoaderResolver`.
   - Маршрути реєструються в `Router`.
4. Зіставлення запиту: при виклику `router->match($path, $method)` визначається контролер і параметри.

## Приклад конфігурації

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
            'method'     => '*', // визначення методу динамічно, характерно для RESTful контролерів, коли ім'я методу визначається на основі типу запиту
        ],
    ],
];
```

## Перевірка роботи

```php
/ @var Router $router */
$router = $container->get('router');

// Завантаження маршрутів і перевірка відповідності
$routeInfo = $router->match('/api/user', 'GET');
assert($routeInfo['callback']['controller'] === Application\Controllers\Controller::class);
```

[Повернутись до змісту](../../index.md)