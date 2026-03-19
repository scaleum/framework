[Back](../application.md) | [Back to Contents](../../../index.md)

**EN** | [UK](../../../../uk/components/console/contracts/command-interface.md) | [RU](../../../../ru/components/console/contracts/command-interface.md)
#  CommandInterface

`CommandInterface` is an interface for CLI commands in the Scaleum framework. It defines the basic contract that all commands must comply with.

##  Purpose

- To provide a unified method for executing a command based on an incoming request.
- To ensure that each command returns a valid response implementing `ConsoleResponseInterface`.

##  Interface method

```php
interface CommandInterface
{
    /**
     * Executes the command logic based on the input request.
     *
     * @param ConsoleRequestInterface $request Request object with arguments and options
     * @return ConsoleResponseInterface Response object with execution code and output
     */
    public function execute(ConsoleRequestInterface $request): ConsoleResponseInterface;
}
```

- `execute(ConsoleRequestInterface $request): ConsoleResponseInterface` — the main method to run the command. It accepts a `ConsoleRequestInterface` containing the original CLI arguments and returns a `ConsoleResponseInterface` with the execution result.

##  Implementation example

```php
use Scaleum\Console\CommandAbstract;
use Scaleum\Console\Contracts\ConsoleRequestInterface;
use Scaleum\Console\Contracts\ConsoleResponseInterface;

class HelloCommand extends CommandAbstract implements CommandInterface {
    public function execute(ConsoleRequestInterface $request): ConsoleResponseInterface {
        $name = $request->getArgument('name') ?? 'World';
        $response = new Response();
        $response->setContent("Hello, {$name}!");
        $response->setStatusCode(Response::STATUS_SUCCESS);
        return $response;
    }
}
```
[Back](../application.md) | [Back to Contents](../../../index.md)

