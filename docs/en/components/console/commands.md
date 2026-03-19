[Back](./application.md) | [Return to contents](../../index.md)

**EN** | [UK](../../../uk/components/console/commands.md) | [RU](../../../ru/components/console/commands.md)
#  Commands

The class `Scaleum\Console\DependencyInjection\Commands` is a dependency injection container configurator for the console commands module. It registers services and parameters necessary for locating and running CLI commands.

##  Purpose

- Define services and settings in the container for console mode.
- Configure the command dispatcher (`CommandDispatcher`).
- Specify paths to files and directories containing command descriptions.

##  Relation to ядро

Used in `Console\Application::bootstrap()`, where it is added to the ядро configurators registry:
```php
$this->getRegistry()->set('kernel.configurators', [
    new Commands(),
]);
```  
Thus, during `KernelAbstract::bootstrap()`, `Commands::configure()` is called, and the container receives definitions for the CLI command.

##  Main tasks

- Register the `CommandDispatcher` class for routing and executing commands.
- Inject parameters:
  - `commands.file` — path to the `commands.php` file in the ядро config directory.
  - `commands.directory` — directory with additional command files.
  - `commands.dispatcher` — alias for the command dispatcher service.

##  Definitions in the container

| Container name                 | Value / Service                                           | Description                                                   |
|:------------------------------|:----------------------------------------------------------|:--------------------------------------------------------------|
| `CommandDispatcher::class`    | `Autowire::create()`                                      | Automatic dependency autowiring into the command dispatcher.  |
| `commands.file`               | factory returning `<kernel.config_dir>/commands.php`      | Path to the main command description file.                    |
| `commands.directory`          | factory returning `<kernel.config_dir>/commands`          | Directory with additional command files.                      |
| `commands.dispatcher`         | `CommandDispatcher::class`                                | Alias for the command dispatcher service.                     |

###  Configuration and usage example

```php
// In Application::bootstrap():
$app->getRegistry()->set('kernel.configurators', [
    new Scaleum\Console\DependencyInjection\Commands(),
]);
$app->bootstrap();

// Retrieving settings from the container:
/** @var ContainerInterface $c */
$file = $c->get('commands.file');       // '/path/to/config/commands.php'
$dir  = $c->get('commands.directory');  // '/path/to/config/commands'

// Command dispatcher
/** @var CommandDispatcher $dispatcher */
$dispatcher = $c->get('commands.dispatcher');
```

###  Example structure of the `commands.php` file
```php
return [
    'greet'    => \Application\Commands\GreetCommand::class,
    'chat'     => \Application\Commands\ChatCommand::class,
    'migrate'  => \Application\Commands\MigrateCommand::class,
];
```
###  Usage example
The `index.php` file - application entry point
```php
require __DIR__ . '/../vendor/autoload.php';
use Scaleum\Console\Application;

$app = new Application([
    'application_dir' => dirname(__DIR__, 1) . '/protected',
    'config_dir'      => dirname(__DIR__, 1) . '/protected/config',
    'environment'     => 'dev',
]);

$app->bootstrap();
$app->run();
```

###  Running a console command
```
php .\index.php greet --name=User
```

[Back](./application.md) | [Return to contents](../../index.md)

