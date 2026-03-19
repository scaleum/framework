[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/http/method-dispatcher-trait.md) | [RU](../../../ru/components/http/method-dispatcher-trait.md)
#  MethodDispatcherTrait

`MethodDispatcherTrait` is a trait for routing incoming calls to controller methods following the pattern `HTTP_method_path`, where the path is formed from route segments. It provides a flexible "dispatch" mechanism without explicitly specifying the method.

##  Purpose

- Automatic calculation of the target method name based on the combination of HTTP method and part of the route.
- Searching for the most precise match among all possible route prefixes.
- Support for nested segments: each incremental combination of segments is checked.
- Throws `EHttpException(404)` if no corresponding method is found.
- Convenient entry point `__dispatch()` for universal handling of all controller actions.

##  Requirements

The class using the trait must implement the method `getRequest(): InboundRequest`, returning the current request object.

##  Trait Methods

###  __methodName()
```php
public function __methodName(string $str): string
```
- Generates the controller method name in `camelCase` by combining:
  - HTTP method (from `$this->getRequest()->getMethod()`).
  - The string `$str` (part of the path without extension).
- Example for a GET request `user/profile`: `getUserProfile`.

###  __dispatch()
```php
public function __dispatch(): ResponderInterface
```
1. Retrieves all arguments `$args` — route segments (e.g., `['user', 'profile', 42]`).
2. Sequentially builds the string `$route` from segments, checking for each new combination if a method exists:
   ```php
   // Iterate over segments
   foreach ($args as $segment) {
       $route .= ($route ? '_' : '') . $segment;
       $candidate = $this->__methodName($route);
       if (method_exists($this, $candidate)) {
           // Save the last successful combination and remaining arguments
           $matched = ['route' => $route, 'args' => ...];
       }
   }
   ```
3. After the loop, selects the longest match (`$matched`), determines the final `$route` and `$args`.
4. Computes the method name via `__methodName($route)`.
5. If the method does not exist — throws `EHttpException(404, 'Unknown method ...')`.
6. Calls the controller method, passing `$args`.

##  Examples

###  1. Simple routing without nesting
```php
class UserController {
    use MethodDispatcherTrait;

    public function getUser(): ResponderInterface {
        // Handling /user for GET request
    }
}

// External call:
$controller = new UserController();
// Assume getRequest()->getMethod() === 'GET'
$response = $controller->__dispatch('user');
// Will call getUser($args=[])
```

###  2. Nested segments
```php
class ArticleController {
    use MethodDispatcherTrait;

    public function getArticleList(string $category): ResponderInterface {
        //...
    }
}

// GET request to /article/list/sports
$response = $controller->__dispatch('article', 'list', 'sports');
// Method search:
//  - getArticle - not found
//  - getArticleList - called with argument 'sports'
```

###  3. Handling missing method
```php
class TestController {
    use MethodDispatcherTrait;

    // No method for GET /test/unknown
}

try {
    $controller = new TestController();
    $controller->__dispatch('test', 'unknown');
} catch (EHttpException $e) {
    // Code 404, message 'Unknown method TestController::getTestUnknown'
}
```

[Back to Contents](../../index.md)