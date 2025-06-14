[Вернуться к оглавлению](../../index.md)
# Application

Класс `Http\Application` — это реализация ядра `KernelAbstract` специально для работы в режиме HTTP.

> Важно: в дальнейшем возможны другие реализации ядра, например для консольных приложений.

## Назначение

- Приём входящего HTTP-запроса ([InboundRequest](./components/http/inbound-request.md)`)
- Поиск маршрута и контроллера
- Вызов контроллера для обработки запроса
- Получение ответа в виде объекта `ResponderInterface`
- Отправка ответа клиенту на уровне ядра (`KernelAbstract`)

## Связь с ядром

`Http\Application` расширяет `KernelAbstract` и реализует специфическую логику обработки HTTP-запросов.


## Основные задачи

- Создать объект запроса [InboundRequest](./components/http/inbound-request.md)
- Передать его в [RequestHandler](./components/http/request-handler.md)
- Найти контроллер через [ControllerResolver](./components/http/controller-resolver.md)
- Вызвать контроллер через [ControllerInvoker](./components/http/controller-invoker.md)
- Получить объект `ResponderInterface`
- Отправить ответ через `$response->send()` (ядро)

## Важные классы модуля `Http`

| Класс | Назначение |
|:------|:-----------|
| [InboundRequest](./inbound-request.md) | Представление входящего запроса от клиента |
| [InboundResponse](./inbound-response.md) | Ответ на запросы к внешним системам |
| [OutboundRequest](./outbound-request.md) | Исходящий запрос к внешним системам (например, API, CURL) |
| [OutboundResponse](./outbound-response.md) | Ответ приложения клиенту |
| [RequestHandler](./request-handler.md) | Обработка запроса, возврат `ResponderInterface` |
| [DependencyInjection\Routing](./routing.md)   | Конфигуратор DI: регистрирует сервисы и настройки модуля |
| [ControllerResolver](./controller-resolver.md) | Поиск контроллера по маршруту |
| [ControllerInvoker](./controller-invoker.md) | Вызов контроллера с передачей аргументов |
| [Uri](./uri.md) | Работа с URI-записями |
| [Message](./message.md), [Stream](./components/http/stream.md), [StreamTrait](./components/http/stream-trait.md) | Базовые структуры для HTTP-сообщений |
[HeadersManager](./headers-manager.md) | Класс для управления HTTP-заголовками
[MethodDispatcherTrait](./method-dispatcher-trait.md) | Маршрутизация входящих вызовов на методы контроллера по шаблону `HTTP_метод_путь`, где путь формируется из сегментов маршрута

## Процесс обработки запроса
1. Инициализация: `Application::bootstrap()`
   - Установка конфигуратора `DependencyInjection\Routing` маршрутизации в системный реестр.
   - Запуск стандартного процесса ядра: загрузка конфигов, регистрация сервисов, события (`KernelEvents::BOOTSTRAP`).
2. Получение обработчика: `getHandler()` — создаётся `RequestHandler`.
3. Обработка запроса `getHandler()->handle()`:
   - Загрузка настроек маршрутизации.
   - Инициализация объекта `InboundRequest::fromGlobals()` на основе суперглобальных переменных (`$_GET`, `$_POST`, `$_SERVER`)
   - Поиск маршрута через `ControllerResolver`
   - Вызов контроллера через `ControllerInvoker`
4. Получение объекта `ResponderInterface`.
5. Вызов `$response->send()` на уровне ядра для отправки ответа клиенту

[Вернуться к оглавлению](../../index.md)