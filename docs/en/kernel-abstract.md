[Back to contents](./index.md)

**EN** | [UK](../uk/kernel-abstract.md) | [RU](../ru/kernel-abstract.md)
#  KernelAbstract

`KernelAbstract` is the base abstract class of the Scaleum framework, managing the process of bootstrapping and running the application.

##  Purpose

- Loading user configuration
- Loading configuration files
- Setting container configurators
- Registering behaviors
- Registering services
- Generating the `KernelEvents::BOOTSTRAP` event
- Generating lifecycle events `KernelEvents::START`, `KernelEvents::FINISH`, `KernelEvents::HALT`
- Transitioning the ядро to a ready state (`inReadiness = true`)

##  Lifecycle

1. Merging user parameters with the `Registry`.
2. Loading configuration files (`kernel.configs`).
3. Registering container configurators.
4. Registering behaviors via [EventManager](./components/events.md).
5. Registering services in [ServiceManager](./components/service-locator.md).
6. Generating the `KernelEvents::BOOTSTRAP` event.
7. Setting the readiness flag (`inReadiness = true`).
8. Generating the `KernelEvents::START` event (once).
9. Executing the main handler (`HandlerInterface::handle()`) and sending the response.
10. Before finishing the process, generating the `KernelEvents::FINISH` event (once).
11. Generating the `KernelEvents::HALT` event and terminating the process (`exit`).

`KernelEvents::HALT` is the last lifecycle event.

`run()` does not catch exceptions: on error, control is passed up the stack.
The `KernelEvents::FINISH` event is generated once before `KernelEvents::HALT`:
either on the successful path in `run()`, or in `halt($code)` during an emergency shutdown.

##  Main Methods

| Method | Purpose |
|:------|:-----------|
`getApplicationDir(): string` | Returns the path to the project/application folder
`getConfigDir(): string` | Returns the path to the configuration folder
`getEnvironment(): string` | Returns the environment variable value
`getEventManager(): EventManagerInterface` | Returns an instance of [EventManager](./components/events.md)
`getServiceManager(): ServiceProviderInterface` | Returns an instance of [ServiceManager](./components/service-locator.md)
`getContainer(): ContainerInterface` | Returns an instance of the container [Container](./components/dependency-injection.md)
`bootstrap(array $config = []): self` | Starts ядро preparation: configurations, services, events.
`run(): void` | Starts the main flow: request handling, response sending; on success calls `FINISH` and `halt(0)`.
`isStarted(): bool` | Returns `true` if the `KernelEvents::START` event has already been generated
`isFinished(): bool` | Returns `true` if the `KernelEvents::FINISH` event has already been generated
`halt(int $code = 0): void` | Interrupts the flow execution, generates `FINISH` if necessary, and sets the exit code (`$code`)

##  Usage Example

```php
require __DIR__ . '/../vendor/autoload.php';
use Scaleum\Core\KernelAbstract;

class MyApplication extends KernelAbstract
{
    public function run(): void
    {
        echo "Application is running.\n";
        parent::run();
    }
}

$app = new MyApplication([
    'application_dir' => dirname(__DIR__, 1) . '/protected',
    'config_dir'      => dirname(__DIR__, 1) . '/protected/config',
    'environment'     => 'dev',
]);

$app->bootstrap();
$app->run();
```

[Back to contents](./index.md)