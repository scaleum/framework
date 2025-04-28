[Вернуться к оглавлению](../../index.md)
# Routing

Класс `Scaleum\Http\DependencyInjection\Routing` — конфигуратор контейнера зависимостей для модуля HTTP-маршрутизации. Реализует `ConfiguratorInterface`, регистрируя в контейнере сервисы и параметры, необходимые для работы маршрутизатора.

## Назначение

- Определить в контейнере сервис `Router` для сопоставления HTTP-запросов с контроллерами.
- Указать пути к файлу и директории с описанием маршрутов.
- Предоставить псевдоним `router` для удобного получения экземпляра маршрутизатора.

## Связь с ядром

Используется в процессе загрузки конфигурации ядра (`KernelAbstract::bootstrap()`), когда в реестр контейнера добавляются конфигураторы модуля HTTP, в том числе `Routing`:
```php
$this->getRegistry()->set('kernel.configurators', [
    new Scaleum\Http\DependencyInjection\Routing(),
]);
```  
После чего при инициализации контейнера будут доступны сервисы маршрутизации.

## Основные задачи

- Автовнедрить класс маршрутизатора `Router`.
- Настроить параметр `routes.file` → `<kernel.config_dir>/routes.php`.
- Настроить параметр `routes.directory` → `<kernel.config_dir>/routes`.
- Создать псевдоним `router` для получения сервиса маршрутизатора.

## Определения в контейнере

| Имя в контейнере            | Значение / Сервис                                                            | Описание                                                             |
|:----------------------------|:------------------------------------------------------------------------------|:---------------------------------------------------------------------|
| `Router::class`             | `Autowire::create()`                                                          | Экземпляр `Scaleum\Routing\Router` с автоинъекцией зависимостей.   |
| `routes.file`               | фабрика, возвращающая `get('kernel.config_dir') . '/routes.php'`              | Путь к основному файлу описания маршрутов.                           |
| `routes.directory`          | фабрика, возвращающая `get('kernel.config_dir') . '/routes'`                  | Папка с дополнительными файлами маршрутов.                            |
| `router`                    | `Router::class`                                                               | Псевдоним для удобного доступа к сервису `Router`.                   |

## Процесс применения маршрутов

1. Загрузка конфигураторов: `Routing::configure()` вызывается в процессе `KernelAbstract::bootstrap()`.
2. Инициализация контейнера: сервисы и параметры добавляются в DI-контейнер.
3. Обработка HTTP-запроса:
   - В `RequestHandler` извлекается `router = $container->get('router')`.
   - Загружаются маршруты из `routes.file` и `routes.directory` через `LoaderResolver`.
   - Маршруты регистрируются в `Router`.
4. Сопоставление запроса: при вызове `router->match($path, $method)` определяется контроллер и параметры.

## Пример конфигурации

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
            'method'     => '*', // определение метода динамически, характерно для RESTful контроллеров когда имя метода определяется на основании типа запроса
        ],
    ],
];
```

## Проверка работы

```php
/ @var Router $router */
$router = $container->get('router');

// Загрузка маршрутов и проверка соответствия
$routeInfo = $router->match('/api/user', 'GET');
assert($routeInfo['callback']['controller'] === Application\Controllers\Controller::class);
```

[Вернуться к оглавлению](../../index.md)