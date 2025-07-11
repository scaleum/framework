[Вернуться к оглавлению](../../index.md)
# OutboundResponse

`OutboundResponse` — класс для формирования и отправки HTTP-ответа клиенту,
реализующий PSR-7 `ResponseInterface` и `ResponderInterface`.

## Назначение

- Хранение HTTP-статуса, заголовков и тела ответа.
- Подготовка заголовков и тела через трейд `MessagePayloadTrait` (автоматический расчёт `Content-Length`).
- Отправка ответа клиенту методом `send()`.

## Конструктор

```php
public function __construct(
    int $statusCode = 200,
    array $headers = [],
    mixed $body = null,
    string $protocol = '1.1'
)
```

- `$statusCode` — HTTP-код (200, 404, 500 и т.п.).
- `$headers` — ассоциативный массив заголовков; трейд `MessagePayloadTrait` подготовит поток и установит необходимые заголовки.
- `$body` — строка, ресурс или `StreamInterface`.
- `$protocol` — версия протокола.

## Методы

| Метод                                     | Описание                                                           |
|:------------------------------------------|:-------------------------------------------------------------------|
| `getStatusCode(): int`                    | Возвращает текущий код статуса.                                    |
| `withStatus(int $code, string $reasonPhrase = ''): static` | Клонирует объект и устанавливает новый статус.       |
| `getReasonPhrase(): string`               | Возвращает текстовую фразу статуса через `HttpHelper::getStatusMessage()`. |
| `send(): void`                            | Отправляет HTTP-заголовки и тело клиенту, используя `header()` и `fpassthru()`. |

## Примеры

### 1. Простой HTML-ответ
```php
use Scaleum\Http\OutboundResponse;

$html = "<h1>Welcome!</h1><p>Hello, world!</p>";
$response = new OutboundResponse(
    statusCode: 200,
    headers: ['Content-Type' => 'text/html; charset=UTF-8'],
    body: $html
);

$response->send();
// Клиент получит HTML-страницу с кодом 200
```

### 2. JSON-ответ для API
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
// Клиент получит JSON и заголовок Content-Length автоматически
```

### 3. Ответ с файлом для скачивания
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
// Браузер предложит сохранить файл report.pdf
```

### 4. Ответ об ошибке 404
```php
use Scaleum\Http\OutboundResponse;

$response = new OutboundResponse(
    statusCode: 404,
    headers: ['Content-Type' => 'text/plain'],
    body: 'Page not found'
);

$response->send();
// Клиент получит текст "Page not found" с кодом 404
```

### 5. Потоковый ответ (SSE)
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
// Клиент получит серию событий SSE
```

[Вернуться к оглавлению](../../index.md)