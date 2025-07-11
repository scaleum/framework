[Вернуться к оглавлению](../../index.md)
# OutboundRequest

`OutboundRequest` — класс для формирования исходящего HTTP-запроса к внешнему серверу,
расширяющий `Message` и реализующий PSR-7 `RequestInterface`.

## Назначение

- Хранение метода, URI, заголовков и тела исходящего запроса.
- Поддержка синхронных и асинхронных запросов через флаг `$async`.
- Комфортная работа с телом запроса через трейд `StreamTrait`.
- Соответствие PSR-7: методы `withMethod`, `withUri`, `withRequestTarget` возвращают клон.

## Конструктор

```php
public function __construct(
    string $method,
    UriInterface $uri,
    array $headers = [],
    mixed $body = null,
    string $protocol = '1.1',
    bool $async = false
)
```

- `$method` — HTTP-метод (`GET`, `POST`, `PUT` и т.п.).
- `$uri` — экземпляр `UriInterface`.
- `$headers` — ассоциативный массив заголовков.
- `$body` — строка, ресурс или `StreamInterface`; готовится через `prepareHeadersAndStream()`.
- `$protocol` — версия HTTP-протокола.
- `$async` — `true` для асинхронного запроса.

При конструировании заголовки и тело обрабатываются трейтом `MessagePayloadTrait`:
```php
$payload = $this->getMessagePayload($headers, $body);
```  
После чего вызывается `parent::__construct($this->headers, $this->body, $protocol)`.

## Методы

| Метод                              | Описание                                                      |
|:-----------------------------------|:--------------------------------------------------------------|
| `getRequestTarget(): string`       | Возвращает строку запроса (`$requestTarget` или URI).         |
| `withRequestTarget($target): static` | Клонирует запрос и устанавливает новый `requestTarget`.        |
| `getMethod(): string`              | Возвращает HTTP-метод.                                         |
| `withMethod($method): static`      | Возвращает клон с указанным методом в верхнем регистре.        |
| `getUri(): UriInterface`           | Возвращает текущий URI.                                       |
| `withUri(UriInterface $uri, bool $preserveHost=false): static` | Клонирует запрос с новым URI, обновляя заголовок `Host`. |
| `isAsync(): bool`                  | Возвращает флаг асинхронности.                                 |
| `setAsync(bool $async): void`      | Устанавливает флаг асинхронности для текущего объекта.         |

## Примеры

### 1. Простая GET-запрос
```php
use Scaleum\Http\OutboundRequest;
use Scaleum\Http\Stream;
use Scaleum\Stdlib\Helpers\UriHelper;

$uri     = UriHelper::create('https://api.example.com/items');
$request = new OutboundRequest('GET', $uri, ['Accept' => 'application/json']);

// Отправка запроса через клиент...
```

### 2. POST с JSON-телом
```php
$data    = ['name' => 'John', 'age' => 30];
$json    = json_encode($data);
$uri     = UriHelper::create('https://api.example.com/users');
$request = new OutboundRequest(
    'POST',
    $uri,
    ['Content-Type' => 'application/json'],
    $json
);

// Тело будет помещено в stream, заголовок Content-Length рассчитан автоматически
```

### 3. Изменение метода и URI через PSR-7
```php
$request2 = $request
    ->withMethod('PUT')
    ->withRequestTarget('/users/42')
    ->withUri(UriHelper::create('https://api.example.com/users/42'));
```

### 4. Асинхронный запрос
```php
$requestAsync = new OutboundRequest(
    'GET',
    UriHelper::create('https://api.example.com/long'),
    [],
    null,
    '1.1',
    true
);

if ($requestAsync->isAsync()) {
    // Клиент выполнит запрос без ожидания ответа
}
```

[Вернуться к оглавлению](../../index.md)

