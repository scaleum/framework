# Оглавление документации

### 1. Введение
- [Введение](./introduction.md)

### 2. Концепция использования
- [Концепция использования](./concept.md)

### 3. Установка
- [Установка](./installation.md)

### 4. Структура исходников
- [Структура проекта](./architecture.md)
- [Версионирование](./version.md)

### 5. Работа с ядром
- [KernelAbstract](./kernel-abstract.md)
  - [HandlerInterface]() *(в разработке)*
  - [ResponderInterface]() *(в разработке)*
- [Http\Application](./components/http/application.md)
    - Client
      - [RequesterAbstract](./components/http/client/requester-abstract.md)
      - Transport
        - [TransportInterface](./components/http/client/transport/transport-interface.md)
        - [TransportAbstract](./components/http/client/transport/transport-abstract.md)
        - [CurlTransport](./components/http/client/transport/curl-transport.md)
        - [SocketTransport](./components/http/client/transport/socket-transport.md)
    - Renderers
      - [Template](./components/http/renderers/template.md)
      - [TemplateRenderer](./components/http/renderers/template-renderer.md)
      - Plugins
        - [RendererPluginInterface](./components/http/renderers/plugins/renderer-plugin-interface.md)
        - [Gettext](./components/http/renderers/plugins/gettext.md)
        - [IncludeAsset](./components/http/renderers/plugins/include-asset.md)
        - [IncludeTemplate](./components/http/renderers/plugins/include-template.md)
    - [InboundRequest](./components/http/inbound-request.md)
    - [InboundRespons](./components/http/inbound-response.md)
    - [OutboundRequest](./components/http/outbound-request.md)
    - [OutboundResponse](./components/http/outbound-response.md)
    - [RequestHandler](./components/http/request-handler.md)
    - [DependencyInjection\Routing](./components/http/routing.md)
    - [ControllerResolver](./components/http/controller-resolver.md)
    - [ControllerInvoker](./components/http/controller-invoker.md)
    - [Uri](./components/http/uri.md)
    - [Message](./components/http/message.md)
    - [Stream](./components/http/stream.md)
    - [StreamTrait](./components/http/stream-trait.md)
    - [HeadersManager](./components/http/headers-manager.md)
    - [MethodDispatcherTrait](./components/http/method-dispatcher-trait.md)
- [Console\Application](./components/console/application.md)
  - [Contracts\CommandInterface](./components/console/contracts/command-interface.md)
  - [Contracts\ConsoleRequestInterface](./components/console/contracts/console-request-interface.md)
  - [Contracts\ConsoleResponseInterface](./components/console/contracts/console-response-interface.md)
  - [DependencyInjection\Commands](./components/console/commands.md)
  - [CommandHandler](./components/console/command-handler.md)
  - [CommandDispatcher](./components/console/command-dispatcher.md)
  - [CommandAbstract](./components/console/command-abstract.md)
  - [ConsoleOptions](./components/console/console-options.md)
  - [LockManager](./components/console/lock-manager.md)
  - [LockHandle](./components/console/lock-handle.md)
  - [Request](./components/console/request.md)
  - [Response](./components/console/response.md)

### 6. Компоненты
- [Cache](./components/cache.md)
- [DependencyInjection](./components/dependency-injection.md)
- [EventManager](./components/events.md)
- [ServiceManager](./components/service-locator.md)
- [Routing](./components/routing.md)
- i18n
  - Contracts
    - [LocaleIdentityInterface](./components/i18n/contracts/LocaleIdentityInterface.md)
    - [TranslationLoaderInterface](./components/i18n/contracts/TranslationLoaderInterface.md)
  - Loaders
    - [Gettext](./components/i18n/loaders/Gettext.md)
    - [Ini](./components/i18n/loaders/Ini.md)
    - [PhpArray](./components/i18n/loaders/PhpArray.md)
    - [TranslationLoaderAbstract](./components/i18n/loaders/TranslationLoaderAbstract.md)
  - [LoaderDispatcher](./components/i18n/LoaderDispatcher.md)
  - [LocaleDetector](./components/i18n/LocaleDetector.md)
  - [LocaleIdentityAbstract](./components/i18n/LocaleIdentityAbstract.md)
  - [Translator](./components/i18n/Translator.md)
  - [TranslatorTrait](./components/i18n/TranslatorTrait.md)
- [Logger](./components/logger.md)
- [Security](./components/security.md)
- [Session](./components/session.md)
- Storages
  - PDO
    - [DatabaseProviderInterface](./components/storages/pdo/DatabaseProviderInterface.md)

### 7. Базовые классы
- [AttributeContainer](./classes/attribute-container.md)
- [Benchmark](./classes/benchmark.md)
- [CallbackInstance](./classes/callback-instance.md)
- [Collection](./classes/collection.md)
- [FileResolver](./classes/file-resolver.md)
- [Hydrator](./classes/hydrator.md)
- [InitTrait](./classes/init-trait.md)
- [Registry](./classes/registry.md)
- [SAPI Explorer](./classes/sapi.md)

### 8. Помощники
- [ArrayHelper](./helpers/array-helper.md)
- [BytesHelper](./helpers/bytes-helper.md)
- [EnvHelper](./helpers/env-helper.md)
- [FileHelper](./helpers/file-helper.md)
- [HttpHelper](./helpers/http-helper.md)
- [JsonHelper](./helpers/json-helper.md)
- [PathHelper](./helpers/path-helper.md)
- [ProcessHelper](./helpers/process-helper.md)
- [StringCaseHelper](./helpers/string-case-helper.md)
- [StringHelper](./helpers/string-helper.md)
- [TimeHelper](./helpers/time-helper.md)
- [TimezoneHelper](./helpers/timezone-helper.md)
- [TypeHelper](./helpers/type-helper.md)
- [UniqueHelper](./helpers/unique-helper.md)
- [UrlHelper](./helpers/url-helper.md)
- [Utf8Helper](./helpers/utf8-helper.md)
- [XmlHelper](./helpers/xml-helper.md)
