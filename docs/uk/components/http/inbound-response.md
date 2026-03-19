[Повернутись до змісту](../../index.md)

[EN](../../../en/components/http/inbound-response.md) | **UK** | [RU](../../../ru/components/http/inbound-response.md)
# InboundResponse

`InboundResponse` — клас, що представляє HTTP-відповідь, отриману від зовнішнього сервера,
розширює `Message` та реалізує PSR-7 `ResponseInterface`.

## Призначення

- Збереження HTTP-статусу, заголовків та тіла відповіді.
- Автоматичний парсинг тіла залежно від `Content-Type` (JSON, `application/x-www-form-urlencoded`, `text/plain`, `multipart/form-data` тощо).
- Збереження початкового потоку для повторного читання.
- Зручний доступ до розібраних даних через метод `getParsedBody()`.

## Конструктор

```php
public function __construct(
    int $statusCode = 200,
    array $headers = [],
    ?StreamInterface $body = null,
    string $protocol = '1.1'
)
```

- `$statusCode` — HTTP-код відповіді.
- `$headers` — асоціативний масив заголовків.
- `$body` — об’єкт `StreamInterface`; за замовчуванням створюється порожній потік.
- `$protocol` — версія протоколу ("1.1" за замовчуванням).

Під час створення одразу викликається `parseBody()`, результат зберігається у властивість `$parsedBody`.

## Методи

### static parseBody

```php
public static function parseBody(StreamInterface $body, string $contentType): mixed
```

- Читає вміст потоку, повертаючи вказівник на початкову позицію, якщо потік підтримує `seek()`.
- Залежно від `$contentType` повертає:
  - `array` для JSON та urlencoded даних,
  - `string` для `text/plain` та невідомих типів,
  - `array` для multipart-форми через `parseMultipartFormData()`.

### private parseMultipartFormData

```php
private static function parseMultipartFormData(string $data, string $contentType): array
```

- Витягує `boundary` з `$contentType` через `extractBoundary()`.
- Ділить тіло за `boundary` та парсить кожну частину, підтримуючи поля та файли.

### private extractBoundary

```php
private static function extractBoundary(string $contentType): ?string
```

- Витягує параметр `boundary=...` із заголовка або повертає `null`.

### getStatusCode

```php
public function getStatusCode(): int
```

- Повертає HTTP-статус відповіді.

### withStatus

```php
public function withStatus($code, $reasonPhrase = ''): static
```

- Повертає клон об’єкта з зміненим кодом статусу.

### getReasonPhrase

```php
public function getReasonPhrase(): string
```

- Повертає текстовий опис коду статусу за допомогою `HttpHelper::getStatusMessage()`.

### getParsedBody

```php
public function getParsedBody(): mixed
```

- Повертає результат парсингу тіла, збережений під час створення.

## Приклади

```php
use Scaleum\Http\InboundResponse;
use Scaleum\Http\Stream;

// 1. Парсинг JSON
$jsonStream = new Stream(fopen('php://temp', 'r+'));
$jsonStream->write(json_encode(['foo' => 'bar']));
$jsonStream->rewind();

$response = new InboundResponse(
    statusCode: 200,
    headers: ['Content-Type' => 'application/json'],
    body: $jsonStream
);

$data   = $response->getParsedBody();     // ['foo' => 'bar']
$status = $response->getStatusCode();     // 200
$reason = $response->getReasonPhrase();   // "OK"

// 2. Парсинг URL-encoded
$formStream = new Stream(fopen('php://temp', 'r+'));
$formStream->write('a=1&b=2');
$formStream->rewind();

$response = new InboundResponse(
    headers: ['Content-Type' => 'application/x-www-form-urlencoded'],
    body: $formStream
);

$params = $response->getParsedBody();    // ['a' => '1', 'b' => '2']

// 3. Текстове тіло
$textStream = new Stream(fopen('php://temp', 'r+'));
$textStream->write("Hello\nWorld");
$textStream->rewind();

$response = new InboundResponse(
    headers: ['Content-Type' => 'text/plain'],
    body: $textStream
);

$text = $response->getParsedBody();       // "Hello\nWorld"

// 4. Multipart-форма
$boundary  = '----WebKitFormBoundaryXYZ';
$multipart = "--{$boundary}\r\n" .
             "Content-Disposition: form-data; name=\"field1\"\r\n\r\n" .
             "value1\r\n" .
             "--{$boundary}--";

$mpStream = new Stream(fopen('php://temp', 'r+'));
$mpStream->write($multipart);
$mpStream->rewind();

$response = new InboundResponse(
    headers: ['Content-Type' => "multipart/form-data; boundary={$boundary}"],
    body: $mpStream
);

$form = $response->getParsedBody();     // ['field1' => 'value1']
```

[Повернутись до змісту](../../index.md)