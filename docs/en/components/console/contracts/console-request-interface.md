[Back](../application.md) | [Return to contents](../../../index.md)

**EN** | [UK](../../../../uk/components/console/contracts/console-request-interface.md) | [RU](../../../../ru/components/console/contracts/console-request-interface.md)
#  ConsoleRequestInterface

`ConsoleRequestInterface` — an interface for the console mode (CLI) request object in the Scaleum framework. Defines a method to access raw command line arguments.

##  Purpose

- Represent the arguments passed to the script.
- Provide the ability to retrieve the list of arguments for subsequent command routing.

##  Interface method

```php
interface ConsoleRequestInterface
{
    /**
     * Returns an array of all CLI arguments without modification.
     *
     * @return array Raw arguments.
     */
    public function getRawArguments(): array;
}
```

- `getRawArguments(): array` — returns the full array of arguments, including the script name and all parameters.

##  Implementation example

```php
use Scaleum\Console\ConsoleRequestInterface;

class ConsoleRequest implements ConsoleRequestInterface {
    protected array $args;

    public function __construct(array $argv) {
        // Store the original array of arguments
        $this->args = $argv;
    }

    public function getRawArguments(): array {
        return $this->args;
    }
}
```

##  Usage example in dispatcher

```php
// In CommandDispatcher::dispatch:
$request = new ConsoleRequest($argv);
$raw    = $request->getRawArguments();
$commandName = $raw[1] ?? null; // first parameter after the script name
```

[Back](../application.md) | [Return to contents](../../../index.md)

