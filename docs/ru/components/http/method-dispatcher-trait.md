[Вернуться к оглавлению](../../index.md)
# MethodDispatcherTrait

`MethodDispatcherTrait` — трейт для маршрутизации входящих вызовов на методы контроллера по шаблону `HTTP_метод_путь`, где путь формируется из сегментов маршрута. Обеспечивает гибкий механизм «dispatch» без явного указания метода.

## Назначение

- Автоматическое вычисление имени целевого метода на основе комбинации HTTP-метода и части маршрута.
- Поиск наиболее точного совпадения среди всех возможных префиксов маршрута.
- Поддержка вложенных сегментов: проверяется каждое нарастающее сочетание сегментов.
- Бросок `EHttpException(404)` при отсутствии соответствующего метода.
- Удобная точка входа `__dispatch()` для универсальной обработки всех действий контроллера.

## Требования

Класс, использующий трейт, должен реализовать метод `getRequest(): InboundRequest`, возвращающий текущий объект запроса.

## Методы трейта

### __methodName
```php
public function __methodName(string $str): string
```
- Генерирует имя метода контроллера в `camelCase`, объединив:
  - HTTP-метод (из `$this->getRequest()->getMethod()`).
  - Символьную строку `$str` (часть пути без расширения).
- Пример для GET-запроса `user/profile`: `getUserProfile`.

### __dispatch
```php
public function __dispatch(): ResponderInterface
```
1. Получает все аргументы `$args` — сегменты маршрута (например, `['user', 'profile', 42]`).
2. Последовательно наращивает строку `$route` из сегментов, проверяя для каждого нового сочетания наличие метода:
   ```php
   // Итерируем по сегментам
   foreach ($args as $segment) {
       $route .= ($route ? '_' : '') . $segment;
       $candidate = $this->__methodName($route);
       if (method_exists($this, $candidate)) {
           // Сохраняем последнюю успешную комбинацию и оставшиеся аргументы
           $matched = ['route' => $route, 'args' => ...];
       }
   }
   ```
3. После цикла выбирает наиболее длинное совпадение (`$matched`), определяет окончательный `$route` и `$args`.
4. Вычисляет имя метода через `__methodName($route)`.
5. Если метода нет — кидает `EHttpException(404, 'Unknown method ...')`.
6. Вызывает метод контроллера, передавая `$args`.

## Примеры

### 1. Простая маршрутизация без вложений
```php
class UserController {
    use MethodDispatcherTrait;

    public function getUser(InboundRequest $request): ResponderInterface {
        // Обработка /user для GET-запроса
    }
}

// Внешний вызов:
$controller = new UserController();
// Предположим, getRequest()->getMethod() === 'GET'
$response = $controller->__dispatch('user');
// Вызовет getUser($args=[])
```

### 2. Вложенные сегменты
```php
class ArticleController {
    use MethodDispatcherTrait;

    public function getArticleList(): ResponderInterface { /* GET /article/list */ }
    public function getArticleListByCategory(string $category): ResponderInterface { /* GET /article/list/category */ }
}

// GET-запрос к /article/list/sports
$response = $controller->__dispatch('article', 'list', 'sports');
// Поиск методов:
//  - getArticle
//  - getArticleList
//  - getArticleListSports — отсутствует
// Выберется getArticleListByCategory с args=['sports']
```

### 3. Обработка отсутствующего метода
```php
class TestController {
    use MethodDispatcherTrait;

    // Нет метода для GET /test/unknown
}

try {
    $controller = new TestController();
    $controller->__dispatch('test', 'unknown');
} catch (EHttpException $e) {
    // Код 404, сообщение 'Unknown method TestController::getTestUnknown'
}
```

[Вернуться к оглавлению](../../index.md)