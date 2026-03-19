[Повернутися до змісту](../../index.md)

[EN](../../../en/components/http/inbound-request.md) | **UK** | [RU](../../../ru/components/http/inbound-request.md)
# InboundRequest

`InboundRequest` — клас вхідного HTTP-запиту у фреймворку Scaleum,
що розширює базовий `Message` та реалізує PSR-7 інтерфейс `ServerRequestInterface`.

## Призначення

- Отримання та збереження даних запиту: методу, URI, заголовків, тіла, параметрів, файлів, кукі та серверних змінних.
- Парсинг тіла запиту залежно від `Content-Type` (JSON, form-data, urlencoded, XML, plain-text).
- Санітизація глобальних даних (`$_GET`, `$_POST`, `$_COOKIE`, `$_FILES`).
- Нормалізація масиву файлів для підтримки одиночних та множинних завантажень.
- Робота з атрибутами запиту за PSR-7 (getAttribute, withAttribute та без атрибута).

## Конструктор

```php
public function __construct(
    string $method,
    UriInterface $uri,
    array $serverParams = [],
    array $headers = [],
    ?StreamInterface $body = null,
    array $queryParams = [],
    ?array $parsedBody = null,
    array $cookieParams = [],
    array $files = [],
    string $protocol = '1.1'
)
```

- `$method` — HTTP-метод (GET, POST, PUT тощо).
- `$uri` — екземпляр `UriInterface`.
- `$serverParams`, `$headers`, `$cookieParams`, `$files`, `$queryParams` — відповідні PSR-7 колекції.
- Якщо `$parsedBody === null`, викликається `parseBody()`.

## Основні методи

| Метод                                      | Опис                                                                                  |
|:-------------------------------------------|:--------------------------------------------------------------------------------------|
| `parseBody(?StreamInterface $body, string $contentType, string $method): mixed` | Парсить тіло запиту за типом: JSON, form-data, urlencoded, XML, plain-text.           |
| `normalizeFiles(array $files): array`      | Приводить `$_FILES` до уніфікованого вигляду для одиночних та множинних завантажень.   |
| `sanitize(): void`                         | Санітизує глобальні масиви `$_GET`, `$_POST`, `$_COOKIE`, очищаючи ключі та дані.      |
| `fromGlobals(): self`                      | Створює об’єкт із глобальних змінних з автоматичною санітизацією.                      |
| `getInputParams(): array`                  | Повертає параметри GET/HEAD/OPTIONS або розібране `parsedBody` для POST тощо.          |
| `getInputParam(string $param, mixed $default = null): mixed` | Зручний доступ до одного параметра запиту.                     |
| `getAttribute(string $name, $default = null): mixed`   | Читає PSR-7 атрибут запиту.                                        |
| `withAttribute(string $name, $value): static`         | Повертає клон з доданим атрибутом.                             |
| `withoutAttribute(string $name): static`              | Видаляє атрибут і повертає клон.                              |
| `withMethod(string $method): static`                   | Повертає клон із зміненим HTTP-методом.                        |
| `withUri(UriInterface $uri, bool $preserveHost = false): static` | Оновлює URI та заголовок Host за потреби.                   |

## Приклад використання

```php
use Scaleum\Stdlib\Helpers\HttpHelper;

$request = InboundRequest::fromGlobals();

// Отримати параметр 'id' із запиту або null, якщо відсутній
$id = $request->getInputParam('id', null);

// Додати атрибут 'userId' до запиту
$requestWithUser = $request->withAttribute('userId', $user->getId());

// Перевірити метод і контент
if ($request->getMethod() === HttpHelper::METHOD_POST) {
    $data = $request->getParsedBody();
    // ... обробка POST-даних ...
}
```

[Повернутися до змісту](../../index.md)