[Повернутись до змісту](../../index.md)

[EN](../../../en/components/http/controller-resolver.md) | **UK** | [RU](../../../ru/components/http/controller-resolver.md)
# ControllerResolver

`ControllerResolver` — клас, відповідальний за дозвіл контролера за інформацією маршруту та створення його екземпляра через контейнер або рефлексію, з подальшою генерацією події.

## Призначення

- Витягування імені класу контролера та аргументів з `$routeInfo['callback']`.
- Отримання екземпляра контролера з контейнера за відсутності аргументів.
- Створення контролера через `ReflectionClass`, передача аргументів у конструктор за іменами параметрів.
- Валідація: кидає `ERuntimeError` при відсутності контролера, класу або нестачі аргументів.
- Диспатч події `ControllerResolver::CONTROLLER_RESOLVED` після успішного створення.

## Конструктор

```php
public function __construct(ContainerInterface $container)
```

- Витягує з контейнера сервіс подій `Framework::SVC_EVENTS` і перевіряє, що він реалізує `EventManagerInterface`.

## Метод resolve

```php
public function resolve(array $routeInfo): object
```

1. Перевіряє наявність `$routeInfo['callback']['controller']`, інакше кидає `RuntimeException`.
2. Визначає:
   - якщо `callback['controller']` — рядок, повертає `$container->get($controller)`;
   - якщо масив з ключами `class` і опційно `args`, використовує рефлексію для створення екземпляра:
     - збирає аргументи конструктора за іменами з масиву `args` або використовує значення за замовчуванням;
     - при нестачі обов’язкових параметрів — кидає `ERuntimeError`.
3. Генерує подію:

```php
events->dispatch(
    self::CONTROLLER_RESOLVED,
    $this,
    ['controller' => $result]
);
```
4. Повертає створений об’єкт контролера.

## Приклади

### 1. Найпростіший контролер без аргументів
```php
namespace App\Controller;
class HomeController {}

$routeInfo = ['callback' => ['controller' => HomeController::class]];
$resolver = new ControllerResolver($container);
$controller = $resolver->resolve($routeInfo);
// еквівалентно: $container->get(HomeController::class)
```

### 2. Контролер із залежностями через аргументи
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
// екземпляр UserController створений з передачею залежностей у конструктор
```

### 3. Обробка помилок
```php
// a) Контролер не задано
$resolver->resolve(['callback' => []]);
// ERuntimeError: "Controller is not defined"

// b) Клас не існує
$resolver->resolve(['callback' => ['controller' => 'NonExistent']]);
// ERuntimeError: "Controller \"NonExistent\" does not exist"

// c) Відсутній обов’язковий параметр
class FooController { public function __construct($bar) {} }
$routeInfo = ['callback' => ['controller' => ['class' => FooController::class, 'args' => []]]];
$resolver->resolve($routeInfo);
// ERuntimeError: "Missing required parameter \"bar\" for \"FooController\""
```

## Події

- `ControllerResolver::CONTROLLER_RESOLVED` — виникає після успішного створення контролера і передає сам об’єкт контролера.

[Повернутись до змісту](../../index.md)