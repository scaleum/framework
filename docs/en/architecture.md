[Back to Contents](./index.md)

**EN** | [UK](../uk/architecture.md) | [RU](../ru/architecture.md)
#  Project Architecture

##  General Concept

The framework is built on the principles of modularity, readability, and strict separation of concerns.  
Each component is isolated in its own `namespace` and performs a limited set of tasks.

##  Framework Source Structure (`/src/`)

- `Cache/` — caching
- `Config/` — configuration and parameter loading
- `Console/` — core implementation for CONSOLE mode operation
- `Core/` — ядро фреймворка
- `DependencyInjection/` — dependency injection container (DI)
- `Events/` — event bus
- `Http/` — core implementation for HTTP mode operation
- `i18n/` — internationalization
- `Logger/` — event logging
- `Routing/` — routing and dispatching
- `Security/` — security: authentication and authorization
- `Services/` — service management
- `Session/` — session management
- `Stdlib/` — utility classes
- `Storages/` — data storage drivers

##  Project Structure Based on the Framework (Example)

- `/application/` — application code itself, including:
  - `/commands/` — console command configuration files
  - `/config/` — configuration files
  - `/migrations/` — migration files
  - `/src/` — application classes and project services
  - `/routes/` — routing configuration files
  - `/translations/` — localization files
  - `/log/` — log files
  - `/views/` — view templates
- `/public/` — public directory (entry point `index.php`, assets)
- `/vendor/` — third-party dependencies installed via Composer

[Back to Contents](./index.md)