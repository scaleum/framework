[Back to Contents](../index.md)

**EN** | [UK](../../uk/helpers/http-helper.md) | [RU](../../ru/helpers/http-helper.md)
#  HttpHelper

`HttpHelper` is a utility class for working with HTTP headers, statuses, IP addresses, and User-Agent.

##  Purpose

- Managing HTTP statuses and headers
- Validating HTTP methods
- Determining the client's IP address and User-Agent

##  Main constants

| Constant | Value |
|:------------|:------------|
| `METHOD_GET` | GET |
| `METHOD_POST` | POST |
| `METHOD_PUT` | PUT |
| `METHOD_PATCH` | PATCH |
| `METHOD_DELETE` | DELETE |
| `METHOD_OPTIONS` | OPTIONS |
| `METHOD_HEAD` | HEAD |

##  Main methods

| Method | Purpose |
|:------|:-----------|
| `setHeader` | Setting an HTTP header |
| `setStatusHeader` | Setting an HTTP status |
| `getStatusMessage` | Getting the text for an HTTP status |
| `isStatusCode` | Checking the validity of an HTTP status |
| `isMethod` | Checking an HTTP method |
| `getUserIP` | Getting the user's IP address |
| `isIpAddress` | Validating an IP address |
| `getUserAgent` | Getting the User-Agent |

##  Usage examples

###  Setting an HTTP header

```php
HttpHelper::setHeader('Content-Type', 'application/json');
```

###  Setting an HTTP status

```php
HttpHelper::setStatusHeader(404);
```

###  Getting the text for an HTTP status

```php
$statusMessage = HttpHelper::getStatusMessage(200); // "OK"
```

###  Checking an HTTP status

```php
if (HttpHelper::isStatusCode(404)) {
    // This is a valid status
}
```

###  Checking an HTTP method

```php
if (HttpHelper::isMethod('POST')) {
    // POST is a valid HTTP method
}
```

###  Getting the user's IP address

```php
$userIp = HttpHelper::getUserIP();
```

###  Validating an IP address

```php
if (HttpHelper::isIpAddress($userIp)) {
    // The address is valid
}
```

###  Getting the User-Agent

```php
$userAgent = HttpHelper::getUserAgent();
```

[Back to Contents](../index.md)