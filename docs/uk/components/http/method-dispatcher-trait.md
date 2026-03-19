[Вернутися до змісту](../../index.md)

[EN](../../../en/components/http/method-dispatcher-trait.md) | **UK** | [RU](../../../ru/components/http/method-dispatcher-trait.md)
# MethodDispatcherTrait

`MethodDispatcherTrait` — трейд для маршрутизації вхідних викликів на методи контролера за шаблоном `HTTP_метод_шлях`, де шлях формується з сегментів маршруту. Забезпечує гнучкий механізм «dispatch» без явного вказання методу.

## Призначення

- Автоматичне обчислення імені цільового методу на основі комбінації HTTP-методу та частини маршруту.
- Пошук найбільш точного співпадіння серед усіх можливих префіксів маршруту.
- Підтримка вкладених сегментів: перевіряється кожне наростаюче поєднання сегментів.
- Викидання `EHttpException(404)` при відсутності відповідного методу.
- Зручна точка входу `__dispatch()` для універсальної обробки всіх дій контролера.

## Вимоги

Клас, що використовує трейд, повинен реалізувати метод `getRequest(): InboundRequest`, який повертає поточний об’єкт запиту.

## Методи трейту

### __methodName()
```php
public function __methodName(string $str): string
```
- Генерує ім’я методу контролера у `camelCase`, об’єднуючи:
  - HTTP-метод (із `$this->getRequest()->getMethod()`).
  - Символьний рядок `$str` (частина шляху без розширення).
- Приклад для GET-запиту `user/profile`: `getUserProfile`.

### __dispatch()
```php
public function __dispatch(): ResponderInterface
```
1. Отримує всі аргументи `$args` — сегменти маршруту (наприклад, `['user', 'profile', 42]`).
2. Послідовно нарощує рядок `$route` із сегментів, перевіряючи для кожного нового поєднання наявність методу:
   ```php
   // Ітеруємо по сегментах
   foreach ($args as $segment) {
       $route .= ($route ? '_' : '') . $segment;
       $candidate = $this->__methodName($route);
       if (method_exists($this, $candidate)) {
           // Зберігаємо останню успішну комбінацію та залишкові аргументи
           $matched = ['route' => $route, 'args' => ...];
       }
   }
   ```
3. Після циклу обирає найбільш довге співпадіння (`$matched`), визначає остаточний `$route` і `$args`.
4. Обчислює ім’я методу через `__methodName($route)`.
5. Якщо методу немає — кидає `EHttpException(404, 'Unknown method ...')`.
6. Викликає метод контролера, передаючи `$args`.

## Приклади

### 1. Проста маршрутизація без вкладень
```php
class UserController {
    use MethodDispatcherTrait;

    public function getUser(): ResponderInterface {
        // Обробка /user для GET-запиту
    }
}

// Зовнішній виклик:
$controller = new UserController();
// Припустимо, getRequest()->getMethod() === 'GET'
$response = $controller->__dispatch('user');
// Викличе getUser($args=[])
```

### 2. Вкладені сегменти
```php
class ArticleController {
    use MethodDispatcherTrait;

    public function getArticleList(string $category): ResponderInterface {
        //...
    }
}

// GET-запит до /article/list/sports
$response = $controller->__dispatch('article', 'list', 'sports');
// Пошук методів:
//  - getArticle - відсутній
//  - getArticleList - викликається з аргументом 'sports'
```

### 3. Обробка відсутнього методу
```php
class TestController {
    use MethodDispatcherTrait;

    // Нема методу для GET /test/unknown
}

try {
    $controller = new TestController();
    $controller->__dispatch('test', 'unknown');
} catch (EHttpException $e) {
    // Код 404, повідомлення 'Unknown method TestController::getTestUnknown'
}
```

[Вернутися до змісту](../../index.md)