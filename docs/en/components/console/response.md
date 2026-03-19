[Back](./application.md) | [Return to contents](../../index.md)

**EN** | [UK](../../../uk/components/console/response.md) | [RU](../../../ru/components/console/response.md)
#  Response

`Response` — implementation of the `ConsoleResponseInterface` for forming and sending output in console mode (CLI) in the Scaleum framework.

##  Purpose

- Store the response content (`content`) and process exit code (`statusCode`).
- Provide output of the message to the appropriate stream (`STDOUT` or `STDERR`).
- Implement the `send()` method from `ResponderInterface`.

##  Properties

| Property              | Type      | Description                                                          |
|:----------------------|:---------|:---------------------------------------------------------------------|
| `private ?string $content`    | `string\|null` | Text payload of the response.                                        |
| `private int $statusCode`     | `int`        | Exit code (default is `STATUS_SUCCESS`).                            |

##  Constructor

No constructor: properties are initialized by default. Use setters to set values.

##  Methods

###  setContent()
```php
public function setContent(string $content): void
```
- Sets the text that will be output when `send()` is called.

###  getContent()
```php
public function getContent(): ?string
```
- Returns the previously set text or `null` if content is not set.

###  setStatusCode()
```php
public function setStatusCode(int $statusCode): void
```
- Sets the process exit code.
- Validates that `$statusCode` is in the array `ConsoleResponseInterface::STATUSES`.
- Throws `InvalidArgumentException` on invalid value.

###  getStatusCode()
```php
public function getStatusCode(): int
```
- Returns the current exit code.

###  send
```php
public function send(): void
```
- If `$content` is not empty, writes it to the stream:
  - To `STDERR` if the exit code differs from `STATUS_SUCCESS`.
  - To `STDOUT` if the code equals `STATUS_SUCCESS`.
- Adds a newline character.

##  Usage example

```php
use Scaleum\Console\Response;

$response = new Response();
$response->setContent("Operation completed successfully");
$response->setStatusCode(Response::STATUS_SUCCESS);
$response->send();
exit($response->getStatusCode());
```

```php
// In case of error:
$response = new Response();
$response->setContent("Error: Invalid parameters");
$response->setStatusCode(Response::STATUS_INVALID_PARAMS);
$response->send();
exit($response->getStatusCode());
```

[Back](./application.md) | [Return to contents](../../index.md)

