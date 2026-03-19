[Back to Contents](./index.md)

**EN** | [UK](../uk/concept.md) | [RU](../ru/concept.md)
#  Concept of Using Scaleum Framework

Scaleum is a modular PHP framework designed for building scalable, extensible, and strictly organized applications. It does not impose an architecture but provides clear mechanisms for its implementation.

##  Core Principles

- **Logic Containerization** — each functionality is implemented as a module (`Module`), isolated by namespace and responsibility.
- **Clean Architecture** — separation of layers: models, services, controllers, views.
- **Configuration through Code** — preference for configurator over YAML/ENV/annotations.
- **Testability by Default** — all dependencies are explicitly injected, making mocking easy.
- **Minimal Framework Dependency** — most code is portable.

For more details on the architecture, see [here](./architecture.md).

[Back to Contents](./index.md)