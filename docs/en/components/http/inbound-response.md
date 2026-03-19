[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/http/inbound-response.md) | [RU](../../../ru/components/http/inbound-response.md)
#  InboundResponse

`InboundResponse` is a class representing an HTTP response received from an external server,
extending `Message` and implementing the PSR-7 `ResponseInterface`.

##  Purpose

- Storing HTTP status, headers, and response body.
- Automatic parsing of the body depending on `Content-Type` (JSON, `application/x-www-form-urlencoded`, `text/plain`, `multipart/form-data`, etc.).
- Preserving the original stream for re-reading.
- Convenient access to parsed data via the `getParsedBody()` method.

##  Constructor

```php
public function __construct(
    int $statusCode = 200,
    array $headers = [],
    ?StreamInterface $body = null,
    string $protocol = '1.1'
)
```

- `$statusCode` — HTTP response code.
- `$headers` — associative array of headers.
- `$body` — `StreamInterface` object; by default, an empty stream is created.
- `$protocol` — protocol version ("1.1" by default).

`parseBody()` is called immediately upon creation, and the result is stored in the `$parsedBody` property.

##  Methods

###  static parseBody

```php
public static function parseBody(StreamInterface $body, string $contentType): mixed
```

- Reads the stream content, returning the pointer to the original position if the stream supports `seek()`.
- Depending on `$contentType`, returns:
  - `array` for JSON and urlencoded data,
  - `string` for `text/plain` and unknown types,
  - `array` for multipart form via `parseMultipartFormData()`.

###  private parseMultipartFormData

```php
private static function parseMultipartFormData(string $data, string $contentType): array
```

- Extracts the `boundary` from `$contentType` via `extractBoundary()`.
- Splits the body by `boundary` and parses each part, supporting fields and files.

###  private extractBoundary

```php
private static function extractBoundary(string $contentType): ?string
```

- Extracts the `boundary=...` parameter from the header or returns `null`.

###  getStatusCode

```php
public function getStatusCode(): int
```

- Returns the HTTP status of the response.

###  withStatus

```php
public function withStatus($code, $reasonPhrase = ''): static
```

- Returns a clone of the object with the modified status code.

###  getReasonPhrase

```php
public function getReasonPhrase(): string
```

- Returns the textual description of the status code using `HttpHelper::getStatusMessage()`.

###  getParsedBody

```php
public function getParsedBody(): mixed
```

- Returns the result of body parsing saved at creation.

##  Examples

```php
use Scaleum\Http\InboundResponse;
use Scaleum\Http\Stream;

// 1. JSON parsing
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

// 2. URL-encoded parsing
$formStream = new Stream(fopen('php://temp', 'r+'));
$formStream->write('a=1&b=2');
$formStream->rewind();

$response = new InboundResponse(
    headers: ['Content-Type' => 'application/x-www-form-urlencoded'],
    body: $formStream
);

$params = $response->getParsedBody();    // ['a' => '1', 'b' => '2']

// 3. Text body
$textStream = new Stream(fopen('php://temp', 'r+'));
$textStream->write("Hello\nWorld");
$textStream->rewind();

$response = new InboundResponse(
    headers: ['Content-Type' => 'text/plain'],
    body: $textStream
);

$text = $response->getParsedBody();       // "Hello\nWorld"

// 4. Multipart form
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

[Back to Contents](../../index.md)

