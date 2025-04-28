[Вернуться к оглавлению](../../index.md)
# ControllerResolver

`ControllerResolver` — класс, отвечающий за разрешение контроллера по информации маршрута и созданию его экземпляра через контейнер или рефлексию, с последующей генерацией события.

## Назначение

- Извлечение имени класса контроллера и аргументов из `$routeInfo['callback']`.
- Получение экземпляра контроллера из контейнера при отсутствии аргументов.
- Создание контроллера через `ReflectionClass`, передача аргументов в конструктор по именам параметров.
- Валидация: бросает `ERuntimeError` при отсутствии контроллера, классе или нехватке аргументов.
- Диспатч события `ControllerResolver::CONTROLLER_RESOLVED` после успешного создания.

## Конструктор

```php
public function __construct(ContainerInterface $container)
```

- Извлекает из контейнера сервис событий `Framework::SVC_EVENTS` и проверяет, что он реализует `EventManagerInterface`.

## Метод resolve

```php
public function resolve(array $routeInfo): object
```

1. Проверяет наличие `$routeInfo['callback']['controller']`, иначе бросает `RuntimeException`.
2. Определяет:
   - если `callback['controller']` — строка, возвращает `$container->get($controller)`;
   - если массив с ключами `class` и опционально `args`, использует рефлексию для создания экземпляра:
     - собирает аргументы конструктора по именам из массива `args` или использует значения по умолчанию;
     - при нехватке обязательных параметров — бросает `ERuntimeError`.
3. Генерирует событие:

```php
events->dispatch(
    self::CONTROLLER_RESOLVED,
    $this,
    ['controller' => $result]
);
```
4. Возвращает созданный объект контроллера.

## Примеры

### 1. Простейший контроллер без аргументов
```php
namespace App\Controller;
class HomeController {}

$routeInfo = ['callback' => ['controller' => HomeController::class]];
$resolver = new ControllerResolver($container);
$controller = $resolver->resolve($routeInfo);
// эквивалентно: $container->get(HomeController::class)
```

### 2. Контроллер с зависимостями через аргументы
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
// экземпляр UserController создан с передачей зависимостей в конструктор
```

### 3. Обработка ошибок
```php
// a) Контроллер не задан
$resolver->resolve(['callback' => []]);
// ERuntimeError: "Controller is not defined"

// b) Класс не существует
$resolver->resolve(['callback' => ['controller' => 'NonExistent']]);
// ERuntimeError: "Controller \"NonExistent\" does not exist"

// c) Отсутствует обязательный параметр
class FooController { public function __construct($bar) {} }
$routeInfo = ['callback' => ['controller' => ['class' => FooController::class, 'args' => []]]];
$resolver->resolve($routeInfo);
// ERuntimeError: "Missing required parameter \"bar\" for \"FooController\""
```

## События

- `ControllerResolver::CONTROLLER_RESOLVED` — возникает после успешного создания контроллера и передаёт сам объект контроллера.

[Вернуться к оглавлению](../../index.md)

