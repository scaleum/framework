[Back](../application.md) | [Return to contents](../../../index.md)

**EN** | [UK](../../../../uk/components/console/contracts/console-response-interface.md) | [RU](../../../../ru/components/console/contracts/console-response-interface.md)
#  ConsoleResponseInterface

`ConsoleResponseInterface` — an interface for console mode (CLI) response, extending `ResponderInterface` and defining the return code and output content.

##  Purpose

- Provide a standard contract for forming responses in CLI applications.
- Ensure the presence of methods for setting and getting content and exit code.
- Define a set of predefined status codes.

##  Relation to ядро

The interface is used by `CommandHandler` and `CommandDispatcher` to pass the result of command execution and determine the application exit code.

##  Status constants

| Constant                   | Value | Description                                         |
|:----------------------------|:---------|:---------------------------------------------------|
| `STATUS_SUCCESS`            | `0`      | Successful command execution                        |
| `STATUS_NOT_FOUND`          | `1`      | Command not found                                  |
| `STATUS_INVALID_PARAMS`     | `2`      | Invalid or missing command parameters              |

##  Interface methods

```php
interface ConsoleResponseInterface extends ResponderInterface
{
    public function setContent(string $content): void;
    public function getContent(): ?string;
    public function getStatusCode(): int;
    public function setStatusCode(int $statusCode): void;
}
```

| Method                                    | Description                                                  |
|:-----------------------------------------|:-------------------------------------------------------------|
| `setContent(string $content): void`      | Sets the textual content of the response.                    |
| `getContent(): ?string`                  | Returns the previously set content or `null`.                |
| `getStatusCode(): int`                   | Returns the command exit code (one of the `STATUSES`).       |
| `setStatusCode(int $statusCode): void`   | Sets the exit code (must be in `STATUSES`).                  |

##  Implementation example

```php
use Scaleum\Console\Contracts\ConsoleResponseInterface;

class Response implements ConsoleResponseInterface {
    protected ?string $content;
    protected int $statusCode = self::STATUS_SUCCESS;

    public function setContent(string $content): void {
        $this->content = $content;
    }

    public function getContent(): ?string {
        return $this->content;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): void {
        if (!in_array($statusCode, self::STATUSES, true)) {
            throw new InvalidArgumentException('Invalid status code');
        }
        $this->statusCode = $statusCode;
    }

    public function send(): void {
        // Output content and exit with code
        if ($this->content !== null) {
            fwrite(STDOUT, $this->content . PHP_EOL);
        }
        exit($this->statusCode);
    }
}
```

##  Usage example

```php
$response = new Response();
$response->setContent("Hello, CLI!");
$response->setStatusCode(Response::STATUS_SUCCESS);
$response->send(); // outputs "Hello, CLI!" and terminates the script with code 0
```

[Back](../application.md) | [Return to contents](../../../index.md)

