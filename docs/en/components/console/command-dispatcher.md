[Back](./application.md) | [Return to contents](../../index.md)

**EN** | [UK](../../../uk/components/console/command-dispatcher.md) | [RU](../../../ru/components/console/command-dispatcher.md)
#  CommandDispatcher

`CommandDispatcher` is the central component of the console commands module, responsible for registering available commands and invoking them by name based on the incoming CLI request.

##  Purpose

- Store a collection of registered commands (`CommandInterface`).
- Execute the required command based on the first argument of the request.
- Form a `ConsoleResponseInterface` response in case of a missing or erroneous command.

##  Properties

| Property          | Type                            | Description                                       |
|:------------------|:-------------------------------|:-------------------------------------------------|
| `private array $commands` | `string => CommandInterface` | Dictionary of command names and their instances. |

##  Methods

###  registerCommand()
```php
public function registerCommand(string $name, CommandInterface $command): void
```
- Registers a command under the key `$name`.
- Allows adding default commands and commands from configuration.

###  dispatch()
```php
public function dispatch(ConsoleRequestInterface $request): ConsoleResponseInterface
```
1. Extracts raw arguments: `$args = $request->getRawArguments()`.
2. Determines the command name: `$name = $args[0] ?? null`.
3. If the command exists in `$this->commands`, calls its `execute($request)` and obtains a `ConsoleResponseInterface`.
4. If the command is not found or not specified, creates a new `Response` with an error message and status `ConsoleResponseInterface::STATUS_NOT_FOUND`.
5. Returns the response object.

##  Usage example

```php
use Scaleum\Console\CommandDispatcher;
use App\Commands\HelloCommand;
use Scaleum\Console\ConsoleRequest;

// Registering commands
$dispatcher = new CommandDispatcher();
$dispatcher->registerCommand('hello', new HelloCommand());

// Creating a request from argv
$request = new ConsoleRequest();
// Suppose, php script.php hello John

// Executing the command
$response = $dispatcher->dispatch($request);

// Handling the response
echo $response->getContent();
exit($response->getStatusCode());
```

##  Error handling

- If `$request->getRawArguments()` is empty or the command is not registered, a `ConsoleResponseInterface` with an error message will be returned.

[Back](./application.md) | [Return to contents](../../index.md)

