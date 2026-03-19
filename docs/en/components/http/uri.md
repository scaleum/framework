[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/http/uri.md) | [RU](../../../ru/components/http/uri.md)
#  Uri

`Uri` is a class for working with URIs within PSR-7, implementing `UriInterface`. It allows parsing, modifying, and building a URI string, as well as automatically substituting the current request if no URI is provided.

##  Purpose

- Parsing a URI string into components: scheme, user information, host, port, path, query, fragment.
- Building changes through PSR-7 methods like `withScheme`, `withHost`, etc., returning a clone of the object.
- Obtaining the full URI string via the `__toString()` method.
- Automatically determining the current URI if no argument is passed to the constructor.

##  Constructor

```php
public function __construct(?string $uri = null)
```

- `$uri` — URI string; if `null`, it will be extracted from global variables (`$_SERVER['REQUEST_URI']`, `PATH_INFO`, `QUERY_STRING`, `$_GET`).
- Cleans invisible characters (`StringHelper::cleanInvisibleChars`).
- Parses the URI using the `parse_url` function.
- Fills properties:
  - `$scheme`   — scheme (e.g., `http`, `https`).
  - `$userInfo` — `user[:pass]`.
  - `$host`     — domain name.
  - `$port`     — port or `null`.
  - `$path`     — path after the host.
  - `$query`    — query string without `?`.
  - `$fragment` — fragment without `#`.

##  PSR-7 Properties and Methods

| Method                             | Description                                                 |
|:----------------------------------|:------------------------------------------------------------|
| `getScheme(): string`             | Returns the scheme.                                         |
| `withScheme(string $scheme): static` | Clones and sets the scheme.                              |
| `getAuthority(): string`          | Returns `[userInfo@]host[:port]`.                           |
| `getUserInfo(): string`           | Returns the `userInfo`.                                     |
| `withUserInfo(string $user, ?string $password = null): static` | Sets `user:password`.               |
| `getHost(): string`               | Returns the host.                                           |
| `withHost(string $host): static`  | Clones and sets the host.                                  |
| `getPort(): ?int`                 | Returns the port or `null`.                                |
| `withPort(?int $port): static`    | Clones and sets the port.                                  |
| `getPath(): string`               | Returns the path.                                          |
| `withPath(string $path): static`  | Clones and sets the path.                                  |
| `getQuery(): string`              | Returns the query string.                                  |
| `withQuery(string $query): static`| Clones and sets the query string.                          |
| `getFragment(): string`           | Returns the fragment.                                      |
| `withFragment(string $fragment): static` | Clones and sets the fragment.                        |
| `__toString(): string`            | Composes the full URI string from all parts.              |

##  Usage Example

###  1. Parsing a Static URI
```php
use Scaleum\Http\Uri;

$uri = new Uri('https://user:pass@example.com:8080/path/to/resource?foo=1&bar=2#section');
echo $uri->getScheme();      // 'https'
echo $uri->getAuthority();   // 'user:pass@example.com:8080'
echo $uri->getHost();        // 'example.com'
echo $uri->getPort();        // 8080
echo $uri->getPath();        // '/path/to/resource'
echo $uri->getQuery();       // 'foo=1&bar=2'
echo $uri->getFragment();    // 'section'
```

###  2. Modifying URI via PSR-7 Methods
```php
$baseUri = new Uri('http://example.com/articles?page=1');

// Switch to HTTPS and change the page
$secureUri = $baseUri
    ->withScheme('https')
    ->withQuery('page=2')
    ->withFragment('comments');

echo (string) $secureUri;  // 'https://example.com/articles?page=2#comments'
```

###  3. Getting the Current Request in a Web Application
```php
// suppose the client requested '/blog/post/42?preview=true'
$currentUri = new Uri();
// equivalent to: new Uri($_SERVER['REQUEST_URI'])
echo (string) $currentUri; // '/blog/post/42?preview=true'
```

###  4. Working with User Information
```php
$uri = (new Uri('http://example.com'))->withUserInfo('admin', 'secret');
echo $uri->getAuthority(); // 'admin:secret@example.com'
```

[Back to Contents](../../index.md)