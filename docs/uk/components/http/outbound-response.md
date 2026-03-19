[Повернутись до змісту](../../index.md)

[EN](../../../en/components/http/outbound-response.md) | **UK** | [RU](../../../ru/components/http/outbound-response.md)
# OutboundResponse

`OutboundResponse` — клас для формування та відправки HTTP-відповіді клієнту,
який реалізує PSR-7 `ResponseInterface` та `ResponderInterface`.

## Призначення

- Зберігання HTTP-статусу, заголовків та тіла відповіді.
- Підготовка заголовків і тіла через трейд `MessagePayloadTrait` (автоматичний розрахунок `Content-Length`).
- Відправка відповіді клієнту методом `send()`.

## Конструктор

```php
public function __construct(
    int $statusCode = 200,
    array $headers = [],
    mixed $body = null,
    string $protocol = '1.1'
)
```

- `$statusCode` — HTTP-код (200, 404, 500 тощо).
- `$headers` — асоціативний масив заголовків; трейд `MessagePayloadTrait` підготує потік і встановить необхідні заголовки.
- `$body` — рядок, ресурс або `StreamInterface`.
- `$protocol` — версія протоколу.

## Методи

| Метод                                     | Опис                                                               |
|:------------------------------------------|:-------------------------------------------------------------------|
| `getStatusCode(): int`                    | Повертає поточний код статусу.                                     |
| `withStatus(int $code, string $reasonPhrase = ''): static` | Клонує об’єкт і встановлює новий статус.          |
| `getReasonPhrase(): string`               | Повертає текстову фразу статусу через `HttpHelper::getStatusMessage()`. |
| `send(): void`                            | Відправляє HTTP-заголовки та тіло клієнту, використовуючи `header()` і `fpassthru()`. |

## Приклади

### 1. Простий HTML-відповідь
```php
use Scaleum\Http\OutboundResponse;

$html = "<h1>Welcome!</h1><p>Hello, world!</p>";
$response = new OutboundResponse(
    statusCode: 200,
    headers: ['Content-Type' => 'text/html; charset=UTF-8'],
    body: $html
);

$response->send();
// Клієнт отримає HTML-сторінку з кодом 200
```

### 2. JSON-відповідь для API
```php
use Scaleum\Http\OutboundResponse;

$data = ['success' => true, 'data' => ['id' => 123]];
$json = json_encode($data);

$response = new OutboundResponse(
    statusCode: 200,
    headers: ['Content-Type' => 'application/json'],
    body: $json
);

$response->send();
// Клієнт отримає JSON і заголовок Content-Length автоматично
```

### 3. Відповідь з файлом для завантаження
```php
use Scaleum\Http\OutboundResponse;
use Scaleum\Http\Stream;

$stream = new Stream(fopen('/path/to/file.pdf', 'rb'));
$response = new OutboundResponse(
    statusCode: 200,
    headers: [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="report.pdf"'
    ],
    body: $stream
);

$response->send();
// Браузер запропонує зберегти файл report.pdf
```

### 4. Відповідь про помилку 404
```php
use Scaleum\Http\OutboundResponse;

$response = new OutboundResponse(
    statusCode: 404,
    headers: ['Content-Type' => 'text/plain'],
    body: 'Page not found'
);

$response->send();
// Клієнт отримає текст "Page not found" з кодом 404
```

### 5. Потокова відповідь (SSE)
```php
use Scaleum\Http\OutboundResponse;
use Scaleum\Http\Stream;

$callback = function() {
    for ($i = 1; $i <= 5; $i++) {
        echo "data: message {$i}\n\n";
        flush();
        sleep(1);
    }
};

$stream = new Stream(fopen('php://temp', 'r+'));
$callback();
$stream->rewind();

$response = new OutboundResponse(
    statusCode: 200,
    headers: ['Content-Type' => 'text/event-stream', 'Cache-Control' => 'no-cache'],
    body: $stream
);

$response->send();
// Клієнт отримає серію подій SSE
```

[Повернутись до змісту](../../index.md)