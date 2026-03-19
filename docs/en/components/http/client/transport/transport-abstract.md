[Back to Contents](../../../../index.md)

**EN** | [UK](../../../../../uk/components/http/client/transport/transport-abstract.md) | [RU](../../../../../ru/components/http/client/transport/transport-abstract.md)
#  TransportAbstract

`TransportAbstract` is a base abstract class for implementing transport mechanisms in the Scaleum HTTP client. It extends `Hydrator`, stores common settings (timeout, number of redirects), and provides utility helpers.

##  Purpose

- Managing the number of redirects during redirection (`redirectsCount`).
- Managing connection and response timeout (`timeout`).
- Providing a utility method `flatten()` to convert headers into a flat array of strings.
- Injecting properties via `Hydrator` for flexible configuration in subclasses.

##  Properties

| Property             | Type    | Description                                              |
|:---------------------|:--------|:---------------------------------------------------------|
| `protected int $redirectsCount` | `int`   | Maximum number of consecutive redirects (default 5).    |
| `protected int $timeout`        | `int`   | Timeout in seconds for connection and response (default 5). |

##  Methods

###  static flatten
```php
protected static function flatten(array $array): array
```
Converts an associative array `['Name' => 'value']` or `['Name' => ['v1','v2']]` into a flat array of strings:
```
[ 'Name: value', 'Name: v1', 'Name: v2', ... ]
```

###  getRedirectsCount / setRedirectsCount
```php
public function getRedirectsCount(): int;
public function setRedirectsCount(int $redirectsCount): static;
```
- `getRedirectsCount()` — returns the current value of `redirectsCount`.
- `setRedirectsCount($n)` — sets the allowed number of redirects and returns `$this`.

###  getTimeout / setTimeout
```php
public function getTimeout(): int;
public function setTimeout(int $timeout): static;
```
- `getTimeout()` — returns the current value of `timeout`.
- `setTimeout($seconds)` — sets the timeout in seconds and returns `$this`.

##  Usage Examples

###  1. Configuring redirects and timeout in a subclass
```php
$transport = new CurlTransport()
    ->setRedirectsCount(3)  // allow no more than 3 redirects
    ->setTimeout(10);       // timeout 10 seconds
```

###  2. Using flatten() when manual header formation is needed
```php
$headersArray = [
    'Accept' => ['text/html', 'application/json'],
    'X-Custom' => 'Value'
];
$flat = TransportAbstract::flatten($headersArray);
// ['Accept: text/html', 'Accept: application/json', 'X-Custom: Value']
```

###  3. Flexible configuration via Hydrator
```php
// For example, in tests you can inject parameters via hydrate()
$transport = new CurlTransport();
$transport->hydrate([
    'timeout' => 2,
    'redirectsCount' => 0
]);
// Now the connection will have a 2s timeout and will not follow redirects
```

[Back to Contents](../../../../index.md)