[Вернуться к оглавлению](../../index.md)
# InboundRequest

`InboundRequest` — класс входящего HTTP-запроса во фреймворке Scaleum,
расширяющий базовый `Message` и реализующий PSR-7 интерфейс `ServerRequestInterface`.

## Назначение

- Получение и хранение данных запроса: метода, URI, заголовков, тела, параметров, файлов, куки и серверных переменных.
- Парсинг тела запроса в зависимости от `Content-Type` (JSON, form-data, urlencoded, XML, plain-text).
- Санитизация глобальных данных (`$_GET`, `$_POST`, `$_COOKIE`, `$_FILES`).
- Нормализация массива файлов для поддержки одиночных и множественных загрузок.
- Работа с атрибутами запроса по PSR-7 (getAttribute, withAttribute и без атрибута).

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

- `$method` — HTTP-метод (GET, POST, PUT и т.п.).
- `$uri` — экземпляр `UriInterface`.
- `$serverParams`, `$headers`, `$cookieParams`, `$files`, `$queryParams` — соответствующие PSR-7 коллекции.
- Если `$parsedBody === null`, вызывается `parseBody()`.

## Основные методы

| Метод                                      | Описание                                                                              |
|:-------------------------------------------|:--------------------------------------------------------------------------------------|
| `parseBody(?StreamInterface $body, string $contentType, string $method): mixed` | Парсит тело запроса по типу: JSON, form-data, urlencoded, XML, plain-text.            |
| `normalizeFiles(array $files): array`      | Приводит `$_FILES` к единообразному виду для одиночных и множественных загрузок.       |
| `sanitize(): void`                         | Санитизирует глобальные массивы `$_GET`, `$_POST`, `$_COOKIE`, очищая ключи и данные. |
| `fromGlobals(): self`                      | Создаёт объект из глобальных переменных с автоматической санацией.                     |
| `getInputParams(): array`                  | Возвращает параметры GET/HEAD/OPTIONS или разобранный `parsedBody` для POST и др.     |
| `getInputParam(string $param, mixed $default = null): mixed` | Удобный доступ к одному параметру запроса.                    |
| `getAttribute(string $name, $default = null): mixed`   | Читает PSR-7 атрибут запроса.                                       |
| `withAttribute(string $name, $value): static`         | Возвращает клон с добавленным атрибутом.                            |
| `withoutAttribute(string $name): static`              | Удаляет атрибут и возвращает клон.                                 |
| `withMethod(string $method): static`                   | Возвращает клон с изменённым HTTP-методом.                         |
| `withUri(UriInterface $uri, bool $preserveHost = false): static` | Обновляет URI и заголовок Host при необходимости.                |

## Пример использования

```php
use Scaleum\Stdlib\Helpers\HttpHelper;

$request = InboundRequest::fromGlobals();

// Получить параметр 'id' из запроса или null, если отсутствует
$id = $request->getInputParam('id', null);

// Добавить атрибут 'userId' к запросу
$requestWithUser = $request->withAttribute('userId', $user->getId());

// Проверить метод и контент
if ($request->getMethod() === HttpHelper::METHOD_POST) {
    $data = $request->getParsedBody();
    // ... обработка POST-данных ...
}
```

[Вернуться к оглавлению](../../index.md)

