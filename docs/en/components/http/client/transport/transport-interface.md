[Back to Contents](../../../../index.md)

**EN** | [UK](../../../../../uk/components/http/client/transport/transport-interface.md) | [RU](../../../../../ru/components/http/client/transport/transport-interface.md)
#  TransportInterface

`TransportInterface` — interface for HTTP client transport mechanisms in the Scaleum framework. Defines the contract for sending `OutboundRequest` and checking transport support.

##  Purpose

- Abstract the low-level details of sending HTTP requests.
- Provide a unified interface for various transports: cURL, sockets, HTTP streams, etc.
- Allow easy substitution of transport in tests and extend functionality without changing client code.

##  Methods

| Method                                                      | Description                                                                                                  |
|:-----------------------------------------------------------|:-------------------------------------------------------------------------------------------------------------|
| `send(OutboundRequest $request): InboundResponse`          | Sends an outbound HTTP request and returns an `InboundResponse` object with the execution result.             |
| `isSupported(): bool`                                       | Checks if this transport implementation is available (e.g., presence of cURL extension or socket functions). |

##  Implementation Example

###  1. Using cURL
```php
use Scaleum\Http\Client\Transport\TransportInterface;
use Scaleum\Http\Client\Transport\CurlTransport;

class MyCurlClient implements TransportInterface {
    private CurlTransport $transport;

    public function __construct() {
        $this->transport = new CurlTransport();
    }

    public function send(OutboundRequest $request): InboundResponse {
        if (! $this->transport->isSupported()) {
            throw new RuntimeException('cURL unavailable');
        }
        return $this->transport->send($request);
    }

    public function isSupported(): bool {
        return $this->transport->isSupported();
    }
}
```

###  2. Simple "stub" for tests
```php
use Scaleum\Http\Client\Transport\TransportInterface;
use Scaleum\Http\OutboundRequest;
use Scaleum\Http\InboundResponse;

class MockTransport implements TransportInterface {
    private InboundResponse $response;

    public function __construct(InboundResponse $response) {
        $this->response = $response;
    }

    public function send(OutboundRequest $request): InboundResponse {
        // You can check request parameters and return a pre-constructed response
        return $this->response;
    }

    public function isSupported(): bool {
        return true;
    }
}

// In a test:
$mockResponse = new InboundResponse(200, ['Content-Type'=>'application/json'], json_encode(['ok'=>true]));
$transport    = new MockTransport($mockResponse);
$result       = $transport->send(new OutboundRequest('GET', new Uri('/test')));
assert($result->getParsedBody()['ok'] === true);
```

###  3. Checking transport support
```php
foreach ([$curlTransport, $socketTransport] as $t) {
    if ($t->isSupported()) {
        // select the first available transport
        $response = $t->send($request);
        break;
    }
}
```

[Back to Contents](../../../../index.md)