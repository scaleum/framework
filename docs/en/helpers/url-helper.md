[Back to Contents](../index.md)

**EN** | [UK](../../uk/helpers/url-helper.md) | [RU](../../ru/helpers/url-helper.md)
#  UrlHelper

`UrlHelper` is a utility class for working with URLs in the Scaleum Framework.

##  Purpose

- Generating the base URL
- Parsing URLs
- Redirects

##  Main Methods

| Method | Purpose |
|:------|:--------|
| `baseUrl(string $url = '')` | Getting the base URL |
| `getServerPort()` | Getting the server port |
| `getServerName()` | Getting the server name |
| `getServerProtocol()` | Determining the protocol |
| `parse(string $url = '')` | Parsing URL (simple) |
| `parseAlt(string $url = '')` | Parsing URL (alternative) |
| `redirect(string $uri = '', string $method = 'location', int $httpResponseCode = 302)` | Redirecting |

##  Usage Examples

###  Getting the Base URL

```php
$url = UrlHelper::baseUrl('images/logo.png');
```

###  Parsing URL

```php
$parts = UrlHelper::parse('https://example.com:8080/path/to/file.php?param=value#section');
```

###  Parsing URL (Alternative)

```php
$parts = UrlHelper::parseAlt('https://user:pass@sub.domain.com/path/to/file.php?foo=bar');
```

###  Redirect

```php
UrlHelper::redirect('/login');
```

[Back to Contents](../index.md)