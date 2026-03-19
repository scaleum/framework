[Вернутися до змісту](../../index.md)

[EN](../../../en/components/http/outbound-request.md) | **UK** | [RU](../../../ru/components/http/outbound-request.md)
# OutboundRequest

`OutboundRequest` — клас для формування вихідного HTTP-запиту до зовнішнього сервера,
що розширює `Message` та реалізує PSR-7 `RequestInterface`.

## Призначення

- Зберігання методу, URI, заголовків та тіла вихідного запиту.
- Підтримка синхронних та асинхронних запитів через прапорець `$async`.
- Зручна робота з тілом запиту через трейд `StreamTrait`.
- Відповідність PSR-7: методи `withMethod`, `withUri`, `withRequestTarget` повертають клон.

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

- `$method` — HTTP-метод (`GET`, `POST`, `PUT` тощо).
- `$uri` — екземпляр `UriInterface`.
- `$headers` — асоціативний масив заголовків.
- `$body` — рядок, ресурс або `StreamInterface`; готується через `prepareHeadersAndStream()`.
- `$protocol` — версія HTTP-протоколу.
- `$async` — `true` для асинхронного запиту.

Під час конструювання заголовки та тіло обробляються трейтом `MessagePayloadTrait`:
```php
$payload = $this->getMessagePayload($headers, $body);
```  
Після чого викликається `parent::__construct($this->headers, $this->body, $protocol)`.

## Методи

| Метод                              | Опис                                                         |
|:-----------------------------------|:-------------------------------------------------------------|
| `getRequestTarget(): string`       | Повертає рядок запиту (`$requestTarget` або URI).            |
| `withRequestTarget($target): static` | Клонує запит і встановлює новий `requestTarget`.             |
| `getMethod(): string`              | Повертає HTTP-метод.                                          |
| `withMethod($method): static`      | Повертає клон із вказаним методом у верхньому регістрі.      |
| `getUri(): UriInterface`           | Повертає поточний URI.                                       |
| `withUri(UriInterface $uri, bool $preserveHost=false): static` | Клонує запит із новим URI, оновлюючи заголовок `Host`. |
| `isAsync(): bool`                  | Повертає прапорець асинхронності.                            |
| `setAsync(bool $async): void`      | Встановлює прапорець асинхронності для поточного об’єкта.    |

## Приклади

### 1. Простий GET-запит
```php
use Scaleum\Http\OutboundRequest;
use Scaleum\Http\Stream;
use Scaleum\Stdlib\Helpers\UriHelper;

$uri     = UriHelper::create('https://api.example.com/items');
$request = new OutboundRequest('GET', $uri, ['Accept' => 'application/json']);

// Відправка запиту через клієнт...
```

### 2. POST з JSON-тілом
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

// Тіло буде поміщене у stream, заголовок Content-Length розрахований автоматично
```

### 3. Зміна методу та URI через PSR-7
```php
$request2 = $request
    ->withMethod('PUT')
    ->withRequestTarget('/users/42')
    ->withUri(UriHelper::create('https://api.example.com/users/42'));
```

### 4. Асинхронний запит
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
    // Клієнт виконає запит без очікування відповіді
}
```

[Вернутися до змісту](../../index.md)