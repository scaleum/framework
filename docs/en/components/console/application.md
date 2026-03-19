[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/console/application.md) | [RU](../../../ru/components/console/application.md)
#  Application

The class `Console\Application` is an implementation of the ядро `KernelAbstract` for working in console mode (CLI).<br>
> Important: this is a ядро variant optimized for executing console commands.

##  Purpose

- Registering command configurators in the registry (`kernel.configurators`).
- Initializing and loading command configuration via DI.
- Providing CLI request handling through `CommandHandler`.

##  Relation to ядро

`Console\Application` extends `KernelAbstract`, overriding the `bootstrap()` method to set CLI-specific configurators while preserving the general ядро lifecycle.

##  Main tasks

- Execute the general process `KernelAbstract::bootstrap()` (loading configs, registering services, events).
- Provide a CLI handler via the `getHandler()` method.

##  Important classes of the `Console` module

| Class| Purpose |
|:------|:------|
| [Contracts\CommandInterface](./contracts/command-interface.md) | Interface for CLI commands |
| [Contracts\ConsoleRequestInterface](./contracts/console-request-interface.md) | Interface for the console mode request object |
| [Contracts\ConsoleResponseInterface](./contracts/console-response-interface.md) | Interface for the console mode response |
| [DependencyInjection\Appendix](#) | DI configurator: registers module services and settings |
| [DependencyInjection\Commands](./commands.md) | DI configurator: registers module services and settings |
| [CommandHandler](./command-handler.md) | Loads command descriptions, registers them in `CommandDispatcher` |
| [CommandDispatcher](./command-dispatcher.md) | Handles incoming commands and calls the corresponding `CommandInterface` |
| [CommandAbstract](./command-abstract.md) | Base abstract class for implementing CLI commands |
| [ConsoleOptions](./console-options.md) | Class for parsing and managing command line options and arguments |
| [LockManager](./lock-manager.md) | Class for managing lock files in console applications |
| [LockHandle](./lock-handle.md) | Helper class for managing process locks in console applications |
| [Request](./request.md) | Class representing a CLI request |
| [Response](./response.md) | Class implementing `ConsoleResponseInterface` for forming and sending output in console mode |
<!-- | [HandlerInterface](./handler-interface.md) | Contract for request handlers (`handle(): ResponderInterface`) | -->
<!-- | [ResponderInterface](./responder-interface.md) | Contract for responders returning execution code and output | -->

##  CLI request handling process

1. Initialization: `Application::bootstrap()`
   - Setting the application configurator `DependencyInjection\Appendix` in the system registry.
   - Setting the application configurator `DependencyInjection\Commands` in the system registry.
   - Starting the standard ядро process: loading configs, registering services, events (`KernelEvents::BOOTSTRAP`).
2. Obtaining the handler: `getHandler()` — creates `HandlerInterface(CommandHandler)`.
3. Command handling `getHandler()->handle()`:
   - Parsing arguments from `$argv` via `Request`.
   - Searching and invoking the required command via `CommandDispatcher`.
4. Obtaining the `ResponderInterface` object.
5. Calling `$response->send()` at the ядро level to send the response to the client.


[Back to Contents](../../index.md)

