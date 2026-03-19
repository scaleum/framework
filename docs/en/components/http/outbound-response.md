[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/http/outbound-response.md) | [RU](../../../ru/components/http/outbound-response.md)
#  OutboundResponse

`OutboundResponse` is a class for forming and sending an HTTP response to the client,
implementing PSR-7 `ResponseInterface` and `ResponderInterface`.

##  Purpose

- Storing HTTP status, headers, and response body.
- Preparing headers and body via the `MessagePayloadTrait` trait (automatic calculation of `Content-Length`).
- Sending the response to the client using the `send()` method.

##  Constructor

```php
public function __construct(
    int $statusCode = 200,
    array $headers = [],
    mixed $body = null,
    string $protocol = '1.1'
)
```

- `$statusCode` — HTTP code (200, 404, 500, etc.).
- `$headers` — associative array of headers; the `MessagePayloadTrait` will prepare the stream and set necessary headers.
- `$body` — string, resource, or `StreamInterface`.
- `$protocol` — protocol version.

##  Methods

| Method                                     | Description                                                        |
|:------------------------------------------|:------------------------------------------------------------------|
| `getStatusCode(): int`                    | Returns the current status code.                                  |
| `withStatus(int $code, string $reasonPhrase = ''): static` | Clones the object and sets a new status.            |
| `getReasonPhrase(): string`               | Returns the status reason phrase via `HttpHelper::getStatusMessage()`. |
| `send(): void`                            | Sends HTTP headers and body to the client using `header()` and `fpassthru()`. |

##  Examples

###  1. Simple HTML Response
```php
use Scaleum\Http\OutboundResponse;

$html = "<h1>Welcome!</h1><p>Hello, world!</p>";
$response = new OutboundResponse(
    statusCode: 200,
    headers: ['Content-Type' => 'text/html; charset=UTF-8'],
    body: $html
);

$response->send();
// The client will receive an HTML page with status code 200
```

###  2. JSON Response for API
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
// The client will receive JSON and the Content-Length header automatically
```

###  3. Response with File for Download
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
// The browser will prompt to save the file report.pdf
```

###  4. 404 Error Response
```php
use Scaleum\Http\OutboundResponse;

$response = new OutboundResponse(
    statusCode: 404,
    headers: ['Content-Type' => 'text/plain'],
    body: 'Page not found'
);

$response->send();
// The client will receive the text "Page not found" with status code 404
```

###  5. Streaming Response (SSE)
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
// The client will receive a series of SSE events
```

[Back to Contents](../../index.md)