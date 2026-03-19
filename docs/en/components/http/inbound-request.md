[Back to Table of Contents](../../index.md)

**EN** | [UK](../../../uk/components/http/inbound-request.md) | [RU](../../../ru/components/http/inbound-request.md)
#  InboundRequest

`InboundRequest` is a class for incoming HTTP requests in the Scaleum framework,
extending the base `Message` and implementing the PSR-7 `ServerRequestInterface`.

##  Purpose

- Receiving and storing request data: method, URI, headers, body, parameters, files, cookies, and server variables.
- Parsing the request body depending on `Content-Type` (JSON, form-data, urlencoded, XML, plain-text).
- Sanitizing global data (`$_GET`, `$_POST`, `$_COOKIE`, `$_FILES`).
- Normalizing the files array to support single and multiple uploads.
- Working with request attributes according to PSR-7 (getAttribute, withAttribute, and withoutAttribute).

##  Constructor

```php
public function __construct(
    string $method,
    UriInterface $uri,
    array $serverParams = [],
    array $headers = [],
    ?StreamInterface $body = null,
    array $queryParams = [],
    ?array $parsedBody = null,
    array $cookieParams = [],
    array $files = [],
    string $protocol = '1.1'
)
```

- `$method` — HTTP method (GET, POST, PUT, etc.).
- `$uri` — an instance of `UriInterface`.
- `$serverParams`, `$headers`, `$cookieParams`, `$files`, `$queryParams` — corresponding PSR-7 collections.
- If `$parsedBody === null`, `parseBody()` is called.

##  Main Methods

| Method                                      | Description                                                                            |
|:-------------------------------------------|:--------------------------------------------------------------------------------------|
| `parseBody(?StreamInterface $body, string $contentType, string $method): mixed` | Parses the request body by type: JSON, form-data, urlencoded, XML, plain-text.         |
| `normalizeFiles(array $files): array`      | Normalizes `$_FILES` to a uniform format for single and multiple uploads.              |
| `sanitize(): void`                         | Sanitizes global arrays `$_GET`, `$_POST`, `$_COOKIE`, cleaning keys and data.         |
| `fromGlobals(): self`                      | Creates an object from global variables with automatic sanitization.                   |
| `getInputParams(): array`                  | Returns GET/HEAD/OPTIONS parameters or parsed `parsedBody` for POST and others.        |
| `getInputParam(string $param, mixed $default = null): mixed` | Convenient access to a single request parameter.                      |
| `getAttribute(string $name, $default = null): mixed`   | Reads a PSR-7 request attribute.                                         |
| `withAttribute(string $name, $value): static`         | Returns a clone with the added attribute.                              |
| `withoutAttribute(string $name): static`              | Removes an attribute and returns a clone.                               |
| `withMethod(string $method): static`                   | Returns a clone with the changed HTTP method.                           |
| `withUri(UriInterface $uri, bool $preserveHost = false): static` | Updates the URI and Host header if necessary.                      |

##  Usage Example

```php
use Scaleum\Stdlib\Helpers\HttpHelper;

$request = InboundRequest::fromGlobals();

// Get the 'id' parameter from the request or null if absent
$id = $request->getInputParam('id', null);

// Add the 'userId' attribute to the request
$requestWithUser = $request->withAttribute('userId', $user->getId());

// Check method and content
if ($request->getMethod() === HttpHelper::METHOD_POST) {
    $data = $request->getParsedBody();
    // ... process POST data ...
}
```

[Back to Table of Contents](../../index.md)

