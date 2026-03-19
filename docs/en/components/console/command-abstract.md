[Back](./application.md) | [Return to contents](../../index.md)

**EN** | [UK](../../../uk/components/console/command-abstract.md) | [RU](../../../ru/components/console/command-abstract.md)
#  CommandAbstract

`CommandAbstract` is a base abstract class for implementing CLI commands in the Scaleum framework. It partially implements `CommandInterface`, providing convenient methods for working with options, arguments, and message output.

##  Purpose

- Provide access to the `ConsoleOptions` object for managing command-line options and arguments.
- Ensure output of information and errors to the console via the `printLine` method.
- Serve as a common base for all specific commands inheriting shared functionality.

##  Interface relation

Implements `Scaleum\Console\Contracts\CommandInterface`, so child classes must implement the method:
```php
public function execute(ConsoleRequestInterface $request): ConsoleResponseInterface;
```

##  Properties

| Property                         | Type                          | Description                                         |
|:---------------------------------|:-----------------------------|:----------------------------------------------------|
| `protected ?ConsoleOptions $options` | `ConsoleOptions|null`         | Object for parsing CLI request options and arguments. |

##  Methods

###  getOptions
```php
public function getOptions(): ConsoleOptions
```
- Creates and stores a new instance of `ConsoleOptions` on first call.
- Returns an object containing methods for parsing and retrieving option and argument values.

###  printLine
```php
public function printLine(string $message, bool $isError = false): void
```
- Outputs the string `$message` with a newline to the console.
- If `$isError === true`, outputs to the `STDERR` stream; otherwise, to `STDOUT`.

##  Command implementation example
```php
use Scaleum\Console\CommandAbstract;
use Scaleum\Console\Contracts\ConsoleRequestInterface;
use Scaleum\Console\Contracts\ConsoleResponseInterface;

class HelloCommand extends CommandAbstract
{
    public function execute(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        // Parsing options and arguments
        $options = $this->getOptions()
            ->setOptsLong(["name::"])
            ->setArgs($request->getRawArguments())
            ->parse();

        $name    = $options->get('name', 'World');

        // Output message to STDOUT
        $this->printLine("Hello, {$name}!");

        // Form response with success code
        $response = new Response();
        $response->setContent("Greeting sent to {$name}");
        $response->setStatusCode(Response::STATUS_SUCCESS);
        return $response;
    }
}
```

##  Recommendations

- Use `getOptions()` for centralized management of CLI flags and parameter parsing.
- For error output, use `printLine($msg, true)` so the message goes to `STDERR`.
- Inherit `CommandAbstract` in all command classes for consistent behavior.

[Back](./application.md) | [Return to contents](../../index.md)
