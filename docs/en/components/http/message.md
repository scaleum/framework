[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/http/message.md) | [RU](../../../ru/components/http/message.md)
#  Message

`Message` is the base PSR-7 class for HTTP messages, implementing `MessageInterface`. It serves as the foundation for requests and responses, managing headers, body, and protocol version.

##  Purpose

- Storing and modifying HTTP headers.
- Storing and replacing the body (`StreamInterface`).
- Managing the HTTP protocol version.

##  Constructor

```php
public function __construct(
    array $headers = [],
    ?StreamInterface $body = null,
    string $protocol = '1.1'
)
```

- `$headers` — associative array of headers `['Name' => ['value1', 'value2'], ...]`.
- `$body` — a `StreamInterface` object; by default, an empty stream is created.
- `$protocol` — HTTP protocol version (e.g., `"1.1"`).

##  Methods

| Method                                           | Description                                                               |
|:-------------------------------------------------|:-------------------------------------------------------------------------|
| `getProtocolVersion(): string`                   | Returns the protocol version string.                                     |
| `withProtocolVersion(string $version): static`   | Returns a clone with the specified protocol version.                     |
| `getHeaders(): array`                            | Returns all headers as an associative array.                             |
| `hasHeader(string $name): bool`                  | Checks for the presence of a header (case-insensitive).                  |
| `getHeader(string $name): array`                 | Returns an array of header values or an empty array.                     |
| `getHeaderLine(string $name): string`            | Returns header values as a single string separated by `", "`.            |
| `withHeader(string $name, string|array $value): static`     | Clones the message, setting the header `Name: value`.            |
| `withAddedHeader(string $name, string|array $value): static`| Clones and adds a value to an existing header.                   |
| `withoutHeader(string $name): static`            | Clones the message without the specified header.                         |
| `getBody(): StreamInterface`                     | Returns the current body stream.                                         |
| `withBody(StreamInterface $body): static`        | Clones the message with a new body.                                      |

##  Examples

###  1. Creating a basic message
```php
use Scaleum\Http\Message;
use Scaleum\Http\Stream;

// Empty message with default body
$message = new Message();
// Protocol version
echo $message->getProtocolVersion(); // '1.1'
```

###  2. Adding and retrieving headers
```php
$message = (new Message())
    ->withHeader('Content-Type', 'application/json')
    ->withAddedHeader('X-Custom', ['A', 'B']);

// Check
if ($message->hasHeader('Content-Type')) {
    $line = $message->getHeaderLine('Content-Type'); // 'application/json'
    $all  = $message->getHeader('X-Custom');         // ['A', 'B']
}
```

###  3. Replacing the message body
```php
$bodyStream = new Stream(fopen('php://temp', 'r+'));
$bodyStream->write(json_encode(['foo' => 'bar']));
$bodyStream->rewind();

$messageWithBody = $message->withBody($bodyStream);
$data = json_decode((string)$messageWithBody->getBody(), true); // ['foo' => 'bar']
```

###  4. Changing the protocol version
```php
$newMessage = $message->withProtocolVersion('2.0');
echo $newMessage->getProtocolVersion(); // '2.0'
```

[Back to Contents](../../index.md)