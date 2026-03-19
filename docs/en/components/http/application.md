[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/http/application.md) | [RU](../../../ru/components/http/application.md)
#  Application

The `Http\Application` class is an implementation of the ядро `KernelAbstract` specifically designed to operate in HTTP mode.

> Important: other ядро implementations may be possible in the future, for example, for console applications.

##  Purpose

- Receiving an incoming HTTP request ([InboundRequest](./components/http/inbound-request.md))
- Finding the route and controller
- Calling the controller to process the request
- Obtaining a response as a `ResponderInterface` object
- Sending the response to the client at the ядро level (`KernelAbstract`)

##  Relation to ядро

`Http\Application` extends `KernelAbstract` and implements specific logic for handling HTTP requests.

##  Main tasks

- Create a request object [InboundRequest](./components/http/inbound-request.md)
- Pass it to [RequestHandler](./components/http/request-handler.md)
- Find the controller via [ControllerResolver](./components/http/controller-resolver.md)
- Call the controller via [ControllerInvoker](./components/http/controller-invoker.md)
- Obtain a `ResponderInterface` object
- Send the response via `$response->send()` (ядро)

##  Important classes of the `Http` module

| Class | Purpose |
|:------|:--------|
| [InboundRequest](./inbound-request.md) | Representation of an incoming request from the client |
| [InboundResponse](./inbound-response.md) | Response to requests to external systems |
| [OutboundRequest](./outbound-request.md) | Outgoing request to external systems (e.g., API, CURL) |
| [OutboundResponse](./outbound-response.md) | Application response to the client |
| [RequestHandler](./request-handler.md) | Request processing, returns `ResponderInterface` |
| [DependencyInjection\Appendix](#) | DI configurator: registers services and module settings |
| [DependencyInjection\Routing](./routing.md) | DI configurator: registers services and module settings |
| [ControllerResolver](./controller-resolver.md) | Finds the controller by route |
| [ControllerInvoker](./controller-invoker.md) | Calls the controller with argument passing |
| [Uri](./uri.md) | Working with URI records |
| [Message](./message.md), [Stream](./components/http/stream.md), [StreamTrait](./components/http/stream-trait.md) | Basic structures for HTTP messages |
| [HeadersManager](./headers-manager.md) | Class for managing HTTP headers |
| [MethodDispatcherTrait](./method-dispatcher-trait.md) | Routing incoming calls to controller methods by the pattern `HTTP_method_path`, where the path is formed from route segments |

##  Request processing flow
1. Initialization: `Application::bootstrap()`
   - Setting the application configurator `DependencyInjection\Appendix()` in the system registry.
   - Setting the routing configurator `DependencyInjection\Routing` in the system registry.
   - Starting the standard ядро process: loading configs, registering services, events (`KernelEvents::BOOTSTRAP`).
2. Obtaining the handler: `getHandler()` — creates `HandlerInterface(RequestHandler)`.
3. Processing the request `getHandler()->handle()`:
   - Loading routing settings.
   - Initializing the `InboundRequest::fromGlobals()` object based on superglobals (`$_GET`, `$_POST`, `$_SERVER`)
   - Finding the route via `ControllerResolver`
   - Calling the controller via `ControllerInvoker`
4. Obtaining the `ResponderInterface` object.
5. Calling `$response->send()` at the ядро level to send the response to the client

[Back to Contents](../../index.md)