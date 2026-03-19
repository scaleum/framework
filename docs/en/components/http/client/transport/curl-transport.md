[Back to Contents](../../../../index.md)

**EN** | [UK](../../../../../uk/components/http/client/transport/curl-transport.md) | [RU](../../../../../ru/components/http/client/transport/curl-transport.md)
#  CurlTransport

`CurlTransport` — an implementation of `TransportInterface` based on cURL, responsible for sending `OutboundRequest` and receiving `InboundResponse`.

##  Purpose

- Initialization and configuration of a cURL session for HTTP requests (GET, POST, PUT, DELETE, HEAD, etc.).
- Support for handling headers, request and response body, status, redirects.
- Support for basic and Bearer authorization, NTLM, and Digest.
- Support for asynchronous requests (without waiting for a response).

##  Properties

| Property           | Type          | Description                                                  |
|:------------------|:-------------|:-------------------------------------------------------------|
| `protected ?string $authType` | `string\|null` | Authorization type: BASIC, DIGEST, BEARER, NTLM, ANY, ANYSAFE. |
| `protected ?string $username` | `string\|null` | Username for basic or NTLM authorization.                    |
| `protected ?string $password` | `string\|null` | Password for basic or NTLM authorization.                    |
| `protected ?string $token`    | `string\|null` | Token for Bearer authorization.                              |
| `protected ?string $domain`   | `string\|null` | Domain for NTLM authorization.                               |

##  Methods

###  send
```php
public function send(OutboundRequest $request): InboundResponse
```
- Checks cURL support via `isSupported()`.
- Reads URL, method, headers, and body from `$request`.
- Parses URL and configures cURL options (`CURLOPT_*`) depending on method and content.
- Sets headers via `CURLOPT_HTTPHEADER`.
- Handles authorization:
  - BASIC, DIGEST, NTLM: `CURLOPT_USERPWD`
  - BEARER: `CURLOPT_XOAUTH2_BEARER`
- Executes the request (`curl_exec`) and handles cURL errors.
- Extracts HTTP code, response headers, and body, forms `InboundResponse`.
- Handles redirects, recursively calling `send()` if necessary.

###  isSupported
```php
public function isSupported(): bool
```
- Checks for the presence of `curl_init` and `curl_exec` functions.

###  Authorization getters/setters
```php
public function setAuthType(string $authType): static
public function setUsername(string $username): static
public function setPassword(string $password): static
public function setToken(string $token): static
public function setDomain(string $domain): static
```
- Set parameters for various authorization schemes.

##  Usage examples

###  1. Simple GET request
```php
$transport = new CurlTransport();
$request   = new OutboundRequest('GET', new Uri('https://api.example.com/data'));
$response  = $transport->send($request);
$data      = $response->getParsedBody();
```

###  2. POST with JSON data
```php
$transport = new CurlTransport();
$request   = new OutboundRequest(
    'POST',
    new Uri('https://api.example.com/users'),
    ['Content-Type' => 'application/json'],
    json_encode(['name' => 'Alice', 'age' => 25])
);
$response  = $transport->send($request);
```

###  3. Basic authorization
```php
$transport = (new CurlTransport())
    ->setAuthType('BASIC')
    ->setUsername('user')
    ->setPassword('secret');

$request  = new OutboundRequest('GET', new Uri('https://secure.example.com/profile'));
$response = $transport->send($request);
```

###  4. Bearer authorization
```php
$transport = (new CurlTransport())
    ->setAuthType('BEARER')
    ->setToken('your-jwt-token');

$request  = new OutboundRequest('DELETE', new Uri('https://api.example.com/resource/42'));
$response = $transport->send($request);
```

###  5. Handling redirects
```php
$transport = (new CurlTransport())
    ->setRedirectsCount(3); // allow up to 3 redirects

$response = $transport->send(
    new OutboundRequest('GET', new Uri('http://short.url/xyz'))
);
// will automatically follow Location up to 3 times
```

[Back to Contents](../../../../index.md)