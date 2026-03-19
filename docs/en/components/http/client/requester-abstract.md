[Back to Contents](../../../index.md)

**EN** | [UK](../../../../uk/components/http/client/requester-abstract.md) | [RU](../../../../ru/components/http/client/requester-abstract.md)
#  RequesterAbstract

`RequesterAbstract` is a base abstract class for sending outgoing HTTP requests via various transport mechanisms (e.g., cURL), extending `Hydrator` and providing a common interface for connection configuration.

##  Purpose

- Storing and managing HTTP client settings: protocol (`http`, `https`), host, port.
- Lazy initialization of the transport via the `getDefaultClient()` method.
- Building the full URL for the request via `getRequestUrl()`.
- Abstract method `send()` for implementing the logic of sending the request and receiving the response.

##  Properties

| Property                       | Type                | Description                        |
| ------------------------------ | ------------------ | ------------------------------- |
| `protected string $protocol`   | `string`           | Protocol (default `http`)         |
| `protected string $host`       | `string`           | Host (default `localhost`)        |
| `protected int $port`          | `int`              | Port (default `80`)               |
| `protected TransportInterface $transport` | `TransportInterface` or `null`  | Transport client; if `null`, `getDefaultClient()` is used |

##  Methods

###  getDefaultClient

```php
protected function getDefaultClient(): TransportInterface
```

Returns the default implementation of `TransportInterface` (e.g., `CurlTransport`). Allows overriding the transport in a subclass.

###  getProtocol / setProtocol

```php
public function getProtocol(): string
public function setProtocol(string $value): self
```

- `getProtocol()` — returns a non-empty protocol (`http` by default).
- `setProtocol()` — normalizes the string (lowercase, removes special characters) and sets `protocol`.

###  getHost / setHost

```php
public function getHost(): string
public function setHost(mixed $value): self
```

- `getHost()` — returns a non-empty host (`localhost` by default).
- `setHost()` — casts the value to string and sets `host`.

###  getPort / setPort

```php
public function getPort(): int
public function setPort(mixed $value): self
```

- `getPort()` — returns a valid port number (`80` by default).
- `setPort()` — casts the value to `int` and sets `port`.

###  getRequestUrl

```php
public function getRequestUrl(string $url = ''): string
```

- Builds the base URL: `<protocol>://<host>[:port]`.
- Adds `/$url` if the parameter is not empty.
- Example: `https://api.example.com:8080/v1/users`

###  send

```php
abstract public function send(OutboundRequest $request): InboundResponse
```

- Abstract method for sending an `OutboundRequest` and receiving an `InboundResponse`.
- Implemented in concrete subclasses using `$this->transport`.

##  Examples

###  1. cURL subclass implementation

```php
use Scaleum\Http\Client\RequesterAbstract;
use Scaleum\Http\Client\Transport\TransportInterface;
use Scaleum\Http\OutboundRequest;
use Scaleum\Http\InboundResponse;

class Requester extends RequesterAbstract {
    protected function getDefaultClient(): TransportInterface {
        return new CurlTransport(); // custom cURL class
    }

    public function send(OutboundRequest $request): InboundResponse {
        $client = $this->transport ?? $this->getDefaultClient();
        return $client->sendRequest($request);
    }
}
```

###  2. Configuration and sending a request

```php
$requester = new Requester();
$requester
    ->setProtocol('https')
    ->setHost('api.example.com')
    ->setPort(443);

// Will form URL https://api.example.com:443/v1/items?limit=10
$url = $requester->getRequestUrl('v1/items?limit=10');

$request = new OutboundRequest(
    'GET',
    new Uri('/v1/items?limit=10')
);

$response = $requester->send($request);
$data = $response->getParsedBody();
```

###  3. Transport substitution

```php
// You can inject MockTransport for testing
$mock = new class implements TransportInterface {
    public function send($req) {
        return new InboundResponse(200, ['Content-Type'=>'application/json'], json_encode(['ok'=>true]));
    }
};

$requester = (new Requester())
    ->init(['transport' => $mock]); // Hydrator method

$response = $requester->send(new OutboundRequest('POST', new Uri('/test'), [], ['foo'=>'bar']));
echo $response->getParsedBody()['ok']; // true
```

[Back to Contents](../../../index.md)

