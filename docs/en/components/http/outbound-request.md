[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/http/outbound-request.md) | [RU](../../../ru/components/http/outbound-request.md)
#  OutboundRequest

`OutboundRequest` is a class for forming an outgoing HTTP request to an external server,
extending `Message` and implementing the PSR-7 `RequestInterface`.

##  Purpose

- Storing the method, URI, headers, and body of the outgoing request.
- Supporting synchronous and asynchronous requests via the `$async` flag.
- Convenient handling of the request body through the `StreamTrait`.
- PSR-7 compliance: methods `withMethod`, `withUri`, `withRequestTarget` return a clone.

##  Constructor

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

- `$method` — HTTP method (`GET`, `POST`, `PUT`, etc.).
- `$uri` — an instance of `UriInterface`.
- `$headers` — associative array of headers.
- `$body` — string, resource, or `StreamInterface`; prepared via `prepareHeadersAndStream()`.
- `$protocol` — HTTP protocol version.
- `$async` — `true` for asynchronous request.

During construction, headers and body are processed by the `MessagePayloadTrait`:
```php
$payload = $this->getMessagePayload($headers, $body);
```  
After which `parent::__construct($this->headers, $this->body, $protocol)` is called.

##  Methods

| Method                              | Description                                                  |
|:-----------------------------------|:-------------------------------------------------------------|
| `getRequestTarget(): string`       | Returns the request target string (`$requestTarget` or URI). |
| `withRequestTarget($target): static` | Clones the request and sets a new `requestTarget`.           |
| `getMethod(): string`              | Returns the HTTP method.                                      |
| `withMethod($method): static`      | Returns a clone with the specified method in uppercase.      |
| `getUri(): UriInterface`           | Returns the current URI.                                     |
| `withUri(UriInterface $uri, bool $preserveHost=false): static` | Clones the request with a new URI, updating the `Host` header. |
| `isAsync(): bool`                  | Returns the asynchronous flag.                               |
| `setAsync(bool $async): void`      | Sets the asynchronous flag for the current object.           |

##  Examples

###  1. Simple GET request
```php
use Scaleum\Http\OutboundRequest;
use Scaleum\Http\Stream;
use Scaleum\Stdlib\Helpers\UriHelper;

$uri     = UriHelper::create('https://api.example.com/items');
$request = new OutboundRequest('GET', $uri, ['Accept' => 'application/json']);

// Sending the request via client...
```

###  2. POST with JSON body
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

// The body will be placed into a stream, Content-Length header calculated automatically
```

###  3. Changing method and URI via PSR-7
```php
$request2 = $request
    ->withMethod('PUT')
    ->withRequestTarget('/users/42')
    ->withUri(UriHelper::create('https://api.example.com/users/42'));
```

###  4. Asynchronous request
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
    // Client will perform the request without waiting for a response
}
```

[Back to Contents](../../index.md)

