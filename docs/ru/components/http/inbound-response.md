[Вернуться к оглавлению](../../index.md)
# InboundResponse

`InboundResponse` — класс, представляющий HTTP-ответ, полученный от внешнего сервера,
расширяющий `Message` и реализующий PSR-7 `ResponseInterface`.

## Назначение

- Хранение HTTP-статуса, заголовков и тела ответа.
- Автоматический парсинг тела в зависимости от `Content-Type` (JSON, `application/x-www-form-urlencoded`, `text/plain`, `multipart/form-data` и др.).
- Сохранение исходного потока для повторного чтения.
- Удобный доступ к разобранным данным через метод `getParsedBody()`.

## Конструктор

```php
public function __construct(
    int $statusCode = 200,
    array $headers = [],
    ?StreamInterface $body = null,
    string $protocol = '1.1'
)
```

- `$statusCode` — HTTP-код ответа.
- `$headers` — ассоциативный массив заголовков.
- `$body` — объект `StreamInterface`; по умолчанию создаётся пустой поток.
- `$protocol` — версия протокола ("1.1" по умолчанию).

При создании сразу вызывается `parseBody()`, результат сохраняется в свойство `$parsedBody`.

## Методы

### static parseBody

```php
public static function parseBody(StreamInterface $body, string $contentType): mixed
```

- Читает содержимое потока, возвращая указатель на исходную позицию, если поток поддерживает `seek()`.
- В зависимости от `$contentType` возвращает:
  - `array` для JSON и urlencoded данных,
  - `string` для `text/plain` и неизвестных типов,
  - `array` для multipart-формы через `parseMultipartFormData()`.

### private parseMultipartFormData

```php
private static function parseMultipartFormData(string $data, string $contentType): array
```

- Извлекает `boundary` из `$contentType` через `extractBoundary()`.
- Делит тело по `boundary` и парсит каждую часть, поддерживая поля и файлы.

### private extractBoundary

```php
private static function extractBoundary(string $contentType): ?string
```

- Вычленяет параметр `boundary=...` из заголовка или возвращает `null`.

### getStatusCode

```php
public function getStatusCode(): int
```

- Возвращает HTTP-статус ответа.

### withStatus

```php
public function withStatus($code, $reasonPhrase = ''): static
```

- Возвращает клон объекта с изменённым кодом статуса.

### getReasonPhrase

```php
public function getReasonPhrase(): string
```

- Возвращает текстовое описание кода статуса с помощью `HttpHelper::getStatusMessage()`.

### getParsedBody

```php
public function getParsedBody(): mixed
```

- Возвращает результат парсинга тела, сохранённый при создании.

## Примеры

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

// 3. Текстовое тело
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

[Вернуться к оглавлению](../../index.md)

