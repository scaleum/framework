[Back to table of contents](../../../../index.md)

**EN** | [UK](../../../../../uk/components/http/client/transport/socket-transport.md) | [RU](../../../../../ru/components/http/client/transport/socket-transport.md)
#  SocketTransport

`SocketTransport` is an implementation of `TransportInterface` based on sockets (fsockopen), responsible for sending `OutboundRequest` and receiving `InboundResponse` without using cURL.

##  Purpose

- Sending arbitrary HTTP requests (GET, POST, PUT, DELETE, HEAD, etc.) at the socket level.
- Manually forming the request line and headers.
- Supporting Basic and Bearer (JWT/OAuth2) authorization via the `Authorization` header.
- Handling server responses: reading the status line, headers, body, and redirects.
- Asynchronous mode (`isAsync()`) — sending without waiting for a response.

##  Properties

| Property           | Type            | Description                                                               |
|:------------------|:---------------|:-------------------------------------------------------------------------|
| `protected ?string $authType` | `string\|null`   | Authorization type: `BASIC`, `BEARER`, `OAUTH2`, `JWT`.                   |
| `protected ?string $username` | `string\|null`   | Username for Basic authorization.                                        |
| `protected ?string $password` | `string\|null`   | Password for Basic authorization.                                        |
| `protected ?string $token`    | `string\|null`   | Token for Bearer authorization.                                          |

##  Methods

###  isSupported()
```php
public function isSupported(): bool
```
Checks for the presence of the `fsockopen` function. Returns `false` if sockets are not supported.

###  send()
```php
public function send(OutboundRequest $request): InboundResponse
```
1. **Support check**:
```php
if (! $this->isSupported()) {
    throw new ERuntimeError('Socket transport is not supported');
}
```

2. **Reading request parameters**:
```php
$url        = (string)$request->getUri();
$method     = strtoupper($request->getMethod());
$headers    = new HeadersManager($request->getHeaders());
$body       = $request->getBody()->isSeekable()
    ? $request->getBody()->rewind() && $request->getBody()->getContents()
    : $request->getBody()->getContents();
```

3. **Parsing URL** via `UrlHelper::parse` and selecting port (80 or 443 with SSL).
4. **Opening socket**:
```php
$socket = fsockopen($host, $port, $errno, $errstr, $this->getTimeout());
if (! $socket) {
    throw new EHttpException(501, $errstr);
}
```

5. **Setting headers** (Content-Type, Content-Length, Host, Connection) and authorization:
```php
// Basic
echo $headers->setHeader('Authorization', 'Basic '.base64_encode("$user:$pass"));
// Bearer
echo $headers->setHeader('Authorization', "Bearer $token");
```

6. **Forming the request**:
```php
$requestLine = sprintf("%s %s HTTP/%.1f\r\n",
    $method,
    $requestPath,
    $request->getProtocolVersion()
);
$raw = $requestLine . implode("\r\n", $headers->getAsStrings()) . "\r\n\r\n" . $body;
fwrite($socket, $raw);
```

7. **Asynchronous mode**:
```php
if ($request->isAsync()) {
    fclose($socket);
    return new InboundResponse();
}
```

8. **Reading the response**:
   - Status line and status code via `fgets` and `preg_match`.
   - Loop reading headers until an empty line.
   - Reading body via `stream_get_contents`.
9. **Creating `InboundResponse`** with status code, headers, and body in `Stream`.
10. **Handling redirects**: if a `Location` header is present, recursively call `send()`.

##  Usage examples

###  1. GET request
```php
$transport = new SocketTransport();
$request   = new OutboundRequest('GET', new Uri('/api/status')); // base host and port are taken from Requester settings
$response  = $transport->send($request);
echo $response->getStatusCode();     // e.g., 200
echo $response->getParsedBody();     // response body
```

###  2. POST with JSON
```php
$transport = new SocketTransport();
$data      = ['name' => 'Bob', 'age' => 28];
$request   = new OutboundRequest(
    'POST',
    new Uri('/users'),
    ['Content-Type' => 'application/json'],
    json_encode($data)
);
$response  = $transport->send($request);
```

###  3. Basic authorization
```php
$transport = (new SocketTransport())
    ->setAuthType('BASIC')
    ->setUsername('user')
    ->setPassword('secret');

$request  = new OutboundRequest('GET', new Uri('/private'));
$response = $transport->send($request);
```

###  4. Bearer authorization
```php
$transport = (new SocketTransport())
    ->setAuthType('BEARER')
    ->setToken('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoxLCJleHBpcnkiOjE2MjIwNzYwMzA');

$request  = new OutboundRequest('DELETE', new Uri('/resource/123'));
$response = $transport->send($request);
```

###  5. Asynchronous request
```php
$request = (new OutboundRequest('GET', new Uri('/long-poll')))
    ->setAsync(true);

$transport = new SocketTransport();
// sending without waiting for a response
$transport->send($request);
```

###  6. Redirect Handling
```php
// By default, redirect limit from TransportAbstract
$response = (new SocketTransport())->send(
    new OutboundRequest('GET', new Uri('http://short.url/xyz'))
);
```

[Back to contents](../../../../index.md)

