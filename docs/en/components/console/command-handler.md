[Back](./application.md) | [Return to contents](../../index.md)

**EN** | [UK](../../../uk/components/console/command-handler.md) | [RU](../../../ru/components/console/command-handler.md)
#  CommandHandler

`CommandHandler` is a class for handling console requests, implementing `HandlerInterface`. It is responsible for loading command descriptions, registering them in `CommandDispatcher`, and executing the selected command.

##  Purpose

- Loading command configurations from a file and directory.
- Registering commands in `CommandDispatcher`.
- Generating events before and after processing (`HandlerInterface::EVENT_GET_REQUEST`, `HandlerInterface::EVENT_GET_RESPONSE`).
- Invoking command dispatching (`CommandDispatcher::dispatch`).

##  Constructor

```php
public function __construct(ContainerInterface $container)
```
- Retrieves the event service from the PSR-11 container by the alias `Framework::SVC_EVENTS`.
- Checks that the service implements `EventManagerInterface`, otherwise throws `ERuntimeError`.

##  Method handle()

```php
public function handle(): ResponderInterface
```
1. Getting the dispatcher
   ```php
   $dispatcher = $this->container->get('commands.dispatcher');
   ```
2. Loading command descriptions
   ```php
   $loader   = $this->container->get(LoaderResolver::class);
   $commands = [];
   if (file_exists($file = $container->get('commands.file'))) {
       $commands = $loader->fromFile($file);
   }
   if (is_dir($dir = $container->get('commands.directory'))) {
       $commands = ArrayHelper::merge($commands, $loader->fromDir($dir));
   }
   ```
3. Registering commands
   ```php
   foreach ($commands as $name => $class) {
       $dispatcher->registerCommand($name, $container->get($class));
   }
   ```
4. Generating event before execution
   ```php
   $this->events->dispatch(
       HandlerInterface::EVENT_GET_REQUEST,
       $this,
       ['request' => new Request()]
   );
   ```
5. Calling `CommandDispatcher::dispatch`
   ```php
   $response = $dispatcher->dispatch(new Request());
   ```
6. Generating event after execution
   ```php
   $this->events->dispatch(
       HandlerInterface::EVENT_GET_RESPONSE,
       $this,
       ['response' => $response]
   );
   ```
7. Returning `ResponderInterface`
   ```php
   return $response;
   ```

###  Usage example

```php
use Scaleum\Console\Application;

$app = new Application([...]);
$app->bootstrap();
/ @var ResponderInterface $response */
$response = $app->getHandler()->handle();
// $response->send() or exit($response->getExitCode());
```
[Back](./application.md) | [Return to contents](../../index.md)

